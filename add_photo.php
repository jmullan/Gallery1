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
require($GALLERY_BASEDIR . "init.php"); ?>
<?php
// Hack check
if (!$gallery->user->canAddToAlbum($gallery->album)) {
	exit;
}
?>
<html>
<head>
  <title><?php echo _("Add Photo") ?></title>
  <?php echo getStyleSheetLink() ?>

<script language="Javascript">
<!--
	function reloadPage() {
		document.count_form.submit();
		return false;
	}
// -->
</script>
</head>
<body dir="<?php echo $gallery->direction ?>">
<?php
if (isset($userfile_name) && fs_file_exists($userfile)) { ?>
<script language="Javascript">
	opener.showProgress();
</script>
<?php
	$tag = ereg_replace(".*\.([^\.]*)$", "\\1", $userfile_name);
        $tag = strtolower($tag); 
	if (get_magic_quotes_gpc()) {
		$caption=stripslashes($caption);    
		foreach ($extra_fields as $key => $value) {
			$extra_fields[$key] = stripslashes($value);
		}
	}
	processNewImage($userfile, $tag, $userfile_name, $caption, $setCaption, $extra_fields);
	$gallery->album->save();

	if ($temp_files) {
		/* Clean up the temporary url file */
		foreach ($temp_files as $tf => $junk) {
		    fs_unlink($tf);
		}
	}
?>
<p align="center">
<form>
	<input type="button" value="<?php echo _("Dismiss") ?>" onclick='parent.close()'>
</form>
</p>
<script language="Javascript">
	opener.hideProgressAndReload();
</script>

<?php
	reload();

}
else
{
?>

<p class="popuphead"><?php echo _("Add Photo") ?></p>
<?php
if (isset($userfile_name) && ! fs_file_exists($userfile)) {
	echo "<p>" . gallery_error(sprintf(_("The file %s does not exist"),
				"&quot;" . $userfile . "&quot;")) . "</p>";
}
// Note: file button is not labelled "Browse" in all browsers.  Eg Safari "Choose File"

?>
<span class="popup">
<?php echo _("Click the <b>Browse</b> button to locate a photo to upload.") ?>
</span>
<span class="admin">
<br>
&nbsp;&nbsp;(<?php echo _("Supported file types") ?>: <?php echo join(", ", acceptableFormatList()) ?>)
</span>

<br><br>

<?php echo makeFormIntro("add_photo.php",
			array("name" => "upload_form",
				"enctype" => "multipart/form-data",
				"method" => "POST")); ?>
<input type="hidden" name="max_file_size" value="10000000">
<table>
<tr><td>
<?php echo _("File") ?></td>
<td><input name="userfile" type="file" size=40></td></tr>
<td><?php echo _("Caption") ?></td><td> <textarea name="caption" rows=2 cols=40></textarea></td></tr>
<?php
foreach ($gallery->album->getExtraFields() as $field) {
        if (in_array($field, array_keys(automaticFieldsList())))
        {
                continue;
        }
        if ($field == "Title")
        {
        	print "<tr><td valign=top>Title</td><td>";
                print "<input type=text name=\"extra_fields[$field]\" value=\"\" size=\"40\">";
        }
	else
	{
        	print "<tr><td valign=top>$field</td><td>";
        	print "<textarea name=\"extra_fields[$field]\" rows=2 cols=40>";
        	print "</textarea>";
	}
        print "</td></tr>";
}
?>

</table>
<input type="checkbox" name="setCaption" checked value="1"><?php echo _("Use filename as caption if no caption is specified.") ?>
<br>
<center>
<input type="submit" value="<?php echo _("Upload Now") ?>">
<input type="button" value="<?php echo _("Cancel") ?>" onclick='parent.close()'>
</center>
</form>
<?php } ?>

</body>
</html>
