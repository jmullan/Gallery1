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
		'value' => $gallery->album->fields["slideshow_length"],
		'vartype' => 'int_empty'
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
		'value' => $gallery->album->fields["thumb_size"],
		'vartype' => 'int_notnull'
	),
	'resize_size' => array(
		'prompt' => _("Maximum dimensions of intermediate sized images"),
		'desc' => '',
		'choices' => array(0 => _("off"), 400 => 400, 500 => 500, 600 => 600, 640 => 640, 700 => 700, 800 => 800, 1024 => 1024, 1280 => 1280),
		'value' => $gallery->album->fields["resize_size"],
		'vartype' => 'int_empty'
	),
	'resize_file_size' => array(
		'prompt' => _("Maximum file size of intermediate sized JPEG/PNG images in kilobytes (0 or blank for no size restriction)"),
		'desc' => '',
		'type' => 'text',
		'value' => $gallery->album->fields["resize_file_size"],
		'vartype' => 'int_empty'
	),
	'max_size' => array(
		'prompt' => _("Maximum dimensions of full sized images"),
		'desc' => '',
		'choices' => array(0 => _('off'), 400 => 400, 500 => 500, 600 => 600, 640 => 640, 700 => 700, 800 => 800, 1024 => 1024, 1280 => sprintf(_('%d (%d MPix)'), 1280, 1), 1600 => sprintf(_('%d (%d MPix)'), 1600, 2), 2048 => sprintf(_('%d (%d MPix)'), 2048, 3)),
		'value' => $gallery->album->fields["max_size"],
		'vartype' => 'int_empty'
	),
	'max_file_size' => array(
		'prompt' => _("Maximum file size of full sized JPEG/PNG images in kilobytes (0 or blank for no size restriction)"),
		'desc' => '',
		'type' => 'text',
		'value' => $gallery->album->fields["max_file_size"],
		'vartype' => 'int_empty'
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
		'skip' => (empty($gallery->app->use_exif)) ? true : false
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
                'value' => $gallery->album->getPollScale(),
		'vartype' => 'int_empty'
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
                'value' => $gallery->album->getPollNumResults(),
		'vartype' => 'int_empty'
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
);
if (isset($customField)) {
    $properties = array_merge($properties, array(
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
	    'value' => $checked_EF,
	),
        'num_user_fields' => array(
	    'prompt' => _("Number of user defined custom fields"),
	    'desc' => '',
	    'type' => 'text',
	    'value' => $num_user_fields,
	    'attrs' => array('size' => 2),
	    'vartype' => 'int_empty'
	)
      )
    );
    $properties = array_merge($properties, $customFields);
    $properties = array_merge($properties, array(
        'group_CustomFields_end' => array (
        'type' => "group_end"
	),
      )
    );
}

$properties = array_merge($properties, array(
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

?>