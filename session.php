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

if(!defined('LOAD_SESSIONS')) {
	echo "Invalid Request. Hard Exit.";
	exit;
}

/*
 * PHP 4.0.1pl2 introduces a bug where you can't unserialize a
 * stdClass instance correctly.  So create a dummy class to hold all
 * of our session data.
 */
class GallerySession {}

/*
 * Turn on cookie support, if possible.  Don't complain on errors, in case
 * safe mode has disabled this call.
 */
@ini_set('session.use_cookies', 1);

/* If using PHP < 4.3.2, create our own session_regenerate_id() function */
if (!function_exists('session_regenerate_id')) {
	function make_seed() {
		list($usec, $sec) = explode(' ', microtime());
		return (float)$sec + ((float)$usec * 100000);
	}

	function php_combined_lcg() {
		mt_srand(make_seed());
		$tv = gettimeofday();
		$lcg['s1'] = $tv['sec'] ^ (~$tv['usec']);
		$lcg['s2'] = mt_rand();
		$q = (int) ($lcg['s1'] / 53668);
		$lcg['s1'] = (int) (40014 * ($lcg['s1'] - 53668 * $q) - 12211 * $q);
		if ($lcg['s1'] < 0) {
			$lcg['s1'] += 2147483563;
		}
		$q = (int) ($lcg['s2'] / 52774);
		$lcg['s2'] = (int) (40692 * ($lcg['s2'] - 52774 * $q) - 3791 * $q);
		if ($lcg['s2'] < 0) {
			$lcg['s2'] += 2147483399;
		}
		$z = (int) ($lcg['s1'] - $lcg['s2']);
		if ($z < 1) {
			$z += 2147483562;
		}
		return $z * 4.656613e-10;
	}

	function session_regenerate_id() {
		$tv = gettimeofday();
		$buf = sprintf("%.15s%ld%ld%0.8f", $_SERVER['REMOTE_ADDR'], $tv['sec'], $tv['usec'], php_combined_lcg() * 10);
		session_id(md5($buf));
		if (ini_get('session.use_cookies')) {
			setcookie(session_name(), session_id(), NULL, '/');
		}
		return TRUE;
	}
}

function destroyGallerySession() {
	// Unset all of the session variables.
	$_SESSION = array();

	// If it's desired to kill the session, also delete the session cookie.
	// Note: This will destroy the session, and not just the session data!
	if (isset($_COOKIE[session_name()])) {
		setcookie(session_name(), '', time() - 42000, '/');
	}

	// Finally, destroy the session.
	session_destroy();
}

function createGallerySession($newSession = false) {
	global $gallery;

	if (!session_id()) {
		/*
		 * If no session has been created, set our session cookie name
		 */
		session_name('GallerySession');
		$useStdClass = false;
	}
	else {
		/*
		 * The session is being created externally.  This means that if
		 * we store our data in a GallerySession class, we won't be able
		 * to deserialize it (because it's not being defined before the
		 * external session starts).  So, we'll have to fall back on using
		 * stdClass() which will cause problems on older PHP4 servers.
		 * oh well.
		 */
		$useStdClass = true;
	}

	/* Start a new session, or resume our current one */
	$curCookieParams = session_get_cookie_params();
	session_set_cookie_params(
		$curCookieParams['lifetime'],
		$curCookieParams['path'],
		$curCookieParams['domain'],
		isHttpsConnection()
	);

	@session_start();

	// If we're requesting a new session, generate a new session id
	if ($newSession) {
		session_regenerate_id();
	}

	/*
	 * Are we resuming an existing session?  Determine this by checking
	 * to see if the session container variable is already set.  If not, then
	 * create the appropriate container for it.
	 */

	if (empty($gallery->app->sessionVar)) {
		$gSessionVar = "gallery_session_" . md5(getcwd());
	} else {
		$gSessionVar = $gallery->app->sessionVar . "_" . md5($gallery->app->userDir);
	}

	if (isset($_SESSION[$gSessionVar])) {
		/* Get a simple reference to the session container (for convenience) */
		$gallery->session =& $_SESSION[$gSessionVar];

		/* DISABLED BY THE "0 &&" BELOW */
		// Allow session-sharing in devMode so that pages can be validated using the W3 validation links
		if (0 && ($gallery->app->devMode != "yes" && !empty($gallery->session->remoteHost)) && $gallery->session->remoteHost != $_SERVER['REMOTE_ADDR']) {
			printf('Attempted session access from different IP address. Please <a href="%s">re-login</a>.', $gallery->app->photoAlbumURL . '?PHPSESSID=');
			exit;
		}
	} else {
		/* Create a new session container */
		if (!empty($useStdClass)) {
			$_SESSION[$gSessionVar] = new stdClass();
		} else {
			$_SESSION[$gSessionVar] = new GallerySession();
		}

		/* Get a simple reference to the session container (for convenience) */
		$gallery->session =& $_SESSION[$gSessionVar];

		/* Tag this session with the gallery version */
		$gallery->session->version = $gallery->version;
		$gallery->session->sessionStart = time();
		$gallery->session->remoteHost = $_SERVER['REMOTE_ADDR'];
	}
}

// Create or resume our session
createGallerySession();

update_session_var("albumName");
update_session_var("version");
update_session_var("albumListPage");
update_session_var("fullOnly");
update_session_var("username", 1);
update_session_var("offline");
update_session_var("offlineAlbums");

if (!isset($gallery->session->offlineAlbums) || $gallery->session->offlineAlbums == null) {
	  $gallery->session->offlineAlbums = array();
}

/*
 * Process changes to session variables via parameters submitted in a
 * POST or GET.
 */
function update_session_var($name, $protected=0) {
	global $gallery;

	// If this is a protected session variable, don't allow it
	// to be changed by data from POST or GET requests.
	if ($protected) {
		return;
	}

	$setname = "set_$name";
	if (!emptyFormVar($setname)) {
		$gallery->session->{$name} = formVar($setname);
	}
}

function isHttpsConnection() {
	$httpType = isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : null;

	return !empty($httpType);
}
?>
