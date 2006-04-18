<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2006 Bharat Mediratta
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
 *
 */
?>
<?php

if (!isset($gallery->version)) {
        require_once(dirname(__FILE__) . '/init.php');
}

// Security check
if (!$gallery->user->isAdmin()) {
	header('Location: ' . makeAlbumHeaderUrl());
	exit;
}

$adminOptions[] = array( 'text' => _("_statistics"),
			 'url' => makeGalleryUrl('stats-wizard.php'),
			 'longtext' => _("View some statistics about your Gallery. Such as most viewed pictures, or best rated photos etc."));

$adminOptions[] = array( 'text' => _("configuration _wizard"),
			 'url' => $gallery->app->photoAlbumURL . '/setup/index.php',
			 'longtext' => _("Use the config wizard to reconfigure or tweak your Gallery"));

$adminOptions[] = array( 'text' => _("find _orphans"),
			 'url' => makeGalleryUrl('tools/find_orphans.php'),
			 'longtext' => _("Find, remove or re-attach orphaned elements."));

$adminOptions[] = array( 'text' => _("find _comment spam"),
			 'url' => makeGalleryUrl('tools/despam-comments.php'),
			 'longtext' => _("Find and remove comments that contains spam."));

$adminOptions[] = array( 'text' => _("_validate albums"),
			 'url' => makeGalleryUrl('tools/validate_albums.php'),
			 'longtext' => _("Identify invalid albums, missing files, and other errors that may prevent you from migrating to Gallery 2"));

#$adminOptions[] = array( 'text' => _("Gallery backup"),
#			 'url' => makeGalleryUrl('backup_albums.php'),
#			 'longtext' => _("Make a backup of your Gallery."));

if (!$GALLERY_EMBEDDED_INSIDE) {
    $adminOptions[]  = array('text' => _("manage _users"),
			 'popupFile' => 'manage_users.php',
			 'longtext' => _("Manage your users."));
}

array_sort_by_fields($adminOptions, 'text', 'asc');

if (!$GALLERY_EMBEDDED_INSIDE) {
    doctype();
?>
<html>
<head>
<title><?php echo $gallery->app->galleryTitle; ?>::<?php echo _("Admin options") ?></title>
<?php
	common_header() ;
?>
</head>
<body dir="<?php echo $gallery->direction ?>">
<?php
}

includeHtmlWrap('gallery.header');

$adminbox['text'] ='<span class="head">'. _("Admin options") .'</span>';
$adminbox['commands'] = galleryLink(makeAlbumUrl(), _("return to _gallery"), array(), '', true);
$breadcrumb['text'][] = languageSelector();

includeLayout('adminbox.inc');
includeLayout('breadcrumb.inc');

if(!empty($adminOptions)) {
	echo "\n" .'<table style="width:100%; margin:10px; margin-bottom:50px">';
	foreach ($adminOptions as $option) {

		echo "\n<tr>";
		if (isset($option['url'])) {
		    $link = galleryLink($option['url'],$option['text']);
		} else {
			$link = popup_link($option['text'], $option['popupFile'], false, true, 500, 500, '', '', '', false);
		}
		echo "\n<td class=\"g-adm-options\">$link</td>";
		echo "\n<td class=\"g-adm-options\">". $option['longtext'] ."</td>";
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
