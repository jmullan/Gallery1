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
/*
 * PHP 4.0.1pl2 introduces a bug where you can't unserialize a 
 * stdClass instance correctly.  So create a dummy class to hold all
 * of our session data.
 */
class GallerySession {}

/* Start a new session, or resume our current one */
session_start();

/*
 * Are we resuming an existing session?  Determine this by checking
 * to see if a pre-existing session variable is already associated
 * (before we register it, below).  
 */
if (session_is_registered($gallery->app->sessionVar)) {
	/* Get a simple reference to the session container (for convenience) */
	$gallery->session =& ${$gallery->app->sessionVar};

	/* Make sure our session is current.  If not, nuke it and restart. */
	if (strcmp($gallery->session->version, $gallery->version)) {
		session_destroy();
		header("Location: index.php");
		exit;
	}	
} else {
	/* Register the session variable */
	session_register($gallery->app->sessionVar);

	/* Create a new session container */
	${$gallery->app->sessionVar} = new GallerySession();

	/* Get a simple reference to the session container (for convenience) */
	$gallery->session =& ${$gallery->app->sessionVar};

	/* Tag this session with the gallery version */
	$gallery->session->version = $gallery->version;
}

update_session_var("albumName");
update_session_var("version");
update_session_var("albumListPage");
update_session_var("fullOnly");
update_session_var("username", 1);

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
