#!/bin/sh
chmod 755 setup

if [ ! -f config.php ]; then
    touch config.php
fi
if [ ! -f .htaccess ]; then
    touch .htaccess
fi
chmod 666 config.php .htaccess

echo ""
echo "You are now in setup mode, which is *INSECURE*.  Your Gallery"
echo "installation can be configured by pointing your web browser"
echo "to the URL to 'setup' in this directory."
echo ""
echo "When you are done with your installation, don't forget to"
echo "run the secure.sh script!"
echo ""
echo "    % sh secure.sh"
echo ""
