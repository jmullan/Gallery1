<?
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2001 Bharat Mediratta
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
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */
?>
<?
require($GALLERY_BASEDIR . "init.php");

// Hack check
if (!$gallery->user->canWriteToAlbum($gallery->album)) {
	exit;
}
?>


<html>
<head>
  <title>Custom Thumbnail</title>
  <link rel="stylesheet" type="text/css" href="<?= getGalleryStyleSheetName() ?>">
</head>

<? 
if ($action == "doit") {
	
	#-- rebuild the thumbnail, cropped) ---
	echo("Remaking the Thumbnail...");
	my_flush();
	if ($gallery->session->albumName && isset($index)) { 
		$photo = $gallery->album->getPhoto($index);
		$photo->image->setThumbRectangle($crop_x, $crop_y, $crop_w, $crop_h);
		$photo->makeThumbnail($gallery->album->getAlbumDir(), $gallery->album->fields["thumb_size"]);
		$gallery->album->setPhoto($photo, $index);
		$gallery->album->save();
	}	
	
	#-- close and reload parent ---
	dismissAndReload();

} else if ($action == "cancel") {
	#-- just close ---
	dismiss();
} else {
	#-- show the applet ---
?>
<body>

<span class="popuphead">Custom Thumbnail</span>
<br>

<?
	#-- are we a go? ---
	if ($gallery->session->albumName && isset($index)) { 

		$photo = $gallery->album->getPhoto($index);
	
		#-- the url to the image ---
		$photoURL = $gallery->album->getAlbumDirURL() . "/";
		if ($photo->image->resizedName) {
			$photoURL .= $photo->image->resizedName . "." . $photo->image->type;
		} else {
			$photoURL .= $photo->image->name . "." . $photo->image->type;
		}

		#-- the dimensions of the raw image ---
		list($image_w, $image_h) = $photo->image->getRawDimensions($gallery->album->getAlbumDir());
		list($t_x, $t_y, $t_w, $t_h) = $photo->image->getThumbRectangle();
	
		$bgcolor = "#FFFFFF";

		$this_page = "edit_thumb.php"; # hmm... 
?>

<span class="popup">
Choose which part of the image will compose your thumbnail:
</span>

<APPLET CODE="ImageCrop" WIDTH=460 HEIGHT=430 CODEBASE="java" ARCHIVE="ImageTools.jar">
  <PARAM NAME="type"   VALUE="application/x-java-applet;version=1.1.2">
  <PARAM NAME=bgcolor  VALUE="<?= $bgcolor ?>">
  <PARAM NAME=image    VALUE="<?= $photoURL ?>">
  <PARAM NAME=image_w  VALUE="<?= $image_w ?>">
  <PARAM NAME=image_h  VALUE="<?= $image_h ?>">
  <PARAM NAME=crop_x   VALUE="<?= $t_x ?>">
  <PARAM NAME=crop_y   VALUE="<?= $t_y ?>">
  <PARAM NAME=crop_w   VALUE="<?= $t_w ?>">
  <PARAM NAME=crop_h   VALUE="<?= $t_h ?>">
  <PARAM NAME=crop_to_size  VALUE="<?= $gallery->album->fields["thumb_size"] ?>">
</APPLET>

<? 
    	#-- we're not a go. abort! abort! ---
	} else { 
		error("no album / index specified");
	} 
} 
?>

</body>
</html>

