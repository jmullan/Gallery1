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
if ($save) {
	$album->fields[$field] = stripslashes($data);
	$album->save();
	dismissAndReload();
	return;
}

?>

<html>
<head>
  <title>Edit <?= $field ?></title>
  <link rel="stylesheet" type="text/css" href="<?= getGalleryStyleSheetName() ?>">
</head>
<body>

<center>
Edit the <?= $field ?> and click <b>Save</b> when you're done.

<form name="theform" action=edit_field.php method=POST>
<input type=hidden name="save" value=1>
<input type=hidden name="field" value="<?= $field ?>">
<textarea name="data" rows=5 cols=40>
<?= $album->fields[$field] ?>
</textarea>
<p>
<input type=submit name="submit" value="Save">
<input type=submit name="submit" value="Cancel" onclick='parent.close()'>
</form>

<script language="javascript1.2">
<!--   
// position cursor in top form field
document.theform.data.focus();
//-->
</script>

</body>
</html>
