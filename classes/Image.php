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
class Image {
	var $name;
	var $type;
	var $width;
	var $height;
	var $resizedName;

	function setFile($dir, $name, $type) {
		$this->name = $name;
		$this->type = $type;
	}

	function resize($dir, $target) {
		global $app;

		/* getting rid of the resized image */
		if (stristr($target, "orig")) {
			list($w, $h) = getDimensions("$dir/$this->name.$this->type");
			$this->width = $w;
			$this->height = $h;
			if (file_exists("$dir/$this->resizedName.$this->type")) {
				unlink("$dir/$this->resizedName.$this->type");
			}
			$this->resizedName = "";
		/* doing a resize */
		} else {
			$name = $this->name;
			$type = $this->type;

			$ret = resize_image("$dir/$name.$type",
					     "$dir/$name.sized.$this->type",
					     $target);
			
			#-- resized image is always a jpeg ---
			if ($ret) {
				$this->resizedName = "$name.sized";
				list($w, $h) = getDimensions("$dir/$name.sized.$this->type");
				$this->height = $w;
				$this->width = $h;
			}
		}	
	}

	function delete($dir) {
		if (file_exists("$dir/$this->resizedName.$this->type")) {
			unlink("$dir/$this->resizedName.$this->type");
		}
		if (file_exists("$dir/$this->name.highlight.$this->type")) {
			unlink("$dir/$this->name.highlight.$this->type");
		}
		unlink("$dir/$this->name.$this->type");
	}

	function getTag($dir, $full=0, $attrs="") {
		global $app;

		$name = $this->getName($dir);
		
		$attrs .= " border=0";
		
		if ($this->resizedName) {
			if ($full) {
				return "<img src=$dir/$this->name.$this->type $attrs>";
			} else {
				return "<img src=$dir/$this->resizedName.$this->type $attrs>";
			}
		} else {
			return "<img src=$dir/$this->name.$this->type width=$this->width height=$this->height $attrs>";
		}
	}

	function getName($dir) {
		if (file_exists("$dir/$this->resizedName.$this->type")) {
			return $this->resizedName;
		} else {
			return $this->name;
		}
	}

	function getId($dir) {
		return $this->name;
	}
	
	function getPath($dir) {
		$name = $this->getName($dir);
		return "$dir/$name.$this->type";
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

	function getDimensions() {
		return array($this->width, $this->height);
	}
}	

?>
