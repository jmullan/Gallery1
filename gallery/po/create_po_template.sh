#!/bin/sh
# $Id$
#
#note: requires xgettext version 0.12.1 or greater

cat copyright.txt > gallery.pot

xgettext --files-from=filelist -LPHP --keyword=_ --no-wrap --msgid-bugs-address="gallery-translations@lists.sourceforge.net" -o - | tail +7 >> gallery.pot
