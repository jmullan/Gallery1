#!/bin/sh
# $Id$
#
#note: requires xgettext version 0.12.1 or greater


##### CORE .pot ############
echo '# $Id$' > gallery-core.pot
cat copyright.txt >> gallery-core.pot

xgettext --files-from=filelist-core -LPHP --keyword=_ --no-wrap --msgid-bugs-address="gallery-translations@lists.sourceforge.net" -o - | tail +7 >> gallery-core.pot

##### CONFIG .pot
echo '# $Id$' > gallery-config.pot
cat copyright.txt >> gallery-config.pot

xgettext --files-from=filelist-config -LPHP --keyword=_ --no-wrap --msgid-bugs-address="gallery-translations@lists.sourceforge.net" -o - | tail +7 >> gallery-config.pot
