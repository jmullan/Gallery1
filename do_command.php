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
<? require('init.php'); ?>
<?
if (!strcmp($cmd, "remake-thumbnail")) {
	if ($user->canWriteToAlbum($album)) {
?>
<html>
<head>
  <title>Rebuilding Thumbnails</title>
  <link rel="stylesheet" type="text/css" href="<?= getGalleryStyleSheetName() ?>">
</head>
<body>
<?
		if ($albumName && isset($index)) {
			if (!strcmp($index, "all")) {
				$np = $album->numPhotos();
				echo ("<br> Rebuilding $np thumbnails...");
				my_flush();
				for ($i = 1; $i <= $np; $i++) {
					echo("<br> Processing image $i...");
					my_flush();
					set_time_limit(90);
					$album->makeThumbnail($i);
				}
			} else {
				echo ("<br> Rebuilding 1 thumbnail...");
				my_flush();
				set_time_limit(90);
				$album->makeThumbnail($index);
			}
			$album->save();
			dismissAndReload();
		}
	}
} else if (!strcmp($cmd, "logout")) {
	$username = "";
	header("Location: $return");	
} else if (!strcmp($cmd, "hide")) {
	if ($user->canWriteToAlbum($album)) {
		$album->hidePhoto($index);
		$album->save();
	}
	header("Location: $return");	
} else if (!strcmp($cmd, "show")) {
	if ($user->canWriteToAlbum($album)) {
		$album->unhidePhoto($index);
		$album->save();
	}
	header("Location: $return");	
} else if (!strcmp($cmd, "new-album")) {
	if ($user->canCreateAlbums()) {
		$albumDB = new AlbumDB();
		$albumName = $albumDB->newAlbumName();
		$album = new Album();
		$album->fields["name"] = $albumName;
		$album->setOwner($user->getUid());
		$album->save();
	
	        /* move the album to the top */ 
		$albumDB = new AlbumDB();
	        $numAlbums = $albumDB->numAlbums($user);
	        $albumDB->moveAlbum($user, $numAlbums, 1);
	        $albumDB->save();

		header("Location: $return?set_albumName=$albumName");
	} else {
		header("Location: albums.php");
	}
}
?>

<center>
<form>
<input type=submit value="Dismiss" onclick='parent.close()'>
</form>
