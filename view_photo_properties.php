<?
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2001 Bharat Mediratta
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */
?>
<?
// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
	print "Security violation\n";
	exit;
}
?>
<? require($GALLERY_BASEDIR . "init.php"); ?>

<html>
<head>
  <title>Photo Properties</title>
  <?= getStyleSheetLink() ?>
</head>
<body>

<?
if ($gallery->session->albumName && $index) {
?>

<center>
Photo Properties<br>
<br>

<?= $gallery->album->getThumbnailTag($index) ?>
<br>
<?= $gallery->album->getCaption($index) ?>
<br><br>

<?
/* 
Here is the EXIF parsing code...
I have chosen to use a program called "jhead" to do EXIF parsing.
            
jhead is a public domain EXIF parser.  Source, linux binaries, and
windows binaries can be found at:
http://www.sentex.net/~mwandel/jhead/index.html

Why am I not using the php function: read_exif_data() ???

Well... this is where I started, but it didn't work for me
for the following reasons:
1.  This module must be compiled into PHP, and it wasn't compiled
into PHP in any default installation that I have access to.
2.  After compiling this module into PHP, I found it to be
unusable because ALL error conditions in it are E_ERROR conditions.
E_ERROR conditions cause php to report a "fatal error" and stop
parsing the php script.  Well, the exif PHP module was reporting   
a "fatal error" even in the cases where you tried to read an
EXIF header from a JPEG file that didn't contain one.  Since I don't
know whether any given JPEG file contains an EXIF header, I had
to use read_exif_data to check... and then... BAM... fatal error.
You cannot trap fatal errors (I tried this already), so I was stuck.  

After reading through the read_exif_data source from the PHP web
site, I changed some of the E_ERROR conditions to E_NOTICE conditions
and I no longer had fatal errors in my code.  I will be submitting
my code changes to the PHP development team to fix the read_exif_data
function, but it won't be of any use for the gallery product until
some future release of PHP.

So... since the read_exif_data function is based on the 'jhead'
program, I build the functionality using 'jhead'.

-John Kirkland

*/

	$myExif = $gallery->album->getExif($index);

	if ($myExif) {
		array_pop($myExif); // get rid of empty element at end
		array_shift($myExif); // get rid of file name at beginning
		$sizeOfExif = sizeof($myExif);
		$sizeOfTable = $sizeOfExif / 2;
		$i = 1;
		$column = 1;
		echo ("<table width=100%>\n");
		echo ("<tr valign=top>\n");
		echo ("<td>\n");
		while (list($key, $value) = each ($myExif)) {
			echo "<b>$key</b>:  $value<br>\n";
			if (($i >= $sizeOfTable) && ($column == 1)) {
				echo ("</td>\n");
				echo ("<td>\n");
				$column = 2;
			}
    		$i++;
		}
		echo ("</td>\n</table><br>");
	}

	echo ("File Upload Date: &nbsp;&nbsp; " . date("m-d-Y H:i:s" , $gallery->album->getUploadDate($index)) . "<br>");
	$itemCaptureDate = $gallery->album->getItemCaptureDate($index);
	echo ("Item Capture Date: &nbsp;&nbsp; " . $itemCaptureDate[mon] . "-" . $itemCaptureDate[mday] . "-" . $itemCaptureDate[year] . "&nbsp;&nbsp;");
	echo ($itemCaptureDate[hours] . ":" . $itemCaptureDate[minutes] . ":" . $itemCaptureDate[seconds] . "<br>");

	if ($gallery->album->getKeyWords($index)) {
		echo "<b>KEYWORDS</b>: &nbsp;&nbsp; " . $gallery->album->getKeyWords($index);
	}
} else {
	error("no album / index specified");
}
?>
<br><br>
<form action=#>
<input type=submit value="Done" onclick='parent.close()'>
</form>

</body>
</html>

