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
 * $Id: lib-find_orphans.php 14282 2006-08-12 00:46:01Z jenst $
 */

function attachOrphanedAlbums($orphans) {
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

function findOrphanedAlbums() {
	global $albumDB;
	$orphaned = Array();
	foreach ($albumDB->albumList as $album) {

		// Root albums can't be orphans
		if ($album->isRoot()) {
			continue;
		}

		$parent = $album->getParentAlbum();

		if (!isset($parent) || !isset($parent->fields['name'])) {
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

function findOrphanedImages() {
	global $gallery, $albumDB;

	$orphans = Array();

	// "." covers the "." and ".." dir entries
	$ignoreFiles = array('.', 'dat', 'bak', 'lock');

	foreach ($albumDB->albumList as $album) {

		// Get the album name, build the album path, open the directory
		$albumName = $album->fields['name'];
		$albumDir = $gallery->app->albumDir . "/" . $albumName;
		$dirhandle = fs_opendir($albumDir);

		// Storage array
		$albumFiles = array();

		// Retrieve each file until the directory ends
		// Skip the files which have arrays in the ignoreFiles array
		while (false !== ($file = readdir($dirhandle))) {
			$file = pathinfo($file);

			if (empty($file['extension']) || in_array($file['extension'], $ignoreFiles))
			continue;

			$albumFiles[$file['basename']] = 1;
		}

		// Don't bother doing anything if there are no orphans
		if (sizeof($albumFiles)) {

			// Only check subkeys if the album has photos
			if (!empty($album->photos)) {
				foreach ($album->photos as $photo) {
					foreach ($photo as $image) {

						// Theoretically we know which keys hold image locations,
						// however this is to be absolutely safe as we go forward
						// in case any new keys are added

						// Since we're iterating through the entire AlbumItem class looking for files
						// we know we can skip any objects that aren't of the class "Image"
						if (strcasecmp(get_class($image), "Image")) {
							continue;

							// If we encounter a file that's in the AlbumItem, and in the file array
							// purge it, because it's valid
						} elseif (isset($albumFiles[$image->name . "." . $image->type])) {
							unset($albumFiles[$image->name . "." . $image->type]);
						}

						// Resized files have to be handled separately
						if (!empty($image->resizedName) && isset($albumFiles[$image->resizedName . "." . $image->type])) {
							unset($albumFiles[$image->resizedName . "." . $image->type]);
						}
					}
				}
			}

			// Make sure the array isn't empty
			// It is valid to get here even if the album has no _valid_ photos
			if (sizeof($albumFiles)) {
				$orphans[$albumName] = $albumFiles;
			}
		}
	}

	asort($orphans);
	return $orphans;
}

function deleteOrphanedImages($orphans) {
	global $gallery;
	$unwriteableFiles = array();

	foreach ($orphans as $albumName => $imageVal) {
		foreach (array_keys($imageVal) as $fileName) {
			$deleteFile = $gallery->app->albumDir . "/" . $albumName . "/" . $fileName;
			if (! fs_unlink($deleteFile)) {
				$unwriteableFiles[] = $deleteFile;
			}
		}
	}
	return $unwriteableFiles;
}

?>