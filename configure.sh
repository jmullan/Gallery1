#!/bin/sh
chmod 755 setup

if [ ! -f config.php ]; then
    touch config.php
fi
if [ ! -f .htaccess ]; then
    touch .htaccess
fi
chmod 777 config.php .htaccess

cat <<EOF

You are now in setup mode, which is *INSECURE*.  Your Gallery
installation can be configured by pointing your web browser
to the URL to 'setup' in this directory.

When you are done with your installation, don't forget to
run the secure.sh script!

    % sh secure.sh

EOF
