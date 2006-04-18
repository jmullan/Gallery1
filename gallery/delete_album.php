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

require_once(dirname(__FILE__) . '/init.php');

list($formaction, $guid) = getRequestVar(array('formaction', 'guid'));

// Hack check
if (!$gallery->user->canDeleteAlbum($gallery->album)) {
	echo _("You are not allowed to perform this action!");
	exit;
}


if (!empty($formaction) && $formaction == 'delete') {
    if ($guid == $gallery->album->fields['guid']) {
        $gallery->album->delete();
    }

    doctype();
    echo "\n<html>";
    dismissAndReload();
    return;
}

printPopupStart(_("Delete Album"));
if ($gallery->album) {
    echo _("Do you really want to delete this album?"); ?>
<p>
<b><?php echo $gallery->album->fields["title"] ?></b>
<?php
    echo "\n<br>";
    if ($gallery->album->numPhotos(1)) {
        echo $gallery->album->getHighlightTag();
    }
?>
</p>
<?php echo makeFormIntro("delete_album.php", 
	array('name' => 'deletealbum_form', 'onsubmit' => 'deletealbum_form.deleteButton.disabled = true;'),
 	array("type" => "popup"));
?>
<input type="hidden" name="guid" value="<?php echo $gallery->album->fields['guid']; ?>">
<input type="hidden" name="formaction" value="">
<input type="submit" name="deleteButton" value="<?php echo _("Delete") ?>" onclick="deletealbum_form.formaction.value='delete'" class="g-button">
<input type="button" name="cancel" value="<?php echo _("Cancel") ?>" onclick="parent.close()" class="g-button">
</form>
<?php
} else {
	echo gallery_error(_("no album specified"));
}
?>
</div>

<?php print gallery_validation_link("delete_album.php"); ?>
</body>
</html>
