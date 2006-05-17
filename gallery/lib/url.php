<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2006 Bharat Mediratta
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
?>
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
	global $modpath;

        if (empty($GALLERY_MODULENAME)
          && $GALLERY_EMBEDDED_INSIDE =='nuke'
          && !empty($modpath)) {
            $GALLERY_MODULENAME = basename(dirname($modpath));
        }

	/* Needed for phpBB2 */
	global $userdata;
	global $board_config;

	/* Needed for Mambo / Joomla! */
	global $MOS_GALLERY_PARAMS;

	/* Needed for CPGNuke */
	global $mainindex;

	$url = '';
	$prefix = '';
	$isSetupUrl = (stristr($target,"setup")) ? true : false;

	if(!urlIsRelative($gallery->app->photoAlbumURL)) {
		$gUrl = parse_url($gallery->app->photoAlbumURL);
		$urlprefix = $gUrl['scheme'] .'://'. $gUrl['host'];
	}
	else {
	    $urlprefix = '';
	}

	/* make sure the urlprefix doesnt end with a / */
	$urlprefix = ereg_replace("\/$", "", $urlprefix);

	/* Add the folder to the url when *Nuke is not direct in the main folder */
	$addpath = substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/'));

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
				$args["name"] = $GALLERY_MODULENAME;
				$args["file"] = "index";

				/*
				 * include *must* be last so that the JavaScript code in
				 * view_album.php can append a filename to the resulting URL.
				 */
				$args["include"] = $target;
				$url = $urlprefix . $addpath .'/modules.php';
			break;

			case 'cpgnuke':
				$args["name"] = "$GALLERY_MODULENAME";
				$args["file"] = "index";

				/*
				 * include *must* be last so that the JavaScript code in
				 * view_album.php can append a filename to the resulting URL.
				 */
				$args["include"] = $target;
				$url = $urlprefix . $addpath . "/$mainindex";
			break;

			case 'postnuke':
				if (substr(_PN_VERSION_NUM, 0, 7) < "0.7.6.0") {
					$args["op"] = "modload";
					$args["file"] = "index";

					$url = $urlprefix . $addpath . '/modules.php';
				}
				else {
					$url = $urlprefix . pnGetBaseURI()."/index.php";
				}

				$args["name"] = $GALLERY_MODULENAME;
				/*
				 * include *must* be last so that the JavaScript code in
				 * view_album.php can append a filename to the resulting URL.
				 */
				$args["include"] = $target;
			break;

			case 'mambo':
			case 'joomla':
				$args['option'] = $GALLERY_MODULENAME;
				$args['Itemid'] = $MOS_GALLERY_PARAMS['itemid'];
				$args['include'] = $target;

				/* We cant/wantTo load the complete Mambo / Joomla! Environment into the pop up
				** E.g. the Upload Framwork does not work then
				** So we need to put necessary infos of Mambo / Joomla! into session.
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

/**
 * makeAlbumUrl is a wrapper around makeGalleryUrl.  You tell it what
 * album (and optional photo id) and it does the rest.  You can also
 * specify additional key/value pairs in the optional third argument.
*/
function makeAlbumUrl($albumName = '', $photoId = '', $args = array()) {
	global $GALLERY_EMBEDDED_INSIDE, $GALLERY_EMBEDDED_INSIDE_TYPE;
	global $gallery;

	// We can use GeekLog with rewrite because Gallery is embedded in a different way.
	if ( $gallery->app->feature["rewrite"] == 1 &&
		(! $GALLERY_EMBEDDED_INSIDE || $GALLERY_EMBEDDED_INSIDE_TYPE == 'GeekLog')) {
		if ($albumName) {
			$target = urlencode($albumName);

			// Can't have photo without album
			if ($photoId) {
				$target .= '/'. urlencode($photoId);
			}
		} else {
			$target = "albums.php";
		}
	} else {
		if ($albumName) {
			$args["set_albumName"] = urlencode ($albumName);
			if ($photoId) {
				$target = "view_photo.php";
				$args["id"] = urlencode($photoId);
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
    $defaultname = dirname(dirname(__FILE__)). "/images/$name";
    $defaultURL = "$base/images/$name";
    $fullname = dirname(dirname(__FILE__)) . "/skins/$skinname/images/$name";
    $fullURL = "$base/skins/$skinname/images/$name";

    if (fs_file_exists($fullname) && !broken_link($fullname)) {
    	$retUrl = $fullURL;
    }
    elseif (fs_file_exists($defaultname) && !broken_link($defaultname)) {
    	$retUrl = $defaultURL;
    }

    return $retUrl;
}

/**
 * @param	string	$name		Name of Image
 * @param	string	$skinname	Optional Name skin, if file is not found, fallback to default location
 * @return	string	$retPath	Complete Path to the Image
 * @author	Jens Tkotz <jens@peino.de>
 */
function getAbsoluteImagePath($name, $skinname = '') {
    global $gallery;
    $retPath = '';

    $base = dirname(dirname(__FILE__));

    $defaultPath = "$base/images/$name";

    /* Skin maybe 'none', but this is never found, so we fall back to default. */
    if (!$skinname) {
    	$skinname = $gallery->app->skinname;
    }
    $skinPath = "$base/skins/$skinname/images/$name";

    if (fs_file_exists($skinPath)) {
    	$retPath = $skinPath;
    } else {
    	$retPath = $defaultPath;
    }

    return $retPath;
}

/**
 * Checkes wether an URL is relative or not
 * @param	string	$url
 * @return	boolean
 * @author	Jens Tkotz <jens@peino.de>
 */
function urlIsrelative($url) {
	if (substr($url, 0,4) == 'http') {
		return false;
	}
	else {
		return true;
	}
}

function broken_link($file) {
    if (fs_is_link($file)) {
        return !fs_is_file($file);
    } else {
        return false;
    }
}

function galleryLink($url, $text='', $attrList = array(), $icon = '', $addBrackets = false, $accesskey = true) {
   $html = '';
   $altText = $text;

   if($accesskey == true && empty($attrList['accesskey']) && !empty($text)) {
	if(is_int($text) && $text < 10) {
	    $attrList['accesskey'] = $text;
	    $altText = $text;
	}
	else {
	    $pos = strpos($text, '_');

	    if ($pos !== false) {
		$attrList['accesskey'] = substr($text,$pos+1,1);
		$altText = substr_replace($text, '', $pos,1);
		$text = substr_replace($text, '<span class="g-accesskey">'. $attrList['accesskey'] .'</span>', $pos,2);
	    }
	}
    }

    if (!$accesskey) {
	unset($attrList['accesskey']);
    }

    if (!empty($attrList['altText'])) {
	$altText = $attrList['altText'];
	unset($attrList['altText']);
    }

    $attrs = generateAttrs($attrList);

    if(!empty($icon)) {
        $content = getIconText($icon, $text, '', $addBrackets, $altText);
    }
    else {
        if($addBrackets) {
            $content = '['. $text .']';
        } else {
            $content = $text;
        }
    }

    if (!empty($url)) {
	   $html .= "<a href=\"$url\"$attrs>$content</a>";
    }
    else {
        $html .= "<a$attrs>$content</a>";
    }

    return $html;
}
?>