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

class AlbumItem {
	var $image;
	var $thumbnail;
	var $preview;
	var $caption;
	var $hidden;
	var $highlight;
	var $highlightImage;
	var $isAlbumName;
	var $clicks;
	var $keywords;
	var $comments;  	// array of comment objects
	var $uploadDate;	// date the item was uploaded
	var $itemCaptureDate;	// associative array of date the item was captured
	// not in EPOCH so we can support dates < 1970
	var $exifData;
	var $owner;		// UID of item owner.
	var $extraFields;
	var $rank;
	var $version;
	var $emailMe;
	var $imageAreas;

	function AlbumItem() {
		global $gallery;
		$this->version = $gallery->album_version;
		$this->extraFields = array();
	}

	//upload date should only be set at file upload time.
	function setUploadDate($uploadDate = '') {
		global $gallery;

		if (!empty($uploadDate)) {
			// set the upload time from the time provided
			$this->uploadDate = $uploadDate;
		} else {
			// if nothing is passed in, get the upload time from the file creation time
			$dir = $gallery->album->getAlbumDir();
			$name = $this->image->name;
			$tag = $this->image->type;
			$file = "$dir/$name.$tag";
			$this->uploadDate = filectime($file);
		}
	}

	function getUploadDate() {
		if (!$this->uploadDate) {
			return false;
		}
		else {
			return $this->uploadDate;
		}
	}

	/**
	 * Sets the capture date for an item.
	 * Either a given, or from EXIF data, or the creation date.
	 *
	 * @param string $itemCaptureDate
	 * @param object $album
	 * @return boolean
	 */
	function setItemCaptureDate($itemCaptureDate = '', $album = null) {
		global $gallery;
		/* Before 1.4.5-cvs-b106 this was an associative array */

		if (empty($itemCaptureDate)) {
			if(empty($album)) {
				return false;
			}
			else {
				$dir	= $album->getAlbumDir();
				$name	= $this->image->name;
				$tag	= $this->image->type;
				$file	= "$dir/$name.$tag";

				$itemCaptureDate = getItemCaptureDate($file);
				if(! $itemCaptureDate) {
					return false;
				}
			}
		}

		$this->itemCaptureDate = $itemCaptureDate;

		return true;
	}

	function getItemCaptureDate() {
		// need to set this value for old photos that don't yet contain it.
		if (!$this->itemCaptureDate) {
			return 0;
		}
		else {
			return $this->itemCaptureDate;
		}
	}

	function getExif($dir, $forceRefresh = false) {
		global $gallery;

		$file = $dir . "/" . $this->image->name . "." . $this->image->type;

		echo debugMessage(sprintf(gTranslate('core', "Getting Exif of file '%s'"), $file), __FILE__, __LINE__);

		/*
		* If we don't already have the exif data, get it now.
		* Otherwise return what we have.
		*/
		$needToSave = false;
		if ($gallery->app->cacheExif != 'yes') {
			if (empty($this->exifData) || $forceRefresh) {
				/* Cache the current EXIF data and update the item capture date */
				list($status, $this->exifData) = getExif($file);
				$this->setItemCaptureDate($this->exifData);
				$needToSave = true;
			}
			else {
				/* We have a cached value and are not forcing a refresh */
				$status = 0;
			}
			$returnExifData = $this->exifData;
		}
		else {
			/* If the data is cached but the feature is disabled, remove the cache */
			if (!empty($this->exifData)) {
				unset($this->exifData);
				$needToSave = true;
			}
			list($status, $returnExifData) = getExif($file);
		}

		return array($status, $returnExifData, $needToSave);
	}

	function numComments() {
		return sizeof($this->comments);
	}

	function getComment($commentIndex) {
		if (!empty($this->comments)) {
			return $this->comments[$commentIndex-1];
		} else {
			return null;
		}
	}

	function integrityCheck($dir) {
		global $gallery;
		$changed = 0;

		if (!isset($this->version)) {
			$this->version = 0;
		}
		if ($this->version < 10) {
			if (!isset($this->extraFields) or !is_array($this->extraFields)) {
				$this->extraFields=array();
				$changed = 1;
			}
		}
		if ($this->version < 11) {
			if (!isset($this->owner)) {
				$this->owner = $gallery->album->fields["owner"];
				$changed = 1;
			}
		}
		if ($this->version < 12) {
			$nobody = $gallery->userDB->getNobody();
			$nobodyUid = $nobody->getUid();
			if ((!strcmp($this->owner, $nobodyUid) || empty($this->owner)) &&
			strcmp($gallery->album->fields["owner"], $nobodyUid)) {
				$this->owner = $gallery->album->fields["owner"];
				$changed = 1;
			}
		}
		if ($this->version < 16) {
			$this->setRank(0);
		}
		if ($this->version < 24) {
			$this->emailMe = array();
		}

		/* Convert all uids to the new style */
		if ($this->version < 25) {
			// Owner
			$this->owner = $gallery->userDB->convertUidToNewFormat($this->owner);

			// Comments
			for ($i = 0; $i < sizeof($this->comments); $i++) {
				$this->comments[$i]->UID =
				$gallery->userDB->convertUidToNewFormat($this->comments[$i]->UID);
			}
		}

		// Use TimeStamp for capture Date instead of assoziative Array
		if ($this->version < 32) {
			if (isset($this->itemCaptureDate)) {
				$this->itemCaptureDate = mktime(
				$this->itemCaptureDate['hours'],
				$this->itemCaptureDate['minutes'],
				$this->itemCaptureDate['seconds'],
				$this->itemCaptureDate['mon'],
				$this->itemCaptureDate['mday'],
				$this->itemCaptureDate['year']
				);
				$changed = 1;
			}
		}

		/* autoRotated field depricated as of 1.5-cvs-b258 */
		if ($this->version < 33 && !empty($this->extraFields['autoRotated'])) {
			unset($this->extraFields['autoRotated']);
			$changed = 1;
		}

		if ($this->image) {
			if ($this->image->integrityCheck($dir)) {
				$changed = 1;
			}

			if ($this->thumbnail) {
				if ($this->thumbnail->integrityCheck($dir)) {
					$changed = 1;
				}
			}

			if ($this->preview) {
				if ($this->preview->integrityCheck($dir)) {
					$changed = 1;
				}
			}

			if ($this->highlight && $this->highlightImage)  {
				if ($this->highlightImage->integrityCheck($dir)) {
					$changed = 1;
				}
			}
		}

		if (strcmp($this->version, $gallery->album_version)) {
			$this->version = $gallery->album_version;
			$changed = 1;
		}

		return $changed;
	}

	function addComment($comment, $IPNumber, $name) {
		global $gallery;

		if ($gallery->user) {
			$UID = $gallery->user->getUID();
		} else {
			$UID = '';
		}

		$comment = new Comment($comment, $IPNumber, $name, $UID);

		$this->comments[] = $comment;
		return 0;
	}

	function deleteComment($comment_index) {
		array_splice($this->comments, $comment_index-1, 1);
	}

	function setKeyWords($kw) {
		$this->keywords = $kw;
	}

	function getKeyWords() {
		return $this->keywords;
	}

	function setOwner($owner) {
		$this->owner = $owner;
	}

	function getOwner() {
		global $gallery;
		if (!isset($this->owner)) {
			$nobody = $gallery->userDB->getNobody();
			$nobodyUid = $nobody->getUid();
			$this->setOwner($nobodyUid);
		}
		return $this->owner;
	}

	function resetItemClicks() {
		$this->clicks = 0;
	}

	function getItemClicks() {
		if (!isset($this->clicks)) {
			$this->resetItemClicks();
		}
		return $this->clicks;
	}

	function incrementItemClicks() {
		if (!isset($this->clicks)) {
			$this->resetItemClicks();
		}
		$this->clicks++;
	}
	function setRank($rank) {
		$this->rank = $rank;
	}
	function getRank() {
		return $this->rank;
	}

	function hide() {
		$this->hidden = 1;
	}

	function unhide() {
		$this->hidden = 0;
	}

	function isHidden() {
		return $this->hidden;
	}

	function setHighlight($dir, $bool, &$album, $name = null, $tag = null, $srcdir = null, $srcitem = null) {
		global $gallery;

		$this->highlight = $bool;
		// if it is now the highlight make sure it has a highlight thumb otherwise get rid of it's thumb (ouch!).

		if ($this->highlight) {
			if (!isset($name)) {
				$srcdir = $dir;
				$srcitem = $this;
				if ($this->isAlbum()) {
					$name = $this->getAlbumName();
					$nestedAlbum = new Album();
					$nestedAlbum->load($name);
					list ($srcalbum, $srcitem) = $nestedAlbum->getHighlightedItem();
					if ($srcalbum !== null && $srcitem !== null) {
						$srcdir = $srcalbum->getAlbumDir();
						$tag = $srcitem->image->type;
					}
					else {
						if (is_object($this->highlightImage)) {
							$this->highlightImage->simpleDelete($dir);
							$this->highlightImage = null;
						}
						return;
					}
				}
				else {
					$name = $this->image->name;
					$tag = $this->image->type;
				}
			}
			$size = $album->getHighlightSize();

			if ($srcitem->image->thumb_width > 0  && !$srcitem->isMovie()) {
				// Crop it first
				$ret = cut_image("$srcdir/".$srcitem->image->name.".$tag",
				"$dir/$name.tmp.$tag",
				$srcitem->image->thumb_x,
				$srcitem->image->thumb_y,
				$srcitem->image->thumb_width,
				$srcitem->image->thumb_height);

				// Then resize it down
				if ($ret) {
					$ret = resize_image(
					"$dir/$name.tmp.$tag",
					"$dir/$name.highlight.$tag",
					$size,
					0,
					0,
					true,
					$gallery->app->highlightJpegImageQuality
					);
				}
				fs_unlink("$dir/$name.tmp.$tag");
			}
			elseif ($srcitem->isMovie()) {
				if (fs_file_exists($gallery->app->movieThumbnail)) {
					$tag = substr(strrchr($gallery->app->movieThumbnail, '.'), 1);
					$ret = resize_image(
					$gallery->app->movieThumbnail,
					"$dir/$name.highlight.$tag",
					$size,
					0,
					0,
					true,
					$gallery->app->highlightJpegImageQuality
					);
				}
				else {
					$ret = 0;
				}
			} else {
				$ratio = $album->getHighlightRatio();
				$src = "$srcdir/".$srcitem->image->name.".$tag";
				$dest = "$dir/$name.highlight.$tag";

				if(!empty($ratio)) {
					$ret = cropImageToRatio($src, $dest, $size, $ratio);
				}

				if(isset($ret)) {
					$ret = resize_image($dest, $dest, $size, 0, 0, true, $gallery->app->highlightJpegImageQuality);
				} else {
					$ret = resize_image($src, $dest, $size, 0, 0, true, $gallery->app->highlightJpegImageQuality);
				}
			}

			if ($ret) {
				list($w, $h) = getDimensions("$dir/$name.highlight.$tag");

				$high = new Image;
				$high->setFile($dir, "$name.highlight", "$tag");
				$high->setDimensions($w, $h);
				$this->highlightImage = $high;

				/* Check if we need to cascade highlight up to parent album */
				$parentAlbum =& $album->getParentAlbum();
				if (isset($parentAlbum) && !strcmp($parentAlbum->version, $gallery->album_version)) {
					$highlightIndex = $parentAlbum->getHighlight();
					if ($highlightIndex == $parentAlbum->getAlbumIndex($album->fields['name'])) {
						$item = &$parentAlbum->getPhoto($highlightIndex);
						$item->setHighlight($parentAlbum->getAlbumDir(), 1, $parentAlbum, $album->fields['name'], $tag, $srcdir, $srcitem);
						$parentAlbum->save(array(),0);
					}
				}
			}
		}
		else {
			if (is_object($this->highlightImage)) {
				$this->highlightImage->simpleDelete($dir);
				$this->highlightImage = null;
			}
		}
	}

	function isHighlight() {
		return $this->highlight;
	}

	function getThumbDimensions($size = 0) {
		if ($this->thumbnail) {
			return $this->thumbnail->getDimensions($size);
		}
		else {
			return array(0, 0);
		}
	}

	function getHighlightDimensions($size = 0) {
		if (is_object($this->highlightImage)) {
			return $this->highlightImage->getDimensions($size);
		}
		else {
			return array(0, 0);
		}
	}

	function getFileSize($full = 0) {
		global $gallery;
		$stat = fs_stat($this->image->getPath($gallery->album->getAlbumDir(), $full));

		if (is_array($stat)) {
			return $stat[7];
		} else {
			return 0;
		}
	}

	function getDimensions($full = 0) {
		if ($this->image) {
			return $this->image->getDimensions(0, $full);
		} else {
			return array(0, 0);
		}
	}

	function isResized() {
		$image = $this->image;
		return $image->isResized();
	}

	function rotate($dir, $direction, $thumb_size, &$album, $clearexifrotate = false) {
		global $gallery;

		$name = $this->image->name;
		$type = $this->image->type;

		$retval = rotate_image("$dir/$name.$type", "$dir/$name.$type", $direction, $type);

		if ($clearexifrotate && isset($gallery->app->use_exif) && ($type === 'jpg' || $type === 'jpeg')) {
			$path = $gallery->app->use_exif;
			exec_internal(fs_import_filename($path, 1) . " -norot '$dir/$name.$type'");
		}

		if (!$retval) {
			return $retval;
		}

		list($w, $h) = getDimensions("$dir/$name.$type");
		$this->image->setRawDimensions($w, $h);

		if ($this->isResized()) {
			rotate_image("$dir/$name.sized.$type", "$dir/$name.sized.$type", $direction, $type);
			if ($clearexifrotate && isset($gallery->app->use_exif) && ($type === 'jpg' || $type === 'jpeg')) {
				$path = $gallery->app->use_exif;
				exec_internal(fs_import_filename($path, 1) . " -norot '$dir/$name.sized.$type'");
			}

			list($w, $h) = getDimensions("$dir/$name.sized.$type");
			$this->image->setDimensions($w, $h);
		}
		else {
			$this->image->setDimensions($w, $h);
		}

		/* Reset the thumbnail to the default before regenerating thumb */
		$this->image->setThumbRectangle(0, 0, 0, 0);
		$this->makeThumbnail($dir, $thumb_size, $album);

		return 1;
	}

	function watermark($dir, $wmName, $wmAlphaName, $wmAlign, $wmAlignX, $wmAlignY, $preview = 0, $previewSize = 0, $wmSelect = 0) {
		global $gallery;

		if(isset($this->image)) {
			$type = $this->image->type;
		}
		else {
			return false;
		}

		if (isMovie($type) || $this->isAlbum()) {
			// currently there is no watermarking support for movies
			return (0);
		}
		if ($wmSelect < 0) {
			$wmSelect = 0;
		}
		else if ($wmSelect > 2) {
			$wmSelect = 2;
		}

		$name = $this->image->name;
		$oldpreviews = glob($dir . "/$name.preview*.$type");
		if (!empty($oldpreviews) && is_array($oldpreviews)) {
			foreach ($oldpreviews as $oldpreview) {
				unlink($oldpreview);
			}
		}

		if ($preview) {
			$previewtag = "preview" . time();
			if (($previewSize == 0) && $this->isResized()) {
				$src_image = "$dir/" . $this->image->resizedName . ".$type";
			}
			else {
				$src_image = "$dir/$name.$type";
			}

			$retval = watermark_image(
				$src_image,
				"$dir/$name.$previewtag.$type",
				$gallery->app->watermarkDir."/$wmName",
				$gallery->app->watermarkDir."/$wmAlphaName",
				$wmAlign, $wmAlignX, $wmAlignY
			);

			if ($retval) {
				list($w, $h) = getDimensions("$dir/$name.$previewtag.$type");

				$high = new Image;
				$high->setFile($dir, "$name.$previewtag", "$type");
				$high->setDimensions($w, $h);
				$this->preview = $high;
			}
		}
		else {
			/* $wmSelect =
			 * 0 - Both, sized and Full
			 * 1 - Only sized photos
			 * 2 - Only full photos
			*/

			// Watermark the fullsize
			if ($wmSelect != 1) {
				$retval = watermark_image(
					"$dir/$name.$type",
					"$dir/$name.$type",
					$gallery->app->watermarkDir . '/' . $wmName,
					$gallery->app->watermarkDir . '/' . $wmAlphaName,
					$wmAlign, $wmAlignX, $wmAlignY
				);
			}

			// Watermark the resized
			if ($wmSelect != 2) {
				if (($wmSelect == 1) && !$this->isResized()) {
					// If watermarking only resized images, and image is not resized
					// Call resize as if the full image is resized
					$pathToResized = $dir . "/" . $this->image->name . "." . $this->image->type;
					$this->resize($dir, "", 0, $pathToResized);
				}
				if ($this->isResized()) {
					$retval = watermark_image(
						"$dir/$name.sized.$type",
						"$dir/$name.sized.$type",
						$gallery->app->watermarkDir . '/' . $wmName,
						$gallery->app->watermarkDir . '/' . $wmAlphaName,
						$wmAlign,
						$wmAlignX,
						$wmAlignY
					);
				}
			}
		}

		return ($retval);
	}

	/**
	 * Creates the Image Object for an albumitem
	 *
	 * @param string	$dir	Albumdir
	 * @param string	$name	Filname without extension
	 * @param string	$tag	Extension
	 * @param int		$thumb_size
	 * @param object	$album
	 * @param string	$pathToThumb
	 * @return mixed	$ret
	 */
	function setPhoto($dir, $name, $tag, $thumb_size, &$album, $pathToThumb = '') {
		global $gallery;

		/*
		* Sanity: make sure we can handle the file first.
		*/
		if (!isMovie($tag) && !valid_image("$dir/$name.$tag")) {
			return sprintf(gTranslate('core',"Invalid image: %s"), "$name.$tag");
		}

		/* Set our image. */
		$this->image = new Image;
		$this->image->setFile($dir, $name, $tag);

		$ret = $this->makeThumbnail($dir, $thumb_size, $album, $pathToThumb);

		return $ret;
	}

	/**
	 * Creates the thumbnail for an image, or use a predefined for movies.
	 *
	 * @param string	$dir		Path to album
	 * @param int		$thumb_size
	 * @param object	$album
	 * @param string	$pathToThumb
	 * @return mixed
	 */
	function makeThumbnail($dir, $thumb_size, &$album, $pathToThumb = '') {
		global $gallery;
		$name = $this->image->name;
		$tag = $this->image->type;

		$ratio = isset($album->fields["thumb_ratio"]) ? $album->fields["thumb_ratio"] : 0;

		echo debugMessage(gTranslate('core', "Generating thumbnail."),__FILE__, __LINE__);

		debugMessage(sprintf(gTranslate('core', "Saved dimensions: width:%d ; height:%d"),
		$this->image->raw_width, $this->image->raw_height), __FILE__, __LINE__, 3);

		if ($this->isMovie()) {
			/* Use a preset thumbnail */
			if(realpath($gallery->app->movieThumbnail)) {
				fs_copy($gallery->app->movieThumbnail, "$dir/$name.thumb.jpg");
			}
			else {
				echo infobox(array(array(
					'type' => 'error',
					'text' => sprintf(gTranslate('core', "Defined thumbnail does not exist. %s"),
								'<br>'. $gallery->app->movieThumbnail)
				)));
				return false;
			}

			$this->thumbnail = new Image;
			$this->thumbnail->setFile($dir, "$name.thumb", "jpg");
			list($w, $h) = getDimensions("$dir/$name.thumb.jpg");
			$this->thumbnail->setDimensions($w, $h);
		}
		else {
			/* Make thumbnail (first crop it spec) */
			if ($pathToThumb) {
				$ret = copy($pathToThumb,"$dir/$name.thumb.$tag");
			}
			else if ($this->image->thumb_width > 0) {
				$ret = cut_image(
				"$dir/$name.$tag",
				"$dir/$name.thumb.$tag",
				$this->image->thumb_x,
				$this->image->thumb_y,
				$this->image->thumb_width,
				$this->image->thumb_height);

				if ($ret) {
					$ret = resize_image(
					"$dir/$name.thumb.$tag",
					"$dir/$name.thumb.$tag",
					$thumb_size,
					0,
					0,
					true,
					$gallery->app->thumbJpegImageQuality
					);
				}
			} else {
				if(!empty($ratio)) {
					$ret = cropImageToRatio(
					"$dir/$name.$tag",
					"$dir/$name.thumb.$tag",
					$thumb_size,
					$ratio
					);
					if($ret) {
						$ret = resize_image(
						"$dir/$name.thumb.$tag",
						"$dir/$name.thumb.$tag",
						$thumb_size,
						0,
						0,
						true,
						$gallery->app->thumbJpegImageQuality
						);
					}
					else {
						$ret = resize_image(
						"$dir/$name.$tag",
						"$dir/$name.thumb.$tag",
						$thumb_size,
						0,
						0,
						true,
						$gallery->app->thumbJpegImageQuality
						);
					}
				}
				else {
					debugMessage(gTranslate('core', "Generating normal thumbs."), __FILE__, __LINE__);
					// no resizing, use ratio for thumb as for the image itself;
					$ret = resize_image(
					"$dir/$name.$tag",
					"$dir/$name.thumb.$tag",
					$thumb_size,
					0,
					0,
					true,
					$gallery->app->thumbJpegImageQuality
					);
				}
			}

			if ($ret) {
				$this->thumbnail = new Image;
				$this->thumbnail->setFile($dir, "$name.thumb", $tag);

				list($w, $h) = getDimensions("$dir/$name.thumb.$tag");
				$this->thumbnail->setDimensions($w, $h);

				/* if this is the highlight, remake it */
				if ($this->highlight) {
					$this->setHighlight($dir, 1, $album);
				}
			}
			else {
				return gTranslate('core', "Unable to make thumbnail.") ." ($ret)";
			}
		}

		return true;
	}

	function getPreviewTag($dir, $size = 0, $attrs = array()) {
		if(empty($attrs['alt'])) {
			$attrs['alt'] = $this->getAlttext();
		}

		if ($this->preview) {
			return $this->preview->getTag($dir, 0, $size, $attrs);
		}
		else {
			return "<i>". gTranslate('core', "No preview") ."</i>";
		}
	}

	/**
	 * @return	string	$alttext
	 * @author	Jens Tkotz
	 */
	function getAlttext() {
		$alttext = '';

		if (!empty($this->extraFields['AltText'])) {
			$alttext = $this->extraFields['AltText'];
		}
		elseif (!empty($this->caption)) {
			$alttext = $this->caption;
		}

		return $alttext;
	}

	function getThumbnailTag($dir, $size = 0, $attrs = array()) {
		// Prevent non-integer data from being passed
		$size = (int)$size;

		if(empty($attrs['alt'])) {
			$attrs['alt'] = $this->getAlttext();
		}

		if ($this->thumbnail) {
			return $this->thumbnail->getTag($dir, false, $size, $attrs);
		}
		else {
			return "<i>". gTranslate('core', "No thumbnail") ."</i>";
		}
	}

	function getHighlightTag($dir, $size = 0, $attrList = array()) {
		// Prevent non-integer data from being passed
		$size = (int)$size;

		if (is_object($this->highlightImage)) {
			if (empty($attrs['alt'])) {
				$attrs['alt'] = $this->getAlttext();
			}
			return $this->highlightImage->getTag($dir, 0, $size, $attrList);
		}
		else {
			if(isset($attrList['alt'])) {
				unset($attrList['alt']);
			}

			if(isset($attrList['class'])) {
				$attrList['class'] .= ' title';
			}
			else {
				$attrList['class'] = 'title';
			}

			$attrs = generateAttrs($attrList);

			return "<span$attrs>". gTranslate('core', "Requested item has no highlight!") .'</span>';
		}
	}

	function getPhotoTag($dir, $full = false, $attrs) {
		if (empty($attrs['alt'])) {
			$attrs['alt'] = $this->getAlttext();
		}

		if ($this->image) {
			return $this->image->getTag($dir, $full, 0, $attrs);
		}
		else {
			return "about:blank";
		}
	}

	function getPhotoPath($dir, $full = false) {
		if ($this->image) {
			return $this->image->getPath($dir, $full);
		}
		else {
			return "about:blank";
		}
	}

	/**
	 * @param	$full		boolean
	 * @return	$imageName	string
	 * @author	Jens Tkotz
	 */
	function getImageName($full = false) {
		if($this->image) {
			$imageName = $this->image->getImageName($full);
		}
		else {
			$imageName = "about:blank";
		}

		return $imageName;
	}


	/**
	 * Returns the name of an item.
	 * Can either be the name of the photo, or the albumname.
	 *
	 * @return string
	 */
	function getPhotoId() {
		if ($this->image) {
			return $this->image->getId();
		}
		elseif ($this->getAlbumName() != NULL) {
			return $this->getAlbumName();
		}
		else {
			return "unknown";
		}
	}

	function delete($dir) {
		if (is_object($this->highlightImage)) {
			$this->highlightImage->delete($dir);
		}

		if ($this->image) {
			$this->image->delete($dir);
		}

		if ($this->thumbnail) {
			$this->thumbnail->delete($dir);
		}

		if ($this->preview) {
			$this->preview->delete($dir);
		}
	}

	function getCaption() {
		return $this->caption;
	}

	function setCaption($caption) {
		$this->caption = $caption;
	}

	function createCaption($dir, $captionType) {
		global $gallery;
		$dateTimeFormat = $gallery->app->dateTimeString;
		$path = $this->image->getPath($dir, true);

		switch ($captionType) {
			case 0:
				$caption = '';
				break;

			case 1:
			default:
				/* Use filename */
				$caption = strtr($this->image->name, '_', ' ');
				break;

			case 2:
				/* Use file cration date */
				$caption = strftime($dateTimeFormat, filectime($path));
				break;

			case 3:
				/* Use capture date */
				$caption = strftime($dateTimeFormat, getItemCaptureDate($path));
				break;
		}

		$this->setCaption($caption);
	}

	function isAlbum() {
		return ($this->isAlbumName !== NULL) ? true : false;
	}

	function setAlbumName($name) {
		$this->isAlbumName = $name;
	}

	function getAlbumName() {
		return $this->isAlbumName;
	}

	/**
	 * Returns true or false whether the item is in acceptableMovieList or not
	 * @uses lib/filetypes
	 * @return bool
	 */
	function isMovie() {
		if (isset($this->image)) {
			return isMovie($this->image->type);
		}
		else {
			return false;
		}
	}

	/**
	 * Returns true or false whether the item is in acceptableImageList or not
	 * @uses lib/filetypes
	 * @return bool
	 */
	function isImage() {
		if (isset($this->image)) {
			return isImage($this->image->type);
		}
		else {
			return false;
		}
	}

	function resize($dir, $target, $filesize, $pathToResized) {
		if (isset($this->image)) {
			$this->image->resize($dir, $target, $filesize, $pathToResized);
		}
	}

	function setExtraField($name, $value) {
		$this->extraFields[$name] = $value;
	}

	/**
	 * Returns value of an extrafield
	 * @param	$name	string	fieldname
	 * @return	$value	mixed
	 */
	function getExtraField($name) {
		if (isset($this->extraFields[$name])) {
			$value = $this->extraFields[$name];
		}
		else {
			$value = '';
		}
		return $value;
	}

	function lastCommentDate()  {
		global $gallery;
		if ($this->numComments() == 0) {
			return -1;
		}
		$comment = $this->getComment($this->numComments());
		return $comment->datePosted; // returns the time()
	}

	function getEmailMe($type, $user) {
		$uid = $user->getUid();
		if (isset ($this->emailMe[$type])) {
			return isset($this->emailMe[$type][$uid]);
		} else {
			return false;
		}
	}

	function getEmailMeListUid ($type) {
		if (isset( $this->emailMe[$type])) {
			return array_keys($this->emailMe[$type]);
		} else {
			return array();
		}
	}

	function setEmailMe($type, $user) {
		if ($this->getEmailMe($type, $user)) {
			return;
		}
		$uid = $user->getUid();
		$this->emailMe[$type][$uid]=true;
	}

	function unsetEmailMe($type, $user) {
		if (!$this->getEmailMe($type, $user)) {
			return;
		}
		$uid = $user->getUid();
		
		unset($this->emailMe[$type][$uid]);
	}

	function addImageArea($area) {
		if(!isset($this->imageAreas)) {
			$this->imageAreas = array();
		}
		$this->imageAreas[] = $area;
	}

	function getAllImageAreas() {
		if(!isset($this->imageAreas)) {
			$this->imageAreas = array();
		}
		return $this->imageAreas;
	}

	function deleteImageArea($index) {
		unset($this->imageAreas[$index]);
	}
}

?>
