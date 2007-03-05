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
require_once(dirname(__FILE__) . '/init.php');

$recursive = getRequestVar('recursive');
$rebuild = getRequestVar('rebuild');

// Hack check
if (!$gallery->user->canWriteToAlbum($gallery->album)) {
	echo gTranslate('core', "You are not allowed to perform this action!");
	exit;
}

doctype();
printPopupStart(sprintf(gTranslate('core', "Rebuilding capture dates: %s"), $gallery->album->fields["title"]), '', 'left');

if(!empty($rebuild)) {
	$gallery->album->rebuildCaptureDates($recursive);
	echo "\n<br><br>";
	echo gButton('close', gTranslate('core', "Close"), 'parent.close()');
}
else {
	echo gTranslate('core', "Here you can rebuild all capture dates of your photos.");
	echo "\n<br>";
	echo gTranslate('core', "This is usefull when something went wrong, of you enabled jhead/exiftags after you upload items.");
	echo "\n<br>";
	echo gTranslate('core', "Do you also want to rebuild the capture dates of items in subalbums?");
	echo "\n<br>";

	echo makeFormIntro('rebuild_capture_dates.php', array(), array('type' => 'popup'));
?>
	<p align="center">
	<input type="radio" name="recursive" value="1"> <?php echo gTranslate('core', "Yes"); ?>
	<input type="radio" name="recursive" value="0" checked> <?php echo gTranslate('core', "No"); ?>
	<br><br>
	<?php

	echo gSubmit('rebuild', gTranslate('core', "Start"));
	echo gButton('close', gTranslate('core', "Close"), 'parent.close()');
?>
	</p>
	</form>
<?php
}
?>
</div>
</body>
</html>
