<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
 * This file by Joan McGalliard.
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

require_once(dirname(dirname(__FILE__)) . '/init.php');

list($id, $index, $confirm) = getRequestVar(array('id', 'index', 'confirm'));

if (isset($id)) {
		$index = $gallery->album->getPhotoIndex($id);
}

printPopupStart(gTranslate('core', "Reset Voting"));

// Hack check
if (!$gallery->user->canDeleteFromAlbum($gallery->album) &&
    !$gallery->album->isItemOwner($gallery->user, $index))
{
	showInvalidReqMesg(gTranslate('core', "You are not allowed to perform this action!"));
	exit;
}

if (!empty($confirm)) {
	$gallery->album->fields['votes'] = array();
	$gallery->album->save(array(i18n("All votes removed")));
	dismissAndReload();
	exit;
}
?>

<p>
<?php echo sprintf(gTranslate('core', "Do you really want to remove all votes from album <b>%s</b>?"), $gallery->album->fields['title']) ?>
</p>

<?php 
echo makeFormIntro('reset_votes.php', array(), array('type' => 'popup'));

echo gSubmit('confirm', gTranslate('core', "_Remove Votes"));
echo gButton('cancel', gTranslate('core', "_Cancel"), 'parent.close()');
?>

</form>

<?php includeTemplate('overall.footer'); ?>
</body>
</html>
