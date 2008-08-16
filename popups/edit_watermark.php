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

/**
 * This popup provides the possibility to put a watermark on a picture.
 * A preview can be viewed before.
 *
 * @package Item
 */

/**
 *
 */
require_once(dirname(dirname(__FILE__)) . '/init.php');

list($index, $save, $preview) = getRequestVar(array('index', 'save', 'preview'));

list($wmAlignX, $wmAlignY) = getRequestVar(array('wmAlignX', 'wmAlignY'));

// Hack check
if (! $gallery->user->canWriteToAlbum($gallery->album) &&
	! $gallery->album->getItemOwnerModify() &&
	! $gallery->album->isItemOwner($gallery->user->getUid(), $index))
{
	showInvalidReqMesg(gTranslate('core', "You are not allowed to perform this action!"));
	exit;
}


$photo = $gallery->album->getPhoto($index);
$err = '';

if (isset($save) || isset($preview)) {
	if (isset($wmAlign) && ($wmAlign > 0) && ($wmAlign < 12)) {
		if (isset($wmName) && !empty($wmName)) {
			if (isset($save)) {
				my_flush();
				set_time_limit($gallery->app->timeLimit);
				$gallery->album->watermarkPhoto($index, $wmName, "", $wmAlign,
				  isset($wmAlignX) ? $wmAlignX : 0,
				  isset($wmAlignY) ? $wmAlignY : 0,
				  0, 0, // Not a preview
				  isset($wmSelect) ? $wmSelect : 0
				);
				dismissAndReload();
				return;
			}
			else {
				$gallery->album->watermarkPhoto(
				  $index,
				  $wmName,
				  '',
				  $wmAlign,
				  isset($wmAlignX) ? $wmAlignX : 0,
				  isset($wmAlignY) ? $wmAlignY : 0,
				  1, // set as preview
				  isset($previewFull) ? $previewFull : 0
				);
			}
		} else {
			$err = gTranslate('core', "Please select a watermark.");
		}
	} else {
		$err = gTranslate('core', "Please select an alignment.");
	}
}

printPopupStart(gTranslate('core', "Edit Watermark"));
echo "\n<p>";

if (isset($preview)) {
	echo $gallery->album->getPreviewTag($index);
}
else {
	echo $gallery->album->getThumbnailTag($index);
}
echo "\n</p>";

if (!empty($err)) {
	echo '<p class="g-error">'. $err .'</p>';
}

if ($photo->image->type == 'gif') {
	echo infoBox(array(array(
		'type' => 'info',
		'text' => gTranslate('core', "Your image is a gif. Watermarking on animated gifs is currently not supported. It will 'deface' and 'unanimate' your picture.")
	)));
}

echo makeFormIntro('edit_watermark.php', array(), array('type' => 'popup', 'index' => $index));

global $watermarkForm;
$watermarkForm['askRecursive']	= 0;
$watermarkForm['askPreview']	= 1;
$watermarkForm['allowNone']	= 0;
includeLayout('watermarkform.inc');

echo "\n<br>\n";

// $errors is from watermarkform.inc
if(empty($errors)) {
	echo gSubmit('save', gTranslate('core', "_Save"));
	echo gSubmit('preview', gTranslate('core', "_Preview"));
}

echo gButton('cancel', gTranslate('core', "_Cancel"), 'parent.close()');
?>
</form>

<?php includeTemplate('overall.footer'); ?>

</body>
</html>
