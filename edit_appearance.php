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

list($nv_pairs, $extra_fields, $num_user_fields) = 
    getRequestVar(array('nv_pairs','extra_fields', 'num_user_fields'));

$reloadOpener = false;
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

	$gallery->album->fields["nav_thumbs"] = $nav_thumbs;
	$gallery->album->fields["nav_thumbs_style"] = $nav_thumbs_style;
	$gallery->album->fields["nav_thumbs_first_last"] = $nav_thumbs_first_last;
	$gallery->album->fields["nav_thumbs_prev_shown"] = $nav_thumbs_prev_shown;
	$gallery->album->fields["nav_thumbs_next_shown"] = $nav_thumbs_next_shown;
	$gallery->album->fields["nav_thumbs_location"] = $nav_thumbs_location;
	$gallery->album->fields["nav_thumbs_size"] = $nav_thumbs_size;
	$gallery->album->fields["nav_thumbs_current_bonus"] = $nav_thumbs_current_bonus;
	
	/* Poll properties */
	for ($i=0; $i<$gallery->album->getPollScale() ; $i++) {
                //convert values to numbers
                $nv_pairs[$i]["value"]=0+$nv_pairs[$i]["value"];
        }
        $gallery->album->fields["poll_nv_pairs"]=$nv_pairs;
        $gallery->album->fields["poll_hint"]=$poll_hint;
        $gallery->album->fields["poll_type"] = $poll_type;
        if ($voter_class == "Logged in" &&
            $gallery->album->fields["voter_class"] == "Everybody" &&
            sizeof($gallery->album->fields["votes"]) > 0) {
                $error="<br>" .
                        sprintf(_("Warning: you have changed voters from %s to %s. It is advisable to reset the poll to remove all previous votes."),
                                        "<i>". _("Everybody") ."</i>",
                                        "<i>". _("Logged in") ."</i>");
        }
        $gallery->album->fields["voter_class"] = $voter_class;
        $gallery->album->fields["poll_scale"] = $poll_scale;
        $gallery->album->fields["poll_show_results"] = $poll_show_results;
        $gallery->album->fields["poll_num_results"] = $poll_num_results;
        $gallery->album->fields["poll_orientation"] = $poll_orientation;


	/* Extrafields and Custom Fields */
	$count=0;
        if (!isset($extra_fields)) {
                $extra_fields = array();
        }

        for ($i = 0; $i < sizeof($extra_fields); $i++) {
            if (get_magic_quotes_gpc()) {
                $extra_fields[$i] = stripslashes($extra_fields[$i]);
            }
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
			    'Title' => _("Title"),
			    'AltText' => _("Alt Text / onMouseOver")
);
$extra_fields = $gallery->album->getExtraFields();
$checked_EF = array();

foreach (automaticFieldsList() as $automatic => $printable_automatic) {
    if ($automatic === "EXIF" && (($gallery->album->fields["use_exif"] !== "yes") || !$gallery->app->use_exif)) {
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
	'name' => 'extra_fields[]',
	'prompt' => sprintf(_("Field %s:"),$i),
	'desc' => '',
	'type' => 'text',
	'value' => $value
    );
    $i++;
}

include (dirname(__FILE__) . '/includes/definitions/services.php');
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
		'attrs' => array('cols' => 40, 'rows' => 6)
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
	'group_pollProperties_start' => array (
                'type' => "group_start",
                'name' => "group_pollProperties",
                'default' => "none",
                'title' => _("Poll Properties"),
                'desc' => ""
        ),
	'poll_type' => array(
		'prompt' => _("Type of poll for this album"),
		'desc' => '',
		'choices' => array("rank" => _("Rank"), "critique" => _("Critique")),
		'value' => $gallery->album->fields["poll_type"]
	),
	'poll_scale' => array(
                'prompt' => _("Number of voting options"),
                'desc' => '',
                'type' => 'text',
                'value' => $gallery->album->getPollScale()
        ),
	'poll_show_results' => array(
                'prompt' => _("Show results of voting to all visitors?"),
                'desc' => '',
                'choices' => array("yes" => _("yes"), "no" => _("no")),
                'value' => $gallery->album->fields["poll_show_results"]
        ),
	'poll_num_results' => array(
                'prompt' => _("Number of lines of results graph to display on the album page"),
                'desc' => '',
                'type' => 'text',
                'value' => $gallery->album->getPollNumResults()
        ),
	'voter_class' => array(
                'prompt' => _("Who can vote?"),
                'desc' => '',
                'choices' => array("Logged in" => _("Logged in"), "Everybody" => _("Everybody"), "Nobody" => _("Nobody")),
                'value' => $gallery->album->fields["voter_class"]
        ),
	'poll_orientation' => array(
                'prompt' => _("Orientation of vote choices"),
                'desc' => '',
                'choices' => array('horizontal' => _("Horizontal"), 'vertical' => _("Vertical")),
                'value' => isset($gallery->album->fields['poll_orientation']) ? 
				 $gallery->album->fields['poll_orientation'] : ''
        ),
        'poll_hint' => array(
                'prompt' => _("Vote hint"),
                'desc' => '',
                'type' => 'text',
                'value' => $gallery->album->getPollHint(),
		'attrs' => array('size' => 60)
	),
        'poll_displayed_values' => array(
                'prompt' => _("Voting Options"),
                'desc' => '',
                'type' => 'table_values',
		'elements' => buildVotingInputFields(),
		'columns' => array(_("Displayed Value"),_("Points")),
		'value' => ''
	),
	'group_pollProperties_end' => array (
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
    'group_CustomFields_start' => array (
        'type' => "group_start",
        'name' => "group_CustomFields",
        'default' => "none",
        'title' => _("Custom Fields")
    ),
    'extra_fields' => array(
	'prompt' => '',
	'desc' => '',
        'multiple_choices' => $multiple_choices_EF,
        'value' => $checked_EF
    ),
    'num_user_fields' => array(
                'prompt' => _("Number of user defined custom fields"),
                'desc' => '',
                'type' => 'text',
                'value' => $num_user_fields,
		'attrs' => array('size' => 2)
    )
);

$properties = array_merge($properties, $customFields);
$properties = array_merge($properties, array(
    'group_CustomFields_end' => array (
        'type' => "group_end"
    ),
    'group_MicroThumbs_start' => array (
        'type' => "group_start",
        'name' => "group_MicroThumbs",
        'default' => "none",
        'title' => _("Microthumbs")
    ),
    'nav_thumbs' => array(
                'prompt' => _("Use micro thumb photo navigation"),
                'desc' => '',
                'choices' => array("yes" => _("yes"), "no" => _("no"), "both" => _("both")),
                'value' => $gallery->album->fields["nav_thumbs"]
    ),
    'nav_thumbs_style' => array(
                'prompt' => _("Micro thumb style"),
                'desc' => '',
                'choices' => array("fixed" => _("Fixed"), "dynamic" => _("Dynamic")),
                'value' => $gallery->album->fields["nav_thumbs_style"]
    ),
    'nav_thumbs_first_last' => array(
                'prompt' => _("Show first & last micro thumb"),
                'desc' => '',
                'choices' => array("yes" => _("yes"), "no" => _("no")),
                'value' => $gallery->album->fields["nav_thumbs_first_last"]
    ),
    'nav_thumbs_prev_shown' => array(
                'prompt' => _("Number of previous thumbs"),
                'desc' => '',
                'choices' => array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10),
                'value' => $gallery->album->fields["nav_thumbs_prev_shown"]
    ),
    'nav_thumbs_next_shown' => array(
                'prompt' => _("Number of next thumbs"),
                'desc' => '',
                'choices' => array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10),
                'value' => $gallery->album->fields["nav_thumbs_next_shown"]
    ),
    'nav_thumbs_location' => array(
                'prompt' => _("Position of micro-thumb navigation bar"),
                'desc' => '',
                'choices' => array("top" => _("Top"), "both" => _("Both"), "bottom" => _("Bottom")),
                'value' => $gallery->album->fields["nav_thumbs_location"]
    ),
    'nav_thumbs_size' => array(
                'prompt' => _("Height of micro-thumbs in navigation bar"),
                'desc' => '',
                'type' => 'text',
                'value' => $gallery->album->fields["nav_thumbs_size"],
                'attrs' => array('size' => 3)
    ),
    'nav_thumbs_current_bonus' => array(
                'prompt' => _("Bonus to height of current micro-thumb (pixels)"),
                'desc' => '',
                'type' => 'text',
                'value' => $gallery->album->fields["nav_thumbs_current_bonus"],
                'attrs' => array('size' => 3)
        ),
    'group_MicroThumbs_end' => array (
        'type' => "group_end"
    ),
  )
);

$initialtab = getRequestVar('initialtab');

doctype();
?>
<html>
<head>
  <title><?php echo _("Album Properties") ?></title>
  <?php common_header(); ?>
</head>

<body dir="<?php echo $gallery->direction ?>" class="popupbody">
<?php if ($reloadOpener) reload(); ?>
<div class="popuphead"><?php echo _("Album Properties") ?></div>
<div class="popup" align="center">
<?php 

echo makeFormIntro("edit_appearance.php", 
		array("name" => "theform", "method" => "POST"),
		array("type" => "popup"));

$i = 0;

makeSectionTabs($properties,5, $initialtab);
foreach ($properties as $key => $val) {
	if(!empty($val['skip'])) {
		continue;
	}
	
	if (isset($val["type"]) && ($val["type"] === 'group_start' )) {
		if ($val['name'] == $initialtab || (empty($initialtab) && $val['default'] == 'inline')) {
		    $display = 'inline';
		} else {
		    $display = 'none';
		}
		echo "\n<div id=\"". $val["name"] ."\" style=\"display: $display\">";
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
<input type="hidden" name="set_albumName" value="<?php echo $gallery->session->albumName ?>">
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
