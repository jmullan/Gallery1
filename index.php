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
// Hack prevention.

global $GALLERY_EMBEDDED_INSIDE;
global $GALLERY_EMBEDDED_INSIDE_TYPE;
global $GALLERY_MODULENAME;
global $op;
global $mop;
global $include;
global $name;
global $option;
global $MOS_GALLERY_PARAMS;

/* Detect PHP-Nuke, Postnuke, phpBB2 or Mambo and react accordingly.
 * Gallery can run embedded in GeekLog too, but to catch this we need
 * config.php * Therefore we have to detect GeeLog in init.php.
 */
if (!strcmp($op, "modload") || !strcmp($mop, "modload") || isset($option)) {

	/* 
	 * Change this variable if your Gallery module has a different
	 * name in the Nuke or phpBB2 modules directory.
	 */

	if (isset($name)) {
		$GALLERY_MODULENAME = $name;
		define ('GALLERY_URL',"modules/$GALLERY_MODULENAME/");
	}
	
	if (isset($option)) {
		$GALLERY_MODULENAME = $option;
		$mamboDir = getcwd();
		define ('GALLERY_URL',$MOS_GALLERY_PARAMS['path']);
		$GALLERY_EMBEDDED_INSIDE = 'mambo';
		$GALLERY_EMBEDDED_INSIDE_TYPE = 'mambo';
	}
	elseif (isset($GLOBALS['phpbb_root_path'])) {
		$GALLERY_EMBEDDED_INSIDE='phpBB2';
		$GALLERY_EMBEDDED_INSIDE_TYPE = 'phpBB2';
	}
	elseif (isset($GLOBALS['pnconfig']) && function_exists('authorised')) {
		$GALLERY_EMBEDDED_INSIDE='nuke';
		$GALLERY_EMBEDDED_INSIDE_TYPE = 'postnuke';
	}
	elseif ($GLOBALS['user_prefix'] == "nukea") {
		$GALLERY_EMBEDDED_INSIDE='nuke';
		$GALLERY_EMBEDDED_INSIDE_TYPE = 'nsnnuke';
	}
	else {
		$GALLERY_EMBEDDED_INSIDE='nuke';
		$GALLERY_EMBEDDED_INSIDE_TYPE = 'phpnuke';
	}

	if (empty($include)) {
		if (!empty($HTTP_GET_VARS['include'])) {
			$include = $HTTP_GET_VARS['include'];
		}
		elseif (!empty($HTTP_POST_VARS['include'])) {
			$include = $HTTP_POST_VARS['include'];
		}
		else {
			$include = "albums.php";
		}
	}
	elseif (empty($include) && !empty($HTTP_GET_VARS['include'])) {
		// Kludge for postnuke .75 - they're not extracting all GET
		// variables any longer, which is good security, but broke us.
		$include = $HTTP_GET_VARS['include'];
	}

	/*
	 * As a security precaution, only allow one of the following files to be included.
	 * If you want Gallery to allow you to include other files (such as the random photo block)
	 * then you need to add the name of the file including any relevant path components to this
	 * array.
	 */
	$safe_to_include =
		 array(
		       "add_comment.php",
		       "add_photos.php",
		       "add_photos_frame.php",
		       "album_permissions.php",
		       "albums.php",
		       "block-random.php",
		       "captionator.php",
		       "copy_photo.php",
		       "create_user.php",
		       "delete_album.php",
		       "delete_photo.php",
		       "delete_user.php",
		       "do_command.php",
		       "edit_appearance.php",
		       "edit_caption.php",
		       "edit_field.php",
		       "edit_thumb.php",
		       "edit_watermark.php",
		       "extra_fields.php",
		       "gallery_remote.php",
		       "gallery_remote2.php",
		       "highlight_photo.php",
		       "login.php",
		       "manage_users.php",
		       "modify_user.php",
		       "move_album.php",
		       "move_photo.php",
		       "multi_create_user.php",
		       "photo_owner.php",
		       "poll_properties.php",
		       "poll_results.php",
		       "progress_uploading.php",
		       "publish_xp.php",
		       "publish_xp_docs.php",
		       "register.php",
		       "rename_album.php",
		       "reset_votes.php",
		       "resize_photo.php",
		       "rotate_photo.php",
		       "save_photos.php",
		       "search.php",
		       "slideshow.php",
		       "slideshow_high.php",
		       "slideshow_low.php",
		       "sort_album.php",
		       "upgrade_album.php",
		       "upgrade_users.php",
		       "user_preferences.php",
		       "view_album.php",
		       "view_comments.php",
		       "view_photo.php",
		       "view_photo_properties.php",
		       "watermark_album.php",
		       );
	
	if (!in_array($include, $safe_to_include)) {
	    $include = escapeshellcmd($include);
	    print sprintf(_("Security error!  The file you tried to include is not on the <b>approved file list</b>.  To include this file you must edit %s's index.php and add <b>%s</b> to the <i>\$safe_to_include</i> array"), 
			    'Gallery', $include);
	    exit;
	}
	include(dirname(__FILE__) . "/$include");
} else {
	include("albums.php");
}
?>
