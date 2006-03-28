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

list($save, $field, $data, $set_albumName) = getRequestVar(array('save', 'field', 'data', 'set_albumName'));

// Hack check
if (!$gallery->user->canChangeTextOfAlbum($gallery->album)) {
	echo gTranslate('core', "You are not allowed to perform this action!");
	exit;
}

$reloadOpener = false;
doctype();
echo "\n<html>";

if (isset($save)) {
    $gallery->album->fields[$field] = $data;
    $gallery->album->save(array(i18n("%s modified"), $field));
    $reloadOpener = true;
}
?>
<head>
  <title><?php echo sprintf(gTranslate('core', "Edit %s"), gTranslate('common', $field)) ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>" class="popupbody">
<?php if ($reloadOpener) reload(); ?>
<div class="popuphead"><?php echo sprintf(gTranslate('core', "Edit %s"), gTranslate('common', $field)) ?></div>
<div class="popup" align="center">
<?php
	echo sprintf(gTranslate('core', "Edit the %s and click %s when you're done"), gTranslate('common', $field),
	  '<b>' . gTranslate('core', "Save") . '</b>');

	echo makeFormIntro("edit_field.php",
		array("name" => "theform"),
		array("type" => "popup"));
?>
	<input type="hidden" name="field" value="<?php echo $field ?>">
	<input type="hidden" name="set_albumName" value="<?php echo $gallery->session->albumName ?>">
	<textarea name="data" rows="8" cols="50"><?php echo $gallery->album->fields[$field] ?></textarea>
	<p>
		<input type="submit" name="save" value="<?php echo gTranslate('core', "Save") ?>">
		<input type="button" name="cancel" value="<?php echo gTranslate('core', "Close Window") ?>" onclick='parent.close()'>
	</p>
	</form>

	<script language="javascript1.2" type="text/JavaScript">
	<!--
	// position cursor in top form field
	document.theform.data.focus();
	//-->
	</script>
</div>
<?php print gallery_validation_link("edit_field.php",true,array('field' => $field)); ?>
</body>
</html>
