#!/bin/sh
# $Id$

set -e

# Note: These Gallery path are valid for 1.4.2

GALLERY_DEB_ROOT=/usr/share/gallery
GALLERY_DEB_CONFDIR=/etc/gallery
GALLERY_DEB_SETUPDIR=/var/lib/gallery/setup

#
# Set Pathes depending on Environment
#
if [ -d $GALLERY_DEB_ROOT ] && [ -d $GALLERY_DEB_CONFDIR ] ; then
	# It seems were in Debian, where Gallery has a different structure.
	if [ `whoami` != "root" ] ; then
		echo "You must be root to run this script" 2>&1
		exit 1
	fi

	GALLERY_ROOT=$GALLERY_DEB_ROOT
	GALLERY_CONFDIR=$GALLERY_DEB_CONFDIR
else
	# We are in a "normal installation"
	GALLERY_ROOT="."
	GALLERY_CONFDIR="."
	GALLERY_SETUPDIR="./setup"
fi	
	

# Set Permissions
for file in $GALLERY_CONFDIR/config.php $GALLERY_CONFDIR/htaccess ; do
	if [ ! -f $file ]; then
        	touch $file
	fi
    
	if [ -d $GALLERY_DEB_ROOT ] && [ -d $GALLERY_DEB_CONFDIR ] ; then
		chown www-data:root $file
	fi
	
	chmod 755 $file
done

chmod 755 $GALLERY_ROOT/setup


echo ""
echo "You are now in setup mode, which is *INSECURE*.  Your Gallery"
echo "installation can be configured by pointing your web browser"
echo "to the URL to 'setup' in this directory."
echo ""
echo "When you are done with your installation, don't forget to"
echo "run the secure.sh script!"
echo ""
echo "    # sh $GALLERY_ROOT/secure.sh"
echo ""
