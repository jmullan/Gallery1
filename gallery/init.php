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
/* Load bootstrap code */
if (file_exists($GALLERY_BASEDIR . "config.php")) {
	require($GALLERY_BASEDIR . "config.php");
}
require($GALLERY_BASEDIR . "version.php");
require($GALLERY_BASEDIR . "util.php");

/* 
 * Turn off magic quotes runtime as they interfere with saving and
 * restoring data from our file-based database files
 */
set_magic_quotes_runtime(0);

/* Make sure that Gallery is set up properly */
gallerySanityCheck();

/* Load classes and session information */
require($GALLERY_BASEDIR . "classes/Album.php");
require($GALLERY_BASEDIR . "classes/Image.php");
require($GALLERY_BASEDIR . "classes/AlbumItem.php");
require($GALLERY_BASEDIR . "classes/AlbumDB.php");
require($GALLERY_BASEDIR . "classes/User.php");
require($GALLERY_BASEDIR . "classes/EverybodyUser.php");
require($GALLERY_BASEDIR . "classes/NobodyUser.php");
require($GALLERY_BASEDIR . "classes/UserDB.php");
require($GALLERY_BASEDIR . "session.php");

/* Load our user database (and user object) */
$gallery->userDB = new UserDB;

/* Load their user object with their username as the key */
if ($gallery->session->username) {
	$gallery->user = 
		$gallery->userDB->getUserByUsername($gallery->session->username);
}

/* If there's no specific user, they are the special Everybody user */
if (!$gallery->user) {
	$gallery->user = $gallery->userDB->getEverybody();
	$gallery->session->username = "";
}

/* Load the correct album object */
$gallery->album = new Album;
if ($gallery->session->albumName) {
	$gallery->album->load($gallery->session->albumName);
	if ($gallery->album->integrityCheck()) {
		$gallery->album->save();
	}
}
?>
