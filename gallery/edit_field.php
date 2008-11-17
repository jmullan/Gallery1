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

list($save, $field, $data) = getRequestVar(array('save', 'field', 'data'));

// Hack checks
if (! isset($gallery->album) || ! isset($gallery->session->albumName) ||
	($field != 'title' && $field != 'description') || 
	!isValidText($data))
{
	printPopupStart(gTranslate('core', "Edit texts"));
	showInvalidReqMesg();
	exit;
}

// Hack check
if (!$gallery->user->canChangeTextOfAlbum($gallery->album)) {
	printPopupStart(gTranslate('core', "Edit texts"));
	echo showInvalidReqMesg(gTranslate('core', "You are not allowed to perform this action!"));
	includeHtmlWrap("popup.footer");
	exit;
}

if (isset($save)) {
    $gallery->album->fields[$field] = $data;
    $gallery->album->save(array(i18n("%s modified"), $field));

	dismissAndReload();
	return;
	}

printPopupStart(sprintf(gTranslate('core', "Edit %s"), gTranslate('common', $field)));

printf(gTranslate('core', "Edit the %s and click %s when you're done."),
			gTranslate('common', $field),
		'<b>' . gTranslate('core', "Save") . '</b>'
);
	
echo makeFormIntro('edit_field.php', array(), array('type' => 'popup', 'field' => $field));
echo gInput('textarea', 'data', null, false, $gallery->album->fields[$field], array('rows' => 8, 'cols' => 50));
echo "<br><br>\n";
echo gSubmit('save', gTranslate('core', "Save"));
echo gButton('cancel', gTranslate('core', "Cancel"), 'parent.close()');
?>
	</form>

<?php includeHtmlWrap("popup.footer"); ?>
</body>
</html>
