<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2007 Bharat Mediratta
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
 * This is a wrapper around different valchecks
 * The input is the value, the type its tested against and optional a default
 * The return is given by the valcheck function
*/
function sanityCheck($var, $type, $default = NULL, $choices = array()) {
	  switch ($type) {
		case 'int':
			return isValidInteger($var, true, NULL, true);
			break;
		case 'int_notnull':
			return isValidInteger($var, true, $default, false);
			break;
		case 'int_empty':
			return isValidInteger($var, true, $default, true);
			break;
		case 'pictureFrame':
			if(array_key_exists($var, available_frames())) {
				return array(0, $var, '');
			}
			else {
				return array(2, $var, gTranslate('common', "The given frame is not valid."));
			}
			break;
		case 'inChoice':
			if(in_array($var, $choices)) {
				return array(0, $var, '');
			}
			elseif (isset($default)) {
                return array(1, $default, gTranslate('common', "Value was set to given default, because the original value is not in the allowed list of choices."));
			}
			else {
                return array(2, $var, gTranslate('common', "The given value is not in the allowed list of choices."));
			}
			break;
		default:
		case 'text':
			return isValidText($var, $default);
			break;
	}
}

/**
 * This function checks if a given value is a valid integer.
 * Valid means:
 * --- its a numeric value
 * --- is not lower a given minum (can be 1 or 0)
 * You can give a default to correct an invalid input
 *
 * Return is an array that contains:
 * --- Status, can be 0 for OK, 1 for set to default, 2 failure and no default
 * --- Original or default value
 * --- Debug message
*/
function isValidInteger($mixed, $includingZero = false, $default = NULL, $emptyAllowed = false) {
	$minimum = ($includingZero == true) ? 0 : 1;

	if ( $mixed == '' && $emptyAllowed) {
		return array(0, $mixed, '');
	}

	if (! is_numeric($mixed)) {
		if (isset($default)) {
            return array(1, $default, gTranslate('common', "Value was set to given default, because the original value is not numeric."));
		}
		else {
            return array(2, false, gTranslate('common', "The given value is not numeric."));
		}
	}

	if($mixed < $minimum) {
		if (isset($default)) {
            return array(1, $default, gTranslate('common', "Value was set to given default, because the original value is not a valid integer."));
		}
		else {
            return array(2, false, gTranslate('common', "The given value not a valid integer."));
		}
	}

	return array(0, $mixed, '');
}

function isValidText($text, $default = NULL) {
	$sanitized = sanitizeInput($text);

	if($sanitized != $text) {
		if(isset($default)) {
            return array(1, $default, gTranslate('common', "Value was set to given default, because the original value is not a valid text."));
		}
		else {
            return array(1, $sanitized, gTranslate('common', "Value was corrected, because the original value is not a valid text."));
		}
	}
	else {
		return array(0, $text, '');
	}
}

function sanitizeInput($value) {
	if(!is_array($value) && strip_tags($value) == $value) {
		return $value;
	}

	require_once(dirname(dirname(__FILE__)) .'/classes/HTML_Safe/Safe.php');
	static $safehtml;

	if (empty($safehtml)) {
		$safehtml =& new HTML_Safe();
	}

	if(is_array($value)) {
		//echo "\n -> Array";
		//echo "\n<ul>";
		foreach($value as $subkey => $subvalue) {
			//printf("\n<li>Checking SubValue: %s", htmlspecialchars($subkey));
			$sanitized[$subkey] = sanitizeInput($subvalue);
		}
		//echo "\n</ul>";
	}
	else {
		//echo " === ". htmlspecialchars($value);
		$sanitized = $safehtml->parse($value);
		if($sanitized != $value) {
			//echo "--->". $sanitized;
		}
	}
	return $sanitized;
}

/**
 * Checks whether an URL is a Gallery URL
 *
 * @param string $url	Full URL to a Gallery file.
 * @return boolean
 */
function isValidGalleryUrl($url) {
	if (!empty($url)) {
		//Detect header injection attempts
		if (!isSafeHttpHeader($url)) {
			debugMessage(gTranslate('core', "Invalid return URL! The requested URL contains malicious characters and is denied."), __FILE__, __LINE__);
			return false;
		}

		// Check for phishing attacks, don't allow return URLs to other sites
		$galleryBaseUrl = getGalleryBaseUrl();

		/*
		* We check for ../ and /../ patterns and on windows \../ would also break out,
		* normalize to URL / *nix style paths to check fewer cases
		*/

		$normalizedUrl = str_replace("\\", '/', $url);

		if (
			strpos($normalizedUrl, $galleryBaseUrl) !== 0 ||
			strpos($normalizedUrl, '/../') !== false)
		{
			debugMessage(gTranslate('core', "Invalid return URL! The requested URL tried to insert a redirection which is not a part of this Gallery."), __FILE__, __LINE__);
			echo $normalizedUrl;
			echo "\n<br>";
			echo $galleryBaseUrl;
			return false;
		}
	}

	return true;
}

/**
 * Checks whether a URL does contains malicious characters.
 *
 * @param string	$header
 * @return boolean
 */
function isSafeHttpHeader($header) {
	if (!is_string($header)) {
		return false;
	}

	/* Don't allow plain occurrences of CR or LF */
	if (strpos($header, chr(13)) !== false || strpos($header, chr(10)) !== false) {
		return false;
	}

	/* Don't allow (x times) url encoded versions of CR or LF */
	if (preg_match('/%(25)*(0a|0d)/i', $header)) {
		return false;
	}

	return true;

}
?>
