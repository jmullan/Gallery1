#!/usr/bin/perl -n
# $Id$
#
/^., fuzzy/ and $fuzzy = 1 and next;
/^$/ and $fuzzy = 0;
s/msgstr.*/msgstr ""/ if ($fuzzy);

print $_;

