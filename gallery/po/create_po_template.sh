xgettext --files-from=filelist -o gallery.pot -C --keyword=_ --no-wrap

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