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
