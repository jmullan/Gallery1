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
			$ret = resize_image("$dir/$name.$tag",
					     "$dir/$name.highlight.jpg",
					     $app->highlight_size);

			if ($ret) {
				list($w, $h) = getDimensions("$dir/$name.highlight.jpg");

				$high = new Image;
				$high->setFile($dir, "$name.highlight", "jpg");
				$high->setDimensions($w, $h);
				$this->highlightImage = $high;
			}
		}
		else {
			if (file_exists("$dir/$name.highlight.jpg")) {
				unlink("$dir/$name.highlight.jpg");
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

	function makeThumbnail($dir, $size) {
		$name = $this->image->name;
		$type = $this->image->type;

		$this->setPhoto($dir, $name, $type, $size);
	}

	function isResized() {
		$im = $this->image;
		return $im->isResized();
	}

	function rotate($dir, $direction, $thumb_size) {
		global $app;

		$name = $this->image->name;
		$type = $this->image->type;

		/* GD doesn't do rotation!?!? */
		$cmd = getAnytopnmCmd("$dir/$name.$type", 
			"| $app->pnmDir/pnmrotate $direction".
			"| $app->pnmDir/ppmtojpeg > $dir/tmp.jpg");

		exec_wrapper($cmd);
		
		if (file_exists("$dir/tmp.jpg") && filesize("$dir/tmp.jpg") > 0) {
			copy("$dir/tmp.jpg", "$dir/$name.jpg");
			unlink("$dir/tmp.jpg");
		}

		/* The file is now a jpeg.  If it wasn't a jpeg before then
		 * clean up the old file 
		 */
		if (strcmp($type, "jpg")) {
			$this->delete($dir);
		}

		$isResized = $this->isResized();

		if ($isResized) {
			list($w, $h) = $this->image->getDimensions();			
		}

		/* And rebuild the thumbnail */
		$this->setPhoto($dir, $name, $type, $thumb_size);

		if ($isResized) {
			$this->image->resize($dir, max($h, $w));
		}
	}

	function setPhoto($dir, $name, $tag, $thumb_size) {
		global $app;

		/*
	 	 * Sanity: make sure we can handle the file first.
		 */
		if (!valid_image("$dir/$name.$tag") &&
		    !strcmp($tag, "avi") &&
		    !strcmp($tag, "mpg")) {
			return "Invalid image: $name.$tag";
		}

		/*
		 * If the image is a GIF, convert it to a JPEG.  This is because
		 * GD no longer has support for GIFs and we want to use GD to make
		 * the thumbnail.
		 */
		if (!strcmp($tag, "gif")) {
			exec_wrapper("$app->pnmDir/giftopnm $dir/$name.gif | ".
				     "$app->pnmDir/ppmtojpeg > $dir/$name.jpg");
			$tag = "jpg";
			unlink("$dir/$name.gif");
		} 

		/* Set our image.  It might not load if this is a movie. */
		list($w, $h) = getDimensions("$dir/$name.$tag");
		$this->image = new Image;
		$this->image->setFile($dir, $name, $tag);

		if ($w != 0 && $h != 0) {
			$this->image->setDimensions($w, $h);
		}

		if (!strcmp($tag, "avi") || !strcmp($tag, "mpg")) {
			/* Use a preset thumbnail */
			copy($app->movieThumbnail, "$dir/$name.thumb.jpg");
			$this->thumbnail = new Image;
			$this->thumbnail->setFile($dir, "$name.thumb", "jpg");

			list($w, $h) = getDimensions("$dir/$name.thumb.jpg");
			$this->thumbnail->setDimensions($w, $h);
		} else {
			/* Make thumbnail */
			$ret = resize_image("$dir/$name.$tag",
					     "$dir/$name.thumb.jpg",
					     $thumb_size);

			if ($ret) { 
				$this->thumbnail = new Image;
				$this->thumbnail->setFile($dir, "$name.thumb", "jpg");
	
				list($w, $h) = getDimensions("$dir/$name.thumb.jpg");
				$this->thumbnail->setDimensions($w, $h);
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
