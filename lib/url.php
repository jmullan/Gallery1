<?php

/**
 * Functions that provide possibility to create and modify URLS
 *
 * @package	urls
 */

/**
 * Define Constants for Gallery pathes.
 */
function setGalleryPaths() {
	if (defined('GALLERY_BASE')) {
		return;
	}

	$currentFile = __FILE__;
	if ( $currentFile == '/usr/share/gallery/lib/url.php') {
		/* We assum Gallery runs on as Debian Package */
		define ("GALLERY_CONFDIR", "/etc/gallery");
		define ("GALLERY_SETUPDIR", "/var/lib/gallery/setup");
	} else {
		define ("GALLERY_CONFDIR", dirname(dirname(__FILE__)));
		define ("GALLERY_SETUPDIR", dirname(dirname(__FILE__)) . "/setup");
	}

	define ("GALLERY_BASE", dirname(dirname(__FILE__)));
}

/**
 *
 */
function getGalleryBaseUrl() {
    global $gallery;

    if (isset($gallery->app) && isset($gallery->app->photoAlbumURL)) {
        $base = $gallery->app->photoAlbumURL;
    }
    elseif(where_i_am() == 'config') {
        $base = '..';
    } elseif (defined('GALLERY_URL')) {
        $base = GALLERY_URL;
    } else {
        $base = '.';
    }

    return $base;
}
/**
 * Any URL that you want to use can either be accessed directly
 * in the case of a standalone Gallery, or indirectly if we're
 * mbedded in another app such as Nuke.  makeGalleryUrl() will
 * always create the appropriate URL for you.
 *
 * @param	string	$target	File with a relative path to the gallery base
 *				(eg, "album_permissions.php")
 *
 * @param	array	$args	Optional array containg additional Urlargs.
 *				(eg, array("index" => 1, "set_albumName" => "foo"))
 * @return	string
 */

function makeGalleryUrl($target = '', $args = array()) {
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

	$url = '';
	$prefix = '';
	$isSetupUrl = (stristr($target,"setup")) ? true : false;

	if (isset($_SERVER['HTTP_REFERER'])) {
		$referer = parse_url($_SERVER['HTTP_REFERER']);
		$urlprefix = $referer['scheme'] .'://'. $referer['host'];
	}
	else {
		$urlprefix = '';
	}
    
	if( isset($GALLERY_EMBEDDED_INSIDE) && !$isSetupUrl && where_i_am() != 'config') {
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
			case 'nsnnuke':
				$args["op"] = "modload";
				$args["name"] = "$GALLERY_MODULENAME";
				$args["file"] = "index";

				/*
				 * include *must* be last so that the JavaScript code in
				 * view_album.php can append a filename to the resulting URL.
				 */
				$args["include"] = $target;
				$url = $urlprefix .'/modules.php';
			break;

			case 'postnuke':
				if (substr(_PN_VERSION_NUM, 0, 7) < "0.7.6.0") {
					$args["op"] = "modload";
					$args["file"] = "index";

					$url = $urlprefix .'/modules.php';
				}
				else {
					$url = $urlprefix . pnGetBaseURI()."/index.php";
				}
				
				$args["name"] = "$GALLERY_MODULENAME";
				/*
				 * include *must* be last so that the JavaScript code in
				 * view_album.php can append a filename to the resulting URL.
				 */
				$args["include"] = $target;
			break;
							
			case 'mambo':
				$args['option'] = $GALLERY_MODULENAME;
				$args['Itemid'] = $MOS_GALLERY_PARAMS['itemid'];
				$args['include'] = $target;

				/* We cant/wantTo load the complete Mambo Environment into the pop up
				** E.g. the Upload Framwork does not work then
				** So we need to put necessary infos of Mambo into session.
				*/
				if ((isset($args['type']) && $args['type'] == 'popup') ||
					(!empty($args['gallery_popup']))) {
					$target = 'index.php';
				} else {
					if (!empty($gallery->session->mambo->mosRoot)) {
						$url = $urlprefix . $gallery->session->mambo->mosRoot . 'index.php';
					} else {
						$url ='index.php';
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
				$url = $urlprefix . "/$mainindex";
			break;

			// Maybe something went wrong, we do nothing as URL we be build later.
			default:
			break;
		}
	}

	if (empty($url)) {       
	    $url = getGalleryBaseUrl() ."/$target";
	}

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

/**
 * @param	string	$name		Name of Image
 * @param	string	$skinname	Optional Name skin, if file is not found, fallback to default location
 * @return	string	$retUrl		Complete URL to the Image
 */
function getImagePath($name, $skinname = '') {
    global $gallery;
    $retUrl = '';

    if (!$skinname) {
	$skinname = $gallery->app->skinname;
    }

    /* We cant use makeGalleryUrl() here, as Gallery could be embedded. */
    $base = getGalleryBaseUrl();
    $defaultname = "$base/images/$name";
    $fullname = dirname(dirname(__FILE__)) . "/skins/$skinname/images/$name";
    $fullURL = "$base/skins/$skinname/images/$name";

    if (fs_file_exists($fullname) && !broken_link($fullname)) {
	$retUrl = $fullURL;
    } else {
	$retUrl = $defaultname;
    }

    return $retUrl;
}

?>
