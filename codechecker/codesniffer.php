<?php
/**
 * Runs the moodle code sniffer
 */

require_once(__DIR__ . '/CodeSniffer/pear/PHP/CodeSniffer.php');
require_once(__DIR__ . '/codecheckerlib.php');
//require_once(__DIR__ . '/config.php');

function run_codesniffer($file, $dirroot, $changedlinenumbers) {
    $results = array();
    
    $out = codesniff_file($file, $dirroot);
    $testresults = explode("\n", trim($out));

    foreach ($testresults as $line) {
        $testresult = str_getcsv($line);
        if (!empty($changedlinenumbers[$testresult[1]])) {
            $result = new stdClass();
            $result->file = $file;
            $result->line = $testresult[1];
            $result->column = $testresult[2];
            $result->class = $testresult[3];
            $result->message = $testresult[4];
            $result->checker = 'codesniffer';
            $results[] = $result;
        }
    }
    
    return $results;
}

class local_codechecker_codesniffer_cli extends PHP_CodeSniffer_CLI {
    /** Constructor */
    public function __construct() {
        $this->errorSeverity = 1;
        $this->warningSeverity = 1;
    }
    public function getCommandLineValues() {
        return array('showProgress' => false);
    }
}

function codesniff_file($path, $dirroot) {
    //raise_memory_limit(MEMORY_HUGE);

    // for some reason this function changes working directory
    // save it to restore later (TODO figure out why)
    $cwd = getcwd();
    ob_start();
    
    $standard = __DIR__ . '/CodeSniffer/moodle';
    $phpcs = new PHP_CodeSniffer(0);
    $phpcs->setCli(new local_codechecker_codesniffer_cli());
    $phpcs->setIgnorePatterns(local_codechecker_get_ignores($dirroot));
    $numerrors = $phpcs->process(local_codechecker_clean_path($dirroot . '/' . trim($path, '/')),
            local_codechecker_clean_path($standard));
    $reporting = new PHP_CodeSniffer_Reporting();
    $problems = $phpcs->getFilesErrors();
    $reporting->printReport('csv', $problems, false, null);

    $out = ob_get_clean();
    // restore old working directory
    chdir($cwd);
    return $out;
}
?>
