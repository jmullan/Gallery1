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

		$this->fields["title"] = _("Untitled");
		$this->fields["description"] = "";
		$this->fields["summary"]="";
		$this->fields["nextname"] = "aaa";
		$this->fields["bgcolor"] = "";
		$this->fields["textcolor"] = "";
		$this->fields["linkcolor"] = "";
		$this->fields["background"] = "";
		$this->fields["font"] = $gallery->app->default["font"];
		$this->fields["border"] = $gallery->app->default["border"];
		$this->fields["bordercolor"] = $gallery->app->default["bordercolor"];
		$this->fields["returnto"] = $gallery->app->default["returnto"];
		$this->fields["thumb_size"] = $gallery->app->default["thumb_size"];
		$this->fields["resize_size"] = $gallery->app->default["resize_size"];
		$this->fields["resize_file_size"] = $gallery->app->default["resize_file_size"];
		$this->fields['max_size'] = $gallery->app->default['max_size'];
		$this->fields['max_file_size'] = $gallery->app->default['max_file_size'];
		$this->fields["rows"] = $gallery->app->default["rows"];
		$this->fields["cols"] = $gallery->app->default["cols"];
		$this->fields["fit_to_window"] = $gallery->app->default["fit_to_window"];
		$this->fields["use_fullOnly"] = $gallery->app->default["use_fullOnly"];
		$this->fields["print_photos"] = $gallery->app->default["print_photos"];
		if (isset($gallery->app->use_exif)) {
			$this->fields["use_exif"] = "yes";
		} else {
			$this->fields["use_exif"] = "no";
		}

		$everybody = $gallery->userDB->getEverybody();
		$this->setPerm("canRead", $everybody->getUid(), 1);
		$this->setPerm("canViewFullImages", $everybody->getUid(), 1);
		$this->setPerm("canViewComments", $everybody->getUid(), 1);
		$this->setPerm("canAddComments", $everybody->getUid(), 1);
		$this->fields["parentAlbumName"] = 0;
		$this->fields["clicks"] = 0;
		$this->fields["clicks_date"] = time();
		$this->fields["display_clicks"] = $gallery->app->default["display_clicks"];
		$this->fields["serial_number"] = 0;
		$this->fields["extra_fields"] =
		    split(",", trim($gallery->app->default["extra_fields"]));
		foreach ($this->fields["extra_fields"] as $key => $value) {
			$value=trim($value);
			if ($value == "") {
				unset($this->fields["extra_fields"][$key]);
			} else {
				$this->fields["extra_fields"][$key]=$value;
			}

		}
		$this->fields["cached_photo_count"] = 0;
		$this->fields["photos_separate"] = FALSE;
		$this->transient->photosloaded = TRUE; 
		
		$this->fields["item_owner_display"] = $gallery->app->default["item_owner_display"];
		$this->fields["item_owner_modify"] = $gallery->app->default["item_owner_modify"];
		$this->fields["item_owner_delete"] = $gallery->app->default["item_owner_delete"];
		$this->fields["add_to_beginning"] = $gallery->app->default["add_to_beginning"];
		$this->fields["last_quality"] = $gallery->app->jpegImageQuality;


               // VOTING Variables
               $this->fields["poll_type"]=$gallery->app->default["poll_type"]; // none, rank or critique
               $this->fields["poll_scale"]=$gallery->app->default["poll_scale"]; // num of choices to offer voter
               $this->fields["votes"]=array(); // holds all the votes by UID or session ID
               $this->fields["poll_nv_pairs"]= $gallery->app->default["poll_nv_pairs"];
                       // allows admin to explicitly set display value and
                       // points for all voting options.  EG "Excellent" -> 4
                       // points; "Good" -> 3 points etc etc
               $this->fields["poll_hint"]=$gallery->app->default["poll_hint"];
                       // This is displayed above the voting options
                       // for each image.
               $this->fields["poll_show_results"]=$gallery->app->default["poll_show_results"];
                       // The results graph and breakdown will be displayed
                       // if this is yes.  Note that this should eventually
                       // be part of permissions
               $this->fields["poll_num_results"]=$gallery->app->default["poll_num_results"]; 
	       		// number of lines of graph to show on the album page
	       $this->fields["voter_class"]=$gallery->app->default["voter_class"];
                        // Nobody, Everybody, Logged in
	       // end of VOTING variables

	       $this->fields["slideshow_type"]=$gallery->app->default["slideshow_type"];
	       $this->fields["slideshow_length"]=$gallery->app->default["slideshow_length"];
	       $this->fields["slideshow_recursive"]=$gallery->app->default["slideshow_recursive"];
	       $this->fields["slideshow_loop"]=$gallery->app->default["slideshow_loop"];
	       $this->fields["album_frame"]=$gallery->app->default["album_frame"];
	       $this->fields["thumb_frame"]=$gallery->app->default["thumb_frame"];
	       $this->fields["image_frame"]=$gallery->app->default["image_frame"];
	       $this->fields["showDimensions"] = $gallery->app->default["showDimensions"];
	       $this->fields["email_me"] = array();

	       // Seed new albums with the appropriate version.
	       $this->version = $gallery->album_version;
       	}

	function isRoot() {
		if ($this->fields["parentAlbumName"]) return 0;
		else return 1;
	}

	function itemLastCommentDate($i) {
	       	$photo = $this->getPhoto($i);
			if ($photo->isAlbum()) {
		       	$album = $this->getNestedAlbum($i);
		       	return $album->lastCommentDate();
	       	} else {
		       	return $photo->lastCommentDate();
	       	}
	}
	function lastCommentDate() {
		global $gallery;
		if (!$gallery->user->canViewComments($this)) {
			return -1;
		}
		if ($gallery->app->comments_indication != "albums" && 
				$gallery->app->comments_indication != "both") {
			return -1;
		}
	       	$count = $this->numPhotos(1);
		$mostRecent = -1;
	       	for ($i = 1; $i <= $count; $i++) {
			$subMostRecent=$this->itemLastCommentDate($i);
		       	if ($subMostRecent > $mostRecent) {
			       	$mostRecent = $subMostRecent;
			       	if ($gallery->app->comments_indication_verbose == "no") {
				       	break;
			       	}

			}
	       	}
		return $mostRecent;
	}

	function &getNestedAlbum($index) {
		
		$albumName = $this->getAlbumName($index);
		$album = new Album();
		$album->load($albumName);
		return $album;	
	}

	function &getParentAlbum($loadphotos=TRUE) {
		if ($this->fields['parentAlbumName']) {
			$parentAlbum = new Album();
			$parentAlbum->load($this->fields['parentAlbumName'], $loadphotos);
			return $parentAlbum;
		}
		return null;
	}

	function getRootAlbumName() {

		$parentAlbum =& $this->getParentAlbum(FALSE);
		if (isset($parentAlbum)) {
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
			print _("Album up to date.") ." <br>";
			return 0;
		}

		print _("Upgrading album properties...");
		my_flush();

		$changed = 0;
		$this->fields["last_quality"] = $gallery->app->jpegImageQuality;
		$check = array("thumb_size", 
				"resize_size", 
				"resize_file_size",
			        'max_size',
			        'max_file_size',
				"rows", 
				"cols",
				"fit_to_window", 
				"use_fullOnly", 
				"print_photos",
				"display_clicks",
				"item_owner_display",
				"item_owner_modify",
				"item_owner_delete", 
				"add_to_beginning",
				"poll_type",
				"poll_scale",
				"poll_nv_pairs",
				"poll_hint",
				"poll_show_results",
				"poll_num_results",
				"voter_class",
				"slideshow_type",
				"slideshow_length",
				"slideshow_recursive",
				"slideshow_loop",
				"album_frame",
				"thumb_frame",
				"image_frame",
				"showDimensions",
				"background",
				);
		foreach ($check as $field) {
			if (!isset($this->fields[$field]) && isset($gallery->app->default[$field])) {
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
		if ($this->version < 16) {
		    if (empty($this->fields['votes'])) {
		    		$this->fields['votes']=array();
			$changed = 1;
		    }
		}
		if ($this->version < 17) {
			foreach ($this->fields['votes'] as $key => $value) {
				unset($this->fields['votes'][$key]);
				$this->fields['votes']["item.$key"]=$value;
				$changed = 1;
			}
		}
		if ($this->version < 20) {
			/* upgrade photo print services to new format */
			switch ($this->fields['print_photos']) {
			case 'fotokasten':
			case 'photoaccess':
				$this->fields['print_photos'] = array($this->fields['print_photos'] => array('checked' => true));
				break;
			case 'shutterfly':
				$this->fields['print_photos'] = array('shutterfly' => array('checked' => true, 'donation' => 'yes'));
				break;
			case 'shutterfly without donation':
				$this->fields['print_photos'] = array('shutterfly' => array('checked' => true, 'donation' => 'no'));
				break;
			default:
				$this->fields['print_photos'] = array();
				break;
			}
			$changed = true;
		}
		if ($this->version < 23) {
			if ($this->fields['public_comments'] == 'yes') {
			       	$everybody = $gallery->userDB->getEverybody();
			       	$this->setPerm("canViewComments", $everybody->getUid(), 1);
			       	$this->setPerm("canAddComments", $everybody->getUid(), 1);

			} else {
			       	$nobody = $gallery->userDB->getNobody();
			       	$this->setPerm("canViewComments", $nobody->getUid(), 1);
			       	$this->setPerm("canAddComments", $nobody->getUid(), 1);
			}
		}
		if ($this->version < 24) {
			$this->fields['email_me'] = array();
		}

		/* Convert all uids to the new style */
		if ($this->version < 25) {
		    // Owner
		    $this->fields["owner"] = $gallery->userDB->convertUidToNewFormat($this->fields["owner"]);

		    // Permissions
		    $newPerms = array();
		    foreach ($this->fields["perms"] as $perm => $uids) {
			foreach ($uids as $uid => $value) {
			    $newUid = $gallery->userDB->convertUidToNewFormat($uid);
			    $newPerms[$perm][$newUid] = 1;
			}
		    }
		    $this->fields["perms"] = $newPerms;
		}
		
		/* 
	         * Added for album revision 26:
		 * Changes "." to "-" in gallery names
		 *  Since we're not sure how the .'s are appearing in gallery names
		 *  this is worth running on any DB upgrade, for now
	         */
		if (strpos($this->fields["name"], ".") !== false) {
			$oldName = $this->fields["name"];
			$newName = strtr($this->fields["name"], ".", "-");

			global $albumDB;
			$albumDB->renameAlbum($oldName, $newName);
			$albumDB->save();
			printf(_("Renaming album from %s to %s..."), $oldName, $newName);

			// AlbumDB will set this value .. but it will be set in a different
			// instance of this album, so we have to do it here also so that
			// when *this* instance gets saved the value is right
			$this->fields["name"] = $newName;
			$changed = 1;
		}

		/* Rebuild highlight */
		if ($this->version < 27) {
			$index = $this->getHighlight();
			if (isset($index)) {
				$this->setHighlight($index);
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

		print _("done").".<br>";

		/* 
		* Check all items 
		*/
		$count = $this->numPhotos(1);
		for ($i = 1; $i <= $count; $i++) {
			set_time_limit(30);
			print sprintf(_("Upgrading item %d of %d . . . "), $i, $count);
			my_flush();

			$photo = &$this->getPhoto($i);
			if ($photo->integrityCheck($this->getAlbumDir())) {
				$changed = 1;
				$this->updateSerial = 1;
			}

			print _("done").".<br>";
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
			$func = "sortByUpload";
		} else if (!strcmp($sort,"itemCapture")) {
			$func = "sortByItemCapture";
		} else if (!strcmp($sort, "filename")) {
			$func = "sortByFilename";
		} else if (!strcmp($sort, "click")) {
			$func = "sortByClick";
		} else if (!strcmp($sort, "caption")) {
			$func = "sortByCaption";
		}  else if (!strcmp($sort, "comment")) {
			$func = "sortByComment";
		}
		usort($this->photos, array('Album', $func));
		if ($order) {
			$this->photos = array_reverse($this->photos);
		}
	}

	/*
	 *  Globalize the sort functions from sortPhotos()
	 */
	
	function sortByUpload($a, $b) {
		$objA = (object)$a; 
		$objB = (object)$b;
		$timeA = $objA->getUploadDate();
		$timeB = $objB->getUploadDate();
		if ($timeA == $timeB) {
			return 0;
		}
		
		if ($timeA < $timeB) {
			return -1;
		} else {
			return 1;
		}
	}
	
	function sortByItemCapture($a, $b) {
		$objA = (object)$a;
		$objB = (object)$b;
		$arrayTimeA = $objA->getItemCaptureDate();
		$arrayTimeB = $objB->getItemCaptureDate();
		$timeA = "${arrayTimeA['year']}${arrayTimeA['mon']}${arrayTimeA['mday']}${arrayTimeA['hours']}${arrayTimeA['minutes']}${arrayTimeA['seconds']}";
		$timeB = "${arrayTimeB['year']}${arrayTimeB['mon']}${arrayTimeB['mday']}${arrayTimeB['hours']}${arrayTimeB['minutes']}${arrayTimeB['seconds']}";
		//print "$timeA $timeB<br>";
	
		if ($timeA == $timeB) {
			return 0;
		}
		
		if ($timeA < $timeB) {
			return -1; 
		} else {
			return 1;
		}
	}
	
	function sortByFileName($a, $b) {
		$objA = (object)$a;
		$objB = (object)$b;
		if ($objA->isAlbum()) {
			$filenameA = $objA->getAlbumName();
		} else {
			$filenameA = $objA->image->name;
		}
		
		if ($objB->isAlbum()) {
			$filenameB = $objB->getAlbumName();
		} else {
			$filenameB = $objB->image->name;
		}
	
		//print $filenameA $filenameB;
		return (strnatcasecmp($filenameA, $filenameB));
	}
	
	function sortByClick($a, $b) {
		$objA = (object)$a;
		$objB = (object)$b;
		$aClick = $objA->getItemClicks();
		$bClick = $objB->getItemClicks();
		if ($aClick == $bClick) {
			return 0;
		}
		
		if ($aClick < $bClick) {
			return -1; 
		} else {
			return 1;
		}
	}
	
	function sortByCaption($a, $b) {
		// sort album alphabetically by caption
		$objA = (object)$a;
		$objB = (object)$b;
		$captionA = $objA->getCaption();	
		$captionB = $objB->getCaption();
		return (strnatcasecmp($captionA, $captionB));
	}
	
	function sortByComment($a, $b) {
		// sort by number of comments
		$objA = (object)$a;
		$objB = (object)$b;
		$numCommentsA = $objA->numComments();
		$numCommentsB = $objB->numComments();
		if ($numCommentsA == $numCommentsB) return 0;
		if ($numCommentsA < $numCommentsB) {
			return -1; 
		} else {
			return 1;
		}
	}

	function getThumbDimensions($index, $size=0) {
		if (empty($index)) {
			return array(0, 0);
		}	

		$photo = $this->getPhoto($index);
		$album = $this;
		while ($photo->isAlbum() && $album->numPhotos(1)) {
			$album = $album->getNestedAlbum($index);
			$index = $album->getHighlight();
			if (!isset($index)) {
				return array(0, 0);
			}
			$photo = $album->getPhoto($index);
		}
		return $photo->getThumbDimensions($size);
	}

	function getHighlightDimensions($size=0) {
		$index = $this->getHighlight();
		if (!isset($index)) {
			return array(0, 0);
		}
		$photo = $this->getPhoto($index);
		return $photo->getHighlightDimensions($size);
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

	function getHighlightSize() {
		global $gallery;
		$parentAlbum =& $this->getParentAlbum(FALSE);
		if (isset($parentAlbum)) {
			$size = $parentAlbum->fields["thumb_size"];
		} else {
			$size = $gallery->app->highlight_size;
		}
		return $size;
	}

	function setHighlight($index) {
		$this->updateSerial = 1;

		for ($i = 1; $i <= $this->numPhotos(1); $i++) {
			$photo = &$this->getPhoto($i);
			$photo->setHighlight($this->getAlbumDir(), $i == $index, $this);
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

		foreach ($tmp as $k => $v) {
			$this->$k = $v;
		}
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

	/*  The parameter $msg should be an array ready to pass to sprintf.  
	    This is so we can translate into appropriate languages for each 
	    recipient.  You will note that we don't currently translate these 
	    messages.
	 */
	function save($msg=array(), $resetModDate=1) {
		global $gallery;
		$dir = $this->getAlbumDir();
		$success = FALSE;

		if ($resetModDate) {
			$this->fields["last_mod_time"] = time();
		}

		if (!fs_file_exists($dir)) {
			fs_mkdir($dir, 0775);
		}

		if (!empty($this->updateSerial)) {
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
			$success = (safe_serialize($this->photos, "$dir/photos.dat"));
			if ($success) {
				$this->fields["photos_separate"] = TRUE;
				unset ($this->photos);
			} else {
			    $success = FALSE;
			}
		} else {
			$success = TRUE;
		}

		/* Don't save transient data */
		$transient_save = $this->transient;
		unset($this->transient);

		if ($success) {
		    $success = (safe_serialize($this, "$dir/album.dat"));

		    /* Restore transient data after saving */
		    $this->transient = $transient_save;
		    $this->photos = $transient_photos;

		    /* Create the new album serial file */
		    if (!empty($this->updateSerial)) {
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
		}
		if ($success && $msg) { // send email
			global $HTTP_SERVER_VARS;
			if (!is_array($msg)) {
				echo gallery_error(_("msg should be an array!"));
				vd($msg);
				return $success;
			}
		       	$to = implode(", ", $this->getEmailMeList('other'));
			$msg_str=call_user_func_array('sprintf', $msg);
		       	if (strlen($to) > 0) {
			       	$text = sprintf("A change has been made to %s by %s (IP %s).  The change is: %s",
					       	makeAlbumUrl($this->fields['name']),
						user_name_string($gallery->user->getUID(),
							$gallery->app->comments_display_name),
						$HTTP_SERVER_VARS['REMOTE_ADDR'],
					       	$msg_str);
			       	$text .= "\n\n". "If you no longer wish to receive emails about this image, follow the links above and ensure that \"Email me when other changes are made\" is unchecked (You'll need to login first).";
			       	$subject=sprintf("Changes to %s", $this->fields['name']);
			       	$logmsg=sprintf("Change to %s: %s.",
						       	makeAlbumUrl($this->fields['name']),
						       	$msg_str);
			       	gallery_mail($to, $subject, $text, $logmsg, true);

			} else if (isDebugging()) {
			       	print _("No email sent as no valid email addresses were found");
		       	}
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

	function resizePhoto($index, $target, $filesize=0, $pathToResized="") {
		$this->updateSerial = 1;

		$photo = &$this->getPhoto($index);
		if (!$photo->isMovie()) {
			$photo->resize($this->getAlbumDir(), $target, $filesize, $pathToResized);
		}
	}

	function addPhoto($file, $tag, $originalFilename, $caption, $pathToThumb="", $extraFields=array(), $owner="", $votes=NULL) {
	       	global $gallery;

		$this->updateSerial = 1;

		$dir = $this->getAlbumDir();
		if (!strcmp($gallery->app->default["useOriginalFileNames"], "yes")) {
			$name = $originalFilename;
			// check to see if a file by that name already exists
			// or thumbnail conflict between movie and jpeg
			if (file_exists("$dir/$name.$tag") ||
			    ((isMovie($tag) || $tag=="jpg") && file_exists("$dir/$name.thumb.jpg"))) {
				// append a 3 digit number to the end of the filename if it exists already
				if (!ereg("_[[:digit:]]{3}$", $name)) {
					$name = $name . "_001";
				}
				// increment the 3 digits until we get a unique filename
				while (file_exists("$dir/$name.$tag") ||
				       ((isMovie($tag) || $tag=="jpg") && file_exists("$dir/$name.thumb.jpg"))) {
					$name++;
				}
			}
		} else {
			$name = $this->newPhotoName();
			// do filename checking here, too... users could introduce a duplicate 3 letter
			// name if they switch original file names on and off.
			while (file_exists("$dir/$name.$tag") ||
			       ((isMovie($tag) || $tag=="jpg") && file_exists("$dir/$name.thumb.jpg"))) {
				$name = $this->newPhotoName();
			}
		}
		/* Get the file */
		$newFile = "$dir/$name.$tag";
		fs_copy($file, $newFile);

		/* Do any preprocessing necessary on the image file */
		preprocessImage($dir, "$name.$tag");

		/* Resize original image if necessary */
		processingMsg("&nbsp;&nbsp;&nbsp;". _('Resizing/compressing original image') . "\n");
		if (isImage($tag)) {
		    resize_image($newFile, $newFile, $this->fields['max_size'], $this->fields['max_file_size'], true);
		} else {
		    processingMsg(_('Cannot resize/compress this filetype') . "\n");
		}

		/* Add the photo to the photo list */
		$item = new AlbumItem();
		$err = $item->setPhoto($dir, $name, $tag, $this->fields["thumb_size"], $this, $pathToThumb);
		if ($err) {
			if (fs_file_exists($newFile)) {
				fs_unlink($newFile);
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

		if ($votes) {
			$this->fields["votes"]["item.$name"]=$votes;
		}

	       	/* resize the photo if needed */
	       	if (($this->fields["resize_size"] > 0 ||
				       	$this->fields["resize_file_size"] > 0 ) 
				&& isImage($tag)) {
		       	$index = $this->numPhotos(1);
		       	$photo = $this->getPhoto($index);
		       	list($w, $h) = $photo->image->getRawDimensions();
		       	if ($w > $this->fields["resize_size"] ||
				       	$h > $this->fields["resize_size"] ||
				       	$this->fields["resize_file_size"] > 0) {
			       	processingMsg("- " . sprintf(_("Resizing %s"), $name));
			       	$this->resizePhoto($index, 
						$this->fields["resize_size"],
					       	$this->fields["resize_file_size"]);
		       	}
	       	}

		/* auto-rotate the photo if needed */
	       	if (!strcmp($gallery->app->autorotate, 'yes') && $gallery->app->use_exif) {
		       	$index = $this->numPhotos(1);
		       	$exifData = $this->getExif($index);
		       	if (isset($exifData['Orientation']) && $orientation = trim($exifData['Orientation'])) {
			       	$photo = $this->getPhoto($index);
			       	switch ($orientation) {
				       	case "rotate 90":
					       	$rotate = -90;
				       	break;
				       	case "rotate 180":
					       	$rotate = 180;
				       	break;
				       	case "rotate 270":
					       	$rotate = 90;
				       	break;
				       	case "flip horizontal":
					       	$rotate = 'fh';
				       	break;
				       	case "flip vertical":
					       	$rotate = 'fv';
				       	break;
				       	case 'transpose':
				       	$rotate = 'tr';
				       	break;
				       	case 'transverse':
				       	$rotate = 'tv';
				       	break;
				       	default:
				       	$rotate = 0;
			       	}
			       	if ($rotate) {
				       	$this->rotatePhoto($index, $rotate);
				       	processingMsg("- ". _("Photo auto-rotated/transformed"));
			       	}
		       	}
	       	}
	       	/*move to the beginning if needed */
	       	if ($this->getAddToBeginning() ) {
		       	$this->movePhoto($this->numPhotos(1), 0);
	       	}

		return 0;
	}

	function addNestedAlbum($albumName) {
		$this->updateSerial = 1;
		$item = new AlbumItem();
		$item->setAlbumName($albumName);
		$this->photos[] = $item;
		if ($this->getAddToBeginning() ) {
			$this->movePhoto($this->numPhotos(1), 0);
		}
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
		if ($recursive && $photo[0]->isAlbum()) {
			$albumName = $photo[0]->getAlbumName();
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
				// make sure not to try on a movie or subalbum
                		if (!$newHighlight->isMovie() && !$newHighlight->isAlbum()) {
					$this->setHighlight(1);
				}
			}
		}
	}

	function newPhotoName() {
		return $this->fields["nextname"]++;
	}

	function getThumbnailTag($index, $size=0, $attrs="") {
		if ($index === null) {
			return "";
		}
		$photo = $this->getPhoto($index);
		if ($photo->isAlbum()) {
			$myAlbum = $this->getNestedAlbum($index);
			return $myAlbum->getHighlightAsThumbnailTag($size, $attrs);
		} else {
			return $photo->getThumbnailTag($this->getAlbumDirURL("thumb"), $size, $attrs);
		}
	}
	function getThumbnailTagById($id, $size=0, $attrs="") {
		return $this->getThumbnailTag($this->getPhotoIndex($id), $size, $attrs); 
	}

	function getHighlightedItem() {
		$index = $this->getHighlight();
		if (!isset($index)) {
			return array(null, null);
		}
		$photo = $this->getPhoto($index);
		$album = $this;
		while ($photo->isAlbum() && $album->numPhotos(1)) {
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
			return "<span class=title>". _("No highlight") ."!</span>";
		}
	}

	function getHighlightTag($size=0, $attrs="",$alttext="") {
		$index = $this->getHighlight();
		if (isset($index)) {
			$photo = $this->getPhoto($index);
			return $photo->getHighlightTag($this->getAlbumDirURL("highlight"), $size, $attrs, $alttext);
		} else {
			return "<span class=title>". _("No highlight") ."!</span>";
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
		return $photo->getPhotoId();
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

		$albumPath = "/".urlencode ($this->fields['name']);

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

	function numVisibleItems($user) {
		$numPhotos = $numAlbums = 0;
		$canWrite = $user->canWriteToAlbum($this);
		$numItems = $this->numPhotos(1);
		for ($i = 1; $i <= $numItems; $i++) {
			$photo = $this->getPhoto($i);
			if ($canWrite || !$photo->isHidden()) {
				if ($photo->isAlbum()) {
					$album = new Album();
					$album->load($photo->getAlbumName());
					if ($user->canReadAlbum($album)) {
						$numAlbums++;
					}
				} else{
					$numPhotos++;
				}
			}
		}
		return array($numPhotos, $numAlbums);
	}

	function getIds($show_hidden=0) {
		foreach ($this->photos as $photo) {
			if ((!$photo->isHidden() || $show_hidden) && !$photo->getAlbumName()) {
				$ids[] = $photo->getPhotoId();
			}
		}
		return $ids;
	}

	function &getPhoto($index) {
		if ($index >= 1 && $index <= sizeof($this->photos)) { 
			return $this->photos[$index-1];
		} else {
			echo gallery_error(sprintf(_("Requested index [%d] out of bounds [%d]"),$index,sizeof($this->photos)));
		}
	}

	function getPhotoIndex($id) {
		for ($i = 1; $i <= $this->numPhotos(1); $i++) {
			$photo = $this->getPhoto($i);
			if (!strcmp($photo->getPhotoId(), $id)) {
				return $i;
			}
		}
		return -1;
	}

	function getAlbumIndex($albumName) {
		for ($i = 1; $i <= $this->numPhotos(1); $i++) {
			if ($albumName === $this->getAlbumName($i)) {
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

       function getRank($index) {
               $photo = $this->getPhoto($index);
               return $photo->getRank();
       }
       function setRank($index, $rank) {
               $photo = &$this->getPhoto($index);
               $photo->setRank($rank);
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

	function addComment($id, $comment, $IPNumber, $name) {
	       	$index=$this->getPhotoIndex($id);
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
		$retval = $photo->rotate($this->getAlbumDir(), $direction, $this->fields["thumb_size"], $this);
		if (!$retval) {
			return $retval;
		}
	}

        function watermarkPhoto($index, $wmName, $wmAlphaName, $wmAlign, $wmAlignX, $wmAlignY) {
                $this->updateSerial = 1;
                $photo = &$this->getPhoto($index);
                $retval = $photo->watermark($this->getAlbumDir(),
                                            $wmName, $wmAlphaName, $wmAlign, $wmAlignX, $wmAlignY);
                if (!$retval) {
                        return $retval;
                }
		$resetModDate = 1;
		$this->save(array(), $resetModDate);
        }

	function watermarkAlbum($wmName, $wmAlphaName, $wmAlign, $wmAlignX, $wmAlignY) {
		$this->updateSerial = 1;
	       	$count = $this->numPhotos(1);
		for ($index = 1; $index <= $count; $index++) {
			$photo = &$this->getPhoto($index);
			$retval = $photo->watermark($this->getAlbumDir(),
						$wmName, $wmAlphaName, $wmAlign, $wmAlignX, $wmAlignY);
		}
	}

	function makeThumbnail($index) {
		$this->updateSerial = 1;
		$photo = &$this->getPhoto($index);
		if (!$photo->isAlbum()) {
			$photo->makeThumbnail($this->getAlbumDir(), $this->fields["thumb_size"], $this);
		} else {
			// Reselect highlight of subalbum..
			$album = $this->getNestedAlbum($index);
			$i = $album->getHighlight();
			if (isset($i)) {
				$album->setHighlight($i);
			}
		}
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
	
	function isAlbum($index) {
		$photo = $this->getPhoto($index);
		return ($photo->getAlbumName() !== NULL) ? true : false;
	}
	
	function getAlbumName($index) {
		$photo = $this->getPhoto($index);
		return $photo->getAlbumName();
	}

	function setAlbumName($index, $name) {
		$photo = &$this->getPhoto($index);
		$photo->setAlbumName($name);
	}
	
	function resetClicks() {
		$this->fields["clicks"] = 0;
		$this->fields["clicks_date"] = time();
		$resetModDate=0;
		$this->save(array(), $resetModDate);

	}
	
	function resetAllClicks() {
		$this->resetClicks();
		for ($i=1; $i<=$this->numPhotos(1); $i++) {
			$this->resetItemClicks($i);
		}	
		$resetModDate=0;
		$this->save(array(), $resetModDate);
	}

	function getClicks() {
		// just in case we have no clicks yet...
		if (!isset($this->fields["clicks"])) {
			$this->resetClicks();
		}
		return $this->fields["clicks"];
	}

	function getClicksDate() {
		global $gallery;

                $time = $this->fields["clicks_date"];

                // albums may not have this field.
                if (!$time) {
                        $this->resetClicks();
			$time = $this->fields["clicks_date"];
                }
		return strftime($gallery->app->dateString,$time);

        }

	function incrementClicks() {
		if (strcmp($this->fields["display_clicks"], "yes")) {
			return;
		}

		$this->fields["clicks"]++;
		$resetModDate=0; // don't reset last_mod_date
	        $this->save(array(), $resetModDate);
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
		$this->save(array(), $resetModDate);
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
				 "Error" => sprintf(_("Error %s getting EXIF data"),$status),
				 "junk2" => "");
		}

		if ($needToSave) {
		    $resetModDate=0; //don't reset last_mod_date
		    $this->save(array(), $resetModDate);
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

		return strftime($gallery->app->dateString,$time);
	}

	function setNestedProperties() {
		for ($i=1; $i <= $this->numPhotos(1); $i++) {
			if ($this->getAlbumName($i)) {
				$nestedAlbum = new Album();
				$nestedAlbum->load($this->getAlbumName($i));
				$nestedAlbum->fields["bgcolor"] = $this->fields["bgcolor"];
				$nestedAlbum->fields["textcolor"] = $this->fields["textcolor"];
				$nestedAlbum->fields["linkcolor"] = $this->fields["linkcolor"];
				$nestedAlbum->fields['background'] = $this->fields['background'];				
				$nestedAlbum->fields["font"] = $this->fields["font"];
				$nestedAlbum->fields["bordercolor"] = $this->fields["bordercolor"];
				$nestedAlbum->fields["border"] = $this->fields["border"];
				$nestedAlbum->fields["thumb_size"] = $this->fields["thumb_size"];
				$nestedAlbum->fields["resize_size"] = $this->fields["resize_size"];
				$nestedAlbum->fields["resize_file_size"] = $this->fields["resize_file_size"];
				$nestedAlbum->fields["max_size"] = $this->fields["max_size"];
				$nestedAlbum->fields["max_file_size"] = $this->fields["max_file_size"];				
				$nestedAlbum->fields["returnto"] = $this->fields["returnto"];
				$nestedAlbum->fields["rows"] = $this->fields["rows"];
				$nestedAlbum->fields["cols"] = $this->fields["cols"];
				$nestedAlbum->fields["fit_to_window"] = $this->fields["fit_to_window"];
				$nestedAlbum->fields["use_fullOnly"] = $this->fields["use_fullOnly"];
				$nestedAlbum->fields["print_photos"] = $this->fields["print_photos"];
				$nestedAlbum->fields['slideshow_type']  = $this->fields['slideshow_type'];
				$nestedAlbum->fields['slideshow_recursive'] = $this->fields['slideshow_recursive'];
				$nestedAlbum->fields['slideshow_length'] = $this->fields['slideshow_length'];
				$nestedAlbum->fields['slideshow_loop'] = $this->fields['slideshow_loop'];
				$nestedAlbum->fields['album_frame']    = $this->fields['album_frame'];
				$nestedAlbum->fields['thumb_frame']    = $this->fields['thumb_frame'];
				$nestedAlbum->fields['image_frame']    = $this->fields['image_frame'];
				$nestedAlbum->fields["use_exif"] = $this->fields["use_exif"];
				$nestedAlbum->fields["display_clicks"] = $this->fields["display_clicks"];
				$nestedAlbum->fields["item_owner_display"] = $this->fields["item_owner_display"];
				$nestedAlbum->fields["item_owner_modify"] = $this->fields["item_owner_modify"];
				$nestedAlbum->fields["item_owner_delete"] = $this->fields["item_owner_delete"];
				$nestedAlbum->fields["add_to_beginning"] = $this->fields["add_to_beginning"];
				$nestedAlbum->fields["showDimensions"] = $this->fields["showDimensions"];
				$nestedAlbum->fields["email_me"] = array();
				$nestedAlbum->save();
				$nestedAlbum->setNestedProperties();
			}
		}
	}

	function setNestedExtraFields() {
		for ($i=1; $i <= $this->numPhotos(1); $i++) {
			if ($this->getAlbumName($i)) {
				$nestedAlbum = new Album();
				$nestedAlbum->load($this->getAlbumName($i));
				$nestedAlbum->fields["extra_fields"] = $this->fields["extra_fields"];
				$nestedAlbum->save();
				$nestedAlbum->setNestedExtraFields();
			}
		}
	}
	function setNestedPollProperties() {
		for ($i=1; $i <= $this->numPhotos(1); $i++) {
			if ($this->getAlbumName($i)) {
				$nestedAlbum = new Album();
			       	$nestedAlbum->load($this->getAlbumName($i));
			       	$nestedAlbum->fields["poll_type"]=$this->fields["poll_type"];
			       	$nestedAlbum->fields["poll_scale"]=$this->fields["poll_scale"];
			       	$nestedAlbum->fields["poll_nv_pairs"]=$this->fields["poll_nv_pairs"];
			       	$nestedAlbum->fields["poll_hint"]=$this->fields["poll_hint"];
			       	$nestedAlbum->fields["poll_show_results"]=$this->fields["poll_show_results"];
			       	$nestedAlbum->fields["poll_num_results"]=$this->fields["poll_num_results"];
			       	$nestedAlbum->fields["voter_class"]=$this->fields["voter_class"];

				$nestedAlbum->save();
				$nestedAlbum->setNestedPollProperties();
			}
		}
	}


	function getPerm($permName, $uid) {
		if (isset($this->fields["perms"][$permName])) {
			$perm = $this->fields["perms"][$permName];
		} else {
			$perm=array();
		}
		if (isset($perm[$uid])) {
			return true;
		}

		global $gallery;

		/* If everybody has the perm, then we do too */
		$everybody = $gallery->userDB->getEverybody();
		if (isset($perm[$everybody->getUid()])) {
			return true;
		}

		/*
		** If loggedIn has the perm and we're logged in, then
		** we're ok also.
		**
		** phpBB2's anonymous user are also "logged in", but we have to ignore this.
		*/
		global $GALLERY_EMBEDDED_INSIDE_TYPE;

		$loggedIn = $gallery->userDB->getLoggedIn();
		if (isset($perm[$loggedIn->getUid()]) && strcmp($gallery->user->getUid(), $everybody->getUid()) &&
			! ($GALLERY_EMBEDDED_INSIDE_TYPE == 'phpBB2' && $gallery->user->uid == -1)) {
		        return true;
		}


		/* GEEKLOG MOD
		** We're also going to check to see if its possible that a
		** group membership can authenticate us.
		*/
		
		if ($GALLERY_EMBEDDED_INSIDE_TYPE == 'GeekLog' && is_array($perm)) {
			foreach ($perm as $gid => $pbool) {
				$group = $gallery->userDB->getUserByUid($gid);
				if ($group->isGroup == 1) {
					if (SEC_inGroup(abs($group->uid), $uid)) {
						return true;
					}
				}
			}
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
        function canAddComments($uid) {
                if ($this->isOwner($uid)) {
                        return true;
                }
                return $this->getPerm("canAddComments", $uid);
        }

        function setAddComments($uid, $bool) {
                $this->setPerm("canAddComments", $uid, $bool);
        }

        // -------------
        function canViewComments($uid) {
                if ($this->isOwner($uid)) {
                        return true;
                }
                return $this->getPerm("canViewComments", $uid);
        }

        function setViewComments($uid, $bool) {
                $this->setPerm("canViewComments", $uid, $bool);
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
	function getExtraFields($all=true) {
		if ($all) {
			return $this->fields["extra_fields"];
		} else {
			$return=array();
			foreach($this->fields["extra_fields"] as $value) {
				if ($value != 'AltText') {
					$return[]=$value;
				}
			}
			return $return;
		}
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

		if ( !$user) {
			return "";
		}
		if ( !strcmp($user->getUid(), $nobodyUid) || !strcmp($user->getUid(), $everybodyUid) ) {
			return "";
		}

		$fullName=$user->getFullname();	
		if (empty($fullName)) {
			return ' - '. $user->getUsername();
		} else {
			return ' - '. $user->getFullname() .' ('. $user->getUsername() .')';
		}
        }



       /*
        * Voting type can either be Rank (first, second, third) or critique
        * (1 point, 2 point 3 point).  The difference is with rank there
        * can be only one of each point value.
        */
        function getPollType() {
		if (!isset($this->fields["poll_type"]) || $this->fields["poll_type"] == "")
		{
			return "critique";
		}
		return $this->fields["poll_type"];
	}
	function getVoterClass() {
		if (isset($this->fields["voter_class"])) {
			return $this->fields["voter_class"];
		}
		return "Nobody";
	}

       function getPollScale() {
               if (isset($this->fields["poll_scale"])) {
                       return $this->fields["poll_scale"];
               }
               return 0;
       }
	function getPollNumResults(){
		if (isset($this->fields["poll_num_results"])) {
			return $this->fields["poll_num_results"];
		}
		return 3;
	}
	function getPollShowResults() {
		if (isset($this->fields["poll_show_results"])) {
			if (strcmp($this->fields["poll_show_results"], "no"))
			{
				return true;
			}
		}
		return false;
	}
	function getPollHorizontal() {
		if (isset($this->fields["poll_orientation"])) {
			if (!strcmp($this->fields["poll_orientation"], "horizontal"))
			{
				return true;
			}
		}
		return false;
	}
	function getVoteNVPairs()
	{
		global $gallery;
		$nv_pairs=$this->fields["poll_nv_pairs"];
		if ($nv_pairs == null)
		{
			$nv_pairs == array();
			if ($this->getPollScale() == 1)
			{
				$nv_pairs[0]["name"]="";
				$nv_pairs[0]["value"]="1";
			}
		}
		for ($i = sizeof($nv_pairs); $i<$this->getPollScale() ; $i++)
		{
			if ($this->getPollType() == "rank")
			{
				$nv_pairs[$i]["name"]=sprintf(_("#%d"),($i));
				$nv_pairs[$i]["value"]=$this->getPollScale()-$i+1;
			}
			else
			{
				$nv_pairs[$i]["name"]=$i;
				$nv_pairs[$i]["value"]=$i;
			}
		}
		return $nv_pairs;
	}
	function getPollHint()
	{
		global $gallery;
		$hint=$this->fields["poll_hint"];
		if (is_string($hint))
			return $hint;
		if ($this->getPollScale() == 1 && $this->getPollType() != "rank")
			return "I like this";
		else if ($this->getPollType() == "rank")
			return "Vote for this";
		else
			return "Do you like this? (1=love it)";
	}
	/* Returns true if votes can be moved with images between $this and 
	   $album
	 */
	function pollsCompatible($album) 
	{
		if ($this->fields["poll_type"] != "critique") {
			return false;
		}
		if (isset($album->fields["poll_type"]) && ($album->fields["poll_type"] != "critique")) {
			return false;
		}
		if ($this->fields["poll_scale"] != $album->fields["poll_scale"]) {
			return false;
		}
		for ($i = 0; $i<$this->fields["poll_scale"]; $i++ ) {
			if ($this->fields["poll_nv_pairs"][$i]["value"] !=
				$album->fields["poll_nv_pairs"][$i]["value"] )
			{
				return false;
			}
		}
		return true;

	}

	function getIndexByVotingId($vote_id) {
		global $gallery;
		if (ereg("^item\.(.*)$", $vote_id, $matches)) {
			$index=$this->getPhotoIndex($matches[1]);
		} else if (ereg("^album\.(.*)$", $vote_id, $matches)) {
			$index=$this->getAlbumIndex($matches[1]);
			if ($index > 0) {
				$myAlbum = new Album();
				$myAlbum->load($matches[1]);
				if (!$gallery->user->canReadAlbum($myAlbum)) {
					$index=-1;
				}
			}
		} else {
			$index=-1;
		}
		if ($index > 0 && $this->isHidden($index) &&
				!$gallery->user->isAdmin() && 
				!$gallery->user->isOwnerOfAlbum($this)) {
			$index=-1;

		}
		return $index;
																			 
	}
	function getVotingIdByIndex($index) {
		$albumName = $this->getAlbumName($index);
		if ($albumName) {
			$vote_id = "album.$albumName";
		} else {
			$vote_id = "item.".$this->getPhotoId($index);
		}
		return $vote_id;
	}

	function getSubAlbum($index) {
		$myAlbum = new Album();
		$myAlbum->load($this->getAlbumName($index));
		return $myAlbum;
	}

	//values for type "comment" and "other"
	function getEmailMe($type, $user, $id=null) {
		$uid=$user->getUid();
	       	if ($id) {
		       	$index = $this->getPhotoIndex($id);
		       	$photo = $this->getPhoto($index);
			return $photo->getEmailMe($type, $user);
	       	} else if (isset($this->fields['email_me'][$type]) && 
				isset($this->fields['email_me'][$type][$uid])) {
		       	return true;
	       	} else {
			return false;
		}
	}
	function getEmailMeList($type, $id=null) {
		global $gallery;

		if (isset($this->fields['email_me'][$type])) {
			$uids=array_keys($this->fields['email_me'][$type]);
		} else {
			$uids=array();
		}
		$admin=$gallery->userDB->getUserByUsername('admin');
		if ($admin) {
			if ($type == 'comments' && $gallery->app->adminCommentsEmail == "yes") {
				$uids[]=$admin->getUid();
			} else if ($type == 'other' && $gallery->app->adminOtherChangesEmail == "yes") {
				$uids[]=$admin->getUid();
			}
		}

		if ($id) {
			$index=$this->getPhotoIndex($id);
			$photo=$this->getPhoto($index);
			if ($photo) {
			       	$uids=array_merge($uids,
					$photo->getEmailMeListUid($type));
			}
		}
		$result=array();
	       	foreach ($uids as $uid) {
		       	$user=$gallery->userDB->getUserByUid($uid);
			if ($user->isPseudo()) {
				continue;
			}
		       	if (gallery_validate_email($user->getEmail())) {
			       	$result[]=$user->getEmail();
		       	} else if (isDebugging()) {
				echo gallery_error( sprintf(_("Email problem: skipping %s (UID %s) because email address %s is not valid."), 
							$user->getUsername(), $uid, $user->getEmail()));
		       	}
	       	}
		return array_unique($result);
	}
	
	function setEmailMe($type, $user, $id=null) {
		$uid=$user->getUid();
	       	if ($this->getEmailMe($type, $user, $id)) {
			// already set
			return;
		} else if ($id) {
		       	$index = $this->getPhotoIndex($id);
		       	$photo = &$this->getPhoto($index);
			$photo->setEmailMe($type, $user);
		} else {
		       	$this->fields['email_me'][$type][$uid]=true;
		}
		$this->save();
	}
	function unsetEmailMe($type, $user, $id=null) {
		$uid=$user->getUid();
	       	if (!$this->getEmailMe($type, $user, $id)) {
			// not set
			return;
		} else if ($id) {
		       	$index = $this->getPhotoIndex($id);
		       	$photo = &$this->getPhoto($index);
			$photo->unsetEmailMe($type, $user);
		} else {
		       	unset($this->fields['email_me'][$type][$uid]);
		}
		$this->save();
	}
}
?>
