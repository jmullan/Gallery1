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
require('version.php');
if (file_exists('config.php')) {
	require('config.php');
}
require('util.php');

/* 
 * Turn off magic quotes runtime as they interfere with saving and
 * restoring data from our file-based database files
 */
set_magic_quotes_runtime(0);

/* Make sure that Gallery is set up properly */
gallerySanityCheck();

/* Load classes and session information */
require('classes/Album.php');
require('classes/Image.php');
require('classes/AlbumItem.php');
require('classes/AlbumDB.php');
require('classes/User.php');
require('classes/EverybodyUser.php');
require('classes/NobodyUser.php');
require('classes/UserDB.php');
require('session.php');

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
