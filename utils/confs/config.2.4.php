<?php  // Totara 2.4 configuration file

unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = '%%dbtype%%';
$CFG->dblibrary = '%%dblibrary%%';
$CFG->dbhost    = '%%dbhost%%';
$CFG->dbname    = '%%dbname%%';
$CFG->dbuser    = '%%dbuser%%';
$CFG->dbpass    = '%%dbpass%%';
$CFG->prefix    = '%%prefix%%';
$CFG->dboptions = array (
  'dbpersist' => 0,
  'dbsocket' => 0,
);

$port = '';
if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != '80') {
    $port = ':'.$_SERVER['SERVER_PORT'];
}

$CFG->dirroot   = '%%dirroot%%';
$CFG->wwwroot   = '%%wwwroot%%';
$CFG->dataroot  = '%%dataroot%%';
$CFG->admin     = 'admin';
$CFG->passwordsaltmain = '';

$CFG->directorypermissions = 0777;

$CFG->unittestprefix = 'unit_';
$CFG->phpunit_prefix = 'tst_';
$CFG->phpunit_dataroot = '%%phpunit_dataroot%%';

$CFG->debug = E_ALL | E_STRICT;
$CFG->debugdisplay = 1;
$CFG->perfdebug = 15;

$CFG->cachejs = false;
$CFG->langstringcache = false;
$CFG->themedesignermode = true;
$CFG->allowthemechangeonurl = true;

$CFG->passwordpolicy = false;
$CFG->defaultcity = '%%defaultcity%%';
$CFG->country = '%%defaultcountry%%';

$CFG->sitetype = 'development';

require_once(dirname(__FILE__) . '/lib/setup.php');

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!
