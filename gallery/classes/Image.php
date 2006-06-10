<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2006 Bharat Mediratta
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
class Image {
	var $name;
	var $type;
	var $width;
	var $height;
	var $resizedName;
	var $thumb_x;
	var $thumb_y;
	var $thumb_width;
	var $thumb_height;
	var $raw_width;
	var $raw_height;
	var $version;

	function Image() {
		global $gallery;

		// Seed new images with the appropriate version.
		$this->version = $gallery->album_version;
	}

	function setFile($dir, $name, $type) {
		$this->name = $name;
		$this->type = $type;

		if (!isMovie($this->type)) {
			list($w, $h) = getDimensions("$dir/$this->name.$this->type");
			$this->raw_width = $w;
			$this->raw_height = $h;
			$this->width = $w;
			$this->height = $h;
		}
	}

	function integrityCheck($dir) {
		global $gallery;

		if (!strcmp($this->version, $gallery->album_version)) {
			return 0;
		}

		$changed = 0;

		/*
		 * Fix a specific bug where the width/height are reversed
		 * for sized images 
		 */
		if ($this->version < 3) {
			if ($this->resizedName) {
				list($w, $h) = getDimensions("$dir/$this->resizedName.$this->type");
				$this->width = $w;
				$this->height = $h;
				$changed = 1;
			}
		}

		$filename = "$dir/$this->name.$this->type";
		if (!isMovie($this->type)) {
			if (!$this->raw_width) {
				list($w, $h) = getDimensions($filename);
				$this->raw_width = $w;
				$this->raw_height = $h;
				$changed = 1;
			}
		}

		/* We introduced raw_filesize in 1.28 of this file, then got rid of it later. */
		unset($this->raw_filesize);

		if (strcmp($this->version, $gallery->album_version)) {
			$this->version = $gallery->album_version;
			$changed = 1;
		}

		return $changed;
	}

	function resize($dir, $target, $filesize, $pathToResized) {
		global $gallery;

		/* getting rid of the resized image */
		if (stristr($target, "orig")) {
			list($w, $h) = getDimensions("$dir/$this->name.$this->type");
			$this->width = $w;
			$this->height = $h;
			if (fs_file_exists("$dir/$this->resizedName.$this->type")) {
				fs_unlink("$dir/$this->resizedName.$this->type");
			}
			$this->resizedName = '';
		/* doing a resize */
		} else {
			$name = $this->name;
			$type = $this->type;
			
			if ($pathToResized) {
				$ret = copy($pathToResized,"$dir/$name.sized.$this->type");	
			} else {
				$ret = resize_image("$dir/$name.$type", "$dir/$name.sized.$this->type", $target, $filesize);
			}
			
			#-- resized image is not always a jpeg ---
			if ($ret == 1) {
				$this->resizedName = "$name.sized";
				list($w, $h) = getDimensions("$dir/$name.sized.$this->type");
				$this->width = $w;
				$this->height = $h;
			}
			elseif ($ret == 2) {
				$this->resize($dir, "orig", 0, $pathToResized);
			}
		}	
	}

	function delete($dir) {
		clearstatcache();
		if (fs_file_exists("$dir/$this->resizedName.$this->type")) {
			fs_unlink("$dir/$this->resizedName.$this->type");
		}
		if (fs_file_exists("$dir/$this->name.highlight.$this->type")) {
			fs_unlink("$dir/$this->name.highlight.$this->type");
		}
		fs_unlink("$dir/$this->name.$this->type");
	}

	function simpleDelete($dir) {
		if (fs_file_exists("$dir/$this->name.$this->type")) {
			fs_unlink("$dir/$this->name.$this->type");
		}
	}

	function getTag($dir, $full = false, $size = 0, $attrs = array()) {
		global $gallery;

		/* Prevent non-integer data */
		$size = (int)$size;

		$name = $this->getName($dir);
		$attrs['alt'] = $attrs['title'] = htmlspecialchars(strip_tags(trim($attrs['alt'])));

		if ($size) {
			if ($this->width > $this->height) {
				$width = $size;
				$height = round($size * ($this->height / $this->width));
			} else {
				$width = round($size * ($this->width / $this->height));
				$height = $size;
			}
			$attrs['width']		= $width;
			$attrs['height']	= $height;
		} else if ($full || !$this->resizedName) {
			$attrs['width']		= $this->raw_width;
			$attrs['height']	= $this->raw_height;
		} else {
			$attrs['width']		= $this->width;
			$attrs['height']	= $this->height;
		}

		$fullImage = urlencode($this->name) .".$this->type";
		$resizedImage = urlencode($this->resizedName) .".$this->type";

		if ($this->resizedName && $size == 0) {
			$attrs['width']		= $this->raw_width;
			$attrs['height']	= $this->raw_height;
			if ($full) {
				$attrs['src']	= "$dir/$fullImage";
			} else {
				$attrs['src']	= "$dir/$resizedImage";
			}
		} else {
			$attrs['src']		= "$dir/$fullImage";
		}

		$tag = '<img'. generateAttrs($attrs). '>';
		return $tag;
	}

	function getName($dir, $full = false) {
		if ((!$full) && (fs_file_exists("$dir/$this->resizedName.$this->type"))) {
			return $this->resizedName;
		} else {
			return $this->name;
		}
	}

	function getId() {
		return $this->name;
	}
	
	function getPath($dir, $full=0) {
		if ($full || !$this->resizedName) {
		    $name = $this->name;
		} else {
		    $name = $this->resizedName;
		}
		return "$dir/$name.$this->type";
	}

	function getImageName($full = false) {
		if ($full || !$this->resizedName) {
		    $name = $this->name;
		} else {
		    $name = $this->resizedName;
		}
		return "$name.$this->type";
	}

	function isResized() {
		if ($this->resizedName) {
			return 1;
		} else {
			return 0;
		}
	}

	function setDimensions($w, $h) {
		$this->width = $w;
		$this->height = $h;
	}

	function setRawDimensions($w, $h) {
		$this->raw_width = $w;
		$this->raw_height = $h;
	}

	function getDimensions($size=0, $full=false) {
	    if ($size) {
                if ($this->width > $this->height) {
                    $width = $size;
                    $height = round($size * ($this->height / $this->width));
                } else {
                    $width = round($size * ($this->width / $this->height));
                    $height = $size;
                }
            } else if ($full) {
		$width = $this->raw_width;
		$height = $this->raw_height;
	    }
	    else {
		$width = $this->width;
		$height = $this->height;
            }
	        
		return array($width, $height);
	}

	function setThumbRectangle($x, $y, $w, $h) {
		$this->thumb_x = $x;
		$this->thumb_y = $y;
		$this->thumb_width = $w;
		$this->thumb_height = $h;
	}

	function getThumbRectangle() {
		return array($this->thumb_x, $this->thumb_y,
		             $this->thumb_width, $this->thumb_height);
	}

	function getRawDimensions() {
		return array($this->raw_width, $this->raw_height);
	}

	function rawFileSize($dir) {
	    $filename = "$dir/$this->name.$this->type";
	    return fs_filesize($filename);
	}
}	

?>
