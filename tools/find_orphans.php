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

$albumDB = new AlbumDB();

function attachOrphans($orphans) {
	global $albumDB;
	foreach ($orphans as $childname => $parentname) {
		if ($parentname == 0) {
			// Parent was deleted - attach it to root
			$child = $albumDB->getAlbumByName($childname);
			$child->fields['parentAlbumName'] = 0;
			while ($child->save() != true);
			continue;
		}
		
		$parent = $albumDB->getAlbumByName($parentname);
		$parent->addNestedAlbum($childname);
	
		// Set a default highlight if appropriate, for the parent
		if ($parent->numPhotos(1) == 1) {
			$parent->setHighlight(1);
		}
	
		// If the machine is fast, it can find a new album before it
		// has time to finish physically saving the last one.
		// Keep trying to save until it works.
		while ($parent->save() != true);
	}
}

function findOrphans() {
	global $albumDB;
	$orphaned = Array();
	foreach ($albumDB->albumList as $album) {
		
		// Root albums can't be orphans
		if ($album->isRoot()) {
			continue;
		}
	
		$parent = $album->getParentAlbum();
	
		if (!isset($parent)) {
			// Orphaned, but the parent album is missing - link it to root
			$orphaned[$album->fields['name']] = 0;
			continue;
		}
	
		// Search for a filename match in the parent album
		if (!empty($parent->photos)) {
			foreach ($parent->photos as $photo) {
				if ($photo->isAlbum() && ($photo->getAlbumName() == $album->fields['name'])) {
					// Found a matching name - this is not an orphaned album
					// continue from outer loop
					continue 2;
				}
			}
		}
	
		// "Orphaned Album => Parent Album"
		$orphaned[$album->fields['name']] = $parent->fields['name'];
	}
	
	// Sort the array by value (parent) so it can be displayed to the user
	asort($orphaned);
	return $orphaned;
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
<?php if (!isset($update)) { ?>
	<table>
	<tr><th>Orphaned Album</th><th>&nbsp;</th><th>Parent Album</th></tr>
<?php
	$orphans = findOrphans();

	foreach ($orphans as $childname => $parentname) {
		echo "\t<tr><td>" . $childname . "</td><td>=&gt;</td><td>" . ($parentname ? $parentname : "Gallery Root") . "</td></tr>\n";
	}
?>
	</table>
<?php echo makeFormIntro("tools/find_orphans.php", array("method" => "GET")); ?>
	<input type="hidden" name="update" value="1">
	<input type="submit" value="Correct Them!">
	</form>	
<?php 
} // !isset(update) 
else { 
	// attachOrphans();
	echo "attachOrphans();<br /><br />";
	echo "<a href='" . makeAlbumUrl() . "'>Return to Gallery</a>";
}
?>
<hr>
<?php includeHtmlWrap("gallery.footer"); ?>
