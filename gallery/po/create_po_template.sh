#!/bin/sh
# $Id$

#note: requires xgettext version 0.12.1 or greater

xgettext --files-from=filelist -o gallery.pot -LPHP --keyword=_ --no-wrap

>> gallery.pot
