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
	GALLERY_SETUPDIR="./setup"
fi	


# Set Permissions
for file in $GALLERY_CONFDIR/config.php $GALLERY_CONFDIR/htaccess ; do
	if [ -s $file ] ; then
		if [ -d $GALLERY_DEB_ROOT ] && [ -d $GALLERY_DEB_CONFDIR ] ; then
        		chown root:root $file
		else
			chmod 644 $file
		fi
	else
        	# the file is 0 bytes, remove it as it didn't get written out
		rm -f $file
	fi
done

chmod 0 $GALLERY_ROOT/setup

echo ""
echo "Your Gallery is now secure and cannot be configured.  If"
echo "you wish to reconfigure it, run:"
echo ""
echo "    % sh $GALLERY_ROOT/configure.sh"
echo ""
