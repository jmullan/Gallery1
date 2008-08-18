<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * $Id$
*/

// Hack Prevention
$sensitiveList = array('gallery', 'GALLERY_EMBEDDED_INSIDE', 'GALLERY_EMBEDDED_INSIDE_TYPE', 'GLOBALS');
foreach ($sensitiveList as $sensitive) {
    if (!empty($_REQUEST[$sensitive])) {
        echo "Security violation! Override attempt.\n";
        exit;
    }
}

if(function_exists('date_default_timezone_get')) {
	$defaultTimeZone = date_default_timezone_get();
	date_default_timezone_set($defaultTimeZone);
}

/*
*  Seed the randomization pool once, instead of doing it every place
*  that we use rand() or mt_rand()
*/
mt_srand((double) microtime() * 1000000);

global $gallery;
require(dirname(__FILE__) . "/Version.php");
require(dirname(__FILE__) . "/util.php");

/* Load bootstrap code */
if (getOS() == OS_WINDOWS) {
	include_once(dirname(__FILE__) . "/platform/fs_win32.php");
}
else {
	include_once(dirname(__FILE__) . "/platform/fs_unix.php");
}

/**
 * Checks whether our Gallery configuration is configured.
 * This check also loads the config.php (if present)
*/
$gallerySanity = gallerySanityCheck();

/* Make sure that Gallery is set up properly */
if ($gallerySanity != NULL) {
	header("Location: " . makeGalleryHeaderUrl('setup/index.php'));
	exit;
}

/* Here we set a default execution time limit for the entire Gallery script the value is defined
 * by the user during setup, so set if after the config.php was successfully loaded.
 * If the user increases from the default, this will cover
 * potential execution issues on slow systems, or installs with gigantic galleries.
 * By calling set_time_limit() again further in the script (in locations we know can
 * take a long time) we reset the counter to 0 so that a longer execution can occur.
*/
set_time_limit($gallery->app->timeLimit);

/*
 *  We TRY to make sure that register_globals is disabled.  If the user has not disabled
 *  register_globals in their php.ini, we try to emulate its functionality by unsetting all
 *  variables from $_REQUEST.  Some *Nuke systems apparently do not function well with this
 *  emulation, so we have given users a method to opt-out.
 *
 *  WE DO NOT OFFICIALLY SUPPORT THE USE OF skipRegisterGlobals BECAUSE IT COULD POTENTIALLY
 *  OPEN Gallery TO SECURITY RISKS!  This is for advanced users only.
*/
if (empty($gallery->app->skipRegisterGlobals) || $gallery->app->skipRegisterGlobals != "yes") {
	$register_globals = @ini_get('register_globals');
	if (!empty($register_globals) && !eregi("no|off|false", $register_globals)) {
		foreach (array_keys($_REQUEST) as $key) {
			unset($$key);
		}
	}
}

// Optional developer hook - location to add useful
// functions such as code profiling modules
if (file_exists(dirname(__FILE__) . "/lib/devel.php")) {
	require_once(dirname(__FILE__) . "/lib/devel.php");
}

/*
 * Now we can catch if were are in GeekLog
 * We also include the common lib file as we need it in initLanguage()
 *
 * If the old example path is still set, remove it.
*/
if (!empty($gallery->app->geeklog_dir) &&
	$gallery->app->geeklog_dir == "/path/to/geeklog/public_html")
{
	$gallery->app->geeklog_dir = '';
}

// Verify that the geeklog_dir isn't overwritten with a remote exploit
if (!empty($gallery->app->geeklog_dir) && !realpath($gallery->app->geeklog_dir)) {
    print gTranslate('core', "Security violation. Geeklog Dir is invalid.") ."\n";
    exit;
}
elseif (!empty($gallery->app->geeklog_dir)) {
	$GALLERY_EMBEDDED_INSIDE='GeekLog';
	$GALLERY_EMBEDDED_INSIDE_TYPE = 'GeekLog';

	if (! defined ("GEEKLOG_DIR")) {
		define ("GEEKLOG_DIR",$gallery->app->geeklog_dir);
	}

	require_once(GEEKLOG_DIR . '/lib-common.php');
}

if (isset($gallery->app->devMode) && $gallery->app->devMode == 'yes') {
	ini_set("display_errors", "1");
	error_reporting(E_ALL);
}
else {
	error_reporting(E_ALL & ~E_NOTICE);
}

/*
 * Detect if we're running under SSL and adjust the URL accordingly.
*/
if(isset($gallery->app)) {
	if (isset($_SERVER["HTTPS"] ) && stristr($_SERVER["HTTPS"], "on")) {
		$gallery->app->photoAlbumURL =
			eregi_replace("^http:", "https:", $gallery->app->photoAlbumURL);
		$gallery->app->albumDirURL =
			eregi_replace("^http:", "https:", $gallery->app->albumDirURL);
	}
	else {
		$gallery->app->photoAlbumURL =
			eregi_replace("^https:", "http:", $gallery->app->photoAlbumURL);
		$gallery->app->albumDirURL =
			eregi_replace("^https:", "http:", $gallery->app->albumDirURL);
	}

	/*
	 * We have a Coral (http://www.scs.cs.nyu.edu/coral/) request coming in, adjust outbound links
	*/
	if(isset($_SERVER['HTTP_USER_AGENT']) && strstr($_SERVER['HTTP_USER_AGENT'], 'CoralWebPrx')) {
		if (ereg("^(http://[^:]+):(\d+)(.*)$", $gallery->app->photoAlbumURL)) {
			$gallery->app->photoAlbumURL =
				ereg_replace("^(http://[^:]+):(\d+)(.*)$", "\1.\2\3", $galllery->app->photoAlbumURL);
		}

		$gallery->app->photoAlbumURL =
			ereg_replace("^(http://[^/]+)(.*)$", '\1.nyud.net:8090\2',$gallery->app->photoAlbumURL);

		if (ereg("^(http://[^:]+):(\d+)(.*)$", $gallery->app->albumDirURL)) {
			$gallery->app->albumDirURL =
				ereg_replace("^(http://[^:]+):(\d+)(.*)$", "\1.\2\3", $galllery->app->albumDirURL);
		}

		$gallery->app->albumDirURL =
			ereg_replace("^(http://[^/]+)(.*)$", '\1.nyud.net:8090\2',$gallery->app->albumDirURL);
	}
}

/*
 * Turn off magic quotes runtime as they interfere with saving and
 * restoring data from our file-based database files
 */
set_magic_quotes_runtime(0);

define('LOAD_SESSIONS', true);
if (!isset($GALLERY_NO_SESSIONS)) {
	require(dirname(__FILE__) . "/session.php");
}

// We need to init the language before we include the files below, as they contain gettext calls.
initLanguage();

/* Load classes
 * Note: Some classes and libs are loaded in util.php
 */
require(dirname(__FILE__) . "/classes/Album.php");
require(dirname(__FILE__) . "/classes/Image.php");
require(dirname(__FILE__) . "/classes/AlbumItem.php");
require(dirname(__FILE__) . "/classes/AlbumDB.php");
require(dirname(__FILE__) . "/classes/User.php");
require(dirname(__FILE__) . "/classes/EverybodyUser.php");
require(dirname(__FILE__) . "/classes/NobodyUser.php");
require(dirname(__FILE__) . "/classes/LoggedInUser.php");
require(dirname(__FILE__) . "/classes/UserDB.php");
require(dirname(__FILE__) . "/classes/Comment.php");

if (isset($GALLERY_EMBEDDED_INSIDE)) {
	/* Okay, we are embedded */
	switch($GALLERY_EMBEDDED_INSIDE_TYPE) {
		case 'postnuke':
			/* We're in embedded in Postnuke */
			include_once(dirname(__FILE__) . "/classes/Database.php");
			if (!function_exists("pnUserGetVar")) {
				/* pre 0.7.1 */
				include_once(dirname(__FILE__) . "/classes/postnuke/UserDB.php");
				include_once(dirname(__FILE__) . "/classes/postnuke/User.php");

				$gallery->database{"db"} = $GLOBALS['dbconn'];
				$gallery->database{"prefix"} = $GLOBALS['pnconfig']['prefix'] . "_";
			}
			else {
				/* 0.7.1 and beyond */
				include_once(dirname(__FILE__) . "/classes/postnuke0.7.1/UserDB.php");
				include_once(dirname(__FILE__) . "/classes/postnuke0.7.1/User.php");
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
			include_once(dirname(__FILE__) . "/classes/Database.php");
			include_once(dirname(__FILE__) . "/classes/database/mysql/Database.php");
			include_once(dirname(__FILE__) . "/classes/nuke5/UserDB.php");
			include_once(dirname(__FILE__) . "/classes/nuke5/User.php");

			$gallery->database{"nuke"} = new MySQL_Database(
				$GLOBALS['dbhost'],
				$GLOBALS['dbuname'],
				$GLOBALS['dbpass'],
				$GLOBALS['dbname']
			);

			if (isset($GLOBALS['user_prefix'])) {
				$gallery->database{"user_prefix"} = $GLOBALS['user_prefix'] . '_';
			}
			else {
				$gallery->database{"user_prefix"} = 'nuke_';
			}

			$gallery->database{"prefix"} = $GLOBALS['prefix'] . '_';

			/* PHP-Nuke changed its "users" table field names in v.6.5 */
			/* Select the appropriate field names */
			if (isset($Version_Num) && $Version_Num >= "6.5") {
				$gallery->database{'fields'} = array (
					'name'  => 'name',
					'uname' => 'username',
					'email' => 'user_email',
					'uid'   => 'user_id'
				);
			}
			else {
				$gallery->database{'fields'} = array (
					'name'  => 'name',
					'uname' => 'uname',
					'email' => 'email',
					'uid'   => 'uid'
				);
			}

			/* Load our user database (and user object) */
			$gallery->userDB = new Nuke5_UserDB;
			if ($GLOBALS['user']) {
				$gallery->session->username = $GLOBALS['user'];
			}

			if (isset($GLOBALS['admin']) && is_admin($GLOBALS['admin'])) {
				include_once(dirname(__FILE__) . "/classes/nuke5/AdminUser.php");

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
			include_once(dirname(__FILE__) . "/classes/Database.php");
			include_once(dirname(__FILE__) . "/classes/database/mysql/Database.php");
			include_once(dirname(__FILE__) . "/classes/nsnnuke/UserDB.php");
			include_once(dirname(__FILE__) . "/classes/nsnnuke/User.php");

			$gallery->database{"nsnnuke"} = new MySQL_Database(
				$GLOBALS['dbhost'],
				$GLOBALS['dbuname'],
				$GLOBALS['dbpass'],
				$GLOBALS['dbname']
			);

			if (isset($GLOBALS['user_prefix'])) {
				$gallery->database{"user_prefix"} = $GLOBALS['user_prefix'] . '_';
			}
			else {
				$gallery->database{"user_prefix"} = 'nukea_';
			}

			$gallery->database{"prefix"} = $GLOBALS['prefix'] . '_';
			$gallery->database{"admin_prefix"} = $GLOBALS['prefix'] . 'b_';

			/* Select the appropriate field names */
			$gallery->database{'fields'} = array (
				'name'  => 'realname',
				'uname' => 'username',
				'email' => 'user_email',
				'uid'   => 'user_id'
			);

			/* Load our user database (and user object) */
			$gallery->userDB = new NsnNuke_UserDB;
			if ($GLOBALS['user']) {
				$gallery->session->username = $GLOBALS['user'];
			}

			if (isset($GLOBALS['admin']) && is_admin($GLOBALS['admin'])) {
				include_once(dirname(__FILE__) . "/classes/nsnnuke/AdminUser.php");

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
		//print_r($GLOBALS['board_config']['version']);
			include_once(dirname(__FILE__) . "/classes/Database.php");
			include_once(dirname(__FILE__) . "/classes/database/mysql/Database.php");
			include_once(dirname(__FILE__) . "/classes/phpbb/UserDB.php");
			include_once(dirname(__FILE__) . "/classes/phpbb/User.php");

			if(!isset($GLOBALS['board_config']['version']) ||
			  $GLOBALS['board_config']['version'] < '.0.17' ) {
				$dbhost = $GLOBALS['dbhost'];
				$dbuser = $GLOBALS['dbuser'];
				$dbpasswd = $GLOBALS['dbpasswd'];
				$dbname = $GLOBALS['dbname'];
			}
			else {
				$dbhost = $GLOBALS['db']->server;
				$dbuser = $GLOBALS['db']->user;
				$dbpasswd = $GLOBALS['db']->password;
				$dbname = $GLOBALS['db']->dbname;
			}

			$gallery->database{"phpbb"} = new MySQL_Database($dbhost, $dbuser, $dbpasswd, $dbname);

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
		case 'joomla':
			include_once(dirname(__FILE__) . '/classes/Database.php');
			include_once(dirname(__FILE__) . '/classes/database/mysql/Database.php');
			include_once(dirname(__FILE__) . '/classes/mambo/UserDB.php');
			include_once(dirname(__FILE__) . '/classes/mambo/User.php');

			global $mosConfig_host;
			global $mosConfig_user;
			global $mosConfig_password;
			global $mosConfig_db;
			global $mosConfig_dbprefix;
			global $mosConfig_lang;
			global $my;

			/* Session info about Mambo are available when we open a Popup from Mambo,
			 * but content isnt parsed through Mambo
			 */
			if (!empty($gallery->session->mambo) && empty($mosConfig_db)) {
				$mosConfig_host		= $gallery->session->mambo->mosConfig_host;
				$mosConfig_user		= $gallery->session->mambo->mosConfig_user;
				$mosConfig_password	= $gallery->session->mambo->mosConfig_password;
				$mosConfig_db		= $gallery->session->mambo->mosConfig_db;
				$mosConfig_dbprefix	= $gallery->session->mambo->mosConfig_dbprefix;
				$mosConfig_lang		= $gallery->session->mambo->mosConfig_lang;
				$MOS_GALLERY_PARAMS	= $gallery->session->mambo->MOS_GALLERY_PARAMS;
			}
			elseif (!empty($mosConfig_db)) {
				$gallery->session->mambo->mosRoot = dirname($_SERVER['PHP_SELF']);

				if (substr($gallery->session->mambo->mosRoot, -1) != '/') {
					$gallery->session->mambo->mosRoot .= '/';
				}
				$gallery->session->mambo->mosConfig_host	= $mosConfig_host;
				$gallery->session->mambo->mosConfig_user	= $mosConfig_user;
				$gallery->session->mambo->mosConfig_password	= $mosConfig_password;
				$gallery->session->mambo->mosConfig_db		= $mosConfig_db;
				$gallery->session->mambo->mosConfig_dbprefix	= $mosConfig_dbprefix;
				$gallery->session->mambo->mosConfig_lang	= $mosConfig_lang;
				$gallery->session->mambo->MOS_GALLERY_PARAMS	= $MOS_GALLERY_PARAMS;
			}
			else {
				echo 'init.php: ' . gTranslate('core', "Gallery seems to be inside Mambo, but we couldn't get the necessary info.");
				exit;
			}

			$gallery->database{'mambo'} = new MySQL_Database(
					$mosConfig_host,
					$mosConfig_user,
					$mosConfig_password,
					$mosConfig_db
			);
			$gallery->database{'user_prefix'} = $mosConfig_dbprefix;
			$gallery->database{'fields'} = array (
				'name'  => 'name',
				'uname' => 'username',
				'email' => 'email',
				'uid'   => 'id',
				'gid'   => 'gid'
			);

			$gallery->userDB = new Mambo_UserDB;

			/* Check if user is logged in, else explicit log him/her out */
			if (!empty($my->username)) {
				$gallery->session->username = $my->username;
				$gallery->user = $gallery->userDB->getUserByUsername($gallery->session->username);

				/* We were loaded correctly through Mambo, so we dont need/want "old" session infos */
			}
			elseif (!empty($gallery->session->username) && empty($my)) {
				/* This happens, when we are in a Popup */
				$gallery->user = $gallery->userDB->getUserByUsername($gallery->session->username);
			}
			else {
				/* logout */
				unset($gallery->session->username);
				unset($gallery->session->language);
			}

			/* For proper Mambo breadcrumb functionality, we need
			 * to know the Item ID of the Gallery component's menu
			 * item. +2 DB calls. <sigh>
			*/
			$db = $gallery->database{'mambo'};
			$results = $db->query('SELECT id FROM ' . $gallery->database{'user_prefix'} . "components WHERE link='option=$GALLERY_MODULENAME'");
			$row = $db->fetch_row($results);
			$componentId = $row[0];
			$results = $db->query('SELECT id FROM ' . $gallery->database{'user_prefix'} . "menu WHERE componentid='$componentId' AND type = 'components' AND published = 1");
			$row = $db->fetch_row($results);
			$MOS_GALLERY_PARAMS['itemid'] = $row[0]; // pick the first one

		break;

		case 'GeekLog':
			// Cheat, and grab USER information from the global session variables.
			// Hey, it's faster and easier than reading them out of the database.

			global $_USER;

			/* Check if user is logged in, else explicit log him/her out */
			if (isset($_USER["username"])) {
				$gallery->session->username = $_USER['username'];
			}
			else {
				unset($gallery->session->username);
				unset($gallery->session->language);
			}

			/* Implement GeekLogUserDB and User class. */
			require(dirname(__FILE__) . "/classes/geeklog/UserDB.php");
			require(dirname(__FILE__) . "/classes/geeklog/User.php");

			/* Load GeekLog user database (and user object) */

			$gallery->userDB = new Geeklog_UserDB;

			/* Load their user object with their username as the key */
			if (isset($gallery->session->username)) {
				$gallery->user = $gallery->userDB->getUserByUsername($gallery->session->username);
			}

		break;

		case 'cpgnuke':
			/* we're in CPG-Nuke */
			include_once(dirname(__FILE__) . "/classes/Database.php");
			include_once(dirname(__FILE__) . "/classes/database/mysql/Database.php");
			include_once(dirname(__FILE__) . "/classes/nsnnuke/UserDB.php");
			include_once(dirname(__FILE__) . "/classes/nsnnuke/User.php");

			$gallery->database{"cpgnuke"} = new MySQL_Database(
				$GLOBALS['dbhost'],
				$GLOBALS['dbuname'],
				$GLOBALS['dbpass'],
				$GLOBALS['dbname']
			);

			if (isset($GLOBALS['user_prefix'])) {
				$gallery->database{"user_prefix"} = $GLOBALS['prefix'] . '_';
			}
			else {
				$gallery->database{"user_prefix"} = 'cms';
			}
			$gallery->database{"prefix"} = $GLOBALS['prefix'] . '_';
			$gallery->database{"admin_prefix"} = $GLOBALS['prefix'] . 'b_';

			/* Select the appropriate field names */
			$gallery->database{'fields'} = array (
				'name'  => 'name',
				'uname' => 'username',
				'email' => 'user_email',
				'uid'   => 'user_id'
			);

			/* Load our user database (and user object) */
			$gallery->userDB = new CPGNuke_UserDB;
			if (is_user()) {
				$gallery->session->username = $userinfo['username'];
				$gallery->user = $gallery->userDB->getUserByUsername($gallery->session->username);
			}

			if (is_admin()) {
				include_once(dirname(__FILE__) . "/classes/nsnnuke/AdminUser.php");

				$gallery->user = new CPGNuke_AdminUser();
				$gallery->session->username = $gallery->user->getUsername();
			}
		break;
	}
}
else {
	/* Standalone */
	include_once(dirname(__FILE__) . "/classes/gallery/UserDB.php");
	include_once(dirname(__FILE__) . "/classes/gallery/User.php");

	/* Load our user database (and user object) */
	$gallery->userDB = new Gallery_UserDB;

	/* Load their user object with their username as the key */
	if (isset($gallery->session->username) && !empty($gallery->session->username)) {
		$gallery->user = $gallery->userDB->getUserByUsername($gallery->session->username);
	}
}

/* If there's no specific user, they are the special Everybody user */
if (!isset($gallery->user) || empty($gallery->user)) {
	if (!$gallery->userDB->isInitialized()) {
		echo gallery_error(gTranslate('core', "Fatal error: UserDB failed to initialize!"));
	}

	$gallery->user = $gallery->userDB->getEverybody();
	$gallery->session->username = '';
}

if (!isset($gallery->session->offline)) {
	$gallery->session->offline = FALSE;
}

if ($gallery->userDB->isInitialized() && $gallery->userDB->versionOutOfDate()) {
	include_once(dirname(__FILE__) . "/upgrade_users.php");
	exit;
}

/* Load the correct album object */
if (!empty($gallery->session->albumName)) {
	$gallery->album = new Album;
	$ret = $gallery->album->load($gallery->session->albumName);
	if (!$ret) {
		$gallery->session->albumName = "";
	}
	else {
		if ($gallery->album->versionOutOfDate()) {
			include_once(dirname(__FILE__) . "/popups/upgrade_album.php");
			exit;
		}
	}
}
?>
