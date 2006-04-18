<?php
/*
   $Id$

 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2006 Bharat Mediratta
 *
 * This file Copyright (C) 2003-2004 Joan McGalliard
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
 */
?>
<?php

require_once(dirname(__FILE__) . '/init.php');

list($id, $index, $confirm) = getRequestVar(array('id', 'index', 'confirm'));

if (isset($id)) {
        $index = $gallery->album->getPhotoIndex($id);
}

// Hack check
if (!$gallery->user->canDeleteFromAlbum($gallery->album) && !$gallery->album->isItemOwner($gallery->user, $index)) {
	echo _("You are not allowed to perform this action!");
	exit;
}

printPopupStart(_("Reset Voting"));

if (!empty($confirm)) {
	$gallery->album->fields["votes"]=array();
	$gallery->album->save(array(i18n("All votes removed")));
	dismissAndReload();
	return;
}
?>

<p>
<?php echo sprintf(_("Do you really want to remove all votes from album <b>%s</b>?"), $gallery->album->fields['title']) ?>
</p>

<?php echo makeFormIntro("reset_votes.php"); ?>
<input type="submit" name="confirm" value="<?php echo _("Remove Votes") ?>" class="g-button">
<input type="submit" value="<?php echo _("Cancel") ?>" onclick="parent.close()" class="g-button">
</form>
</div>

<?php print gallery_validation_link("reset_votes.php"); ?>
</body>
</html>
