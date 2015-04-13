#!/bin/bash
#set -e

if [ ! -f config ]; then
  echo "Cannot find config file"
  exit 1
fi

source ./config

if [ -z "$TOOLSBEHATPATH" ]; then
  echo "Configuration variables are not set"
  exit 1
fi

if [ -e $TOOLSBEHATPATH/vendor/composer.phar ]; then
  echo 'Already installed.'
  exit
fi

echo ''
echo 'INSTALLING'
echo '----------'

# Add Google public key to apt
wget -q -O - "https://dl-ssl.google.com/linux/linux_signing_key.pub" | sudo apt-key add -

# Add Google to the apt-get source list
#echo 'deb http://dl.google.com/linux/chrome/deb/ stable main' >> /etc/apt/sources.list
cp $TOOLSBEHATPATH/extra/google-chrome.list /etc/apt/sources.list.d/

apt-get update

# Install Chrome and firefox
apt-get -y install google-chrome-stable firefox

# Install Java, Xvfb, unzip, and imagemagick
apt-get -y install openjdk-7-jre xvfb x11vnc ratpoison xterm unzip imagemagick

# Setup display variable for xvfb and apps
cp displayenv.sh /etc/profile.d/

# Install php and apache
apt-get -y install apache2 php5 php5-pgsql php5-curl php5-gd postgresql

cd $TOOLSBEHATPATH/vendor/
# Install Selenium Server
echo "Installing Selenium Version 2.45.0. For newer versions visit: http://seleniumhq.org/download/"
wget http://selenium-release.storage.googleapis.com/2.45/selenium-server-standalone-2.45.0.jar

# Install chrome driver
echo "Installing Chrome Driver for selenium v2.14"
wget http://chromedriver.storage.googleapis.com/2.14/chromedriver_linux64.zip
unzip chromedriver_linux64.zip -d /usr/local/bin

# Install composer
cp composer.phar /usr/local/bin

# Totara Site
chown -R behat /var/www/html
rm /var/www/html/*
cd /var/www/html/
sudo -u behat git clone ssh://review.totaralms.com:29418/totara.git .
cd $TOOLSBEHAT

# metrics storage
mkdir /srv/metrics
mkdir /srv/metrics/current
mkdir /srv/metrics/base

mkdir /srv/data/html
mkdir /srv/data/phpunit_html
mkdir /srv/data/behat_html

chown -R behat /srv/metrics
chmod -R a+w /srv/data

cd /srv/metrics/base
sudo -u behat git init

/etc/profile.d/displayenv.sh