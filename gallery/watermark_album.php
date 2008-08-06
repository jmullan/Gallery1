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
 * This popup provides the possibility to put a watermark on every picture in an album.
 * Subalbums can be watermarked revcursively.
 *
 * @package Item
 */

/**
 *
 */
require_once(dirname(__FILE__) . '/init.php');

list($save, $preview, $wmAlign, $wmName, $wmSelect) =
	getRequestVar(array('save', 'preview', 'wmAlign', 'wmName', 'wmSelect'));

list($wmAlignX, $wmAlignY, $recursive, $previewFull) =
	getRequestVar(array('wmAlignX', 'wmAlignY', 'recursive', 'previewFull'));

// Hack checks
if (empty($gallery->album)) {
	printPopupStart(gTranslate('core', "Watermark Album"));
	showInvalidReqMesg();
	exit;
}

printPopupStart(sprintf(gTranslate('core', "Watermarking album :: %s"), $gallery->album->fields['title']));

if (! $gallery->user->canWriteToAlbum($gallery->album)) {
	showInvalidReqMesg(gTranslate('core', "You are not allowed to perform this action!"));
	exit;
}

if ($gallery->album->numPhotos(1) == 0) {
	echo "\n<p>". gallery_error(gTranslate('core', "No items to watermark.")) . "</p>";
	echo gButton('close', gTranslate('core', "Close"), 'parent.close()');
	includeTemplate('overall.footer');
	exit;
}

$highlightIndex = $gallery->album->getHighlight();

$notice_messages = array();

if (isset($save) || isset($preview)) {
	$notice_messages =
		checkWatermarkSetting($wmName, $wmAlign, $wmSelect, $previewFull, $wmAlignX, $wmAlignY);

	if(empty($notice_messages)) {
		if (isset($save)) {
			echo gallery_info(gTranslate('core', "Watermarking album... (this may take a while)"));
			my_flush();
			set_time_limit($gallery->app->timeLimit);
			$gallery->album->watermarkAlbum(
				$wmName,
				"",
				$wmAlign,
				$wmAlignX,
				$wmAlignY,
				$recursive,
				$wmSelect
			);
			$gallery->album->save();

			echo gallery_success(gTranslate('core', "Watermarking done."));
		}
		else {
			// create a preview of the highlight image
			$gallery->album->watermarkPhoto(
				$highlightIndex,
				$wmName,
				"",
				$wmAlign,
				isset($wmAlignX) ? $wmAlignX : 0,
				isset($wmAlignY) ? $wmAlignY : 0,
				1, // set as preview
				isset($previewFull) ? $previewFull : 0);
		}
	}
}

echo infoBox($notice_messages);

if (isset($highlightIndex)) {
	if (isset($preview)) {
		echo gallery_info(gTranslate('core', "Preview"));
		echo $gallery->album->getPreviewTag($highlightIndex);
	}
	else {
		echo $gallery->album->getThumbnailTag($highlightIndex);
	}
}
else {
	echo gallery_error(gTranslate('core', "No preview possible, as this album has no highlight set."));
}

echo gallery_info(
	gTranslate('core', "Keep in mind that watermarking on animated gifs is currently not supported and will 'deface &amp; unanimate' your pictures.")
);

echo makeFormIntro('watermark_album.php', array(), array('type' => 'popup'));

global $watermarkForm;
$watermarkForm['askRecursive']	= 1;
$watermarkForm['askPreview']	= 1;
$watermarkForm['allowNone']	= 0;
includeLayout ('watermarkform.inc');

?>

<p>
<?php

if(empty($errors)) {
	echo gSubmit('save', gTranslate('core', "Save"));
}
echo gButton('close', gTranslate('core', "Close"), 'parent.close()');

?>
</p>
</form>

</div>

</body>
</html>
