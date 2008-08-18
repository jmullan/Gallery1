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
require_once(dirname(dirname(__FILE__)) . '/init.php');

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

if (empty($index)) {
	$index = '';
}
$highlightIndex = $gallery->album->getHighlight();
$err = '';

if (isset($save) || isset($preview)) {
	if (isset($wmAlign) && ($wmAlign > 0) && ($wmAlign < 12)) {
		if (isset($wmName) && !empty($wmName)) {
			if (isset($save)) {
				printPopupStart(gTranslate('core', "Watermarking album..."));
		
		echo gTranslate('core', "(this may take a while)");
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
		echo '<br><br>';
			echo gButton('close', gTranslate('core', "_Close"), 'parent.close()');
?>
</div>
</body>
</html>
<?php
			} else {
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
		} else {
			$err = gTranslate('core', "Please select a watermark.");
		}
	} else {
		$err = gTranslate('core', "Please select an alignment.");
	}
} else {
	if (!isset($recursive)) {
		$recursive = 1;
	}
}

printPopupStart(gTranslate('core', "Watermark Album"));

if (!$gallery->album->numPhotos(1)) {
	echo "\n<p>". gallery_error(gTranslate('core', "No items to watermark.")) . "</p>";
} else {
	$highlightIndex = $gallery->album->getHighlight();
	if (isset($highlightIndex)) {
		if (isset($preview)) {
			echo $gallery->album->getPreviewTag($highlightIndex);
		} else {
			echo $gallery->album->getThumbnailTag($highlightIndex);
		}
	}

	if (!empty($err)) {
		echo "\n<p>". gallery_error($err) . "</p>";
	}

	echo infoBox(array(array(
	'type' => 'information',
	'text' => gTranslate('core', "Keep in mind that watermarking on animated gifs is currently not supported and will 'deface & unanimate' your pictures.")
	)));
	echo makeFormIntro('watermark_album.php',
		array(),
		array('type' => 'popup', 'index' => $index));
		
	global $watermarkForm;
	$watermarkForm["askRecursive"] = 1;
	$watermarkForm["askPreview"] = 1;
	$watermarkForm["allowNone"] = 0;
	includeLayout ('watermarkform.inc');
?>

<p>
<?php

if(empty($errors)) {
	echo gSubmit('save', gTranslate('core', "_Save"));
	// only allow preview if there is a highlight
	if (isset($highlightIndex)) {
	echo gSubmit('preview', gTranslate('core', "_Preview"));
	}
}
	echo gButton('cancel', gTranslate('core', "_Cancel"), 'parent.close()');
?>
</p>
</form>

<?php 
} // end if numPhotos()
?>
</div>

</body>
</html>
