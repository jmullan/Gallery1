<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2002 Bharat Mediratta
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
<?php
// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
	print "Security violation\n";
	exit;
}
?>
<?php require($GALLERY_BASEDIR . "init.php"); ?>
<?php
if (!strcmp($cmd, "remake-thumbnail")) {
	if ($gallery->user->canWriteToAlbum($gallery->album)) {
?>
<html>
<head>
  <title>Rebuilding Thumbnails</title>
  <?php echo getStyleSheetLink() ?>
</head>
<body>
<?php
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
						set_time_limit($gallery->app->timeLimit);
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
				set_time_limit($gallery->app->timeLimit);
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
	if ($gallery->user->canCreateAlbums() ||
	    $gallery->user->canCreateSubAlbum($gallery->album)) {
		$albumDB = new AlbumDB(FALSE);
		$gallery->session->albumName = $albumDB->newAlbumName();
		$gallery->album = new Album();
		$gallery->album->fields["name"] = $gallery->session->albumName;
		$gallery->album->setOwner($gallery->user->getUid());
		$gallery->album->save();
		/* if this is a nested album, set nested parameters */
		if ($parentName) {
			$gallery->album->fields[parentAlbumName] = $parentName;
			$parentAlbum = $albumDB->getAlbumbyName($parentName);
			$parentAlbum->addNestedAlbum($gallery->session->albumName);
			$parentAlbum->save();
			// Set default values in nested album to match settings of parent.
			$gallery->album->fields["perms"] 	= $parentAlbum->fields["perms"];
			$gallery->album->fields["bgcolor"] 	= $parentAlbum->fields["bgcolor"];
			$gallery->album->fields["textcolor"] 	= $parentAlbum->fields["textcolor"];
			$gallery->album->fields["linkcolor"]	= $parentAlbum->fields["linkcolor"];
			$gallery->album->fields["font"]		= $parentAlbum->fields["font"];
			$gallery->album->fields["border"]	= $parentAlbum->fields["border"];
			$gallery->album->fields["bordercolor"]	= $parentAlbum->fields["bordercolor"];
			$gallery->album->fields["returnto"]	= $parentAlbum->fields["returnto"];
			$gallery->album->fields["thumb_size"]	= $parentAlbum->fields["thumb_size"];
			$gallery->album->fields["resize_size"]	= $parentAlbum->fields["resize_size"];
			$gallery->album->fields["rows"]		= $parentAlbum->fields["rows"];
			$gallery->album->fields["cols"]		= $parentAlbum->fields["cols"];
			$gallery->album->fields["fit_to_window"]= $parentAlbum->fields["fit_to_window"];
			$gallery->album->fields["use_fullOnly"]	= $parentAlbum->fields["use_fullOnly"];
			$gallery->album->fields["print_photos"]	= $parentAlbum->fields["print_photos"];
			$gallery->album->fields["use_exif"]	= $parentAlbum->fields["use_exif"];
			$gallery->album->fields["display_clicks"]=$parentAlbum->fields["display_clicks"];
			$gallery->album->fields["public_comments"]=$parentAlbum->fields["public_comments"];

			$gallery->album->save();
		} else {
			/* move the album to the top if not a nested album*/
                	$numAlbums = $albumDB->numAlbums($gallery->user);
                	$albumDB->moveAlbum($gallery->user, $numAlbums, 1);
                	$albumDB->save();
		}
	
		$url = addUrlArg($return, "set_albumName=" .
				 $gallery->session->albumName);
		header("Location: $url");
	} else {
	        header("Location: " . makeAlbumUrl());
	}
} else if (!strcmp($cmd, "reset-album-clicks")) {
	if ($gallery->user->canWriteToAlbum($gallery->album)) {
		$gallery->album->resetAllClicks();
		// this is a popup do dismiss and reload!
		dismissAndReload();
	} else {
	        header("Location: " . makeAlbumUrl());
	}

} else if (!strcmp($cmd, "delete-comment")) {
	if ($gallery->user->canWriteToAlbum($gallery->album)) {
		$gallery->album->deleteComment($index, $comment_index);
		$gallery->album->save();
		dismissAndReload();
	} else {
	        header("Location: " . makeAlbumUrl());
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
</body>
</html>
