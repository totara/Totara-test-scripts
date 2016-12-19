Totara test scripts README
==========================

This repository provides some useful utilities to help with Moodle/Totara
development.

EXISTING UTILITIES
==================

codecheck - command line version of moodle's code sniffer module with some
            totara additions
wwwroot - looks up the directory tree to find a moodle/totara "code"
          folder, returning the path
version - uses wwwroot to find the version.php file then parses out version
          information
setconf - generates a new config.php file for the current code directory,
          based on version data and user input
linkchecker.php - Spiders a site, following internal links it finds and
          records errors it comes across
resetdb - do a fresh install based on the current code
savedb  - backup the database and dataroot for the current site to a named backup
loaddb  - restore the database and dataroot for the current site from a named backup
listdb  - list all available backups for the current site's database type
startdb - load a database then upgrade to latest code from it. If no db given, this
          command tries to load the best option from a named backup matching the
          pattern 'startdb[versionnumber]' or 'startdb'.

INSTALL
=======

1. Clone the repository:

cd somedirectory
git clone git@github.com:totara/Totara-test-scripts.git

2. Add bin directory to your PATH shell variable in ~/.bashrc, ~/.zshrc
   or similar:

PATH=$PATH:path/to/repository/bin

3. Reload your shell config file, e.g.:

source ~/.bashrc

4. You should be able to start using the scripts in bin, e.g.:

setconf --help


To use resetdb, savedb or loaddb you need to complete some additional steps:

5. Copy utils/settings-dist.php to utils/settings.php and edit values for
   your system

6. (Optional) Make any changes to the config or cli templates in
   utils/confs/config.*.php and utils/cli/cli.*.php

See utils/confs/README.txt and utils/cli/README.txt for details


Pull the repository to update to newer versions. Use a branch for making
local modifications (share if they are good!).

