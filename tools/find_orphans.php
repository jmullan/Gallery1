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

if (!isset($gallery->version)) {
        require(dirname(dirname(__FILE__)) . '/init.php');
}

// Security check
if (!$gallery->user->isAdmin()) {
	header("Location: " . makeGalleryHeaderUrl());
	exit;
}
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
<table>
<tr><th>Orpaned Album</th><th>&nbsp;</th><th>Parent Album</th></tr>
<?php
$albumDB = new AlbumDB();
foreach ($albumDB->albumList as $album) {
	
	// Root albums can't be orphans
	if ($album->isRoot()) {
		continue;
	}

	$parent = $album->getParentAlbum();

	if (!isset($parent)) {
		// Orphaned, but the parent album is missing.
		// Move it to root
		echo "<tr><td>" . $album->fields['name'] . "</td><td>=&gt;</td><td>Gallery Root</td></tr>\n";
		$album->fields['parentAlbumName'] = 0;
		while ($album->save() != true);
		continue;
	}

	$ret = 0;
	if (!empty($parent->photos)) {
		foreach ($parent->photos as $photo) {
			if ($photo->isAlbum() && ($photo->getAlbumName() == $album->fields['name'])) {
				// Found a matching name - this is not an orphaned album
				$ret = 1;
				break;
			}
		}
	}

	if ($ret == 0) {
		// "Orphaned Album => Parent Album"
		echo "<tr><td>" . $album->fields['name'] . "</td><td>=&gt;</td><td>" . $parent->fields['name'] . "</td></tr>\n";

		// Attach the album to its parent
		$parent->addNestedAlbum($album->fields['name']);

		// Set a default highlight if appropriate
		if ($parent->numPhotos(1) == 1) {
			$parent->setHighlight(1);
		}

		// If the machine is fast, it can find a new album before it
		// has time to finish saving the last one.
		// Keep trying to save until it works.
		while ($parent->save() != true);
	}
}
?>
</table>
<hr>
<?php includeHtmlWrap("gallery.footer"); ?>
