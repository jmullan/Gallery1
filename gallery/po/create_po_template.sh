#!/bin/sh
# $Id$

#note: requires xgettext version 0.12.1 or greater

echo '# $Id$' > gallery.pot

xgettext --files-from=filelist -LPHP --keyword=_ --no-wrap -o - >> gallery.pot
