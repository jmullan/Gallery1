<?php

/*
 * Any URL that you want to use can either be accessed directly
 * in the case of a standalone Gallery, or indirectly if we're
 * mbedded in another app such as Nuke.  makeGalleryUrl() will
 * always create the appropriate URL for you.
 *
 * Usage:  makeGalleryUrl(target, args [optional])
 *
 * target is a file with a relative path to the gallery base
 *        (eg, "album_permissions.php")
 *
 * args   are extra key/value pairs used to send data
 *        (eg, array("index" => 1, "set_albumName" => "foo"))
 */

function makeGalleryUrl($target, $args=array()) {

	global $gallery;
	global $GALLERY_EMBEDDED_INSIDE;
	global $GALLERY_EMBEDDED_INSIDE_TYPE;
	global $GALLERY_MODULENAME;

	/* Needed for phpBB2 */
	global $userdata;
	global $board_config;
        
	/* Needed for Mambo */
	global $MOS_GALLERY_PARAMS;

	/* Needed for CPGNuke */
	global $mainindex;

	if( isset($GALLERY_EMBEDDED_INSIDE)) {
		switch ($GALLERY_EMBEDDED_INSIDE_TYPE) {
			case 'phpBB2':
				$cookiename = $board_config['cookie_name'];
				if(!isset($_COOKIE[$cookiename . '_sid'])) {
					// no cookie so we need to pass the session ID manually.
					$args["sid"] = $userdata['session_id'];
					if(!isset($args["set_albumName"])) {
						// This var is only passed some of the time and but is required so PUT IT IN when needed.
						$args["set_albumName"] = $gallery->session->albumName;
					}
				}

			case 'phpnuke':
			case 'postnuke':
			case 'nsnnuke':
				$args["op"] = "modload";
				$args["name"] = "$GALLERY_MODULENAME";
				$args["file"] = "index";

				/*
				 * include *must* be last so that the JavaScript code in
				 * view_album.php can append a filename to the resulting URL.
				 */
				$args["include"] = $target;
				$target = "modules.php";
			break;

			case 'mambo':
				$args['option'] = "com_gallery";
				$args['Itemid'] = $MOS_GALLERY_PARAMS['itemid'];
				$args['include'] = $target;

				/* We cant/wantTo load the complete Mambo Environment into the pop up
				** E.g. the Upload Framwork does not work then
				** So we need to put necessary infos of Mambo into session.
				*/
				if ((isset($args['type']) && $args['type'] == 'popup') ||
					(!empty($args['gallery_popup']))) {
					$target= $gallery->app->photoAlbumURL . "/index.php";
				} else {
					if (!empty($gallery->session->mambo->mosRoot)) {
						$target = $gallery->session->mambo->mosRoot . "index.php";
					} else {
						$target = 'index.php';
					}
				}
			break;

			case 'cpgnuke':
				$args["name"] = "$GALLERY_MODULENAME";
				$args["file"] = "index";

				/*
				 * include *must* be last so that the JavaScript code in
				 * view_album.php can append a filename to the resulting URL.
				 */
				$args["include"] = $target;
				$target = $mainindex;
			break;

			// Maybe something went wrong, then we assume we are like standalone.
			default:
				$target = $gallery->app->photoAlbumURL . "/" . $target;
		}
	}
	else {
		$prefix = isset($gallery->app->photoAlbumURL) ? $gallery->app->photoAlbumURL . "/" : "";
		$target = $prefix . $target;
	}
       
	$url = $target;
	if ($args) {
		$i = 0;
		foreach ($args as $key => $value) {
			if ($i++) {
				$url .= "&";  // should replace with &amp; for validatation
			} else {
				$url .= "?";
			}

			if (! is_array($value)) {
				$url .= "$key=$value";
			} else {
				$j = 0;
				foreach ($value as $subkey => $subvalue) {
					if ($j++) {
						$url .= "&";  // should replace with &amp; for validatation
					}
					$url .= $key .'[' . $subkey . ']=' . $subvalue;
				}
			}
		}
	}
	return htmlspecialchars($url);
}

function makeGalleryHeaderUrl($target, $args=array()) {
	$url = makeGalleryUrl($target, $args);
	return unhtmlentities($url);
}

/*
 * makeAlbumUrl is a wrapper around makeGalleryUrl.  You tell it what
 * album (and optional photo id) and it does the rest.  You can also
 * specify additional key/value pairs in the optional third argument.
 */

function makeAlbumUrl($albumName="", $photoId="", $args=array()) {
	global $GALLERY_EMBEDDED_INSIDE, $GALLERY_EMBEDDED_INSIDE_TYPE;
	global $gallery;

	// We can use GeekLog with rewrite because Gallery is embedded in a different way.
	if ( $gallery->app->feature["rewrite"] == 1 &&
		(! $GALLERY_EMBEDDED_INSIDE || $GALLERY_EMBEDDED_INSIDE_TYPE == 'GeekLog')) {
		if ($albumName) {
			$target = urlencode ($albumName);

			// Can't have photo without album
			if ($photoId) {
				$target .= "/".urlencode ($photoId);
			}
		} else {
			$target = "albums.php";
		}
	} else {
		if ($albumName) {
			$args["set_albumName"] = urlencode ($albumName);
			if ($photoId) {
				$target = "view_photo.php";
				$args["id"] = urlencode ($photoId);
			} else {
				$target = "view_album.php";
			}
		} else {
			$target = "albums.php";
		}
	}
	return makeGalleryUrl($target, $args);
}

function makeAlbumHeaderUrl($albumName="", $photoId="", $args=array()) {
	$url = makeAlbumUrl($albumName, $photoId, $args);
	return unhtmlentities($url);
}

function addUrlArg($url, $arg) {
	if (strchr($url, "?")) {
		return "$url&$arg"; // should replace with &amp; for validatation
	} else {
		return "$url?$arg";
	}
}

?>
