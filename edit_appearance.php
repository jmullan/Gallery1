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
 */
?>
<?php
// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
	print "Security violation\n";
	exit;
}
?>
<?php if (!isset($GALLERY_BASEDIR)) {
    $GALLERY_BASEDIR = '';
}
require($GALLERY_BASEDIR . 'init.php'); ?>
<?php
// Hack check
if (!$gallery->user->canWriteToAlbum($gallery->album)) {
	exit;
}
	
if ($save) {
    $gallery->album->fields["summary"] = $summary;
	$gallery->album->fields["title"] = $title;
	$gallery->album->fields["bgcolor"] = $bgcolor;
	$gallery->album->fields["textcolor"] = $textcolor;
	$gallery->album->fields["linkcolor"] = $linkcolor;
	$gallery->album->fields["font"] = $font;
	$gallery->album->fields["bordercolor"] = $bordercolor;
	$gallery->album->fields["border"] = $border;
	$gallery->album->fields["background"] = $background;
	$gallery->album->fields["thumb_size"] = $thumb_size;
	$gallery->album->fields["resize_size"] = $resize_size;
	$gallery->album->fields["returnto"] = $returnto;
	$gallery->album->fields["rows"] = $rows;
	$gallery->album->fields["cols"] = $cols;
	$gallery->album->fields["fit_to_window"] = $fit_to_window;
	$gallery->album->fields["use_fullOnly"] = $use_fullOnly;
	$gallery->album->fields["print_photos"] = $print_photos;
	$gallery->album->fields["use_exif"] = $use_exif;
	$gallery->album->fields["display_clicks"] = $display_clicks;
	$gallery->album->fields["public_comments"] = $public_comments;
	$gallery->album->save();

	if ($setNested) {
	
		$gallery->album->setNestedProperties();

	}

	reload();
}

?>
<html>
<head>
  <title>Album Properties</title>
  <?php echo getStyleSheetLink() ?>
</head>
<body>

<center>
Album Properties

<?php echo makeFormIntro("edit_appearance.php", 
			array("name" => "theform", 
				"method" => "POST")); ?>
<input type=hidden name="save" value=1>
<table>
<tr>
<td colspan="2">Album Summary</td>
</tr>
<tr>
<td colspan="2" align="left">
<textarea cols=50 rows=4 name="summary"><?php echo $gallery->album->fields["summary"] ?></textarea>
</td>
</tr>
<tr>
<td>Album Title</td>
<td><input type=text name="title" value="<?php echo htmlentities($gallery->album->fields["title"])?>"></td>
</tr>
<tr>
<td>Background Color</td>
<td><input type=text name="bgcolor" value="<?php echo $gallery->album->fields["bgcolor"]?>"></td>
</tr>
<tr>
<td>Text Color</td>
<td><input type=text name="textcolor" value="<?php echo $gallery->album->fields["textcolor"]?>"></td>
</tr>
<tr>
<td>Link Color</td>
<td><input type=text name="linkcolor" value="<?php echo $gallery->album->fields["linkcolor"]?>"></td>
</tr>
<tr>
<td>Background Image (URL)</td>
<td><input type=text name="background" value="<?php echo $gallery->album->fields["background"]?>"></td>
</tr>
<tr>
<td>Font</td>
<td><input type=text name="font" value="<?php echo $gallery->album->fields["font"]?>"></td>
</tr>
<tr>
<td>Borders</td>
<td><select name="border"><?php echo selectOptions($gallery->album, "border", array("off", 1, 2, 3, 4)) ?></select></td>
</tr>
<tr>
<td>Border color</td>
<td><input type=text name="bordercolor" value="<?php echo $gallery->album->fields["bordercolor"]?>"></td>
</tr>
<tr>
<td>Thumbnail size</td>
<td><input type=text name="thumb_size" value="<?php echo $gallery->album->fields["thumb_size"]?>"></td>
</tr>
<tr>
<td>Auto-Resize</td>
<td><select name="resize_size"><?php echo selectOptions($gallery->album, "resize_size", array("off", 400, 500, 600, 640, 700, 800, 1024)) ?></select></td>
</tr>
<tr>
<td>Show <i>Return to</i> link</td>
<td><select name="returnto"><?php echo selectOptions($gallery->album, "returnto", array("yes", "no")) ?></select></td>
</tr>
<tr>
<td>Rows</td>
<td>
 <select name="rows">
  <?php echo selectOptions($gallery->album, "rows", array(1, 2, 3, 4, 5, 6, 7, 8, 9)) ?>
 </select>
</td>
</tr>
<tr>
<td>Columns</td>
<td>
 <select name="cols">
  <?php echo selectOptions($gallery->album, "cols", array(1, 2, 3, 4, 5, 6, 7, 8, 9)) ?>
 </select>
</td>
</tr>
<tr>
<td>Auto fit-to-window for<br>images without a resized copy</td>
<td><select name="fit_to_window"><?php echo selectOptions($gallery->album, "fit_to_window", array("yes", "no")) ?></select></td>
</tr>
<tr>
<td>Offer visitors ability to specify<br>preference for full-size or resized images</td>
<td><select name="use_fullOnly"><?php echo selectOptions($gallery->album, "use_fullOnly", array("yes", "no")) ?></select></td>
</tr>
<tr>
<td>Which photo printing service<br>do you want to let visitors use?</td>
<td><select name="print_photos"><?php echo selectOptions($gallery->album, "print_photos", array("none", "shutterfly", "shutterfly without donation", "photoaccess", "fotokasten")) ?></select></td>
</tr>
<?php
if ($gallery->app->use_exif) {
?>
<tr>
<td>Display EXIF data?</td>
<td><select name="use_exif"><?php echo selectOptions($gallery->album, "use_exif", array("no", "yes")) ?></select></td>
</tr>
<?php
} // end if
?>
<tr>
<td>Display click counter for this album?</td>
<td><select name="display_clicks"><?php echo selectOptions($gallery->album, "display_clicks", array("yes", "no")) ?></select></td>
</tr>
<tr>
<td>Allow public commenting for photos in this album?</td>
<td><select name="public_comments"><?php echo selectOptions($gallery->album, "public_comments", array("no", "yes")) ?></select></td>
</tr>
</table>

<br>
<input type=checkbox name=setNested value="1">Apply values to nested Albums (except Album Title and Summary).
<br>
<br>
<input type=submit name="submit" value="Apply">
<input type=reset value="Undo">
<input type=submit name="submit" value="Close" onclick='parent.close()'>

</form>

<script language="javascript1.2">
<!--   
// position cursor in top form field
document.theform.title.focus();
//-->
</script>

</body>
</html>

