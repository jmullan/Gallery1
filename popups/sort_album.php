<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2006 Bharat Mediratta
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

require_once(dirname(dirname(__FILE__)) . '/init.php');

list($sort, $order, $albumsFirst) = getRequestVar(array('sort', 'order', 'albumsFirst'));

// Hack check
if (!$gallery->user->canWriteToAlbum($gallery->album)) {
	exit;
}

printPopupStart(gTranslate('core', "Sort Album"));

if ($gallery->session->albumName) {
	if (getRequestVar('confirm')) {
		if (!strcmp($sort,"random")) {
			$gallery->album->shufflePhotos();
			$gallery->album->save(array(i18n("Album resorted")));
			dismissAndReload();
			return;
		} else {
			$gallery->album->sortPhotos($sort,$order, $albumsFirst);
			$gallery->album->save(array(i18n("Album resorted")));
			dismissAndReload();
			return;
		}
	} else {
?>

<p>
<?php echo gTranslate('core', "Select your sorting criteria for this album below") ?>
<br>
<b><?php echo gTranslate('core', "Warning:  This operation can't be undone.") ?></b>
</p>

<?php
$highlightIndex = $gallery->album->getHighlight();

if ($highlightIndex != null) {
	print $gallery->album->getThumbnailTag($highlightIndex);
}

echo "\n<br>";

if (isset($gallery->album->fields['caption'])) {
	echo $gallery->album->fields['caption'];
}

echo "\n<br>";

echo makeFormIntro('sort_album.php', array(), array('type' => 'popup'));
?>

<table>
  <tr>
    <td><input checked type="radio" name="sort" value="upload"><?php echo gTranslate('core', "By Upload Date") ?></td>
  </tr>
  <tr>
    <td><input type="radio" name="sort" value="itemCapture"><?php echo gTranslate('core', "By Picture-Taken Date") ?></td>
  </tr>
  <tr>
    <td><input type="radio" name="sort" value="filename"><?php echo gTranslate('core', "By Filename") ?></td>
  </tr>
  <tr>
    <td><input type="radio" name="sort" value="click"><?php echo gTranslate('core', "By Number of Clicks") ?></td>
  </tr>
  <tr>
    <td><input type="radio" name="sort" value="caption"><?php echo gTranslate('core', "By Caption") ?></td>
  </tr>
  <tr>
    <td><input type="radio" name="sort" value="comment"><?php echo gTranslate('core', "By Number of Comments") ?></td>
  </tr>
  <tr>
    <td><input type="radio" name="sort" value="random"> <?php echo gTranslate('core', "Randomly") ?></td>
  </tr>
  <tr>
    <td align="center">
<?php echo gTranslate('core', "Sort Order:"); ?>
    <select name="albumsFirst">
        <option value=""><?php echo gTranslate('core', "Just sort") ?></option>
        <option value="1"><?php echo gTranslate('core', "Albums first") ?></option>
        <option value="-1"><?php echo gTranslate('core', "Photos/Movies first") ?></option>
    </select>
    <select name="order">
        <option value="1"><?php echo gTranslate('core', "Ascending") ?></option>
        <option value="-1"><?php echo gTranslate('core', "Descending") ?></option>
    </select>
    </td>

  </tr>
</table>
<br>
<?php echo gSubmit('confirm', gTranslate('core', "_Sort")); ?>
<?php echo gButton('cancel', gTranslate('core', "_Cancel"), 'parent.close()'); ?>
</form>
<?php
	}
} else {
	echo gallery_error(gTranslate('core', "no album specified"));
}
?>
</div>

</body>
</html>
