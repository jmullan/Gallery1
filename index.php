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

global $GALLERY_EMBEDDED_INSIDE;
global $GALLERY_EMBEDDED_INSIDE_TYPE;
global $GALLERY_MODULENAME;
global $MOS_GALLERY_PARAMS;

$_REQUEST = array_merge($_GET, $_POST);

// Mambo / Joomla calls index.php directly for popups - we need to make
// sure that the option var has been extracted into the environment
// otherwise it just won't work.
$option		= isset($_REQUEST['option']) ? $_REQUEST['option'] : null;
$op		= isset($_REQUEST['op']) ? $_REQUEST['op'] : null;
$mop		= isset($_REQUEST['mop']) ? $_REQUEST['mop'] : null;
$name		= isset($_REQUEST['name']) ? $_REQUEST['name'] : null;
$include	= isset($_REQUEST['include']) ? $_REQUEST['include'] : null;
$postnuke	= ( defined('_PN_VERSION_ID') || defined('PN_VERSION_ID') ) ? true : false;
$phpnuke	= isset($GLOBALS['nukeurl']) ? true : false;

/*
 * Detect PHP-Nuke, Postnuke, phpBB2 or Mambo and react accordingly.
 * Gallery can run embedded in GeekLog too, but to catch this we need
 * config.php * Therefore we have to detect GeekLog in init.php.
 *
 */
if ($postnuke ||
	$phpnuke ||
	!strcmp($op, "modload") ||
	!strcmp($mop, "modload") ||
	isset($option))
{
	/*
	 * Change this variable if your Gallery module has a different
	 * name in the Nuke or phpBB2 modules directory.
	*/

	if (isset($name)) {
		$GALLERY_MODULENAME = $name;
		define ('GALLERY_URL',"modules/$GALLERY_MODULENAME");
	}

	if (isset($option)) {
		$GALLERY_MODULENAME = $option;
		$mamboDir = getcwd();
		$GALLERY_EMBEDDED_INSIDE = 'mambo';
		$GALLERY_EMBEDDED_INSIDE_TYPE = 'mambo';

		if (isset($GLOBALS['_VERSION']->PRODUCT) &&
		  $GLOBALS['_VERSION']->PRODUCT == 'Joomla!') {
			$GALLERY_EMBEDDED_INSIDE = 'joomla';
			$GALLERY_EMBEDDED_INSIDE_TYPE = 'joomla';
		}
	}
	elseif (isset($GLOBALS['phpbb_root_path'])) {
		$GALLERY_EMBEDDED_INSIDE='phpBB2';
		$GALLERY_EMBEDDED_INSIDE_TYPE = 'phpBB2';
	}
	elseif ($postnuke) {
		$GALLERY_EMBEDDED_INSIDE='nuke';
		$GALLERY_EMBEDDED_INSIDE_TYPE = 'postnuke';
		if(defined('PN_VERSION_NUM')) {
			// postNuke 0.8
			$GALLERY_POSTNUKE_VERSION = PN_VERSION_NUM;
		}
		else {
			$GALLERY_POSTNUKE_VERSION = _PN_VERSION_NUM;
		}
	}
	elseif ($GLOBALS['user_prefix'] == "nukea") {
		$GALLERY_EMBEDDED_INSIDE='nuke';
		$GALLERY_EMBEDDED_INSIDE_TYPE = 'nsnnuke';
	}
	elseif (defined('CPG_NUKE')) {
		$GALLERY_EMBEDDED_INSIDE='nuke';
		$GALLERY_EMBEDDED_INSIDE_TYPE='cpgnuke';
	}
	else {
		$GALLERY_EMBEDDED_INSIDE='nuke';
		$GALLERY_EMBEDDED_INSIDE_TYPE = 'phpnuke';
	}

	if (empty($include)) {
		$include = "albums.php";
	}

	/*
	 * As a security precaution, only allow one of the following files to be included.
	 * If you want Gallery to allow you to include other files (such as the random photo block)
	 * then you need to add the name of the file including any relevant path components to this
	 * array.
	 */
	$safe_to_include = array(
			"admin-page.php",
			"albums.php",
		'block-random.php',
		'block-feature-photo.php',
			"captionator.php",
			"create_user.php",
		'crop_photo.php',
			"delete_user.php",
			"edit_watermark.php",
			"gallery_remote.php",
			"gallery_remote2.php",
			"help/imagemap.php",
			"help/metadataOnUpload.php",
			"highlight_photo.php",
			"imagemap.php",
			"login.php",
			"manage_users.php",
			"modify_user.php",
			"multi_create_user.php",
			"poll_properties.php",
			"poll_results.php",
		'popups/add_comment.php',
		'popups/add_photos.php',
		'popups/add_photos_frame.php',
		'popups/administer_startpage.php',
		'popups/album_permissions.php',
		'popups/colorpicker.php',
		'popups/copy_photo.php',
		'popups/create_group.php',
		'popups/delete_album.php',
		'popups/delete_group.php',
		'popups/delete_item.php',
		'popups/download.php',
		'popups/edit_appearance.php',
		'popups/ecard_form.php',
		'popups/edit_caption.php',
		'popups/edit_field.php',
		'popups/edit_thumb.php',
		'popups/do_command.php',
		'popups/featured-item.php',
		'popups/item_owner.php',
		'popups/manage_groups.php',
		'popups/modify_group.php',
		'popups/move_rootalbum.php',
		'popups/move_albumitem.php',
		'popups/rearrange.php',
		'popups/rebuild_capture_dates.php',
		'popups/rebuild_thumbs.php',
		'popups/recreate_captions.php',
		'popups/rename_album.php',
		'popups/reset_votes.php',
		'popups/resize_photo.php',
		'popups/rotate_photo.php',
		'popups/save_photos.php',
		'popups/sort_album.php',
		'popups/view_photo_properties.php',
		'popups/progress_uploading.php',
			"publish_xp.php",
			"publish_xp_docs.php",
			"register.php",
			"rss.php",
			"search.php",
			"slideshow.php",
			"slideshow_high.php",
			"slideshow_low.php",
			"stats-wizard.php",
			"stamp_preview.php",
			"stats.php",
			"tools/find_orphans.php",
			"tools/despam-comments.php",
			"tools/validate_albums.php",
			"upgrade_album.php",
			"upgrade_users.php",
		'usage.php',
			"user_preferences.php",
			"view_album.php",
			"view_comments.php",
			"view_photo.php",
			"watermark_album.php",
	);

	if (!in_array($include, $safe_to_include)) {
		$include = htmlentities($include);
		printf ("Security error!  The file you tried to include is not on the <b>approved file list</b>.  To include this file you must edit Gallery's index.php and add <b>%s</b> to the <i>\$safe_to_include</i> array",
				$include);
		exit;
	}
	include(dirname(__FILE__) . "/$include");
}
else {
	include("albums.php");
}
?>
