<?
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000 Bharat Mediratta
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
<? require('style.php'); ?>

<?
if ($albumName && isset($index)) {
	$numPhotos = $album->numPhotos(1);
	if (isset($newIndex)) {
		$album->movePhoto($index, $newIndex);
		$album->save();
		dismissAndReload();
		return;
	} else {
?>

<center>
Select the new location of photo #<?=$index+1?>:
<form>
<input type=hidden name="index" value="<?=$index?>">
<select name="newIndex">
<?
for ($i = 1; $i <= $numPhotos; $i++) {
	$sel = "";
	if ($i == $index+1) {
		$sel = "selected";
	} 
	$j = $i - 1;
	echo "<option value=$j $sel> $i";
}
?>
<input type=submit value="Move it!">
<input type=submit name="submit" value="Cancel" onclick='parent.close()'>
</form>

<p>
<?= $album->getThumbnailTag($index) ?>

<?
	}
} else {
	error("no album / index specified");
}
?>
