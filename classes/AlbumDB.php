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
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * $Id$
 */
?>
<?php
class AlbumDB {
	var $albumList;
	var $albumOrder;

	function AlbumDB($loadphotos=TRUE) {
		global $gallery;
		$changed = 0;

		$dir = $gallery->app->albumDir;

		$tmp = getFile("$dir/albumdb.dat");
		if (strcmp($tmp, "")) {
			$this->albumOrder = unserialize($tmp);

			// albumdb.dat is corrupt, rebuild it
			if (empty($this->albumOrder)) {
				$this->albumOrder = array();
			}
			$changed = 1;
		} else {
			$this->albumOrder = array();
		}

		$this->albumList = array();
		$this->brokenAlbums = array();
		$this->outOfDateAlbums = array();
		$i = 0;
		while ($i < sizeof($this->albumOrder)) {
			$name = $this->albumOrder[$i];
		       	if (ereg("^\.", $name)) { // how did this get here??
				array_splice($this->albumOrder, $i, 1);
				$changed = 1;
			} else if (fs_is_dir("$dir/$name")) {
				$album = new Album;
				if ($album->load($name,$loadphotos)) {
					array_push($this->albumList, $album);
					if ($album->versionOutOfDate()) {
						array_push($this->outOfDateAlbums, $name);
					}
				} else if ($name != 'CVS') {
					array_push($this->brokenAlbums, $name);
				}
				$i++;
			} else {
				/* Couldn't find the album -- delete it from order */
				array_splice($this->albumOrder, $i, 1);
				$changed = 1;
			}
		}

		if ($fd = fs_opendir($dir)) {
			while ($file = readdir($fd)) {
				if (!ereg("^\.", $file) && 
				    fs_is_dir("$dir/$file") &&
				    strcmp($file, "_vti_cnf") &&
				    !in_array($file, $this->albumOrder)) {
					$album = new Album;
					$album->load($file,$loadphotos);
					array_push($this->albumList, $album);
					array_push($this->albumOrder, $file);
					$changed = 1;
				}
			}
			closedir($fd);
		}

		if ($changed) {
			$this->save();
		}
	}

	function renameAlbum($oldName, $newName) {
		global $gallery;

		$dir = $gallery->app->albumDir;
		$galdir = dirname(dirname(__FILE__));

		if (fs_is_file("$galdir/$newName") || fs_is_dir("$galdir/$newName")) {
			return 0;
		}

		if (fs_is_dir("$dir/$newName")) {
			return 0;
		}

		if (fs_is_dir("$dir/$oldName")) {
			$success = fs_rename("$dir/$oldName", "$dir/$newName");
			if (!$success) {
				return 0;
			}
		}

		$album = new Album;
		$album->load($newName);
		$album->fields['name'] = $newName;
		$album->save();
		
		for ($i = 0; $i < sizeof($this->albumOrder); $i++) {
			if (!strcmp($this->albumOrder[$i], $oldName)) {
				$this->albumOrder[$i] = $newName;
			}
		}

		return 1;
	}

	function newAlbumName($name="album01") {
		global $gallery;

		if (!$name) {
			$name="album01";
		}
		$albumDir = $gallery->app->albumDir;
		while (fs_file_exists("$albumDir/$name")) {
			switch($name) {
				case 'album99':
					$name = 'album100';
					break;

				case 'album999':
					$name = 'album1000';
					break;

				case 'album9999':
					$name = 'album10000';
					break;

				default:
					if (!ereg('[0-9][0-9]$', $name)) {
						$name.="00";
					}
					$name++;
			}
		}
		return $name;
	}

	function numAlbums($user) {
		return sizeof($this->getVisibleAlbums($user));
	}
	
	function numPhotos($user) {
		$numPhotos = 0;
		foreach ($this->albumList as $album) {
			if ($user->canWriteToAlbum($album)) {
				$numPhotos += $album->numPhotos(1);
                        } else if ($user->canReadAlbum($album)) {
                                $numPhotos += $album->numPhotos(0);
                        }
		}

		return $numPhotos;
	}

	function getCachedNumPhotos($user) {
		$numPhotos = 0;
		foreach ($this->albumList as $album) {
			if ($user->canReadAlbum($album)) {
				$numPhotos += $album->fields["cached_photo_count"];
			}
		}
		return $numPhotos;
	}

	function getAlbum($user, $index) {
	    global $gallery;
	    $list = $this->getVisibleAlbums($user);
	    $wantedAlbum = isset($list[$index-1]) ? $list[$index-1] : false;
	    if ($wantedAlbum && (!isset($wantedAlbum->transient) || !$wantedAlbum->transient->photosloaded)) {
		$wantedAlbum->loadPhotos($gallery->app->albumDir . "/" . $wantedAlbum->fields["name"]);
	    }
	    return $wantedAlbum;
	}

	function getAlbumByName($name, $load=TRUE) {
		global $gallery;
		/* Look for an exact match */
		foreach ($this->albumList as $album) {
		        set_time_limit($gallery->app->timeLimit);
			if ($album->fields["name"] == $name) {
				if ((!isset($album->transient) || !$album->transient->photosloaded) && $load) {
					$album->loadPhotos($gallery->app->albumDir . "/$name");
				}
				return $album;
			}
		}

		/* Look for a match that is case insensitive */
		foreach ($this->albumList as $album) {
		        set_time_limit($gallery->app->timeLimit);
			if (!strcasecmp($album->fields["name"], $name)) {
				if (!$album->transient->photosloaded && $load) {
					$album->loadPhotos($gallery->app->albumDir . "/$name");
				}
				return $album;
			}
		}
		
		return 0;
	}

	function moveAlbum($user, $index, $newIndex) {

		// This is tricky.  The old and new indices are only relevant
		// within the list of albums that this user is able to see!  
		// Find the location that the user desires and determine that it's
		// one of three cases:
		//	1. At the beginning of the album
		// 	2. At the end
		// 	3. After another album
		// Beginning and end are easy.  If it's after another album, then
		// figure out that album, find its absolute index and move it to
		// that spot +1
		//

		$visible = $this->getVisibleAlbums($user);
		$album1 = $visible[$index-1];
		$album2 = $visible[$newIndex-1];

		// Locate absolute indices of the target and destination
		for ($i = 0; $i < sizeof($this->albumList); $i++) {
			if ($this->albumList[$i]->fields['name'] == $album1->fields['name']) {
				$absIndex = $i;
			} else if ($this->albumList[$i]->fields['name'] == $album2->fields['name']) {
				$absNewIndex = $i;
			}
		}

		if ($newIndex == 1) {
			// Move to beginning
			$this->moveAlbumAbsolute($absIndex, 0);
		} else if ($newIndex == sizeof($visible)) {
			// Move to end
			$this->moveAlbumAbsolute($absIndex, sizeof($this->albumList)-1);
		} else {
			// Move to relative spot
			$this->moveAlbumAbsolute($absIndex, $absNewIndex);
		}

		return;
	}

	function moveAlbumAbsolute($index, $newIndex) {
		/* Pull album out */
		$name = array_splice($this->albumOrder, $index, 1);

		/* Add it back in */
		array_splice($this->albumOrder, $newIndex, 0, $name);
	}

	function getVisibleAlbums($user) {
		global $gallery;
		$list = array();
		foreach ($this->albumList as $album) {
			if ($user->canReadAlbum($album) && $album->isRoot()) {
				array_push($list, $album);
			}
		}

		return $list;
	}

	function save() {
		global $gallery;
		$success = 0;

		$dir = $gallery->app->albumDir;
		return safe_serialize($this->albumOrder, "$dir/albumdb.dat");
	}

	function numAccessibleAlbums($user) {
		$numAlbums = 0;
		foreach ($this->albumList as $album) {
			if ($user->canReadAlbum($album))
			$numAlbums++;
		}
		return $numAlbums;
	}

	function numAccessibleItems($user) {
		global $gallery;
		$numPhotos = $numAlbums = $numTopAlbums = 0;
		foreach ($this->albumList as $album) {
			if ($user->canReadAlbum($album)) {
				$numAlbums++;
				if ($album->isRoot()) {
					$numTopAlbums++;
				}
				if (empty($gallery->app->slowPhotoCount) || $gallery->app->slowPhotoCount == "no") {
					$numPhotos += $album->fields["cached_photo_count"];
				} else {
					$album->load($album->fields['name']);
					$numPhotos += $album->numPhotos(1,1);
				}
			}
		}
		return array($numPhotos, $numAlbums, $numTopAlbums);
	}

	function getAlbumsByRoot($rootAlbumName, $user=null) {
		$namedAlbum = $this->getAlbumByName($rootAlbumName);
		if ($namedAlbum) {
			$arr = $namedAlbum->getSubAlbums();
			array_push($arr, $namedAlbum);
			return ($arr);
		}
	}
}

?>
