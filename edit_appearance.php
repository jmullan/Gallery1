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
// Hack check
if (!$user->canWriteToAlbum($album)) {
	exit;
}
	
if ($save) {
	$album->fields["title"] = $title;
	$album->fields["bgcolor"] = $bgcolor;
	$album->fields["textcolor"] = $textcolor;
	$album->fields["linkcolor"] = $linkcolor;
	$album->fields["font"] = $font;
	$album->fields["bordercolor"] = $bordercolor;
	$album->fields["border"] = $border;
	$album->fields["background"] = $background;
	$album->fields["thumb_size"] = $thumb_size;
	$album->fields["resize_size"] = $resize_size;
	$album->fields["returnto"] = $returnto;
	$album->save();
	reload();
}

?>
<html>
<head>
  <title>Album Properties</title>
  <link rel="stylesheet" type="text/css" href="<?= getGalleryStyleSheetName() ?>">
</head>
<body>

<center>

You can modify the appearance of your photo album here.

<form name="theform" action=edit_appearance.php method=POST>
<input type=hidden name="save" value=1>
<table>
<tr>
<td>Album Title</td>
<td><input type=text name="title" value=<?=$album->fields["title"]?>></td>
</tr>
<tr>
<td>Background Color</td>
<td><input type=text name="bgcolor" value=<?=$album->fields["bgcolor"]?>></td>
</tr>
<tr>
<td>Text Color</td>
<td><input type=text name="textcolor" value=<?=$album->fields["textcolor"]?>></td>
</tr>
<tr>
<td>Link Color</td>
<td><input type=text name="linkcolor" value=<?=$album->fields["linkcolor"]?>></td>
</tr>
<tr>
<td>Background Image (URL)</td>
<td><input type=text name="background" value=<?=$album->fields["background"]?>></td>
</tr>
<tr>
<td>Font</td>
<td><input type=text name="font" value="<?=$album->fields["font"]?>"></td>
</tr>
<tr>
<td>Borders</td>
<td><select name="border"><?= selectOptions($album, "border", array("off", 1, 2, 3, 4)) ?></select></td>
</tr>
<tr>
<tr>
<td>Thumbnail size</td>
<td><input type=text name="thumb_size" value="<?=$album->fields["thumb_size"]?>"></td>
</tr>
<tr>
<td>Auto-Resize</td>
<td><select name="resize_size"><?= selectOptions($album, "resize_size", array("off", 400, 500, 600, 700, 800)) ?></select></td>
</tr>
<tr>
<td>Border color</td>
<td><input type=text name="bordercolor" value=<?=$album->fields["bordercolor"]?>></td>
</tr>
<tr>
<td>Show <i>Return to</i> link</td>
<td><select name="returnto"><?= selectOptions($album, "returnto", array("yes", "no")) ?></select></td>
</tr>
<tr>
</table>

<br><br>

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

