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

# Install Java, Chrome, Xvfb, and unzip
apt-get -y install openjdk-7-jre google-chrome-stable xvfb unzip

# Install php and apache
apt-get -y install apache2 php5 postgresql

cd $TOOLSBEHATPATH/vendor/
# Install Selenium Server
echo "Installing Selenium Version 2.45.0. For newer versions visit: http://seleniumhq.org/download/"
wget http://selenium-release.storage.googleapis.com/2.45/selenium-server-standalone-2.45.0.jar

# Install chrome driver
echo "Installing Chrome Driver for selenium v2.14"
wget http://chromedriver.storage.googleapis.com/2.14/chromedriver_linux64.zip

# Install composer
curl http://getcomposer.org/installer | php
  
cd $TOOLSBEHAT
