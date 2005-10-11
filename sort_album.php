<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2005 Bharat Mediratta
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
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * $Id$
 */
?>
<?php

require_once(dirname(__FILE__) . '/init.php');

list($sort, $order, $albumsFist) = getRequestVar(array('sort', 'order', 'albumsFirst'));

// Hack check
if (!$gallery->user->canWriteToAlbum($gallery->album)) {
	exit;
}

doctype();
?>
<html>
<head>
  <title><?php echo _("Sort Album") ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>" class="popupbody">

<?php
if ($gallery->session->albumName) {
	if (getRequestVar('confirm')) {
		if (!strcmp($sort,"random")) {
			$gallery->album->shufflePhotos();
			$gallery->album->save(array(i18n("Album resorted")));
			dismissAndReload();
			return;
		} else {
			$gallery->album->sortPhotos($sort,$order, $albumsFist);
			$gallery->album->save(array(i18n("Album resorted")));
			dismissAndReload();
			return;
		}
	} else {
?>
<div class="popuphead"><?php echo _("Sort Album"); ?></div>
<div class="popup" align="center">
<p>
<?php echo _("Select your sorting criteria for this album below") ?>
<br>
<b><?php echo _("Warning:  This operation can't be undone.") ?></b>
</p>

<p>
<?php
if ($gallery->album->getHighlight()) {
	print $gallery->album->getThumbnailTag($gallery->album->getHighlight());
}
?>
<br>
<?php
if (isset($gallery->album->fields['caption'])) {
	echo $gallery->album->fields['caption'];
}
echo makeFormIntro("sort_album.php");
?>

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
    <td align="center">
<?php echo _("Sort Order:"); ?>
    <select name="albumsFirst">
        <option value="NULL"><?php echo _("Just sort") ?></option>
        <option value="true"><?php echo _("Albums first") ?></option>
        <option value="false"><?php echo _("Photos/Movies first") ?></option>
    </select>
    <select name="order">
        <option value="0"><?php echo _("Ascending") ?></option>
        <option value="1"><?php echo _("Descending") ?></option>
    </select>
    </td>
    
  </tr>
</table>
<br>
<input type="submit" name="confirm" value="<?php echo _("Sort") ?>">
<input type="button" name="cancel" value="<?php echo _("Cancel") ?>" onclick='parent.close()'>
</form>
<?php
	}
} else {
	echo gallery_error(_("no album specified"));
}
?>
<?php print gallery_validation_link("sort_album.php"); ?>
</div>
</body>
</html>
