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
		$this->fields["nextname"] = "aaa";
        	$this->fields["bgcolor"] = "";
        	$this->fields["textcolor"] = "";
        	$this->fields["linkcolor"] = "";
		$this->fields["font"] = $app->default["font"];
		$this->fields["border"] = $app->default["border"];
		$this->fields["bordercolor"] = $app->default["bordercolor"];
		$this->fields["returnto"] = $app->default["returnto"];
		$this->fields["thumb_size"] = $app->default["thumb_size"];
		$this->fields["resize_size"] = $app->default["resize_size"];
	}

	function integrityCheck() {
		global $app;

		$changed = 0;
		$check = array("thumb_size", "resize_size");
		foreach ($check as $field) {
			if (!$this->fields[$field]) {
				$this->fields[$field] = $app->default[$field];
				$changed = 1;
			}
		}
		return $changed;
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
			$photo->setHighlight($this->getAlbumDir(), $i == $index);
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
		global $app;
		$dir = $this->getAlbumDir();

		$this->fields["last_mod_time"] = time();

		if (!file_exists($dir)) {
			mkdir($dir, 0777);
		}

		if ($fd = fopen("$dir/album.dat.new", "w")) {
			fwrite($fd, serialize($this));
			fclose($fd);
			rename("$dir/album.dat.new", "$dir/album.dat");
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
		if (!$photo->isMovie()) {
			$photo->resize($this->getAlbumDir(), $target);
			$this->setPhoto($photo, $index);
		}
	}

	function addPhoto($file, $tag) {
		$dir = $this->getAlbumDir();
		$name = $this->newPhotoName();

		/* Get the file */
		copy($file, "$dir/$name.$tag");
		
		/* Add the photo to the photo list */
		$item = new AlbumItem();
		$err = $item->setPhoto($dir, $name, $tag, $this->fields["thumb_size"]);
		if ($err) {
			unlink("$dir/$name.$tag");
			return $err;
		}
		$this->photos[] = $item;

		/* If this is the only photo, make it the highlight */
		if ($this->numPhotos(1) == 1) {
			$this->setHighlight(1);
		}

		return 0;
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
		                
                /* are we deleteing the highlight? pick a new one */
		$needToRehighlight = 0;
		if ($photo[0]->isHighlight() && ($this->numPhotos(1) > 0)) {
			$needToRehighlight = 1;
		}
		$photo[0]->delete($this->getAlbumDir());
		if ($needToRehighlight) {
			$this->setHighlight(1);
		}
	}

	function newPhotoName() {
		return $this->fields["nextname"]++;
	}

	function getThumbnailTag($index, $attrs="") {
		$photo = $this->getPhoto($index);
		return $photo->getThumbnailTag($this->getAlbumDirURL(), $attrs);
	}

	function getHighlightTag($attrs="") {
		$photo = $this->getPhoto($this->getHighlight());
		return $photo->getHighlightTag($this->getAlbumDirURL(), $attrs);
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

	function getPhotoId($index) {
		$photo = $this->getPhoto($index);
		return $photo->getPhotoId($this->getAlbumDirURL());
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
		$photo->rotate($this->getAlbumDir(), $direction, $this->fields["thumb_size"]);
		$this->setPhoto($photo, $index);
	}

	function makeThumbnail($index) {
		$photo = $this->getPhoto($index);
		$photo->makeThumbnail($this->getAlbumDir(), $this->fields["thumb_size"]);
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

	function getLastModificationDate() {
		global $app;
		$dir = $this->getAlbumDir();

		$time = $this->fields["last_mod_time"];

		// Older albums may not have this field.
		if (!$time) {
			$stat = stat("$dir/album.dat");
			$time = $stat[9];
		}

		return date("M d, Y", $time);
	}
}

?>
