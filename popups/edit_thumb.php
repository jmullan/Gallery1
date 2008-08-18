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
require_once(dirname(dirname(__FILE__)) . '/includes/definitions/cropOptions.php');

list($index, $offsetX, $offsetY, $width, $height, $muliplier) =
	getRequestVar(array('index', 'x1', 'y1', 'width', 'height', 'muliplier'));

list($cropit, $dismiss) =
	getRequestVar(array('cropit', 'dismiss'));

if (!empty($dismiss)) {
	// -- just close ---
	dismissAndLoad();
	exit;
}

// Hack checks
if (! isset($gallery->album) || ! isset($gallery->session->albumName) ||
	! $photo = $gallery->album->getPhoto($index))
{
	printPopupStart(gTranslate('core', "Custom Thumbnail"), '', 'left');
	showInvalidReqMesg();
	exit;
}

// Hack check
if (!$gallery->user->canWriteToAlbum($gallery->album) &&
	!$gallery->album->isItemOwner($gallery->user->getUid(), $index) &&
	!$gallery->album->getItemOwnerModify())
{
	printPopupStart(gTranslate('core', "Custom Thumbnail"), '', 'left');
	showInvalidReqMesg(gTranslate('core', "You are not allowed to perform this action!"));
	exit;
}

if (!empty($cropit)) {
	doctype();
	echo "<html>";

	#-- rebuild the thumbnail, cropped) ---
	echo(gTranslate('core', "Recreating the Thumbnail..."));
	my_flush();

	$offsetX	= intval($muliplier * $offsetX);
	$offsetY	= intval($muliplier * $offsetY);
	$width		= intval($muliplier * $width);
	$height		= intval($muliplier * $height);

	if ($gallery->session->albumName && isset($index)) {
		$photo = $gallery->album->getPhoto($index);
		$photo->image->setThumbRectangle($offsetX, $offsetY, $width, $height);
		$gallery->album->setPhoto($photo, $index);
		$gallery->album->makeThumbnail($index);
		$gallery->album->save(
			array(i18n("Thumbnail modified for %s"),
			makeAlbumURL($gallery->album->fields["name"], $gallery->album->getPhotoId($index)))
		);
	}

	// -- close and reload parent ---
	dismissAndReload();
	exit;
}

if (!empty($dismiss)) {
	#-- just close ---
	dismissAndLoad();
	exit;
}

/* No Action done */
$messages = array();

$photo = $gallery->album->getPhoto($index);
list($imageWidth, $imageHeight) = $photo->image->getRawDimensions();
$fullImageWidth		= $imageWidth;
$fullImageHeight	= $imageHeight;

$ratio = $imageHeight/$imageWidth;
$muliplier = 1;

$maxSize = 500;
$minSize = 25;

if($imageWidth > $maxSize) {
	$muliplier = $imageWidth/$maxSize;
	$imageWidth = $maxSize;
	$imageHeight = intval($imageWidth * $ratio);
	$messages[] = array(
			'type' => 'information',
			'text' => sprintf(gTranslate('core', "You see a downscaled preview. Its scalled from '%dx%d' to '%dx%d'"),
				$fullImageWidth, $fullImageHeight, $imageWidth, $imageHeight)
	);
}

$photoTag = $gallery->album->getPhotoTag(
	$index, true, array(
		'id' => 'cropImage',
		'width' => $imageWidth,
		'height' => $imageHeight)
);

if($imageWidth < $minSize || $imageHeight < $minSize) {
	$messages[] = array(
			'type' => 'error',
			'text' => sprintf(gTranslate('core', "Cropping this image does not seem to make sense. Both sides should be at least %spx"),
				      $minSize)
    );
    $noCrop = true;
}

$fullpath = $gallery->album->getAbsolutePhotoPath($index, true);

/*
 * Big files are slowing the cropper.
 * If the file is bigger then 500K,
 * we check whether GD is support and create dynamically a resized version
*/
$filesize = fs_filesize($fullpath);
if($filesize > 512000) {
	$ext = getExtension($fullpath);
	if(gdAvailable($ext)) {
		$src = plainUrl('picture.php', array(
			'index'		=> $index,
			'newwidth'	=> $maxSize)
		);
		$photoTag = "<img src=\"$src\" alt=\"\" id=\"cropImage\" width=\"$imageWidth\" height=\"$imageHeight\">";
	}
	else {
		$messages[] = array(
			'type' => 'warning',
			'text' => sprintf(gTranslate('core', "Your fullsize image is over 500k (%s) and will slow down the cropper. To fix this you need a PHP with GD support."),
				formatted_filesize($filesize))
		);
	}
}

printPopupStart(gTranslate('core', "Custom Thumbnail"), '', 'left');
if ($imageWidth > 400) {
?>
	<script language="JavaScript" type="text/javascript">
		window.outerWidth = 800;
	</script>
<?php }
if (!isset($noCrop)) {
?>
	<script language="JavaScript" type="text/javascript" src="<?php echo $gallery->app->photoAlbumURL .'/js/cropper/prototype.js'; ?>"></script>
	<script language="JavaScript" type="text/javascript" src="<?php echo $gallery->app->photoAlbumURL .'/js/cropper/scriptaculous.js?load=builder,dragdrop'; ?>"></script>
	<script language="JavaScript" type="text/javascript" src="<?php echo $gallery->app->photoAlbumURL .'/js/cropper/cropper.js'; ?>"></script>
	<script language="JavaScript" type="text/javascript" src="<?php echo $gallery->app->photoAlbumURL .'/js/cropper/cropperInit.js'; ?>"></script>
<?php
}

printInfoBox($messages);
if (!isset($noCrop)) {
	echo gTranslate('core', "Choose which part of the image will compose your thumbnail:");
}
?>
	<div class="g-cropImageBox floatleft" style="width: <?php echo $imageWidth ?>px; height: <?php echo $imageHeight ?>px">
		<div id="cropArea" style="position: absolute;">
		<?php echo $photoTag; ?>
		</div>
	</div>
<?php

if (!isset($noCrop)) {
	echo makeFormIntro('edit_thumb.php',
		array('name' => 'crop'),
		array('type' => 'popup',
			'index' => $index,
			'x1' => '',
			'y1' => '',
			'x2' => '',
			'y2' => '',
			'width' => '',
			'height' => '',
			'muliplier' => $muliplier
		)
	);

	echo "\n<br><br>";
	echo gTranslate('core', "Select a ratio for the crop frame:");
	echo "\n<br>";
	echo drawSelect('cropRatio', $ratioOptions, '0|0', 1, array('id' => 'cropRatio', 'onChange' => 'setRatio()'));
	echo drawSelect('cropRatioDir', $ratioDirections, '', 1, array('id' => 'cropRatioDir',  'onChange' => 'setRatio()'));

	echo "\n<br><br>";
	echo gSubmit('dismiss', gTranslate('core', "_Dismiss"));
	echo gSubmit('cropit', gTranslate('core', "_Crop"));

?>
<div class="clear"></div>
</form>
<?php
}
else {
	echo "\n<p class=\"clear center\">";
	echo gButton('close', gTranslate('core', "_Close Window"), 'parent.close()');
	echo "\n</p>";
}
?>
</div>

</body>
</html>

