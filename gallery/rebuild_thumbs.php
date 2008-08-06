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
* $Id: rebuild_thumbs.php 13778 2006-06-08 17:51:08Z jenst $
*/

require_once(dirname(__FILE__) . '/init.php');

$recursive = getRequestVar('recursive');
$recreate = getRequestVar('recreate');

// Hack checks
if (empty($gallery->album) || ! isset($gallery->session->albumName)) {
	printPopupStart(gTranslate('core', "Rebuilding Thumbnails"));
	showInvalidReqMesg();
	exit;
}

// Hack check
if (!$gallery->user->canWriteToAlbum($gallery->album)) {
	printPopupStart(gTranslate('core', "Rebuilding Thumbnails"));
	showInvalidReqMesg(gTranslate('core', "You are not allowed to perform this action!"));
	exit;
}

printPopupStart(sprintf(gTranslate('core', "Rebuilding Thumbnails: %s"), $gallery->album->fields["title"]), '', 'left');

echo '<p align="center">' . $gallery->album->getHighlightAsThumbnailTag() . '</p>';

if(!empty($recreate)) {
	$gallery->album->makeThumbnails($recursive);
	$gallery->album->save('Thumbnails recreated');
	echo '<script type="text/javascript">opener.location.reload();</script>';

	echo "\n<p align=\"center\">";
	echo gButton('close', gTranslate('core', "Close"), 'parent.close()');
	echo "\n</p>";
}
else {
	echo gTranslate('core', "Here you can rebuild all thumbnails of your album. This is useful when thumbnails got broken, or you changed the thumnbail size / quality.");
	echo "\n<br>";
	echo gTranslate('core', "Custom thumbnails will not be reset to default. (Just resized, or rebuild)");
	echo "\n<br><br>";

	echo makeFormIntro('rebuild_thumbs.php', array('align' => 'center'), array('type' => 'popup'));

	echo gTranslate('core', "Do you also want to rebuild the thumbnails in subalbums?");
	echo gInput('radio', 'recursive', gTranslate('core', "Yes"), false, 1);
	echo gInput('radio', 'recursive', gTranslate('core', "No"), false, 0, array('checked' => null));

	echo "\n<br><br>";

	echo "\n<div align=\"center\">";
	echo gSubmit('recreate', gTranslate('core', "Start"));
	echo gButton('close', gTranslate('core', "Close"), 'parent.close()');
	echo "\n</div>";

	echo "\n</form>";
}

?>
</div>
</body>
</html>
