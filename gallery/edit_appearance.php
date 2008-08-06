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

// Hack checks
if (! isset($gallery->album) || ! isset($gallery->session->albumName)) {
	printPopupStart(gTranslate('core', "Album Properties"));
	showInvalidReqMesg();
	exit;
}

if(! $gallery->user->canWriteToAlbum($gallery->album)) {
	printPopupStart(gTranslate('core', "Album Properties"));
	showInvalidReqMesg(gTranslate('core', "You are not allowed to perform this action!"));
	exit;
}

list($nv_pairs, $extra_fields, $num_user_fields) =
	getRequestVar(array('nv_pairs','extra_fields', 'num_user_fields'));

include_once (dirname(__FILE__) . '/includes/definitions/services.php');
include_once (dirname(__FILE__) . '/lib/setup.php');
include_once (dirname(__FILE__) . '/js/sectionTabs.js.php');

$infoMessages = array();
$reloadOpener = false;

$shortdescWidth = '50%';

if (getRequestVar('save')) {
	/**
     * This part does 2 things:
     * 1.) get the values given by user, so we can put them into the album later.
     * 2.) Load the properties and check wether a user input is invalid.
     */
	include (dirname(__FILE__) . '/includes/definitions/albumProperties.php');
	foreach ($properties as $fieldName => $values) {
		${$fieldName} = getRequestVar($fieldName);
		if (isset($properties[$fieldName]['vartype'])) {
			list($status, ${$fieldName}, $infoMessage) =
				sanityCheck(${$fieldName}, $properties[$fieldName]['vartype'], $gallery->app->default[$fieldName]);
			if (!empty($infoMessage)) {
				$infoMessages[] .= sprintf (gTranslate('core', "Problem with input of field '%s'. %s"), $fieldName, $infoMessage);
			}
		}
	}

	$gallery->album->fields["summary"] = $summary;
	$gallery->album->fields["title"] = trim($title);
	$gallery->album->fields["bgcolor"] = $bgcolor;
	$gallery->album->fields["textcolor"] = $textcolor;
	$gallery->album->fields["linkcolor"] = $linkcolor;
	$gallery->album->fields["font"] = $font;
	$gallery->album->fields["bordercolor"] = $bordercolor;
	$gallery->album->fields["border"] = $border;
	$gallery->album->fields["background"] = $background;
	$gallery->album->fields["thumb_size"] = $thumb_size;
	$gallery->album->fields["thumb_ratio"] = $thumb_ratio;
	$gallery->album->fields["resize_size"] = $resize_size;
	$gallery->album->fields["resize_file_size"] = $resize_file_size;
	$gallery->album->fields["max_size"] = $max_size;
	$gallery->album->fields["max_file_size"] = $max_file_size;
	$gallery->album->fields["returnto"] = $returnto;
	$gallery->album->fields["rows"] = $rows;
	$gallery->album->fields["cols"] = $cols;
	$gallery->album->fields["fit_to_window"] = $fit_to_window;
	$gallery->album->fields["use_fullOnly"] = $use_fullOnly;
	$gallery->album->fields["print_photos"] = $print_photos;
	$gallery->album->fields["use_exif"] = $use_exif;
	$gallery->album->fields["display_clicks"] = $display_clicks;
	$gallery->album->fields["item_owner_modify"] = $item_owner_modify;
	$gallery->album->fields["item_owner_delete"] = $item_owner_delete;
	$gallery->album->fields["item_owner_display"] = $item_owner_display;
	$gallery->album->fields["add_to_beginning"] = $add_to_beginning;
	$gallery->album->fields["slideshow_type"] = $slideshow_type;
	$gallery->album->fields["slideshow_recursive"] = $slideshow_recursive;
	$gallery->album->fields["slideshow_loop"] = $slideshow_loop;
	$gallery->album->fields["slideshow_length"] = $slideshow_length;
	$gallery->album->fields["album_frame"] = $album_frame;
	$gallery->album->fields["thumb_frame"] = $thumb_frame;
	$gallery->album->fields["image_frame"] = $image_frame;
	$gallery->album->fields["showDimensions"] = $showDimensions;
	$gallery->album->fields["ecards"] = $ecards;

	$gallery->album->fields['nav_thumbs']			= $nav_thumbs;
	$gallery->album->fields['nav_thumbs_style']		= $nav_thumbs_style;
	$gallery->album->fields['nav_thumbs_first_last']	= $nav_thumbs_first_last;
	$gallery->album->fields['nav_thumbs_prev_shown']	= $nav_thumbs_prev_shown;
	$gallery->album->fields['nav_thumbs_next_shown']	= $nav_thumbs_next_shown;
	$gallery->album->fields['nav_thumbs_location']		= $nav_thumbs_location;
	$gallery->album->fields['nav_thumbs_size']		= $nav_thumbs_size;
	$gallery->album->fields['nav_thumbs_current_bonus']	= $nav_thumbs_current_bonus;

	/* Poll properties */
	for ($i = 0; $i < $gallery->album->getPollScale() ; $i++) {
		//convert values to numbers
		$nv_pairs[$i]['value'] = 0 + $nv_pairs[$i]['value'];
	}

	$gallery->album->fields['poll_nv_pairs']	= $nv_pairs;
	$gallery->album->fields['poll_hint']		= $poll_hint;
	$gallery->album->fields['poll_type']		= $poll_type;

	if ($voter_class == "Logged in" &&
		$gallery->album->fields['voter_class'] == "Everybody" &&
		sizeof($gallery->album->fields['votes']) > 0)
	{
		$error = "<br>" .
			sprintf(gTranslate('core', "Warning: you have changed voters from %s to %s. It is advisable to reset the poll to remove all previous votes."),
			"<i>". gTranslate('core', "Everybody") ."</i>",
			"<i>". gTranslate('core', "Logged in") ."</i>");
	}

	$gallery->album->fields['voter_class']		= $voter_class;
	$gallery->album->fields['poll_scale']		= $poll_scale;
	$gallery->album->fields['poll_show_results']	= $poll_show_results;
	$gallery->album->fields['poll_num_results']	= $poll_num_results;
	$gallery->album->fields['poll_orientation']	= $poll_orientation;


	/* Extrafields and Custom Fields */
	$count = 0;
	if (!isset($extra_fields)) {
		$extra_fields = array();
	}

	for ($i = 0; $i < sizeof($extra_fields); $i++) {
		$extra_fields[$i] = str_replace('"', '&quot;', $extra_fields[$i]);
	}

	$num_fields = $num_user_fields + num_special_fields($extra_fields);

	$gallery->album->setExtraFields($extra_fields);

	if ($num_fields > 0 && !$gallery->album->getExtraFields()) {
		$gallery->album->setExtraFields(array());
	}

	if (sizeof ($gallery->album->getExtraFields()) < $num_fields) {
		$gallery->album->setExtraFields(array_pad($gallery->album->getExtraFields(), $num_fields, gTranslate('core', "untitled field")));
	}

	if (sizeof ($gallery->album->getExtraFields()) > $num_fields) {
		$gallery->album->setExtraFields(array_slice($gallery->album->getExtraFields(), 0, $num_fields));
	}

	$gallery->album->save(array(i18n("Properties changed")));

	if (getRequestVar('setNested')) {
		$gallery->album->setNestedProperties();
	}

	$reloadOpener = true;
}


/* Custom / Extra Fields */
function num_special_fields($extra_fields) {
	$num_special_fields = 0;

	foreach (array_keys(automaticFieldsList()) as $special_field) {
		if (in_array($special_field, $extra_fields)) {
			$num_special_fields++;
		}
	}

	foreach (array("Title", "AltText") as $named_field) {
		if (in_array($named_field, $extra_fields)) {
			$num_special_fields++;
		}
	}

	return $num_special_fields;
}

$multiple_choices_EF = array(
	'Title' => gTranslate('core', "Title"),
	'AltText' => gTranslate('core', "Alt text / Tooltip")
);

$extra_fields	= $gallery->album->getExtraFields();
$checked_EF	= array();

foreach (automaticFieldsList() as $automatic => $printable_automatic) {
	if ($automatic === "EXIF" &&
		(($gallery->album->fields['use_exif'] != "yes") || !isset($gallery->app->use_exif)))
	{
		continue;
	}

	$multiple_choices_EF[$automatic] = $printable_automatic;
}

foreach($multiple_choices_EF as $field => $trash) {
	if (in_array($field, $extra_fields)) {
		$checked_EF[] = $field;
	}
}

$num_user_fields = sizeof($extra_fields) - num_special_fields($extra_fields);

$customFields = array();
$i = 1;
foreach ($extra_fields as $value) {
	if (in_array($value, array_keys(automaticFieldsList())) || !strcmp($value, "Title") || !strcmp($value, "AltText")) {
		continue;
	}

	$customFields["cf_$i"] = array(
		'name'		=> 'extra_fields[]',
		'prompt'	=> sprintf(gTranslate('core', "Field %s:"),$i),
		'desc'		=> '',
		'type'		=> 'text',
		'value'		=> $value
	);

	$i++;
}
/* We may load the properties now the second time, but its needed as they might have change above. */
include (dirname(__FILE__) . '/includes/definitions/albumProperties.php');
$initialtab = getRequestVar('initialtab');

doctype();
?>
<html>
<head>
  <title><?php echo gTranslate('core', "Album Properties") ?></title>
  <?php common_header(); ?>
</head>

<body dir="<?php echo $gallery->direction ?>" class="popupbody">
<?php if ($reloadOpener) reload(); ?>
<div class="popuphead"><?php echo gTranslate('core', "Album Properties") ?></div>
<?php echo infoLine($infoMessages, 'error'); ?>
<div class="popup" align="center">
<?php

$i = 0;
$initialtab = makeSectionTabs($properties, $initialtab, true);

echo "<div style=\"clear: both\"></div>";
echo makeFormIntro('edit_appearance.php',
	array(),
	array('type' => 'popup', 'initialtab' => $initialtab));

foreach ($properties as $key => $val) {
	if(!empty($val['skip'])) {
		continue;
	}

	if (isset($val["type"]) && ($val["type"] === 'group_start' )) {
		if ($val['name'] == $initialtab || (empty($initialtab) && $val['default'] == 'inline')) {
			$display = 'inline';
		}
		else {
			$display = 'none';
		}

		echo "\n<div id=\"{$val["name"]}\" style=\"display: $display\">";
		echo make_separator($key, $val);
		echo "\n<table width=\"100%\" class=\"inner\">";
		continue;
	}

	if (isset($val['type']) && ($val['type'] === 'subgroup' )) {
		echo '<tr><td colspan="2">'. make_separator($key, $val) .'</td></tr>';
		continue;
	}

	if (isset($val['type']) && ($val['type'] === 'group_end' )) {
		echo "\n</table>";
		echo "\n</div>";
		continue;
	}

	// Protect quote characters to avoid screwing up HTML forms
	$val['value'] = array_str_replace('"', "&quot;", $val['value']);

	if (isset($val['type']) && $val['type'] == 'hidden') {
		list($f1, $f2) = make_fields($key, $val);
		echo $f2;
	}
	else {
		echo evenOdd_row(make_fields($key, $val), $i++ % 2);
	}

	$onThisPage[$key] = 1;
	$preserve[$key] = 1;
}
?>
<input type="hidden" name="save" value="1">
<input type="hidden" name="set_albumName" value="<?php echo $gallery->session->albumName ?>">
<hr>
<input type="checkbox" name="setNested" id="setNested" value="1"><label for="setNested"><?php echo gTranslate('core', "Apply values to nested albums (except album title and summary).") ?></label>
<br>
<br>
<?php echo gSubmit('apply', gTranslate('core', "Apply")); ?>
<?php echo gReset('reset', gTranslate('core', "Undo")); ?>
<?php echo gButton('close', gTranslate('core', "Close"), 'parent.close()'); ?>

</form>

</div>

</body>
</html>
