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
 *
 */
?>
<?php

if (!isset($gallery->version)) {
        require_once(dirname(__FILE__) . '/init.php');
}

// Security check
if (!$gallery->user->isAdmin()) {
	header("Location: " . makeAlbumHeaderUrl());
	exit;
}

if ($gallery->user->isAdmin()) {

	$adminOptions[] = array( 'text' => _("statistics"), 
				 'url' => makeGalleryUrl('stats-wizard.php'),
				 'longtext' => _("View some statistics about your Gallery. Such as most viewed pictures, or best rated photos etc."));
	$adminOptions[] = array( 'text' => _("configuration wizard"),
				 'url' => $gallery->app->photoAlbumURL . '/setup/index.php',
				 'longtext' => _("Use the config wizard to reconfigure or tweak your Gallery"));
	$adminOptions[] = array( 'text' => _("find orphans"),
				 'url' => makeGalleryUrl('tools/find_orphans.php'),
				 'longtext' => _("Find, remove or re-attach orphaned elements."));
	$adminOptions[] = array( 'text' => _("find comment spam"),
				 'url' => makeGalleryUrl('tools/despam-comments.php'),
				 'longtext' => _("Find and remove comments that contains spam."));
	$adminOptions[] = array( 'text' => _("validate albums"),
				 'url' => makeGalleryUrl('tools/validate_albums.php'),
				 'longtext' => _("Identify invalid albums, missing files, and other errors that may prevent you from migrating to Gallery 2"));
}


if ($gallery->userDB->canModifyUser() ||
	$gallery->userDB->canCreateUser() ||
	$gallery->userDB->canDeleteUser()) {
		$adminOptions[]  = array('text' => _("manage users"),
					 'popupFile' => 'manage_users.php',
				 	 'longtext' => _("Manage your users."));
}

function cmp ($a, $b) {
   return strcmp($a["text"], $b["text"]);
}

usort($adminOptions, "cmp");

doctype();
?>
<html>
<head>
<title><?php echo $gallery->app->galleryTitle ?></title>
<?php 
	common_header() ;
?>
  <style>
	td.adm_options { vertical-align:top; height:30px; padding: 5px; }
  </style>
</head>
<body dir="<?php echo $gallery->direction ?>">
<?php  
        
includeHtmlWrap("gallery.header");

$borderColor = $gallery->app->default["bordercolor"];
$navigator["fullWidth"] = 100;
$navigator["widthUnits"] = "%";
$adminbox["text"] ='<span class="head">'. _("Admin options") .'</span>';
$adminbox["commands"] = '[<a href="'. makeAlbumUrl() .'">'. _("return to gallery") .'</a>]';

includeLayout('navtablebegin.inc');
includeLayout('adminbox.inc');
includeLayout('navtablemiddle.inc');
includeLayout('breadcrumb.inc');
includeLayout('navtableend.inc');
includeLayout('ml_pulldown.inc');

if(!empty($adminOptions)) {
	echo "\n" .'<table style="width:80%; margin:10px; margin-bottom:50px">';
	foreach ($adminOptions as $option) {
		echo "\n<tr>";
		if (isset($option['url'])) {
			$link = '<a class="admin" href="'. $option['url'] .'">'. $option['text'] .'</a>';
		} else {
			$link = popup_link($option['text'], $option['popupFile'], false, true, 500, 500, 'admin');
		}
		echo "\n<td class=\"adm_options\">$link</td>";
		echo "\n<td class=\"adm_options\">". $option['longtext'] ."</td>";
		echo "\n</tr>";
	}
	echo "\n</table>";
}

$validation_file = basename(__FILE__);
includeHtmlWrap("general.footer");

if (!$GALLERY_EMBEDDED_INSIDE) { ?>
</body>
</html>
<?php } ?>
