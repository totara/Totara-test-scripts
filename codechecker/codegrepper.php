<?php
/**
 * A new script to run as part of code checker
 *
 * This script runs some regular expressions over the changed files
 * looking for likely bugs or problems and reports the results
 *
 * TODO:
 * - currently only a few checks, need lots more
 * - currently doesn't support multi-line regexps
 * - could be tidied up
 */

function run_codegrepper($file, $dirroot, $changedlinenumbers) {
    $results = array();

    $filecontents = file_get_contents($dirroot . DIRECTORY_SEPARATOR . $file);
    $lineoffsets = get_line_offsets($filecontents);

    // Add more preg regular expressions here to match potential bugs/code issues
    $regexpclasses = array(
        // a table name should never be aliased with the AS keyword
        'bad AS sql' => '/\{[^{}]+\}\s+AS/',
        'LIMIT sql' => '/LIMIT\s+[0-9]+/',
        'wwwroot in moodle_url' => '/moodle_url\([^)]*?\$CFG\->wwwroot/',
        'unescaped like' => '/sql_like\([^\)]*%/',
        'bad alist' => '/\$OUTPUT\->alist/',
        'deprecated' => '/admin_externalpage_print_header/',
        'use abs path' => '/moodle_url\(\'[^\/]+?[,\)]/',
        // this is probably bad, at least warn about it they can always commit anyway
        'PARAM_RAW' => '/PARAM_RAW/',
        // same here
        'var_dump' => '/var_dump/',
    );

    // Loop through each regexp to check
    foreach ($regexpclasses as $class => $regexp) {
        // Look for matches. Avoid doing line by line to allow for support of multiline regexps
        $matches = find_matches($regexp, $filecontents);
        foreach ($matches as $match) {
            $result = process_match($match, $lineoffsets, $changedlinenumbers, $filecontents, $file, $class);
            if ($result) {
                $results[] = $result;
            }
        }
    }

    // only apply these checks in files called renderer.php
    if (basename($file) == 'renderer.php') {
        // (you should use $this->output instead of $OUTPUT within a renderer)
        $matches = find_matches('/\$OUTPUT/', $filecontents);
        foreach ($matches as $match) {
            $result = process_match($match, $lineoffsets, $changedlinenumbers, $filecontents, $file, '$OUTPUT in renderer');
            if ($result) {
                $results[] = $result;
            }
        }

        // try to minimise database calls inside renderers
        $matches = find_matches('/\$DB/', $filecontents);
        foreach ($matches as $match) {
            $result = process_match($match, $lineoffsets, $changedlinenumbers, $filecontents, $file, '$DB in renderer');
            if ($result) {
                $results[] = $result;
            }
        }
    }

    return $results;
}

/**
 * Perform a match and return a results object suitable for codechecker
 */
function process_match($match, $lineoffsets, $changedlinenumbers, $filecontents, $file, $class) {
    include('colors.php');
    list($matchstr, $matchoffset) = $match;
    $linenum = get_line_number_from_offset($matchoffset, $lineoffsets);
    // Ignore issues unless we modified the line
    if (!in_array($linenum, $changedlinenumbers)) {
        return false;
    }
    $pos = get_positions_from_offset($matchstr, $matchoffset, $filecontents, $lineoffsets);

    $message = substr($filecontents, $pos['linestart'], ($pos['matchstart'] - $pos['linestart']));
    $message .= ColorCli::$colorshouldbefixed(substr($filecontents, $pos['matchstart'], ($pos['matchend'] - $pos['matchstart'])));
    $message .= substr($filecontents, $pos['matchend'], ($pos['lineend'] - $pos['matchend']));

    $result = new stdClass();
    $result->file = $file;
    $result->line = $linenum;
    $result->column = ($pos['matchstart'] - $pos['linestart'] + 1);
    $result->class = $class;
    $result->message = $message;
    $result->checker = 'codegrepper';
    return $result;
}

/**
 * Get any matches for a particular regexp
 */
function find_matches($regexp, $filecontents) {
    $status = preg_match_all($regexp, $filecontents, $matches, PREG_OFFSET_CAPTURE);
    // no match or error with regexp
    if (!$status) {
        return array();
    }
    return $matches[0];
}


/**
 * Returns an array of string positions containing new lines
 *
 * Used to calculate the linenumber that a particular offset occurs at
 */
function get_line_offsets($filecontents) {
    $status = preg_match_all("/\n/", $filecontents, $matches, PREG_OFFSET_CAPTURE);
    if (!$status) {
        // single line file, no newlines
        return array();
    }
    $lineoffsets = array();
    foreach ($matches[0] as $match) {
        $lineoffsets[] = $match[1];
    }
    return $lineoffsets;
}

/**
 * Given a particular offset and the array of new line positions,
 * calculate the line an offset is on
 */
function get_line_number_from_offset($offset, $lineoffsets) {
    $linenum = 1;
    foreach ($lineoffsets as $lineoffset) {
        if ($offset <= $lineoffset) {
            break;
        }
        $linenum++;
    }
    return $linenum;
}

/**
 * Given a match and its offset, return the positions of the start and end of the matching line
 * and matching string
 *
 * TODO doesn't currently support multi-line regexps
 */
function get_positions_from_offset($match, $matchoffset, $filecontents, $lineoffsets) {
    $start = 0;
    $end = strlen($filecontents);
    foreach ($lineoffsets as $lineoffset) {
        $currentline = $lineoffset;
        if ($matchoffset <= $lineoffset) {
            $start = $lastline;
            $end = $currentline;
            break;
        }
        $lastline = $lineoffset;
    }
    return array(
        'linestart' => $start + 1,
        'lineend' => $end,
        'matchstart' => $matchoffset,
        'matchend' => $matchoffset + strlen($match)
    );
}

