#!/bin/bash
# $Id$

esc=`echo -en "\033"`
tab="${esc}[5G"

clear

ACTUALPATH=${0%/*}
cd $ACTUALPATH

#make sure the pot file is uptodate:

echo -n "making gallery.pot . . . "
sh create_po_template.sh

echo "done".
#find all .po files
echo -n "checking for .po files ...."
ls ??_??-*.po >/dev/null 2>/dev/null || {
	echo $rc_failed	
	echo "$tab No valid .po files found"
	exit 0
}

for all_po in $(ls ??_*-*.po) ; do
	echo 
	echo "Found : $all_po"
	
	lang=$(echo ${all_po%-*})
	echo "$tab Language = $lang"
	echo "$tab Updating ..."
	msgmerge -U $all_po gallery.pot --no-wrap -v
done
