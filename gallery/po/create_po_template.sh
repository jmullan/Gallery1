#!/bin/sh
# $Id$

#note: requires xgettext version 0.12.1 or greater

xgettext --files-from=filelist -o gallery.pot -LPHP --keyword=_ --no-wrap

echo '

# -------------------------------------------

msgid "Transition: "
msgstr ""

# Neccessary infact of hardcoded function editField() in util.php
msgid "title"
msgstr ""

# Neccessary infact of hardcoded function editField() in util.php
msgid "description"
msgstr ""

# Neccessary infact of hardcoded function editField() in util.php
msgid "summary"
msgstr ""

# -------------------------------------------
' >> gallery.pot
