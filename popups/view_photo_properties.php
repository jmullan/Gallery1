<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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

require_once(dirname(dirname(__FILE__)) . '/init.php');

list($index, $reloadExifFromFile) = getRequestVar(array('index', 'reloadExifFromFile'));

echo printPopupStart(gTranslate('core', "Photo Properties"));

// Hack checks
if (empty($gallery->album) ||
    ! ($item = $gallery->album->getPhoto($index))) {
	showInvalidReqMesg();
	exit;
}

if (! $gallery->user->canReadAlbum($gallery->album)) {
	showInvalidReqMesg(gTranslate('core', "You are not allowed to perform this action!"));
	exit;
}

echo $gallery->album->getThumbnailTag($index);
echo "\n<br>";
echo $gallery->album->getCaption($index);
echo $gallery->album->getCaptionName($index);

/*
Here is the EXIF parsing code...
I have chosen to use a program called "jhead" to do EXIF parsing.

jhead is a public domain EXIF parser.  Source, linux binaries, and
windows binaries can be found at:
http://www.sentex.net/~mwandel/jhead/index.html

Why am I not using the php function: read_exif_data() ???

Well... this is where I started, but it didn't work for me for the following reasons:
1.  This module must be compiled into PHP, and it wasn't compiled
    into PHP in any default installation that I have access to.
2.  After compiling this module into PHP, I found it to be
    unusable because ALL error conditions in it are E_ERROR conditions.
    E_EROR conditions cause php to report a "fatal error" and stop parsing the php script.
    Well, the exif PHP module was reporting a "fatal error" even in the cases where you tried
    to read an EXIF header from a JPEG file that didn't contain one.
    Since I don't know whether any given JPEG file contains an EXIF header,
    I had to use read_exif_data to check... and then... BAM... fatal error.
    You cannot trap fatal errors (I tried this already), so I was stuck.

    After reading through the read_exif_data source from the PHP web site,
    I changed some of the E_ERROR conditions to E_NOTICE conditions and
    I no longer had fatal errors in my code.
    I will be submitting my code changes to the PHP development team to fix the read_exif_data
    function, but it won't be of any use for the gallery product until some future release of PHP.

    So... since the read_exif_data function is based on the 'jhead' program,
    I build the functionality using 'jhead'.

-John Kirkland

PS: Rasmus has fixed this bug in later versions of PHP (yay Rasmus)
    but we have not yet worked out the code that will detect if
    we're using the fixed version and use it instead of the
    jhead binary -- BM 2/23/2002

*/

$forceRefresh = false;
if ($gallery->user->canWriteToAlbum($gallery->album)) {
	if (!empty($reloadExifFromFile)) {
		$forceRefresh = true;
	}
}

$extra_fields = $gallery->album->getExtraFields(false);
$photoInfos = displayPhotoFields($index, $extra_fields, false, true, NULL, $forceRefresh);

if(!empty($photoInfos)) {
	echo "\n<div style=\"margin: 15px; height: 250px; overflow: auto;\">";
		echo $photoInfos;
		echo "\n</div>";
}

if ($gallery->album->getKeyWords($index)) {
	echo '<div class="left g-emphasis">'. gTranslate('core', "Keywords: ") . $gallery->album->getKeyWords($index) .'</div>';
	echo "\n<br>";
}

	if ($gallery->user->canWriteToAlbum($gallery->album) && $gallery->app->cacheExif == 'yes') {
		echo "\n</div>\n";
		echo '<div style="padding: 2px;">';
		echo galleryLink(
			makeGalleryUrl("view_photo_properties.php",
				array(
					'reloadExifFromFile' => 1,
					'set_albumName' => $gallery->session->albumName,
					'index' => $index,
					'type' => 'popup')),
			gTranslate('core', "Reload EXIF Data From File")
		);
		echo "<br>";
		echo gTranslate('core', "(if the data is current, this will not appear to do anything)");
	}
}
else {
	echo gallery_error(gTranslate('core', "no album / index specified"));
}

echo gButton('close', gTranslate('core', "_Close Window") , 'parent.close()');

includeTemplate('overall.footer');

?>
</body>
</html>
