<?
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
<?
// Hack prevention.
$sensitiveList = array("gallery", "GALLERY_BASEDIR");
foreach ($sensitiveList as $sensitive) {
	if (!empty($HTTP_GET_VARS[$sensitive]) ||
			!empty($HTTP_POST_VARS[$sensitive]) ||
			!empty($HTTP_COOKIE_VARS[$sensitive]) ||
			!empty($HTTP_POST_FILES[$sensitive])) {
		print "Security violation\n";
		exit;
	}
}
?>
<?
/*
 * Turn down the error reporting to just critical errors for now.
 * In v1.2, we know that we'll have lots and lots of warnings if
 * error reporting is turned all the way up.  We'll fix this in v2.0
 */
error_reporting(E_ALL & ~E_NOTICE);

/* Load bootstrap code */
if (substr(PHP_OS, 0, 3) == 'WIN') {
	include($GALLERY_BASEDIR . "platform/fs_win32.php");
} else {
	include($GALLERY_BASEDIR . "platform/fs_unix.php");
}


if (fs_file_exists($GALLERY_BASEDIR . "config.php")) {
        global $gallery;
	include($GALLERY_BASEDIR . "config.php");
}
require($GALLERY_BASEDIR . "Version.php");
require($GALLERY_BASEDIR . "util.php");

/*
 * Detect if we're running under SSL and adjust the URL accordingly.
 */
if (stristr($HTTP_SERVER_VARS["HTTPS"], "on")) {
	$gallery->app->photoAlbumURL = 
		eregi_replace("^http:", "https:", $gallery->app->photoAlbumURL);
	$gallery->app->albumDirURL = 
		eregi_replace("^http:", "https:", $gallery->app->albumDirURL);
} else {
	$gallery->app->photoAlbumURL = 
		eregi_replace("^https:", "http:", $gallery->app->photoAlbumURL);
	$gallery->app->albumDirURL = 
		eregi_replace("^https:", "http:", $gallery->app->albumDirURL);
}

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
require($GALLERY_BASEDIR . "classes/LoggedInUser.php");
require($GALLERY_BASEDIR . "classes/UserDB.php");
require($GALLERY_BASEDIR . "classes/Comment.php");

if (!$GALLERY_NO_SESSIONS) {
    require($GALLERY_BASEDIR . "session.php");
}

if (!strcmp($GALLERY_EMBEDDED_INSIDE, "nuke")) {
        include($GALLERY_BASEDIR . "classes/Database.php");

	/* Check for PostNuke 0.7's new methodology */
	if (isset($GLOBALS['pnconfig']) && function_exists("authorised")) {
	    include($GALLERY_BASEDIR . "classes/postnuke/UserDB.php");
	    include($GALLERY_BASEDIR . "classes/postnuke/User.php");

	    $gallery->database{"db"} = $GLOBALS['dbconn'];
	    $gallery->database{"prefix"} = $GLOBALS['pnconfig']['prefix'] . "_";

	    /* Load our user database (and user object) */
	    $gallery->userDB = new PostNuke_UserDB;

	    if (isset($GLOBALS['user'])) {
		$gallery->session->username = $GLOBALS['user']; 
	    }
	    
	    if (isset($GLOBALS['user']) && is_user($GLOBALS['user'])) {
		$user_info = getusrinfo($GLOBALS['user']);
		$gallery->session->username = $user_info["uname"]; 
		$gallery->user = 
		    $gallery->userDB->getUserByUsername($gallery->session->username);
	    }
	} else {
	    include($GALLERY_BASEDIR . "classes/database/mysql/Database.php");
	    include($GALLERY_BASEDIR . "classes/nuke5/UserDB.php");
	    include($GALLERY_BASEDIR . "classes/nuke5/User.php");

	    $gallery->database{"nuke"} = new MySQL_Database(
			$GLOBALS['dbhost'],
			$GLOBALS['dbuname'],
			$GLOBALS['dbpass'],
			$GLOBALS['dbname']);
	    $gallery->database{"nuke"}->setTablePrefix($GLOBALS['prefix'] . "_");

	    /* Load our user database (and user object) */
	    $gallery->userDB = new Nuke5_UserDB;
	    if ($GLOBALS['user']) {
		$gallery->session->username = $GLOBALS['user']; 
	    }
	    
	    if (isset($GLOBALS['admin']) && is_admin($GLOBALS['admin'])) {
		include($GALLERY_BASEDIR . "classes/nuke5/AdminUser.php");
		
		$gallery->user = new Nuke5_AdminUser($GLOBALS['admin']);
		$gallery->session->username = $gallery->user->getUsername();
	    } else if (is_user($GLOBALS['user'])) {
		$user_info = getusrinfo($GLOBALS['user']);
		$gallery->session->username = $user_info["uname"]; 
		$gallery->user = 
			 $gallery->userDB->getUserByUsername($gallery->session->username);
	    }
	}
} else {
	include($GALLERY_BASEDIR . "classes/gallery/UserDB.php");
	include($GALLERY_BASEDIR . "classes/gallery/User.php");

	/* Load our user database (and user object) */
	$gallery->userDB = new Gallery_UserDB;

	/* Load their user object with their username as the key */
	if ($gallery->session->username) {
		$gallery->user = 
			$gallery->userDB->getUserByUsername($gallery->session->username);
	}
}

/* If there's no specific user, they are the special Everybody user */
if (!$gallery->user) {
	$gallery->user = $gallery->userDB->getEverybody();
	$gallery->session->username = "";
}

/* Load the correct album object */
if ($gallery->session->albumName) {
	$gallery->album = new Album;
	$ret = $gallery->album->load($gallery->session->albumName);
	if (!$ret) {
		$gallery->session->albumName = "";
	} else {
		if ($gallery->album->versionOutOfDate()) {
			include($GALLERY_BASEDIR . "upgrade_album.php");
			exit;
		}
	}
}
?>
