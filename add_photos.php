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
<?
require('style.php');
if (!$boxes) {
	$boxes = 5;
}
	
?>

<center>

<b>Add Photos</b>
<br>
Click the <b>Browse</b> button to locate a photo to upload.
<? if ($app->feature["zip_support"]) { ?>
<br>
Tip:  Upload a ZIP file full of photos and movies!
<? } ?>
<br>
<font size=+0>(Supported file types: JPG, 
<? if ($app->feature["gif_support"]) { ?>
GIF, 
<? } ?>
PNG, AVI, MPG)</font>

<br>
<form enctype="multipart/form-data" action="save_photos.php" method=post>
<input type="hidden" name="max_file_size" value="10000000">
<? for ($i = 0; $i < $boxes;  $i++) { ?>
<br> <input name="userfile[]" type="file" size=50>
<? } ?>
<br> URL <input name="url">

<p>
<input type="submit" value="Send Files">
<input type=submit name="submit" value="Cancel" onclick='parent.close()'>
</form>

<? if ($boxes < 10) { ?>
<table bordercolor=black cellpadding=0 cellspacing=0 border=1><tr><td>
<table width=100% bgcolor=#9999CC>
<tr><td align=center>
<font face=arial size=+1>
<a href=add_photos.php?boxes=<?=$boxes+5?>>More boxes, please!</a>
<br>
<font size=2>
Warning! you'll lose what you've already entered
</font>
</td></tr>
</table>
<? } ?>

