#!/bin/bash
# $Id$

esc=`echo -en "\033"`
tab="${esc}[5G"
clear

if [ -z $1 ] || [ ! -z $1 ]  && [ -z $2 ] ; then
	echo -e "\nusage :"
	echo "sh update_po_files.sh -all for all .po file"
	echo -e "or sh update_po_files.sh -po <language_COUNTRY> for only one. e.g. sh update_po_files.sh -po de_DE\n" 
	exit
fi

if [ $1 != "-all" ] && [ ! -e "$2-gallery.po" ] ; then
	echo -e "\n$2-gallery.po does not exist or your paramater was wrong"
	echo -e "\nusage :"
	echo -e "sh update_po_files.sh -po <language_COUNTRY> for only one. e.g. sh update_po_files.sh -po de_DE\n" 
	exit
fi

ACTUALPATH=${0%/*}
cd $ACTUALPATH

#make sure the pot file is uptodate:

echo -n "making gallery.pot . . . "
sh create_po_template.sh

echo "done".
#find all .po files or use only one

if [ $1 = "-all" ] ; then
	echo -n "checking for .po files ...."
	ls ??_??-*.po >/dev/null 2>/dev/null || {
		echo $rc_failed	
		echo "$tab No valid .po files found"
		exit 0
	}

	for all_po in $(ls ??_*-*.po) ; do
		echo -e "\nFound : $all_po"
		
		lang=$(echo ${all_po%-*})
		echo "$tab Language = $lang"
		echo "$tab Updating ..."
		msgmerge -U $all_po gallery.pot --no-wrap -v || exit
	done
else
	msgmerge -U $2-gallery.po gallery.pot --no-wrap -v
fi
