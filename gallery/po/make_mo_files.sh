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

ACTUALPATH=${0%/*}
cd $ACTUALPATH

# check if ../locale dir is there, if not create

echo -n "Checking ../locale"
if [ ! -e ../locale ] ; then 
	echo $rc_missing
	echo -n "$tab Creating ../locale folder"
	
	mkdir ../locale && { 
		echo $rc_ok 
	} || {
		echo $rc_failed
		exit 0
	}
else
	echo $rc_ok
fi


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
	t=${all_po%%.*}
	filename=gallery.mo
	echo "$tab Language = $lang"

	echo -n "$tab Checking if ../locale/$lang/LC_MESSAGES exists"
	if [ -e ../locale/$lang/LC_MESSAGES ] ; then
		echo $rc_ok
		echo -n "$tab Making ../locale/$lang/LC_MESSAGES/$filename file"
			msgfmt $all_po --output-file=../locale/$lang/LC_MESSAGES/$filename && {
				echo $rc_ok
			} || {
				echo $rc_failed
				exit 1
			}
		echo -n "$tab cp $all_po ../locale/$lang/gallery.po"
		cp $all_po ../locale/$lang/gallery.po && echo $rc_ok || echo $rc_failed
	else
		echo $rc_missing
		echo -n " $tab Creating ../locale/$lang/LC_MESSAGES"
		mkdir -p ../locale/$lang/LC_MESSAGES && {
			echo $rc_ok
			echo -n "$tab Making ../locale/$lang/LC_MESSAGES/$filename file"
			msgfmt $all_po --output-file=../locale/$lang/LC_MESSAGES/$filename && {
				echo $rc_ok
			} || {
				echo $rc_failed
				exit 1
			}
			echo -n "$tab cp $all_po ../locale/$lang/gallery.po"
			cp $all_po ../locale/$lang/gallery.po && echo $rc_ok || echo $rc_failed
		} || {
			echo $rc_failed
			echo -n "$tab Making ../locale/$lang/LC_MESSAGES/$filename file"
			echo $rc_skipped
		}
	fi

done

# /etc/init.d/apache restart
