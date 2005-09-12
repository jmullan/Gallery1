<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2005 Bharat Mediratta
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
 *
 * $Id$
 */
?>
<?php

require_once(dirname(__FILE__) . '/init.php');

list($apply, $extra_fields, $num_user_fields, $setNested) = 
  getRequestVar(array('apply', 'extra_fields', 'num_user_fields', 'setNested'));

// Hack check
if (!$gallery->user->canWriteToAlbum($gallery->album)) {
	echo _("You are not allowed to perform this action!");
	exit;
}

if (isset($apply)) {
	$count=0;
	if (!isset($extra_fields)) {
		$extra_fields = array();
	}

	for ($i = 0; $i < sizeof($extra_fields); $i++) {
	    $extra_fields[$i] = str_replace('"', '&quot;', $extra_fields[$i]);
	}
	
	$num_fields=$num_user_fields+num_special_fields($extra_fields);

	$gallery->album->setExtraFields($extra_fields);

	if ($num_fields > 0 && !$gallery->album->getExtraFields()) {
		$gallery->album->setExtraFields(array());
	}

	if (sizeof ($gallery->album->getExtraFields()) < $num_fields) {
		$gallery->album->setExtraFields(array_pad($gallery->album->getExtraFields(), $num_fields, _("untitled field")));
	}
	
	if (sizeof ($gallery->album->getExtraFields()) > $num_fields) {
		$gallery->album->setExtraFields(array_slice($gallery->album->getExtraFields(), 0, $num_fields));
	}

	if (!empty($setNested)) {
		$gallery->album->setNestedExtraFields();
	}

	$gallery->album->save(array(i18n("Custom fields modified")));

	reload();
}

doctype();
?>
<html>
<head>
  <title><?php echo _("Configure Custom Fields") ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>" class="popupbody">
<div class="popuphead"><?php echo _("Configure Custom Fields") ?></div>
<div class="popup" align="center">
<p>
<?php echo makeFormIntro("extra_fields.php", 
		array("name" => "theform", "method" => "POST"),
		array("type" => "popup")); 

	$num_user_fields=sizeof($gallery->album->getExtraFields()) - num_special_fields($gallery->album->getExtraFields());
?>

<table>

<?php
$extra_fields=$gallery->album->getExtraFields();

// Translate the first "Title" in the line below only
?>
<tr>
	<td><?php echo _("Title") ?></td>
	<td align="right">
	<input type="checkbox" name="extra_fields[]" value="Title" <?php print in_array("Title", $extra_fields) ?  "checked" : ""; ?>>
	</td>
</tr>
<?php
foreach (automaticFieldsList() as $automatic => $printable_automatic) {
	if ($automatic === "EXIF" && (($gallery->album->fields["use_exif"] !== "yes") || !$gallery->app->use_exif)) {
		continue;
	}
?>
<tr>
	<td><?php print $printable_automatic ?></td>
	<td align="right">
	<input type="checkbox" name="extra_fields[]" value="<?php print $automatic ?>" <?php print in_array($automatic, $extra_fields) ?  "checked" : ""; ?>>
	</td>
</tr>
<?php
}
?>
<tr>
	<td><?php echo _("Alt Text / onMouseOver"); ?></td>
	<td align="right">
	<input type="checkbox" name="extra_fields[]" value="AltText" <?php print in_array("AltText", $extra_fields) ?  "checked" : ""; ?>>
	</td>
</tr>
<tr>
	<td colspan="2">&nbsp;</td>
</tr>
<tr>
	<td colspan="2">
	<?php echo _("Number of user defined custom fields") ?> 
	<input type="text" size="4" name="num_user_fields" value="<?php echo $num_user_fields ?>">
	</td>
</tr>
<tr>
	<td colspan="2">&nbsp;</td></tr>
<?php
$i=0;

foreach ($extra_fields as $value) {
	if (in_array($value, array_keys(automaticFieldsList())))
		continue;
	if (!strcmp($value, "Title") || !strcmp($value, "AltText")) {
		continue;
	}
	print "\n<tr>";
	print "\n\t<td>". _("Field").($i+1).": </td>";
	print "\n\t<td align=\"right\"><input type=\"text\" name=\"extra_fields[]\" value=\"".$value."\"></td>";
	print "\n</tr>";
	$i++;
}

function num_special_fields($extra_fields) {

	$num_special_fields=0;
	foreach (array_keys(automaticFieldsList()) as $special_field) {
		if (in_array($special_field, $extra_fields))
			$num_special_fields++;
	}

	foreach (array("Title", "AltText") as $named_field) {
		if (in_array($named_field, $extra_fields)) {
			$num_special_fields++;
		}
	}

	return $num_special_fields;  
}
?>
</table>
<p>
	<input type="checkbox" name="setNested" value="1"><?php echo _("Apply values to nested albums.") ?>.
</p>
<p>
	<input type="submit" name="apply" value="<?php echo _("Apply") ?>">
	<input type="reset" value="<?php echo _("Undo") ?>">
	<input type="button" name="close" value="<?php echo _("Close") ?>" onclick='parent.close()'>
</p>
</form>
</div>
<?php print gallery_validation_link("extra_fields.php"); ?>

</body>
</html>
