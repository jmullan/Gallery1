#!/bin/sh
chmod 0 setup

if [ -f config.php ]; then
    chmod 644 config.php 
fi

if [ -f .htaccess ]; then
    chmod 644 .htaccess
fi

cat <<EOF

Your application is now secure and cannot be configured.  If
you wish to reconfigure it, run:

    % sh configure.sh

EOF
