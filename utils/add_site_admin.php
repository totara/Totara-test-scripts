<?php
define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->libdir.'/clilib.php');      // cli only functions

// now get cli options
list($options, $unrecognized) = cli_get_params(array('help'=>false,'username'=>false),
                                               array('h'=>'help','u'=>'username'));

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help'] || $options['username'] === true) {
    $help =
"Add a user as a site administrator.

This script will add the user with the specified username to the list of site administrators.
Options:
-h, --help            Print out this help
-u, --username        Specify the username to add

Example:
php admin/cli/add_site_admin.php -u=admin
";

    echo $help;
    die;
}

$username = $options['username'];

$user = $DB->get_record('user', ['username' => $username, 'deleted' => 0, 'suspended' => 0], '*');

if ($user === false) {
    cli_error("An active user with the username '{$username}' could not be found.");
}

if (is_siteadmin($user)) {
    cli_error("The user '{$username}' is already a site admin.");
};

if (isguestuser($user)) {
    cli_error("Guest user cannot be made a site admin.");
}

cli_heading('Adding user');

$admins = array();
foreach (explode(',', $CFG->siteadmins) as $admin) {
    $admin = (int)$admin;
    if ($admin) {
        $admins[$admin] = $admin;
    }
}
$admins[$user->id] = $user->id;
set_config('siteadmins', implode(',', $admins));

mtrace("User '{$username}' has been added as a site administrator");


