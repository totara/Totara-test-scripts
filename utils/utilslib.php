<?php

require_once(__DIR__ . '/dblib.php');

/**
 * Recursively look for main moodle config file
 * Returning the first directory below the current one
 * that contains it (or false if none found).
 *
 * @param $directory string Directory to start in (current directory if not
 *                          specified).
 * @return string The path to the directory containing the config
 * @throws Exception if webroot found.
 */
function get_web_root($directory = null) {
    if (is_null($directory)) {
        $directory = getcwd();
    }

    if (file_exists($directory.'/config.php')) {
        $configfile = file_get_contents($directory.'/config.php');
        // check for some common contents
        $patterns = array(
            '/\$CFG\s=\snew/',
            '/Moodle configuration file/',
            '/\$CFG\->wwwroot\s*=/',
            '/dbhost\s*=/',
            '/dbname\s*=/',
            '/dbuser\s*=/',
            '/dbpass\s*=/',
            '/\$CFG\->prefix\s*=/',
            '|/lib/setup\.php|',
            '/There is no php closing tag in this file/',
        );

        $matches = 0;
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $configfile)) {
                $matches++;
            }
        }
        if (($matches / count($patterns) >= 0.5)) {
            // assume this is a moodle main config file
            return $directory;
        }
    }

    // No main config, let's look for some other common files/directories
    $dirs = array('admin', 'blocks', 'calendar', 'course', 'install', 'lang', 'lib', 'local', 'login', 'mod', 'theme', 'user', '.git');
    $files = array('config-dist.php', 'README.txt', 'TRADEMARK.txt', 'version.php', 'index.php');
    $matches = 0;
    foreach ($dirs as $dir) {
        if (is_dir($directory.'/'.$dir)) {
            $matches++;
        }
    }
    foreach ($files as $file) {
        if (file_exists($directory.'/'.$file)) {
            $matches++;
        }
    }

    if ($matches / (count($dirs) + count($files)) >= 0.75) {
        // assume this is the main moodle folder but with no config
        return $directory;
    }

    if (dirname($directory) == $directory) {
        // we've reached the top level directory
        throw new Exception('Could not find Moodle root directory');
    } else {
        // try parent directory
        return get_web_root(dirname($directory));
    }
}

/**
 * Looks for a site within the given directory (default to current dir). If
 * found returns an object containing site version info, or false if none
 * could be found.
 *
 * The object is of the form:
 *
 * stdClass Object(
 *    [totara] => stdClass Object(
 *        [version] => 2.4.0a
 *        [build] => 20121126.00
 *        [release] => 2.4.0a (Build: 20121126.00)
 *    )
 *
 *    [moodle] => stdClass Object(
 *        [version] => 2.4.3
 *        [build] => (Build: 20130318)
 *        [release] => 2.4.3 (Build: 20130318)
 *    )
 * )
 *
 * The 'totara' property is not defined if the site is a moodle site.
 *
 * @param string $directory Path to the site (optional)
 * @return object The version info object
 */
function get_site_version($directory = null) {
    $rootdir = get_web_root($directory);
    $versionfile = $rootdir . '/version.php';
    if (!is_readable($versionfile)) {
        // no version.php to get version from
        throw new Exception("Cannot get site version - '{$versionfile}' is missing or unreadable");
    }
    if (!defined('MOODLE_INTERNAL')) {
        define('MOODLE_INTERNAL', 1);
    }
    // required for MATURITY constants
    require_once($rootdir.'/lib/setuplib.php');
    require($versionfile);

    $versions = new stdClass();
    if (isset($TOTARA)) {
        $versions->totara = $TOTARA;
    }
    if (isset($version)) {
        $versions->moodle->version = $version;
    }
    if (isset($release)) {
        $versions->moodle->release = $release;
        $releaseinfo = explode(" ", $release, 2);
        $versions->moodle->version = $releaseinfo[0];
        $versions->moodle->build = $releaseinfo[1];
    }
    return $versions;
}

/**
 * Given a moodle/totara site version, find the most appropriate config file
 * from the confs/ directory. Most appropriate in this context means the
 * highest version file which is still below or equal to the site's version
 *
 * @param string $version Moodle or Totara version number e.g. 2.4.3
 * @return string The contents of the best config file to use
 */
function get_best_config($version, $type='config') {

    if ($type == 'config') {
        $sourcedir = '/confs/';
        $fileregex = '/config\.(.+)\.php/';
    } else {
        $sourcedir = '/cli/';
        $fileregex = '/cli\.(.+)\.php/';
    }
    $confdir = __DIR__ . $sourcedir;
    // sort backwards to start from the highest version number
    $files = scandir($confdir, 1);
    if (empty($files)) {
        throw new Exception("No config file templates found in '{$confdir}'");
        // no config files found
        return false;
    }
    foreach ($files as $file) {
        if (!preg_match($fileregex, $file, $matches)) {
            // doesn't match format
            continue;
        }
        $fileversion = $matches[1];
        if (version_compare($version, $fileversion) >= 0) {
            // return the first version that's equal to or below the specified version
            return file_get_contents($confdir . $file);
        }
    }
    throw new Exception("No config file template old enough for version '{$version}'");
}

/**
 * Given a config template file and an object containing variables, substitute the
 * placeholders and return the completed config.php file.
 *
 * @param string $template Config template as a string (with placeholders)
 * @param object $vars Object containing placeholder values (as object properties)
 * @return string Final config with placeholders substituted
 */
function substitute_template_config($template, $vars) {
    $find = $replace = array();
    foreach ($vars as $prop => $value) {
        $find[] = '%%' . $prop . '%%';
        $replace[] = $value;
    }

    return str_replace($find, $replace, $template);

}

/**
 * Load the settings, substituting the instance when
 * necessary
 *
 * @param string $instance Name of the code instance we are using (normally the directory name)
 * @return object
 */
function get_instance_settings($instance) {
    $settingspath = __DIR__.'/settings.php';
    if (!is_readable($settingspath)) {
        throw new Exception("Settings file '{$settingspath}' not found. You need to create this file by copying from settings-dist.php and updating for your system.");
    }
    require($settingspath);
    if (!isset($settings)) {
        throw new Exception("\$settings not defined");
    }

    if (!isset($settings->adminuser)) {
        $settings->adminuser = 'admin';
    }

    if (!isset($settings->adminpass)) {
        $settings->adminpass = 'pw';
    }

    if ($settings->adminpass == 'admin') {
        echo "Unfortunately you can't use 'admin' as the admin password - using 'pw' instead";
        $settings->adminpass = 'pw';
    }

    if (!isset($settings->adminemail)) {
        $settings->adminemail = 'admin@example.com';
    }

    if (!isset($settings->defaultcity)) {
        $settings->defaultcity = 'Wellington';
    }

    if (!isset($settings->defaultcountry)) {
        $settings->defaultcountry = 'NZ';
    }

    foreach ($settings as $property => $value) {
        $settings->$property = str_replace('%%instance%%', $instance, $value);
    }

    return $settings;
}

/**
 * Given a database type, convert to the common type description
 * required by moodle, e.g. pgsql, mysql or mssql_n
 */
function normalise_dbtype($dbtype) {
    switch($dbtype) {
        case 'postgres7':
        case 'pgsql':
        case 'postgres':
            return 'pgsql';
            break;
        case 'mysql':
        case 'mysqli':
            return 'mysql';
            break;
        case 'mssql':
        case 'mssql_n':
        case 'sqlsrv':
            return 'mssql_n';
            break;
        default:
            throw new Exception("Unknown database type '{$dbtype}'");
    }
}

/**
 * Given a database type and moodle/totara version number
 * return an object containing the appropriate database settings
 * for that version and type.
 *
 * @param string $dbtype Type of database to connect to.
 * @param string $dbname Name of database to connect to.
 * @param string $version Version of the site.
 * @return object Object containing database settings.
 */
function get_database_settings($dbtype, $dbname, $version) {
    $settings = new stdClass();
    $settings->dbname = $dbname;
    $settings->dbtype = normalise_dbtype($dbtype);

    // now customise based on release version
    if (version_compare($version, '2.0') < 0) {
        // version is before 2.0
        // postgres driver used to be called 'postgres7'
        $settings->dbtype = str_replace('pgsql', 'postgres7', $settings->dbtype);
    } else {
        // version is after 2.0
        $settings->dblibrary = 'native';
        // mysql driver now called mysqli
        $settings->dbtype = str_replace('mysql', 'mysqli', $settings->dbtype);
    }

    return $settings;
}

/**
 * Merge two objects by creating an object containing the properties of both.
 * If the same property exists in both objects, the version from $object2 is retained
 *
 * @param object $object1 The first object
 * @param object $object2 The second object
 * @return object The merged object to return
 */
function merge_objects($object1, $object2) {
    return (object) array_merge((array) $object1, (array) $object2);
}

/*
 * Returns the $CFG object that would be generated by the config.php
 * file located at $configfile.
 *
 * @param string $configfile Path to a config.php file.
 * @return object $CFG object generated by evaluating $configfile.
 */
function get_settings_from_config($configfile) {
    $config_str = '';
    $config_array = file($configfile);
    foreach ($config_array as $line) {
        // Skip the PHP tags since we're going to include this.
        if (strpos($line, '<?php') !== false) {
            continue;
        }
        // Don't include setuplib, we only care about CFG
        if (strpos($line, 'require_once') !== false) {
            continue;
        }
        // No need for CFG to be global
        if (strpos($line, 'global $CFG;') !== false) {
            continue;
        }
        $config_str .= $line;
    }
    eval($config_str);

    return $CFG;

}

/**
 * Recursively delete contents of a directory
 *
 * @param string $dirpath Path to the directory to empty
 */
function delete_directory_contents($dirpath) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(
            $dirpath,
            FilesystemIterator::SKIP_DOTS
        ),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($files as $path) {
            $path->isFile() ? unlink($path->getPathname()) : rmdir($path->getPathname());
    }
}

/**
 * Initialise a CLI install using the settings provided
 *
 * @param object Object containing settings
 */
function cli_install($dbtype, $dbname, $dirroot) {
    // confirm this is a site we can install
    try {
        $root = get_web_root($dirroot);
    } catch (Exception $e) {
        throw new Exception("Path '{$dirroot}' does not appear to contain a moodle/totara site");
    }
    if ($root != $dirroot) {
        throw new Exception("Path '{$dirroot}' does not appear to be the wwwroot folder of this site");
    }

    $clicommand = get_cli_install_command($dirroot, $dbtype, $dbname);

    chdir($dirroot);
    // run the command printing output in real-time
    $handle = popen($clicommand, 'r');
    while (!feof($handle)) {
        echo fread($handle, 2096);
    }
    pclose($handle);
}

/**
 * Returns the config file contents for a given installation and database
 *
 * @param string $dirroot Path to code directory
 * @param string $dbtype  Type of database to install
 * @param string $dbname  Name of database to install
 * @return string Contents of the config.php file to use with the site
 */
function get_config_file($dirroot, $dbtype, $dbname) {
    $versioninfo = get_site_version($dirroot);
    $version = isset($versioninfo->totara->version) ?
        $versioninfo->totara->version : $versioninfo->moodle->version;
    $instance = basename($dirroot);
    $settings = get_instance_settings($instance);
    $settings->dirroot = $dirroot;
    $configtemplate = get_best_config($version);
    $dbsettings = get_database_settings($dbtype, $dbname, $version);
    $settings = merge_objects($settings, $dbsettings);
    $config = substitute_template_config($configtemplate, $settings);
    return $config;
}

/**
 * Returns the CLI install command for a given installation and database
 *
 * @param string $dirroot Path to code directory
 * @param string $dbtype  Type of database to install
 * @param string $dbname  Name of database to install
 * @return string CLI command to install the site
 */
function get_cli_install_command($dirroot, $dbtype, $dbname) {
    $versioninfo = get_site_version($dirroot);
    $version = isset($versioninfo->totara->version) ?
        $versioninfo->totara->version : $versioninfo->moodle->version;
    $instance = basename($dirroot);
    $settings = get_instance_settings($instance);
    $settings->dirroot = $dirroot;

    $clitemplate = get_best_config($version, 'cli');
    $dbsettings = get_database_settings($dbtype, $dbname, $version);
    $settings = merge_objects($settings, $dbsettings);
    $clicommand = substitute_template_config($clitemplate, $settings);
    return $clicommand;
}

