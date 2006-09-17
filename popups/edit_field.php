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

list($save, $field, $data) = getRequestVar(array('save', 'field', 'data'));

// Hack check
if (!$gallery->user->canChangeTextOfAlbum($gallery->album)) {
	echo gTranslate('core', "You are not allowed to perform this action!");
	exit;
}

if (isset($save)) {
    $gallery->album->fields[$field] = $data;
    $gallery->album->save(array(i18n("%s modified"), $field));
    doctype();
    echo "\n<html>";
    dismissAndReload();
    return;
}

printPopupStart(sprintf(gTranslate('core', "Edit %s"), gTranslate('common', $field)));

echo sprintf(gTranslate('core', "Edit the %s and click %s when you're done"),
    gTranslate('common', $field),
    '<b>' . gTranslate('core', "Save") . '</b>'
);

echo makeFormIntro('edit_field.php', array(), array('type' => 'popup'));
?>
  <input type="hidden" name="field" value="<?php echo $field ?>">
  <textarea name="data" rows="8" cols="50"><?php echo $gallery->album->fields[$field] ?></textarea>
  <p>
    <?php echo gSubmit('save', gTranslate('core', "_Save")); ?>
    <?php echo gButton('cancel', gTranslate('core', "_Cancel"), 'parent.close()'); ?>
  </p>
  </form>

    <script language="javascript1.2" type="text/JavaScript">
    <!--
    // position cursor in top form field
    document.g1_form.data.focus();
    //-->
    </script>
</div>

</body>
</html>
