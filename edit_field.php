<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2004 Bharat Mediratta
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
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 * $Id$
 */
?>
<?php

require_once(dirname(__FILE__) . '/init.php');

list($save, $field, $data) = getRequestVar(array('save', 'field', 'data'));

// Hack check
if (!$gallery->user->canChangeTextOfAlbum($gallery->album)) {
	echo _("You are not allowed to perform this action!");
	exit;
}

doctype();
echo "\n<html>";

if (isset($save)) {
	if (!strcmp($field, 'title')) {
		$data = removeTags($data);
	}
	$gallery->album->fields[$field] = stripslashes($data);
	$gallery->album->save(array(i18n("%s modified"), $field));
	dismissAndReload();
	return;
}
?>
<head>
  <title><?php echo sprintf(_("Edit %s"), _($field)) ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>" class="popupbody">
<div class="popuphead"><?php echo sprintf(_("Edit %s"), _($field)) ?></div>
<div class="popup" align="center">
<?php 
	echo sprintf(_("Edit the %s and click %s when you're done"), _($field), '<b>' . _("Save") . '</b>');

	echo makeFormIntro("edit_field.php", 
		array("name" => "theform", "method" => "POST"),
		array("type" => "popup")); 
?>
	<input type="hidden" name="field" value="<?php echo $field ?>">
	<textarea name="data" rows="8" cols="50"><?php echo $gallery->album->fields[$field] ?></textarea>
	<p>
		<input type="submit" name="save" value="<?php echo _("Save") ?>">
		<input type="button" name="cancel" value="<?php echo _("Cancel") ?>" onclick='parent.close()'>
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
