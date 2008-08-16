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
 * $Id: defaults.inc 13795 2006-06-11 14:21:15Z jenst $
 */

/**
 * Defines all the albumsettings from step 3.
 *
 * @package Setup
 */


/**
 * Printservices
 */
require_once(GALLERY_BASE . '/includes/definitions/services.php');

$defaults = array(
	'group_layout' => array (
		'type' => 'group_start',
		'default' => 'inline',
		'title' => gTranslate('config',"Layout"),
		'inst_type' => 'easy'
	),
	'cols' => array(
		'prompt' => gTranslate('config',"Default Columns"),
		'desc' => gTranslate('config',"The default number of columns in an album."),
		'value' => 3,
		'choices' => array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9),
	),
	'rows' => array(
		'prompt' => gTranslate('config',"Default Rows"),
		'desc' => gTranslate('config',"The default number of rows in an album."),
		'value' => 3,
		'choices' => array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10),
	),
	'group_layout_end' => array (
		'type' => 'group_end',
	),
	'group_appearance' => array (
		'type' => 'group_start',
		'default' => 'none',
		'title' => gTranslate('config',"Appearance")
	),
	'bordercolor' => array(
		'prompt' => gTranslate('config',"Border color"),
		'desc' => sprintf(gTranslate('config',"Default color of (image) borders. You can learn about %scolors%s here."),
		  '<a href="http://www.permadi.com/tutorial/websafecolor/" target="_blank">', '</a>'),
		'value' => "black",
		'type' => 'colorpicker'
	),
	'border' => array(
		'prompt' => gTranslate('config',"Border width"),
		'desc' => gTranslate('config',"Default width of (image) borders."),
		'value' => 1,
		'choices' => array(0 => 0, 1 => 1, 2 => 2, 3 => 3)
	),
	'font' => array(
		'prompt' => gTranslate('config',"Default Font"),
		'desc' => gTranslate('config',"Default font face.  E.g. <i>arial</i> or <i>trebuchet</i>"),
		'value' => 'arial',
		'attrs' => array('size' => 10),
		'type' => 'text'
	),
	'group_appearance_end' => array (
		'type' => 'group_end',
	),
	'group_display' => array (
		'type' => 'group_start',
		'default' => 'none',
		'title' => gTranslate('config',"Image Processing and Display"),
		'inst_type' => 'easy'
	),
	'thumb_size' => array(
		'prompt' => gTranslate('config',"Thumbnail size"),
		'desc' => gTranslate('config',"The default target size (in pixels) of album thumbnails."),
		'value' => 150,
		'attrs' => array('size' => 10),
		'type' => 'text'
	),
	'thumb_ratio' => array(
		'prompt' => gTranslate('config',"Thumbnail Ratio"),
		'desc' => gTranslate('config',"The ratio in which the thumbnails are made"),
		'choices' => array(
			'0' => gTranslate('config',"As the original image"),
			'1/1' => gTranslate('config',"Square thumbs")
		),
		'value' => 0,
		'inst_type' => 'advanced'
	),
	'resize_size' => array(
		'prompt' => gTranslate('config',"Resized image size"),
		'desc' => gTranslate('config',"When large images are added to an album, an intermediate version will automatically be generated at this size."),
		'value' => 640,
		'choices' => array(
			0 => gTranslate('config',"off"),
			400 => 400,
			500 => 500,
			600 => 600,
			640 => 640,
			700 => 700,
			800 => 800,
			1024 => 1024
		)
	),
	'resize_file_size' => array(
		'prompt' => gTranslate('config',"Resized JPEG/PNG images file size"),
		'desc' => gTranslate('config',"The default target size (in kilobytes) of resized JPEG/PNG images.  Including a value may slow down file uploads, but will speed up page viewing time especially for people with dialup connections.  Set to 0 for no target size."),
		'value' => 0,
		'optional' => true,
		'type' => 'text',
		'inst_type' => 'advanced'
	),
	'max_size' => array(
		'prompt' => gTranslate('config','Maximum image size'),
		'desc' => gTranslate('config',"You can optionally limit the dimensions your images on upload to Gallery.") .
		  '<br><span class="g-attention">'. gTranslate('config',"Warning:") .'</span> '.
		  gTranslate('config',"Enabling this feature will leave you <b>without a full size original</b> image in your Gallery, but allows you to save limited hard drive space. Gallery will attempt to preserve JPEG comments and EXIF data for the shrunken image, but this will require either <b>ImageMagick</b> or the optional <b>jhead</b> programs (see Step 2)."),
		'value' => 'off',
		'choices' => array(
			0 => gTranslate('config',"Off"),
			400 => 400,
			500 => 500,
			600 => 600,
			640 => 640,
			700 => 700,
			800 => 800,
			1024 => 1024,
			1280 => sprintf(gTranslate('config','%d (%d MPix)'), 1280, 1),
			1600 => sprintf(gTranslate('config','%d (%d MPix)'), 1600, 2),
			2048 => sprintf(gTranslate('config','%d (%d MPix)'), 2048, 3)),
        'inst_type' => 'advanced'
	),
	'max_file_size' => array(
		'prompt' => gTranslate('config','Maximum JPEG/PNG image file size'),
		'desc' => gTranslate('config',"You can optionally limit the filesize of your JPEG/PNG images on upload to Gallery.") .
		  '<br><span class="g-attention">'. gTranslate('config',"Warning:") . '</span> '.
		  gTranslate('config',"Enabling this feature will leave you <b>without an original quality image</b> in your Gallery. Set the default target filesize (in kilobytes).  Including a value may slow down file uploads, but will speed up page viewing time especially for people with dialup connections. Set to 0 if you don't want to restrict filesize."),
		'value' => 0,
		'optional' => true,
		'type' => 'text',
		'inst_type' => 'advanced'
	),
	'useOriginalFileNames' => array(
		'prompt' => gTranslate('config',"Preserve Names"),
		'desc' => gTranslate('config',"When uploading images to Gallery, should we preserve the original names?  If you select <i>no</i>, Gallery will assign a new name for each photo or movie that gets uploaded."),
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		),
		'value' => 'yes',
		'inst_type' => 'advanced'
	),
	'add_to_beginning' => array(
		'prompt' => gTranslate('config',"Add to beginning"),
		'desc' => gTranslate('config',"New images added to beginning rather than end of albums."),
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		),
		'value' => 'no',
	),
	'fit_to_window' => array(
		'prompt' => gTranslate('config',"Fit-to-window"),
		'desc' => gTranslate('config',"When viewing photos which don't have an intermediate version, Gallery can automatically resize the image to fit the viewer's browser window (using JavaScript).  This may not work in embedded environments such as Nuke."),
		'value' => 'no',
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		),
		'inst_type' => 'advanced'
	),
	'use_fullOnly' => array(
		'prompt' => gTranslate('config',"Visitor size prefs"),
		'desc' => gTranslate('config',"Offer visitors the choice of always viewing the full sized version of an image instead of the intermediate size.  This setting only lasts for the visitor's session."),
		'value' => 'no',
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		),
		'inst_type' => 'advanced'
	),
	'group3_end' => array (
		'type' => 'group_end',
	),
	'group_options2' => array (
		'type' => 'group_start',
		'title' => gTranslate('config',"Options"),
		'default' => 'none',
	),
	/* old .. needs to be fixed */
	'print_photos' => array(
		'prompt' => gTranslate('config',"Printing Services"),
		'desc' => gTranslate('config',"Allow visitors to print photos directly from the album.  Fotoserve is based in the United Kindom and Fotokasten is a print service based in Germany so they are good for the European market. EZ Prints, PhotoWorks and Shutterfly are based in the United States. mPUSH is a service that allows you to send photos to cellular phones worldwide. All services will give a small donation to the Gallery project for every print you make. Using these services is a great way for you to help support this free software."),
		'multiple_choices' => makeMultipleChoiceContent($services),
		'value' => array(
			'fotokasten' => array(
				'checked' => true
			),
			'photoaccess' => array(
				'checked' => true
			),
			'shutterfly' => array(
				'checked' => true
			),
			'fotoserve' => array(
				'checked' => true
			),
			'mpush' => array(
				'checked' => true,
				'account' => 'gallery'
			),
		),
		'optional' => true
	),
	'mPUSHAccount' => array(
		'prompt' => gTranslate('config',"Account for the mPUSH service"),
		'desc' => sprintf(gTranslate('config',"If you want to use mPUSH cellular photo service you can either use your own account, or leave this to 'gallery'. Visit this link to %screate your own account%s."),
		  '<a href="http://mpush.msolutions.cc/safe.php?p=Register&amp;ref=gallery" target="_blank">', '</a>'),
		'value' => 'gallery',
		'type' => 'text',
	),
	'ecards' => array(
		'prompt' => gTranslate('config',"Enable Ecards ?"),
		'desc' => gTranslate('config',"Set this to 'yes' to allow users to send a picure as eCard via eMail.") .
		  '<div class="g-attention">' . gTranslate('config',"This option requires the Email feature to be enabled.") . '</div>',
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		),
		'value' => 'no'
	),
	'returnto' => array(
		'prompt' => gTranslate('config',"Gallery Link"),
		'desc' => gTranslate('config',"Display a link navigation from your album to the top level Gallery link within the album?  If you turn this feature off, the user will not be able to navigate out of the album.  This is only useful when you want to embed an album within a website and not allow users to (easily) view the other albums in the gallery."),
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		),
		'value' => 'yes',
	),
	'defaultPerms' => array(
		'prompt' => gTranslate('config',"Default Album Permissions"),
		'desc' => gTranslate('config',"Defines the default set of permissions for an album.  Specifically, who can see the album, and view its photos."),
		'choices' => array(
			'everybody' => gTranslate('config',"Everybody"),
			'loggedin' => gTranslate('config',"Logged-In Users"),
			'nobody' => gTranslate('config',"Nobody")
		),
		'value' => 'everybody',
	),
	'group4_end' => array (
		'type' => 'group_end',
	),
	'group_displayed_data' => array (
		'type' => 'group_start',
		'default' => 'none',
		'title' => gTranslate('config',"Data displayed"),
		'inst_type' => 'easy'
	),
	'display_clicks' => array(
		'prompt' => gTranslate('config',"Click counts"),
		'desc' => gTranslate('config',"Display click counts within Albums?"),
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		),
		'value' => 'yes',
	),
	'extra_fields' => array(
		'prompt' => gTranslate('config',"Custom Fields"),
		'optional' => 1,
		'desc' => gTranslate('config',"Custom fields that are available to describe each image.  They should be separated by commas.  E.g. <i>Location, Film Type</i>"),
		'attrs' => array('size' => 80),
		'type' => 'text',
		'value' => '',
		'inst_type' => 'advanced'
	),
	'showDimensions' => array(
		'prompt' => gTranslate('config',"Clickable image dimensions"),
		'desc' => gTranslate('config',"If set to 'yes', then clickable dimensions for each available size of the picture are shown beneath the thumbnails . You may click on these instead of the thumbnail to either image."),
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		),
		'value' => 'no'
	),
	'dimensionsAsPopup' => array(
		'prompt' => gTranslate('config',"Dimensions-link opens popup"),
		'desc' => gTranslate('config',"If you show the dimensions-links, you can choose whether you just want the images to appear in a popup, or open the complete photoview."),
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		),
		'value' => 'no'
	),
	'group5_end' => array (
		'type' => 'group_end',
	),
	'group_itemOwner' => array (
		'type' => 'group_start',
		'default' => 'none',
		'title' => gTranslate('config',"Item Owner")
	),
	'item_owner_modify' => array(
		'prompt' => gTranslate('config',"Photo owner modification"),
		'desc' => gTranslate('config',"Allow photo owners to edit and rotate/flip their own photos?"),
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		),
		'value' => 'yes',
	),
	'item_owner_delete' => array(
		'prompt' => gTranslate('config',"Photo owner deletion"),
		'desc' => gTranslate('config',"Allow photo owners to delete their own photos?"),
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")),
		'value' => 'yes',
	),
	'item_owner_display' => array(
		'prompt' => gTranslate('config',"Photo owner display"),
		'desc' => gTranslate('config',"Display the name of photo owners with the caption?"),
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		),
		'value' => 'no',
	),
	'group6_end' => array (
		'type' => 'group_end'
	),
	'group_polling' => array (
		'type' => 'group_start',
		'default' => 'none',
		'title' => gTranslate('config',"Polling configuration"),
		'desc' =>
		  sprintf(gTranslate('config',"It allows your visitors to vote for or rate the contents of an album.  You set defaults for polling here, which will apply to new albums, and to all existing albums when you upgraded from an earlier version then %s."),
		  '1.4.1') .
		  '<p>' .
		  gTranslate('config',"In each album, voting can be switched off, restricted to logged in users or open to all visitors.")
		  .'<p>' .
		  sprintf(gTranslate('config',"There are two sorts of polls: %s, which allows voters to chose their favourites as first, second, third etc., and %s, which allows voters to rate any or all of the images without restriction.  %s is like a film review where all the images can get (for example) 5 points, while %s is useful in scoring competitions."),
		  '<b>'. gTranslate('config',"Rank") .'</b>',
		  '<b>'. gTranslate('config',"Critique") .'</b>',
		  gTranslate('config',"Critique"),
		  gTranslate('config',"Rank")
		  )
	),
	'voter_class' => array(
		'prompt' => gTranslate('config',"Voter class"),
		'desc' => gTranslate('config',"Voting is available to:") .
			'<dl>'
			.'<dt>'. gTranslate('config',"Nobody").'</dt>'
			.'<dd>'. gTranslate('config',"Voting is off.") .'</dd>'
			.'<dt>'. gTranslate('config',"Everybody") . '</dt>'
			.'<dd>'. gTranslate('config',"Anyone can vote.  Votes are recorded against session ID.") .'</dd>'
			.'<dt>'. gTranslate('config',"Logged in") .'</dt>'
			.'<dd>'. gTranslate('config',"Registered users can vote.  Votes are recorded against User ID, and voters can come back and change their choices.") . '</dd>'
			.'</dl>' ,
		'choices' => array(
			'Nobody' => gTranslate('config',"Nobody"),
			'Everybody' => gTranslate('config',"Everybody"),
			'Logged in' => gTranslate('config',"Logged in")
		),
		'value' => 'Nobody'
		),
	'poll_type' => array(
		'prompt' => gTranslate('config',"Poll type"),
		'desc' => gTranslate('config',"Gallery offers two sorts of polls for voting for images.  See above for a more detailed explanation.") .
		'<dl>'
		.'<dt>'. gTranslate('config',"Rank").'</dt>'
		.'<dd>'. gTranslate('config',"Users have a set of votes which they can allocate to the images.") . '</dd>'
		.'<dt>'. gTranslate('config',"Critique").'</dt>'
		.'<dd>'. gTranslate('config',"Enables users to give all images points without restriction.") . '</dd>'
		.'</dl>' ,
		'choices' => array(
			'rank' => gTranslate('config',"Rank"),
			'critique' => gTranslate('config',"Critique")
		),
		'value' => 'critique'
	),
	'poll_scale' => array(
		'prompt' => gTranslate('config',"Number of choices"),
		'desc' => gTranslate('config',"Number of choices that will be available to voters as radio buttons or in a pulldown menu."),
		'value' => 3,
		'choices' => array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9)
	),
	'poll_hint' => array(
		'prompt' => gTranslate('config',"Vote hint"),
		'desc' => gTranslate('config',"This is displayed above the voting options under each image."),
		'type' => 'text',
		'value' => gTranslate('config',"Vote for this image"),
		'attrs' => array('size' => 50),
		'optional' => 1
	),
	'poll_show_results' => array(
		'prompt' => gTranslate('config',"Show vote results"),
		'desc' => sprintf(gTranslate('config',"Display a summary and graph on the album page of images that have received the highest votes, and provide a link to the full results.  If this option is %s, results of voting will only be available to admins and album owners."),
		  gTranslate('config',"No")),
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		),
		'value' => 'no',
	),
	'poll_num_results' => array(
		'prompt' => gTranslate('config',"Number of results displayed"),
		'desc' => sprintf(gTranslate('config',"If %s is %s, display a graph showing this many top results.  (Full results will be available on a linked page.)"),
		  '"'. gTranslate('config',"Show vote results").'"' ,
		  '"'. gTranslate('config',"Yes").'"'),
		  'value' => 3,
		'choices' => array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9)
	),
	'poll_orientation' => array(
		'prompt' => gTranslate('config',"Orientation of vote choices?"),
		'desc' => gTranslate('config',"Rank poll choices can be displayed beside each other (horizontally) or underneath each other (vertically).  Has no effect on critique voting which is a pulldown menu."),
		'value' => 'vertical',
		'choices' => array(
			'horizontal' => gTranslate('config',"Horizontal"),
			'vertical' => gTranslate('config',"Vertical"))
	) ,
	'poll_nv_pairs' => array(
		'prompt' => gTranslate('config',"Vote words and values"),
		'desc' => gTranslate('config',"This allows you to configure the actual text that will be displayed for each voting option, plus the number of points that option is worth."),
		'value' => array (
			array('name' => gTranslate('config',"Excellent"), 'value' => 5),
			array('name' => gTranslate('config',"Very Good"), 'value' => 4),
			array('name' => gTranslate('config',"Good"), 'value' => 3),
			array('name' => gTranslate('config',"Average"), 'value' => 2),
			array('name' => gTranslate('config',"Poor"), 'value' => 1)
		),
		'type' => 'nv_pairs',
		'verify-func' => 'check_poll_nv_pairs',
		'size' => 9
	),
	'group7_end' => array (
		'type' => 'group_end',
	),
	'group_albumSlidshow' => array (
		'type' => 'group_start',
		'default' => 'none',
		'title' => gTranslate('config',"Album Slideshow"),
		'desc' => gTranslate('config',"These settings do not apply to the low tech slideshow."),
		'inst_type' => 'easy'
	),
	'slideshow_type' => array(
		'prompt' => gTranslate('config',"Slideshow Type"),
		'desc' => '<dl>'
		.'<dt>'. gTranslate('config',"Off") .'</dt>'
		.'<dd>'. gTranslate('config',"No slideshow link will appear.") . '</dd>'
		.'<dt>'. gTranslate('config',"Ordered") . '</dt>'
		.'<dd>'. gTranslate('config',"Images will display in the same order as they appear in the Gallery.") . '</dd>'
		.'<dt>'. gTranslate('config',"Random") . '</dt>'
		.'<dd>'. gTranslate('config',"Images will appear in a random order.") . '</dd>'
		.'</dl>',
		'choices' => array(
			'off' => gTranslate('config',"Off"),
			'ordered' => gTranslate('config',"Ordered"),
			'random' => gTranslate('config',"Random"),
		),
		'value' => 'ordered',
	),
	'slideshow_recursive' => array(
		'prompt' => gTranslate('config',"Recursive slideshow"),
		'desc' => sprintf(gTranslate('config',"Set to %s if you want to include images in sub albums as part of the slideshow."), '<b>'.gTranslate('config',"Yes").'</b>'),
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		),
		'value' => 'no'
	),
	'slideshow_loop' => array(
		'prompt' => gTranslate('config',"Allow slideshow to loop"),
		'desc' => sprintf(gTranslate('config',"This option will allow visitors to have your slideshow repeat until they close the window or go to another page.  Set it to %s if you want to save bandwidth."), '<b>'.gTranslate('config',"No").'</b>'),
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		),
		'value' => 'yes'
	),
	'slideshow_length' => array(
		'prompt' => gTranslate('config',"Slideshow length"),
		'desc' => gTranslate('config',"Number of images to display in slideshow.  Set to 0 or blank to display all images.  Set to a number if slideshow is random and you want to display only a selection of the images."),
		'type' => 'text',
		'value' => 0,
		'optional' => 1,
		'attrs' => array('size' => 5)
	),
	'group8_end' => array (
		'type' => 'group_end',
	),
	'group_photoMicroNav' => array (
		'type' => 'group_start',
		'default' => 'none',
		'title' => gTranslate('config',"Microthumb Photo Navigation"),
		'desc' => gTranslate('config',"These settings apply to the microthumb photo navigation system.  The microthumbs are only visible when viewing individual images.")
	),
	'nav_thumbs' => array(
		'prompt' => gTranslate('config',"Microthumb Photo Navigation"),
		'desc' => gTranslate('config',"Uses microthumbs for photo navigation. This creates a navigation panel with tiny thumbnails of the images around the photo you are viewing.  You can also choose both to display the microthumb navigation system AND the old style navigation system."),
		'value' => 'no',
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No"),
			'both' => gTranslate('config',"both")
		)
	),
	'nav_thumbs_style' => array(
		'prompt' => gTranslate('config',"Microthumb Photo Navigation"),
		'desc' => '<dl>'
		  .'<dt>'. gTranslate('config',"Fixed") .'</dt>'
		  .'<dd>'. gTranslate('config',"Displays prev and next groups of thumbs, changing only to display next group of thumbs.") . '</dd>'
		  .'<dt>'. gTranslate('config',"Dynamic") . '</dt>'
		  .'<dd>'. gTranslate('config',"Displays previous and next images, with current image between.") . '</dd>'
		  .'</dl>',
		'value' => 'fixed',
		'choices' => array(
			'fixed' => gTranslate('config',"Fixed"),
			'dynamic' => gTranslate('config',"Dynamic")
		)
	),
	'nav_thumbs_first_last' => array(
		'prompt' => gTranslate('config',"Show Microthumb for First/Last"),
		'desc' => gTranslate('config',"This allows you to turn on and off the display of the first and last microthumb.  Since these two images are always the same, some might find that it is not very useful and would rather display more next/previous thumbs."),
		'value' => 'no',
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		)
	),
	'nav_thumbs_prev_shown' => array(
		'prompt' => gTranslate('config',"Number of Previous Microthumbs"),
		'desc' => gTranslate('config',"This is the number of previous microthumbs on the screen at a time.  You might prefer to show fewer previous microthumbs and more next microthumbs."),
		'value' => 1,
		'choices' => array(
			1 => 1, 2 => 2, 3 => 3, 4 => 4,
			5 => 5, 6 => 6, 6 => 6, 7 => 7,
			8 => 8, 9 => 9, 10 => 10
		)
	),
	'nav_thumbs_next_shown' => array(
		'prompt' => gTranslate('config',"Number of Next Microthumbs"),
		'desc' => gTranslate('config',"This is the number of next microthumbs on the screen at a time.  You might prefer to show fewer previous microthumbs and more next microthumbs."),
		'value' => 1,
		'choices' => array(
			1 => 1, 2 => 2, 3 => 3, 4 => 4,
			5 => 5, 6 => 6, 6 => 6, 7 => 7,
			8 => 8, 9 => 9, 10 => 10
		)
	),
	'nav_thumbs_location' => array(
		'prompt' => gTranslate('config',"Location of Microthumb Navigation"),
		'desc' => gTranslate('config',"Displays the microthumb navigation bar at the Top, Bottom or Both of the photo viewing page.  Since the microthumb navigation system is larger than the original navigation system, you may want to display it at only the top or bottom to conserve space usage."),
		'value' => 'top',
		'choices' => array(
			'top' => gTranslate('config',"Top"),
			'both' => gTranslate('config',"Both"),
			'bottom' => gTranslate('config',"Bottom")
		)
	),
	'nav_thumbs_size' => array(
		'prompt' => gTranslate('config',"Height of Microthumbs"),
		'desc' => gTranslate('config',"The size of microthumbs will determine how clear the images are as well as how much room they take up.  This value is the height and is in pixels.  45 is a reasonable value for viewing at 1024x768."),
		'value' => '45',
		'type' => 'text'
	),
	'nav_thumbs_current_bonus' => array(
		'prompt' => gTranslate('config',"Bonus to Current Microthumb's Size"),
		'desc' => gTranslate('config',"This is the bonus in pixels to the size of the current microthumb.  This creates a nice look and a value of 10-15 is reasonable.  Zero (0) and negative numbers (-10) are also valid here.  Zero (0) will result in the current thumb being of identical size to all the other microthumbs and a negative value will result in a smaller microthumb."),
		'value' => '15',
		'type' => 'text'
	),
	'group_MicroNav_End' => array (
		'type' => 'group_end',
	),
	'group_frames' => array (
		'type' => 'group_start',
		'default' => 'none',
		'title' => gTranslate('config',"Frames"),
		'desc' => sprintf(gTranslate('config',"Each image (thumbnails, resized and full sized) in %s can have a frame.  The following styles available:"),Gallery()) .
		available_frames(true),
	),
	'album_frame' => array(
		'prompt' => gTranslate('config',"Subalbum frames"),
		'desc' => gTranslate('config',"This frame will appear around the thumbnail image representing a subalbum."),
		'choices' => available_frames(),
		'value' =>
		'simple_book'
	),
	'thumb_frame' => array(
		'prompt' => gTranslate('config',"Frame around thumbs"),
		'desc' => gTranslate('config',"This frame will appear around the thumbnail image representing a photo or movie."),
		'choices' => available_frames(),
		'value' => 'solid'
	),
	'image_frame' => array(
		'prompt' => gTranslate('config',"Frame around images"),
		'desc' => gTranslate('config',"This frame will appear around fulled sized and resized photos."),
		'choices' => available_frames(),
		'value' => 'solid'
	),
	'group_frames_end' => array (
		'type' => 'group_end',
	),
);
?>