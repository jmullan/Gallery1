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
$all = !strcmp($index, "all");
if ($albumName && isset($index)) {
	if ($resize) {
		if (!strcmp($index, "all")) {
			$np = $album->numPhotos();
			echo("<br> Resizing $np photos...");
			my_flush();
			for ($i = 1; $i <= $np; $i++) {
				echo("<br> Processing image $i...");
				my_flush();
				set_time_limit(90);
				$album->resizePhoto($i, $resize);
			}
		} else {
			echo("<br> Resizing 1 photo...");
			my_flush();
			set_time_limit(90);
			$album->resizePhoto($index, $resize);
		}
		$album->save();
		dismissAndReload();
		return;
	} else {
?>

<center>
<font size=+1>Resizing photos</a>
<br>
This will resize your photos so that the longest side of the 
photo is equal to the target size below.  

<p>

What is the target size for <?= $all ? "all the photos in this album" : "this photo" ?>?
<br>
<form>
<input type=hidden name=index value=<?=$index?>>
<input type=submit name=resize value="Original Size">
<input type=submit name=resize value="800">
<input type=submit name=resize value="700">
<input type=submit name=resize value="600">
<input type=submit name=resize value="500">
<input type=submit name=resize value="400">
<input type=submit value="Cancel" onclick='parent.close()'>
</form>

<p>
<?
if (!$all) {
	echo $album->getThumbnailTag($index);
} 
?>

<?
	}
} else {
	error("no album / index specified");
}
?>







