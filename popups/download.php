<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2007 Bharat Mediratta
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

require_once(dirname(dirname(__FILE__)) . '/init.php');

list($doit, $full) = getRequestVar(array('doit', 'full'));

$message = array();

if (!empty($doit)) {
	$albumItemNames = $gallery->album->getAlbumItemNames($gallery->user, $full, false, true);
	$albumcopyName = createTempAlbum($albumItemNames);
	if($albumcopyName) {
		$zipfileName = createZip($albumcopyName, $gallery->album->fields['name']);
		if($zipfileName) {
			if(!isDebugging()) {
				downloadFile($zipfileName);
			}
			else {
				$message = array(array(
					'type' => 'information',
					'text' => gTranslate('core', "Zipdownload would work, but is disabled when debugging.")));
			}
		}
	}
}

printPopupStart(gTranslate('core', "Download album as archive"));

echo infoBox($message);

list($numItems, $numAlbums, $numPhotos) = $gallery->album->numItems($gallery->user, true);

$albumSize = $gallery->album->getAlbumSize($gallery->user, $full, false, true);

echo "\n<p class=\"g-title g-emphasis\">";

if ($gallery->album->numPhotos(1)) {
	echo $gallery->album->getHighlightTag();
	echo "<br>";
}
echo $gallery->album->fields["title"];
echo "</p>";

$textNumItems = sprintf(gTranslate('core', "This album contains just one item in total.", "This album contains %d items in total.", $numItems), $numItems);
$textNumSubAlbums = sprintf(gTranslate('core', "One subalbum", "%d subalbums", $numAlbums, gTranslate('core', "No subalbums")), $numAlbums);
$textNumPhotos = sprintf(gTranslate('core', "one photo/movie", "%d photos/movies", $numPhotos , gTranslate('core', "no photo/movie")), $numPhotos);

printf("%s ". gTranslate('core', "%s and %s."), $textNumItems, $textNumSubAlbums, $textNumPhotos);

if($numPhotos > 0) {
	echo '<p>'. sprintf(gTranslate('core', "Approximate size of zipfile: %s"), formatted_filesize($albumSize)) .'</p>';

	echo makeFormIntro('download.php', array(), array('type' => 'popup'));
	?>
		<input type="radio" id="full" name="full" value="1" <?php echo ($full ? ' checked' : '') ?> onChange="document.g1_form.submit()">
		<label for="full"><?php echo gTranslate('core', "Full Version"); ?></label>
		<br>
		<input type="radio" id="resized" name="full" value="0" <?php echo (!$full ? ' checked' : '') ?> onChange="document.g1_form.submit()">
		<label for="resized"><?php echo gTranslate('core', "Resized Version"); ?></label>

		<br><br>
	<?php
	echo gSubmit('doit', gTranslate('core', "_Download"));
	echo gButton('cancel', gTranslate('core', "_Cancel"), 'parent.close()');
	echo "\n</form>";
}
else {
	echo "<br><br>";
	echo gTranslate('core', "This album is not empty, but contains no photo or movie! Download wouldn't make sense.");
	echo "<br><br>";
	echo gButton('close', gTranslate('core', "_Close Window"),'parent.close()');
}
?>
</div>
</body>
</html>