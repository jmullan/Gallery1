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
	print _("Security violation"). "\n";
	exit;
}
?>
<?php if (!isset($GALLERY_BASEDIR)) {
    $GALLERY_BASEDIR = './';
}
require($GALLERY_BASEDIR . 'init.php'); ?>
<?php
// Hack check
if (!$gallery->user->canChangeTextOfAlbum($gallery->album)) {
	exit;
}
	
if (isset($save)) {
	if (!strcmp($field, 'title')) {
		$data = removeTags($data);
	}
	$gallery->album->fields[$field] = stripslashes($data);
	$gallery->album->save();
	dismissAndReload();
	return;
}

?>

<html>
<head>
  <title><?php echo sprintf(_("Edit %s"), _($field)) ?></title>
  <?php echo getStyleSheetLink() ?>
</head>
<body dir="<?php echo $gallery->direction ?>">

<center>
<?php echo sprintf(_("Edit the %s and click %s when you're done"),
		_($field), '<b>' . _("Save") . '</b>') ?>

<?php echo makeFormIntro("edit_field.php", array(
	"name" => "theform",
	 "method" => "POST")); 
?>
<input type="hidden" name="field" value="<?php echo $field ?>">
<textarea name="data" rows="5" cols="40">
<?php echo $gallery->album->fields[$field] ?>
</textarea>
<p>
<input type="submit" name="save" value="<?php echo _("Save") ?>">
<input type="button" name="cancel" value="<?php echo _("Cancel") ?>" onclick='parent.close()'>
</form>

<script language="javascript1.2">
<!--   
// position cursor in top form field
document.theform.data.focus();
//-->
</script>

</body>
</html>
