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
    $GALLERY_BASEDIR = '';
}
require($GALLERY_BASEDIR . 'init.php'); ?>
<?php
// Hack check
if (!$gallery->user->canWriteToAlbum($gallery->album)) {
	exit;
}
?>
<html>
<head>
  <title><?php echo _("Sort Album") ?></title>
  <?php echo getStyleSheetLink() ?>
</head>
<body dir=<?php echo $gallery->direction ?>>

<?php
if ($gallery->session->albumName) {
	if ($confirm) {
		if (!strcmp($sort,"random")) {
			$gallery->album->shufflePhotos();
			$gallery->album->save();
			dismissAndReload();
			return;
		} else {
			$gallery->album->sortPhotos($sort,$order);
			$gallery->album->save();
			dismissAndReload();
			return;
		}
	} else {
?>

<center>
<?php echo _("Select your sorting criteria for this album below?") ?>
<br>
<?php echo _("Warning:  This operation can't be undone.") ?>
<br>
<br>

<p>
<?php
if ($gallery->album->getHighlight()) {
	print $gallery->album->getThumbnailTag($gallery->album->getHighlight());
}
?>
<br>
<?php echo $gallery->album->fields["caption"] ?>

<?php echo makeFormIntro("sort_album.php"); ?>
<table>
  <tr>
<td><input checked type="radio" name="sort" value="upload"><?php echo _("By Upload Date") ?></td>
  </tr>
  <tr>
    <td><input type="radio" name="sort" value="itemCapture"><?php echo _("By Picture-Taken Date") ?></td>
  </tr>
  <tr>
    <td><input type="radio" name="sort" value="filename"><?php echo _("By Filename") ?></td>
  </tr>
  <tr>
    <td><input type="radio" name="sort" value="click"><?php echo _("By Number of Clicks") ?></td>
  </tr>
  <tr>
    <td><input type="radio" name="sort" value="caption"><?php echo _("By Caption") ?></td>
  </tr>
  <tr>
    <td><input type="radio" name="sort" value="comment"><?php echo _("By Number of Comments") ?></td>
  </tr>
  <tr>
    <td><input type="radio" name="sort" value="random"> <?php echo _("Randomly") ?></td>
  </tr>
  <tr>
    <td align=center>
<select name="order">
  <option value="0"><?php echo _("Ascending") ?></option>
  <option value="1"><?php echo _("Descending") ?></option>
</select>
    </td>
  </tr>
</table>
<br>
<input type=submit name=confirm value="<?php echo _("Sort") ?>">
<input type=submit value="<?php echo _("Cancel") ?>" onclick='parent.close()'>
</form>
<?php
	}
} else {
	gallery_error(_("no album specified"));
}
?>

</body>
</html>
