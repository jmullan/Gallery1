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

require_once(dirname(__FILE__) . '/init.php');

list($confirm, $sort, $order, $albumsFirst) =
	getRequestVar(array('confirm', 'sort', 'order', 'albumsFirst'));

printPopupStart(gTranslate('core', "Sort Album"));

// Hack checks
if (empty($gallery->album)) {
	showInvalidReqMesg();
	exit;
}

if (! $gallery->user->canWriteToAlbum($gallery->album)) {
	showInvalidReqMesg(gTranslate('core', "You are not allowed to perform this action!"));
	exit;
}

$allowedSorts = array('upload', 'itemCapture', 'filename', 'click', 'caption', 'comment', 'random');

if (!empty($confirm) &&
    in_array($sort, $allowedSorts) &&
    (empty($albumsFirst) || $albumsFirst == -1 || $albumsFirst == 1) &&
    ($order == -1 || $order == 1))
{
	if (!strcmp($sort, "random")) {
		$gallery->album->shufflePhotos();
		$gallery->album->save(array(i18n("Album resorted")));
		dismissAndReload();
		exit;
	}
	else {
		$gallery->album->sortPhotos($sort, $order, $albumsFirst);
		$gallery->album->save(array(i18n("Album resorted")));
		dismissAndReload();
		exit;
	}
}
else {
?>

<p>
<?php echo gTranslate('core', "Select your sorting criteria for this album below") ?>
<br>
<span class="g-emphasis"><?php echo gTranslate('core', "Warning:  This operation can't be undone.") ?></span>
</p>

<?php

	echo  $gallery->album->getHighlightAsThumbnailTag();
  
	echo "\n<br>";
  
	if (isset($gallery->album->fields['caption'])) {
		echo $gallery->album->fields['caption'];
	}
  
	echo "\n<br>";
  
	echo makeFormIntro('sort_album.php', array(), array('type' => 'popup'));
?>

<table class="left">
<?php
	echo gInput('radio', 'sort', gTranslate('core', "By Upload Date"), true, 'upload', array('checked' => null));
  	echo gInput('radio', 'sort', gTranslate('core', "By Picture-Taken Date"), true, 'itemCapture');
  	echo gInput('radio', 'sort', gTranslate('core', "By Filename"), true, 'filename');
  	echo gInput('radio', 'sort', gTranslate('core', "By Number of Clicks"), true, 'click');
  	echo gInput('radio', 'sort', gTranslate('core', "By Caption"), true, 'caption');
  	echo gInput('radio', 'sort', gTranslate('core', "By Number of Comments"), true, 'comment');
  	echo gInput('radio', 'sort', gTranslate('core', "Randomly"), true, 'random');
?>
  <tr><td colspan="2">&nbsp;</td></tr>
  <tr>
	<td colspan="2" class="center"><?php echo gTranslate('core', "Sort Order:"); ?>
	<select name="albumsFirst">
		<option value=""><?php echo gTranslate('core', "Just sort") ?></option>
		<option value="1"><?php echo gTranslate('core', "Albums first") ?></option>
		<option value="-1"><?php echo gTranslate('core', "Photos/Movies first") ?></option>
	</select>
	<select name="order">
		<option value="1"><?php echo gTranslate('core', "Ascending") ?></option>
		<option value="-1"><?php echo gTranslate('core', "Descending") ?></option>
	</select>
	</td>

  </tr>
</table>
<br>
<?php echo gSubmit('confirm', gTranslate('core', "Sort")); ?>
<?php echo gButton('cancel', gTranslate('core', "Cancel"), 'parent.close()'); ?>
</form>
<?php
}

?>
</div>
<?php print gallery_validation_link("sort_album.php"); ?>
</body>
</html>
