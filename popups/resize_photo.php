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

require_once(dirname(dirname(__FILE__)) . '/init.php');

list($index, $manual, $resize, $resize_file_size, $remove_resized, $resizeRecursive) = 
  getRequestVar(array('index', 'manual', 'resize', 'resize_file_size', 'remove_resized', 'resizeRecursive'));

// Hack check
if (! $gallery->user->canWriteToAlbum($gallery->album) &&
  ! $gallery->album->getItemOwnerModify() &&
  ! $gallery->album->isItemOwner($gallery->user->getUid(), $index)) {
	echo gTranslate('core', "You are not allowed to perform this action!");
	exit;
}

printPopupStart(gTranslate('core', "Resize Photo"));

$all = !strcmp($index, "all");
if ($gallery->session->albumName && isset($index)) {
	if (isset($manual) && $manual > 0) {
		$resize = $manual;
	}
	if (!empty($remove_resized)) {
		$resize = 'orig';
	}
	if (!empty($resize)) {
		if ($index === 'all') {
			$gallery->album->resizeAllPhotos($resize,$resize_file_size,"", $resizeRecursive);
		} else {
			echo("<br> ". gTranslate('core', "Resizing 1 photo..."));
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
<p><?php echo gTranslate('core', "This will resize your intermediate photos so that the longest side of the photo is equal to the target size below and the filesize will be close to the chosen size."); ?>
</p>

<?php echo makeFormIntro('resize_photo.php', 
	array('name' => 'resize_photo'),
	array('type' => 'popup'));
?>

<h3><?php echo $all ? gTranslate('core', "What is the target size for all the intermediate photos in this album?") : gTranslate('core', "What is the target size for the intermediate version of this photo?");?></h3>
<?php
		if (!$all) {
			echo "\n<p>";
			echo $gallery->album->getThumbnailTag($index);
			echo "\n</p>";
		}
?>

<table style="border: 1px solid; padding:10px 20px;">
<tr>
	<td><?php echo gTranslate('core', "Target filesize"); ?></td>
	<td><input type="text" size="4" name="resize_file_size" value="<?php print $gallery->album->fields["resize_file_size"] ?>" >  kbytes</td>
</tr>
<tr>
	<td style="vertical-align: middle"><?php print gTranslate('core', "Maximum side length in pixels") ?></td>
	<td><br>
	<table>
	<?php 
		$choices=array(1280,1024,700,800,640,600,500,400);
		for ($i = 0; $i<count($choices); $i = $i+2) {
			echo "\n\t<tr>";
			echo "\n\t\t". '<td style="white-space:nowrap"><input type="radio" name="resize" value="' . $choices[$i] .'" id="size_' . $choices[$i] .'">'. '<label for="size_' . $choices[$i] .'">'. $choices[$i] .'</label></td>';
			echo "\n\t\t". '<td style="white-space:nowrap"><input type="radio" name="resize" value="' .$choices[$i+1].'" id="size_' .$choices[$i+1].'">'. '<label for="size_' .$choices[$i+1].'">'.$choices[$i+1].'</label></td>';
			echo "\n\t</tr>\n";
		}
?>
	<tr>
		<td colspan="2">
			<input id="none" type="radio" name="resize" value="manual">
			<input type="text" size="5" name="manual" onFocus="document.getElementById('none').checked=true;"><label for="none"> <?php echo gTranslate('core', "(manual value)"); ?></label>
		</td>
	</tr>


	</table>
	</td>
</tr>
</table>

<?php
     if ($index === 'all') { ?>
<p>
	<?php echo gTranslate('core', "Apply to nested albums ?"); ?>
	<input type="checkbox" name="resizeRecursive" value="false">
</p>
<?php } ?>
<p>
	<input type="hidden" name="index" value="<?php echo $index ?>">
	<?php echo gSubmit('remove_resized', gTranslate('core', "Get _rid of resized")); ?>
	<?php echo gTranslate('core', "(Use only the original picture)"); ?>

</p>
<br>

<?php echo gSubmit('change_size', gTranslate('core', "Change _Size")) ?>
<?php echo gButton('cancel', gTranslate('core', "_Cancel"), 'parent.close()'); ?>

</form>

<?php
	}
} else {
	echo gallery_error(gTranslate('core', "no album / index specified"));
}
?>
</div>

</body>
</html>
