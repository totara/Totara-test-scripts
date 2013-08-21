<?php
/**
 * 
 */

function run_lintchecker($file, $dirroot, $changedlinenumbers) {
    $results = array();
    
    $descriptorspec = array(
            0 => array("pipe", "r"),
            1 => array("pipe", "w"),
            2 => array("pipe", "w")
    );
    $process = proc_open('php -l ' . $file, $descriptorspec, $pipes);
    $streamcontent = stream_get_contents($pipes[2]);
    proc_close($process);

    $unparsedresults = explode("\n", $streamcontent);

    for ($i = 0; $i < sizeof($unparsedresults); $i++) {
        $unparsedresult = $unparsedresults[$i];
        if ($unparsedresult) {
            $result = new stdClass();
            //TODO parse $unparsedresult, should put "PHP Parse error: " -> 'class', "on line xx" -> 'line' => xx
            $result->message = $unparsedresult;
            $result->class = 'error';
            $result->checker = 'lintchecker';
            $results[] = $result;
        }
    }

    return $results;
}
