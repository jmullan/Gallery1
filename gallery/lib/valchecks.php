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

/**
 * Checks whether an albuname has unallowed chars
 *
 * @param string $name
 * @return string		empty on success, otherwise the sanitized albumname.
 */
function validAlbumName($name) {

	$nameOrig = $name;

	$name = str_replace("'", "", $name);
	$name = str_replace("`", "", $name);
	$name = strtr($name, "%\\/*?\"<>|& .+#(){}~", "-------------------");
	$name = ereg_replace("\-+", "-", $name);
	$name = ereg_replace("\-+$", "", $name);
	$name = ereg_replace("^\-", "", $name);
	$name = ereg_replace("\-$", "", $name);

	if ($name != $nameOrig) {
		$ret = $name;
	}
	else {
		$ret = '';
	}

	return $ret;
}

/**
 * Calculates the password strength of a password.
 *
 * The code is *VERY* inspired (nearly copied) from the 'Password strength meter'
 * Written by firas kassem [2007.04.05]
 * Firas Kassem  phiras.wordpress.com || phiras at gmail {dot} com
 * For more information : http://phiras.wordpress.com/2007/04/08/password-strength-meter-a-jquery-plugin/
 *
 * @param string	$password
 * @return integer	$score
 */
function passwordStrength($password) {
	$score = 0 ;

	//password length
	$password_length = strlen($password);
	$score += $password_length * 4;

	$score += strlen(cleanRepetition(1, $password)) - $password_length;
	$score += strlen(cleanRepetition(2, $password)) - $password_length;
	$score += strlen(cleanRepetition(3, $password)) - $password_length;
	$score += strlen(cleanRepetition(4, $password)) - $password_length;

	//password has 3 numbers
	if (preg_match('/(.*[0-9].*[0-9].*[0-9])/', $password)) {
		$score += 5;
	}

	//password has 2 symbols
	if (preg_match('/(.*[!,@,#,$,%,^,&,*,?,_,~].*[!,@,#,$,%,^,&,*,?,_,~])/', $password)) {
		$score += 5;
	}

	//password has Upper and Lower chars
	if (preg_match('/([a-z].*[A-Z])|([A-Z].*[a-z])/', $password)) {
		$score += 10;
	}

	//password has number and chars
	if (preg_match('/([a-zA-Z])/', $password) && preg_match('/([0-9])/', $password)) {
		$score += 15;
	}

	//password has number and symbol
	if (preg_match('/([!,@,#,$,%,^,&,*,?,_,~])/', $password) && preg_match('/([0-9])/', $password)) {
		$score += 15;
	}

	//password has char and symbol
	if (preg_match('/([!,@,#,$,%,^,&,*,?,_,~])/', $password) && preg_match('/([a-zA-Z])/', $password)) {
		$score += 15;
	}

	//password is just a nubers or chars
	if (preg_match('/^\w+$/', $password) || preg_match('/^\d+$/', $password)) {
		$score -= 10;
	}

	//verifing 0 < score < 100
	if ($score < 0)  {
		$score = 0;
	}

	if ($score > 100) {
		$score = 100;
	}

	return $score;
}

/**
 * Removes repeated char(s) from a String
 *
 * The code is *VERY* inspired (nearly copied) from the 'Password strength meter'
 * Written by firas kassem [2007.04.05]
 * Firas Kassem  phiras.wordpress.com || phiras at gmail {dot} com
 * For more information : http://phiras.wordpress.com/2007/04/08/password-strength-meter-a-jquery-plugin/
 *
 * @param integer	$partLen	How big is the repeated string?
 * @param string	$string		String to check
 * @example checkRepetition(1,'aaaaaaabcbc')   = 'abcbc'
 * @example checkRepetition(2,'aaaaaaabcbc')   = 'aabc'
 * @example checkRepetition(2,'aaaaaaabcdbcd') = 'aabcd'
 * @return string	$cleaned
 */
function cleanRepetition($partLen, $string) {
	$cleaned = '';

	for ( $i = 0; $i < strlen($string) ; $i++ ) {
		$repeated = true;

		for ($j = 0; $j < $partLen && ($j + $i + $partLen) < strlen($string) ; $j++) {
			$repeated = $repeated && ($string{($j + $i)} == $string{($j + $i + $partLen)});
		}

		if ($j < $partLen) {
			$repeated = false;
		}

		if ($repeated) {
			$i += $partLen - 1;
			$repeated = false;
		}
		else {
			$cleaned .= $string{$i};
		}

	}

	return $cleaned;
}

/**
 * Checks whether a value seems to be a valid timestamp.
 *
 * @param integer  $timestamp
 * @return boolean $valid
 * @author Jesse Mullan
 */
function isValidTimestamp($timestamp) {
    /* See http://us3.php.net/strtotime */
    $valid = is_numeric($timestamp);
    $value = intval($timestamp);

    /* Negative values are not always legal (but sometimes are) */
    $valid &= (0 <= $value);
    /* This is the same test */
    $valid &= (strtotime('1970/1/1 GMT') < $timestamp);

    /* The maximum value of a 32-bit signed integer is 4294967296, or the y2k39 "bug" */
    $valid &= (strtotime('2038/01/19 03:14:07 GMT') > $timestamp);

    return $valid;
}

/**
 * Does the given watermark file exists in our watemark folder?
 * Hint: No subfolders supported.
 *
 * @param string    $wmName
 * @return boolean
 */
function watermarkPicExists($wmName = '') {
	global $gallery;

	if(empty($gallery->app->watermarkDir) || empty($wmName)) {
		return false;
	}

	if(!isXSSclean($wmName)) {
		return false;
	}

	if(fs_file_exists($gallery->app->watermarkDir . '/' . $wmName)) {
		return true;
	}
	else {
		return false;
	}
}

/**
 * Is a given value (number) in a certain range?
 *
 * @param float     $value
 * @param float     $min
 * @param float     $max
 * @return boolean
 */
function inRange($value, $min, $max) {
	if(!ctype_digit(trim($value))) {
		return false;
	}

	if ($value < $min || $value > $max) {
		return false;
	}
	else {
		return true;
	}

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
