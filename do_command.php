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
<? require($GALLERY_BASEDIR . "init.php"); ?>
<?
if (!strcmp($cmd, "remake-thumbnail")) {
	if ($gallery->user->canWriteToAlbum($gallery->album)) {
?>
<html>
<head>
  <title>Rebuilding Thumbnails</title>
  <?= getStyleSheetLink() ?>
</head>
<body>
<?
		if ($gallery->session->albumName && isset($index)) {
			if (!strcmp($index, "all")) {
				$np = $gallery->album->numPhotos(1);
				echo ("<br> Rebuilding $np thumbnails...");
				my_flush();
				for ($i = 1; $i <= $np; $i++) {
					$isAlbumName = $gallery->album->isAlbumName($i);
					if (!$isAlbumName) { // process the images
						echo("<br> Processing image $i...");
						my_flush();
						set_time_limit(90);
						$gallery->album->makeThumbnail($i);
					} else { 
						// we just skip albums... we could have
						// recursively created new thumbnails in each
						// album that we ran across, but skipping them
						// might be preferred.
						echo("<br> Skipping album $i...");
						my_flush();
					}
				}
			} else {
				echo ("<br> Rebuilding 1 thumbnail...");
				my_flush();
				set_time_limit(90);
				$gallery->album->makeThumbnail($index);
			}
			$gallery->album->save();
			//-- this is expected to be loaded in a popup, so dismiss ---
			dismissAndReload();
		}
	}
} else if (!strcmp($cmd, "logout")) {
	$gallery->session->username = "";
	header("Location: $return");
} else if (!strcmp($cmd, "hide")) {
	if ($gallery->user->canWriteToAlbum($gallery->album)) {
		$gallery->album->hidePhoto($index);
		$gallery->album->save();
	}
	//-- this is expected to be loaded in a popup, so dismiss ---
	dismissAndReload();
} else if (!strcmp($cmd, "show")) {
	if ($gallery->user->canWriteToAlbum($gallery->album)) {
		$gallery->album->unhidePhoto($index);
		$gallery->album->save();
	}
	//-- this is expected to be loaded in a popup, so dismiss ---
	dismissAndReload();
} else if (!strcmp($cmd, "new-album")) {
	if ($gallery->user->canCreateAlbums()) {
		$albumDB = new AlbumDB();
		$gallery->session->albumName = $albumDB->newAlbumName();
		$gallery->album = new Album();
		$gallery->album->fields["name"] = $gallery->session->albumName;
		$gallery->album->setOwner($gallery->user->getUid());
		$gallery->album->save();
		/* if this is a nested album, set nested parameters */
		if ($parentName) {
			$gallery->album->fields[parentAlbumName] = $parentName;
			$myAlbum = $albumDB->getAlbumbyName($parentName);
			$myAlbum->addNestedAlbum($gallery->session->albumName);
			$myAlbum->save();
			$gallery->album->fields["perms"] = $myAlbum->fields["perms"];
			$gallery->album->save();
		} else {
			/* move the album to the top if not a nested album*/
			$albumDB = new AlbumDB();
                	$numAlbums = $albumDB->numAlbums($gallery->user);
                	$albumDB->moveAlbum($gallery->user, $numAlbums, 1);
                	$albumDB->save();
		}
	
		$url = addUrlArg($return, "set_albumName=" .
				 $gallery->session->albumName);
		header("Location: $url");
	} else {
		header("Location: albums.php");
	}
} else if (!strcmp($cmd, "reset-album-clicks")) {
	if ($gallery->user->canWriteToAlbum($gallery->album)) {
		$albumDB = new AlbumDB();
		$myAlbum=$albumDB->getAlbumByName($albumName);
		$myAlbum->resetAllClicks();
		// this is a popup do dismiss and reload!
		dismissAndReload();
	} else {
		header("Location: albums.php");
	}

} else if (!empty($return)) {
	// No command; Can be used to set a session variable
	header("Location: $return");
}
?>

<center>
<form>
<input type=submit value="Dismiss" onclick='parent.close()'>
</form>
