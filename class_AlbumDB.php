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

		$name = "album01";
		$albumDir = $app->albumDir;
		while (file_exists("$albumDir/$name")) {
			$name++;
		}
		return $name;
	}

	function numAlbums() {
		return sizeof($this->albumList);
	}

	function getAlbum($index) {
		return $this->albumList[$index-1];
	}

	function moveAlbum($index, $newIndex) {
		/* Pull album out */
		$name = array_splice($this->albumOrder, $index-1, 1);
		array_splice($this->albumOrder, $newIndex-1, 0, $name);
	}

	function save() {
		global $app;

		$dir = $app->albumDir;
		if ($fd = fopen("$dir/albumdb.dat.new", "w")) {
			fwrite($fd, serialize($this->albumOrder));
			fclose($fd);
			system("$app->mv $dir/albumdb.dat.new $dir/albumdb.dat");
		}
	}
}

?>
