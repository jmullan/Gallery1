<?
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000 Bharat Mediratta
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
		global $app;
		
		$this->highlight = $bool;
		
		/*
		 * if it is now the highlight make sure it has a highlight
                 * thumb otherwise get rid of it's thumb (ouch!).
		 */
		$name = $this->image->name;
		$tag = $this->image->type;

		if ($this->highlight) {
			if ($this->image->thumb_width > 0) {
				// Crop it first
				$ret = cut_image("$dir/$name.$tag", 
						 "$dir/$name.tmp.$tag", 
						 $this->image->thumb_x, 
						 $this->image->thumb_y,
						 $this->image->thumb_width, 
						 $this->image->thumb_height);

				// Then resize it down
				if ($ret) {
					$ret = resize_image("$dir/$name.tmp.$tag", 
							    "$dir/$name.highlight.$tag",
							    $app->highlight_size);
				}
				unlink("$dir/$name.tmp.$tag");
			} else {
				$ret = resize_image("$dir/$name.$tag", 
						    "$dir/$name.highlight.$tag",
						    $app->highlight_size);
			}

			if ($ret) {
				list($w, $h) = getDimensions("$dir/$name.highlight.$tag");

				$high = new Image;
				$high->setFile($dir, "$name.highlight", "$tag");
				$high->setDimensions($w, $h);
				$this->highlightImage = $high;
			}
		}
		else {
			if (file_exists("$dir/$name.highlight.$tag")) {
				unlink("$dir/$name.highlight.$tag");
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
		global $app;

		$name = $this->image->name;
		$type = $this->image->type;
	 	rotate_image("$dir/$name.$type", "$dir/$name.$type", $direction);

		if ($this->isResized()) {
			list($w, $h) = $this->image->getDimensions();			
			rotate_image("$dir/$name.sized.$type", "$dir/$name.sized.$type", $direction);
		}

		/* Reset the thumbnail to the default before regenerating thumb */
		$this->image->setThumbRectangle(0, 0, 0, 0);
		$this->makeThumbnail($dir, $thumb_size);
	}

	function setPhoto($dir, $name, $tag, $thumb_size) {
		global $app;

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

		$ret = $this->makeThumbnail($dir, $thumb_size);
		return $ret;
	}

	function makeThumbnail($dir, $thumb_size)
	{
		global $app;
		$name = $this->image->name;
		$tag = $this->image->type;

		if (!strcmp($tag, "avi") || !strcmp($tag, "mpg")) {
			/* Use a preset thumbnail */
			copy($app->movieThumbnail, "$dir/$name.thumb.jpg");
			$this->thumbnail = new Image;
			$this->thumbnail->setFile($dir, "$name.thumb", "jpg");

			list($w, $h) = getDimensions("$dir/$name.thumb.jpg");
			$this->thumbnail->setDimensions($w, $h);
		} else {
			list($w, $h) = getDimensions("$dir/$name.$tag");
			if ($w != 0 && $h != 0) {
				$this->image->setDimensions($w, $h);
			}

			/* Make thumbnail (first crop it spec) */
			if ($this->image->thumb_width > 0)
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


	function getThumbnailTag($dir, $attrs="") {
		if ($this->thumbnail) {
			return $this->thumbnail->getTag($dir, 0, $attrs);
		} else {
			return "<i>No thumbnail</i>";
		}
	}

	function getHighlightTag($dir, $attrs) {
		if (is_object($this->highlightImage)) {
			return $this->highlightImage->getTag($dir, 0, $attrs);
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

	function getPhotoPath($dir) {
		if ($this->image) {
			return $this->image->getPath($dir);
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

	function isMovie() {
		return isMovie($this->image->type);
	}

	function resize($dir, $target) {
		if ($this->image) {
			$this->image->resize($dir, $target);
		}
	}
}

?>
