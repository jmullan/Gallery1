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

doctype();
?>

<html>
<head>
<title><?php echo $gallery->app->galleryTitle ?></title>
<?php 
	common_header() ;
?>
</head>
<body dir="<?php echo $gallery->direction ?>">
<?php  
        includeHtmlWrap("gallery.header");
?>
<div style="text-align:right"><a href="<?php echo makeAlbumUrl(); ?>"><?php echo _("Return to Gallery"); ?></a></div>

<div class="head"><?php echo _("Admin options"); ?></div>
<?php

if ($gallery->user->isAdmin()) {

	$adminOptions[] = array( 'text' => _("statistics"), 
				 'url' => makeGalleryUrl('stats-wizard.php'),
				 'longtext' => _("View some statistics about your Gallery. Such as most viewd pictures, or best rated photos etc."));
	$adminOptions[] = array( 'text' => _("configuration wizard"),
				 'url' => $gallery->app->photoAlbumURL . '/setup/index.php',
				 'longtext' => _("Use the config wizard to reconfigure or tweak your Gallery"));
	$adminOptions[] = array( 'text' => _("find orphans"),
				 'url' => makeGalleryUrl('tools/find_orphans.php'),
				 'longtext' => _("Find, remove or readd orphaned elements."));
	$adminOptions[] = array( 'text' => _("find comment spam"),
				 'url' => makeGalleryUrl('tools/despam-comments.php'),
				 'longtext' => _("Find and remove comments that contains spam."));
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

$breadcrumb["bordercolor"] = $gallery->app->default["bordercolor"];

includeLayout('breadcrumb.inc');
includeLayout('ml_pulldown.inc');

if(!empty($adminOptions)) {
	echo "\n" .'<table style="width:80%; margin: 20px;">';
	foreach ($adminOptions as $option) {
		echo "\n<tr>";
		if (isset($option['url'])) {
			$link = '<a class="admin" href="'. $option['url'] .'">'. $option['text'] .'</a>';
		} else {
			$link = popup_link($option['text'], $option['popupFile'], false, true, 500, 500, 'admin');
		}
		echo "\n<td>$link</td>";
		echo "\n<td>". $option['longtext'] ."</td>";
		echo "\n</tr>";
	}
	echo "\n</table>";
}

$validation_file = basename(__FILE__);
includeHtmlWrap("general.footer");
?>
<!-- gallery.footer end -->

<?php if (!$GALLERY_EMBEDDED_INSIDE) { ?>
</body>
</html>
<?php } ?>
