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
 *
 */

if (!isset($gallery->version)) {
		require_once(dirname(__FILE__) . '/init.php');
}

// Security check
if (!$gallery->user->isAdmin()) {
	header('Location: ' . makeAlbumHeaderUrl());
	exit;
}

$adminOptions[] = array(
			'text' => gTranslate('core', "_Statistics"),
			'url' => makeGalleryUrl('stats-wizard.php'),
			'longtext' => gTranslate('core', "View some statistics about your Gallery. Such as most viewed pictures, or best rated photos etc."));

$adminOptions[] = array(
			'text' => gTranslate('core', "Configuration _wizard"),
			'url' => $gallery->app->photoAlbumURL . '/setup/index.php',
			'longtext' => gTranslate('core', "Use the config wizard to reconfigure or tweak your Gallery."));

$adminOptions[] = array(
			'text' => gTranslate('core', "Find _orphans"),
			'url' => makeGalleryUrl('tools/find_orphans.php'),
			'longtext' => gTranslate('core', "Find, remove or re-attach orphaned elements."));

$adminOptions[] = array(
			'text' => gTranslate('core', "Find _comment spam"),
			'url' => makeGalleryUrl('tools/despam-comments.php'),
			'longtext' => gTranslate('core', "Find and remove comments that contain spam."));

$adminOptions[] = array(
			'text' => gTranslate('core', "_Validate albums"),
			'url' => makeGalleryUrl('tools/validate_albums.php'),
			'longtext' => gTranslate('core', "Identify invalid albums, missing files, and other errors that may prevent you from migrating to Gallery 2"));

/*
$adminOptions[] = array(
		'text' => gTranslate('core', "Gallery backup"),
		'url' => makeGalleryUrl('backup_albums.php'),
		'longtext' => gTranslate('core', "Make a backup of your Gallery."));
*/

if (!$GALLERY_EMBEDDED_INSIDE) {
	$adminOptions[]  = array(
			'text' => gTranslate('core', "Manage _users"),
			'popupFile' => 'manage_users.php',
			'longtext' => gTranslate('core', "Manage your users."));
}

if (!$GALLERY_EMBEDDED_INSIDE || $GALLERY_EMBEDDED_INSIDE == 'joomla') {
	$adminOptions[]  = array(
			'text' => gTranslate('core', "Manage user_groups"),
			'popupFile' => 'manage_groups.php',
			'longtext' => gTranslate('core', "Manage your user groups."));
}

$adminOptions[] = array(
			'text' => gTranslate('core', "_Filesystem usage"),
			'url' => makeGalleryUrl('usage.php'),
			'longtext' => gTranslate('core', "See how much space your Gallery consumes. Viewable by usage per user and per album."));

array_sort_by_fields($adminOptions, 'text', 'asc');

$iconElements[] = galleryIconLink(
			makeAlbumUrl(),
			'navigation/return_to.gif',
			gTranslate('core', "Return to _gallery"));

$iconElements[] = languageSelector();
$iconElements[] = LoginLogoutButton();

$adminbox['text']	= '<span class="g-title">'.  gTranslate('core', "Admin options") .'</span>';
$adminbox['commands']	= makeIconMenu($iconElements, 'right');

if (!$GALLERY_EMBEDDED_INSIDE) {
	doctype();
?>
<html>
<head>
<title><?php echo clearGalleryTitle(gTranslate('core', "Admin options")) ?></title>
<?php
	common_header() ;
?>
</head>
<body>
<?php
}

includeTemplate("gallery.header", '', 'classic');

includeLayout('adminbox.inc');
includeLayout('breadcrumb.inc');

includeTemplate("headerbox.footer");

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

includeTemplate('info_donation-block');

includeTemplate('overall.footer');

if (!$GALLERY_EMBEDDED_INSIDE) { ?>
</body>
</html>
<?php } ?>
