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
 */
?>
<?php

$adminAlbumCommands = '';

/* User is allowed to delete the album */
if ($gallery->user->canDeleteAlbum($gallery->album)) {
	$adminAlbumCommands .= "\n\t". popup_link(gTranslate('core',"delete album"), "delete_album.php?set_albumName={$tmpAlbumName}");
}

/* User is allowed to change the album */
if ($gallery->user->canWriteToAlbum($gallery->album)) {
	$adminAlbumCommands .= "\n\t". popup_link(gTranslate('core',"move album"), "move_album.php?set_albumName={$tmpAlbumName}&index=$i&reorder=0");
	$adminAlbumCommands .= "\n\t". popup_link(gTranslate('core',"reorder album"), "move_album.php?set_albumName={$tmpAlbumName}&index=$i&reorder=1");
	$adminAlbumCommands .= "\n\t". popup_link(gTranslate('core',"rename album"), "rename_album.php?set_albumName={$tmpAlbumName}&index=$i");
}

/* User ist allowed to change album captions */
/* Should this be into the above group ? */
if ($gallery->user->canChangeTextOfAlbum($gallery->album) && !$gallery->session->offline) {
	$adminAlbumCommands .= "\n\t". '<a href="'. makeGalleryUrl("captionator.php", array("set_albumName" => $tmpAlbumName)) . '">' .
		'['. gTranslate('core',"edit captions") .']</a>';
}

/* User is Admin or Owner */
if ($gallery->user->isAdmin() || $gallery->user->isOwnerOfAlbum($gallery->album)) {
	/* User is allowed to change Album Permissions */
	$adminAlbumCommands .= "\n\t". popup_link(gTranslate('core',"permissions"), "album_permissions.php?set_albumName={$tmpAlbumName}");
	$adminAlbumCommands .= "\n\t". popup_link(gTranslate('core',"properties"), "edit_appearance.php?set_albumName={$tmpAlbumName}", 0, true, 500, 600);

	/* User is allowed to view ALL comments */
	if ($gallery->user->canViewComments($gallery->album)
		&& ($gallery->app->comments_enabled == 'yes')
		&& ($gallery->album->lastCommentDate("no") != -1)) {
		    $adminAlbumCommands .= galleryLink(
		      makeGalleryUrl("view_comments.php", array("set_albumName" => $tmpAlbumName)),
		      gTranslate('core',"view&nbsp;comments"), array(), '', true
		    );
	}

	/* Watermarking support is enabled and user is allowed to watermark images/albums */
	if (!empty($gallery->app->watermarkDir) && $gallery->album->numPhotos(1)) {
		$adminAlbumCommands .= "\n\t". popup_link(gTranslate('core',"watermark&nbsp;album"), "watermark_album.php?set_albumName={$tmpAlbumName}");
	}
}

if(!empty($adminAlbumCommands)) {
    $html = "\n\t<!-- Begin adminAlbumCommands -->";
    $html .= "\n\t<div class=\"g-admin\">";
    $html .= $adminAlbumCommands;
    $html .= "\n\t</div>";
    $html .= "\n\t<!-- End adminAlbumCommands -->";
    $adminAlbumCommands = $html;
}
else {
    $adminAlbumCommands = "<!-- ". gTranslate('core', "No Admin commands available.") . '-->';
}
?>