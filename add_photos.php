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

<script language="Javascript">
<!--
	function reloadPage() {
		document.count_form.submit();
		return false;
	}
// -->
</script>


<b>Add Photos</b>
<br>
<font size=+0>
Click the <b>Browse</b> button to locate a photo to upload.
<? if ($app->feature["zip_support"]) { ?>
<br>
Tip:  Upload a ZIP file full of photos and movies!
<? } ?>
<br>
(Supported file types: JPG, 
GIF, 
PNG, AVI, MPG)

<p>
<form enctype="multipart/form-data" action="add_photos.php" method=post name="count_form">
1. Select the number of files you want to upload:
<select name="boxes" onChange='reloadPage()'>
<? for ($i = 1; $i <= 10;  $i++) {
	echo "<option ";
        if ($i == $boxes) {
		echo "selected ";
	}
	echo "value=\"$i\">$i\n";

} ?>
</select>
<br>
</form>

<form enctype="multipart/form-data" action="save_photos.php" method=post name="upload_form">
2. Use the Browse button to find the photos on your computer
<input type="hidden" name="max_file_size" value="10000000">
<? for ($i = 0; $i < $boxes;  $i++) { ?>
<br> <input name="userfile[]" type="file" size=50>
<? } ?>
<p>
<center>
<input type="submit" value="Upload Now">
<input type=submit name="submit" value="Cancel" onclick='parent.close()'>
</center>
</form>

<form enctype="multipart/form-data" action="save_photos.php" method=post name="uploadurl_form">
Or, upload all the images from this URL:
<input name="url">
<p>
<center>
<input type="submit" value="Submit URL">
<input type=submit name="submit" value="Cancel" onclick='parent.close()'>
</center>
</form>
</font>

