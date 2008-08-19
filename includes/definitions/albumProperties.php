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

/**
 * Defintion for album properties.
 *
 * @package Definitions
 * @author Jens Tkotz
 */

if (!isset($gallery) || !function_exists('gTranslate')) {
	exit;
}

$properties = array(
	'group_text' => array (
		'type'		=> 'group_start',
		'default'	=> 'inline',
		'title'		=> gTranslate('common', "_Texts"),
		'contains_required' => false,
	),
	'title' => array(
		'prompt' =>	gTranslate('common', "Album Title"),
		'desc' 		=> '',
		'type' 		=> 'text',
		'value' 	=> $gallery->album->fields['title'],
		'attrs'		=> array('size' => 50)
	),
	'summary' => array(
        	'prompt'	=> gTranslate('common', "Album summary"),
		'desc' 		=> '',
		'value' 	=> $gallery->album->fields['summary'],
		'type' 		=> 'textarea',
		'attrs'		=> array('cols' => 40, 'rows' => 6)
	),
	'group_text_end' => array (
		'type'		=> 'group_end',
	),
	'group_layout' => array (
		'type'		=> 'group_start',
		'default'	=> 'none',
		'title'		=> gTranslate('common', "_Layout"),
		'desc'		=> ''
	),
	'background' => array(
		'prompt' =>	gTranslate('common', "Background Image (URL)"),
		'desc'		=> '',
		'type'		=> 'text',
		'value'		=> $gallery->album->fields['background']
	),
	'font' => array(
		'prompt'	=> gTranslate('common', "Font"),
		'desc'		=> '',
		'type'		=> 'text',
		'value'		=> $gallery->album->fields['font']
	),
	'cols' => array(
		'prompt'	=> gTranslate('common', "Columns"),
		'desc'		=> '',
		'choices'	=> array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10),
		'value'		=> $gallery->album->fields['cols']
	),
	'rows' => array(
		'prompt'	=> gTranslate('common', "Rows"),
		'desc'		=> '',
		'choices'	=> array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10),
		'value'		=> $gallery->album->fields['rows']
	),
	'border' => array(
		'prompt'	=> gTranslate('common', "Borders"),
		'desc'		=> '',
		'choices'	=> array(0 => 0, 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 10 => 10, 15 => 15, 20	=> 20),
		'value'		=> $gallery->album->fields['border']
	),
	'subgroup_colors' => array (
		'type'		=> 'subgroup',
		'title'		=> gTranslate('common', "Colors")
	),
	'bgcolor' => array(
		'prompt' =>	gTranslate('common', "Background Color"),
		'desc'		=> '',
		'type'		=> 'colorpicker',
		'value'		=> $gallery->album->fields['bgcolor']
	),
	'textcolor' => array(
		'prompt' =>	gTranslate('common', "Text Color"),
		'desc'		=> '',
		'type'		=> 'colorpicker',
		'value'		=> $gallery->album->fields['textcolor']
	),
	'linkcolor' => array(
		'prompt' =>	gTranslate('common', "Link Color"),
		'desc'		=> '',
		'type'		=> 'colorpicker',
		'value'		=> $gallery->album->fields['linkcolor']
	),
	'bordercolor' => array(
		'prompt' =>	gTranslate('common', "Border Color"),
		'desc'		=> '',
		'type'		=> 'colorpicker',
		'value'		=> $gallery->album->fields['bordercolor']
	),
	'subgroup_frames' => array (
		'type'		=> 'subgroup',
		'title'		=> gTranslate('common', "Frames")
	),
	'album_frame' => array(
		'prompt' =>	gTranslate('common', "Album Frame"),
		'desc'		=> '',
		'choices'	=> available_frames(),
		'value'		=> $gallery->album->fields['album_frame'],
		'vartype'	=> 'pictureFrame'
	),
	'thumb_frame' => array(
		'prompt' =>	gTranslate('common', "Thumb Frame"),
		'desc'		=> '',
		'choices'	=> available_frames(),
		'value'		=> $gallery->album->fields['thumb_frame'],
		'vartype'	=> 'pictureFrame'
	),
	'image_frame' => array(
		'prompt' =>	gTranslate('common', "Item Frame"),
		'desc'		=> '',
		'choices'	=> available_frames(),
		'value'		=> $gallery->album->fields['image_frame'],
		'vartype'	=> 'pictureFrame'
	),
	'group_layout_end' => array (
		'type'		=> 'group_end'
	),
	'group_slideshow' => array (
		'type'		=> 'group_start',
		'default'	=> 'none',
		'title'		=> gTranslate('common', "_Slideshow"),
		'desc'		=> ''
	),
	'slideshow_type' => array(
		'prompt' =>	gTranslate('common', "Slideshow Type?"),
		'desc'		=> '',
		'choices'	=> array("off" => gTranslate('common', "Off"),
					 "ordered" => gTranslate('common', "Ordered"),
					 "random" => gTranslate('common', "Random")),
		'value' =>	$gallery->album->fields["slideshow_type"]
	),
	'slideshow_recursive' => array(
        	'prompt'	=> gTranslate('common', "Include sub-albums in slideshow?"),
		'desc'		=> '',
		'choices'	=> array('yes' => gTranslate('common', "Yes"), 'no' => gTranslate('common', "No")),
		'value'		=> $gallery->album->fields['slideshow_recursive']
	),
	'slideshow_loop' => array(
        	'prompt'	=> gTranslate('common', "Allow slideshow to loop?"),
		'desc'		=> '',
		'choices'	=> array('yes' => gTranslate('common', "Yes"), 'no' => gTranslate('common', "No")),
		'value'		=> $gallery->album->fields['slideshow_loop']
	),
	'slideshow_length' => array(
		'prompt'	=> gTranslate('common', "Slideshow Length"),
		'desc'		=> '',
		'type'		=> 'text',
		'value'		=> $gallery->album->fields['slideshow_length'],
		'vartype'	=> 'int_ZeroEmpty'
	),
	'group_slideshow_end' => array (
		'type'		=> 'group_end'
	),
	'group_sizes' => array (
		'type'		=> 'group_start',
		'default'	=> 'none',
		'title'		=> gTranslate('common', "Si_zes"),
		'desc'		=> ''
	),
	'thumb_size' => array(
        	'prompt'	=> gTranslate('common', "Thumbnail size (in px)?"),
		'desc'		=> '',
		'type'		=> 'text',
		'value'		=> $gallery->album->fields['thumb_size'],
		'vartype'	=> 'int_ZeroNotEmpty'
	),
	'thumb_ratio' => array(
        	'prompt'	=> gTranslate('common', "The ratio in which the thumbnails are made.<br>This affects only new thumbs. For existing use 'rebuild thumbs'."),
		'desc'		=> '',
		'choices' =>	array('0' => gTranslate('common', "As the original image"),
				      '1/1' => gTranslate('common', "Square thumbs")),
		'value'		=> getPropertyDefault('thumb_ratio', $gallery->album, false),
	),
	'resize_size' => array(
        	'prompt'	=> gTranslate('common', "Maximum size of intermediate sized images (in px)?"),
		'desc'		=> '',
		'choices'	=> array(
					0 => gTranslate('common', "Off"),
					400 => 400, 500 => 500,
					600 => 600, 640 => 640,
					700 => 700, 800 => 800,
					1024 => 1024,
					1280 => 1280),
		'value'		=>	$gallery->album->fields["resize_size"],
		'vartype'	=> 'int_ZeroEmpty'
	),
	'resize_file_size' => array(
        	'prompt'	=> gTranslate('common', "Maximum file size of intermediate sized JPEG/PNG images in kilobytes (0 or blank for no size restriction)?"),
		'desc'		=> '',
		'type'		=> 'text',
		'value'		=> $gallery->album->fields['resize_file_size'],
		'vartype'	=> 'int_ZeroEmpty'
	),
	'max_size' => array(
        	'prompt'	=> gTranslate('common', "Maximum size of full sized images (in px)?"),
		'desc'		=> '',
		'choices' =>	array(0 => gTranslate('common', "Off"),
				     400 => 400, 500 => 500,
				     600 => 600, 640 => 640,
				     700 => 700, 800 => 800,
				     1024 => 1024,
				     1280 => sprintf(gTranslate('common', '%d (%d MPix)'), 1280, 1),
				     1600 => sprintf(gTranslate('common', '%d (%d MPix)'), 1600, 2),
				     2048 => sprintf(gTranslate('common', '%d (%d MPix)'), 2048, 3)),
		'value' =>	$gallery->album->fields["max_size"],
		'vartype'	=> 'int_ZeroEmpty'
	),
	'max_file_size' => array(
        	'prompt'	=> gTranslate('common', "Maximum file size of full sized JPEG/PNG images in kb (0 or blank for no size restriction)?"),
		'desc'		=> '',
		'type'		=> 'text',
		'value'		=> $gallery->album->fields['max_file_size'],
		'vartype'	=> 'int_ZeroEmpty'
	),
	'group_sizes_end' => array (
		'type'		=> 'group_end'
	),
	'group_display' => array (
		'type'		=> 'group_start',
		'default'	=> 'none',
		'title'		=> gTranslate('common', "_Display"),
		'desc'		=> ''
	),
	'display_clicks' => array(
		'prompt'	=> gTranslate('common', "Display click counter for this album?"),
		'desc'		=> '',
		'choices'	=> array('yes' => gTranslate('common', "Yes"), 'no' => gTranslate('common', "No")),
		'value'		=> $gallery->album->fields['display_clicks']
	),
	'item_owner_display' => array(
		'prompt' 	=> gTranslate('common', "Display owner's name with caption?"),
		'desc'		=> '',
		'choices'	=> array('yes' => gTranslate('common', "Yes"), 'no' => gTranslate('common', "No")),
		'value'		=> $gallery->album->fields['item_owner_display']
	),
	'showDimensions' => array(
		'prompt'	=> gTranslate('common', "Display clickable image dimensions?"),
		'desc'		=> '',
		'choices'	=> array('yes' => gTranslate('common', "Yes"), 'no' => gTranslate('common', "No")),
		'value'		=> $gallery->album->fields['showDimensions']
	),
	'dimensionsAsPopup' => array(
		'prompt'	=> gTranslate('common', "Open dimensions-link as popup?"),
		'desc'		=> gTranslate('common', "If you show the dimensions-links, you can choose whether you just want the images shown in a popup, or open the complete photoview."),
		'choices'	=> array("yes" => gTranslate('common', "Yes"), "no"		=> gTranslate('common', "No")),
		'value'		=> $gallery->album->fields['dimensionsAsPopup']
	),
	'use_exif' => array(
		'prompt'	=> gTranslate('common', "Display EXIF data?"),
		'desc'		=> '',
		'choices'	=> array('yes' => gTranslate('common', "Yes"), 'no' => gTranslate('common', "No")),
		'value'		=> $gallery->album->fields['use_exif'],
		'skip'		=> (empty($gallery->app->use_exif)) ? true : false
	),
	'group_display_end' => array (
		'type'		=> 'group_end'
	),
	'group_services' => array (
		'type'		=> 'group_start',
		'default'	=> 'none',
		'title'		=> gTranslate('common', "Ser_vices"),
		'desc'		=> ''
	),
	'print_photos' => array(
		'prompt'	=> gTranslate('common', "Which photo printing services<br>do you want to let visitors use?"),
		'desc'		=> '',
		'multiple_choices' => array(
			'photoaccess'	=> '<a href="http://www.photoworks.com/" target="_blank">PhotoWorks</a>',
			'shutterfly' 	=> '<a href="http://www.shutterfly.com/" target="_blank">Shutterfly</a>',
			'fotokasten' 	=> '<a href="http://www.fotokasten.de/" target="_blank">Fotokasten</a>',
			'mpush'	  	=> '<a href="http://www.mpush.cc/" target="_blank">mPush</a>'
		),
		'value'		=> $gallery->album->fields['print_photos']
	),
	'ecards' => array(
		'prompt'	=> gTranslate('common', "Enable Ecards?"),
		'desc'		=> '',
		'choices'	=> array('yes' => gTranslate('common', "Yes"), 'no' => gTranslate('common', "No")),
		'value'		=> isset($gallery->album->fields['ecards']) ? $gallery->album->fields['ecards'] : 'no',
		'skip'		=> ($gallery->app->emailOn == 'yes') ? false : true
	),
	'group_services_end' => array (
		'type'		=> 'group_end'
	),
	'group_pollProperties' => array (
		'type'		=> 'group_start',
		'default'	=> 'none',
		'title'		=> gTranslate('common', "_Poll Properties"),
		'desc'		=> ''
	),
	'voter_class' => array(
	'prompt'	=> gTranslate('common', "Who can vote?"),
        'desc'		=> gTranslate('common', "This enables/disable voting and if enabled it controls who can vote."),
	'choices'	=> array("Logged in" => gTranslate('common', "Logged in"),
				 "Everybody" => gTranslate('common', "Everybody"),
				 "Nobody" => gTranslate('common', "Nobody")),
	'value'		=> $gallery->album->fields['voter_class']
	),
	'poll_type' => array(
		'prompt'	=> gTranslate('common', "Type of poll for this album"),
		'desc'		=> '',
		'choices'	=> array("rank" => gTranslate('common', "Rank"), "critique" => gTranslate('common', "Critique")),
		'value'		=> $gallery->album->fields['poll_type']
	),
	'poll_scale' => array(
		'prompt'	=> gTranslate('common', "Number of voting options"),
		'desc'		=> '',
		'type'		=> 'text',
		'value'		=> $gallery->album->getPollScale(),
		'vartype'	=> 'int_ZeroEmpty'
	),
	'poll_show_results' => array(
		'prompt'	=> gTranslate('common', "Show results of voting to all visitors?"),
		'desc'		=> '',
		'choices' 	=> array("yes" => gTranslate('common', "Yes"), "no" => gTranslate('common', "No")),
		'value'		=> $gallery->album->fields['poll_show_results']
	),
	'poll_num_results' => array(
		'prompt'	=> gTranslate('common', "Number of lines of results graph to display on the album page"),
		'desc'		=> '',
		'type'		=> 'text',
		'value'		=> $gallery->album->getPollNumResults(),
		'vartype'	=> 'int_ZeroEmpty'
	),
	'poll_orientation' => array(
        	'prompt'	=> gTranslate('common', "Orientation of vote choices?"),
		'desc'		=> '',
		'choices'	=> array('horizontal' => gTranslate('common', "Horizontal"),
					 'vertical' => gTranslate('common', "Vertical")),
		'value'		=> isset($gallery->album->fields['poll_orientation']) ?
		  				$gallery->album->fields['poll_orientation'] : ''
		),
	'poll_hint' => array(
		'prompt'	=> gTranslate('common', "Vote hint"),
		'desc'		=> '',
		'value'		=> $gallery->album->getPollHint(),
		'type'		=> 'textarea',
		'attrs'		=> array('cols' => 40, 'rows' => 2)
	),
	'poll_displayed_values' => array(
		'prompt' =>	gTranslate('common', "Voting Options"),
		'desc'		=> '',
		'type'		=> 'table_values',
		'elements'	=> buildVotingInputFields(),
		'columns'	=> array(gTranslate('common', "Displayed Value"),gTranslate('common', "Points")),
		'value'		=> ''
	),
	'group_pollProperties_end' => array (
		'type'		=> 'group_end'
	),
	'group_misc' => array (
		'type'		=> 'group_start',
		'default'	=> 'none',
		'title'		=> gTranslate('common', "_Misc"),
	),
	'add_to_beginning' => array(
        	'prompt'	=> gTranslate('common', "Add new items at beginning of album?"),
		'desc'		=> '',
		'choices'	=> array('yes' => gTranslate('common', "Yes"), 'no' => gTranslate('common', "No")),
		'value'		=> $gallery->album->fields['add_to_beginning']
	),
	'returnto' => array(
        	'prompt'	=> gTranslate('common', "Show <i>Return to</i> link?"),
		'desc'		=> '',
		'choices'	=> array('yes' => gTranslate('common', "Yes"), 'no' => gTranslate('common', "No")),
		'value'		=> $gallery->album->fields['returnto']
	),
	'use_fullOnly' => array(
        	'prompt'	=> gTranslate('common', "Offer visitors ability to specify<br>preference for full-size or resized images?"),
		'desc'		=> '',
		'choices'	=> array('yes' => gTranslate('common', "Yes"), 'no' => gTranslate('common', "No")),
		'value'		=> $gallery->album->fields['use_fullOnly']
	),
	'group_misc_end' => array (
		'type'		=> 'group_end'
	),
	'group_owner_permission' => array (
		'type'		=> 'group_start',
		'default'	=> 'none',
		'title'		=> gTranslate('common', "Owner _Permissions"),
		'desc'		=> gTranslate('common', "For historical reasons permissions for owners are handled as property, not as permission. ;-) G1-Style ;-)")
	),
	'item_owner_modify' => array(
        	'prompt'	=> gTranslate('common', "Allow users to modify their own items?"),
		'desc'		=> '',
		'choices'	=> array('yes' => gTranslate('common', "Yes"), 'no' => gTranslate('common', "No")),
		'value'		=> $gallery->album->fields['item_owner_modify']
	),
	'item_owner_delete' => array(
        	'prompt'	=> gTranslate('common', "Allow users to delete their own items?"),
		'desc'		=> '',
		'choices'	=> array('yes' => gTranslate('common', "Yes"), 'no' => gTranslate('common', "No")),
		'value'		=> $gallery->album->fields['item_owner_delete']
	),
	'group_owner_permission_end' => array (
		'type'		=> 'group_end'
	),
	'group_effects' => array (
		'type' =>	'group_start',
		'default' =>	'none',
		'title' =>	gTranslate('common', "_Effects")
	),
	'fit_to_window' => array(
        	'prompt'	=> gTranslate('common', "Auto fit-to-window for<br>images without a resized copy?"),
		'desc'		=> '',
		'choices'	=> array('yes' => gTranslate('common', "Yes"), 'no' => gTranslate('common', "No")),
		'value'		=> $gallery->album->fields['fit_to_window']
	),
	'lightbox' => array(
        	'prompt'	=> gTranslate('common', "Turn on the lightbox effect view in thumbs?"),
		'desc'		=> '',
		'choices'	=> array('yes' => gTranslate('common', "Yes"), 'no' => gTranslate('common', "No")),
		'value'		=> $gallery->album->fields['lightbox']
	),
	'group_effects_end' => array (
		'type' =>	'group_end'
	),
	'group_CustomFields' => array (
		'type'		=> 'group_start',
		'default'	=> 'none',
		'title'		=> gTranslate('common', "Custom _Fields")
	),
	'extra_fields' => array(
		'prompt'	=> '',
		'desc'		=> '',
		'multiple_choices' => isset($multiple_choices_EF) ? $multiple_choices_EF : '',
		'value'		=> !empty($checked_EF) ? $checked_EF : '',
	),
	'num_user_fields' => array(
		'prompt'	=> gTranslate('common', "Number of user defined custom fields"),
		'desc'		=> '',
		'type'		=> 'text',
		'value'		=> $num_user_fields,
		'attrs'		=> array('size' => 2),
	)
);
if (isset($customFields)) {
	$properties = array_merge($properties, $customFields);
}
$properties = array_merge($properties, array(
	'group_CustomFields_end' => array (
		'type'		=> 'group_end'
	),
	'group_MicroThumbs'		=> array (
		'type'		=> 'group_start',
		'default'	=> 'none',
		'title'		=> gTranslate('common', "M_icrothumbs")
	),
	'nav_thumbs' => array(
        	'prompt'	=> gTranslate('common', "Use microthumb photo navigation?"),
		'desc'		=> '',
		'choices'	=> array(
					'yes'	=> gTranslate('common', "Yes"),
					'no'	=> gTranslate('common', "No, just the normal navigation"),
					'both'	=> gTranslate('common', "Both kinds of Navigation")),
		'value'		=> $gallery->album->fields['nav_thumbs']
	),
	'nav_thumbs_style' => array(
		'prompt'	=> gTranslate('common', "Microthumb style?"),
		'desc'		=> '',
		'choices'	=> array("fixed" => gTranslate('common', "Fixed"), "dynamic" => gTranslate('common', "Dynamic")),
		'value'		=> $gallery->album->fields['nav_thumbs_style']
	),
	'nav_thumbs_first_last' => array(
        	'prompt'	=> gTranslate('common', "Show first &amp; last microthumb?"),
		'desc'		=> '',
		'choices'	=> array('yes' => gTranslate('common', "Yes"), 'no' => gTranslate('common', "No")),
		'value'		=> $gallery->album->fields['nav_thumbs_first_last']
	),
	'nav_thumbs_prev_shown' => array(
		'prompt'	=> gTranslate('common', "Number of previous thumbs?"),
		'desc' 		=> '',
		'choices' =>	array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10),
		'value'		=> $gallery->album->fields['nav_thumbs_prev_shown']
	),
	'nav_thumbs_next_shown' => array(
		'prompt'	=> gTranslate('common', "Number of next thumbs?"),
		'desc'		=> '',
		'choices'	=> array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10),
		'value'		=> $gallery->album->fields['nav_thumbs_next_shown']
	),
	'nav_thumbs_location' => array(
		'prompt'	=> gTranslate('common', "Position of microthumb navigation bar?"),
		'desc'		=> '',
		'choices'	=> array('top'	  => gTranslate('common', "Top"),
					 'both'	  => gTranslate('common', "Both"),
					 'bottom' => gTranslate('common', "Bottom")),
		'value'		=> $gallery->album->fields['nav_thumbs_location']
	),
	'nav_thumbs_size' => array(
        	'prompt' => gTranslate('common', "Height of microthumbs in navigation bar (in px)?"),
		'desc'		=> '',
		'type'		=> 'text',
		'value'		=> $gallery->album->fields['nav_thumbs_size'],
		'attrs'		=> array('size' => 3)
	),
	'nav_thumbs_current_bonus' => array(
        	'prompt'	=> gTranslate('common', "Bonus to height of current microthumb (in px)?"),
		'desc'		=> '',
		'type'		=> 'text',
		'value'		=> $gallery->album->fields['nav_thumbs_current_bonus'],
		'attrs'		=> array('size' => 3)
	),
	'group_MicroThumbs_end' => array (
		'type' => 'group_end'
	),
  )
);

?>