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

list($doit, $full) =
getRequestVar(array('doit', 'full'));

if (!empty($doit)) {
    $albumItemNames = $gallery->album->getAlbumItemNames($gallery->user, $full, false, true);
    $albumcopyName = createTempAlbum($albumItemNames);
    $zipfileName = createZip($albumcopyName, $gallery->album->fields['name']);
    downloadFile($zipfileName);
} else {
    list($numItems, $numAlbums, $numPhotos) = $gallery->album->numItems($gallery->user, true);

    $albumSize = $gallery->album->getAlbumSize($gallery->user, $full, false, true);

    doctype();
    echo "\n<html>";
?>
<head>
  <title><?php echo gTranslate('core', "Download album as archive") ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>" class="popupbody">
<div class="popuphead"><?php echo gTranslate('core', "Download album as archive") ?></div>
<div class="popup" align="center">
<p class="title">
<?php
    if ($gallery->album->numPhotos(1)) {
	echo $gallery->album->getHighlightTag();
	echo "<br>";
    }
    echo $gallery->album->fields["title"];
    echo "</p>";

    $textNumItems = sprintf(gTranslate('core', "This album contains just one item in total.", "This album contains %d items in total.", $numItems), $numItems);
    $textNumSubAlbums = sprintf(gTranslate('core', "One subalbum", "%d subalbums", $numAlbums, _("No subalbums")), $numAlbums);
    $textNumPhotos = sprintf(gTranslate('core', "one photo/movie", "%d photos/movies", $numPhotos , _("no photo/movie")), $numPhotos);

    echo sprintf("%s ". gTranslate('core', "%s and %s."), $textNumItems, $textNumSubAlbums, $textNumPhotos);

    if($numPhotos > 0) {
        echo '<p>'. sprintf(gTranslate('core', "Approximitaly size of zipfile: %s"), formatted_filesize($albumSize)) .'</p>';

        echo makeFormIntro('download.php', array('onChange' => 'document.g1_form.submit()'), array('gallery_popup' => 'true'));

        echo "\n". '<input type="radio" name="full" value="1"'. ($full ? ' checked' : '') .'>'. gTranslate('core', "Full Version") .'<br>';
        echo "\n". '<input type="radio" name="full" value="0"'. (!$full ? ' checked' : '') .'>'. gTranslate('core', "Resized Version") .'<br>';
        echo "\n<br>";
        echo "\n". '<input type="submit" name="doit" value="'. gTranslate('core', "Download") .'">';
        echo "\n". '<input type="button" value="'. gTranslate('core', "Cancel") .'" onclick="parent.close()">';
        echo "\n</form>";
    }
    else {
	echo "<br><br>";
	echo _("This album album is not empty, but contains no photo or movie! Download would'nt make sense.");
	echo "<br><br>";
        echo "\n". '<input type="button" value="'. gTranslate('core', "Close Window") .'" onclick="parent.close()">';
    }
}
?>
</div>
</body>
</html>