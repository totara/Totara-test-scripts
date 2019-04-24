<?php  // Totara 1.1 configuration file

unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = '%%dbtype%%';
$CFG->dbhost    = '%%dbhost%%';
$CFG->dbname    = '%%dbname%%';
$CFG->dbuser    = '%%dbuser%%';
$CFG->dbpass    = '%%dbpass%%';
$CFG->prefix    = '%%prefix%%';
$CFG->dboptions = array (
  'dbpersist' => 0,
  'dbsocket' => 0,
);

$CFG->dirroot   = '%%dirroot%%';
$CFG->wwwroot   = '%%wwwroot%%';
$CFG->dataroot  = '%%dataroot%%';
$CFG->admin     = 'admin';
$CFG->passwordsaltmain = '';

$CFG->directorypermissions = 0777;

$CFG->unittestprefix = 'unit_';

$CFG->debug = 38911;
$CFG->debugdisplay = 1;
$CFG->perfdebug = 15;

$CFG->passwordpolicy = false;
$CFG->defaultcity = '%%defaultcity%%';
$CFG->country = '%%defaultcountry%%';

$CFG->sitetype = 'development';

require_once(dirname(__FILE__) . '/lib/setup.php');

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!
