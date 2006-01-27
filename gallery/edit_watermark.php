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
/**
 * This popup provides the possibility to put a watermark on a picture.
 * A preview can be viewed before.
 *
 * @package Item
 */

/**
 *
 */
require_once(dirname(__FILE__) . '/init.php');

list($index, $save, $preview, $previewFull) = 
	getRequestVar(array('index', 'save', 'preview', 'previewFull'));
list($wmName, $wmAlign, $wmAlignX, $wmAlignY, $wmSelect) = 
	getRequestVar(array('wmName', 'wmAlign', 'wmAlignX', 'wmAlignY', 'wmSelect'));

// Hack check
if (! $gallery->user->canWriteToAlbum($gallery->album) &&
  ! $gallery->album->getItemOwnerModify() &&
  ! $gallery->album->isItemOwner($gallery->user->getUid(), $index)) {
	echo _("You are not allowed to perform this action!");
	exit;
}


$photo = $gallery->album->getPhoto($index);
$err = '';

if (isset($save) || isset($preview)) {
    if (isset($wmAlign) && ($wmAlign > 0) && ($wmAlign < 12)) {
        if (isset($wmName) && !empty($wmName)) {
            if (isset($save)) {
                my_flush();
                set_time_limit($gallery->app->timeLimit);
                $gallery->album->watermarkPhoto($index, $wmName, "", $wmAlign,
                  isset($wmAlignX) ? $wmAlignX : 0,
                  isset($wmAlignY) ? $wmAlignY : 0,
                  0, 0, // Not a preview
                  isset($wmSelect) ? $wmSelect : 0
                );
                dismissAndReload();
                return;
            }
            else {
                $gallery->album->watermarkPhoto(
                  $index,
                  $wmName,
                  '',
                  $wmAlign,
                  isset($wmAlignX) ? $wmAlignX : 0,
                  isset($wmAlignY) ? $wmAlignY : 0,
                  1, // set as preview
                  isset($previewFull) ? $previewFull : 0
                );
            }
        } else {
            $err = _("Please select a watermark.");
        }
    } else {
        $err = _("Please select an alignment.");
    }
}

doctype();

?>
<html>
<head>
  <title><?php echo _("Edit Watermark") ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>" class="popupbody">
<div class="popuphead"><?php echo _("Edit Watermark") ?></div>
<div class="popup" align="center">
<p>
<?php
if (isset($preview)) {
    echo $gallery->album->getPreviewTag($index);
} else {
    echo $gallery->album->getThumbnailTag($index);
}
?>
</p>
<?php 

if (!empty($err)) {
    echo '<p class="error">'. $err . '</p>';
}


if ($photo->image->type == 'gif') {
    echo infoLine(_("Your image is a gif. Watermarking on animated gifs is currently not supported and will 'deface & unanimate' your picture."), 'notice');
}

echo makeFormIntro('edit_watermark.php', array('name' => 'theform'));
global $watermarkForm;
$watermarkForm["askRecursive"] = 0;
$watermarkForm["askPreview"] = 1;
$watermarkForm["allowNone"] = 0;
includeLayout ('watermarkform.inc');
?>
<p>
	<input type="hidden" name="index" value="<?php echo $index ?>">
	<input type="submit" name="save" value="<?php echo _("Save") ?>">
	<input type="submit" name="preview" value="<?php echo _("Preview") ?>">
	<input type="button" name="cancel" value="<?php echo _("Cancel") ?>" onclick='parent.close()'>
</p>
</form>

<script language="javascript1.2" type="text/JavaScript">
<!--
// position cursor in top form field
document.theform.cancel.focus();
//-->
</script>
</div>
<?php 
print gallery_validation_link("edit_watermark.php", false,
  array('index' => $index, 'set_albumName' => $gallery->album->fields["name"]));
?>
</body>
</html>
