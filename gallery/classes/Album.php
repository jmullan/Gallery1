<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2003 Bharat Mediratta
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
class Album {
	var $fields;
	var $photos;
	var $dir;
	var $version;
	var $tsilb = "TSILB";

	/* 
	 * This variable contains data that is useful for the lifetime
	 * of the album object but which should not be saved in the
	 * database.  Data like the mirrorUrl which we want to validate
	 * the first time we touch an album.
	 */
	var $transient;

	function Album() {
		global $gallery;

		$this->fields["title"] = "Untitled";
		$this->fields["description"] = "No description";
		$this->fields["summary"]="";
		$this->fields["nextname"] = "aaa";
		$this->fields["bgcolor"] = "";
		$this->fields["textcolor"] = "";
		$this->fields["linkcolor"] = "";
		$this->fields["font"] = $gallery->app->default["font"];
		$this->fields["border"] = $gallery->app->default["border"];
		$this->fields["bordercolor"] = $gallery->app->default["bordercolor"];
		$this->fields["returnto"] = $gallery->app->default["returnto"];
		$this->fields["thumb_size"] = $gallery->app->default["thumb_size"];
		$this->fields["resize_size"] = $gallery->app->default["resize_size"];
		$this->fields["rows"] = $gallery->app->default["rows"];
		$this->fields["cols"] = $gallery->app->default["cols"];
		$this->fields["fit_to_window"] = $gallery->app->default["fit_to_window"];
		$this->fields["use_fullOnly"] = $gallery->app->default["use_fullOnly"];
		$this->fields["print_photos"] = $gallery->app->default["print_photos"];
		if ($gallery->app->use_exif) {
			$this->fields["use_exif"] = "yes";
		} else {
			$this->fields["use_exif"] = "no";
		}

		$everybody = $gallery->userDB->getEverybody();
		$this->setPerm("canRead", $everybody->getUid(), 1);
		$this->setPerm("canViewFullImages", $everybody->getUid(), 1);
		$this->fields["parentAlbumName"] = 0;
		$this->fields["clicks"] = 0;
		$this->fields["clicks_date"] = time();
		$this->fields["display_clicks"] = $gallery->app->default["display_clicks"];
		$this->fields["public_comments"] = $gallery->app->default["public_comments"];
		$this->fields["serial_number"] = 0;
		$this->fields["extra_fields"] =
		    split(",", trim($gallery->app->default["extra_fields"]));
		for ($i = 0; $i < sizeof($this->fields["extra_fields"]); $i++) {
		    $this->fields["extra_fields"][$i] = trim($this->fields["extra_fields"][$i]);
		}
		$this->fields["cached_photo_count"] = 0;
		$this->fields["photos_separate"] = FALSE;
		$this->transient->photosloaded = TRUE; 
		
		$this->fields["item_owner_display"] = $gallery->app->default["item_owner_display"];
		$this->fields["item_owner_modify"] = $gallery->app->default["item_owner_modify"];
		$this->fields["item_owner_delete"] = $gallery->app->default["item_owner_delete"];
		$this->fields["add_to_beginning"] = $gallery->app->default["add_to_beginning"];

		// Seed new albums with the appropriate version.
		$this->version = $gallery->album_version;
	}

	function isRoot() {
		if ($this->fields["parentAlbumName"]) return 0;
		else return 1;
	}

	function getNestedAlbum($index) {
		
		$albumName = $this->isAlbumName($index);
		$album = new Album();
		$album->load($albumName);
		return $album;	
	}

	function getRootAlbumName() {

		if ($this->fields['parentAlbumName']) {
			$parentAlbum = new Album();
			$parentAlbum->load($this->fields['parentAlbumName']);
			$returnValue = $parentAlbum->getRootAlbumName();
		} else {
			$returnValue = $this->fields['name'];
		}
		return $returnValue;
	}
			
	function versionOutOfDate() {
		global $gallery;
		if (strcmp($this->version, $gallery->album_version)) {
			return 1;
		}
		return 0;
	}

	/*
	 * Whenever you change this code, you should bump the $gallery->album_version
	 * appropriately.
	 */	
	function integrityCheck() {
		global $gallery;

		if (!strcmp($this->version, $gallery->album_version)) {
			print "Album up to date.<br>";
			return 0;
		}

		print "Upgrading album properties...";
		my_flush();

		$changed = 0;
		$check = array("thumb_size", 
				"resize_size", 
				"rows", 
				"cols",
				"fit_to_window", 
				"use_fullOnly", 
				"print_photos",
				"display_clicks",
				"public_comments", 
				"item_owner_display",
				"item_owner_modify",
				"item_owner_delete", 
				"add_to_beginning");
		foreach ($check as $field) {
			if (!$this->fields[$field]) {
				$this->fields[$field] = $gallery->app->default[$field];
				$changed = 1;
			}
		}

		/* 
		 * Copy the canRead permissions to viewFullImage if
		 * the album version is older than the feature.
		 */
		if ($this->version < 5) {
		    if (!empty($this->fields['perms']['canRead'])) {
			foreach ($this->fields['perms']['canRead'] as $uid => $p) {
				$this->fields['perms']['canViewFullImages'][$uid] = $p;
			}
			$changed = 1;
		    }
		}
		if ($this->version < 10) {
		    if (empty($this->fields['summary'])) {
		    		$this->fields['summary']='';
			$changed = 1;
		    }
		    if (empty($this->fields['extra_fields']) || !is_array($this->fields['extra_fields'])) {
		    	$this->fields['extra_fields']=array();
			$changed = 1;
		    }
		}


		/* Special case for EXIF :-( */
		if (!$this->fields["use_exif"]) {
			if ($gallery->app->use_exif) {
				$this->fields["use_exif"] = "yes";
			} else {
				$this->fields["use_exif"] = "no";
			}
			$changed = 1;
		}

		/* Special case for serial number */
		if (!$this->fields["serial_number"]) {
			$this->fields["serial_number"] = 0;
			$changed = 1;
		}

		print "done.<br>";

		/* 
		* Check all items 
		*/
		$count = $this->numPhotos(1);
		for ($i = 1; $i <= $count; $i++) {
			set_time_limit(30);
			print "Upgrading item $i of $count...";
			my_flush();

			$photo = &$this->getPhoto($i);
			if ($photo->integrityCheck($this->getAlbumDir())) {
				$changed = 1;
				$this->updateSerial = 1;
			}

			print "done.<br>";
		}

		if (strcmp($this->version, $gallery->album_version)) {
			$this->version = $gallery->album_version;
			$changed = 1;
		}

		if ($changed) {
			$this->updateSerial = 1;
		}
		return $changed;
	}



	function shufflePhotos() {
		$this->updateSerial = 1;

		shuffle($this->photos);
	}

	function sortPhotos($sort,$order) {
		$this->updateSerial = 1;

		// if we are going to use sort, we need to set the historic dates.
		// the get Date functions set any null dates for us, so that's what
		// this for loop does for us...
		for ($i=1; $i<=$this->numPhotos(1); $i++) {
			$this->getItemCaptureDate($i);
			$this->getUploadDate($i);
		}
		$this->save();

		if (!strcmp($sort,"upload")) {
			$func = "\$objA = (object)\$a; \$objB = (object)\$b; ";
			$func .= "\$timeA = \$objA->getUploadDate(); ";
			$func .= "\$timeB = \$objB->getUploadDate(); ";
			$func .= "if (\$timeA == \$timeB) return 0; ";

			if (!$order) {
				$func .= "if (\$timeA < \$timeB) return -1; else return 1;";
			} else {
				$func .= "if (\$timeA > \$timeB) return -1; else return 1;";
			}
		} else if (!strcmp($sort,"itemCapture")) {
			$func = "\$objA = (object)\$a; \$objB = (object)\$b; ";
			$func .= "\$arrayTimeA = \$objA->getItemCaptureDate(); ";
			$func .= "\$arrayTimeB = \$objB->getItemCaptureDate(); ";
			$func .= "\$timeA = \"\${arrayTimeA['year']}\${arrayTimeA['mon']}\${arrayTimeA['mday']}\${arrayTimeA['hours']}\${arrayTimeA['minutes']}\${arrayTimeA['seconds']}\";";
			$func .= "\$timeB = \"\${arrayTimeB['year']}\${arrayTimeB['mon']}\${arrayTimeB['mday']}\${arrayTimeB['hours']}\${arrayTimeB['minutes']}\${arrayTimeB['seconds']}\";";
			//$func .= "print \"\$timeA \$timeB<br>\";";
			$func .= "if (\$timeA == \$timeB) return 0; ";
			if (!$order) {
				$func .= "if (\$timeA < \$timeB) return -1; else return 1;";
			} else {
				$func .= "if (\$timeA > \$timeB) return -1; else return 1;";
			}    
		} else if (!strcmp($sort, "filename")) {
			$func = "\$objA = (object)\$a; \$objB = (object)\$b; ";
			$func .= "if (\$objA->isAlbumName) { ";
			$func .= "	\$filenameA = \$objA->isAlbumName; ";
			$func .= "} else { ";
			$func .= "	\$filenameA = \$objA->image->name; ";
			$func .= "} ";
			$func .= "if (\$objB->isAlbumName) { ";
			$func .= "	\$filenameB = \$objB->isAlbumName; ";
			$func .= "} else { ";
			$func .= "	\$filenameB = \$objB->image->name; ";
			$func .= "} ";
			//$func .= "print \$filenameA \$filenameB; ";
			if (!$order) {
				$func .= "return (strnatcmp(\$filenameA, \$filenameB)); ";
			} else {
				$func .= "return (strnatcmp(\$filenameB, \$filenameA)); ";
			}
		} else if (!strcmp($sort, "click")) {
			// sort album by number of clicks
			$func = "\$objA = (object)\$a; \$objB = (object)\$b; ";
			$func .= "\$aClick = \$objA->getItemClicks(); ";
			$func .= "\$bClick = \$objB->getItemClicks(); ";
			$func .= "if (\$aClick == \$bClick) return 0; ";
			if (!$order) {
				$func .= "if (\$aClick < \$bClick) return -1; else return 1;";
			} else {
				$func .= "if (\$aClick > \$bClick) return -1; else return 1;";
			}
		
		} else if (!strcmp($sort, "caption")) {
			// sort album alphabetically by caption
			$func = "\$objA = (object)\$a; \$objB = (object)\$b; ";
			$func .= "\$captionA = \$objA->getCaption(); ";	
			$func .= "\$captionB = \$objB->getCaption(); ";
			if (!$order) {
				$func .= "return (strnatcmp(\$captionA, \$captionB)); ";
			} else {
				$func .= "return (strnatcmp(\$captionB, \$captionA)); ";
			}
		}  else if (!strcmp($sort, "comment")) {
			// sort by number of comments
			$func = "\$objA = (object)\$a; \$objB = (object)\$b; ";
			$func .= "\$numCommentsA = \$objA->numComments(); ";
			$func .= "\$numCommentsB = \$objB->numComments(); ";
			$func .= "if (\$numCommentsA == \$numCommentsB) return 0; ";
			if (!$order) {
				$func .= "if (\$numCommentsA < \$numCommentsB) return -1; else return 1;";
			} else {
				$func .= "if (\$numCommentsA > \$numCommentsB) return -1; else return 1;";
			}
		}
		
		usort($this->photos, create_function('$a,$b', $func));
	}

	function getThumbDimensions($index, $size=0) {
		if (empty($index)) {
			return array(0, 0);
		}	

		$photo = $this->getPhoto($index);
		$album = $this;
		while ($photo->isAlbumName && $album->numPhotos(1)) {
			$album = $album->getNestedAlbum($index);
			$index = $album->getHighlight();
			if (!isset($index)) {
				return array(0, 0);
			}
			$photo = $album->getPhoto($index);
		}
		return $photo->getThumbDimensions($size);
	}

	function hasHighlight() {
		if ($this->numPhotos(1) == 0) {
			return 0;
		}

		for ($i = 1; $i <= $this->numPhotos(1); $i++) {
			$photo = $this->getPhoto($i);
                        if ($photo->isHighlight()) {
                                return 1;
			}
		}
		return 0;
	}

	function getHighlight() {
		if ($this->numPhotos(1) == 0) {
			return null;
		}

		for ($i = 1; $i <= $this->numPhotos(1); $i++) {
			$photo = $this->getPhoto($i);
			if ($photo->isHighlight()) {
				return $i;
			}
		}
		return 1;
	}

	function setHighlight($index) {
		$this->updateSerial = 1;

		for ($i = 1; $i <= $this->numPhotos(1); $i++) {
			$photo = &$this->getPhoto($i);
			$photo->setHighlight($this->getAlbumDir(), $i == $index);
		}
	}

	function load($name,$loadphotos=TRUE) {
		global $gallery;

		$this->transient->photosloaded = FALSE;
		$dir = $gallery->app->albumDir . "/$name";

		if (!$this->loadFromFile("$dir/album.dat")) {
			/*
			 * v1.2.1 and prior had a bug where high volume albums
			 * would lose album.dat files.  Deal with that by loading
			 * the backup file silently.
			 *
			 * Oh, and Win32 has a bug (?) where you can't
			 * rename a file to album.dat.bak so win32 now
			 * uses album.bak for it's backup file names.
			 */
			if (!$this->loadFromFile("$dir/album.dat.bak") &&
			    !$this->loadFromFile("$dir/album.bak")) {
				/* Uh oh */
				return 0;
			}
		}

		if ($this->fields["photos_separate"] && ($this->fields["cached_photo_count"] > 0)) {
			if ($loadphotos) {
				$this->loadPhotos($dir);
			}
		} else {
			$this->transient->photosloaded = TRUE;
		}
		$this->fields["name"] = $name;
		$this->updateSerial = 0;
		return 1;
	}


	function loadPhotos($dir){
		if (!$this->loadPhotosFromFile("$dir/photos.dat") &&
			!$this->loadPhotosFromFile("$dir/photos.dat.bak") &&
		    !$this->loadPhotosFromFile("$dir/photos.bak")) {
			/* Uh oh */
			return 0;
		}
		$this->transient->photosloaded = TRUE;
		return 1;
	}

	function loadFromFile($filename) {

		$tmp = unserialize(getFile($filename));
		if (strcasecmp(get_class($tmp), "album")) {
			/* Dunno what we unserialized .. but it wasn't an album! */
		        $tmp = unserialize(getFile($filename, true));
			if (strcasecmp(get_class($tmp), "album")) {
				return 0;
			}
		}

		$this = $tmp;
		return 1;
	}

	function loadPhotosFromFile($filename) {
		$tmp = unserialize(getFile($filename));
		if (!is_Array($tmp)){
			$tmp = unserialize(getFile($filename, true));
			if (!is_Array($tmp)){
				return 0;
			}
		}
		if (count($tmp) > 0) {
			if (strcasecmp(get_class($tmp[0]), "albumitem")) {
				/* Dunno what we unserialized .. but it wasn't an album! */
				return 0;
			}
		}

		/*
		 * We used to pad TSILB with \n, but on win32 that gets
		 * converted to \r which causes problems.  So get rid of it
		 * when we load albums back.
		 */
		$this->tsilb = trim($this->tsilb);
		
		$this->photos = $tmp;

		return 1;
	}

	function isLoaded() {
		if ($this->fields["name"]) {
			return 1;
		} else {
			return 0;
		}
	}

	function isResized($index) {
		$photo = $this->getPhoto($index);
		return ($photo->isResized());
	}

	function save($resetModDate=1) {
		global $gallery;
		$dir = $this->getAlbumDir();
		$savephotosuccess = FALSE;

		if ($resetModDate) {
			$this->fields["last_mod_time"] = time();
		}

		if (!fs_file_exists($dir)) {
			fs_mkdir($dir, 0775);
		}

		if ($this->updateSerial) {
			/* Remove the old serial file, if it exists */
			$serial = "$dir/serial." . $this->fields["serial_number"]. ".dat";
			if (fs_file_exists($serial)) {
				fs_unlink($serial);
			}
			$this->fields["serial_number"]++;
		}

		if ($this->transient->photosloaded) {
			$this->fields["cached_photo_count"] = $this->numPhotos(1);
		}

		$transient_photos = $this->photos;

		/* Save photo data separately */
		if ($this->transient->photosloaded) {
			$savephotosuccess = (safe_serialize($this->photos, "$dir/photos.dat"));
			if ($savephotosuccess) {
				$this->fields["photos_separate"] = TRUE;
				unset ($this->photos);
			}
		} else {
			$savephotosuccess = TRUE;
		}

		/* Don't save transient data */
		$transient_save = $this->transient;
		unset($this->transient);

		$success = (safe_serialize($this, "$dir/album.dat") && $savephotosuccess);

		/* Restore transient data after saving */
		$this->transient = $transient_save;
		$this->photos = $transient_photos;

		/* Create the new album serial file */
		if ($this->updateSerial) {
			$serial = "$dir/serial." . $this->fields["serial_number"]. ".dat";
			if ($fd = fs_fopen($serial, "w")) {
				/* This space intentionally left blank */
				fwrite($fd, trim($this->tsilb));
				fclose($fd);
			}

			/* Update the master serial file */
			if ($fd = fs_fopen($gallery->app->albumDir . "/serial.dat", "w")) {
				fwrite($fd, time() . "\n");
				fclose($fd);
			}
			$this->updateSerial = 0;
		}

		return $success;
	}

	function delete() {
		$safe_to_scrub = 0;
		$dir = $this->getAlbumDir();

		/* Delete all pictures */
		while ($this->numPhotos(1)) {
			$this->deletePhoto(0);
		}

		/* Delete data file */
		if (fs_file_exists("$dir/album.dat")) {
			$safe_to_scrub = 1;
			fs_unlink("$dir/album.dat");
		}

		/* 
		 * Clean out everything else in the album dir.  I was
		 * trying to avoid having to do this, but now that we're
		 * no longer forcing the resize/thumbnail type to be a jpg 
		 * it's possible that we're going strand some old JPGs
		 * in the system.
		 *
		 * Don't scrub things unless we've removed an album.dat
		 * file (which lets us know that 'dir' is a valid album
		 * directory.
		 */
		if ($safe_to_scrub) {
			if ($fd = fs_opendir($dir)) {
				while (($file = readdir($fd)) != false) {
					if (!fs_is_dir("$dir/$file")) {
						fs_unlink("$dir/$file");
					}
				}
				closedir($fd);
			}
		}

		/* Delete album dir */
		rmdir($dir);
	}

	function resizePhoto($index, $target, $pathToResized="") {
		$this->updateSerial = 1;

		$photo = &$this->getPhoto($index);
		if (!$photo->isMovie()) {
			$photo->resize($this->getAlbumDir(), $target, $pathToResized);
		}
	}

	function addPhoto($file, $tag, $originalFilename, $caption, $pathToThumb="", $extraFields=array(), $owner="") {
		global $gallery;

		$this->updateSerial = 1;

		$dir = $this->getAlbumDir();
		if (!strcmp($gallery->app->default["useOriginalFileNames"], "yes")) {
			$name = $originalFilename;
			// check to see if a file by that name already exists
			if (file_exists("$dir/$name.$tag")) {
				// append a 3 digit number to the end of the filename if it exists already
				if (!ereg("_[[:digit:]]{3}$", $name)) {
					$name = $name . "_001";
				}
				// increment the 3 digits until we get a unique filename
				while (file_exists("$dir/$name.$tag")) {
					$name++;
				}
			}
		} else {
			$name = $this->newPhotoName();
			// do filename checking here, too... users could introduce a duplicate 3 letter
			// name if they switch original file names on and off.
			while (file_exists("$dir/$name.$tag")) {
				$name = $this->newPhotoName();
			}
		}
		/* Get the file */
		fs_copy($file, "$dir/$name.$tag");

		/* Do any preprocessing necessary on the image file */
		preprocessImage($dir, "$name.$tag");
		
		/* Add the photo to the photo list */
		$item = new AlbumItem();
		$err = $item->setPhoto($dir, $name, $tag, $this->fields["thumb_size"], $pathToThumb);
		if ($err) {
			if (fs_file_exists("$dir/$name.$tag")) {
				fs_unlink("$dir/$name.$tag");
			}
			return $err;
		} else {
			$item->setCaption("$caption");
			$originalItemCaptureDate = getItemCaptureDate($file);
			$now = time();
			$item->setItemCaptureDate($originalItemCaptureDate);
			$item->setUploadDate($now);
			foreach ($extraFields as $field => $value)
			{
				$item->setExtraField($field, $value);
			}
			if (!strcmp($owner, "")) {
				$nobody = $gallery->userDB->getNobody();
				$owner = $nobody->getUid();
			}
			$item->setOwner($owner);
		}
		$this->photos[] = $item;

		/* If this is the only photo, make it the highlight */
		if ($this->numPhotos(1) == 1 && !$item->isMovie()) {
			$this->setHighlight(1);
		}

		return 0;
	}

	function addNestedAlbum($albumName) {
		$this->updateSerial = 1;
		$item = new AlbumItem();
		$item->isAlbumName = $albumName;
		$this->photos[] = $item;
	}

	function hidePhoto($index) {
		$photo = &$this->getPhoto($index);
		$photo->hide();
	}
	
	function unhidePhoto($index) {
		$photo = &$this->getPhoto($index);
		$photo->unhide();
	}

	function isHidden($index) {
		$photo = $this->getPhoto($index);
		return $photo->isHidden();
	}

	function deletePhoto($index, $forceResetHighlight="0", $recursive=1) {
		$this->updateSerial = 1;
		$photo = array_splice($this->photos, $index-1, 1);
		// need to check for nested albums and delete them ...
		if ($recursive && $photo[0]->isAlbumName) {
			$albumName = $photo[0]->isAlbumName;
			$album = new Album();
			$album->load($albumName);
			$album->delete();
		}
                /* are we deleting the highlight? pick a new one */
		$needToRehighlight = 0;
		if ( ($photo[0]->isHighlight()) && ($this->numPhotos(1) > 0) && (!$forceResetHighlight==-1)) {
			$needToRehighlight = 1;
		}
		$photo[0]->delete($this->getAlbumDir());
		if (($needToRehighlight) || ($forceResetHighlight==1)){
			if ($this->numPhotos(1) > 0) {
				$newHighlight = $this->getPhoto(1);
                		if (!$newHighlight->isMovie()) {
					$this->setHighlight(1);
				}
			}
		}
	}

	function newPhotoName() {
		return $this->fields["nextname"]++;
	}

	function getThumbnailTag($index, $size=0, $attrs="") {
		$photo = $this->getPhoto($index);
		if ($photo->isAlbumName) {
			$myAlbum = $this->getNestedAlbum($index);
			return $myAlbum->getHighlightAsThumbnailTag($size, $attrs);
		} else {
			return $photo->getThumbnailTag($this->getAlbumDirURL("thumb"), $size, $attrs);
		}
	}

	function getHighlightedItem() {
		$index = $this->getHighlight();
		if (!isset($index)) {
			return array(null, null);
		}
		$photo = $this->getPhoto($index);
		$album = $this;
		while ($photo->isAlbumName && $album->numPhotos(1)) {
			$album = $album->getNestedAlbum($index);
			$index = $album->getHighlight();
			if (!isset($index)) {
				return array(null, null);
			}
			$photo = $album->getPhoto($index);
		}
		return array($album, $photo);
	}

	function getHighlightAsThumbnailTag($size=0, $attrs="") {
		list ($album, $photo) = $this->getHighlightedItem();
		if ($photo) {
			return $photo->getThumbnailTag($album->getAlbumDirURL("highlight"), $size, $attrs);
		} else {
			return "<span class=title>No highlight!</span>";
		}
	}

	function getHighlightTag($size=0, $attrs="") {
		list ($album, $photo) = $this->getHighlightedItem();
		if ($photo) {
			return $photo->getHighlightTag($album->getAlbumDirURL("highlight"), $size, $attrs);
		} else {
			return "<span class=title>No highlight!</span>";
		}
	}

	function getPhotoTag($index, $full) {
		$photo = $this->getPhoto($index);
		if ($photo->isMovie()) {
			return $photo->getThumbnailTag($this->getAlbumDirURL("thumb"));
		} else {
			return $photo->getPhotoTag($this->getAlbumDirURL("full"), $full);
		}
	}

	function getPhotoPath($index, $full=0) {
		$photo = $this->getPhoto($index);
		return $photo->getPhotoPath($this->getAlbumDirURL("full"), $full);
	}

	function getPhotoId($index) {
		$photo = $this->getPhoto($index);
		return $photo->getPhotoId($this->getAlbumDirURL("full"));
	}

	function getAlbumDir() {
		global $gallery;

		return $gallery->app->albumDir . "/{$this->fields['name']}";
	}

	function getAlbumDirURL($type) {
		global $gallery;

		if (!empty($this->transient->mirrorUrl)) {
			return $this->transient->mirrorUrl;
		}

		$albumPath = "/{$this->fields['name']}";

		/* 
		 * Highlights are typically shown for many albums at once,
		 * and it's slow to check each different album just for one
		 * image.  Highlights are also typically pretty small.  So,
		 * if this is for a highlight, don't mirror it.
		 */
		if ($gallery->app->feature["mirror"] &&
		    strcmp($type, "highlight")) {
			foreach(split("[[:space:]]+", $gallery->app->mirrorSites) as $base_url) {
				$base_url .= $albumPath;
				$serial = $base_url . "/serial.{$this->fields['serial_number']}.dat";

				/* Don't use fs_fopen here since we're opening a url */
				if ($fd = @fopen($serial, "r")) {
					$serialContents = fgets($fd, strlen($this->tsilb)+1);
					if (!strcmp($serialContents, $this->tsilb)) {
						$this->transient->mirrorUrl = $base_url;
						return $this->transient->mirrorUrl;
					}
				} 
			}

			/* All mirrors are out of date */
			$this->transient->mirrorUrl = 
				$gallery->app->albumDirURL . $albumPath;
			return $this->transient->mirrorUrl;
		} 

		return $gallery->app->albumDirURL . $albumPath;
	}

	function numHidden() {
		$cnt = 0;
		for ($i = 1; $i <= $this->numPhotos(1); $i++) {
			$photo = $this->getPhoto($i);
			if ($photo->isHidden()) {
				$cnt++;
			}
		}
		return $cnt;
	}

	function numPhotos($show_hidden=0) {
		if ($show_hidden) {
			return sizeof($this->photos);
		} else {
			return sizeof($this->photos) - $this->numHidden();
		}
	}

	function getIds($show_hidden=0) {
		foreach ($this->photos as $photo) {
			if (!$photo->isHidden() || $show_hidden) {
				$ids[] = $photo->getPhotoId($this->getAlbumDir());
			}
		}
		return $ids;
	}

	function &getPhoto($index) {
		if ($index >= 1 && $index <= sizeof($this->photos)) { 
			return $this->photos[$index-1];
		} else {
			print "ERROR: requested index [$index] out of bounds [" . sizeof($this->photos) . "]";
		}
	}

	function getPhotoIndex($id) {
		for ($i = 1; $i <= $this->numPhotos(1); $i++) {
			$photo = $this->getPhoto($i);
			if (!strcmp($photo->getPhotoId($this->getAlbumDir()), $id)) {
				return $i;
			}
		}
		return -1;
	}

	function setPhoto($photo, $index) {
		$this->updateSerial = 1;
		$this->photos[$index-1] = $photo;		
	}

	function getCaption($index) {
		$photo = $this->getPhoto($index);
		return $photo->getCaption();
	}

	function setCaption($index, $caption) {
		$photo = &$this->getPhoto($index);
		$photo->setCaption($caption);
	}

	function getItemOwner($index) {
		$photo = $this->getPhoto($index);
		return $photo->getOwner();
	}

	function setItemOwner($index, $owner) {
		$photo = &$this->getPhoto($index);
		$photo->setOwner($owner);
	}

	function getUploadDate($index) {
		$photo = $this->getPhoto($index);
		$uploadDate = $photo->getUploadDate();
		if (!$uploadDate) { // populating old photos with data
			$this->setUploadDate($index);
			$this->save();
			$uploadDate = $this->getUploadDate($index);
		}
		return $uploadDate;
	}

	function setUploadDate($index, $uploadDate="") {
		$photo = &$this->getPhoto($index);
		$photo->setUploadDate($uploadDate);
	}

	function getItemCaptureDate($index) {
		$photo = $this->getPhoto($index);
		$itemCaptureDate = $photo->getItemCaptureDate();
		if (!$itemCaptureDate) { // populating old photos with data
			$this->setItemCaptureDate($index);
			$this->save();
			$itemCaptureDate = $this->getItemCaptureDate($index);
		}
		return $itemCaptureDate;
	}

	function setItemCaptureDate($index, $itemCaptureDate="") {
		$photo = &$this->getPhoto($index);
		$photo->setItemCaptureDate($itemCaptureDate);
	}
	
	function numComments($index) {
		$photo = $this->getPhoto($index);
		return $photo->numComments();
	}

	function getComment($photoIndex, $commentIndex) {
		$photo = $this->getPhoto($photoIndex);
		return $photo->getComment($commentIndex);
	}

	function addComment($index, $comment, $IPNumber, $name) {
            $photo = &$this->getPhoto($index);
            $photo->addComment($comment, $IPNumber, $name);
	}

	function deleteComment($index, $comment_index) {
		$photo = &$this->getPhoto($index);
		$photo->deleteComment($comment_index);
	}

	function getKeywords($index) {
		$photo = $this->getPhoto($index);
		return $photo->getKeywords();
	}

	function setKeyWords($index, $keywords) {
		$photo = &$this->getPhoto($index);
		$photo->setKeywords($keywords);
        }

	function rotatePhoto($index, $direction) {
		$this->updateSerial = 1;
		$photo = &$this->getPhoto($index);
		$retval = $photo->rotate($this->getAlbumDir(), $direction, $this->fields["thumb_size"]);
		if (!$retval) {
			return $retval;
		}

		/* Are we rotating the highlight?  If so, rebuild the highlight. */
		if ($photo->isHighlight()) {
			$photo->setHighlight($this->getAlbumDir(), 1);
		}
	}

	function makeThumbnail($index) {
		$this->updateSerial = 1;
		$photo = &$this->getPhoto($index);
		$photo->makeThumbnail($this->getAlbumDir(), $this->fields["thumb_size"]);
	}

	function movePhoto($index, $newIndex) {
		/* Pull photo out */
		$photo = array_splice($this->photos, $index-1, 1);
		array_splice($this->photos, $newIndex, 0, $photo);
	}

	function isMovie($id) {
		$index = $this->getPhotoIndex($id);
		$photo = $this->getPhoto($index);
		return $photo->isMovie();
	}

	function isItemOwner($uid, $index)
	{
		global $gallery;
		$nobody = $gallery->userDB->getNobody();
		$nobodyUid = $nobody->getUid();
		$everybody = $gallery->userDB->getEverybody();
		$everybodyUid = $everybody->getUid();

		if ($uid == $nobodyUid || $uid == $everybodyUid) {
			return false;
		}
		return ($uid == $this->getItemOwner($index));
	}
	function isAlbumName($index) {
		$photo = $this->getPhoto($index);
		return $photo->isAlbumName;
	}

	function setIsAlbumName($index, $name) {
		$photo = &$this->getPhoto($index);
		$photo->setIsAlbumName($name);
	}
	
	function resetClicks() {
		$this->fields["clicks"] = 0;
		$this->fields["clicks_date"] = time();
		$resetModDate=0;
		$this->save($resetModDate);

	}
	
	function resetAllClicks() {
		$this->resetClicks();
		for ($i=1; $i<=$this->numPhotos(1); $i++) {
			$this->resetItemClicks($i);
		}	
		$resetModDate=0;
		$this->save($resetModDate);
	}

	function getClicks() {
		// just in case we have no clicks yet...
		if (!isset($this->fields["clicks"])) {
			$this->resetClicks();
		}
		return $this->fields["clicks"];
	}

	function getClicksDate() {
                $time = $this->fields["clicks_date"];

                // albums may not have this field.
                if (!$time) {
                        $this->resetClicks();
			$time = $this->fields["clicks_date"];
                }

                return date("M d, Y", $time);
        }

	function incrementClicks() {
		if (strcmp($this->fields["display_clicks"], "yes")) {
			return;
		}

		$this->fields["clicks"]++;
		$resetModDate=0; // don't reset last_mod_date
	        $this->save($resetModDate);
	}

	function getItemClicks($index) {
		$photo = $this->getPhoto($index);
		return $photo->getItemClicks();
	}

	function incrementItemClicks($index) {
		if (strcmp($this->fields["display_clicks"], "yes")) {
			return;
		}

		$photo = &$this->getPhoto($index);
		$photo->incrementItemClicks();

		$resetModDate=0; //don't reset last_mod_date
		$this->save($resetModDate);
	}

	function resetItemClicks($index) {
		$photo = &$this->getPhoto($index);
		$photo->resetItemClicks();
	}

	function getExif($index, $forceRefresh=0) {
		global $gallery;

		if (empty($gallery->app->use_exif)) {
		    return array();
		}
		
		$dir = $this->getAlbumDir();
		$photo =& $this->getPhoto($index);
		list ($status, $exif, $needToSave) = $photo->getExif($dir, $forceRefresh);

		if ($status != 0) {
		    // An error occurred.
		    return array("junk1" => "",
				 "Error" => "Error $status getting EXIF data",
				 "junk2" => "");
		}

		if ($needToSave) {
		    $resetModDate=0; //don't reset last_mod_date
		    $this->save($resetModDate);
		}
		
		return $exif;
	}

	function getLastModificationDate() {
		global $gallery;
		$dir = $this->getAlbumDir();

		$time = $this->fields["last_mod_time"];

		// Older albums may not have this field.
		if (!$time) {
			$stat = fs_stat("$dir/album.dat");
			$time = $stat[9];
		}

		return date("M d, Y", $time);
	}

	function setNestedProperties() {
		for ($i=1; $i <= $this->numPhotos(1); $i++) {
			if ($this->isAlbumName($i)) {
				$nestedAlbum = new Album();
				$nestedAlbum->load($this->isAlbumName($i));
				$nestedAlbum->fields["bgcolor"] = $this->fields["bgcolor"];
				$nestedAlbum->fields["textcolor"] = $this->fields["textcolor"];
				$nestedAlbum->fields["linkcolor"] = $this->fields["linkcolor"];
				$nestedAlbum->fields["font"] = $this->fields["font"];
				$nestedAlbum->fields["bordercolor"] = $this->fields["bordercolor"];
				$nestedAlbum->fields["border"] = $this->fields["border"];
				$nestedAlbum->fields["background"] = $this->fields["background"];
				$nestedAlbum->fields["thumb_size"] = $this->fields["thumb_size"];
				$nestedAlbum->fields["resize_size"] = $this->fields["resize_size"];
				$nestedAlbum->fields["returnto"] = $this->fields["returnto"];
				$nestedAlbum->fields["rows"] = $this->fields["rows"];
				$nestedAlbum->fields["cols"] = $this->fields["cols"];
				$nestedAlbum->fields["fit_to_window"] = $this->fields["fit_to_window"];
				$nestedAlbum->fields["use_fullOnly"] = $this->fields["use_fullOnly"];
				$nestedAlbum->fields["print_photos"] = $this->fields["print_photos"];
				$nestedAlbum->fields["use_exif"] = $this->fields["use_exif"];
				$nestedAlbum->fields["display_clicks"] = $this->fields["display_clicks"];
				$nestedAlbum->fields["public_comments"] = $this->fields["public_comments"];
				$nestedAlbum->fields["item_owner_display"] = $this->fields["item_owner_display"];
				$nestedAlbum->fields["item_owner_modify"] = $this->fields["item_owner_modify"];
				$nestedAlbum->fields["item_owner_delete"] = $this->fields["item_owner_delete"];
				$nestedAlbum->fields["add_to_beginning"] = $this->fields["add_to_beginning"];
				$nestedAlbum->save();
				$nestedAlbum->setNestedProperties();
			}
		}
	}
	function setNestedExtraFields() {
		for ($i=1; $i <= $this->numPhotos(1); $i++) {
			if ($this->isAlbumName($i)) {
				$nestedAlbum = new Album();
				$nestedAlbum->load($this->isAlbumName($i));
				$nestedAlbum->fields["extra_fields"] = $this->fields["extra_fields"];
				$nestedAlbum->save();
				$nestedAlbum->setNestedExtraFields();
			}
		}
	}

	function getPerm($permName, $uid) {
		$perm = $this->fields["perms"][$permName];
		if ($perm[$uid]) {
			return true;
		}

		global $gallery;

		/* If everybody has the perm, then we do too */
		$everybody = $gallery->userDB->getEverybody();
		if ($perm[$everybody->getUid()]) {
			return true;
		}

		/*
		 * If loggedIn has the perm and we're logged in, then
		 * we're ok also.
		 */
		$loggedIn = $gallery->userDB->getLoggedIn();
		if ($perm[$loggedIn->getUid()] &&
		    strcmp($gallery->user->getUid(), $everybody->getUid())) {
		        return true;
		}

		return false;
	}

	function getPermUids($permName) {
	    global $gallery;
	    
	    $perms = array();
	    if (!empty($this->fields["perms"][$permName])) {
		foreach ($this->fields["perms"][$permName] as $uid => $junk) {
		    $tmpUser = $gallery->userDB->getUserByUid($uid);
		    $perms[$uid] = $tmpUser->getUsername();
		}
	    }

	    return $perms;
	}

	function setPerm($permName, $uid, $bool) {
		if ($bool) {
			$this->fields["perms"][$permName][$uid] = 1;
		} else {
			unset($this->fields["perms"][$permName][$uid]);
		}
	}

	// ------------- 
	function canRead($uid) {
		if ($this->isOwner($uid)) {
			return true;
		}

		// In the default case where there are no permissions for the album,
		// let everybody see it.
		if (!isset($this->fields["perms"])) {
			return 1;			
		}

		return $this->getPerm("canRead", $uid);
	}

	function setRead($uid, $bool) {
		$this->setPerm("canRead", $uid, $bool);
	}

	// ------------- 
	function canWrite($uid) {
		if ($this->isOwner($uid)) {
			return true;
		}
		return $this->getPerm("canWrite", $uid);
	}

	function setWrite($uid, $bool) {
		$this->setPerm("canWrite", $uid, $bool);
	}

	// ------------- 
	function canDelete($uid) {
		if ($this->isOwner($uid)) {
			return true;
		}
		return $this->getPerm("canDelete", $uid);
	}

	function setDelete($uid, $bool) {
		$this->setPerm("canDelete", $uid, $bool);
	}

	// ------------- 
	function canDeleteFrom($uid) {
		if ($this->isOwner($uid)) {
			return true;
		}
		return $this->getPerm("canDeleteFrom", $uid);
	}

	function setDeleteFrom($uid, $bool) {
		$this->setPerm("canDeleteFrom", $uid, $bool);
	}

	// ------------- 
	function canAddTo($uid) {
		if ($this->isOwner($uid)) {
			return true;
		}
		return $this->getPerm("canAddTo", $uid);
	}

	function setAddTo($uid, $bool) {
		$this->setPerm("canAddTo", $uid, $bool);
	}

	// ------------- 
	function canChangeText($uid) {
		if ($this->isOwner($uid)) {
			return true;
		}
		return $this->getPerm("canChangeText", $uid);
	}

	function setChangeText($uid, $bool) {
		$this->setPerm("canChangeText", $uid, $bool);
	}

	// ------------- 
	function canCreateSubAlbum($uid) {
		if ($this->isOwner($uid)) {
			return true;
		}
		return $this->getPerm("canCreateSubAlbum", $uid);
	}

	function setCreateSubAlbum($uid, $bool) {
		$this->setPerm("canCreateSubAlbum", $uid, $bool);
	}

	// ------------- 
	function canViewFullImages($uid) {
		if ($this->isOwner($uid)) {
			return true;
		}
		return $this->getPerm("canViewFullImages", $uid);
	}

	function setViewFullImages($uid, $bool) {
		$this->setPerm("canViewFullImages", $uid, $bool);
	}

	// ------------- 
	function isOwner($uid) {
		return (!strcmp($uid, $this->fields["owner"]));
	}

	function setOwner($uid) {
		$this->fields["owner"] = $uid;
	}

	function getOwner() {
		global $gallery;
		return $gallery->userDB->getUserByUid($this->fields["owner"]);
	}
	function getExtraFields() {
		return $this->fields["extra_fields"];
	}
	function setExtraFields($extra_fields) {
		$this->fields["extra_fields"]=$extra_fields;
	}
	function getExtraField($index, $field)
	{
		$photo = $this->getPhoto($index);
		return $photo->getExtraField($field);
	}
	function setExtraField($index, $field, $value)
	{
		$photo = &$this->getPhoto($index);
		$photo->setExtraField($field, $value);
	}
	function getItemOwnerById($id) 
	{ 
		return $this->getItemOwner($this->getPhotoIndex($id)); 
	}

	function setItemOwnerById($id, $owner) {
		$index=$this->getPhotoIndex($id);
		$this->setItemOwner($index, $owner);
	}
	function getItemOwnerDisplay() {
		if (isset($this->fields["item_owner_display"])) {
			if (strcmp($this->fields["item_owner_display"], "yes"))
			{
				return false;
			}
		}
		return true;
	}
	function getItemOwnerModify() {
		if (isset($this->fields["item_owner_modify"])) {
			if (strcmp($this->fields["item_owner_modify"], "yes"))
			{
				return false;
			}
		}
		return true;
	}
	function getItemOwnerDelete() {
		if (isset($this->fields["item_owner_delete"])) {
			if (strcmp($this->fields["item_owner_delete"], "yes"))
			{
				return false;
			}
		}
		return true;
	}
	function getAddToBeginning() {
		if (isset($this->fields["add_to_beginning"])) {
			if ($this->fields["add_to_beginning"] === "yes")
			{
				return true;
			}
		}
		return false;
	}
	function getCaptionName($index) {
                global $gallery;
		if (!$this->getItemOwnerDisplay()) {
			return "";
		}
		$nobody = $gallery->userDB->getNobody();
		$nobodyUid = $nobody->getUid();
		$everybody = $gallery->userDB->getEverybody();
		$everybodyUid = $everybody->getUid();

                $user=$gallery->userDB->getUserByUid($this->getItemOwner($index));
		if (!$user) {
			return "";
		}
		if (!strcmp($user->getUid(), $nobodyUid) || !strcmp($user->getUid(), $everybodyUid)) {
			return "";
		}
		return " - ".$user->getFullname()." (". $user->getUsername().")";
        }


}

?>
