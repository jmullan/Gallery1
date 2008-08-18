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
 * $Id: recreate_captions.php 13778 2006-06-08 17:51:08Z jenst $
*/

require_once(dirname(dirname(__FILE__)) . '/init.php');

list($recursive, $setCaption) = getRequestVar(array('recursive', 'setCaption'));

// Hack checks
if (empty($gallery->album) || ! isset($gallery->session->albumName)) {
	printPopupStart(gTranslate('core', "Recreate captions"));
	showInvalidReqMesg();
	exit;
}

// Hack check
if (!$gallery->user->canWriteToAlbum($gallery->album)) {
	printPopupStart(gTranslate('core', "Recreate captions"));
	showInvalidReqMesg(gTranslate('core', "You are not allowed to perform this action!"));
	exit;
}

printPopupStart(sprintf(gTranslate('core', "Recreate captions for album: %s"), $gallery->album->fields["title"]), '', 'left');

if(!empty($setCaption) && !empty($recursive)) {
	if($gallery->album->createCaption(0, $setCaption, $recursive)) {
		echo '<script type="text/javascript">opener.location.reload();</script>';
		echo infoBox(array(array(
			'type' => 'success',
			'text' => gTranslate('core', "Captions successfully recreated.")
		)));
	}
	else {
		echo gallery_error(gTranslate('core', "Captions not successfully recreated."));
	}
	echo "\n<br>";
}

echo makeFormIntro('recreate_captions.php', array(), array('type' => 'popup'));

echo gTranslate('core', "Choose the type you want to recreate the captions.");

include(dirname(dirname(__FILE__)) .'/includes/add_photos/captionOptions.inc.php');

echo "\n<br><br>\n";
echo gTranslate('core', "Do you also want to recreate the captions in subalbums?");
echo "\n<br>";
?>
	<input type="radio" name="recursive" value="yes"> <?php echo gTranslate('core', "Yes"); ?>
	<input type="radio" name="recursive" value="no" checked> <?php echo gTranslate('core', "No"); ?>
	<br>
	<p align="center">
<?php

echo gSubmit('recreate', empty($recreate_type) ? gTranslate('core', "_Start") : gTranslate('core', "_Start over"));
echo gButton('close', gTranslate('core', "_Close"), 'parent.close()');
?>
	</p>
	</form>
</div>
</body>
</html>
