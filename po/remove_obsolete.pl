#!/usr/bin/perl
# Perl script to clean up .po files in Gallery's po/ directory
# Don Willingham, for Tim_j in #gallery irc.freenode.net
# 2003-08-13 19:00EST
use strict;
use File::Find::Rule;
my @files = File::Find::Rule->file()->name('*.po')->in('../locale');
my $curr_file;
foreach $curr_file (@files)
{
  if ($curr_file =~ /(.{2}_.{2})(\..+)?\-gallery_.+\.po$/)
  {
    print "processing : $curr_file \n";
    my @lines;
    my $i=0;
    my $j=0;
    my $deleted = 0;
    my $input;
    my $output;
    open input, "<$curr_file";
    while (<input>)
    {
      my $tmp = $_;
      chomp $tmp;
#      if ($tmp =~ /^\#\~\ /) {
#      } else {
        $lines[$i++] = $tmp;
#      }
    }
    close input;
    # 
    for ($j = 0; $j < $i; $j++)
    {
      #if (($lines[$j] =~ /^#~\ msgid/) && ($lines[$j+1] =~ /^#~\ msgstr/))
      if (($lines[$j] =~ /^#~\ /) && ($lines[$j+1] =~ /^#~\ /))
      {
        my $k = $j;
        my $c = 0;
        while (($lines[$k-1] =~ /^#/) || ($lines[$k-1] =~ /^\ *$/))
#        while (($lines[$k-1] =~ /^\ *$/))
        {
          $c++;
          $k--;
        }
        for (;$k < $i - $c; $k++)
        {
          $lines[$k] = $lines[$k+2+$c];
        }
        $i = $i - 2 - $c;
        $j = $j - 2 - $c;
        $deleted += 2 + $c;
      }
    }
    # remove empty lines at the end
    while ($lines[$i-1] =~ /^\ *\r?\n?$/)
    {
      $i--;
    }
#    open output, ">$curr_file.tmp";
    open output, ">$curr_file";
    for ($j = 0; $j < $i; $j++)
    {
      print output $lines[$j]."\n";
    }
    close output;
    print "deleted : $deleted obsolete lines \n";
    print "--------------------------------- \n";
#to test the results    my $cmdline = "tkdiffb $curr_file $curr_file".".tmp";
#    my $results = `$cmdline`;
    
  }
}
