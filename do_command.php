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
if (!strcmp($cmd, "remake-thumbnail")) {
	if ($albumName && isset($index)) {
		$album->makeThumbnail($index);
		$album->save();
		dismissAndReload();
	}
} else if (!strcmp($cmd, "leave-edit")) {
	$edit = "";
	header("Location: $return");	
} else if (!strcmp($cmd, "hide")) {
	$album->hidePhoto($index);
	$album->save();
	header("Location: $return");	
} else if (!strcmp($cmd, "show")) {
	$album->unhidePhoto($index);
	$album->save();
	header("Location: $return");	
} else if (!strcmp($cmd, "new-album")) {
	$albumDB = new AlbumDB();
	$albumName = $albumDB->newAlbumName();
	$album = new Album();
	$album->fields["name"] = $albumName;
	$album->save();

        /* move the album to the top */ 
	$albumDB = new AlbumDB();
        $numAlbums = $albumDB->numAlbums();
        $albumDB->moveAlbum($numAlbums-1, 0);
        $albumDB->save();

	header("Location: $return?set_albumName=$albumName");
}
?>
