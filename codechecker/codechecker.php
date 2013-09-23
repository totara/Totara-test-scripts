<?php
/**
 *
 */
require_once(__DIR__ . '/codesniffer.php');
require_once(__DIR__ . '/lintchecker.php');
require_once(__DIR__ . '/codegrepper.php');
require_once(dirname(__DIR__) . '/utils/utilslib.php');

function compare_result_by_line($a, $b) {
    if (isset($a->line) && isset($b->line)) {
        if ($a->line == $b->line) {
            return 0;
        }
        return ($a->line < $b->line) ? -1 : 1;
    } else if (isset($a->line)) {
        return -1;
    } else if (isset($b->line)) {
        return 1;
    } else {
        return 0;
    }
}

/**
 * Run all code checks.
 * @param type $dir
 * @return An error code - 0 to continue
 */
function run_codechecker($githash = null) {
    include('colors.php');
    $problems = 0;

    $dirroot = get_web_root();

    // Find the files that have been changed
    if (isset($githash)) {
        exec('git show --name-status ' . $githash, $files);
    } else {
        exec('git diff --cached --name-status', $files);
    }

    // Run the code sniffer
    for ($i = 0; $i < sizeof($files); $i++) {
        if (!preg_match('/^([AMD])\s+(.+\.php)$/', $files[$i], $matches)) {
            // diff output doesn't appear to match expected format
            continue;
        }
        if ($matches[1] == 'D') {
            // no need to test deleted files
            continue;
        }
        $file = $matches[2];

        $changedlinenumbers = get_changed_lines($file, $dirroot, $githash);

        // Run each of the checks on the file and collect the results
        $results = array();
        $results = array_merge($results, run_codesniffer($file, $dirroot, $changedlinenumbers));
        $results = array_merge($results, run_lintchecker($file, $dirroot, $changedlinenumbers));
        $results = array_merge($results, run_codegrepper($file, $dirroot, $changedlinenumbers));

        usort($results, "compare_result_by_line");

        // Print results
        if (!empty($results)) {
            echo ColorCli::$colorfile($file) . " contains problems\n";
            foreach ($results as $result) {
                echo "  ";
                switch ($result->class) {
                    case 'fatal':
                    case 'error':
                        $color = $colormustbefixed;
                        break;
                    case 'warning':
                    case 'notice':
                        $color = $colorshouldbefixed;
                        break;
                    default:
                        $color = $colorcanbeignored;
                        break;
                }
                echo ColorCli::$color(str_pad($result->class, 12));
                if (isset($result->line)) {
                    echo ColorCli::$colorline(" line " . str_pad($result->line, 4));
                } else {
                    echo "          ";
                }
                if (isset($result->column)) {
                    echo ColorCli::$colorcolumn(" col " . str_pad($result->column,3));
                } else {
                    echo "        ";
                }
                echo " " . $result->message . "\n";
            }
        }

        $problems += sizeof($results);
    }

    if ($problems > 0) {
        echo ColorCli::$colorresult("Finished with {$problems} problems.\n");
    }

    return $problems;
}
