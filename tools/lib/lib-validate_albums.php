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
 * $Id: lib-validate_albums.php 14282 2006-08-12 00:46:01Z jenst $
 */

function findInvalidAlbums() {
	global $gallery;
	global $results;

	$albumsDir = opendir($gallery->app->albumDir);

	$allowedInvalidAlbums = array('.', '..', '.users', 'CVS', 'SVN', '_vti_cnf', 'lost+found', 'captcha_tmp');

	while (($file = readdir($albumsDir)) !== false) {
		$albumPath = $gallery->app->albumDir . '/' . $file;
		if (fs_is_dir($albumPath)) {
			if(in_array($file, $allowedInvalidAlbums)) {
				continue;
			}
			else {
				// Load the album - if it fails, it's invalid
				$album = new Album();
				if (!$album->load($file)) {
					$results['invalid_album'][] = $file;
					continue;
				}

				// Determine if the album is missing any essential files
				findMissingFiles($album, $albumPath);
			}
		}
	}
	closedir($albumsDir);

	sort($results['file_missing']);
	sort($results['invalid_album']);
}

function findMissingFiles($album, $albumPath) {
	global $gallery;
	global $results;

	// Try to ensure we'll have enough time to process this album
	@set_time_limit($gallery->app->time_limit);

	/*
	* Try and load each photo and examine its physical file
	* if the file doesn't exist, we flag it.
	*/
	for ($i = 1; $i <= sizeof($album->photos); $i++) {
		$photo = $album->getPhoto($i);

		// Albums will be tested on their own
		if ($photo->isAlbum()) {
			continue;
		}

		// Get the file path and verify
		$photoPath = $photo->getPhotoPath($albumPath, true);
		if (!fs_file_exists($photoPath)) {
			// album/filename.ext
			$results['file_missing'][] = substr($photoPath, strlen($gallery->app->albumDir) + 1);
		}
	}
}

/**
 * Removes recursively (!) a directory and its content.
 *
 * @param string $path
 * @return boolean
 */
function removeInvalidAlbum($path) {
	if(!isXSSclean($path)) {
		return false;
	}
	
	if(!fs_is_dir($path)) {
		return true;
	}

	$removePath = opendir($path);

	while (($file = readdir($removePath)) !== false) {
		if ($file == '.' || $file == '..') {
			continue;
		}

		if (fs_is_dir($path . '/' . $file)) {
			removeInvalidAlbum($path . '/' . $file);
		} else {
			unlink($path . '/' . $file);
		}
	}
	closedir($removePath);
	rmdir($path);

	return true;
}
?>