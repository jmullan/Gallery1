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

if (session_id()) {
	/* 
	 * The session is being created externally.  This means that if
	 * we store our data in a GallerySession class, we won't be able
	 * to deserialize it (because it's not being defined before the
	 * external session starts).  So, we'll have to fall back on using
	 * stdClass() which will cause problems on older PHP4 servers.
	 * oh well.
	 */
	$useStdClass = 1;
} else {
	$useStdClass = 0;
}

/* Start a new session, or resume our current one */
@session_start();

/*
 * Are we resuming an existing session?  Determine this by checking
 * to see if the session container variable is already set.  If not, then
 * create the appropriate container for it.
 */

if (empty($gallery->app->sessionVar)) {
	$gSessionVar = "gallery_session_".md5(getcwd()); 
} else {
	$gSessionVar = $gallery->app->sessionVar . "_" . md5($gallery->app->userDir);
}

if (isset($_SESSION[$gSessionVar])) {
	/* Get a simple reference to the session container (for convenience) */
	$gallery->session =& $_SESSION[$gSessionVar];

	// Allow session-sharing in devMode so that pages can be validated using the W3 validation links
	if (($gallery->app->devMode != "yes" && !empty($gallery->session->remoteHost)) && $gallery->session->remoteHost != $_SERVER['REMOTE_ADDR']) {
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

update_session_var("albumName");
update_session_var("version");
update_session_var("albumListPage");
update_session_var("fullOnly");
update_session_var("username", 1);
update_session_var("offline");
update_session_var("offlineAlbums");
if (!isset($gallery->session->offlineAlbums) || $gallery->session->offlineAlbums == null)
{
      $gallery->session->offlineAlbums=array();
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
?>
