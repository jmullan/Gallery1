<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2004 Bharat Mediratta
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

    /*
     * Prevent hackers from overwriting one HTTP_ global using another one.  For example,
     * appending "?HTTP_POST_VARS[GALLERY_BASEDIR]=xxx" to the url would cause extract
     * to overwrite HTTP_POST_VARS when it extracts HTTP_GET_VARS
     */
    $scrubList = array('HTTP_GET_VARS', 'HTTP_POST_VARS', 'HTTP_COOKIE_VARS', 'HTTP_POST_FILES');
    foreach ($scrubList as $outer) {
	foreach ($scrubList as $inner) {
	    unset(${$outer}[$inner]);
	}
    }
    
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
global $gallery;
require($GALLERY_BASEDIR . "Version.php");
require($GALLERY_BASEDIR . "util.php");

/* Load bootstrap code */
if (getOS() == OS_WINDOWS) {
	include($GALLERY_BASEDIR . "platform/fs_win32.php");
} else {
	include($GALLERY_BASEDIR . "platform/fs_unix.php");
}

if (fs_file_exists($GALLERY_BASEDIR . "config.php")) {
	include($GALLERY_BASEDIR . "config.php");
}

/* 
** Now we can catch if were are in GeekLog
*/

if (isset($gallery->app->embedded_inside_type) && $gallery->app->embedded_inside_type=='GeekLog') {
	$GALLERY_EMBEDDED_INSIDE='GeekLog';
	$GALLERY_EMBEDDED_INSIDE_TYPE = 'GeekLog';
}

if (isset($gallery->app->devMode) && 
		$gallery->app->devMode == "yes") {
       	ini_set("display_errors", "1");
       	error_reporting(E_ALL);
} else {
       	error_reporting(E_ALL & ~E_NOTICE);
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
$gallerySanity = gallerySanityCheck();
initLanguage();

/* Make sure that Gallery is set up properly */
if ($gallerySanity != NULL) {
	include ("${GALLERY_BASEDIR}errors/$gallerySanity");
	exit;
}

if (isset($GALLERY_EMBEDDED_INSIDE)) {
	/* Okay, we are embedded */
	switch($GALLERY_EMBEDDED_INSIDE_TYPE) {
		case 'postnuke':
			/* We're in embedded in Postnuke */
			include($GALLERY_BASEDIR . "classes/Database.php");
			if (!function_exists("pnUserGetVar")) {
				/* pre 0.7.1 */
				include($GALLERY_BASEDIR . "classes/postnuke/UserDB.php");
				include($GALLERY_BASEDIR . "classes/postnuke/User.php");
		
				$gallery->database{"db"} = $GLOBALS['dbconn'];
				$gallery->database{"prefix"} = $GLOBALS['pnconfig']['prefix'] . "_";
			} 
			else {
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
				$gallery->user = $gallery->userDB->getUserByUsername($gallery->session->username);
			}
		break;
		case 'phpnuke':
			/* we're in phpnuke */
			include($GALLERY_BASEDIR . "classes/Database.php");
			include($GALLERY_BASEDIR . "classes/database/mysql/Database.php");
			include($GALLERY_BASEDIR . "classes/nuke5/UserDB.php");
			include($GALLERY_BASEDIR . "classes/nuke5/User.php");

	   		 $gallery->database{"nuke"} = new MySQL_Database(
				$GLOBALS['dbhost'],
				$GLOBALS['dbuname'],
				$GLOBALS['dbpass'],
				$GLOBALS['dbname']);
	    
			if (isset($GLOBALS['user_prefix'])) {
				$gallery->database{"user_prefix"} = 'nuke_';
			}
			else {
				$gallery->database{"user_prefix"} = 'nuke_';
			}
			$gallery->database{"prefix"} = $GLOBALS['prefix'] . '_';

			/* PHP-Nuke changed its "users" table field names in v.6.5 */
			/* Select the appropriate field names */
			if (isset($Version_Num) && $Version_Num >= "6.5") {
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
	    		} 
			else if (is_user($GLOBALS['user'])) {
				$user_info = getusrinfo($GLOBALS['user']);
				$gallery->session->username = $user_info[$gallery->database{'fields'}{'uname'}]; 
				$gallery->user = $gallery->userDB->getUserByUsername($gallery->session->username);
			}
		break;
		case 'nsnnuke':
			/* we're in nsnnuke */
			include($GALLERY_BASEDIR . "classes/Database.php");
			include($GALLERY_BASEDIR . "classes/database/mysql/Database.php");
			include($GALLERY_BASEDIR . "classes/nsnnuke/UserDB.php");
			include($GALLERY_BASEDIR . "classes/nsnnuke/User.php");

	   		 $gallery->database{"nsnnuke"} = new MySQL_Database(
				$GLOBALS['dbhost'],
				$GLOBALS['dbuname'],
				$GLOBALS['dbpass'],
				$GLOBALS['dbname']);
	    
			if (isset($GLOBALS['user_prefix'])) {
				$gallery->database{"user_prefix"} = 'nukea_';
			}
			else {
				$gallery->database{"user_prefix"} = 'nukea_';
			}
			$gallery->database{"prefix"} = $GLOBALS['prefix'] . '_';
			$gallery->database{"admin_prefix"} = $GLOBALS['prefix'] . 'b_';

			/* Select the appropriate field names */
				$gallery->database{'fields'} =
					array ('name'  => 'realname',
			       			'uname' => 'username',
						'email' => 'user_email',
			       			'uid'   => 'user_id');
	    
	   		/* Load our user database (and user object) */
			$gallery->userDB = new NsnNuke_UserDB;
	    		if ($GLOBALS['user']) {
				$gallery->session->username = $GLOBALS['user']; 
			}
	    
			if (isset($GLOBALS['admin']) && is_admin($GLOBALS['admin'])) {
				include($GALLERY_BASEDIR . "classes/nsnnuke/AdminUser.php");
				
				$gallery->user = new NsnNuke_AdminUser($GLOBALS['admin']);
				$gallery->session->username = $gallery->user->getUsername();
	    		} 
			else if (is_user($GLOBALS['user'])) {
				$user_info = getusrinfo($GLOBALS['user']);
				$gallery->session->username = $user_info[$gallery->database{'fields'}{'uname'}]; 
				$gallery->user = $gallery->userDB->getUserByUsername($gallery->session->username);
			}
		break;
		case 'phpBB2':
			include($GALLERY_BASEDIR . "classes/Database.php");
			include($GALLERY_BASEDIR . "classes/database/mysql/Database.php");
			include($GALLERY_BASEDIR . "classes/phpbb/UserDB.php");
			include($GALLERY_BASEDIR . "classes/phpbb/User.php");
 			$gallery->database{"phpbb"} = new MySQL_Database(			
							$GLOBALS['dbhost'],			
							$GLOBALS['dbuser'],			
							$GLOBALS['dbpasswd'],			
							$GLOBALS['dbname']);
			//		$gallery->database{"phpbb"}->setTablePrefix($GLOBALS['table_prefix']);		
			$gallery->database{"prefix"} = $GLOBALS['table_prefix']; 		
			/* Load our user database (and user object) */		
			$gallery->userDB = new phpbb_UserDB;		
			if (isset($GLOBALS['userdata']) && isset($GLOBALS['userdata']['username'])) {
				$gallery->session->username = $GLOBALS['userdata']['username'];
				$gallery->user = $gallery->userDB->getUserByUsername($gallery->session->username);
			}
			elseif ($gallery->session->username) {
				$gallery->user = $gallery->userDB->getUserByUsername($gallery->session->username);		
			}
		break;
		case 'mambo':
			include($GALLERY_BASEDIR . 'classes/Database.php');
			include($GALLERY_BASEDIR . 'classes/database/mysql/Database.php');
			include($GALLERY_BASEDIR . 'classes/mambo/UserDB.php');
			include($GALLERY_BASEDIR . 'classes/mambo/User.php');

			global $mosConfig_host;
			global $mosConfig_user;
			global $mosConfig_password;
			global $mosConfig_db;
			global $mosConfig_dbprefix;
			global $my;

			$gallery->database{'mambo'} = new MySQL_Database($mosConfig_host, $mosConfig_user, $mosConfig_password, $mosConfig_db);
			$gallery->database{'user_prefix'} = $mosConfig_dbprefix;
			$gallery->database{'fields'} =
			array ('name'  => 'name',
			       'uname' => 'username',
			       'email' => 'email',
			       'uid'   => 'id',
			       'gid'   => 'gid');

			$gallery->userDB = new Mambo_UserDB;
			if (isset($my->username) && !empty($my->username)) {
				$gallery->session->username = $my->username;
				$gallery->user = $gallery->userDB->getUserByUsername($gallery->session->username);
			}

			/* For proper Mambo breadcrumb functionality, we need
			 * to know the Item ID of the Gallery component's menu
			 * item. +2 DB calls. <sigh> */
			$db = $gallery->database{'mambo'};
			$results = $db->query('SELECT id FROM ' . $gallery->database{'user_prefix'} . "components WHERE link='option=$GALLERY_MODULENAME'");
			$row = $db->fetch_row($results);
			$componentId = $row[0];
			$results = $db->query('SELECT id FROM ' . $gallery->database{'user_prefix'} . "menu WHERE componentid='$componentId'");
			$row = $db->fetch_row($results);
			$MOS_GALLERY_PARAMS['itemid'] = $row[0]; // pick the first one
		break;
		case 'GeekLog':
			// Cheat, and grab USER information from the global session variables.
			// Hey, it's faster and easier than reading them out of the database.

			// Verify that the geeklog_dir isn't overwritten with a remote exploit
		        if (!realpath($gallery->app->geeklog_dir)) {
				print _("Security violation") ."\n";
				exit;
			}

			require_once($gallery->app->geeklog_dir . '/lib-common.php');
			global $_USER;

			if (isset($_USER["username"])) {
				$gallery->session->username = $_USER['username'];
			} else if (!empty($gallery->session->username)) {
				$gallery->session->username = "";
			}

			/* Implement GeekLogUserDB and User class. */
			include($GALLERY_BASEDIR . "classes/geeklog/UserDB.php");
			include($GALLERY_BASEDIR . "classes/geeklog/User.php");

			/* Load GeekLog user database (and user object) */
			
			$gallery->userDB = new Geeklog_UserDB;

			/* Load their user object with their username as the key */
			if ($gallery->session->username) {
				$gallery->user = $gallery->userDB->getUserByUsername($gallery->session->username);
			}
		break;
	}
} 
else {
	/* Standalone */
	include($GALLERY_BASEDIR . "classes/gallery/UserDB.php");
	include($GALLERY_BASEDIR . "classes/gallery/User.php");

	/* Load our user database (and user object) */
	$gallery->userDB = new Gallery_UserDB;

	/* Load their user object with their username as the key */
	if (isset($gallery->session->username) && !empty($gallery->session->username)) {
		$gallery->user = $gallery->userDB->getUserByUsername($gallery->session->username);
	}
}

/* If there's no specific user, they are the special Everybody user */
if (!isset($gallery->user) || empty($gallery->user)) {
	$gallery->user = $gallery->userDB->getEverybody();
	$gallery->session->username = "";
}

if (!isset($gallery->session->offline)) {
    $gallery->session->offline = FALSE;
}

if ($gallery->userDB->versionOutOfDate()) 
{
	include($GALLERY_BASEDIR . "upgrade_users.php");
	exit;
}

/* Load the correct album object */
if (!empty($gallery->session->albumName)) {
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
