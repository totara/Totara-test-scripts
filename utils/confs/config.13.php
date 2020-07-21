<?php  // Totara 13 configuration file

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
$CFG->wwwroot   = "%%wwwroot%%";
$CFG->dataroot  = '%%dataroot%%';
$CFG->admin     = 'admin';
$CFG->passwordsaltmain = '';

$CFG->directorypermissions = 0777;

$CFG->phpunit_prefix = 'tst_';
$CFG->phpunit_dataroot = '%%phpunit_dataroot%%';
$CFG->behat_prefix = 'bht_';
$CFG->behat_dataroot = '%%behat_dataroot%%';
$CFG->behat_wwwroot = '%%behat_wwwroot%%';
$CFG->behat_faildump_path = '/tmp';
$CFG->behat_config = array(
    'chrome' => array(
        'extensions' => array(
            'Behat\MinkExtension' => array(
                'selenium2' => array(
                    'browser' => 'chrome',
                    'wd_host' => 'http://127.0.0.1:4444/wd/hub',
                    'capabilities' => array(
                        'extra_capabilities' => array(
                            'chromeOptions' => array(
                                'args' => array(
                                    '--disable-infobars',
                                    '--disable-background-throttling'
                                ),
                                'prefs' => array(
                                    'credentials_enable_service' => false,
                                )
                            )
                        )
                    )
                )
            )
        )
    ),
    'firefox' => array(
        'extensions' => array(
            'Behat\MinkExtension\Extension' => array(
                'selenium2' => array(
                    'browser' => 'firefox',
                    'wd_host' => 'http://localhost:4444/wd/hub'
                )
            )
        )
    ),
);

// Metrics require MDL_PERF to be defined and path to store results.
//$CFG->behat_metrics_path = '%%behat_metrics_path%%';
//define('MDL_PERF', true);

$CFG->debug = E_ALL | E_STRICT;
$CFG->debugdisplay = 1;
$CFG->perfdebug = 15;

$CFG->cachejs = false;
$CFG->langstringcache = false;
$CFG->themedesignermode = true;
$CFG->tuidesignermode = true;
$CFG->allowthemechangeonurl = true;
$CFG->debugallowscheduledtaskoverride = true;
$CFG->forced_plugin_settings['totara_tui'] = ['cache_js' => false, 'cache_scss' => false, 'development_mode' => true];

$CFG->passwordpolicy = false;
$CFG->defaultcity = '%%defaultcity%%';
$CFG->country = '%%defaultcountry%%';

$CFG->sessiontimeout = 4*60*60;

// Some optional useful settings
//$CFG->noemailever = true;
//$CFG->divertallemailsto = 'simon.coggins@totaralms.com';

// Divert mail to mailcatcher
$CFG->smtphosts = 'localhost:1025';

$CFG->sitetype = 'development';

// Allow auto-login block to continue to work - dev only!
$CFG->allowlogincsrf = true;

// Keep session alive
$CFG->persistentloginenable = true;

// Focus to username field on login page
$CFG->loginpageautofocus = true;

// Add warning to distinguish development sites from production.
$CFG->additionalhtmltopofbody = '<div style="position:fixed;top:0;left:50%;width:200px;margin-left:-100px;height:35px;background:yellow;font-size: 16px;font-weight:bold;text-align:center;line-height:35px;z-index:10000;" onclick="this.style.display = \'none\'"><span class="flex-icon ft-fw ft fa-exclamation-triangle"></span>Development site</div>';

if (file_exists(__DIR__ . '/lib/setup.php')) {
    require_once(dirname(__FILE__) . '/lib/setup.php');
}

// GraphQL Dev support.
define('GRAPHQL_DEVELOPMENT_MODE', true);
//$CFG->cache_graphql_schema = false;

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!
