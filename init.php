<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2003 Bharat Mediratta
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
 *
 * $Id$
 */
?>
<?php
// Hack prevention.
$sensitiveList = array("gallery", "GALLERY_BASEDIR");
foreach ($sensitiveList as $sensitive) {
	if (!empty($HTTP_GET_VARS[$sensitive]) ||
			!empty($HTTP_POST_VARS[$sensitive]) ||
			!empty($HTTP_COOKIE_VARS[$sensitive]) ||
			!empty($HTTP_POST_FILES[$sensitive])) {
		print _("Security violation") ."\n";
		exit;
	}
}
?>
<?php
/*
 * Turn down the error reporting to just critical errors for now.
 * In v1.2, we know that we'll have lots and lots of warnings if
 * error reporting is turned all the way up.  We'll fix this in v2.0
 */
error_reporting(E_ALL & ~E_NOTICE);

/*
 * Figure out if register_globals is on or off and save that info
 * for later
 */
$register_globals = ini_get("register_globals");
if (empty($register_globals) ||
        !strcasecmp($register_globals, "off") ||
        !strcasecmp($register_globals, "false")) {
    $gallery->register_globals = 0;
} else {
    $gallery->register_globals = 1;
}

/*
 * If register_globals is off, then extract all HTTP variables into the global
 * namespace.  
 */
if (!$gallery->register_globals) {
    if (is_array($HTTP_GET_VARS)) {
	extract($HTTP_GET_VARS);
    }

    if (is_array($HTTP_POST_VARS)) {
	extract($HTTP_POST_VARS);
    }

    if (is_array($HTTP_COOKIE_VARS)) {
	extract($HTTP_COOKIE_VARS);
    }

    if (is_array($HTTP_POST_FILES)) {
	foreach($HTTP_POST_FILES as $key => $value) {
	    ${$key."_name"} = $value["name"];
	    ${$key."_size"} = $value["size"];
	    ${$key."_type"} = $value["type"];
	    ${$key} = $value["tmp_name"];
	}
    }
}

require($GALLERY_BASEDIR . "Version.php");
require($GALLERY_BASEDIR . "util.php");
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

/*
 * Detect if we're running under SSL and adjust the URL accordingly.
 */
if(isset($gallery->app)) {
	if (isset($HTTP_SERVER_VARS["HTTPS"] ) && stristr($HTTP_SERVER_VARS["HTTPS"], "on")) {
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
}

/* 
 * Turn off magic quotes runtime as they interfere with saving and
 * restoring data from our file-based database files
 */
set_magic_quotes_runtime(0);


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

if (!isset($GALLERY_NO_SESSIONS)) {
    require($GALLERY_BASEDIR . "session.php");
}

initLanguage();

/* Make sure that Gallery is set up properly */
gallerySanityCheck();

if (isset($GALLERY_EMBEDDED_INSIDE) &&
    !strcmp($GALLERY_EMBEDDED_INSIDE, "nuke")) {
        include($GALLERY_BASEDIR . "classes/Database.php");

	/* Check for PostNuke */
	if (isset($GLOBALS['pnconfig']) && function_exists("authorised")) {

	    if (!function_exists("pnUserGetVar")) {
		/* pre 0.7.1 */
		include($GALLERY_BASEDIR . "classes/postnuke/UserDB.php");
		include($GALLERY_BASEDIR . "classes/postnuke/User.php");
		
		$gallery->database{"db"} = $GLOBALS['dbconn'];
		$gallery->database{"prefix"} = $GLOBALS['pnconfig']['prefix'] . "_";
	    } else {
		/* 0.7.1 and beyond */
		include($GALLERY_BASEDIR . "classes/postnuke0.7.1/UserDB.php");
		include($GALLERY_BASEDIR . "classes/postnuke0.7.1/User.php");
	    }

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
	    if (isset($GLOBALS['user_prefix'])) {
		$gallery->database{"user_prefix"} = $GLOBALS['user_prefix'] . '_';
	    } else {
		$gallery->database{"user_prefix"} = $GLOBALS['prefix'] . '_';
	    }
	    $gallery->database{"prefix"} = $GLOBALS['prefix'] . '_';

            /* PHP-Nuke changed its "users" table field names in v.6.5 */
	    /* Select the appropriate field names */
	    if (isset($Version_Num) && $Version_Num >= 6.5) {
		$gallery->database{'fields'} =
			array ('name'  => 'name',
			       'uname' => 'username',
			       'email' => 'user_email',
			       'uid'   => 'user_id');
	    }
	    else {
		$gallery->database{'fields'} =
			array ('name'  => 'name',
			       'uname' => 'uname',
			       'email' => 'email',
			       'uid'   => 'uid');
	    }
	    
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
		$gallery->session->username =
			$user_info[$gallery->database{'fields'}{'uname'}]; 
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
if (!isset($gallery->user)) {
	$gallery->user = $gallery->userDB->getEverybody();
	$gallery->session->username = "";
}

if (!isset($gallery->session->offline)) {
    $gallery->session->offline = FALSE;
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
