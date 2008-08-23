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
 */

class Album {
	var $fields;
	var $photos;
	var $dir;
	var $version;
	var $tsilb = "TSILB";

	/*
	 * transient
	 * This variable contains data that is useful for the lifetime
	 * of the album object but which should not be saved in the
	 * database.  Data like the mirrorUrl which we want to validate
	 * the first time we touch an album.
	*/
	var $transient;

	function Album() {
		global $gallery;

		$this->fields["title"] = gTranslate('core', "Untitled");
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
		$this->fields["thumb_ratio"] = $gallery->app->default["thumb_ratio"];
		$this->fields["resize_size"] = $gallery->app->default["resize_size"];
		$this->fields["resize_file_size"] = $gallery->app->default["resize_file_size"];
		$this->fields["max_size"] = $gallery->app->default["max_size"];
		$this->fields["max_file_size"] = $gallery->app->default["max_file_size"];
		$this->fields["rows"] = $gallery->app->default["rows"];
		$this->fields["cols"] = $gallery->app->default["cols"];
		$this->fields["fit_to_window"] = $gallery->app->default["fit_to_window"];
		$this->fields["use_fullOnly"] = $gallery->app->default["use_fullOnly"];
		$this->fields["print_photos"] = isset($gallery->app->default["print_photos"]) ? $gallery->app->default["print_photos"] : '';
		$this->fields["use_exif"] = isset($gallery->app->use_exif) ? 'yes' : 'no';
		$this->fields["guid"] = 0;

		$standardPerm = ($gallery->app->default['defaultPerms']) ? $gallery->app->default['defaultPerms'] : "everybody";

		switch($standardPerm) {
			case 'nobody':
				$UserToPerm = $gallery->userDB->getNobody();
			break;

			case 'loggedin':
				$UserToPerm = $gallery->userDB->getLoggedIn();
			break;

			case 'everybody':
			default:
				$UserToPerm = $gallery->userDB->getEverybody();
			break;
		}

		$this->setPerm("canRead", $UserToPerm->getUid(), 1);
		$this->setPerm("canViewFullImages", $UserToPerm->getUid(), 1);
		$this->setPerm("canViewComments", $UserToPerm->getUid(), 1);
		$this->setPerm("canAddComments", $UserToPerm->getUid(), 1);

		$this->fields["parentAlbumName"] = 0;
		$this->fields["clicks"] = 0;
		$this->fields["clicks_date"] = time();
		$this->fields["display_clicks"] = $gallery->app->default["display_clicks"];
		$this->fields["serial_number"] = 0;
		$this->fields["extra_fields"] = split(",", trim($gallery->app->default["extra_fields"]));
		foreach ($this->fields["extra_fields"] as $key => $value) {
			$value = trim($value);
			if (empty($value)) {
				unset($this->fields["extra_fields"][$key]);
			} else {
				$this->fields["extra_fields"][$key] = $value;
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

		// MICRO-THUMB NAV Variables
		$this->fields["nav_thumbs"] = $gallery->app->default["nav_thumbs"];
		$this->fields["nav_thumbs_style"] = $gallery->app->default["nav_thumbs_style"];
		$this->fields["nav_thumbs_first_last"] = $gallery->app->default["nav_thumbs_first_last"];
		$this->fields["nav_thumbs_prev_shown"] = $gallery->app->default["nav_thumbs_prev_shown"];
		$this->fields["nav_thumbs_next_shown"] = $gallery->app->default["nav_thumbs_next_shown"];
		$this->fields["nav_thumbs_location"] = $gallery->app->default["nav_thumbs_location"];
		$this->fields["nav_thumbs_size"] = $gallery->app->default["nav_thumbs_size"];
		$this->fields["nav_thumbs_current_bonus"] = $gallery->app->default["nav_thumbs_current_bonus"];

		/* VOTING Variables */
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
		$this->fields["ecards"] = $gallery->app->default["ecards"];

		// Seed new albums with the appropriate version.
		$this->version = $gallery->album_version;
	}

	/**
	 * Is the album a root album?
	 *
	 * @return boolean
	 */
	function isRoot() {
		if ($this->fields['parentAlbumName']) {
			return false;
		}
		else {
			return true;
		}
	}

	function itemLastCommentDate($i) {
		global $gallery;

		$photo = $this->getPhoto($i);
		if ($photo->isAlbum()) {
			$album = $this->getNestedAlbum($i);
			return $album->lastCommentDate($gallery->app->comments_indication_verbose);
		}
		else {
			return $photo->lastCommentDate();
		}
	}

	/**
	 * Get the date of the most recent comment.
	 * If verbose not set the date of the first comment is returned.
	 *
	 * @param string $verbose	'yes', or 'no'
	 * @return mixed			Either a date, or -1 of no comment was found.
	 * @todo make $verbose a boolean
	 */
	function lastCommentDate($verbose = 'yes') {
		global $gallery;

		if (!$gallery->user->canViewComments($this)) {
			return -1;
		}

		$count = $this->numPhotos(1);
		$mostRecent = -1;

		for ($i = 1; $i <= $count; $i++) {
			$subMostRecent = $this->itemLastCommentDate($i);
			if ($subMostRecent > $mostRecent) {
				$mostRecent = $subMostRecent;
				if ($verbose == 'no') {
					break;
				}
			}
		}

		return $mostRecent;
	}

	function &getNestedAlbum($index, $loadphotos = true) {
		$albumName	= $this->getAlbumName($index);
		$album		= new Album();
		$album->load($albumName, $loadphotos);

		return $album;
	}

	function &getParentAlbum($loadphotos = TRUE) {
		$ret = NULL;

		if ($this->fields['parentAlbumName']) {
			$parentAlbum = new Album();
			$parentAlbum->load($this->fields['parentAlbumName'], $loadphotos);
			$ret = $parentAlbum;
		}

		return $ret;
	}

	/**
	 * Returns an array of the parent albums.
	 * Each elemt contains a prefix Text, the title and the url.
	 * Array is reverted, so the first Element is the topalbum.
	 * If you set $ignoreReturnto, then really ALL toplevel albums are added.
	 * Based on code by Dariush Molavi
	 * Note: the 30 is a limit to prevent unlimited recursing.
	 *
	 * @param boolean $addChild        if true, then the child album itself is added as last Element.
	 * @param boolean $ignoreReturnto  if true, then really ALL toplevel albums are added.
	 * @param boolean $withGalleryLink if true, and the last album in the array is a rootalbum, a link to Gallery is added.
	 * @param integer $uplevel         How much level of parent albums to you want to show?
	 * @return unknown
	 * @author Dari Molavi
	 * @author Jens Tkotz
	 */
	function getParentAlbums($addChild = false, $ignoreReturnto = false, $withGalleryLink = true, $uplevel = 30) {
		global $gallery;

		$currentAlbum		= $this;
		$parentAlbumsArray	= array();
		$depth			= 0;

		if ($addChild == true) {
			$parentAlbumsArray[] = array(
				'prefixText'	=> gTranslate('core', "Album"),
				'title'		=> $this->fields['title'],
				'url'		=> makeAlbumUrl($this->fields['name']));
		}

		/** If there is a parent album and our current album allows the return link, or we ignore it,
		 * then add it to the parent album to the list.
		 */
		while (($parentAlbum = $currentAlbum->getParentAlbum(FALSE)) &&
		  $depth < $uplevel &&
		  ($currentAlbum->fields['returnto'] != 'no' || $ignoreReturnto == true)) {
			$parentAlbumsArray[] = array(
				'prefixText'	=> gTranslate('core', "Album"),
				'title'		=> $parentAlbum->fields['title'],
				'url'		=> makeAlbumUrl($parentAlbum->fields['name'])
			);
			$depth++;
			$currentAlbum = $parentAlbum;
		}

		/* If the last album is a root album (= has no parent) and a returnto link is wanted,
		 *  add the link to Gallery mainpage
		 */
		if ($withGalleryLink && !isset($parentAlbum) && $currentAlbum->fields['returnto'] != 'no'){
			$parentAlbumsArray[] = array(
				'prefixText'	=> gTranslate('core', "Gallery"),
				'title'		=> clearGalleryTitle(),
				'url'		=> makeGalleryUrl("albums.php"));
		}

		$parentAlbumsArray = array_reverse($parentAlbumsArray, true);

		return $parentAlbumsArray;
	}

	function getRootAlbumName() {
		$parentAlbum =& $this->getParentAlbum(FALSE);

		if (isset($parentAlbum)) {
			$returnValue = $parentAlbum->getRootAlbumName();
		}
		else {
			$returnValue = $this->fields['name'];
		}
		return $returnValue;
	}

	function versionOutOfDate() {
		global $gallery;

		if (strcmp($this->version, $gallery->album_version)) {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Whenever you change this code, you should bump the $gallery->album_version
	 * appropriately.
	 */
	function integrityCheck() {
		global $gallery;

		$gallery->album = $this;

		if (!$this->transient->photosloaded) {
			$this->load($this->fields['name']);
		}

		if (!strcmp($this->version, $gallery->album_version)) {
			print gTranslate('core', "Album up to date.") ." <br>";
			return 0;
		}

		print gTranslate('core', "Upgrading album properties...");
		my_flush();

		$changed = 0;

		$this->fields['last_quality'] = $gallery->app->jpegImageQuality;
		$check = array(
			'thumb_size',
			'thumb_ratio',
			'resize_size',
			'resize_file_size',
			'max_size',
			'max_file_size',
			'rows',
			'cols',
			'fit_to_window',
			'use_fullOnly',
			'print_photos',
			'display_clicks',
			'item_owner_display',
			'item_owner_modify',
			'item_owner_delete',
			'add_to_beginning',
			'poll_type',
			'poll_scale',
			'poll_nv_pairs',
			'poll_hint',
			'poll_show_results',
			'poll_num_results',
			'voter_class',
			'slideshow_type',
			'slideshow_length',
			'slideshow_recursive',
			'slideshow_loop',
			'album_frame',
			'thumb_frame',
			'image_frame',
			'showDimensions',
			'dimensionsAsPopup',
			'background',
			'nav_thumbs',
			'nav_thumbs_style',
			'nav_thumbs_first_last',
			'nav_thumbs_prev_shown',
			'nav_thumbs_next_shown',
			'nav_thumbs_location',
			'nav_thumbs_size',
			'nav_thumbs_current_bonus'
		);

		foreach ($check as $field) {
			if (!isset($this->fields[$field]) && isset($gallery->app->default[$field])) {
				$this->fields[$field] = $gallery->app->default[$field];
				$changed = true;
			}
		}

		/**
		 * Copy the canRead permissions to viewFullImage if
		 * the album version is older than the feature.
		 */
		if ($this->version < 5) {
			if (!empty($this->fields['perms']['canRead'])) {
				foreach ($this->fields['perms']['canRead'] as $uid => $p) {
					$this->fields['perms']['canViewFullImages'][$uid] = $p;
				}
				$changed = true;
			}
		}
		if ($this->version < 10) {
			if (empty($this->fields['summary'])) {
				$this->fields['summary']='';
				$changed = true;
			}
			if (empty($this->fields['extra_fields']) || !is_array($this->fields['extra_fields'])) {
				$this->fields['extra_fields'] = array();
				$changed = true;
			}
		}
		if ($this->version < 16) {
			if (empty($this->fields['votes'])) {
				$this->fields['votes'] = array();
				$changed = true;
			}
		}
		if ($this->version < 17) {
			foreach ($this->fields['votes'] as $key => $value) {
				unset($this->fields['votes'][$key]);
				$this->fields['votes']["item.$key"] = $value;
				$changed = true;
			}
		}

		/* upgrade photo print services to new (1.5.1) format */
		if ($this->version < 20) {
			if ($this->fields['print_photos'] == 'none') {
				$this->fields['print_photos'] = array();
			}
			else {
				$this->fields['print_photos'] = array($this->fields['print_photos']);
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
			$this->fields['owner'] = $gallery->userDB->convertUidToNewFormat($this->fields['owner']);

			// Permissions
			$newPerms = array();
			foreach ($this->fields['perms'] as $perm => $uids) {
				foreach ($uids as $uid => $value) {
					$newUid = $gallery->userDB->convertUidToNewFormat($uid);
					$newPerms[$perm][$newUid] = 1;
				}
			}
			$this->fields['perms'] = $newPerms;
		}

		/**
		 * Added for album revision 26:
		 * Changes "." to "-" in gallery names
		 *  Since we're not sure how the .'s are appearing in gallery names
		 *  this is worth running on any DB upgrade, for now
		 */
		if (strpos($this->fields['name'], ".") !== false) {
			$oldName = $this->fields['name'];
			$newName = strtr($this->fields['name'], ".", "-");

			global $albumDB;
			$albumDB->renameAlbum($oldName, $newName);
			$albumDB->save();
			printf(gTranslate('core', "Renaming album from %s to %s..."), $oldName, $newName);

			// AlbumDB will set this value .. but it will be set in a different
			// instance of this album, so we have to do it here also so that
			// when *this* instance gets saved the value is right
			$this->fields['name'] = $newName;
			$changed = true;
		}

		/* Rebuild highlight */
		if ($this->version < 27) {
			$index = $this->getHighlight();
			if (isset($index)) {
				$this->setHighlight($index);
			}
		}

		if ($this->version < 29) {
			$this->fields['guid'] = genGUID();
			$changed = true;
		}

		if ($this->version < 30) {
			if ($this->fields['border'] == 'off') {
				$this->fields['border'] = 0;
			}
		}

		// Shutterfly now uses affiliate pricing - the donation option is unnecessary.
		if ($this->version < 31) {
			if (isset($this->fields['print_photos']['shutterfly']['donation'])) {
				unset($this->fields['print_photos']['shutterfly']['donation']);
			}

			if (isset($this->fields['print_photos']['shutterfly']) &&
			    !isset($this->fields['print_photos']['shutterfly']['checked'])) {
				unset($this->fields['print_photos']['shutterfly']);
			}
		}

		if ($this->version < 34) {
			if (isset($this->fields['print_photos']['ezprints'])) {
				if (isset($this->fields['print_photos']['ezprints']['checked'])) {
					$this->fields['print_photos']['shutterfly']['checked'] = 'checked';
				}
				unset($this->fields['print_photos']['ezprints']);
				$changed = true;
			}
		}

		// In gallery 1.5.1 the Structure for print services was 'de-suck-ified' (quoted B.M.W.)
		if ($this->version < 35) {
			$tempArray = array();
			if(!empty($this->fields['print_photos'])) {
				foreach ($this->fields['print_photos'] as $service => $trash) {
					$tempArray[] = $service;
				}
				$this->fields['print_photos'] = $tempArray;
			}
			$changed = true;
		}

		// Added field for ecards
		if ($this->version < 36) {
			if(!isset($this->fields['ecards'])) {
				$this->fields['ecards'] = null;
			}
			$changed = true;
		}

		// In gallery 1.5.8 'fotoserve.com' was removed.
		if ($this->version < 38) {
			if(!empty($this->fields['print_photos'])) {
				foreach($this->fields['print_photos'] as $nr => $service) {
					if ($service == 'fotoserve') {
						unset($this->fields['print_photos'][$nr]);
					}
				}
			}

			$changed = true;
		}

		/* Special case for EXIF :-( */
		if (!$this->fields['use_exif']) {
			if (!empty($gallery->app->use_exif)) {
				$this->fields['use_exif'] = "yes";
			}
			else {
				$this->fields['use_exif'] = "no";
			}
			$changed = true;
		}

		/* Special case for serial number */
		if (!$this->fields['serial_number']) {
			$this->fields['serial_number'] = 0;
			$changed = true;
		}

		/* Check all items */
		$count = $this->numPhotos(1);
		if($count > 0) {
			$onePercent = 100/$count;
			for ($i = 1; $i <= $count; $i++) {
				set_time_limit(30);
				$status = sprintf(gTranslate('core', "Upgrading item %d of %d..."), $i, $count);
				echo "\n<script type=\"text/javascript\">updateProgressBar('albumProgessbar', '$status',". ceil($i * $onePercent) .")</script>";
				my_flush();

				$photo = &$this->getPhoto($i);
				if ($photo->integrityCheck($this->getAlbumDir())) {
					$changed = true;
					$this->updateSerial = 1;
				}
			}
		}

		if (strcmp($this->version, $gallery->album_version)) {
			$this->version = $gallery->album_version;
			$changed = true;
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

	/**
	 * third param can be true, false , or NULL
	 * true  -> sort album again, subalbums first
	 * false -> sort album again, photos first
	 * NULL, no resorting.
	 */
	function sortPhotos($sort , $order, $albumsFirst = '') {
		$this->updateSerial = 1;
		global $func;
		global $order;
		global $albumsFirst;

		// if we are going to use sort, we need to set the historic dates.
		// the get Date functions set any null dates for us, so that's what
		// this for loop does for us...
		for ($i = 1; $i <= $this->numPhotos(1); $i++) {
			$this->getItemCaptureDate($i);
			$this->getUploadDate($i);
		}
		$this->save();

		if (!strcmp($sort,"upload")) {
			$func = "sortByUpload";
		} elseif (!strcmp($sort,"itemCapture")) {
			$func = "sortByItemCapture";
		} elseif (!strcmp($sort, "filename")) {
			$func = "sortByFilename";
		} elseif (!strcmp($sort, "click")) {
			$func = "sortByClick";
		} elseif (!strcmp($sort, "caption")) {
			$func = "sortByCaption";
		} elseif (!strcmp($sort, "comment")) {
			$func = "sortByComment";
		}

		if ($albumsFirst != '') {
			//echo "presort by $func with order $order";
			usort($this->photos, array('Album', 'sortByType'));
		}
		else {
			usort($this->photos, array('Album', $func));
		}

	}

	/**
	 *  Globalize the sort functions from sortPhotos()
	 */
	function sortByType($a, $b) {
		global $func;
		global $albumsFirst;

		$objA = (object)$a;
		$objB = (object)$b;

		$aIsAlbum = $objA->isAlbum();
		$bIsAlbum = $objB->isAlbum();

		if ($aIsAlbum == $bIsAlbum) {
			return Album::$func($a, $b);
		} elseif ($aIsAlbum < $bIsAlbum) {
			return 1 * $albumsFirst;
		} else {
			return -1 * $albumsFirst;
		}
	}

	function sortByUpload($a, $b) {
		global $order;

		$objA = (object)$a;
		$objB = (object)$b;
		$timeA = $objA->getUploadDate();
		$timeB = $objB->getUploadDate();
		if ($timeA == $timeB) {
			return 0;
		} elseif ($timeA < $timeB) {
			return -1 * $order;
		} else {
			return 1 * $order;
		}
	}

	function sortByItemCapture($a, $b) {
		global $order;

		$objA = (object)$a;
		$objB = (object)$b;
		$timeA = $objA->getItemCaptureDate();
		$timeB = $objB->getItemCaptureDate();

		if ($timeA == $timeB) {
			return 0;
		} elseif ($timeA < $timeB) {
			return -1 * $order;
		} else {
			return 1 * $order;
		}
	}

	function sortByFileName($a, $b) {
		global $order;

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

		$result = $order * strnatcasecmp($filenameA, $filenameB);

		return $result;
	}

	function sortByClick($a, $b) {
		global $order;

		$objA = (object)$a;
		$objB = (object)$b;
		$aClick = $objA->getItemClicks();
		$bClick = $objB->getItemClicks();
		if ($aClick == $bClick) {
			return 0;
		} elseif ($aClick < $bClick) {
			return -1 * $order;
		} else {
			return 1 * $order;
		}
	}

	function sortByCaption($a, $b) {
		global $albumDB;
		global $order;

		if (empty($albumDB)) {
			$albumDB = new AlbumDB(false);
		}
		// sort album alphabetically by caption
		$objA = (object)$a;
		$objB = (object)$b;
		if ($objA->isAlbum()) {
			$albumA = $albumDB->getAlbumByName($objA->getAlbumName(), false);
			$captionA = $albumA->fields['title'];
		} else {
			$captionA = $objA->getCaption();
		}

		if ($objB->isAlbum()) {
			$albumB = $albumDB->getAlbumByName($objB->getAlbumName(), false);
			$captionB = $albumB->fields['title'];
		} else {
			$captionB = $objB->getCaption();
		}

		$result = $order* strnatcasecmp($captionA, $captionB);

		return $result;
	}

	function sortByComment($a, $b) {
		global $order;

		// sort by number of comments
		$objA = (object)$a;
		$objB = (object)$b;
		$numCommentsA = $objA->numComments();
		$numCommentsB = $objB->numComments();
		if ($numCommentsA == $numCommentsB) {
			return 0;
		} elseif ($numCommentsA < $numCommentsB) {
			return -1 * $order;
		} else {
			return 1 * $order;
		}
	}

	/*** 	End of Sort methods	 	***/

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

	function getHighlightDimensions($size = 0) {
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
		debugMessage(gTranslate('core', "Getting highlight"), __FILE__, __LINE__, 3);

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

		$parentAlbum = $this->getParentAlbum(FALSE);
		if (isset($parentAlbum)) {
			$size = $parentAlbum->fields['thumb_size'];
		} else {
			$size = $gallery->app->highlight_size;
		}
		return $size;
	}

	function setHighlight($index) {
		debugMessage(gTranslate('core', "Setting highlight"), __FILE__, __LINE__, 3);

		$this->updateSerial = 1;
		$numPhotos = $this->numPhotos(1);

		for ($i = 1; $i <= $numPhotos; $i++) {
			$photo = &$this->getPhoto($i);
			$photo->setHighlight($this->getAlbumDir(), $i == $index, $this);
		}
	}

	/**
	 * Returns ratio of highlight,
	 * which is either thumb ratio of parent album, or value from config if root.
	 * @return string   $size
	 * @author Jens Tkotz
	 */
	function getHighlightRatio() {
		global $gallery;

		$parentAlbum = $this->getParentAlbum(FALSE);

		if (isset($parentAlbum)) {
			$ratio = getPropertyDefault('thumb_ratio', $parentAlbum, false);
		}
		else {
			$ratio = getPropertyDefault('highlight_ratio', false, true);
		}

		return $ratio;
	}

	function load($name, $loadphotos = true) {
		if(!isXSSclean($name, 0)) {
			return false;
		}

		global $gallery;

		$this->transient->photosloaded = FALSE;
		$dir = $gallery->app->albumDir . "/$name";

		if(! fs_is_dir($dir)) {
			return false;
		}

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
				return false;
			}
		}

		// if $this->photos is not empty, assume that the photos were already incorrectly stored in album.dat
		// so pretend that we loaded them already to make sure that they get saved to the correct location
		if ($this->fields['photos_separate'] && ($this->fields['cached_photo_count'] > 0) && empty($this->photos)) {
			if ($loadphotos) {
				$this->loadPhotos($dir);
			}
		}
		else {
			$this->transient->photosloaded = TRUE;
		}

		$this->fields['name'] = $name;
		$this->updateSerial = 0;

		return true;
	}


	function loadPhotos($dir){
		if (!$this->loadPhotosFromFile("$dir/photos.dat") &&
		   !$this->loadPhotosFromFile("$dir/photos.dat.bak") &&
		   !$this->loadPhotosFromFile("$dir/photos.bak")) {
			/* Uh oh */
			return 0;
		}

		$this->transient->photosloaded = TRUE;

		return true;
	}

	function loadFromFile($filename) {
		$tmp = unserialize(fs_file_get_contents($filename));
		if (strcasecmp(get_class($tmp), "album")) {
			/* Dunno what we unserialized .. but it wasn't an album! */
			$tmp = unserialize(fs_file_get_contents($filename, true));
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
		$tmp = unserialize(fs_file_get_contents($filename));
		if (!is_Array($tmp)){
			$tmp = unserialize(fs_file_get_contents($filename, true));
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

		/**
		 * We used to pad TSILB with \n, but on win32 that gets
		 * converted to \r which causes problems.  So get rid of it
		 * when we load albums back.
		 */
		$this->tsilb = trim($this->tsilb);

		$this->photos = $tmp;

		return 1;
	}

	function isLoaded() {
		if ($this->fields['name']) {
			return true;
		}
		else {
			return false;
		}
	}

	function isResized($index) {
		$photo = $this->getPhoto($index);
		return $photo->isResized();
	}

	/**
     *  The parameter $msg should be an array ready to pass to sprintf.
     * This is so we can translate into appropriate languages for each
     * recipient.  You will note that we don't currently translate these messages.
     */
	function save($msg = array(), $resetModDate = 1) {
		global $gallery;
		$dir = $this->getAlbumDir();

		if ($resetModDate) {
			$this->fields['last_mod_time'] = time();
		}

		if (!fs_file_exists($dir)) {
			fs_mkdir($dir, 0775);
		}

		if (!empty($this->updateSerial)) {
			/* Remove the old serial file, if it exists */
			$serial = "$dir/serial." . $this->fields['serial_number']. ".dat";
			if (fs_file_exists($serial)) {
				fs_unlink($serial);
			}
			$this->fields['serial_number']++;
		}

		if ($this->transient->photosloaded) {
			$this->fields['cached_photo_count'] = $this->numPhotos(1);
		}

		$transient_photos = $this->photos;

		/* Save photo data separately */
		if ($this->transient->photosloaded) {
			$success = (safe_serialize($this->photos, "$dir/photos.dat"));
			if ($success) {
				$this->fields['photos_separate'] = true;
				unset ($this->photos);
			}
			else {
				$success = false;
			}
		}
		else {
			$success = true;
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
				$serial = "$dir/serial." . $this->fields['serial_number']. ".dat";
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
		// send email
		if ($gallery->app->emailOn == 'yes' && $success && $msg) {
			if (!is_array($msg)) {
				echo gallery_error(gTranslate('core', "msg should be an array!"));
				vd($msg);
				return $success;
			}

			$to = $this->getEmailMeList('other');
			if (!empty($to)) {
				$text = '';
				$msg_str = call_user_func_array('sprintf', $msg);
				$subject = sprintf(gTranslate('core', "Changes to album: %s"), $this->fields['name']);
				$logmsg = sprintf("Change to %s: %s.", makeAlbumHeaderUrl($this->fields['name']), $msg_str);

				$text .= '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">';
				$text .= "\n\n<html>";
				$text .= "\n  <head>";
				$text .= "\n  <title>$subject</title>";
				$text .= "\n  </head>\n<body>\n<p>";
				$text .= sprintf(gTranslate('core', "A change has been made to album: %s by %s (IP %s).  The change is: %s"),
				'<a href="'. makeAlbumHeaderUrl($this->fields['name']) .'">'. $this->fields['name'] .'</a>',
				$gallery->user->printableName($gallery->app->comments_display_name),
				$_SERVER['REMOTE_ADDR'],
				$msg_str);

				$text .= "\n<p>". gTranslate('core', "If you no longer wish to receive emails about this item, follow the links above and ensure that the 'other' checkbox in the 'Email me' box is unchecked. (You'll need to login first.)");
				$text .= "\n</p>\n</body>\n</html>";


				gallery_mail($to, $subject, $text, $logmsg, true, NULL, false, true);

			}
			else if (isDebugging()) {
				print "\n<br>". gTranslate('core', "Operation was done successfully. Emailing is on, but no email was sent as no valid email address was found.");
			}
		}
		/*
		if (!$success) {
		echo gTranslate('core', "Save failed");
		} else {
		echo gTranslate('core', "Save OK");
		}
		*/
		return $success;
	}

	function delete() {
		$safe_to_scrub = 0;
		$dir = $this->getAlbumDir();

		/* Delete all pictures in reverse order to prevent automatic
		re-highlighting of the album after every delete.
		Using this method, re-highlighting will occur, at most, one time.
		*/
		for ($numPhotos = $this->numPhotos(1); $numPhotos > 0; $numPhotos--) {
			$this->deletePhoto($numPhotos);
		}

		/* Delete data file */
		if (fs_file_exists("$dir/album.dat")) {
			$safe_to_scrub = 1;
			fs_unlink("$dir/album.dat");
		}

		/**
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

	/**
	 * Resize one photo of an album. Movies are skipped.
	 * If the (optional) filesize is given, then file is
	 *
	 * @param integer $index			Index of the item.
	 * @param integer $target			New size of the longest site in pixel.
	 * @param integer $filesize			New minimum filesize
	 * @param string  $pathToResized
	 * @param boolean $full
	 */
	function resizePhoto($index, $target, $filesize = 0, $pathToResized = '') {
		$this->updateSerial = 1;

		$photo = &$this->getPhoto($index);
		if (!$photo->isMovie()) {
			$photo->resize($this->getAlbumDir(), $target, $filesize, $pathToResized);
		}
		else {
			echo gTranslate('core', "Skipping Movie");
		}
	}
	/**
	 * Resize and optionally shrink all photos of an album. Movies are skipped.
	 * If wanted this can be done recursive.
	 *
	 * @param integer	$target		New size of the longest site in pixel.
	 * @param integer	$filesize	New minimum filesize.
	 * @param boolean	$recursive	True if you want to resize elements in subalbums, too.
	 */
	function resizeAllPhotos($target, $filesize = 0, $recursive = false) {
		$numItems = $this->numPhotos(1);

		if($numItems == 0) {
			echo gTranslate('core', " -- Skipping") . '<br>';
			return true;
		}

		$onePercent	= 100/$numItems;
		$progressbarID	= $this->fields['name'];

		echo addProgressbar(
			$progressbarID,
			sprintf(
				gTranslate('core', "Resizing items in album: '<i>%s</i>' (%s) with %d items."),
				$this->fields['title'],
				$this->fields['name'],
				$numItems)
		);

		for ($i = 1; $i <= $numItems; $i++) {
			updateProgressBar(
				$progressbarID,
				sprintf(gTranslate('core', "Processing item %d..."), $i),
				ceil($i * $onePercent)
			);

			if ($this->isAlbum($i) && $recursive == true) {
				$nestedAlbum = new Album();
				$nestedAlbum->load($this->getAlbumName($i));
				$np = $nestedAlbum->numPhotos(1);

				$nestedAlbum->resizeAllPhotos($target, $filesize, $recursive);
				$nestedAlbum->save();
			}
			else {
				if(isDebugging()) {
					echo "\n<br>";
					printf(gTranslate('core', "Resizing item %d..."), $i);
				}

				// Here is actually the action
				my_flush();
				$this->resizePhoto($i, $target, $filesize);
			}
		}
		$this->save();
	}

	/**
	 * This adds a photo to an album.
	 * A few steps are done, here are the important
	 *  - Filename processing
	 *  - Resizing the original photo
	 *  - Setting metainfos
	 *  - Autorotate based on EXIF
	 *
	 * @param string	$file			Absolute filename to the physical file to add.
	 * @param string	$tag			Extension of the file (e.g. 'jpg')
	 * @param string	$originalFilename
	 * @param string	$caption
	 * @param string	$pathToThumb		You can set a non generic path to a thumbnail.
	 * 						(e.g. for movies this is done)
	 * @param array		$extraFields
	 * @param string	$owner			UID of the item owner
	 * @param mixed		$votes			Either an array containing the votes, or NULL
	 * @param string	$wmName			Name for an optional watermark image
	 * @param int		$wmAlign		Number from 1-10 for the position of the watermark.
	 * 						See watermark_image() in lib/imageManipulation for details
	 * @param mixed		$wmAlignX		If $wmAlign = 10 then this is used as horizontal alignment
	 * 						Can be a number or a percentage string.
	 * @param mixed		$wmAlignY		Same like $wmAlignX for the vertical alignment
	 * @param int		$wmSelect		0 - Both, sized and Full
	 *						1 - Only sized photos
	 *						2 - Only full photos
	 * @param boolean	$exifRotate		Autorotate
	 * @return array				(true, $statusMsg)
	 */
	function addPhoto($file, $tag, $originalFilename, $caption, $pathToThumb = '', $extraFields = array(), $owner = '', $votes = NULL, $wmName = '', $wmAlign = 0, $wmAlignX = 0, $wmAlignY = 0, $wmSelect = 0, $exifRotate = true) {
		global $gallery;
		global $plainErrorMessage; 		// Set only when using Gallery Remote

		$this->updateSerial = 1;
		$dir = $this->getAlbumDir();

		/* Begin Filename processing */
		echo debugMessage(gTranslate('core', "Doing the naming of the physical file."), __FILE__, __LINE__);

		if ($gallery->app->default['useOriginalFileNames'] == 'yes') {
			$name = $originalFilename;
			// check to see if a file by that name already exists
			// or thumbnail conflict between movie and jpeg
			foreach (acceptableFormatList() as $ext) {
				if (file_exists("$dir/$name.$ext") ||
				    ((isMovie($tag) || $tag=="jpg") && file_exists("$dir/$name.thumb.jpg")))
				{
					// append a 3 digit number to the end of the filename if it exists already
					if (!ereg("_[[:digit:]]{3}$", $name)) {
						$name = $name . "_001";
					}

					// increment the 3 digits until we get a unique filename
					while ((file_exists("$dir/$name.$ext") || file_exists("$dir/$name.$tag")) ||
					       ((isMovie($tag) || $tag=="jpg") && file_exists("$dir/$name.thumb.jpg")))
					{
						$name++;
					}
				}
			}
		}
		else {
			$name = $this->newPhotoName();
			// do filename checking here, too... users could introduce a duplicate 3 letter
			// name if they switch original file names on and off.
			while (file_exists("$dir/$name.$tag") ||
			       ((isMovie($tag) || $tag=="jpg") && file_exists("$dir/$name.thumb.jpg")))
			{
				$name = $this->newPhotoName();
			}
		}
		/* Get the file */
		$newFile = "$dir/$name.$tag";
		fs_copy($file, $newFile);

		echo debugMessage(gTranslate('core', "Image preprocessing"), __FILE__, __LINE__);
		/* Do any preprocessing necessary on the image file */
		preprocessImage($dir, "$name.$tag");

		/* Resize original image if necessary */
		echo debugMessage("&nbsp;&nbsp;&nbsp;". gTranslate('core', "Resizing/compressing original file"), __FILE__, __LINE__,1);

		if (isImage($tag)) {
			resize_image($newFile, $newFile, $this->fields['max_size'], $this->fields['max_file_size'], true, false);
		}
		elseif (isMovie($tag)) {
			processingMsg(gTranslate('core', "File is a movie, no resizing done."));
		}
		else {
			processingMsg(sprintf(gTranslate('core', "Invalid filetype: %s. Skipping."), $tag));
		}

		/* Create an albumitem */
		$item = new AlbumItem();
		$status = $item->setPhoto($dir, $name, $tag, $this->fields['thumb_size'], $this, $pathToThumb);

		if (!$status) {
			if (fs_file_exists($newFile)) {
				fs_unlink($newFile);
			}

			if($plainErrorMessage) {
				$errorMsg = gTranslate('core', "Item not added.");
			}
			else {
				$errorMsg = infobox(array(array(
						'type' => 'error',
						'text' => gTranslate('core', "Item not added.")
				)));
			}

			return array($status, $errorMsg);
		}
		else {
			$item->setCaption("$caption");
			$item->setItemCaptureDate('', $this);
			$item->setUploadDate(time());

			if(!empty($extraFields)) {
				foreach($extraFields as $fieldname => $value) {
					$item->setExtraField($fieldname, $value);
				}
			}

			if (empty($owner)) {
				$nobody	= $gallery->userDB->getNobody();
				$owner	= $nobody->getUid();
			}
			$item->setOwner($owner);
		}

		/* Add the item to the photo list */
		$this->photos[] = $item;
		$index = $this->numPhotos(1);
		$photo = $this->getPhoto($index);

		/* If this is the only photo, make it the highlight */
		if ($index == 1 && !isMovie($tag)) {
			$this->setHighlight(1);
		}

		if ($votes) {
			$this->fields['votes']["item.$name"] = $votes;
		}

		/* Create the resized photo if wanted/needed */
		if (isImage($tag) &&
		    ($this->fields['resize_size'] > 0 || $this->fields['resize_file_size'] > 0))
		{
			list($w, $h) = $photo->image->getRawDimensions();
			if ($w > $this->fields['resize_size'] ||
			    $h > $this->fields['resize_size'] ||
			    $this->fields['resize_file_size'] > 0)
			{
				processingMsg(
					sprintf(gTranslate('core', "Creating resized intermediate Version of %s"), $name));

				$this->resizePhoto(
					$index,
					$this->fields['resize_size'],
					$this->fields['resize_file_size']
				);
			}
		}

		/* auto-rotate the photo if needed */
		echo debugMessage(gTranslate('core', "Check if image needs to be rotated"), __FILE__, __LINE__);
		if ($exifRotate && hasExif($tag) &&
		    !empty($gallery->app->autorotate) && $gallery->app->autorotate == 'yes'  &&
		    (!empty($gallery->app->use_exif) && $gallery->app->use_exif ||
		    (!empty($gallery->app->exiftags) && $gallery->app->exiftags)))
		{
			list($status, $exifData) = getExif($file);

			if (isset($exifData['Orientation'])) {
				$orientation = trim($exifData['Orientation']);
			}
			else if (isset($exifData['Image Orientation'])) {
				$orientation = trim($exifData['Image Orientation']);
			}
			else {
				$orientation = '';
			}

			echo debugMessage(sprintf(gTranslate('core', "Orientation: %s "), $orientation), __FILE__, __LINE__);

			switch ($orientation) {
				case "Right-Hand, Top":		// exiftags
				case "Top, Right-Hand":
				case "rotate 90":		// jhead
					$rotate = '90';
				break;

				case "Right-Hand, Bottom":	// exiftags
				case "Bottom, Right-Hand":
				case "rotate 180":		// jhead
					$rotate = '180';
				break;

				case "Left-Hand, Bottom":	// exiftags
				case "Bottom, Left-Hand":
				case "rotate 270":		// jhead
					$rotate = '90';
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
				break;
			}

			if ($rotate) {
				$this->rotatePhoto($index, $rotate, true);
				processingMsg(gTranslate('core', "Photo auto-rotated/transformed"));
			}
			elseif(isDebugging()) {
				processingMsg(gTranslate('core', "Photo NOT auto-rotated/transformed"));
			}
		}

		/* move to the beginning if needed */
		if ($this->getAddToBeginning() ) {
			$this->movePhoto($this->numPhotos(1), 0);
		}

		if (strlen($wmName) && isImage($tag)) {
			processingMsg("- ". gTranslate('core', "Watermarking image"));
			$photo->watermark($this->getAlbumDir(),
			$wmName, '', $wmAlign, $wmAlignX, $wmAlignY, 0, 0, $wmSelect);
		}

		$this->fields['guid'] = genGUID();
		if($plainErrorMessage) {
			$statusMsg = gTranslate('core', "Item successfully added.");
		}
		else {
			$statusMsg = infobox(array(array(
						'type' => 'success',
						'text' => gTranslate('core', "Item successfully added.")
			)));
		}

		return array(true, $statusMsg);
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

	function isHiddenRecurse($index = 0) {
		if ($index && $this->isHidden($index)) {
			return true;
		}
		elseif ($this->isRoot()) {
			// Root albums can't be hidden
			return false;
		}

		$parent = $this->getParentAlbum();
		$numphotos = $parent->numPhotos(1);
		for ($i = 1; $i <= $numphotos; $i++) {
			if ($parent->isAlbum($i) && ($parent->getAlbumName($i) == $this->fields['name'])) {
				if ($parent->isHidden($i)) {
					// This item is hidden
					return true;
				}
				else {
					// This item is not hidden - check the parent
					return $parent->isHiddenRecurse();
				}
			}
		}
		// This should never happen
		return false;
	}

	/**
	 * Deletes a photo from an album.
	 *
	 * @param int	$index
	 * @param int	$forceResetHighlight
	 * @param int	$recursive
	 * @return boolean	false if item to delete does not exist, true otherwise.
	 */
	function deletePhoto($index, $forceResetHighlight = "0", $recursive = 1) {
		global $gallery;

		$index = intval($index);

		if (! $this->getPhoto($index)) {
			return false;
		}

		// Get rid of the block-random cache file, to prevent out-of-bounds
		// errors from getPhoto()
		$randomBlockCache = $gallery->app->albumDir . "/block-random.dat";
		if (fs_file_exists($randomBlockCache)) {
			fs_unlink($randomBlockCache);
		}

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
			// Prevent hidden items, albums, and movies from
			// automatically becoming the new highlight.
			for ($i = 1; $i <= $this->numPhotos(1); $i++) {
				$newHighlight = $this->getPhoto($i);
				if (!$newHighlight->isMovie() && !$newHighlight->isAlbum() && !$newHighlight->isHidden()) {
					$this->setHighlight($i);
					break;
				}
			}
		}

		return true;
	}

	function newPhotoName() {
		return $this->fields['nextname']++;
	}

	function getPreviewTag($index, $size = 0, $attrs = array()) {
		if ($index === null) {
			return '';
		}

		$photo = $this->getPhoto($index);

		if ($photo->isAlbum()) {
			return '';
			//$myAlbum = $this->getNestedAlbum($index);
			//return $myAlbum->getHighlightAsThumbnailTag($size, $attrs);
		}
		else {
			return $photo->getPreviewTag($this->getAlbumDirURL("preview"), $size, $attrs);
		}
	}

	/**
	 * Returns the HTML code for displaying the thumbnail of a albumitem,
	 * or highlight, if its a subalbum.
	 *
	 * @param integer $index
	 * @param integer $size
	 * @param array $attrs
	 * @return string
	 */
	function getThumbnailTag($index, $size = 0, $attrs = array()) {
		if ($index === null) {
			return '';
		}

		if(empty($attrs['id'])) {
			$attrs['id'] = "thumbnail_${index}";
		}

		$photo = $this->getPhoto($index);

		if(! $photo) {
			return '';
		}

		if ($photo->isAlbum()) {
			$myAlbum = $this->getNestedAlbum($index);
			return $myAlbum->getHighlightAsThumbnailTag($size, $attrs);
		}
		else {
			return $photo->getThumbnailTag($this->getAlbumDirURL("thumb"), $size, $attrs);
		}
	}

	function getThumbnailTagById($id, $size = 0, $attrs = array()) {
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

	function getHighlightAsThumbnailTag($size = 0, $attrList = array()) {
		list ($album, $photo) = $this->getHighlightedItem();

		if ($photo) {
			return $photo->getThumbnailTag($album->getAlbumDirURL('highlight'), $size, $attrList);
		}
		else {
			if(isset($attrList['class'])) {
				$attrList['class'] .= 'title';
			}
			else {
				$attrList['class'] = 'title';
			}

			$attrs = generateAttrs($attrList);

			return "<span$attrs>". gTranslate('core', "No highlight!") .'</span>';
		}
	}

	function getHighlightTag($size = 0, $attrList = array(), $useDefaults = true) {
		$index = $this->getHighlight();

		$defaults = array(
			'alt' => sprintf(
				gTranslate('core', "Highlight for album: %s"),
							strip_tags($this->fields['title'])),
			'title' => sprintf(
				gTranslate('core', "Highlight for album: %s"),
							gallery_htmlentities(strip_tags($this->fields['title'])))
		);

		if (isset($index)) {
			$photo = $this->getPhoto($index);

			if($useDefaults) {
				foreach($defaults as $attr => $default) {
					if(empty($attrList[$attr])) {
						$attrList[$attr] = $defaults[$attr];
					}
				}
			}

			return $photo->getHighlightTag($this->getAlbumDirURL('highlight'), $size, $attrList);
		}
		else {
			unset($attrList['alt']);

			if(isset($attrList['class'])) {
				$attrList['class'] .= ' title';
		}
		else {
				$attrList['class'] = 'title';
			}

			$attrs = generateAttrs($attrList);

			return "<span$attrs>". gTranslate('core', "No highlight!") .'</span>';
		}
	}

	function getPhotoTag($index, $full = false, $attrs = array()) {
		$photo = $this->getPhoto($index);
		if ($photo->isMovie()) {
			return $photo->getThumbnailTag($this->getAlbumDirURL("thumb"));
		}
		else {
			return $photo->getPhotoTag($this->getAlbumDirURL("full"), $full, $attrs);
		}
	}

	function getPhotoPath($index, $full = false) {
		$photo = $this->getPhoto($index);
		return $photo->getPhotoPath($this->getAlbumDirURL("full"), $full);
	}

	/**
	 * returns the absolute system path to a photo.
	 *
	 * @param integer $index
	 * @param boolean $full
	 * @return string
	 */
	function getAbsolutePhotoPath($index, $full = false) {
		$photo = $this->getPhoto($index);

		return $photo->getPhotoPath($this->getAlbumDir(), $full);
	}

	/**
	 * Returns the name of an item.
	 * Can either be the name of the photo, or the albumname.
	 *
	 * @param integer $index
	 * @return string
	 */
	function getPhotoId($index) {
		$item = $this->getPhoto($index);

		return $item->getPhotoId();
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
		if (isset($gallery->app->feature['mirror']) &&
		    isset($gallery->app->mirrorSites) &&
		    strcmp($type, "highlight"))
		{
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

	function numPhotos($show_hidden=0, $strict_count=0) {
		if (!$strict_count) {
			if ($show_hidden) {
				return sizeof($this->photos);
			} else {
				return sizeof($this->photos) - $this->numHidden();
			}
		}
		else {
			$count = 0;
			if (!sizeof($this->photos)) {
				return $count;
			}
			foreach ($this->photos as $photo) {
				if ($photo->isAlbum() || ($photo->isHidden() && !$show_hidden)) {
					continue;
				}
				else {
					$count++;
				}
			}
			return $count;
		}
	}

	/* This is a new function for numVisibleItems */
	/* Old function should be removed */
	function numItems($user = NULL, $recursive = false) {
		if(empty($user)) {
			return array(-1, -1, -1);
		}

		$uuid = $user->getUid();
		$numItemsTotal = $numAlbums = $numPhotos = 0;
		$canWrite = $user->canWriteToAlbum($this);
		$numItems = $this->numPhotos(1);

		for ($itemNr = 1; $itemNr <= $numItems; $itemNr++) {
			$item = $this->getPhoto($itemNr);
			if ($item->isAlbum()) {
				$subalbum = new Album();
				$subalbum->load($item->getAlbumName(), $recursive);
				if (($user->canReadAlbum($subalbum) && !$item->isHidden()) || $user->canWriteToAlbum($subalbum)) {
					if(!$recursive) {
						$numAlbums++;
					}
					else {
						list($subNumItems, $subNumAlbums, $subNumPhotos) =  $subalbum->numItems($user, $recursive);
						$numAlbums++;
						$numAlbums += $subNumAlbums;
						$numPhotos += $subNumPhotos;
					}
				}
			} elseif ($canWrite || !$item->isHidden() || $this->isItemOwner($uuid, $itemNr)) {
				$numPhotos++;
			}
		}

		$numItemsTotal = $numAlbums + $numPhotos;

		return (array($numItemsTotal, $numAlbums, $numPhotos));
	}

	function numVisibleItems($user, $returnVisibleItems = false) {
		$uuid = $user->getUid();

		if ($returnVisibleItems) {
			$visibleItems = array();
			$numVisibleItems = 0;
		}
		$numPhotos = $numAlbums = 0;
		$canWrite = $user->canWriteToAlbum($this);
		$numItems = $this->numPhotos(1);
		for ($i = 1; $i <= $numItems; $i++) {
			$photo = $this->getPhoto($i);
			if ($photo->isAlbum()) {
				$album = new Album();
				$album->load($photo->getAlbumName(), false);
				if (($user->canReadAlbum($album) && !$photo->isHidden()) || $user->canWriteToAlbum($album)) {
					$numAlbums++;
					if ($returnVisibleItems) {
						$visibleItems[++$numVisibleItems] = $i;
					}
				}
			}
			elseif ($canWrite || !$photo->isHidden() || $this->isItemOwner($uuid, $i)) {
				$numPhotos++;
				if ($returnVisibleItems) {
					$visibleItems[++$numVisibleItems] = $i;
				}
			}
		}

		if ($returnVisibleItems) {
			return array($numPhotos, $numAlbums, $visibleItems);
		}
		else {
			return array($numPhotos, $numAlbums);
		}
	}

	function getIds($show_hidden = false) {
		foreach ($this->photos as $photo) {
			if ((!$photo->isHidden() || $show_hidden) && !$photo->getAlbumName()) {
				$ids[] = $photo->getPhotoId();
			}
		}
		return $ids;
	}

	function &getPhoto($index) {
		global $errortext;

		$index = intval($index);

		if ($index >= 1 && $index <= sizeof($this->photos)) {
			$photo = & $this->photos[$index-1];
		}
		else {
			$errortext = sprintf(
				gTranslate('core',"Requested index [%d] out of bounds [%d]. Gallery could not load the requested album item."),
				$index,
				sizeof($this->photos)
			);
			echo debugMessage($errortext, __FILE__, __LINE__);
			$photo = false;
		}

		return $photo;
	}

	function getPhotoIndex($id) {
		for ($i = 1; $i <= $this->numPhotos(1); $i++) {
			$photo = $this->getPhoto($i);
			if ($photo->getPhotoId() == $id) {
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
		// populating old photos with data
		if (!$uploadDate) {
			$this->setUploadDate($index);
			$this->save();
			$uploadDate = $this->getUploadDate($index);
		}
		return $uploadDate;
	}

	function setUploadDate($index, $uploadDate = '') {
		$photo = &$this->getPhoto($index);
		$photo->setUploadDate($uploadDate);
	}

	function getItemCaptureDate($index) {
		$photo = $this->getPhoto($index);
		$itemCaptureDate = $photo->getItemCaptureDate();

		// populating old photos with data
		if (!$itemCaptureDate) {
			$this->setItemCaptureDate($index);
			$this->save();
			$itemCaptureDate = $this->getItemCaptureDate($index);
		}

		return $itemCaptureDate;
	}

	function setItemCaptureDate($index, $itemCaptureDate = '') {
		$photo	= &$this->getPhoto($index);
		$ret	= $photo->setItemCaptureDate($itemCaptureDate, $this);

		return $ret;
	}

	/**
	 * Rebuild all capture dates.
	 *
	 * @param boolean $recursive	Rebuild captures dates in subalbums?
	 * @param integer $level	Indicator in wich sublevel we are. For recursivity.
	 * @return boolean
	 */
	function rebuildCaptureDates($recursive = false, $level = 0) {
		global $gallery;

		$result		= true;
		$numItems	= $this->numPhotos(1);

		if($level == 0) {
			printf(gTranslate('core', "Updating album: '<i>%s</i>'."), $this->fields['title']);
		}
		else {
			$parentAlbums = $this->getParentAlbums(true, true, false, $level);
			printf(gTranslate('core', "Updating subalbum '<i>%s</i>'."), albumBreadcrumb($parentAlbums));
		}
		$level++;

		if($numItems == 0) {
			echo '<br>' . gTranslate('core', "-- Skipped, because it is empty.");
			return true;
		}

		$onePercent	= 100/$numItems;
		$progressbarID	= 'pbarId_' . $this->fields['name'];

		echo addProgressbar($progressbarID);

		$step = 0;
		for ($i = 1; $i <= $numItems; $i++) {
			updateProgressBar(
				$progressbarID,
				sprintf(gTranslate('core', "Processing item %d of %d."), $i, $numItems),
				-1
			);

			if ($this->isAlbum($i)) {
				if($recursive) {
					$nestedAlbum = new Album();
					$nestedAlbum->load($this->getAlbumName($i));

					echo "\n<div style=\"margin-top: 10px; padding-left: 10px;\">\n\t";

					$ret = $nestedAlbum->rebuildCaptureDates($recursive, $level);
					if (! $ret) {
						$result = false;
						addProgressBarText($progressbarID, '<br>' .
							sprintf(gTranslate('core', "Problem with item #%d (subalbum)."), $i));
					}
					else {
						$step++;
					}
					$nestedAlbum->save();
					echo '</div>';
				}
			}
			else {
				$ret = $this->setItemCaptureDate($i);
				if (! $ret) {
					$result = false;
					addProgressBarText($progressbarID, '<br>'.
						sprintf(gTranslate('core', "Problem with item #%d."), $i));
				}
				else {
					$step++;
				}
			}

			updateProgressBar(
				$progressbarID,
				'-1',
				ceil($step * $onePercent)
			);
		}

		$this->save();

		return $result;
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

	function getKeyWords($index) {
		$photo = $this->getPhoto($index);
		return $photo->getKeyWords();
	}

	function setKeyWords($index, $keywords) {
		$photo = &$this->getPhoto($index);
		$photo->setKeyWords($keywords);
	}

	function rotatePhoto($index, $direction, $clearexifrotate = false) {
		$this->updateSerial = 1;
		$photo = &$this->getPhoto($index);
		$retval = $photo->rotate($this->getAlbumDir(), $direction, $this->fields['thumb_size'], $this, $clearexifrotate);

		if (!$retval) {
			return $retval;
		}
	}

	function watermarkPhoto($index, $wmName, $wmAlphaName, $wmAlign, $wmAlignX, $wmAlignY, $preview=0, $previewSize=0, $wmSelect=0) {
		$this->updateSerial = 1;
		$photo = &$this->getPhoto($index);
		$retval = $photo->watermark(
			$this->getAlbumDir(),
			$wmName,
			$wmAlphaName,
			$wmAlign,
			$wmAlignX,
			$wmAlignY,
			$preview,
			$previewSize,
			$wmSelect
		);
		if (!$retval) {
			return $retval;
		}
		$resetModDate = 1;
		$this->save(array(), $resetModDate);
	}

	function watermarkAlbum($wmName, $wmAlphaName, $wmAlign, $wmAlignX, $wmAlignY, $recursive=0, $wmSelect=0) {
		$this->updateSerial = 1;
		$count = $this->numPhotos(1);
		for ($index = 1; $index <= $count; $index++) {
			$photo = &$this->getPhoto($index);
			if ($photo->isAlbum() && $recursive) {
				if ($recursive) {
					$subAlbumName = $this->getAlbumName($index);
					$subAlbum = new Album();
					$subAlbum->load($subAlbumName);
					$subAlbum->watermarkAlbum($wmName, $wmAlphaName,
					$wmAlign, $wmAlignX, $wmAlignY, $recursive, $wmSelect);
				}
			}
			else if ($photo->isMovie()) {
				// Watermarking of movies not supported
			}
			else {
				$photo->watermark($this->getAlbumDir(),
				$wmName, $wmAlphaName,
				$wmAlign, $wmAlignX, $wmAlignY,
				0, 0, // Not a preview
				$wmSelect);
			}
		} // next $index
	} // end of function

	function makeThumbnail($index) {
		$this->updateSerial = 1;
		$photo = &$this->getPhoto($index);

		if (!$photo->isAlbum()) {
			$photo->makeThumbnail($this->getAlbumDir(), $this->fields['thumb_size'], $this);
		}
		else {
			// Reselect highlight of subalbum..
			$album = $this->getNestedAlbum($index);
			$i = $album->getHighlight();
			if (isset($i)) {
				$album->setHighlight($i);
				$album->save();
			}
		}
	}

	function makeThumbnails($recursive = false) {
		global $gallery;

		$numItems = $this->numPhotos(1);

		if($numItems == 0) {
			echo gTranslate('core', " -- Skipping") . '<br>';
			return true;
		}

		$onePercent	= 100/$numItems;
		$progressbarID	= $this->fields['name'];

		echo addProgressbar(
				$progressbarID,
				sprintf(
					gTranslate('core', "Updating album: '<i>%s</i>' (%s) with %d items."),
					$this->fields['title'],
					$this->fields['name'],
					$numItems)
		);

		for ($i = 1; $i <= $numItems; $i++) {
			if ($this->isAlbum($i) && $recursive) {
				$nestedAlbum = new Album();
				$nestedAlbum->load($this->getAlbumName($i));
				$np = $nestedAlbum->numPhotos(1);

				echo "<br>";
				printf(gTranslate('core', "Entering subalbum '<i>%s</i>', processing %d items."), $this->getAlbumName($i), $np);
				$nestedAlbum->makeThumbnails($recursive);
				$nestedAlbum->save();
			}
			else {
				my_flush();
				set_time_limit($gallery->app->timeLimit);
				$this->makeThumbnail($i);
			}

			updateProgressBar(
				$progressbarID,
				sprintf(gTranslate('core', "Processing item %d..."), $i),
				ceil($i * $onePercent)
			);
		}
	}

	function movePhoto($index, $newIndex) {
		/* Pull photo out */
		$photo = array_splice($this->photos, $index-1, 1);
		array_splice($this->photos, $newIndex, 0, $photo);
	}

	function rearrangePhotos($newOrder) {
		// safety check.. no repeats, all valid 1-based indices
		$check = array();
		$count = count($this->photos);
		foreach ($newOrder as $index) {
			if ($index < 1 || $index > $count || isset($check[$index]))
			return;
			$check[$index] = 1;
		}
		// build new list..
		$newList = array();
		for ($i=$j=0; $i < $count; $i++) {
			if (in_array($i+1, $newOrder)) {
				$newList[$i] = $this->photos[$newOrder[$j++]-1];
			} else {
				$newList[$i] = $this->photos[$i];
			}
		}
		$this->photos = $newList;
	}

	function isImage($id) {
		$index = $this->getPhotoIndex($id);
		$photo = $this->getPhoto($index);

		return $photo->isImage();
	}

	function isImageByIndex($index) {
		$photo = $this->getPhoto($index);

		return $photo->isImage();
	}

	function isMovie($id) {
		$index = $this->getPhotoIndex($id);
		$photo = $this->getPhoto($index);

		return $photo->isMovie();
	}

	function isMovieByIndex($index) {
		$photo = $this->getPhoto($index);

		return $photo->isMovie();
	}

	/**
	 * Is a user owner of an item?
	 *
	 * @param string $uid
	 * @param integer $index
	 * @return boolean
	 */
	function isItemOwner($uid, $index) {
		global $gallery;

		$ownerID = $this->getItemOwner($index);

		if($uid == $ownerID) {
			debugMessage(sprintf(gTranslate('core', "Userid %s is owner of'%s'"), $uid, $this->fields['name']), __FILE__, __LINE__);
			return true;
		}

		$everybody	= $gallery->userDB->getEverybody();
		$everybodyUid	= $everybody->getUid();
		if($ownerID == $everybodyUid) {
			debugMessage(sprintf(gTranslate('core', "Userid %s is owner of'%s'"), $uid, $this->fields['name']), __FILE__, __LINE__);
			return true;
		}

		debugMessage(sprintf(gTranslate('core', "Userid %d is NOT owner of'%s'"), $uid, $this->fields['name']), __FILE__, __LINE__);
		return false;
	}

	/**
	 * Is an albumitem an album?
	 *
	 * @param integer  $index
	 * @return boolean $ret
	 */
	function isAlbum($index) {
		$photo = $this->getPhoto($index);

		if(!$photo) {
			$ret = false;
		}
		elseif($photo->getAlbumName() === NULL) {
			$ret = false;
		}
		else {
			$ret = true;
		}

		return $ret;
	}

	/**
	 * Get the albumName of an albumitem
	 *
	 * @param integer $index
	 * @return mixed  $ret    null if $photo does not exits, empty on nonalbums,
	 *                        otherwise the album name.
	 */
	function getAlbumName($index) {
		$photo = $this->getPhoto($index);

		if(!$photo) {
			$ret = null;
		}
		else {
			$ret = $photo->getAlbumName();
		}

		return $ret;
	}

	function setAlbumName($index, $name) {
		$photo = &$this->getPhoto($index);
		$photo->setAlbumName($name);
	}

	function resetClicks() {
		$this->fields['clicks'] = 0;
		$this->fields['clicks_date'] = time();
		$resetModDate = 0;
		$this->save(array(), $resetModDate);

	}

	function resetAllClicks() {
		$this->resetClicks();
		for ($i=1; $i <= $this->numPhotos(1); $i++) {
			$this->resetItemClicks($i);
		}
		$resetModDate = 0;
		$this->save(array(), $resetModDate);
	}

	function getClicks() {
		// just in case we have no clicks yet...
		if (!isset($this->fields['clicks'])) {
			$this->resetClicks();
		}
		return $this->fields['clicks'];
	}

	function getClicksDate() {
		global $gallery;

		$time = $this->fields['clicks_date'];

		// albums may not have this field.
		if (!$time) {
			$this->resetClicks();
			$time = $this->fields['clicks_date'];
		}
		return strftime($gallery->app->dateString,$time);

	}

	function incrementClicks() {
		if (strcmp($this->fields['display_clicks'], "yes")) {
			return;
		}

		$this->fields['clicks']++;
		$resetModDate=0; // don't reset last_mod_date
		$this->save(array(), $resetModDate);
	}

	function getItemClicks($index) {
		$photo = $this->getPhoto($index);
		return $photo->getItemClicks();
	}

	function incrementItemClicks($index) {
		if (strcmp($this->fields['display_clicks'], "yes")) {
			return;
		}

		$photo = &$this->getPhoto($index);
		$photo->incrementItemClicks();

		//don't reset last_mod_date
		$resetModDate = 0;
		$this->save(array(), $resetModDate);
	}

	function resetItemClicks($index) {
		$photo = &$this->getPhoto($index);
		$photo->resetItemClicks();
	}

	function getExif($index, $forceRefresh = false) {
		global $gallery;

		if (empty($gallery->app->use_exif)) {
			return array();
		}

		$dir = $this->getAlbumDir();
		$photo =& $this->getPhoto($index);

		list ($status, $exif, $needToSave) = $photo->getExif($dir, $forceRefresh);

		if ($status != 0) {
			// An error occurred.
			return array(
				'junk1' => '',
				'Error' => sprintf(gTranslate('core', "Error getting EXIF data. Expected status 0, got %s."),$status),
				'status' => $status);
		}

		if ($needToSave) {
			//don't reset last_mod_date
			$resetModDate = 0;
			$this->save(array(), $resetModDate);
		}

		return $exif;
	}

	function getCreationDate() {
		global $gallery;

		if(!empty($this->fields['creation_date'])) {
			$creationDate = $this->fields['creation_date'];
		}

		if (isset($creationDate)) {
			return strftime($gallery->app->dateString,$creationDate);
		}
		else {
			return false;
		}
	}

	function getLastModificationDate() {
		global $gallery;
		$dir = $this->getAlbumDir();

		$time = $this->fields['last_mod_time'];

		// Older albums may not have this field.
		if (!$time) {
			$stat = fs_stat("$dir/album.dat");
			$time = $stat[9];
		}

		return strftime($gallery->app->dateString,$time);
	}

	function setNestedProperties() {
		for ($i=1; $i <= $this->numPhotos(1); $i++) {
			if ($this->isAlbum($i)) {
				$nestedAlbum = new Album();
				$nestedAlbum->load($this->getAlbumName($i));
				$nestedAlbum->fields['bgcolor']			= $this->fields['bgcolor'];
				$nestedAlbum->fields['textcolor']		= $this->fields['textcolor'];
				$nestedAlbum->fields['linkcolor']		= $this->fields['linkcolor'];
				$nestedAlbum->fields['background']		= $this->fields['background'];
				$nestedAlbum->fields['font']			= $this->fields['font'];
				$nestedAlbum->fields['bordercolor']		= $this->fields['bordercolor'];
				$nestedAlbum->fields['border']			= $this->fields['border'];
				$nestedAlbum->fields['thumb_size']		= $this->fields['thumb_size'];
				$nestedAlbum->fields['thumb_ratio']		= $this->fields['thumb_ratio'];
				$nestedAlbum->fields['resize_size']		= $this->fields['resize_size'];
				$nestedAlbum->fields['resize_file_size']	= $this->fields['resize_file_size'];
				$nestedAlbum->fields['max_size']		= $this->fields['max_size'];
				$nestedAlbum->fields['max_file_size']		= $this->fields['max_file_size'];
				$nestedAlbum->fields['returnto']		= $this->fields['returnto'];
				$nestedAlbum->fields['rows']			= $this->fields['rows'];
				$nestedAlbum->fields['cols']			= $this->fields['cols'];
				$nestedAlbum->fields['fit_to_window']		= $this->fields['fit_to_window'];
				$nestedAlbum->fields['use_fullOnly']		= $this->fields['use_fullOnly'];
				$nestedAlbum->fields['print_photos']		= $this->fields['print_photos'];
				$nestedAlbum->fields['slideshow_type']		= $this->fields['slideshow_type'];
				$nestedAlbum->fields['slideshow_recursive']	= $this->fields['slideshow_recursive'];
				$nestedAlbum->fields['slideshow_length']	= $this->fields['slideshow_length'];
				$nestedAlbum->fields['slideshow_loop']		= $this->fields['slideshow_loop'];
				$nestedAlbum->fields['album_frame']		= $this->fields['album_frame'];
				$nestedAlbum->fields['thumb_frame']		= $this->fields['thumb_frame'];
				$nestedAlbum->fields['image_frame']		= $this->fields['image_frame'];
				$nestedAlbum->fields['nav_thumbs']		= $this->fields['nav_thumbs'];
				$nestedAlbum->fields['nav_thumbs_style']	= $this->fields['nav_thumbs_style'];
				$nestedAlbum->fields['nav_thumbs_first_last']	= $this->fields['nav_thumbs_first_last'];
				$nestedAlbum->fields['nav_thumbs_prev_shown']	= $this->fields['nav_thumbs_prev_shown'];
				$nestedAlbum->fields['nav_thumbs_next_shown']	= $this->fields['nav_thumbs_next_shown'];
				$nestedAlbum->fields['nav_thumbs_location']	= $this->fields['nav_thumbs_location'];
				$nestedAlbum->fields['nav_thumbs_size']		= $this->fields['nav_thumbs_size'];
				$nestedAlbum->fields['nav_thumbs_current_bonus']= $this->fields['nav_thumbs_current_bonus'];
				$nestedAlbum->fields['use_exif']		= $this->fields['use_exif'];
				$nestedAlbum->fields['display_clicks']		= $this->fields['display_clicks'];
				$nestedAlbum->fields['item_owner_display']	= $this->fields['item_owner_display'];
				$nestedAlbum->fields['item_owner_modify'] 	= $this->fields['item_owner_modify'];
				$nestedAlbum->fields['item_owner_delete']	= $this->fields['item_owner_delete'];
				$nestedAlbum->fields['add_to_beginning']	= $this->fields['add_to_beginning'];
				$nestedAlbum->fields['showDimensions']		= $this->fields['showDimensions'];
				$nestedAlbum->fields['ecards']			= $this->fields['ecards'];
				$nestedAlbum->fields['email_me']		= array();
				$nestedAlbum->fields['poll_type']		= $this->fields['poll_type'];
				$nestedAlbum->fields['poll_scale']		= $this->fields['poll_scale'];
				$nestedAlbum->fields['poll_nv_pairs']		= $this->fields['poll_nv_pairs'];
				$nestedAlbum->fields['poll_hint']		= $this->fields['poll_hint'];
				$nestedAlbum->fields['poll_show_results']	= $this->fields['poll_show_results'];
				$nestedAlbum->fields['poll_num_results']	= $this->fields['poll_num_results'];
				$nestedAlbum->fields['voter_class']		= $this->fields['voter_class'];
				$nestedAlbum->fields['extra_fields']		= $this->fields['extra_fields'];
				$nestedAlbum->save();
				$nestedAlbum->setNestedProperties();
			}
		}
	}

	function setNestedExtraFields() {
		for ($i=1; $i <= $this->numPhotos(1); $i++) {
			if ($this->isAlbum($i)) {
				$nestedAlbum = new Album();
				$nestedAlbum->load($this->getAlbumName($i));
				$nestedAlbum->fields['extra_fields'] = $this->fields['extra_fields'];
				$nestedAlbum->save();
				$nestedAlbum->setNestedExtraFields();
			}
		}
	}

	function setNestedPollProperties() {
		for ($i = 1; $i <= $this->numPhotos(1); $i++) {
			if ($this->isAlbum($i)) {
				$nestedAlbum = new Album();
				$nestedAlbum->load($this->getAlbumName($i));
				$nestedAlbum->fields['poll_type']			= $this->fields['poll_type'];
				$nestedAlbum->fields['poll_scale']			= $this->fields['poll_scale'];
				$nestedAlbum->fields['poll_nv_pairs']		= $this->fields['poll_nv_pairs'];
				$nestedAlbum->fields['poll_hint']			= $this->fields['poll_hint'];
				$nestedAlbum->fields['poll_show_results']	= $this->fields['poll_show_results'];
				$nestedAlbum->fields['poll_num_results']	= $this->fields['poll_num_results'];
				$nestedAlbum->fields['voter_class']			= $this->fields['voter_class'];

				$nestedAlbum->save();
				$nestedAlbum->setNestedPollProperties();
			}
		}
	}

	function setNestedPermissions() {
		for ($i = 1; $i <= $this->numPhotos(1); $i++) {
			if ($this->isAlbum($i)) {
				$nestedAlbum = new Album();
				$nestedAlbum->load($this->getAlbumName($i));

				$nestedAlbum->fields['owner'] = $this->fields['owner'];
				$nestedAlbum->fields['perms'] = $this->fields['perms'];

				$nestedAlbum->save();
				$nestedAlbum->setNestedPermissions();
			}
		}
	}

	function getPerm($permName, $uid) {
		global $gallery;

		if (isset($this->fields['perms'][$permName])) {
			$perm = $this->fields['perms'][$permName];
		}
		else {
			return false;
		}

		if (isset($perm[$uid])) {
			return true;
		}


		/* If everybody has the perm, then we do too */
		$everybody = $gallery->userDB->getEverybody();
		if (isset($perm[$everybody->getUid()])) {
			return true;
		}

		/**
		 * If loggedIn has the perm and we're logged in, then we're ok also.
		 *
		 * phpBB2's anonymous user are also "logged in", but we have to ignore this.
		 */
        	global $GALLERY_EMBEDDED_INSIDE_TYPE;

		$loggedIn = $gallery->userDB->getLoggedIn();
		if (isset($perm[$loggedIn->getUid()]) &&
		    strcmp($gallery->user->getUid(), $everybody->getUid()) &&
		    ! ($GALLERY_EMBEDDED_INSIDE_TYPE == 'phpBB2' && $gallery->user->uid == -1))
		{
			return true;
		}

		/**
		 * GEEKLOG MOD
		 * We're also going to check to see if its possible that a
		 * group membership can authenticate us.
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
		if (!empty($this->fields['perms'][$permName])) {
			foreach ($this->fields['perms'][$permName] as $uid => $junk) {
				$tmpUser = $gallery->userDB->getUserByUid($uid);
				$perms[$uid] = $tmpUser->getUsername();
			}
		}

		return $perms;
	}

	/**
	 * Set or unset a permission on an album for a user
	 * 	 *
	 * @param string  $permName   Name of permission. See includes/definitions/albumPermissions.php
	 * @param integer $id         User or Group ID.
	 * @param boolean $bool       If true, permissions is granted, otherwise revoked.
	 */
	function setPerm($permName, $id, $bool) {
		if ($bool) {
			$this->fields['perms'][$permName][$id] = 1;
		}
		elseif (isset($this->fields['perms'][$permName][$id])) {
			unset($this->fields['perms'][$permName][$id]);
		}
	}


	/**
	 * Is a user allowed to add comments?
	 * Owner and admins are always allowed.
	 * Note: Added in 1.5.9 that admins are always allowed.
	 *
	 * @param string   $uid
	 * @return boolean
	 */
	function canAddComments($uid) {
		global $gallery;

		if ($this->isOwner($uid)) {
			return true;
		}

		$user = $gallery->userDB->getUserByUid($uid);

		if ($user->isAdmin()) {
			return true;
		}

		return $this->getPerm("canAddComments", $uid);
	}

	/**
	 * Is a user allowed to add items to the album?
	 * Owner and admins are always allowed.
	 * Note: Added in 1.5.9 that admins are always allowed.
	 *
	 * @param string   $uid
	 * @return boolean
	 */
	function canAddTo($uid) {
		global $gallery;

		if ($this->isOwner($uid)) {
			return true;
		}

		$user = $gallery->userDB->getUserByUid($uid);

		if ($user->isAdmin()) {
			return true;
		}

		return $this->getPerm("canAddTo", $uid);
	}

	/**
	 * Is a user allowed to change album texts?
	 * Owner and admins are always allowed.
	 * Note: Added in 1.5.9 that admins are always allowed.
	 *
	 * @param string   $uid
	 * @return boolean
	 */
	function canChangeText($uid) {
		global $gallery;

		if ($this->isOwner($uid)) {
			return true;
		}

		$user = $gallery->userDB->getUserByUid($uid);

		if ($user->isAdmin()) {
			return true;
		}

		return $this->getPerm("canChangeText", $uid);
	}

	/**
	 * Is a user allowed to create subalbums?
	 * Owner and admins are always allowed.
	 * Note: Added in 1.5.9 that admins are always allowed.
	 *
	 * @param string   $uid
	 * @return boolean
	 */
	function canCreateSubAlbum($uid) {
		global $gallery;

		if ($this->isOwner($uid)) {
			return true;
		}

		$user = $gallery->userDB->getUserByUid($uid);

		if ($user->isAdmin()) {
			return true;
		}

		return $this->getPerm("canCreateSubAlbum", $uid);
	}

	/**
	 * Is a user allowed to create subalbums?
	 * Admins are always allowed.
	 * Note: Added in 1.5.9 that admins are always allowed.
	 *
	 * @param string   $uid
	 * @return boolean
	 */
	function canDelete($uid) {
		global $gallery;

		$user = $gallery->userDB->getUserByUid($uid);

		if ($user->isAdmin()) {
			return true;
		}

		return $this->getPerm("canDelete", $uid);
	}

	/**
	 * Is a user allowed to create subalbums?
	 * Admins are always allowed.
	 * NOTE: Added in 1.5.9 that admins are always allowed.
	 *       REMOVED that owners can do this always!
	 *
	 * @param string   $uid
	 * @return boolean
	 */
	function canDeleteFrom($uid) {
		global $gallery;

		$user = $gallery->userDB->getUserByUid($uid);

		if ($user->isAdmin()) {
			return true;
		}

		return $this->getPerm("canDeleteFrom", $uid);
	}

	/**
	 * Who can see the album?
	 * Admins and owners always can.
	 * NOTE: Added in 1.5.9 that admins are always allowed.
	 *       REMOVED that everybody can see an album if no permissions are set.
	 *
	 *
	 * @param string   $uid
	 * @return boolean
	 */
	function canRead($uid) {
		global $gallery;

		if ($this->isOwner($uid)) {
			return true;
		}

		$user = $gallery->userDB->getUserByUid($uid);

		if ($user->isAdmin()) {
			return true;
		}

		return $this->getPerm("canRead", $uid);
	}


	/**
	 * Would a user be possible to see this album from root down?
	 * Recursive call of 'canRead'
	 *
	 * @param string   $uid
	 * @return boolean
	 */
	function canReadRecurse($uid) {
		global $albumDB;

		if (empty($albumDB)) {
			$albumDB = new AlbumDB();
		}

		if ($this->canRead($uid)) {
			if ($this->isRoot() || empty($this->fields['parentAlbumName'])) {
				return true;
			}

			$parent = $albumDB->getAlbumByName($this->fields['parentAlbumName'], false);

			return $parent->canReadRecurse($uid);
		}
		else {
			return false;
		}
	}

	/**
	 * Is a user allowed to view comments?
	 * Owner and admins are always allowed.
	 * Note: Added in 1.5.9 that admins are always allowed.
	 *
	 * @param string   $uid
	 * @return boolean
	 */
	function canViewComments($uid) {
		global $gallery;

		if ($this->isOwner($uid)) {
			return true;
		}

		$user = $gallery->userDB->getUserByUid($uid);

		if ($user->isAdmin()) {
			return true;
		}

		return $this->getPerm("canViewComments", $uid);
	}

	/**
	 * Who can see the full/original images?
	 * Admins and owners always can.
	 * Note: Added in 1.5.9 that admins are always allowed.
	 *
	 * @param string   $uid
	 * @return boolean
	 */
	function canViewFullImages($uid) {
		global $gallery;

		if ($this->isOwner($uid)) {
			return true;
		}

		$user = $gallery->userDB->getUserByUid($uid);

		if ($user->isAdmin()) {
			return true;
		}

		return $this->getPerm("canViewFullImages", $uid);
	}

	/**
	 * Can a user see an item?
	 *
	 * @param object   $user
	 * @param integer  $index
	 * @param boolean  $full
	 * @return boolean
	 * @author Jens Tkotz
	 */
	function canViewItem($user, $index, $full = false) {
		if(empty($user)) {
			return false;
		}

		$uuid		= $user->getUid();
		$canWrite	= $user->canWriteToAlbum($this);
		$item		= $this->getPhoto($index);

		if ($item->isAlbum()) {
			$subalbum = new Album();
			$subalbum->load($item->getAlbumName());
			if (($user->canReadAlbum($subalbum) && !$item->isHidden()) || $user->canWriteToAlbum($subalbum)) {
				if($full) {
					return $this->canViewFullImages($uuid);
				}
				else {
					return true;
				}
			}
		}
		elseif ($canWrite || !$item->isHidden() || $this->isItemOwner($uuid, $itemNr)) {
			if($full) {
				return $this->canViewFullImages($uuid);
			}
			else {
				return true;
			}
		}

		return false;
	}

	/**
	 * Can i user modify items in this album?
	 * Rotate, Resize, Watermark, Reorder
	 * But not: Delete or Move!
	 * Note: Added in 1.5.9 that admins are always allowed.
	 *
	 * @param string    $uid
	 * @return boolean
	 */
	function canWrite($uid) {
		global $gallery;

		if ($this->isOwner($uid)) {
			return true;
		}

		$user = $gallery->userDB->getUserByUid($uid);

		if ($user->isAdmin()) {
			return true;
		}

		return $this->getPerm("canWrite", $uid);
	}

	/**
	 * Is a user owner of an album?
	 * Also special users like nobody, everbody or loggedIn can be the owner!
	 * An admin does not own every album!
	 *
	 * @param string   $uid
	 * @return boolean
	 */
	function isOwner($uid) {
		global $gallery;

		//debugMessage(sprintf(gTranslate('core',"OwnerUID of album '%s' is '%s' || Tested uid is '%s'"), $this->fields['name'], $this->fields['owner'], $uid), __FILE__, __LINE__);

		if(!isset($this->fields['owner'])) {
			return false;
		}

		if($uid == $this->fields['owner']) {
			return true;
		}

		$everybody	= $gallery->userDB->getEverybody();
		$everybodyUid	= $everybody->getUid();

		if($this->fields['owner'] == $everybodyUid) {
			return true;
		}

		$loggedIn	= new LoggedinUser();
		$loggedInUid	= $loggedIn->getUid();

		if($this->fields['owner'] == $loggedInUid && $gallery->user->isLoggedIn()) {
			return true;
		}

		return false;
	}

	/**
	 * Sets the owner of an album.
	 *
	 * @param integer $uid
	 * @return boolean		True if a corresponding user to the UID exits.
	 */
	function setOwner($uid) {
		global $gallery;

		$tempUser = $gallery->userDB->getUserByUid($uid);

		if($tempUser->getUid() == $uid) {
			$this->fields['owner'] = $uid;
			return true;
		}
		else {
			return false;
		}
	}

	function getOwner() {
		global $gallery;
		return $gallery->userDB->getUserByUid($this->fields['owner']);
	}

	/**
	 * Returns an array with the names of all extrafields for itemes defined in an album
	 *
	 * @param boolean $all  if false, all fields except the AltText field.
	 * @return array
	 */
	function getExtraFields($all = true) {
		$extra_fields = array();

		if ($all) {
			$extra_fields = $this->fields['extra_fields'];
		}
		else {
			foreach($this->fields['extra_fields'] as $value) {
				if ($value != 'AltText') {
					$extra_fields[] = $value;
				}
			}
		}
		return $extra_fields;
	}

	function setExtraFields($extra_fields) {
		$this->fields['extra_fields'] = $extra_fields;
	}

	/**
	 * Returns the values of an extrafield from a photo.
	 * @param   integer	 $index  albumitemIndex
	 * @param   string	 $field  fieldname
	 * @return  string	 $fieldvalue
	 */
	function getExtraField($index, $field) {
		$photo = $this->getPhoto($index);
		$fieldvalue = $photo->getExtraField($field);
		return $fieldvalue;
	}

	function setExtraField($index, $field, $value) {
		$photo = &$this->getPhoto($index);
		$photo->setExtraField($field, $value);
	}

	function getItemOwnerById($id) {
		return $this->getItemOwner($this->getPhotoIndex($id));
	}

	function setItemOwnerById($id, $owner) {
		$index = $this->getPhotoIndex($id);
		$this->setItemOwner($index, $owner);
	}

	function getItemOwnerDisplay() {
		if (isset($this->fields['item_owner_display'])) {
			if (strcmp($this->fields['item_owner_display'], "yes")) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Can the owner of items modify his/her own items?
	 *
	 * @return boolean $ret
	 */
	function getItemOwnerModify() {
		if (isset($this->fields['item_owner_modify']) &&
			$this->fields['item_owner_modify'] == 'yes')
		{
			$ret = true;
		}
		else {
			$ret = false;
		}

		if (isDebugging(2)) {
			if ($ret) {
				debugMessage(gTranslate('core',"Owner can modify his/her own items."), __FILE__, __LINE__);
			}
			else {
				debugMessage(gTranslate('core',"Owner can NOT modify his/her own items."), __FILE__, __LINE__);
			}
		}

		return $ret;
	}

	/**
	 * Can the owner of items delete his/her own items?
	 *
	 * @return boolean $ret
	 */
	function getItemOwnerDelete() {
		if (isset($this->fields['item_owner_delete']) &&
			$this->fields['item_owner_delete'] == 'yes')
		{
			$ret = true;
		}
		else {
			$ret = false;
		}

		if (isDebugging(2)) {
			if ($ret) {
				debugMessage(gTranslate('core',"Owner can delete his/her own items."), __FILE__, __LINE__);
			}
			else {
				debugMessage(gTranslate('core',"Owner can NOT delete his/her own items."), __FILE__, __LINE__);
			}
		}

		return $ret;
	}

	function getAddToBeginning() {
		if (isset($this->fields['add_to_beginning'])) {
			if ($this->fields['add_to_beginning'] === "yes") {
				return true;
			}
		}
		return false;
	}

	function getCaptionName($index) {
		global $gallery;

		if (!$this->getItemOwnerDisplay()) {
			return '';
		}

		$nobody = $gallery->userDB->getNobody();
		$nobodyUid = $nobody->getUid();
		$everybody = $gallery->userDB->getEverybody();
		$everybodyUid = $everybody->getUid();

		$owner = $gallery->userDB->getUserByUid($this->getItemOwner($index));

		if ( !$owner) {
			return '';
		}

	return '('. showOwner($owner) .')';
	}

	/**
	 * Voting type can either be Rank (first, second, third) or critique
	 * (1 point, 2 point 3 point).  The difference is with rank there
	 * can be only one of each point value.
	 */
	function getPollType() {
		if (!isset($this->fields['poll_type']) || $this->fields['poll_type'] == '')
		{
			return "critique";
		}
		return $this->fields['poll_type'];
	}

	function getVoterClass() {
		if (isset($this->fields['voter_class'])) {
			return $this->fields['voter_class'];
		}

		return "Nobody";
	}

	function getPollScale() {
		if (isset($this->fields['poll_scale'])) {
			return $this->fields['poll_scale'];
		}
		return 0;
	}

	function getPollNumResults(){
		if (isset($this->fields['poll_num_results'])) {
			return $this->fields['poll_num_results'];
		}
		return 3;
	}

	function getPollShowResults() {
		if (isset($this->fields['poll_show_results'])) {
			if (strcmp($this->fields['poll_show_results'], "no"))
			{
				return true;
			}
		}
		return false;
	}

	function getPollHorizontal() {
		if (isset($this->fields['poll_orientation'])) {
			if (!strcmp($this->fields['poll_orientation'], "horizontal"))
			{
				return true;
			}
		}
		return false;
	}

	function getVoteNVPairs() {
		global $gallery;
		$nv_pairs=$this->fields['poll_nv_pairs'];
		if ($nv_pairs == null) {
			$nv_pairs == array();
			if ($this->getPollScale() == 1) {
				$nv_pairs[0]['name'] = '';
				$nv_pairs[0]['value'] = '1';
			}
		}
		for ($i = sizeof($nv_pairs); $i<$this->getPollScale() ; $i++) {
			if ($this->getPollType() == "rank") {
				$nv_pairs[$i]['name'] = sprintf(gTranslate('core', "#%d"),($i));
				$nv_pairs[$i]['value'] = $this->getPollScale()-$i+1;
			}
			else {
				$nv_pairs[$i]['name'] = $i;
				$nv_pairs[$i]['value'] = $i;
			}
		}
		return $nv_pairs;
	}

	function getPollHint() {
		global $gallery;
		$hint = $this->fields['poll_hint'];
		if (is_string($hint)) {
			return $hint;
		}
		if ($this->getPollScale() == 1 && $this->getPollType() != "rank") {
			return "I like this";
		}
		else if ($this->getPollType() == "rank") {
			return "Vote for this";
		}
		else {
			return "Do you like this? (1=love it)";
		}
	}

	/* Returns true if votes can be moved with images between $this and $album */
	function pollsCompatible($album) {
		if ($this->fields['poll_type'] != "critique") {
			return false;
		}
		if (isset($album->fields['poll_type']) && ($album->fields['poll_type'] != "critique")) {
			return false;
		}
		if ($this->fields['poll_scale'] != $album->fields['poll_scale']) {
			return false;
		}
		for ($i = 0; $i<$this->fields['poll_scale']; $i++ ) {
			if ($this->fields['poll_nv_pairs'][$i]['value'] !=
			    $album->fields['poll_nv_pairs'][$i]['value'] )
			{
				return false;
			}
		}
		return true;
	}

	function getIndexByVotingId($vote_id) {
		global $gallery;
		if (ereg("^item\.(.*)$", $vote_id, $matches)) {
			$index = $this->getPhotoIndex($matches[1]);
		} else if (ereg("^album\.(.*)$", $vote_id, $matches)) {
			$index = $this->getAlbumIndex($matches[1]);
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
			$index = -1;
		}
		return $index;
	}

	function getVotingIdByIndex($index) {
		$albumName = $this->getAlbumName($index);

		if($albumName === null) {
			$vote_id = null;
		}
		elseif (!empty($albumName)) {
			$vote_id = "album.$albumName";
		}
		else {
			$vote_id = "item.".$this->getPhotoId($index);
		}

		return $vote_id;
	}

	function getSubAlbum($index) {
		$myAlbum = new Album();
		$myAlbum->load($this->getAlbumName($index));
		return $myAlbum;
	}

	function getSubAlbums() {
		$subAlbums = array();
		$index = 0;
		foreach ($this->photos as $photo) {
			$index++;
			if ($photo->isAlbum()) {
				$subAlbum = new Album();
				$subAlbum->load($this->getAlbumName($index));
				array_push($subAlbums, $subAlbum);
			}
		}
		return ($subAlbums);
	}

	//values for type "comment" and "other"
	function getEmailMe($type, $user, $id=null) {
		$uid = $user->getUid();
		if ($id) {
			$index = $this->getPhotoIndex($id);
			$photo = $this->getPhoto($index);
		}
		if ( (isset($this->fields['email_me'][$type]) &&
		isset($this->fields['email_me'][$type][$uid])) ||
		(isset ($photo) && $photo->getEmailMe($type, $user))) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Returns a list of emails of all people who wants to be informed.
	 * @param	string	$type	The type of action the user wants to be informed on
	 * @param 	integer	$id		Optional id, this is for testing on an album item.
	 * @return	string	$emails
	 */
	function getEmailMeList($type, $id = null) {
		global $gallery;
		$emails = array();
		$uids = array();

		/* First check if someone assigned to "type" for this album */
		if (isset($this->fields['email_me'][$type])) {
			$uids = array_keys($this->fields['email_me'][$type]);
		}

		/** Then check whether THE admin wants to be informed (set in config)
		 * Someone may have remove this user, then this setting in config is senseless.
		 */
		$admin = $gallery->userDB->getUserByUsername('admin');
		if ($admin) {
			if ($type == 'comments' && $gallery->app->adminCommentsEmail == 'yes') {
				$uids[] = $admin->getUid();
			} else if ($type == 'other' && $gallery->app->adminOtherChangesEmail == 'yes') {
				$uids[] = $admin->getUid();
			}
		}

		/* We are checking on a photo, get those emails */
		if ($id) {
			$index = $this->getPhotoIndex($id);
			$photo = $this->getPhoto($index);
			if ($photo) {
				$uids = array_merge($uids, $photo->getEmailMeListUid($type));
			}
		}

		foreach ($uids as $uid) {
			$user = $gallery->userDB->getUserByUid($uid);
			if ($user->isPseudo()) {
				continue;
			}

			$email = $user->getEmail();
			if (check_email($email)) {
				$emails[] = $email;
			}
			else {
				if(empty($email)) {
					$text = sprintf(gTranslate('core', "Problem: Skipping %s (UID %s) because no email address was set."),
					$user->getUsername(), $uid);
				}
				else {
					$text = sprintf(gTranslate('core', "Problem: Skipping %s (UID %s) because email address: '%s' is not valid."),
					$user->getUsername(), $uid, $email);
				}

				$messages[] = array('type' => 'error', 'text' => $text);
			}
		}

		if(isDebugging() && !empty($messages)) {
			if(!headers_sent()) {
				printPopupStart(gTranslate('core', "Email problems"));
			}
			echo infoBox($messages);
		}

		return array_unique($emails);
	}

	function setEmailMe($type, $user, $id = null, $recursive = false) {
		$uid = $user->getUid();

		if ($this->getEmailMe($type, $user, $id) && !$recursive) {
			// already set
			return;
		}
		else if ($id) {
			$index = $this->getPhotoIndex($id);
			$photo = &$this->getPhoto($index);
			$photo->setEmailMe($type, $user);
		}
		else {
			$this->fields['email_me'][$type][$uid] = true;
			if($recursive) {
				for ($i = 1; $i <= $this->numPhotos(1); $i++) {
					if ($this->isAlbum($i)) {
						$nestedAlbum = new Album();
						$nestedAlbum->load($this->getAlbumName($i));
						$nestedAlbum->setEmailMe($type, $user, $id=null, $recursive);

					}
				}
			}
		}

		$this->save(array(), false);
	}

	function unsetEmailMe($type, $user, $id = null, $recursive = false) {
		$uid = $user->getUid();

		if (!$this->getEmailMe($type, $user, $id)  && !$recursive) {
			// not set
			return;
		}
		else if ($id) {
			$index = $this->getPhotoIndex($id);
			$photo = &$this->getPhoto($index);
			$photo->unsetEmailMe($type, $user);
		}
		else {
			unset($this->fields['email_me'][$type][$uid]);
			if($recursive) {
				for ($i = 1; $i <= $this->numPhotos(1); $i++) {
					if ($this->isAlbum($i)) {
						$nestedAlbum = new Album();
						$nestedAlbum->load($this->getAlbumName($i));
						$nestedAlbum->unsetEmailMe($type, $user, $id=null, $recursive);

					}
				}
			}
		}
		$this->save(array(), false);
	}

	/**
	 * This functions returns an array that contains the absolute pathes to albumItems.
	 * subalbums are in subarrays
	 *
	 * @param unknown_type $user
	 * @param unknown_type $full
	 * @param unknown_type $visibleOnly
	 * @param unknown_type $recursive
	 * @return unknown
	 */
	function getAlbumItemNames($user = NULL, $full = false, $visibleOnly = false, $recursive = false) {
		$albumItemNames = array();

		if(empty($user)) {
			return $albumItemNames;
		}

		$uuid = $user->getUid();

		$canWrite = $user->canWriteToAlbum($this);
		$numItems = $numItemsTotal = $this->numPhotos(1);

		for ($itemNr = 1; $itemNr <= $numItems; $itemNr++) {
			$item = $this->getPhoto($itemNr);
			if ($item->isAlbum() && $recursive) {
				$subalbumName = $item->getAlbumName();
				$subalbum = new Album();
				/* Always load complete subalbum recursive */
				$subalbum->load($item->getAlbumName(), true);
				$albumItemNames[$subalbumName] = $subalbum->getAlbumItemNames($user, $full, $visibleOnly, $recursive);
/*
				if (($user->canReadAlbum($subalbum) && !$item->isHidden()) || $user->canWriteToAlbum($subalbum)) {
					if(!$recursive) {
						$numAlbums++;
					}
					else {
						list($subNumItems, $subNumAlbums, $subNumPhotos) =  $subalbum->numItems($user, $recursive);
						$numItemsTotal  += $subNumItems;
						$numAlbums++;
						$numAlbums += $subNumAlbums;
						$numPhotos += $subNumPhotos;
					}
				}
*/
			}
			elseif ($canWrite || !$item->isHidden() || $this->isItemOwner($uuid, $itemNr)) {
				$albumItemNames[] = $this->getAbsolutePhotoPath($itemNr, $full);
			}
		}
		return $albumItemNames;
	}

	/**
     *
    */
	function getAlbumSize($user = NULL, $full = false, $visibleOnly = false, $recursive = false) {

		$albumSize = 0;
		$albumItemNames = $this->getAlbumItemNames($user, $full, $visibleOnly, $recursive);
		$justPureFileNames = array_flaten($albumItemNames);

		foreach ($justPureFileNames as $absoluteFileName) {
			$albumSize += fs_filesize($absoluteFileName);
		}

		return $albumSize;
	}

	/**
	 * Adds an imagearea to an album item
	 * @param	$index	integer	albumitem index
	 * @param	$area	string	area coordinates.
	 * @author	Jens Tkotz
	*/
	function addImageArea($index, $area) {
		$photo = &$this->getPhoto($index);
		if(!isset($photo->imageAreas)) {
			$photo->imageAreas = array();
		}
		$photo->imageAreas[] = $area;
	}

	/**
	 * Returns all imageareas of an album item
	 * @param	$index	integer	albumitem index
	 * @return	$areas	array	array of imageareas
	 * @author	Jens Tkotz
	*/
	function getAllImageAreas($index) {
		$photo = $this->getPhoto($index);
		if(!isset($photo->imageAreas)) {
			$photo->imageAreas = array();
		}
		$areas = $photo->imageAreas;
		return $areas;
	}

	/**
	 * Deletes an imagearea of an album item
	 * @param	$photo_index	integer	albumitem index
	 * @param	$arean_index	integer	area index
	 * @author	Jens Tkotz
	*/
	function deleteImageArea($photo_index, $area_index) {
		$photo = &$this->getPhoto($photo_index);
		for($i = $area_index; $i<sizeof($photo->imageAreas)-1; $i++) {
			$photo->imageAreas[$i] = $photo->imageAreas[$i+1];
		}
		array_pop($photo->imageAreas);
	}

	/**
	 * Updates an imagearea of an album item
	 * @param   $photo_index  integer    albumitem index
	 * @param   $area_index   integer    area index
	 * @param   $area_data    array      updated array data
	 * @author  Jens Tkotz
	*/
	function updateImageArea($photo_index, $area_index, $area_data) {
		$photo = &$this->getPhoto($photo_index);
		foreach ($area_data as $key => $value) {
			$photo->imageAreas[$area_index][$key] = $value;
		}
	}

	function getAltText($index) {
		if ($index === null) {
			return '';
		}

		$photo = $this->getPhoto($index);

		return $photo->getAlttext();
	}
}
?>
