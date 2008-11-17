<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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

require_once(dirname(__FILE__) . '/init.php');

list($formaction, $guid) = getRequestVar(array('formaction', 'guid'));

printPopupStart(gTranslate('core', "Delete Album"));
if(! isset($gallery->album) ||
   (!empty($formaction) && $formaction != 'delete'))
{
   	showInvalidReqMesg(gTranslate('core', "Wrong call! Please try again, and/or contact your admin."));
   	includeHtmlWrap("popup.footer");
	exit;
}

if (!$gallery->user->canDeleteAlbum($gallery->album)) {
   	showInvalidReqMesg(gTranslate('core', "You are not allowed to perform this action!"));
   	includeHtmlWrap("popup.footer");
	exit;
}

if (!empty($formaction) && $formaction == 'delete') {
	if ($guid == $gallery->album->fields['guid']) {
		$gallery->album->delete();
	}

	dismissAndLoad(makeGalleryHeaderUrl());
	return;
}

echo gTranslate('core', "Do you really want to delete this album?"); ?>
<p>
<b><?php echo $gallery->album->fields["title"] ?></b>
<?php
	echo "\n<br>";
	if ($gallery->album->numPhotos(1)) {
		echo $gallery->album->getHighlightTag();
	}
?>
</p>
<?php
	echo makeFormIntro("delete_album.php",
		array('name' => 'deletealbum_form', 'onsubmit' => 'deletealbum_form.deleteButton.disabled = true;'),
 		array('type' => 'popup'));

 	echo gInput('hidden', 'guid', null, false, $gallery->album->fields['guid']);
 	echo gInput('hidden', 'formaction', null, false, '');
 	echo gSubmit('deleteButton', gTranslate('core', "Delete"), array('onClick' => "deletealbum_form.formaction.value='delete'"));
 	echo gButton('cancel', gTranslate('core', "Cancel"), 'parent.close()');
?>
</form>

<?php includeHtmlWrap("popup.footer"); ?>

</body>
</html>