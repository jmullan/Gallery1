<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2005 Bharat Mediratta
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
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * $Id$
 */
?>
<?php

require_once(dirname(__FILE__) . '/init.php');

$index = getRequestVar('index');

// Hack check
if (!$gallery->user->canReadAlbum($gallery->album)) {
        print _("Security violation") ."\n";
	return;
}

doctype();
?>
<html>
<head>
  <title><?php echo _("Photo Properties") ?></title>
  <?php common_header(); ?>
  <style> td { text-align: <?php echo langLeft() ?> } </style>
</head>
<body dir="<?php echo $gallery->direction ?>" class="popupbody">
<?php
if ($gallery->session->albumName && $index) {
?>
<div class="popuphead"><?php echo _("Photo Properties") ?></div>
<div class="popup" align="center">
<span>
	<?php echo $gallery->album->getThumbnailTag($index) ?>
	<br>
	<?php echo $gallery->album->getCaption($index) ?>
	<?php echo $gallery->album->getCaptionName($index) ?>
	<br><br>
</span>

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

PS: Rasmus has fixed this bug in later versions of PHP (yay Rasmus)
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

    $extra_fields = $gallery->album->getExtraFields(false);

    displayPhotoFields($index, $extra_fields, false, true,NULL,$forceRefresh);

    if ($gallery->album->getKeyWords($index)) {
        echo "<br><b>". _("KEYWORDS") ."</b>: &nbsp;&nbsp; " . $gallery->album->getKeyWords($index);
    }

    if ($gallery->user->canWriteToAlbum($gallery->album) &&
        !strcmp($gallery->app->cacheExif, "yes")) {
        echo "<br>";
        echo "<a href=\"" .
            makeGalleryUrl("view_photo_properties.php",
                    array("reloadExifFromFile" => 1,
                        "set_albumName" => $gallery->session->albumName,
                        "index" => $index)) .
            "\">[". _("Reload EXIF Data From File") ."]</a>";
        echo "<br></span>";
        echo "<span class=popup>";
        echo _("(if the data is current, this will not appear to do anything)");
        echo "</span>";
    }
} else {
	echo gallery_error(_("no album / index specified"));
}
?>
<br><br>
</div>
<center>
<form action="#">
<input type="button" value="<?php echo gTranslate('core', "Close Window") ?>" onclick='parent.close()'>
</form>
</center>

<?php print gallery_validation_link("view_photo_properties.php", true, array('index' => $index)); ?>
</div>
</body>
</html>
