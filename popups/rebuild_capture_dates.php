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

$recursive = getRequestVar('recursive');
$rebuild = getRequestVar('rebuild');

// Hack checks
if (empty($gallery->album) || ! isset($gallery->session->albumName)) {
	printPopupStart(gTranslate('core', "Rebuilding capture dates"));
	showInvalidReqMesg();
	exit;
}

// Hack check
if (!$gallery->user->canWriteToAlbum($gallery->album)) {
	printPopupStart(gTranslate('core', "Rebuilding capture dates"));
	showInvalidReqMesg(gTranslate('core', "You are not allowed to perform this action!"));
	exit;
}

printPopupStart(sprintf(gTranslate('core', "Rebuilding capture dates: %s"), $gallery->album->fields["title"]), '', 'left');

if(!empty($rebuild)) {
	$gallery->album->rebuildCaptureDates($recursive);
	echo '<script type="text/javascript">opener.location.reload();</script>';
	echo "\n<br>";
	echo gButton('close', gTranslate('core', "_Close"), 'parent.close()');
}
else {
	echo gTranslate('core', "Here you can rebuild all capture dates of your photos.");
	echo "\n<br>";
	echo gTranslate('core', "This is usefull when something went wrong, of you enabled jhead/exiftags after you upload items.");

	echo makeFormIntro('rebuild_capture_dates.php', array(), array('type' => 'popup'));

	echo gTranslate('core', "Do you also want to rebuild the capture dates of items in subalbums?");
	echo gInput('radio', 'recursive', gTranslate('core', "Yes"), false, 1);
	echo gInput('radio', 'recursive', gTranslate('core', "No"), false, 0, array('checked' => null));

	echo "\n<br><br>";

	echo "\n<div class=\"center\">";
	echo gSubmit('rebuild', gTranslate('core', "_Start"));
	echo gButton('close', gTranslate('core', "_Close"), 'parent.close()');
	echo "</div>";
	echo "\n</form>";
}

includeTemplate('overall.footer');
?>

</body>
</html>
