<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2004 Bharat Mediratta
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
 *
 * $Id$
 */
?>

<?php
if (!isset($boxes)) {
	$boxes = 5;
}
?>

<p>
<span class="popup">
<?php echo _("Click the <b>Browse</b> button to locate a photo to upload.") ?>

<?php if ($gallery->app->feature["zip"]) { ?>
<br>
&nbsp;&nbsp;<?php echo _("Tip:  Upload a ZIP file full of photos and movies!") ?>
<?php } ?>
<br>
&nbsp;&nbsp;(<?php echo _("Supported file types") ?>: <?php echo join(", ", acceptableFormatList()) ?>)

<br><br>
<?php echo makeFormIntro("add_photos.php",
			array("name" => "count_form",
				"method" => "POST")); ?>
<?php echo _("1. Select the number of files you want to upload:") ?>
<select name="boxes" onChange="javascript:document.count_form.submit(); return false;">
<?php for ($i = 1; $i <= 10;  $i++) {
	echo "<option ";
        if ($i == $boxes) {
		echo "selected ";
	}
	echo "value=\"$i\">$i\n";

} ?>
</select>
<br>
<input type="hidden" name="mode" value="<?php echo $mode ?>"/>
</form>

<?php echo makeFormIntro("save_photos.php",
			array("name" => "upload_form",
				"enctype" => "multipart/form-data",
				"method" => "POST")); ?>
<?php echo _("2. Use the Browse button to find the photos on your computer") ?>
<input type="hidden" name="max_file_size" value="10000000">
<table>
<?php for ($i = 0; $i < $boxes;  $i++) { ?>
<tr><td>
<?php echo _("File") ?></td>
<td><input name="userfile[]" type="file" size=40></td></tr>
<td><?php echo _("Caption") ?></td><td> <input name="usercaption[]" type="text" size=40></td></tr>
<tr><td></td></tr> 
<?php } ?>
</table>
<input type=checkbox name=setCaption checked value="1"><?php echo _("Use filename as caption if no caption is specified.") ?>
<br>
<center>
<input type="button" value="<?php echo _("Upload Now") ?>" onClick='parent.opener.showProgress(); document.upload_form.submit()'>
<input type=submit value="<?php echo _("Cancel") ?>" onclick='parent.close()'>
</center>
</form>

</span>
</p>