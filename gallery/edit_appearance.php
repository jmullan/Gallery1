<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2003 Bharat Mediratta
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
// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
	print _("Security violation") ."\n";
	exit;
}
?>
<?php if (!isset($GALLERY_BASEDIR)) {
    $GALLERY_BASEDIR = './';
}
require($GALLERY_BASEDIR . 'init.php'); ?>
<?php
// Hack check
if (!$gallery->user->canWriteToAlbum($gallery->album)) {
	exit;
}
	
if (isset($save)) {
	if (get_magic_quotes_gpc()) {
		$gallery->album->fields["summary"] = stripslashes($summary);
		$gallery->album->fields["title"] = stripslashes($title);
	} else {
		$gallery->album->fields["summary"] = $summary;
		$gallery->album->fields["title"] = $title;
	}
	$gallery->album->fields["bgcolor"] = $bgcolor;
	$gallery->album->fields["textcolor"] = $textcolor;
	$gallery->album->fields["linkcolor"] = $linkcolor;
	$gallery->album->fields["font"] = $font;
	$gallery->album->fields["bordercolor"] = $bordercolor;
	$gallery->album->fields["border"] = $border;
	$gallery->album->fields["background"] = $background;
	$gallery->album->fields["thumb_size"] = $thumb_size;
	$gallery->album->fields["resize_size"] = $resize_size;
	$gallery->album->fields["resize_file_size"] = $resize_file_size;
	$gallery->album->fields["returnto"] = $returnto;
	$gallery->album->fields["rows"] = $rows;
	$gallery->album->fields["cols"] = $cols;
	$gallery->album->fields["fit_to_window"] = $fit_to_window;
	$gallery->album->fields["use_fullOnly"] = $use_fullOnly;
	$gallery->album->fields["print_photos"] = $print_photos;
	$gallery->album->fields["use_exif"] = $use_exif;
	$gallery->album->fields["display_clicks"] = $display_clicks;
	$gallery->album->fields["public_comments"] = $public_comments;
	$gallery->album->fields["item_owner_modify"] = $item_owner_modify;
	$gallery->album->fields["item_owner_delete"] = $item_owner_delete;
	$gallery->album->fields["item_owner_display"] = $item_owner_display;
	$gallery->album->fields["add_to_beginning"] = $add_to_beginning;
	$gallery->album->save();

	if ($setNested) {
	
		$gallery->album->setNestedProperties();

	}

	reload();
}

?>
<html>
<head>
  <title><?php echo _("Album Properties") ?></title>
  <?php echo getStyleSheetLink() ?>
</head>
<body dir=<?php echo $gallery->direction ?>>

<center>
<?php echo _("Album Properties") ?>

<?php echo makeFormIntro("edit_appearance.php", 
			array("name" => "theform", 
				"method" => "POST")); ?>
<input type=hidden name="save" value=1>
<table>
<tr>
<td colspan="2"><?php echo _("Album Summary") ?></td>
</tr>
<tr>
<td colspan="2" align="left">
<textarea cols=50 rows=4 name="summary"><?php echo $gallery->album->fields["summary"] ?></textarea>
</td>
</tr>
<tr>
<td><?php echo _("Album Title") ?></td>
<td><input type=text name="title" value="<?php echo htmlentities($gallery->album->fields["title"]) ?>"></td>
</tr>
<tr>
<td><?php echo _("Background Color") ?></td>
<td><input type=text name="bgcolor" value="<?php echo $gallery->album->fields["bgcolor"] ?>"></td>
</tr>
<tr>
<td><?php echo _("Text Color") ?></td>
<td><input type=text name="textcolor" value="<?php echo $gallery->album->fields["textcolor"] ?>"></td>
</tr>
<tr>
<td><?php echo _("Link Color") ?></td>
<td><input type=text name="linkcolor" value="<?php echo $gallery->album->fields["linkcolor"] ?>"></td>
</tr>
<tr>
<td><?php echo _("Background Image") ?> (URL)</td>
<td><input type=text name="background" value="<?php echo $gallery->album->fields["background"] ?>"></td>
</tr>
<tr>
<td><?php echo _("Font") ?></td>
<td><input type=text name="font" value="<?php echo $gallery->album->fields["font"] ?>"></td>
</tr>
<tr>
<td><?php echo _("Borders") ?></td>
<?php _("off") ?>
<td><select name="border"><?php echo selectOptions($gallery->album, "border", array("off", 1, 2, 3, 4)) ?></select></td>
</tr>
<tr>
<td><?php echo _("Border color") ?></td>
<td><input type=text name="bordercolor" value="<?php echo $gallery->album->fields["bordercolor"] ?>"></td>
</tr>
<tr>
<td><?php echo _("Thumbnail size") ?></td>
<td><input type=text name="thumb_size" value="<?php echo $gallery->album->fields["thumb_size"] ?>"></td>
</tr>
<tr>
<td><?php echo _("Auto-Resize") ?></td>
<td><select name="resize_size"><?php echo selectOptions($gallery->album, "resize_size", array("off", 400, 500, 600, 640, 700, 800, 1024)) ?></select></td>
</tr>
<tr>
<td><?php echo _("Auto-Resize file size kilobytes (0 or blank for no size restriction)") ?></td>
<td><input type=text name="resize_file_size" value="<?php echo $gallery->album->fields["resize_file_size"] ?>"></td>
</tr>
<tr>
<td><?php echo _("Show <i>Return to</i> link") ?></td>
<?php _("yes"); _("no"); ?>
<td><select name="returnto"><?php echo selectOptions($gallery->album, "returnto", array("yes", "no")) ?></select></td>
</tr>
<tr>
<td><?php echo _("Rows") ?></td>
<td>
 <select name="rows">
  <?php echo selectOptions($gallery->album, "rows", array(1, 2, 3, 4, 5, 6, 7, 8, 9)) ?>
 </select>
</td>
</tr>
<tr>
<td><?php echo _("Columns") ?></td>
<td>
 <select name="cols">
  <?php echo selectOptions($gallery->album, "cols", array(1, 2, 3, 4, 5, 6, 7, 8, 9)) ?>
 </select>
</td>
</tr>
<tr>
<td><?php echo _("Auto fit-to-window for<br>images without a resized copy") ?></td>
<td><select name="fit_to_window"><?php echo selectOptions($gallery->album, "fit_to_window", array("yes", "no")) ?></select></td>
</tr>
<tr>
<td><?php echo _("Offer visitors ability to specify<br>preference for full-size or resized images") ?></td>
<td><select name="use_fullOnly"><?php echo selectOptions($gallery->album, "use_fullOnly", array("yes", "no")) ?></select></td>
</tr>
<tr>
<td><?php echo _("Which photo printing service<br>do you want to let visitors use?") ?></td>
<?php _("none"); _("shutterfly without donation"); ?>
<td><select name="print_photos"><?php echo selectOptions($gallery->album, "print_photos", array("none", "photoaccess", "fotokasten", "shutterfly", "shutterfly without donation")) ?></select></td>
</tr>
<?php
if ($gallery->app->use_exif) {
?>
<tr>
<td><?php echo _("Display EXIF data?") ?></td>
<td><select name="use_exif"><?php echo selectOptions($gallery->album, "use_exif", array("no", "yes")) ?></select></td>
</tr>
<?php
} // end if
?>
<tr>
<td><?php echo _("Display click counter for this album?") ?></td>
<td><select name="display_clicks"><?php echo selectOptions($gallery->album, "display_clicks", array("yes", "no")) ?></select></td>
</tr>
<tr>
<td><?php echo _("Display owners name with caption") ?></td>
<td><select name="item_owner_display"><?php echo selectOptions($gallery->album, "item_owner_display", array("yes", "no")) ?></select></td>
</tr>
<tr>
<td><?php echo _("Allow item owners to modify their images") ?></td>
<td><select name="item_owner_modify"><?php echo selectOptions($gallery->album, "item_owner_modify", array("yes", "no")) ?></select></td>
</tr>
<tr>
<td><?php echo _("Allow item owners to delete their images") ?></td>
<td><select name="item_owner_delete"><?php echo selectOptions($gallery->album, "item_owner_delete", array("yes", "no")) ?></select></td>
</tr>
<tr>
<td><?php echo _("Add new items at beginning of album") ?></td>
<td><select name="add_to_beginning"><?php echo selectOptions($gallery->album, "add_to_beginning", array("yes", "no")) ?></select></td>
</tr>
<tr>
<td><?php echo _("Allow public commenting for photos in this album?") ?></td>
<td><select name="public_comments"><?php echo selectOptions($gallery->album, "public_comments", array("no", "yes")) ?></select></td>
</tr>
</table>

<br>
<input type=checkbox name=setNested value="1"><?php echo _("Apply values to nested Albums (except Album Title and Summary).") ?>
<br>
<br>
<input type="submit" name="apply" value="<?php echo _("Apply") ?>">
<input type=reset value="<?php echo _("Undo") ?>">
<input type="button" name="close" value="<?php echo _("Close") ?>" onclick="parent.close()">

</form>

<script language="javascript1.2">
<!--   
// position cursor in top form field
document.theform.title.focus();
//-->
</script>

</body>
</html>

