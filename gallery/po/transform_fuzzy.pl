#!/usr/bin/perl -n
#
/^., fuzzy/ and $fuzzy = 1 and next;
/^$/ and $fuzzy = 0;
s/msgstr.*/msgstr ""/ if ($fuzzy);

print $_;

