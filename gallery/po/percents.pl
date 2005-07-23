#!/usr/bin/perl
# Don Willingham
use strict;

sub markFuzzy
{
  my ($msgid, $msgstr) = @_;
  $msgid =~ s/^\ *\"//;
  $msgid =~ s/\"\ *$//;
  $msgstr =~ s/^\ *\"//;
  $msgstr =~ s/\"\ *$//;
  if ($msgstr eq "")
  {
    return (0);
  }
#print "msgid = $msgid\n";
#print "msgstr = $msgstr\n";
  while ($msgid =~ s/^.*(\%[sSdD])//)
  {
    my $first = $1;
    if ($msgstr =~ s/^.*(\%[sSdD])//)
    {
      my $second = $1;
      if ($first ne $second)
      {
        return (1);
      }
    } else {
      return (1);
    }
  }
  return (0);
} # end sub markFuzzy
my %revs;
my $cvsEntries;
if (open cvsEntries, "<CVS/Entries")
{
  print "Opened CVS\n";
  while (<cvsEntries>)
  {
    my $line = $_;
    if ($line =~ /^\/([a-zA-Z0-9\.\-\_]+)\/([0-9\.]+)/)
    {
      print "$1 $2\n";
      $revs{$1}=$2;
    }
  }
  close cvsEntries;
}
my @files = glob("*");
my $curr_file;
my $report;
open report, ">percents.html" or die ("Couldn't open percents.html");
print report "<html><head><title>Gallery Percent-Code report</title></head><body><table>\n";
my $report_row = 0;
foreach $curr_file (@files)
{
  if ($curr_file =~ /^(.{2}_.{2})(\..+)?\-gallery\.po$/)
  {
    print "processing : $curr_file \n";
    my $changes = 0;
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
      $lines[$i++] = $tmp;
    }
    close input;
    # finished reading input
    my $skip = 0;
    open output, ">$curr_file" or ($skip = 1);
    if ($skip)
    {
      print "Couldn't open $curr_file for writing\n";
    } else {
      $j = 0;
      while ($j < $i)
      {
        my $saveAsFuzzy = 0;
        if (($j < ($i - 1)) && ($lines[$j] =~ /^msgid\ (.*)$/))
        {
          my $msgid = $1;
          if ($lines[$j+1] =~ /^msgstr\ (.*)$/)
          {
            my $msgstr = $1;
            $saveAsFuzzy = markFuzzy($msgid, $msgstr);
            if ($saveAsFuzzy)
            {
              my $row_color = "#EEEEEE";
              if ($report_row++ % 2)
              {
                $row_color = "#AAAAAA"
              }
              my $tr = "<tr bgcolor=\"$row_color\">";
              print report "$tr<td";
              if ($revs{$curr_file} eq "") {
                print report " rowspan=\"2\"";
              }
              print report ">".$curr_file."</td><td>".$j."</td><td>".$lines[$j]."</td></tr>\n";
              print report $tr;
              if ($revs{$curr_file} ne "") {
                print report "<td align=\"right\">".$revs{$curr_file}."</td>";
              }
              print report "<td>".($j+1)."</td><td>".$lines[$j+1]."</td></tr>\n";
              print output "#, fuzzy, ".$lines[$j+1]."\n";
              print output $lines[$j]."\n";
              print output "msgstr \"\"\n";
            } else {
              print output $lines[$j]."\n";
              print output $lines[$j+1]."\n";
            } # end if $saveAsFuzzy
            $j++;
          } else {
              print output $lines[$j]."\n";
          } # end if $lines[$j+1]...
        } else {
          print output $lines[$j]."\n";
        }
        $j++;
      } # end while
      close output;
    } # end if $skip
  } # if $curr_file matches regex
} # next $curr_file
print report "</table></body></html>\n";
close report;

