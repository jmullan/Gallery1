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
class Album {
	var $fields;
	var $photos;
	var $dir;

	function Album() {
		global $app;

		$this->fields["title"] = "Untitled";
		$this->fields["description"] = "No description";
		$this->fields["nextname"] = "photo-aaa";
		$this->fields["font"] = $app->default["font"];
		$this->fields["border"] = $app->default["border"];
		$this->fields["bordercolor"] = $app->default["bordercolor"];
		$this->fields["returnto"] = $app->default["returnto"];
	}

	function shufflePhotos() {
		shuffle($this->photos);
	}

	function getThumbDimensions($index) {
		$photo = $this->getPhoto($index);
		return $photo->getThumbDimensions();
	}

	function getHighlight() {
		for ($i = 1; $i <= $this->numPhotos(1); $i++) {
			$photo = $this->getPhoto($i);
			if ($photo->isHighlight()) {
				return $i;
			}
		}
		return 1;
	}

	function setHighlight($index) {
		for ($i = 1; $i <= $this->numPhotos(1); $i++) {
			$photo = $this->getPhoto($i);
			$photo->setHighlight($i == $index);
			$this->setPhoto($photo, $i);
		}
	}

	function load($name) {
		global $app;

		$dir = "$app->albumDir/$name";
		
		$tmp = getFile("$dir/album.dat");
		if ($tmp) {
			$this = unserialize($tmp);
			$this->fields["name"] = $name;
		}
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

	function save() {
		$dir = $this->getAlbumDir();

		if (!file_exists($dir)) {
			mkdir($dir, 0777);
		}

		if ($fd = fopen("$dir/album.dat.new", "w")) {
			fwrite($fd, serialize($this));
			fclose($fd);
			system("mv $dir/album.dat.new $dir/album.dat");
		}
	}

	function delete() {
		$dir = $this->getAlbumDir();

		/* Delete all pictures */
		while ($this->numPhotos(1)) {
			$this->deletePhoto(0);
		}

		/* Delete data file */
		if (file_exists("$dir/album.dat")) {
			unlink("$dir/album.dat");
		}

		/* Delete album dir */
		rmdir($dir);
	}

	function resizePhoto($index, $target) {
		$photo = $this->getPhoto($index);
		$photo->resize($this->getAlbumDir(), $target);
		$this->setPhoto($photo, $index);
	}

	function resizeAllPhotos($target) {
		for ($i = 1; $i <= $this->numPhotos(1); $i++) {
			set_time_limit(30);
			if (!$this->isMovie($i)) {
				$this->resizePhoto($i, $target);
			}
		}
	}

	function addPhoto($file, $tag) {
		$dir = $this->getAlbumDir();
		$name = $this->newPhotoName();

		/* Get the file */
		copy($file, "$dir/$name.$tag");
		
		/* Add the photo to the photo list */
		$item = new AlbumItem();
		$item->setPhoto($dir, $name, $tag);
		$this->photos[] = $item;
	}

	function hidePhoto($index) {
		$photo = $this->getPhoto($index);
		$photo->hide();
		$this->setPhoto($photo, $index);
	}
	
	function unhidePhoto($index) {
		$photo = $this->getPhoto($index);
		$photo->unhide();
		$this->setPhoto($photo, $index);
	}

	function isHidden($index) {
		$photo = $this->getPhoto($index);
		return $photo->isHidden();
	}

	function deletePhoto($index) {
		$photo = array_splice($this->photos, $index-1, 1);
		$photo[0]->delete($this->getAlbumDir());
	}

	function newPhotoName() {
		return $this->fields["nextname"]++;
	}

	function getThumbnailTag($index, $attrs="") {
		$photo = $this->getPhoto($index);
		return $photo->getThumbnailTag($this->getAlbumDirURL(), $attrs);
	}

	function getPhotoTag($index, $full) {
		$photo = $this->getPhoto($index);
		if ($photo->isMovie()) {
			return $photo->getThumbnailTag($this->getAlbumDirURL());
		} else {
			return $photo->getPhotoTag($this->getAlbumDirURL(), $full);
		}
	}

	function getPhotoPath($index) {
		$photo = $this->getPhoto($index);
		return $photo->getPhotoPath($this->getAlbumDirURL());
	}

	function getAlbumDir() {
		global $app;

		return "$app->albumDir/{$this->fields[name]}";
	}

	function getAlbumDirURL() {
		global $app;

		return "$app->albumDirURL/{$this->fields[name]}";
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

	function getPhoto($index) {
		return $this->photos[$index-1];
	}

	function setPhoto($photo, $index) {
		$this->photos[$index-1] = $photo;		
	}

	function getCaption($index) {
		$photo = $this->getPhoto($index);
		return $photo->getCaption();
	}

	function setCaption($index, $caption) {
		$photo = $this->getPhoto($index);
		$photo->setCaption($caption);
		$this->setPhoto($photo, $index);
	}

	function rotatePhoto($index, $direction) {
		$photo = $this->getPhoto($index);
		$photo->rotate($this->getAlbumDir(), $direction);
		$this->setPhoto($photo, $index);
	}

	function makeThumbnail($index) {
		$photo = $this->getPhoto($index);
		$photo->makeThumbnail($this->getAlbumDir());
		$this->setPhoto($photo, $index);
	}

	function movePhoto($index, $newIndex) {
		/* Pull photo out */
		$photo = array_splice($this->photos, $index-1, 1);
		array_splice($this->photos, $newIndex, 0, $photo);
	}

	function isMovie($index) {
		$photo = $this->getPhoto($index);
		return $photo->isMovie();
	}
}

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
		if (!strcmp($target, "orig")) {
			$img = loadImage($dir, $this->name, $this->type);
			$this->width = imagesx($img);
			$this->height = imagesy($img);
			if (file_exists("$dir/$this->resizedName.jpg")) {
				unlink("$dir/$this->resizedName.jpg");
			}
			$this->resizedName = "";
		} else {
			$img = loadImage($dir, $this->name, $this->type);
			if ($img) {
				/* Calc the new size h/w keeping the aspect ratio */
				$orig_width = imagesx($img);
				$orig_height = imagesy($img);
	
				$aspect = $orig_width / $orig_height;
				if ($aspect > 1) {
					$new_width = $target;
					$new_height = ceil($new_width / $aspect);
				} else {
					$new_height = $target;
					$new_width = ceil($new_height * $aspect);
				}
		
				$this->resizedName = "$this->name.sized";
				$newImg = ImageCreate($new_width, $new_height);
				ImageCopyResized($newImg, $img, 0, 0, 0, 0, 
						 $new_width, $new_height, 
						 $orig_width, $orig_height);
				ImageJpeg($newImg, "$dir/$this->resizedName.jpg");
	
				$this->height = $new_height;
				$this->width = $new_width;
			}
		}	
	}

	function delete($dir) {
		if (file_exists("$dir/$this->resizedName.$this->type")) {
			unlink("$dir/$this->resizedName.$this->type");
		}
		unlink("$dir/$this->name.$this->type");
	}

	function getTag($dir, $full=0, $attrs="") {
		global $app;

		$name = $this->getName($dir);
		
		if (!strcmp($app->default["imageborders"], "no")) {
			$attrs .= " border=0";
		}
		
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

	function getPath($dir) {
		$name = $this->getName($dir);
		return "$dir/$name.$this->type";
	}
	
	function getName($dir) {
		if (file_exists("$dir/$this->resizedName.$this->type")) {
			return $this->resizedName;
		} else {
			return $this->name;
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

class AlbumItem {
	var $image;
	var $thumbnail;
	var $caption;
	var $hidden;
	var $highlight;

	function hide() {
		$this->hidden = 1;
	}

	function unhide() {
		$this->hidden = 0;
	}

	function isHidden() {
		return $this->hidden;
	}

	function setHighlight($bool) {
		$this->highlight = $bool;
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

	function makeThumbnail($dir) {
		$name = $this->image->name;
		$type = $this->image->type;

		$this->setPhoto($dir, $name, $type);
	}

	function isResized() {
		if ($this->image->resizedName) {
			return 1;
		} else {
			return 0;
		}
	}

	function rotate($dir, $direction) {
		global $app;

		$name = $this->image->name;
		$type = $this->image->type;

		/* GD doesn't do rotation!?!? */
		exec("$app->pnmDir/anytopnm $dir/$name.$type | " .
			"$app->pnmDir/pnmrotate $direction | ".
			"$app->pnmDir/ppmtojpeg > $dir/tmp.jpg");
		
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

		/* And rebuild the thumbnail */
		$this->setPhoto($dir, $name, $type);
	}

	function setPhoto($dir, $name, $tag) {
		global $app;

		/*
		 * If the image is a GIF, convert it to a JPEG.  This is because
		 * GD no longer has support for GIFs and we want to use GD to make
		 * the thumbnail.
		 */
		if (!strcmp($tag, "gif")) {
			exec("$app->pnmDir/giftopnm $dir/$name.gif | ".
			     "$app->pnmDir/ppmtojpeg > $dir/$name.jpg");
			$tag = "jpg";
			unlink("$dir/$name.gif");
		} 

		/* Set our image */
		$this->image = new Image;
		$this->image->setFile($dir, $name, $tag);

		if (!strcmp($tag, "avi") || !strcmp($tag, "mpg")) {
			/* Use a preset thumbnail */
			copy($app->movieThumbnail, "$dir/$name.thumb.jpg");
			$this->thumbnail = new Image;
			$this->thumbnail->setFile($dir, "$name.thumb", "jpg");

			$img = loadImage($dir, "$name.thumb", "jpg");
			$this->thumbnail->setDimensions(imagesx($img), imagesy($img));
		} else {
			/* Make thumbnail */
			$img = loadImage($dir, $name, $tag);

			if ($img) {
				/* Calc the thumbnail h/w keeping the aspect ratio */
				$orig_width = imagesx($img);
				$orig_height = imagesy($img);

				$target = $app->thumb_size;
				$aspect = $orig_width / $orig_height;
				if ($aspect > 1) {
					$new_width = $target;
					$new_height = ceil($new_width / $aspect);
				} else {
					$new_height = $target;
					$new_width = ceil($new_height * $aspect);
				}
	
				$thumbImg = ImageCreate($new_width, $new_height);
				ImageCopyResized($thumbImg, $img, 0, 0, 0, 0, 
						 $new_width, $new_height, 
						 $orig_width, $orig_height);
				ImageJpeg($thumbImg, "$dir/$name.thumb.jpg");
	
				$this->thumbnail = new Image;
				$this->thumbnail->setFile($dir, "$name.thumb", "jpg");
	
				$this->image->setDimensions($orig_width, $orig_height);
				$this->thumbnail->setDimensions($new_width, $new_height);
			}
		}
	}

	function getThumbnailTag($dir, $attrs) {
		if ($this->thumbnail) {
			return $this->thumbnail->getTag($dir, 0, $attrs);
		} else {
			return "<i>No thumbnail</i>";
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
		$this->image->resize($dir, $target);
	}
}

class AlbumDB {
	var $albumList;
	var $albumOrder;

	function AlbumDB() {
		global $app;

		$dir = $app->albumDir;

		$tmp = getFile("$dir/albumdb.dat");
		if (strcmp($tmp, "")) {
			$this->albumOrder = unserialize($tmp);
		} else {
			$this->albumOrder = array();
		}

		$this->albumList = array();
		$i = 0;
		while ($i < sizeof($this->albumOrder)) {
			$name = $this->albumOrder[$i];
			if (is_dir("$dir/$name")) {
				$album = new Album;
				$album->load($name);
				array_push($this->albumList, $album);
				$i++;
			} else {
				/* Couldn't find the album -- delete it from order */
				array_splice($this->albumOrder, $i, 1);
			}
		}

		if ($fd = opendir($dir)) {
			while ($file = readdir($fd)) {
				if (!ereg("^\.", $file) && 
				    is_dir("$dir/$file") &&
				    !in_array($file, $this->albumOrder)) {
					$album = new Album;
					$album->load($file);
					array_push($this->albumList, $album);
					array_push($this->albumOrder, $file);
				}
			}
			closedir($fd);
		}

		$this->save();
	}

	function renameAlbum($oldName, $newName) {
		global $app;

		$dir = $app->albumDir;
		if (is_dir("$dir/$oldName")) {
			rename("$dir/$oldName", "$dir/$newName");
		}

		for ($i = 0; $i < sizeof($this->albumOrder); $i++) {
			if (!strcmp($this->albumOrder[$i], $oldName)) {
				$this->albumOrder[$i] = $newName;
			}
		}
	}

	function newAlbumName() {
		global $app;

		$index = "album1";
		$albumDir = $app->albumDir;
		while (file_exists("$albumDir/$index")) {
			$index++;
		}
		return $index;
	}

	function numAlbums() {
		return sizeof($this->albumList);
	}

	function getAlbum($index) {
		return $this->albumList[$index];
	}

	function moveAlbum($index, $newIndex) {
		/* Pull album out */
		$name = array_splice($this->albumOrder, $index, 1);
		array_splice($this->albumOrder, $newIndex, 0, $name);
	}

	function save() {
		global $app;

		$dir = $app->albumDir;
		if ($fd = fopen("$dir/albumdb.dat.new", "w")) {
			fwrite($fd, serialize($this->albumOrder));
			fclose($fd);
			system("mv $dir/albumdb.dat.new $dir/albumdb.dat");
		}
	}
}

?>
