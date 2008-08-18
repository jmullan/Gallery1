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
 * @package Item
 */

require_once(dirname(__FILE__) . '/init.php');

list($index, $offsetX, $offsetY, $width, $height) =
	getRequestVar(array('index', 'x1', 'y1', 'width', 'height'));

list($cropit, $recreateResized) =
	getRequestVar(array('cropit', 'recreateResized'));

// Hack check and prevent errors
if (! isset($gallery->album) || ! $gallery->user->canChangeTextOfAlbum($gallery->album)) {
	header("Location: " . makeAlbumHeaderUrl());
	return;
}

if(!empty($cropit) && !empty($index)) {
	$gallery->album->cropPhoto($index, $offsetX, $offsetY, $width, $height, false);
	if(!empty($recreateResized)) {
		$gallery->album->cropPhoto($index, $offsetX, $offsetY, $width, $height, true);
	}
	$gallery->album->save(array("Image with index $index cropped."));
}

if ($index > $gallery->album->numPhotos(1)) {
	$index = 1;
}

$id = $gallery->album->getPhotoId($index);

$photo = $gallery->album->getPhoto($index);
$photoTag = $gallery->album->getPhotoTag($index, true, array('id' => 'cropImage', 'style' => 'position:absolute'));

list($imageWidth, $imageHeight) = $photo->image->getRawDimensions();
require_once(dirname(__FILE__) . '/includes/definitions/cropOptions.php');

#-- breadcrumb ---
$breadcrumb["text"] = returnToPathArray($gallery->album, true);

$breadcrumb["text"][] = galleryLink(
	makeAlbumUrl($gallery->session->albumName, $id),
	gTranslate('core', "Original photo"). "&nbsp;". gImage('icons/navigation/nav_home.gif'),
	array(), '', false, false
);
$breadcrumb["bordercolor"] = $gallery->album->fields["bordercolor"];

$rows = $gallery->album->fields["rows"];
$cols = $gallery->album->fields["cols"];
$perPage = $rows * $cols;
$page = (int)(ceil($index / ($rows * $cols)));

/* Start the HTML Output */
if (!$GALLERY_EMBEDDED_INSIDE) {
	doctype(); ?>
<html>
<head>
  <title><?php echo $gallery->app->galleryTitle; ?> :: Image Cropping :: </title>
  <?php
  common_header();
  ?>
</head>
<body>
<?php
} // End if ! embedded

includeTemplate("photo.header");

includeLayout('breadcrumb.inc');

// Determine if user has the rights to view full-sized images
if (!$gallery->user->canViewFullImages($gallery->album)) {

	printInfoBox(array(array(
		'type' => 'error',
		'text' => gTranslate('core', "You do not have the permission to edit the fullsize Version of this image.")))
	);
}
else {
?>
  <script language="JavaScript" type="text/javascript" src="<?php echo $gallery->app->photoAlbumURL .'/js/cropper/prototype.js'; ?>"></script>
  <script language="JavaScript" type="text/javascript" src="<?php echo $gallery->app->photoAlbumURL .'/js/cropper/scriptaculous.js?load=builder,dragdrop'; ?>"></script>
  <script language="JavaScript" type="text/javascript" src="<?php echo $gallery->app->photoAlbumURL .'/js/cropper/cropper.js'; ?>"></script>
  <script language="JavaScript" type="text/javascript" src="<?php echo $gallery->app->photoAlbumURL .'/js/cropper/cropperInit.js'; ?>"></script>

  <div class="g-sitedesc">
	<?php
	echo gTranslate('core', "Here you can crop the fullsize Version of your Image. Optionally you can let the intermediate version be created automatically.");
	?>
	<br>
  </div>

  <div class="g-cropImageBox floatleft" style="width: <?php echo $imageWidth ?>px; height: <?php echo $imageHeight ?>px">
	<div id="cropArea" style="position: absolute;">
		<?php echo $photoTag; ?>
  	</div>
  </div>

<?php
	echo makeFormIntro('crop_photo.php',
		array('name' => 'crop'),
		array('index' => $index)
	);

	echo gInput('hidden', 'x1', null, false, '', array('id' => 'x1'));
	echo gInput('hidden', 'y1', null, false, '', array('id' => 'y1'));
	echo gInput('hidden', 'x2', null, false, '', array('id' => 'x2'));
	echo gInput('hidden', 'y2', null, false, '', array('id' => 'y2'));
?>
	<div style="white-space:nowrap; margin: 2px;">
	<fieldset>
		<legend><?php echo gTranslate ('core', "Current Dimension:"); ?></legend>
		<?php echo gInput('text', 'iwidth', gTranslate('core', "Width"), false, $imageWidth, array('disabled' => 'disabled')); ?>
		<?php echo gInput('text', 'iheight', gTranslate('core', "Height"), false, $imageHeight, array('disabled' => 'disabled')); ?>
	</fieldset>

	<br>

	<fieldset style="white-space:nowrap;">
		<legend><?php echo gTranslate ('core', "New Dimension:"); ?></legend>
		<?php echo gInput('text', 'width', gTranslate('core', "Width"), false, '', array('id' => 'width', 'readonly' => 'readonly')); ?>
		<?php echo gInput('text', 'height', gTranslate('core', "Height"), false, '', array('id' => 'height', 'readonly' => 'readonly')); ?>
	</fieldset>

<?php
	echo gTranslate('core', "Select a ratio for the crop frame:");
	echo "\n<br>";
	echo drawSelect('cropRatio', $ratioOptions, '0|0', 1, array('id' => 'cropRatio', 'onChange' => 'setRatio()'));
	echo drawSelect('cropRatioDir', $ratioDirections, '', 1, array('id' => 'cropRatioDir',  'onChange' => 'setRatio()'));

	echo "\n<br>";
	echo gInput('checkbox', 'recreateResized', gTranslate('core', "Recreate re_sized"));

	echo "\n<br><br>";

	echo gButton('preview', gTranslate('core', "_Preview"), 'previewCrop()');
	echo gButton('reset', gTranslate('core', "_Reset"), 'resetCrop()');
	echo gSubmit('cropit', gTranslate('core', "_Crop"));

	echo "\n</div>";
	echo "\n</form>\n";

	echo '<div class="clear"></div>';
	includeLayout('breadcrumb.inc');

	echo languageSelector();
}

includeTemplate('info_donation-block');

includeTemplate('overall.footer');

if (!$GALLERY_EMBEDDED_INSIDE) { ?>
</body>
</html>
<?php }
?>