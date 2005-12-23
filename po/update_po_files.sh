#!/bin/bash
# $Id$

esc=`echo -en "\033"`
tab="${esc}[5G"
clear

if [ -z $1 ] ; then
	echo -e "\nusage :"
	echo "sh update_po_files.sh -all for all .po file"
	echo -e "or sh update_po_files.sh -po <language_COUNTRY> for only one. e.g. sh update_po_files.sh -po de_DE \n" 
	exit
fi

if [ $1 != "-all" ] && [ ! -e ../locale/$2 ]; then
	echo -e "\n$2 does not exist or your paramater was wrong"
	echo -e "\nusage :"
	echo -e "sh update_po_files.sh -po <language_COUNTRY> for only one. e.g. sh update_po_files.sh -po de_DE \n" 
	exit
fi

ACTUALPATH=${0%/*}
cd $ACTUALPATH

#make sure the pot file is uptodate:

echo -n "making *.pot . . . "
sh create_po_template.sh
echo -e "Done.\n"

#find all .po files or use only one

echo -n "checking for .po files ...."
find ../locale/ -iname "??_??*.po" >/dev/null 2>/dev/null || {
	echo $rc_failed	
	echo "$tab No valid .po files found"
	exit 0
}

if [ $1 = "-all" ] ; then
	pofiles=$(find ../locale/ -iname "??_??*.po")
else
	pofiles=$(find ../locale/$2 -iname "??_??*.po")
fi

for all_po in $pofiles ; do
  echo -e "\nFound : $all_po"
		
  lang1=${all_po%-*}
  lang=${lang1##*/}
  
  if [ $lang != "en_US" ] ; then
	
	module1=${all_po##*_}
	module=${module1/.po}

	echo "$tab Language = $lang"
	echo "$tab Module = $module"

	echo "$tab Updating ..."
	#msgmerge ../locale/$lang/compendium.po gallery-$module.pot -v -o $all_po --no-wrap || exit
	msgmerge -v -U $all_po gallery-$module.pot --no-wrap || exit
  fi
done

find ../locale/ -iname "*~" -exec rm {} \;