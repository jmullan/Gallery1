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

list($boxes, $metaboxes) = getRequestVar(array('boxes','metaboxes'));

if (!isset($boxes)) {
    $boxes = 5;
}
if (!isset($metaboxes)) {
    $metaboxes = 0 ;
}
 
echo "\n\t<p>";
echo _("Click the <b>Browse</b> button to locate a photo to upload.");
echo '<br>';
echo _("Supported file types") ?>: <?php echo join(", ", acceptableFormatList());
echo '</p>';

if ($gallery->app->feature["zip"] || $gallery->app->rar) {
    echo '<p><b>'. _("Tip:") . '</b> ';
    echo _("Upload a compressed file (zip, rar, ace ...) full of photos and movies!");
    echo "</p>\n\t";
}

echo makeFormIntro("add_photos.php", array("name" => "count_form", "method" => "POST"), array('type' => 'popup')); ?>
<input type="hidden" name="mode" value="<?php echo $mode ?>"/>

<table>
<tr>
	<td><?php echo _("1. Select the number of files you want to upload:") ?></td>
	<td><select name="boxes" onChange="javascript:document.count_form.submit(); return false;">
<?php 
for ($i = 1; $i <= 10;  $i++) {
    $selected=($i == $boxes) ? 'selected' : '';
    echo "\t\t\t<option $selected value=\"$i\">$i</option>\n";
}
?>
	  </select>
	</td>
</tr>
<tr>
    <td><?php
echo '<b>'. _("Optional:") .'</b> ';
echo _("Select the number of meta data files you want to upload:");
?>
    </td>
    <td><select name="metaboxes" onChange="javascript:document.count_form.submit(); return false;">
<?php 
for ($i = 0  ; $i <= 10;  $i++) {
    $selected=($i == $metaboxes) ? 'selected' : '';
    echo "\t\t\t<option $selected value=\"$i\">$i</option>\n";
}
?>
    </select>
    </td>
</tr>
</table>

</form>

<?php echo makeFormIntro("save_photos.php",
	array("name" => "upload_form",
		"enctype" => "multipart/form-data",
		"method" => "POST"), array('type' => 'popup')); ?>
<table>
<tr>
	<td colspan="2"><?php echo _("2. Use the Browse button to find the photos on your computer") ?></td>
</tr>
<?php for ($i = 0; $i < $boxes;  $i++) { ?>
<tr>
	<td><?php echo _("File") ?></td>
		<td><input name="userfile[]" type="file" size="40"></td>
	</tr>
	<tr>
		<td><?php echo _("Caption") ?></td>
		<td><input name="usercaption[]" type="text" size="40"><br><br></td>
	</tr>
<?php }
	if ($metaboxes >0) {
		echo '<tr><td colspan="2">' . _("3. Use the Browse button to find the meta data files on your computer") . '</td></tr>';
		for ($i = 0; $i < $metaboxes;  $i++) {
			echo "\n<tr>";
			echo "\n\t<td>" . _("File") .'</td>';
			echo "\n\t<td>". '<input name="metafile[]" type="file" size="40"></td>';
			echo "\n</tr>";
		}
	}
?>

	</table><br>
<?php 
	echo _("If no caption is set, what would you like the captions to default to?");
	echo "\n<p>";
	include(dirname(__FILE__) .'/captionOptions.inc.php');

if (isset($gallery->app->watermarkDir)) {
    global $watermarkForm;
    $watermarkForm["askRecursive"] = 0;
    $watermarkForm["askPreview"] = 0;
    $watermarkForm["allowNone"] = 1;
    includeLayout('watermarkform.inc');
}
?>
	<p align="center">
	<input type="button" value="<?php echo _("Upload Now") ?>" onClick='parent.opener.showProgress(); document.upload_form.submit()'>
	<input type="submit" value="<?php echo _("Cancel") ?>" onclick='parent.close()'>
	</p>
	</form>