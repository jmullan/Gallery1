<?
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2001 Bharat Mediratta
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
 */
?>
<?
class AlbumItem {
	var $image;
	var $thumbnail;
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

	function setUploadDate($uploadDate="") { //upload date should only be set at file upload time.
		global $gallery;

		if ($uploadDate) { // set the upload time from the time provided
			$this->uploadDate = $uploadDate;
		} else { // if nothing is passed in, get the upload time from the file creation time
			$dir = $gallery->album->getAlbumDir();
			$name = $this->image->name;
			$tag = $this->image->type;
			$file = "$dir/$name.$tag";
			$this->uploadDate = filectime($file);
		}
	}

	function getUploadDate() {
		if (!$this->uploadDate) {
			return 0;
		} else {
			return $this->uploadDate;
		}
	}

	function setItemCaptureDate($itemCaptureDate="") {
		global $gallery;
		/*$itemCaptureDate should be passed in as an associative array with the following elements:
	 	$itemCaptureDate["hours"]
		$itemCaptureDate["minutes"]
		$itemCaptureDate["seconds"]
		$itemCaptureDate["mon"]
		$itemCaptureDate["mday"]
		$itemCaptureDate["year"]
		*/ 
		if (!$itemCaptureDate) {	
			// we want to attempt to set the $itemCaptureDate from the information that
			// is available to us.  First, look in the exif data if it is a jpeg file.  If that
			// doesn't help us, then use the file creation date.
			$dir = $gallery->album->getAlbumDir();
			$name = $this->image->name;
			$tag = $this->image->type;
			$file = "$dir/$name.$tag";
			$itemCaptureDate = getItemCaptureDate($file);
		}
 
		$this->itemCaptureDate = $itemCaptureDate;
	}

	function getItemCaptureDate() {
		// need to set this value for old photos that don't yet contain it.
		if (!$this->itemCaptureDate) {
			return 0;
		} else {
			return $this->itemCaptureDate;
		}
	}

	function getExif($dir) {
		$file = $dir . "/" . $this->image->name . "." . $this->image->type;
		$myExif = getExif($file);
		return $myExif;
	}

	function numComments() {
		return sizeof($this->comments);
	}

	function getComment($commentIndex) {
		return $this->comments[$commentIndex-1];
	}

	function integrityCheck($dir) {
		$changed = 0;

		if ($this->image) {
			if ($this->image->integrityCheck($dir)) {
				$changed = 1;
			}

			if ($this->thumbnail) {
				if ($this->thumbnail->integrityCheck($dir)) {
					$changed = 1;
				}
			}

			if ($this->highlight && $this->highlightImage)  {
				if ($this->highlightImage->integrityCheck($dir)) {
					$changed = 1;
				}
			}
		}
		return $changed;
	}

	function addComment($comment, $IPNumber, $name) {
		global $gallery;

		if ($gallery->user) {
			$UID = $gallery->user->getUID();
		} else {
			$UID = "";
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

	function hide() {
		$this->hidden = 1;
	}

	function unhide() {
		$this->hidden = 0;
	}

	function isHidden() {
		return $this->hidden;
	}

	function setHighlight($dir, $bool) {
		global $gallery;
		
		$this->highlight = $bool;
		
		/*
		 * if it is now the highlight make sure it has a highlight
                 * thumb otherwise get rid of it's thumb (ouch!).
		 */
		$name = $this->image->name;
		$tag = $this->image->type;

		if ($this->highlight) {
			if ($this->isAlbumName) {
				$nestedName = $this->isAlbumName;
				do {
					$nestedAlbum = new Album();
					$nestedAlbum->load($nestedName);
					$dir = $nestedAlbum->getAlbumDir();
					$nestedHighlightIndex = $nestedAlbum->getHighlight();
					$nestedHighlight = $nestedAlbum->getPhoto($nestedHighlightIndex);
					$nestedName = $nestedHighlight->isAlbumName;
				} while ($nestedName);

				$name = $nestedHighlight->image->name;
				$tag  = $nestedHighlight->image->type;
				$ret = 1;
			} else {

				if (($this->image->thumb_width > 0) || ($nestedHighlight->image->thumb_width > 0)) {
					// Crop it first
					if ($this->isAlbumName) {
						$ret = cut_image("$dir/$name.$tag",
                	                                 	"$dir/$name.tmp.$tag",
                        	                         	$nestedHighlight->image->thumb_x,
                                	                 	$nestedHighlight->image->thumb_y,
                                        	         	$nestedHighlight->image->thumb_width,
                                                	 	$nestedHighlight->image->thumb_height);
					} else {
						$ret = cut_image("$dir/$name.$tag", 
							 	"$dir/$name.tmp.$tag", 
							 	$this->image->thumb_x, 
							 	$this->image->thumb_y,
							 	$this->image->thumb_width, 
						 		$this->image->thumb_height);
					}
	
					// Then resize it down
					if ($ret) {
						$ret = resize_image("$dir/$name.tmp.$tag", 
								    "$dir/$name.highlight.$tag",
								    $gallery->app->highlight_size);
					}
					fs_unlink("$dir/$name.tmp.$tag");
				} else {
					$ret = resize_image("$dir/$name.$tag", 
							    "$dir/$name.highlight.$tag",
							    $gallery->app->highlight_size);
				}
			}

			if ($ret) {
				list($w, $h) = getDimensions("$dir/$name.highlight.$tag");

				$high = new Image;
				$high->setFile($dir, "$name.highlight", "$tag");
				$high->setDimensions($w, $h);
				$this->highlightImage = $high;
			}
		} else {
			if (fs_file_exists("$dir/$name.highlight.$tag")) {
				fs_unlink("$dir/$name.highlight.$tag");
			}
		}	
	}

	function isHighlight() {
		return $this->highlight;
	}

	function getThumbDimensions() {
		if ($this->thumbnail) {
			return $this->thumbnail->getDimensions();
		} else {
			return array(0, 0);
		}
	}

	function getDimensions() {
		if ($this->image) {
			return $this->image->getDimensions();
		} else {
			return array(0, 0);
		}
	}

	function isResized() {
		$im = $this->image;
		return $im->isResized();
	}

	function rotate($dir, $direction, $thumb_size) {
		global $gallery;

		$name = $this->image->name;
		$type = $this->image->type;
		$retval = rotate_image("$dir/$name.$type", "$dir/$name.$type", $direction);
		if (!$retval) {
			return $retval;
		}
		list($w, $h) = getDimensions("$dir/$name.$type");
		$this->image->setRawDimensions($w, $h);	

		if ($this->isResized()) {
			rotate_image("$dir/$name.sized.$type", "$dir/$name.sized.$type", $direction);
			list($w, $h) = getDimensions("$dir/$name.sized.$type");
			$this->image->setDimensions($w, $h);	
		}

		/* Reset the thumbnail to the default before regenerating thumb */
		$this->image->setThumbRectangle(0, 0, 0, 0);
		$this->makeThumbnail($dir, $thumb_size);
	}

	function setPhoto($dir, $name, $tag, $thumb_size, $pathToThumb="") {
		global $gallery;

		/*
	 	 * Sanity: make sure we can handle the file first.
		 */
		if (!isMovie($tag) &&
		    !valid_image("$dir/$name.$tag")) {
			return "Invalid image: $name.$tag";
		}

		/* Set our image. */
		$this->image = new Image;
		$this->image->setFile($dir, $name, $tag);

		$ret = $this->makeThumbnail($dir, $thumb_size, $pathToThumb);
		return $ret;
	}

	function makeThumbnail($dir, $thumb_size, $pathToThumb="")
	{
		global $gallery;
		$name = $this->image->name;
		$tag = $this->image->type;

		if (isMovie($tag)) {
			/* Use a preset thumbnail */
			fs_copy($gallery->app->movieThumbnail, "$dir/$name.thumb.jpg");
			$this->thumbnail = new Image;
			$this->thumbnail->setFile($dir, "$name.thumb", "jpg");

			list($w, $h) = getDimensions("$dir/$name.thumb.jpg");
			$this->thumbnail->setDimensions($w, $h);
		} else {
			/* Make thumbnail (first crop it spec) */
			if ($pathToThumb) {
				$ret = copy ($pathToThumb,"$dir/$name.thumb.$tag");
			} else if ($this->image->thumb_width > 0)
			{
				$ret = cut_image("$dir/$name.$tag", 
								 "$dir/$name.thumb.$tag", 
								 $this->image->thumb_x, 
								 $this->image->thumb_y,
								 $this->image->thumb_width, 
								 $this->image->thumb_height);
				if ($ret) {
					$ret = resize_image("$dir/$name.thumb.$tag", 
										"$dir/$name.thumb.$tag", $thumb_size);
				}
			} else {
				$ret = resize_image("$dir/$name.$tag", "$dir/$name.thumb.$tag",
					     $thumb_size);
			}

			if ($ret) { 
				$this->thumbnail = new Image;
				$this->thumbnail->setFile($dir, "$name.thumb", $tag);
	
				list($w, $h) = getDimensions("$dir/$name.thumb.$tag");
				$this->thumbnail->setDimensions($w, $h);

				/* if this is the highlight, remake it */
				if ($this->highlight) {
					$this->setHighlight($dir, 1);
				}
			} else {
				return "Unable to make thumbnail ($ret)";
			}
		}

		return 0;
	}


	function getThumbnailTag($dir, $size=0, $attrs="") {
		if ($this->thumbnail) {
			return $this->thumbnail->getTag($dir, 0, $size, $attrs);
		} else {
			return "<i>No thumbnail</i>";
		}
	}

	function getHighlightTag($dir, $size=0, $attrs) {
		if (is_object($this->highlightImage)) {
			return $this->highlightImage->getTag($dir, 0, $size, $attrs);
		} else {
			return "<i>No highlight</i>";
		}
	}

	function getPhotoTag($dir, $full=0) {
		if ($this->image) {
			return $this->image->getTag($dir, $full);
		} else {
			return "about:blank";
		}
	}

	function getPhotoPath($dir, $full=0) {
		if ($this->image) {
			return $this->image->getPath($dir, $full);
		} else {
			return "about:blank";
		}
	}

	function getPhotoId($dir) {
		if ($this->image) {
			return $this->image->getId($dir);
		} else {
			return "unknown";
		}
	}

	function delete($dir) {
		if ($this->image) {
			$this->image->delete($dir);
		}

		if ($this->thumbnail) {
			$this->thumbnail->delete($dir);
		}
	}

	function setCaption($cap) {
		$this->caption = $cap;
	}

	function getCaption() {
		return $this->caption;
	}

	function setIsAlbumName($name) {
		$this->isAlbumName = $name;
	}

	function getIsAlbumName() {
		return $this->isAlbumName;
	}

	function isMovie() {
		return isMovie($this->image->type);
	}

	function resize($dir, $target, $pathToResized="") {
		if ($this->image) {
			$this->image->resize($dir, $target, $pathToResized);
		}
	}
}

?>
