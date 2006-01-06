<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2006 Bharat Mediratta
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

list($index, $manual, $resize, $resize_file_size, $remove_resized, $resizeRecursive) = 
  getRequestVar(array('index', 'manual', 'resize', 'resize_file_size', 'remove_resized', 'resizeRecursive'));

// Hack check
if (! $gallery->user->canWriteToAlbum($gallery->album) &&
  ! $gallery->album->getItemOwnerModify() &&
  ! $gallery->album->isItemOwner($gallery->user->getUid(), $index)) {
	echo _("You are not allowed to perform this action!");
	exit;
}

doctype();
?>
<html>
<head>
  <title><?php echo _("Resize Photo") ?></title>
  <?php common_header(); ?>
  <style>
	.nowrap { white-space:nowrap; }
  </style>
</head>
<body dir="<?php echo $gallery->direction ?>" class="popupbody">
<div class="popuphead"><?php echo _("Resizing photos") ?></div>
<div class="popup" align="center">
<?php
$all = !strcmp($index, "all");
if ($gallery->session->albumName && isset($index)) {
	if (isset($manual) && $manual > 0) {
		$resize = $manual;
	}
	if (!empty($remove_resized)) {
		$resize = 'orig';
	}
	if (!empty($resize)) {
		if (!strcmp($index, "all")) {
			$gallery->album->resizeAllPhotos($resize,$resize_file_size,"", $resizeRecursive);
		} else {
			echo("<br> ". _("Resizing 1 photo..."));
			my_flush();
			set_time_limit($gallery->app->timeLimit);
			$gallery->album->resizePhoto($index, $resize, $resize_file_size);
		}
		$gallery->album->save(array(i18n("Images resized to %s pixels, %s kbytes"),
					$resize, $resize_file_size));

		dismissAndReload();
		return;
	} else {
?>
<p><?php echo _("This will resize your intermediate photos so that the longest side of the photo is equal to the target size below and the filesize will be close to the chosen size."); ?>
</p>

<?php echo makeFormIntro("resize_photo.php", 
			array("name" => "resize_photo"),
			array("type" => "popup"));
?>

<h3><?php echo $all ? _("What is the target size for all the intermediate photos in this album?") : _("What is the target size for the intermediate version of this photo?");?></h3>
<?php
		if (!$all) {
			echo "\n<p>";
			echo $gallery->album->getThumbnailTag($index);
			echo "\n</p>";
		}
?>

<table style="border-width:1px; border-style:solid; padding:10px; padding-left:20px; padding-right:20px" class="popuptd">
<tr>
	<td><?php echo _("Target filesize"); ?></td>
	<td><input type="text" size="4" name="resize_file_size" value="<?php print $gallery->album->fields["resize_file_size"] ?>" >  kbytes</td>
</tr>
<tr>
	<td valign="middle"><?php print _("Maximum side length in pixels") ?></td>
	<td><br>
	<table border="0" class="popuptd">
	<?php 
		$choices=array(1280,1024,700,800,640,600,500,400);
		for ($i=0; $i<count($choices); $i=$i+2) {
			echo "\n\t<tr>";
			echo "\n\t\t". '<td class="nowrap"><input type="radio" name="resize" value="' . $choices[$i] .'" id="size_' . $choices[$i] .'">'. '<label for="size_' . $choices[$i] .'">'. $choices[$i] .'</label></td>';
			echo "\n\t\t". '<td class="nowrap"><input type="radio" name="resize" value="' .$choices[$i+1].'" id="size_' .$choices[$i+1].'">'. '<label for="size_' .$choices[$i+1].'">'.$choices[$i+1].'</label></td>';
			echo "\n\t</tr>\n";
		}
?>
	<tr>
		<td colspan="2">
			<input id="none" type="radio" name="resize" value="manual">
			<input type="text" size="5" name="manual" onFocus="document.getElementById('none').checked=true;"><label for="none"> <?php echo _("(manual value)"); ?></label>
		</td>
	</tr>


	</table>
	</td>
</tr>
</table>

<?php
     if (!strcmp($index, "all")) { ?>
<p>
	<?php echo _("Apply to nested albums ?"); ?>
	<input type="checkbox" name="resizeRecursive" value="false">
</p>
<?php } ?>
<p>
	<input type="hidden" name="index" value="<?php echo $index ?>">
	<input type="submit" name="remove_resized" value="<?php echo _("Get rid of resized") ?>">
	<?php echo _("(Use only the original picture)"); ?>

</p>
<br>

<input type="submit" name="change_size" value="<?php echo _("Change Size") ?>">
<input type="button" value="<?php echo _("Cancel") ?>" onclick="parent.close()">

</form>

<?php
	}
} else {
	echo gallery_error(_("no album / index specified"));
}
?>
</div>
<?php print gallery_validation_link("resize_photo.php", true, array('index' => $index)); ?>
</body>
</html>
