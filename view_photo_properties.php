<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2003 Bharat Mediratta
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
 *
 * $Id$
 */
?>
<?php
// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
	print _("Security violation") ."\n";
	exit;
}
?>
<?php if (!isset($GALLERY_BASEDIR)) {
    $GALLERY_BASEDIR = '';
}
require($GALLERY_BASEDIR . 'init.php'); ?>

<html>
<head>
  <title><?php echo _("Photo Properties") ?></title>
  <?php echo getStyleSheetLink() ?>
</head>
<body dir=<?php echo $gallery->direction ?>>

<?php
if ($gallery->session->albumName && $index) {
?>

<center>
<?php echo _("Photo Properties") ?><br>
<br>

<?php echo $gallery->album->getThumbnailTag($index) ?>
<br>
<?php echo $gallery->album->getCaption($index) ?>
<?php echo $gallery->album->getCaptionName($index) ?>
<br><br>

<?php
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

PS:	Rasmus has fixed this bug in later versions of PHP (yay Rasmus)
	but we have not yet worked out the code that will detect if
	we're using the fixed version and use it instead of the
	jhead binary -- BM 2/23/2002

*/
	$forceRefresh = 0;
	if ($gallery->user->canWriteToAlbum($gallery->album)) {
		if (isset($reloadExifFromFile)) {
			$forceRefresh = 1;
		}
	}

	$myExif = $gallery->album->getExif($index, $forceRefresh);

	if ($myExif) {
		// following line commented out because we were losing
		// comments from the Exif array.  This is probably due
		// to differences in versions of jhead.
		// array_pop($myExif); // get rid of empty element at end
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

	echo _("File Upload Date") .":&nbsp;&nbsp; " . strftime("%c" , $gallery->album->getUploadDate($index)) . "<br>";
	$itemCaptureDate = $gallery->album->getItemCaptureDate($index);
	echo _("Item Capture Date") . ":&nbsp;&nbsp; " . strftime("%c", 
			mktime($itemCaptureDate['hours'], 
				$itemCaptureDate['minutes'],
				$itemCaptureDate['seconds'], 
				$itemCaptureDate['mon'],
				$itemCaptureDate['mday'],
				$itemCaptureDate['year']));

	if ($gallery->album->getKeyWords($index)) {
		echo "<b>". _("KEYWORDS") ."</b>: &nbsp;&nbsp; " . $gallery->album->getKeyWords($index);
	}

	if ($gallery->user->canWriteToAlbum($gallery->album) &&
	    !strcmp($gallery->app->cacheExif, "yes")) {
		echo "<br>";
		echo "<a href=" .
			makeGalleryUrl("view_photo_properties.php",
					array("reloadExifFromFile" => 1,
						"set_albumName" => $gallery->session->albumName,
						"index" => $index)) .
			">[". _("Reload EXIF Data From File") ."]</a>";
		echo "<br>";
		echo "<span class=fineprint>";
		echo _("(if the data is current, this will not appear to do anything)");
		echo "</span>";
	}
} else {
	gallery_error(_("no album / index specified"));
}
?>
<br><br>
<form action=#>
<input type=button value="<?php echo _("Done") ?>" onclick='parent.close()'>
</form>

</body>
</html>
