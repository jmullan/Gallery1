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

@ini_set('session.bug_compat_warn', 'off');
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
}

/* Start a new session, or resume our current one */
@session_start();

/* emulate register_globals for sessions */
if (!$gallery->register_globals) {
	if (is_array($HTTP_SESSION_VARS) && !empty($HTTP_SESSION_VARS)) {
		foreach($HTTP_SESSION_VARS as $key => $value) {
			$$key =& $HTTP_SESSION_VARS[$key];
		}
	}
	elseif (is_array($_SESSION) && !empty($_SESSION)) {
		foreach($_SESSION as $key => $value) {
			$$key =& $_SESSION[$key];
		}
	}		
}

/*
 * Are we resuming an existing session?  Determine this by checking
 * to see if the session container variable is already set.  If not, then
 * create the appropriate container for it.
 */

if(! isset($gallery->app->sessionVar)) {
	$gSessionVar = "gallery_session_".md5(getcwd()); 
} else {
	$gSessionVar = $gallery->app->sessionVar . "_" . md5($gallery->app->userDir);
}
session_register($gSessionVar);

if (isset($$gSessionVar)) {
	/* Get a simple reference to the session container (for convenience) */
	$gallery->session =& $$gSessionVar;

	/* Make sure our session is current.  If not, nuke it and restart. */
	/* Disabled this code -- it has too many repercussions */
	if (false) {
	    if (strcmp($gallery->session->version, $gallery->version)) {
		session_destroy();
		header("Location: " . makeGalleryHeaderUrl("index.php"));
		exit;
	    }
	}
} else {
	/* Register the session variable */
	session_register($gSessionVar);

	/* Create a new session container */
	if (isset($useStdClass)) {
		$$gSessionVar = new stdClass();
	} else {
		$$gSessionVar = new GallerySession();
	}

	/* Get a simple reference to the session container (for convenience) */
	$gallery->session =& $$gSessionVar;

	/* Tag this session with the gallery version */
	$gallery->session->version = $gallery->version;
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
