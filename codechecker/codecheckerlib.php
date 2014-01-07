<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function get_changed_lines($file, $dirroot, $githash = null) {

    if (isset($githash)) {
        exec("git show $githash -U0 -- " . local_codechecker_clean_path($dirroot . '/' . $file), $diff);
    } else {
        exec("git diff --cached -U0 -- " . local_codechecker_clean_path($dirroot . '/' . $file), $diff);
    }

    $changedlines = array();
    $chunklinesleft = 0;
    foreach ($diff as $line) {
        if ($chunklinesleft == 0 && !preg_match('/^@@/', $line)) {
            continue;
        }
        if (preg_match('/@@ -(?P<oldstart>[0-9]+)(,(?P<oldnum>[0-9]+))? \+(?<newstart>[0-9]+)(,(?P<newnum>[0-9]+))?/', $line, $matches)) {
            // new chunk
            $currentline = (int)$matches['newstart'];
            $chunklinesleft = !empty($matches['newnum']) ? (int)$matches['newnum'] : 1;
            $header = false;
        } else if (preg_match('/^-/', $line)) {
            // removed line - doesn't count toward position in new file
            continue;
        } else if (preg_match('/^\+/', $line)) {
            // added line - record change and add new line
            $changedlines[$currentline] = $currentline;
            $currentline++;
            $chunklinesleft--;
        } else {
            // unchanged line
            $currentline++;
            $chunklinesleft--;
        }
    }
    return $changedlines;
}

/**
 * Get a list of folders to ignore.
 *
 * @param string $dirroot Path to code folder of site being checked
 * @param string $extraignorelist optional comma separated list of substr matching paths to ignore.
 * @return array of paths.
 */
function local_codechecker_get_ignores($dirroot, $extraignorelist = '') {
    $paths = array();

    if (!file_exists($dirroot . '/lib/thirdpartylibs.xml')) {
        return $paths;
    }

    $thirdparty = simplexml_load_file($dirroot . '/lib/thirdpartylibs.xml');
    foreach ($thirdparty->xpath('/libraries/library/location') as $lib) {
        $paths[] = preg_quote(local_codechecker_clean_path('/lib/' . $lib));
    }

    $paths[] = preg_quote(local_codechecker_clean_path(
            DIRECTORY_SEPARATOR . 'pear'));
    // Changed in PHP_CodeSniffer 1.4.4 and upwards, so we apply the
    // same here: Paths go to keys and mark all them as 'absolute'.
    $finalpaths = array();
    foreach ($paths as $pattern) {
        $finalpaths[$pattern] = 'absolute';
    }
    // Let's add any substr matching path passed in $extraignorelist.
    if ($extraignorelist) {
        $extraignorearr = explode(',', $extraignorelist);
        foreach ($extraignorearr as $extraignore) {
            $extrapath = trim($extraignore);
            $finalpaths[$extrapath] = 'absolute';
        }
    }
    return $finalpaths;
}

/**
 * The code-checker code assumes that paths always use DIRECTORY_SEPARATOR,
 * whereas Moodle is more relaxed than that. This method cleans up file paths by
 * converting all / and \ to DIRECTORY_SEPARATOR. It should be used whenever a
 * path is passed to the CodeSniffer library.
 * @param string $path a file path
 * @return the path with all directory separators changed to DIRECTORY_SEPARATOR.
 */
function local_codechecker_clean_path($path) {
    return str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, $path);
}

/**
 * PHP CLI Colors – PHP Class Command Line Colors (bash)
 *
 * $str = "This is an example ";
 *
 * foreach (ColorCLI::$foregroundColors as $fg => $fgCode) {
 *     echo ColorCLI::$fg($str);
 *
 *     foreach (ColorCLI::$backgroundColors as $bg => $bgCode) {
 *         echo ColorCLI::$fg($str, $bg);
 *     }
 *
 *     echo PHP_EOL;
 * }
 *
 * @see http://www.if-not-true-then-false.com/2010/php-class-for-coloring-php-command-line-cli-scripts-output-php-output-colorizing-using-bash-shell-colors/
 */
class ColorCLI
{
    public static $foregroundColors = array(
        'bold'         => '1',    'dim'         => '2',
        'black'        => '0;30', 'dark_gray'   => '1;30',
        'blue'         => '0;34', 'lightBlue'   => '1;34',
        'green'        => '0;32', 'lightGreen'  => '1;32',
        'cyan'         => '0;36', 'lightCyan'   => '1;36',
        'red'          => '0;31', 'lightRed'    => '1;31',
        'purple'       => '0;35', 'lightPurple' => '1;35',
        'brown'        => '0;33', 'yellow'      => '1;33',
        'lightGray'    => '0;37', 'white'       => '1;37',
        'normal'       => '0;39',
    );
 
    public static $backgroundColors = array(
        'black'        => '40',   'red'         => '41',
        'green'        => '42',   'yellow'      => '43',
        'blue'         => '44',   'magenta'     => '45',
        'cyan'         => '46',   'lightGray'   => '47',
    );
 
    public static $options = array(
        'underline'    => '4',    'blink'       => '5',
        'reverse'      => '7',    'hidden'      => '8',
    );
 
    public static function __callStatic($foregroundColor, array $args)
    {
        if (!isset($args[0])) {
            throw new \InvalidArgumentException('Coloring string must be specified.');
        }
 
        $string        = $args[0];
        $coloredString = "";
 
        // Check if given foreground color found
        if (isset(static::$foregroundColors[$foregroundColor])) {
            $coloredString .= static::color(static::$foregroundColors[$foregroundColor]);
        } else {
            die($foregroundColor . ' not a valid color');
        }
 
        array_shift($args);
 
        foreach ($args as $option) {
            // Check if given background color found
            if (isset(static::$backgroundColors[$option])) {
                $coloredString .= static::color(static::$backgroundColors[$option]);
            } elseif (isset(self::$options[$option])) {
                $coloredString .= static::color(static::$options[$option]);
            }
        }
 
        // Add string and end coloring
        $coloredString .= $string . "\033[0m";
 
        return $coloredString;
    }
 
    public static function bell($count = 1)
    {
        echo str_repeat("\007", $count);
    }
 
    protected static function color($color)
    {
        return "\033[" . $color . "m";
    }
}
