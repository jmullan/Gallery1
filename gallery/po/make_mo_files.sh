#!/bin/bash
# $Id$

#    \033          ascii ESCape
#    \033[<NUM>G   move to column <NUM> (linux console, xterm, not vt100)
#    \033[<NUM>C   move <NUM> columns forward but only upto last column
#    \033[<NUM>D   move <NUM> columns backward but only upto first column
#    \033[<NUM>A   move <NUM> rows up
#    \033[<NUM>B   move <NUM> rows down
#    \033[1m       switch on bold
#    \033[31m      switch on red
#    \033[32m      switch on green
#    \033[33m      switch on yellow
#    \033[m        switch on color/bold
#    \017          exit alternate mode (xterm, vt100, linux console)
#    \033[10m      exit alternate mode (linux console)
#    \015          carriage return (without newline)
#

if test -z "$LINES" -o -z "$COLUMNS" ; then
    eval `stty size 2>/dev/null | (read L C; \
	  echo LINES=${L:-24} COLUMNS=${C:-40})`
fi
test $LINES   -eq 0 && LINES=24
test $COLUMNS -eq 0 && COLUMNS=40

	esc=`echo -en "\033"`
	extd="${esc}[1m"
	warn="${esc}[1;31m"
	done="${esc}[1;32m"
	attn="${esc}[1;33m"
	norm=`echo -en "${esc}[m\017"`
	stat=`echo -en "\015${esc}[${COLUMNS}C${esc}[10D"`
	tab="${esc}[5G"

rc_done="${stat}${done}done${norm}"
rc_ok="${stat}${done}OK${norm}"
rc_failed="${stat}${warn}failed${norm}"
rc_missing="${stat}${warn}missing${norm}"
rc_skipped="${stat}${attn}skipped${norm}"
rc_dead="${stat}${warn}dead${norm}"
rc_unused="${stat}${extd}unused${norm}"

################################

clear

if [ -z $1 ] ; then
        echo -e "\nusage :"
        echo "sh make_mo_files.sh -all for all .po file"
        echo -e "or sh make_mo_files.sh -po <language_COUNTRY> for only one. e.g. sh make_mo_files.sh -po de_DE \n"
        exit
fi

if [ $1 != "-all" ] && [ ! -e ../locale/$2 ] ; then
        echo -e "\n$2-gallery.po does not exist or your paramater was wrong"
        echo -e "\nusage :"
        echo -e "sh make_mo_files.sh -<language_COUNTRY> for only one. e.g. sh update_po_files.sh -po de_DE \n"
        exit
fi


ACTUALPATH=${0%/*}
cd $ACTUALPATH

# check if ../locale dir is there

echo -n "Checking ../locale"
if [ ! -e ../locale ] ; then 
	echo $rc_missing
	exit 1
else
	echo $rc_ok
fi

if [ $1 = "-all" ] ; then
#find all .po files
	echo -n "checking for .po files ...."
	find ../locale -iname ??_??-*.po >/dev/null 2>/dev/null || {
		echo $rc_failed	
		echo "$tab No valid .po files found"
		exit 0
	}
	all_po=$(find ../locale -iname "??_*-*.po")
else
#just use the one the user gave as parameter
	echo "only $2"
	all_po=$(find ../locale/$2 -iname "??_*-*.po")
fi

echo $2
for po_file in $all_po ; do
	echo 
	echo "Found : $po_file"

	version=$(head $po_file -n3 | tail -n1 | cut -d " " -f3)
	echo "$tab Version: $version"

	stripped=${po_file##*/}
#	echo "Stripped: $stripped"
 	
	lang=$(echo ${stripped%-*})

	module1=${stripped##*_}
	module=${module1/.po}
	filename=$lang-gallery_$module.mo

	echo "$tab Language = $lang"
	echo "$tab Module = $module"

	echo -n "$tab Checking if ../locale/$lang/LC_MESSAGES exists"
	if [ -e ../locale/$lang/LC_MESSAGES ] ; then
		echo $rc_ok
		echo -n "$tab Making $lang/LC_MESSAGES/$filename file"
			msgfmt --check $po_file --output-file=../locale/$lang/LC_MESSAGES/$filename && {
				echo $rc_ok
			} || {
				echo $rc_failed
				exit 1
			}
	else
		echo $rc_missing
		echo -n " $tab Creating ../locale/$lang/LC_MESSAGES"
		mkdir -p ../locale/$lang/LC_MESSAGES && {
			echo $rc_ok
			echo -n "$tab Making ../locale/$lang/LC_MESSAGES/$filename file"
			msgfmt --check $po_file --output-file=../locale/$lang/LC_MESSAGES/$filename && {
				echo $rc_ok
			} || {
				echo $rc_failed
				exit 1
			}
		} || {
			echo $rc_failed
			echo -n "$tab Making ../locale/$lang/LC_MESSAGES/$filename file"
			echo $rc_skipped
		}
	fi

#read trash
done

# /etc/init.d/apache restart
