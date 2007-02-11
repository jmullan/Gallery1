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
* $Id: rebuild_thumbs.php 13778 2006-06-08 17:51:08Z jenst $
*/
?>
<?php
require_once(dirname(dirname(__FILE__)) . '/init.php');

$recursive = getRequestVar('recursive');
$recreate = getRequestVar('recreate');

// Hack check
if (!$gallery->user->canWriteToAlbum($gallery->album)) {
	echo gTranslate('core', "You are not allowed to perform this action!");
	exit;
}

printPopupStart(sprintf(gTranslate('core', "Rebuilding Thumbnails: %s"), $gallery->album->fields["title"]), '', 'left');

if(!empty($recreate)) {
	$gallery->album->makeThumbnails($recursive);
	echo '<script type="text/javascript">opener.location.reload();</script>';
	echo "\n<br>";
	echo gButton('close', gTranslate('core', "_Close"), 'parent.close()');
}
else {
	echo gTranslate('core', "Here you can rebuild all thumbnails of your album. This is useful when thumbnails got broken, or you changed the thumnbail size / quality.");
	echo "\n<br>";
	echo gTranslate('core', "Custom thumbnails will not be reset to default. (Just resized, or rebuild)");
	echo "\n<br><br>";
	echo gTranslate('core', "Do you also want to rebuild the thumbnails in subalbums?");
	echo "\n<br>";

	echo makeFormIntro('rebuild_thumbs.php', array(), array('type' => 'popup'));
?>
	<input type="radio" name="recursive" value="true"> <?php echo gTranslate('core', "Yes"); ?>
	<input type="radio" name="recursive" value="false" checked> <?php echo gTranslate('core', "No"); ?>
	<br><br>
	<?php

	echo gSubmit('recreate', gTranslate('core', "_Start"));
	echo gButton('close', gTranslate('core', "_Close"), 'parent.close()');
?>
	  </form>
<?php
}
?>
</div>
</body>
</html>
