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
?>
<html>
<head>
  <title>Sort Album</title>
  <?php echo getStyleSheetLink() ?>
</head>
<body>

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
Select your sorting criteria for this album below?
<br>
Warning:  This operation can't be undone.
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
    <td><input checked type="radio" name="sort" value="upload">&nbsp;By Upload Date</td>
  </tr>
  <tr>
    <td><input type="radio" name="sort" value="itemCapture">&nbsp;By Picture-Taken Date</td>
  </tr>
  <tr>
    <td><input type="radio" name="sort" value="filename">&nbsp;By Filename</td>
  </tr>
  <tr>
    <td><input type="radio" name="sort" value="click">&nbsp;By Number of Clicks</td>
  </tr>
  <tr>
    <td><input type="radio" name="sort" value="caption">&nbsp;By Caption</td>
  </tr>
  <tr>
    <td><input type="radio" name="sort" value="comment">&nbsp;By Number of Comments</td>
  </tr>
  <tr>
    <td><input type="radio" name="sort" value="random">&nbsp;Randomly</td>
  </tr>
  <tr>
    <td align=center>
<select name="order">
  <option value="0">Ascending</option>
  <option value="1">Descending</option>
</select>
    </td>
  </tr>
</table>
<br>
<input type=submit name=confirm value="Sort">
<input type=submit value="Cancel" onclick='parent.close()'>
</form>
<?php
	}
} else {
	gallery_error("no album specified");
}
?>

</body>
</html>
