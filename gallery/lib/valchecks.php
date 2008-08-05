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

/**
 * This is a wrapper around different valchecks
 * The input is the value, the type its tested against and optional a default
  *
 * @param mixed		$value
 * @param string	$type
 * @param mixed		$default
 * @param array		$choices
 * @return mixed	$result
 * @author Jens Tkotz
 */
function sanityCheck($value, $type, $default = NULL, $choices = array()) {
	switch ($type) {
		case 'int':
		case 'int_ZeroEmpty':
			$status = isValidGalleryInteger($value, true, true);

			break;

		case 'int_ZeroNotEmpty':
			$status = isValidGalleryInteger($value, true, false);

			break;

		case 'int_NotZeroNotEmpty':
			$status = isValidGalleryInteger($value, false, false);

			break;

		case 'pictureFrame':
			if(array_key_exists($value, available_frames())) {
				$result = array(0, $value, '');
			}
			else {
				$result = array(2, $value, gTranslate('common', "The given frame is not valid."));
			}

			break;

		case 'inChoice':
			if(in_array($value, $choices)) {
				$result = array(0, $value, '');
			}
			elseif (isset($default)) {
				$result = array(1, $default, gTranslate('common', "Value was set to given default, because the original value is not in the allowed list of choices."));
			}
			else {
				$result = array(2, $value, gTranslate('common', "The given value is not in the allowed list of choices."));
			}

			break;

		case 'filename':
			$status = isXSSclean($value);

			if($status) {
				$result = array(0, $value, '');
			}
			else {
				$result = array(2, $value, gTranslate('common', "The given value is not an allowed filename."));
			}

			break;

		case 'text_NotEmpty':
			if (empty($value)) {
				return array(2, $value, gTranslate('common', "Empty string is not allowed."));
			}

			$status = isValidText($value);

			break;

		default:
		case 'text':
			$status = isValidText($value);

			break;
	}

	/* Handle $result for integers */
	if (substr($type,0, 3) == 'int') {
		if(! $status) {
			if (empty($default)) {
				$result = array(1, $value, gTranslate('core', "The value is not a valid Gallery integer."));
			}
			else {
				$result = array(2, $default, gTranslate('core', "The value is not a valid Gallery integer and was set to a given default."));
			}
		}
		else {
			$result = array(0, $value, '');
		}

		return $result;
	}

	/* Handle $result for strings */
	if (substr($type,0, 4) == 'text') {
		if(! $status) {
			if (empty($default)) {
				$result = array(1, $value, gTranslate('core', "The value is not an allowed string."));
			}
			else {
				$result = array(2, $default, gTranslate('core', "The value is not an allowed string and was set to a given default."));
			}
		}
		else {
			$result = array(0, $value, '');
		}

		return $result;
	}

	return $result;
}

/**
 * This function checks if a given value is a valid "Gallery" integer.
 * A "Gallery" integer is a number of the set |N+ = {0, 1, 2, ...}.
 * Its not the PHP integer which is a number of the set |Z = {..., -2, -1, 0, 1, 2, ...}.
 *
 * Valid means:
 * --- Its a numeric and (real) integer value.
 * --- Is not lower as a given minimum (can be 1 or 0).
 *
 * @param mixed		$value		The value that is to be checked
 * @param boolean	$includingZero	Is 0 allowed?
 * @param boolean	$emptyAllowed
 * @return boolean	$result
 * @author Jens Tkotz
 * @author Jesse Mullan
 */
function isValidGalleryInteger($value, $includingZero = false, $emptyAllowed = false) {
	$minimum = ($includingZero == true) ? 0 : 1;

	if($value === '' || !isset($value)) {
		$result = (boolean) $emptyAllowed;
	}
	elseif($value < $minimum || !ctype_digit(trim($value)) || intval($value) != $value) {
		$result = false;
	}
	else {
		$result = true;
	}

	return $result;
}

/**
 * This function checks if a given value is a "valid" text.
 * Valid means, that is does not contain bad HTML or malicous chars.
 *
 * @param string	$text
 * @package integer	$level	0 = low level, 1 = high level
 * @return boolean	$result
 * @author Jens Tkotz
 */
function isValidText($text, $level = 0) {
	$sanitized = sanitizeInput($text);

	if($sanitized == $text && isXSSclean($text, $level)) {
		$result = true;
	}
	else {
		$result = false;
	}

	return $result;
}

/**
 * Checks whether an URL seems valid
 *
 * @param string 	$url	An URL.
 * @return boolean		True in case its a valid Url, otherwise false.
 * @author Jens Tkotz
 */
function isValidUrl($url) {
	if (!empty($url)) {
		//Detect header injection attempts, or XSS exposure.
		if (!isSafeHttpHeader($url) || ! isXSSclean($url, 0)) {
			return false;
		}
	}

	return true;
}

/**
 * Checks whether an URL is a Gallery URL
 *
 * @param string 	$url	Full URL to a Gallery file.
 * @return boolean		True in case its a valid Url, otherwise false.
 * @author Jens Tkotz
 */
function isValidGalleryUrl($url) {
	if (!empty($url)) {

		if (! isValidUrl($url)) {
			return false;
		}

		// Check for phishing attacks, don't allow return URLs to other sites
		$galleryBaseUrl = getGalleryBaseUrl();

		/*
		* We check for ../ and /../ patterns and on windows \../ would also break out,
		* normalize to URL / *nix style paths to check fewer cases
		*/
		$normalizedUrl = str_replace("\\", '/', $url);

		if (strpos($normalizedUrl, $galleryBaseUrl) !== 0 ||
			strpos($normalizedUrl, '/../') !== false)
		{
			// Invalid return URL! The requested URL tried to insert a redirection which is not a part of this Gallery
			return false;
		}
	}

	return true;
}

/**
 * Checks whether a header contains malicious characters.
 *
 * @param string	$header
 * @return boolean			True in case its a safe header, otherwise false.
 * @author Jens Tkotz
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

/**
 * Returns a set of malicious chars
 * Level 0: Only chars that i (the author) think are evil anytime are considered.
 * Level 1: All malicious.
 *
 * @param integer	$level
 * @return array	$badChars
 * @author Jens Tkotz
 */
function getMaliciousChars($level = 1) {
	$badChars = array();

	$anyTimeEvil = array(
		"../",
		"./",
		"<!--",
		"-->",
		"%20",
		"%22",
		"%3c",		// <
		"%253c", 	// <
		"%3e", 		// >
		"%0e", 		// >
		"%28", 		// (
		"%29", 		// )
		"%2528", 	// (
		"%26", 		// &
		"%24", 		// $
		"%3f", 		// ?
		"%3b", 		// ;
		"%3d",		// =
		"%2F"		// /
	);

	$sometimesAllowed = array(
		"<",
		">",
		"'",
		'"',
		'$',
		'#',
		'{',
		'}',
		'=',
		';',
		'?',
		'/',
		'\\'
	);

	$badChars = $anyTimeEvil;

	if ($level == 1) {
		$badChars = array_merge($anyTimeEvil, $sometimesAllowed);
	}

	return $badChars;
}

/**
 * Removes malicious chars from a string.
 *
 * @param string $string	String to sanitize
 * @param integer $level	See getMaliciousChars(), current: 0 lower level, 1 high
 * @return string
 */
function xssCleanup($string, $level = 1) {
	$badChars = getMaliciousChars($level);

	return stripslashes(str_replace($badChars, '', $string));
}

/**
 * Checks whether a string, (eg. filename or url) contains malicious characters.
 *
 * @param string	$string
 * @param integer	$level	See getMaliciousChars()
 * @return boolean		True/false if no malicious characters were found
 * @author Jens Tkotz
 */
function isXSSclean($string, $level = 1) {
	$sanitized = xssCleanup($string, $level);

	if(strcmp($sanitized, $string)) {
		return false;
	}
	else {
		return true;
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
 * Check given parameters for watermarking
 *
 * @param string  $wmName
 * @param integer $wmAlign
 * @param integer $wmSelect
 * @param integer $previewFull
 * @param integer $wmAlignX
 * @param integer $wmAlignY
 * @return array  $notice_messages
 */
function checkWatermarkSetting($wmName, $wmAlign, $wmSelect, $previewFull, $wmAlignX, $wmAlignY) {
	$notice_messages = array();

	if(! watermarkPicExists($wmName)) {
		$notice_messages[] = array(
			'type' => 'error',
			'text' => gTranslate('core', "No valid watermark image chosen.")
		);
	}

	if(! isValidGalleryInteger($wmAlign) || ! inRange($wmAlign, 1, 10)) {
		$notice_messages[] = array(
			'type' => 'error',
			'text' => gTranslate('core', "Please select a correct alignment.")
		);
	}

	if(! isValidGalleryInteger($wmSelect, true) || ! inRange($wmSelect, 0, 2)) {
		$notice_messages[] = array(
			'type' => 'error',
			'text' => gTranslate('core', "Please select on which photos you want the watermark.")
		);
	}

	if(! isValidGalleryInteger($previewFull, true) || ! inRange($previewFull, 0, 1)){
		$notice_messages[] = array(
			'type' => 'error',
			'text' => gTranslate('core', "Do you want a preview or not?")
		);
	}

	if ($wmAlign == 10 &&
	   (! isValidGalleryInteger($wmAlignX, true, false) || ! isValidGalleryInteger($wmAlignY, true, false)))
	{
		$notice_messages[] = array(
			'type' => 'error',
			'text' => gTranslate('core', "Field X and Y need to be filled correctly.")
		);
	}

	return $notice_messages;
}
?>
