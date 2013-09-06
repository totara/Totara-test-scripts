<?php
// Copy this file to settings.php and update with your own settings.

// common settings (same for all sites and dbs)
// if you want per-db settings for these you'll need to make some code changes
$settings = new stdClass();
$settings->dbhost = 'localhost';
$settings->dbuser = 'dbuser';
$settings->dbpass = 'dbpass';
$settings->prefix = 'mdl_';

$settings->adminuser = 'adminusername';
$settings->adminpass = 'adminpass';
$settings->adminemail = 'adminemail@example.com';
$settings->defaultcity = 'Wellington';
$settings->defaultcountry = 'NZ';

// per instance settings (the placeholder %%instance%% will be substituted with the
// name of the directory containing the code)
$settings->wwwroot = 'http://localhost/%%instance%%';
$settings->dataroot = '/path/to/moodledata/%%instance%%';
$settings->phpunit_dataroot = '/path/to/phpunitdata/%%instance%%';

// Backup directory path. Used by savedb/loaddb to for DB and data backups.
$backupdir = '/tmp/backup';
