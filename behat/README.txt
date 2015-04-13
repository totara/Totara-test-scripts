BEHAT AND PERFORMANCE TESTING UTILITY
=====================================


INSTALLATION
------------

1. Copy behat/config-dist to behat/config file and set all variables to correct
   values

2. Add user 'behat' to system

3. To install required software run (ubuntu 14.04+ only):
./install-headless.sh

4. Create user and database for postgres or allow "postgres" user to login
   using login/pass via tcp

(Do steps required for resetdb to work)
5. Copy utils/settings-dist.php to utils/settings.php and edit values for
your system


BEHAT TESTING VISUAL
--------------------

To view/manage of what being happen during behat testing you need to run
vnc server on virtual display and forward port to testing server using
command (from your dev computer):

# ssh -L 5900:localhost:5900 behat@192.168.12.220 'x11vnc -localhost -display $DISPLAY'

Then run your vnc client using:
Server: localhost
Port: 5900

