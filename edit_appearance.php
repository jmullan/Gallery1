<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2005 Bharat Mediratta
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

// Hack check
if (!$gallery->user->canWriteToAlbum($gallery->album)) {
	echo _("You are not allowed to perform this action!");
	exit;
}

if (getRequestVar('save')) {
	foreach ($gallery->album->fields as $key => $name) {
		${$key} = getRequestVar($key);
	}
	if (get_magic_quotes_gpc()) {
		$gallery->album->fields["summary"] = stripslashes($summary);
		$gallery->album->fields["title"] = stripslashes($title);
	} else {
		$gallery->album->fields["summary"] = $summary;
		$gallery->album->fields["title"] = $title;
	}
	$gallery->album->fields["bgcolor"] = $bgcolor;
	$gallery->album->fields["textcolor"] = $textcolor;
	$gallery->album->fields["linkcolor"] = $linkcolor;
	$gallery->album->fields["font"] = $font;
	$gallery->album->fields["bordercolor"] = $bordercolor;
	$gallery->album->fields["border"] = $border;
	$gallery->album->fields["background"] = $background;
	$gallery->album->fields["thumb_size"] = $thumb_size;
	$gallery->album->fields["resize_size"] = $resize_size;
	$gallery->album->fields["resize_file_size"] = $resize_file_size;
	$gallery->album->fields['max_size'] = $max_size;
	$gallery->album->fields['max_file_size'] = $max_file_size;
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
	
	$gallery->album->save(array(i18n("Properties changed")));

	if (getRequestVar('setNested')) {
		$gallery->album->setNestedProperties();
	}

	reload();
}

$services = array(
	'photoaccess' => array(
		'name'    => 'PhotoWorks',
		'url'     => 'http://www.photoworks.com/'),
		'shutterfly'  => array(
		'name'    => 'Shutterfly',
		'url'     => 'http://www.shutterfly.com/',
	),
	'fotoserve'  => array(
		'name'    => 'Fotoserve.com',
		'url'     => 'http://www.fotoserve.com/',
	),
	'fotokasten'  => array(
		'name'    => 'Fotokasten',
		'url'     => 'http://www.fotokasten.de/'),
	'mpush'       => array(
		'name'	  => 'mPUSH',
		'url'     => 'http://www.mpush.cc/'),
);

include (dirname(__FILE__) . '/setup/functions.inc');
include (dirname(__FILE__) . '/js/sectionTabs.js.php');
$properties = array(
	'group_text_start' => array (
		'type' => "group_start",
		'name' => "group_text",
		'default' => "inline",
		'title' => _("Texts"),
		'contains_required' => false,
	),
	'summary' => array(
		'prompt' => _("Album Summary"),
		'desc' => '',
		'value' => $gallery->album->fields["summary"],
		'type' => "textarea",
		'attrs' => array('cols' => 45, 'rows' => 8)
	),
	'title' => array(
		'prompt' => _("Album Title"),
		'desc' => '',
		'type' => 'text',
		'value' => $gallery->album->fields["title"]
	),
	'group_text_end' => array (
		'type' => "group_end",
	),
	'group_colors_start' => array (
		'type' => "group_start",
		'name' => "group_color",
		'default' => "none",
		'title' => _("Colors"),
		'desc' => ""
	),
	'bgcolor' => array(
		'prompt' => _("Background Color"),
		'desc' => '',
		'type' => 'colorpicker',
		'value' => $gallery->album->fields["bgcolor"]
	),
	'textcolor' => array(
		'prompt' => _("Text Color"),
		'desc' => '',
		'type' => 'colorpicker',
		'value' => $gallery->album->fields["textcolor"]
	),
	'linkcolor' => array(
		'prompt' => _("Link Color"),
		'desc' => '',
		'type' => 'colorpicker',
		'value' => $gallery->album->fields["linkcolor"]
	),
	'group_color_end' => array (
		'type' => "group_end"
	),
	'group_layout_start' => array (
		'type' => "group_start",
		'name' => "group_layout",
		'default' => "none",
		'title' => _("Layout"),
		'desc' => ""
	),
	'background' => array(
		'prompt' => _("Background Image (URL)"),
		'desc' => '',
		'type' => 'text',
		'value' => $gallery->album->fields["background"]
	),
	'font' => array(
		'prompt' => _("Font"),
		'desc' => '',
		'type' => 'text',
		'value' => $gallery->album->fields["font"]
	),
	'rows' => array(
		'prompt' => _("Rows"),
		'desc' => '',
		'choices' => array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10),
		'value' => $gallery->album->fields["rows"]
	),
	'cols' => array(
		'prompt' => _("Columns"),
		'desc' => '',
		'choices' => array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10),
		'value' => $gallery->album->fields["cols"]
	),
	'border' => array(
		'prompt' => _("Borders"),
		'desc' => '',
		'choices' => array(0 => 0, 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 10 => 10, 15 => 15, 20 => 20),
		'value' => $gallery->album->fields["border"]
	),
	'bordercolor' => array(
		'prompt' => _("Border color"),
		'desc' => '',
		'type' => 'colorpicker',
		'value' => $gallery->album->fields["bordercolor"]
	),
	'album_frame' => array(
		'prompt' => _("Album Frame"),
		'desc' => '',
		'choices' => available_frames(),
		'value' => $gallery->album->fields["album_frame"]
	),
	'thumb_frame' => array(
		'prompt' => _("Thumb Frame"),
		'desc' => '',
		'choices' => available_frames(),
		'value' => $gallery->album->fields["thumb_frame"]
	),
	'image_frame' => array(
		'prompt' => _("Image Frame"),
		'desc' => '',
		'choices' => available_frames(),
		'value' => $gallery->album->fields["image_frame"]
	),
	'group_layout_end' => array (
		'type' => "group_end"
	),
	'group_diashow_start' => array (
		'type' => "group_start",
		'name' => "group_diashow",
		'default' => "none",
		'title' => _("Diashow"),
		'desc' => ""
	),
	'slideshow_type' => array(
		'prompt' => _("Slideshow Type"),
		'desc' => '',
		'choices' => array( "off" => _("Off"), "ordered" => _("Ordered"), "random" => _("Random")),
		'value' => $gallery->album->fields["slideshow_type"]
	),
	'slideshow_recursive' => array(
		'prompt' => _("Include sub-albums in slideshow"),
		'desc' => '',
		'choices' => array("yes" => _("yes"), "no" => _("no")),
		'value' => $gallery->album->fields["slideshow_recursive"]
	),
	'slideshow_loop' => array(
		'prompt' => _("Allow slideshow to loop"),
		'desc' => '',
		'choices' => array("yes" => _("yes"), "no" => _("no")),
		'value' => $gallery->album->fields["slideshow_loop"]
	),
	'slideshow_length' => array(
		'prompt' => _("Slideshow Length"),
		'desc' => '',
		'type' => 'text',
		'value' => $gallery->album->fields["slideshow_length"]
	),
	'group_diashow_end' => array (
		'type' => "group_end"
	),
	'group_sizes_start' => array (
		'type' => "group_start",
		'name' => "group_sizes",
		'default' => "none",
		'title' => _("Sizes"),
		'desc' => ""
	),
	'thumb_size' => array(
		'prompt' => _("Thumbnail size"),
		'desc' => '',
		'type' => 'text',
		'value' => $gallery->album->fields["thumb_size"]
	),
	'resize_size' => array(
		'prompt' => _("Maximum dimensions of intermediate sized images"),
		'desc' => '',
		'choices' => array("off" => _("off"), 400 => 400, 500 => 500, 600 => 600, 640 => 640, 700 => 700, 800 => 800, 1024 => 1024, 1280 => 1280),
		'value' => $gallery->album->fields["resize_size"]
	),
	'resize_file_size' => array(
		'prompt' => _("Maximum file size of intermediate sized JPEG/PNG images in kilobytes (0 or blank for no size restriction)"),
		'desc' => '',
		'type' => 'text',
		'value' => $gallery->album->fields["resize_file_size"]
	),
	'max_size' => array(
		'prompt' => _("Maximum dimensions of full sized images"),
		'desc' => '',
		'choices' => array('off' => _('off'), 400 => 400, 500 => 500, 600 => 600, 640 => 640, 700 => 700, 800 => 800, 1024 => 1024, 1280 => sprintf(_('%d (%d MPix)'), 1280, 1), 1600 => sprintf(_('%d (%d MPix)'), 1600, 2), 2048 => sprintf(_('%d (%d MPix)'), 2048, 3)),
		'value' => $gallery->album->fields["max_size"]
	),
	'max_file_size' => array(
		'prompt' => _("Maximum file size of full sized JPEG/PNG images in kilobytes (0 or blank for no size restriction)"),
		'desc' => '',
		'type' => 'text',
		'value' => $gallery->album->fields["max_file_size"]
	),
	'group_sizes_end' => array (
		'type' => "group_end"
	),
	'group_permission_start' => array (
		'type' => "group_start",
		'name' => "group_permission",
		'default' => "none",
		'title' => _("Permissions"),
		'desc' => ""
	),
	'item_owner_modify' => array(
		'prompt' => _("Allow item owners to modify their images"),
		'desc' => '',
		'choices' => array("yes" => _("yes"), "no" => _("no")),
		'value' => $gallery->album->fields["item_owner_modify"]
	),
	'item_owner_delete' => array(
		'prompt' => _("Allow item owners to delete their images"),
		'desc' => '',
		'choices' => array("yes" => _("yes"), "no" => _("no")),
		'value' => $gallery->album->fields["item_owner_delete"]
	),
	'group_permission_end' => array (
		'type' => "group_end"
	),
	'group_data_start' => array (
		'type' => "group_start",
		'name' => "group_data",
		'default' => "none",
		'title' => _("Element data"),
		'desc' => ""
	),
	'display_clicks' => array(
		'prompt' => _("Display click counter for this album?"),
		'desc' => '',
		'choices' => array("yes" => _("yes"), "no" => _("no")),
		'value' => $gallery->album->fields["display_clicks"]
	),
	'item_owner_display' => array(
		'prompt' => _("Display owners name with caption"),
		'desc' => '',
		'choices' => array("yes" => _("yes"), "no" => _("no")),
		'value' => $gallery->album->fields["item_owner_display"]
	),
	'showDimensions' => array(
		'prompt' => _("Display clickable image dimensions"),
		'desc' => '',
		'choices' => array("yes" => _("yes"), "no" => _("no")),
		'value' => $gallery->album->fields["showDimensions"]
	),
	'use_exif' => array(
		'prompt' => _("Display EXIF data?"),
		'desc' => '',
		'choices' => array("yes" => _("yes"), "no" => _("no")),
		'value' => $gallery->album->fields["use_exif"],
		'skip' => ($gallery->app->use_exif == 'yes') ? false : true
	),
	'group_data_end' => array (
		'type' => "group_end"
	),
	'group_services_start' => array (
		'type' => "group_start",
		'name' => "group_services",
		'default' => "none",
		'title' => _("Services"),
		'desc' => ""
	),
	'print_photos' => array(
                'prompt' => _("Which photo printing services<br>do you want to let visitors use?"),
                'desc' => '',
		'multiple_choices' => array(
                        'photoaccess' => '<a href="http://www.photoworks.com/">PhotoWorks</a>',
                        'shutterfly'  => '<a href="http://www.shutterfly.com/">Shutterfly</a>',
                        'fotoserve'   => '<a href="http://www.fotoserve.com/">Fotoserve.com</a>',
                        'fotokasten'  => '<a href="http://www.fotokasten.de/">Fotokasten</a>',
                        'mpush'       => '<a href="http://www.mpush.cc/">mPush</a>'
                ),
                'value' => $gallery->album->fields['print_photos']
        ),
	'ecards' => array(
		'prompt' => _("Enable Ecards ?"),
                'desc' => '',
                'choices' => array("yes" => _("yes"), "no" => _("no")),
                'value' => isset($gallery->album->fields["ecards"]) ? $gallery->album->fields["ecards"] : 'no',
                'skip' => ($gallery->app->emailOn == 'yes') ? false : true
        ),
	'group_services_end' => array (
		'type' => "group_end"
	),
	'group_misc_start' => array (
		'type' => "group_start",
		'name' => "group_misc",
		'default' => "none",
		'title' => _("Misc"),
		'desc' => ""
	),
	'add_to_beginning' => array(
		'prompt' => _("Add new items at beginning of album"),
		'desc' => '',
		'choices' => array("yes" => _("yes"), "no" => _("no")),
		'value' => $gallery->album->fields["add_to_beginning"]
	),
	'returnto' => array(
		'prompt' => _("Show <i>Return to</i> link"),
		'desc' => '',
		'choices' => array("yes" => _("yes"), "no" => _("no")),
		'value' => $gallery->album->fields["returnto"]
	),
	'use_fullOnly' => array(
		'prompt' => _("Offer visitors ability to specify<br>preference for full-size or resized images"),
		'desc' => '',
		'choices' => array("yes" => _("yes"), "no" => _("no")),
		'value' => $gallery->album->fields["use_fullOnly"]
	),
	'fit_to_window' => array(
		'prompt' => _("Auto fit-to-window for<br>images without a resized copy"),
		'desc' => '',
		'choices' => array("yes" => _("yes"), "no" => _("no")),
		'value' => $gallery->album->fields["fit_to_window"]
	),
	'group_misc_end' => array (
		'type' => "group_end"
	),
);
doctype();
?>
<html>
<head>
  <title><?php echo _("Album Properties") ?></title>
  <?php common_header(); ?>
</head>

<body dir="<?php echo $gallery->direction ?>" class="popupbody">
<div class="popuphead"><?php echo _("Album Properties") ?></div>
<div class="popup" align="center">
<?php 

echo makeFormIntro("edit_appearance.php", 
		array("name" => "theform", "method" => "POST"),
		array("type" => "popup"));

$i = 0;

makeSectionTabs($properties,5, getRequestVar('initialtab'));
foreach ($properties as $key => $val) {
	if(!empty($val['skip'])) {
		continue;
	}
	
	if (isset($val["type"]) && ($val["type"] === 'group_start' )) {
		echo "\n<div id=\"". $val["name"] ."\" style=\"display: ". $val["default"] ."\">";
		echo make_separator($key, $val);
		echo "\n<table width=\"100%\" class=\"inner\">";
		continue;
	}

	if (isset($val["type"]) && ($val["type"] === 'group_end' )) {
		echo "\n</table>";
		echo "\n</div>";
		continue;
	}

	/* We dont want separate borders around each property */
	//echo "\n<table width=\"100%\" class=\"inner\">";

	// Protect quote characters to avoid screwing up HTML forms
	$val["value"] = array_str_replace('"', "&quot;", $val["value"]);

	if (isset($val["type"]) && !strcmp($val["type"], "hidden")) {
		list($f1, $f2) = make_fields($key, $val);
		echo $f2;
	} else {
		echo evenOdd_row(make_fields($key, $val),
		$i++ % 2);
	}

	$onThisPage[$key] = 1;
	$preserve[$key] = 1;

	/* We dont want separate borders around each property */
	//echo "\n</table>";
}
?>
<input type="hidden" name="save" value="1">

<hr>
<input type="checkbox" name="setNested" id="setNested" value="1"><label for="setNested"><?php echo _("Apply values to nested albums (except album title and summary).") ?></label>
<br>
<br>
<input type="submit" name="apply" value="<?php echo _("Apply") ?>">
<input type="reset" value="<?php echo _("Undo") ?>">
<input type="button" name="close" value="<?php echo _("Close") ?>" onclick='parent.close()'>

</form>

</div>
<?php print gallery_validation_link("edit_appearance.php"); ?>

</body>
</html>
