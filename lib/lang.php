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
 * @package	Language
 * @author	Jens Tkotz
 */

/**
 * This function is a wrapper around ngettext for two reasons
 * 1.) We can use %s and %d in translation
 * 2.) We can use a special "none" without modifying the plural definition.
 *
 * @param   string  $domain
 * @param   string  $singular
 * @param   string  $plural
 * @param   int	 $count
 * @param   string  $nonetext
 * @return  string  $translation	string with translation on success, otherwise '--- TranslationError --'
 * @author  Jens Tkotz
 */
function gTranslate($domain = null, $singular, $plural = '', $count = null, $nonetext = '', $short = false) {
	global $gallery;

	$allowedDomain = array('config', 'common', 'core');
	if(!in_array($domain, $allowedDomain)) {
		return '<span class="g-error">'. ("-- Translation Domain wrong --") .'</span>';
	}

	if ($count == 0 && $nonetext != '') {
		return $nonetext;
	}

	if (gettext_installed()) {
		$gDomain = $gallery->language. "-gallery_$domain";
		bindtextdomain($gDomain, dirname(dirname(__FILE__)) . '/locale');
		textdomain($gDomain);
	}

	if(!$plural) {
		if (gettext_installed()) {
			$translation = dgettext($gDomain, $singular);
		}
		else {
			$translation = _($singular);
		}
	}
	else {
		if (!empty($count) && intval($count) == 0) {
			$count = 1;
		}
		if (ngettext_installed()) {
			$translation = dngettext($gDomain, $singular, $plural, $count);
		}
		else {
			$translation = ngettext($singular, $plural, $count);
		}
		if($short) {
			$translation = sprintf($translation, $count);
		}
	}

	return $translation;
}

/**
 * Detect the first Language of users Browser
 * Some Browser only send 2 digits like he or de.
 * This is caught later with the aliases
 * @author Jens Tkotz
 * @return string   $browserLang
*/
function getBrowserLanguage() {
	if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
		$lang = explode (",", $_SERVER["HTTP_ACCEPT_LANGUAGE"]);

		/* Maybe there are some extra infos we dont need, so we strip them. */
		$spos = strpos($lang[0],";");
		if ($spos >0) {
			$lang[0] = substr($lang[0],0,$spos);
		}

		/* browser may send aa-bb, then we convert to aa_BB */
		$lang_pieces = explode ("-",$lang[0]);
		if (strlen($lang[0]) >2) {
			$browserLang = strtolower($lang_pieces[0]). "_".strtoupper($lang_pieces[1]) ;
		}
		else {
			$browserLang = $lang[0];
		}
	}
	else {
		$browserLang = false;
	}

	return $browserLang;
}

/**
 * Set Gallery Default:
 * - language
 * - charset
 * - direction
*/
function setLangDefaults($nls) {
	global $gallery;

	$gallery->language 	= 'en_US';
	$gallery->charset  	= $nls['default']['charset'];
	$gallery->direction	= $nls['default']['direction'];
}

/**
 * This function tries to get the languge given by the Environment.
 * @return mixed The language the environment uses, or NULL if Gallery was not able to get it.
 * @author Jens Tkotz
 */
function getEnvLang() {
	global $GALLERY_EMBEDDED_INSIDE_TYPE;

	global $board_config;						/* Needed for phpBB2			*/
	global $_CONF;								/* Needed for GeekLog   		*/
	global $mosConfig_locale, $mosConfig_lang;	/* Needed for Mambo / Joomla!	*/
	global $currentlang;						/* Needed for CPGNuke		*/

	$envLang = NULL;

	switch ($GALLERY_EMBEDDED_INSIDE_TYPE) {
		case 'postnuke':
			if (!empty($_SESSION['PNSVlang'])) {
				$envLang = $_SESSION['PNSVlang'];
			}
			else  {
				$envLang = pnSessionGetVar('lang');
			}
		break;

		case 'phpnuke':
		case 'nsnnuke':
			if (isset($_COOKIE['lang'])) {
				$envLang = $_COOKIE['lang'];
			}
		break;

		case 'phpBB2':
			if (isset($board_config['default_lang'])) {
				$envLang = $board_config['default_lang'];
			}
		break;

		case 'GeekLog':
			/* Note: $_CONF is not a Superglobal ;) */
			if (isset($_CONF['language'])) {
				$envLang = $_CONF['language'];
			} else if (isset($_CONF['locale'])) {
				$envLang = $_CONF['locale'];
			}
		break;

		case 'mambo':
		case 'joomla':
			$envLang = $mosConfig_lang;
			/* if Alias and Lang are equal, then now Alias was defined */
			if (getLanguageAlias($envLang) == $envLang) {
				if (isset($mosConfig_locale)){
					$envLang = $mosConfig_locale;
				}
			}
		break;

		case 'cpgnuke':
			if (isset($currentlang)){
				$envLang = $currentlang;
			}
		break;

		default:
			$envLang = NULL;
		break;
	}

	return $envLang;
}

/**
 * Gets the default language for Gallery.
 * If not set, fallback to browserlanguage.
 * @author Jens Tkotz
 * @return string $defaultLanguage;
 */
function getDefaultLanguage() {
	global $gallery;

	if(isset($gallery->app->default_language)
	  && $gallery->app->default_language != 'browser') {
		$defaultLanguage = $gallery->app->default_language;
	}
	else {
		$defaultLanguage = getBrowserLanguage();
	}

	return $defaultLanguage;
}

/**
 * In some Environments we dont want to allow the user
 * to change the language.
 * In this case we override Mode 3 with Mode 1 and
 * Gallery runs in the language the Environment use.
*/
function forceStaticLang() {
	global $GALLERY_EMBEDDED_INSIDE_TYPE;
	global $gallery;

	$useStatic = array('joomla', 'mambo', 'phpBB2', 'GeekLog');

	if (in_array($GALLERY_EMBEDDED_INSIDE_TYPE, $useStatic)) {
		$gallery->app->ML_mode = 1;
	}
}

/**
 * This function does the initialization of language related things.
 * @author Jens Tkotz
 */
function initLanguage($sendHeader = true) {
	static $languages_initialized = false;

	global $gallery, $GALLERY_EMBEDDED_INSIDE, $GALLERY_EMBEDDED_INSIDE_TYPE;

	/**
	 * Init was already done. Just return, or do a reinit
	 * if the giving userlanguage is different than the current language
	*/
	if($languages_initialized) {
		return;
	}

	/* $locale is *NUKEs locale var*/
	global $locale ;

	$nls = getNLS();

	/* Set Defaults, they may be overwritten. */
	setLangDefaults($nls);

	/* Before we do any tests or settings test if we are in mode 0
	If so, we skip language settings at all */

	/* Mode 0 means no Multilanguage at all. */
	if (isset($gallery->app->ML_mode) && $gallery->app->ML_mode == 0) {
		/* Maybe PHP has no (n)gettext, then we have to substitute _() and ngettext*/
		if (!gettext_installed()) {
			function _($string) {
				return $string ;
			}
		}
		if (!ngettext_installed()) {
			function ngettext($singular, $quasi_plural,$num=0) {
				if ($num == 1) {
					return $singular;
				}
				else {
					return $quasi_plural;
				}
			}
		}

		/* Skip rest*/
		$languages_initialized = true;
		return;
	}

	/**
	 * Does the user wants a new lanuage ?
	 * This is used in Standalone and *Nuke
	 */
	$newlang = getRequestVar('newlang');

	/**
	 * Note: ML_mode is only used when not embedded
	 */

	if (isset($GALLERY_EMBEDDED_INSIDE_TYPE)) {
		/* Gallery is embedded */

		/* Gallery can set nukes language.
		* For phpBB2, GeekLog, Mambo and Joomla! this is not possible, Gallery will always use their language.
		*/
		forceStaticLang();

		if (!empty($newlang)) {
			/* Set Language to the User selected language. */
			$gallery->language = $newlang;
		}
		else {
			/** No new language.
			 * Lets see in which Environment were are and look for a language.
			 * Lets try to determ the used language
			 */
			$gallery->language = getEnvLang();
		}
	}
	else {
		/** We're not embedded.
		 * If we got a ML_mode from config.php we use it
		 * If not we use Mode 2 (Browserlanguage)
		 */
		if (isset($gallery->app->ML_mode)) {
			$ML_mode = $gallery->app->ML_mode;
		}
		else {
			$ML_mode = 2;
		}

		switch ($ML_mode) {
			case 1:
				/* Static Language */
				$gallery->language = getDefaultLanguage();
			break;

			case 3:
				/* Does the user want a new language ?*/
				if (!empty($newlang)) {
					/* Set Language to the User selected language.*/
					$gallery->language = $newlang;
				}
				elseif (isset($gallery->session->language)) {
					/* Maybe we already have a language*/
					$gallery->language = $gallery->session->language;
				}
				else {
					$gallery->language = getDefaultLanguage();
				}
			break;

			default:
				/* Use Browser Language or Userlanguage when mode 2 or any other (wrong) mode*/
				$gallery->language = getBrowserLanguage();

				if (!empty($gallery->user) && $gallery->user->getDefaultLanguage() != '') {
					$gallery->language = $gallery->user->getDefaultLanguage();
				}
			break;
		}
	}

	/* if an alias for the (new or Env) language is given, use it*/
	$gallery->language = getLanguageAlias($gallery->language) ;

	/**
	 *  Fall back to Default Language if :
	 *	- we cant detect Language
	 *	- Nuke/phpBB2 sent an unsupported
	 *	- User sent an undefined
	 */

	if (! isset($nls['language'][$gallery->language])) {
		$gallery->language = getLanguageAlias(getDefaultLanguage());
		/* when we REALLY REALLY cant detect a language */
		if (! isset($nls['language'][$gallery->language])) {
			$gallery->language = 'en_US';
		}
	}

	/* And now set this language into session*/
	$gallery->session->language = $gallery->language;

	/* locale*/
	if (isset($gallery->app->locale_alias[$gallery->language])) {
		$gallery->locale = $gallery->app->locale_alias["$gallery->language"];
	}
	else {
		$gallery->locale = $gallery->language;
	}

	/* Override NUKEs locale :)))*/
	$locale = $gallery->locale;

	/* Check defaults */
	$checklist = array('direction', 'charset') ;

	/**
     * This checks wether the previously defined values are available.
	 * All available values are in $nls
	 * If they are not defined we used the defaults from nls.php
	 */
	foreach($checklist as $check) {
		/* if no ... is given, use default*/
		if ( !isset($nls[$check][$gallery->language])) {
			$gallery->$check = $nls['default'][$check] ;
		}
		else {
			$gallery->$check = $nls[$check][$gallery->language] ;
		}
	}

	/* When all is done do the settings*/

	/* There was previously a != SUNOS check around the LANG= line.  We've determined that it was
	 probably a bogus bug report, since all documentation says this is fine.*/
	putenv("LANG=". $gallery->language);
	putenv("LANGUAGE=". $gallery->language);

	/* This line was added in 1.5-cvs-b190 to fix problems on FreeBSD 4.10*/
	putenv("LC_ALL=". $gallery->language);

	/* Set Locale*/
	setlocale(LC_ALL,$gallery->locale);

	/**
	 * Set Charset header
	 * We do this only if we are not embedded and the "user" wants it.
	 * Because headers might be sent already.
	 */
	if (!headers_sent() && ($sendHeader == true  || ! isset($GALLERY_EMBEDDED_INSIDE))) {
		header('Content-Type: text/html; charset=' . $gallery->charset);
	}

	/**
	 * Test if we're using gettext.
	 * if yes, do some gettext settings.
	 * if not emulate _() function or ngettext()
	 */

	if (gettext_installed()) {
		bindtextdomain($gallery->language. "-gallery_". where_i_am(), dirname(dirname(__FILE__)) . '/locale');
		textdomain($gallery->language. "-gallery_". where_i_am());
	}
	else {
		emulate_gettext($languages_initialized);
	}

	// We test this separate because ngettext() is only available in PHP >=4.2.0 but _() in all PHP4
	if (!ngettext_installed()) {
		emulate_ngettext($languages_initialized);
	}

	$languages_initialized = true;
}

function getTranslationFile() {

	global $gallery;
	static $translationfile;

	if (empty($translationfile)) {
		$filename = dirname(dirname(__FILE__)) . '/locale/' .
		$gallery->language . '/'.
		$gallery->language . '-gallery_' .  where_i_am()  . '.po';
		$translationfile=file($filename);
	}

return $translationfile;
}

/** Substitute ngettext function
 * NOTE: this is the first primitive Step !!
 * It fully ignores the plural definition !!
*/
function emulate_ngettext($languages_initialized = false) {
	global $translation;
	global $gallery;

	if (in_array($gallery->language,array_keys(gallery_languages())) &&
		$gallery->language != 'en_US') {

		$lines=getTranslationFile();
		foreach ($lines as $key => $value) {
		//We trim the String to get rid of cr/lf
			$value=trim($value);
			if (stristr($value, "msgid") &&
				! stristr($lines[$key-1],"fuzzy") && !stristr($value,"msgid_plural")) {
//				echo "\n<br>---SID". $value;
//				echo "\n<br>---PID". $lines[$key+1];
				if (stristr($lines[$key+1],"msgid_plural")) {
					$singular_key=substr($value, 7,-1);
					$translation[$singular_key]=substr(trim($lines[$key+2]),11,-1);
					$plural_key=substr(trim($lines[$key+1]), 14,-1);
					$translation[$plural_key]=substr(trim($lines[$key+3]),11,-1);
//	echo "\n<br>SK". $singular_key;
//	echo "\n<br>ST". $translation[$singular_key];
//	echo "\n<br>PK". $plural_key;
//	echo "\n<br>PT". $translation[$plural_key];
				}
			}
		}
	}

		// Substitute ngettext() function
	if(! $languages_initialized) {
		function ngettext($singular, $quasi_plural,$num=0) {
//			echo "\n<br>----";
//			echo "\nSL: $singular, PL: $quasi_plural, N: $num";
			global $gallery;

			if($gallery->language == 'en_US') {
				if ($num == 1) {
					return $singular;
				}
				else {
					return $quasi_plural;
				}
			}
			else {
				if ($num == 1) {
					if (! empty($GLOBALS['translation'][$singular])) {
						return $GLOBALS['translation'][$singular] ;
					}
					else {
						return $singular;
					}
				}
				else {
					if (! empty($GLOBALS['translation'][$quasi_plural])) {
						return $GLOBALS['translation'][$quasi_plural] ;
					}
					else {
						return $quasi_plural;
					}
				}
			}
		}
	}
}

/**
 *
*/
function emulate_gettext($languages_initialized) {
	global $translation;
	global $gallery;

	if (in_array($gallery->language,array_keys(gallery_languages())) &&
		$gallery->language != 'en_US') {

		$filename = dirname(dirname(__FILE__)) . '/locale/' .
		$gallery->language . '/'.
		$gallery->language . '-gallery_' .  where_i_am()  . '.po';

		$lines=file($filename);

		foreach ($lines as $key => $value) {
			/* We trim the String to get rid of cr/lf */
			$value=trim($value);
			if (stristr($value, "msgid") &&
				! stristr($lines[$key-1],"fuzzy") &&
				! stristr($lines[$key],"msgid_plural") &&
				! stristr($value,"msgid_plural")) {

				$new_key=substr($value, 7,-1);
				$translation[$new_key] = substr(trim($lines[$key+1]),8,-1);
//		echo "\n<br>NK". $new_key;
//		echo "\n<br>NT". $translation[$new_key];
			}
		}
	}

		// Substitute _() gettext function
	if(! $languages_initialized) {
		function _($search) {
			global $gallery;

			if($gallery->language == 'en_US') {
				return $search;
			}
			else {
				if (! empty($GLOBALS['translation'][$search])) {
					return $GLOBALS['translation'][$search] ;
				}
				else {
					return $search;
				}
			}
		}
	}
}

function gettext_installed() {
	if (in_array("gettext", get_loaded_extensions()) && function_exists('gettext') && function_exists('_')) {
		return true;
	}
	else {
		return false;
	}
}

function ngettext_installed() {
	if (in_array("ngettext", get_loaded_extensions()) ||
	  (function_exists('ngettext') && function_exists('dngettext'))) {
		return true;
	}
	else {
		return false;
	}
}

/**
 * @return array 	All languages in this gallery installation
 * @author Jens Tkotz
 */
function gallery_languages() {
	$nls = getNLS();
	return $nls['language'];
}

/**
 * This function tries to find an alias for an given "language".
 * Alias or the original input is returned.
 * @param	string	$language
 * @return 	string  If alias was found that, else the input
 * @author	Jens Tkotz
 */
function getLanguageAlias($language) {
	$nls = getNLS();

	if (isset($nls['alias'][$language])) {
	   return $nls['alias'][$language];
	}
	else {
		return $language;
	}
}

/**
 * returns all language relative that gallery could collect.
 */
function getNLS() {
	static $nls;

	if (empty($nls)) {
		$nls = array();
		// Load defaults
		include (dirname(dirname(__FILE__)) . '/nls.php');

		$modules = array('config','core');
		$dir = dirname(dirname(__FILE__)) . '/locale';
		if (fs_is_dir($dir) && is_readable($dir) && $handle = fs_opendir($dir)) {
			while ($dirname = readdir($handle)) {
				if (ereg("^([a-z]{2}_[A-Z]{2})", $dirname)) {
					$locale = $dirname;
					$fc = 0;
					foreach ($modules as $module) {
						if (gettext_installed()) {
							if (fs_file_exists(dirname(dirname(__FILE__)) . "/locale/$dirname/$locale-gallery_$module.po")) $fc++;
						}
						else {
							if (fs_file_exists(dirname(dirname(__FILE__)) . "/locale/$dirname/LC_MESSAGES/$locale-gallery_$module.mo")) $fc++;
						}
					}

					if (fs_file_exists(dirname(dirname(__FILE__)) . "/locale/$dirname/$locale-nls.php") && $fc==sizeof($modules)) {
						include (dirname(dirname(__FILE__)) . "/locale/$dirname/$locale-nls.php");
					}
				}
			}
			closedir($handle);
		}
	}

	return $nls;
}

function i18n($buf) {
	return $buf;
}

function isSupportedCharset($charset) {
	$supportedCharsets=array(
		'UTF-8',
		'ISO-8859-1',
		'ISO-8859-15',
		'cp1252',
		'BIG5',
		'GB2312',
		'BIG5-HKSCS',
		'Shift_JIS',
		'EUC-JP'
	);

	$supportedCharsetsNewerPHP=array(
		'cp866',
		'cp1251',
		//'KOI8-R'
	);

	/**
	 * Check if we are using PHP >= 4.1.0
	 * If yes, we can use 3rd Parameter so e.g. titles in chinese BIG5 or UTF8 are displayed correct.
	 * Otherwise they are messed.
	 * Not all Gallery Charsets are supported by PHP, so only thoselisted are recognized.
	 */
	if (function_exists('version_compare')) {
		if ( (version_compare(phpversion(), "4.1.0", ">=") && in_array($charset, $supportedCharsets)) ||
			 (version_compare(phpversion(), "4.3.2", ">=") && in_array($charset, $supportedCharsetsNewerPHP)) ) {
			return true;
		}
		else {
			/* Unsupported Charset*/
			return false;
		}
	}
	else {
		/* PHP too old*/
		return false;
	}
}

/**
 * Gallery Version of htmlentities
 * Enhancement: Depending on PHP Version and Charset use
 * optional 3rd Parameter of php's htmlentities
 */
function gallery_htmlentities($string) {
	global $gallery;

	if (isSupportedCharset($gallery->charset)) {
		return htmlentities($string,ENT_COMPAT ,$gallery->charset);
	}
	else {
		return htmlentities($string);
	}
}

/**
 * Convert all HTML entities to their applicable characters
 */
function unhtmlentities($string) {
	global $gallery;

	if (empty($string)) {
		return '';
	}

	if (function_exists('html_entity_decode')) {
		$nls = getNLS();

		if (isset($gallery->language) && isset($nls['charset'][$gallery->language])) {
			$charset = $nls['charset'][$gallery->language];
		}
		else {
			$charset = $nls['default']['charset'];
		}

		if (isSupportedCharset($charset) && strtolower($charset) != 'utf-8') {
			$return = html_entity_decode($string,ENT_COMPAT ,$charset);
		}
		else {
			// For unsupported charsets you may do this:
			$trans_tbl = get_html_translation_table (HTML_ENTITIES);
			$trans_tbl = array_flip ($trans_tbl);
			$return = strtr ($string, $trans_tbl);
		}
	}
	else {
		// For users with PHP prior to 4.3.0 you may do this:
		$trans_tbl = get_html_translation_table (HTML_ENTITIES);
		$trans_tbl = array_flip ($trans_tbl);
		$return = strtr ($string, $trans_tbl);
	}

return $return;
}

/**
 * These are custom fields that are turned on and off at an album
 * level, and are populated for each photo automatically, without the
 * user typing values.  The $value of each pair should be translated
 * as appropriate in the ML version.
 */
function automaticFieldsList() {
	return array(
        'Upload Date'   => gTranslate('common', "Upload date"),
        'Capture Date' 	=> gTranslate('common', "Capture date"),
        'Dimensions' 	=> gTranslate('common', "Image size"),
        'EXIF'          => gTranslate('common', "Additional EXIF data"));
}

/** These are custom fields which can be entered manual by the User
 * Since they are used often, we translated them.
 */
function translateableFields() {
	return array(
		'title'			=> gTranslate('common', "title"),
		'Title'			=> gTranslate('common', "Title"),
		'Description'	=> gTranslate('common', "Description"),
		'description'	=> gTranslate('common', "description"),
        'AltText'		=> gTranslate('common', "Alt text / Tooltip"),
	);
}

/**
 * This "block" returns either a combobox with available languages or show flags for them.
 * Both are only displayed if at least 2 languages are available.
 * @author	Jens Tkotz
 */
function languageSelector() {
	global $gallery, $GALLERY_EMBEDDED_INSIDE, $GALLERY_EMBEDDED_INSIDE_TYPE;

	$html = '';

	if ($gallery->app->ML_mode == 3 && !$gallery->session->offline && sizeof($gallery->app->available_lang) > 1) {
		if($gallery->app->show_flags !='yes') {
			$html .= '<script language="JavaScript" type="text/javascript">';
			$html .= "\n". 'function ML_reload() {';
			$html .= "\n". 'var newlang=document.MLForm.newlang[document.MLForm.newlang.selectedIndex].value ;';
			$html .= "\n". 'window.location.href=newlang;';
			$html .= "\n". '}';
			$html .= "\n" . '</script>';
		}

		$html .= makeFormIntro('#', array('name' => 'MLForm', 'class' => 'langselector'));
		$langSelectTable = new galleryTable();
		$langSelectTable->setColumnCount(20);
		$langSelectTable->setAttrs(array('align' => langRight()));

		$nls = getNLS();

		foreach ($gallery->app->available_lang as $value) {
			/**
			 * We only allow show languages which are available in gallery.
			 * These could differ to the languages defined in config.php.
			*/
			if (! isset($nls['language'][$value])) continue;

			if (isset($GALLERY_EMBEDDED_INSIDE) && $GALLERY_EMBEDDED_INSIDE=='nuke') {
				if ($GALLERY_EMBEDDED_INSIDE_TYPE == 'postnuke') {
					/* postnuke */
					if (! isset($nls['postnuke'][$value])) continue;
					$new_lang = $nls['postnuke'][$value];
				}
				else {
					/* phpNuke, nsnNuke or cpgNuke */
					if (! isset($nls['phpnuke'][$value])) continue;
					$new_lang = $nls['phpnuke'][$value];
				}
			}
			else {
				$new_lang = $value;
			}

			/* now we build the URL according to the new language */
			$request_url = $_SERVER['REQUEST_URI'];
			$pos = strpos($request_url, "newlang");
			if ($pos >0) {
				$request_url = substr($request_url,0,$pos-1);
			}

			$url = htmlspecialchars(addUrlArg($request_url, "newlang=$new_lang"));

			/* Show pulldown or flags */
			if($gallery->app->show_flags !='yes') {
				$options[$url] = $nls['language'][$value];
			}
			else {
				$flagname = $value;
				$flagImage = "<img src=\"". $gallery->app->photoAlbumURL . "/locale/$flagname/flagimage/$flagname.gif\" alt=\"" .$nls['language'][$value] . "\" title=\"" .$nls['language'][$value] . "\">";

				if ($gallery->language != $value) {
					$langSelectTable->addElement(array('content' => "<a href=\"$url\">$flagImage</a>"));
				}
				else {
					$langSelectTable->addElement(array(
						'content' => $flagImage,
						'cellArgs' => array('style' => 'padding-bottom:10px')
					));
				}
			}
		}

		if($gallery->app->show_flags !='yes') {
			$content = drawSelect('newlang',
									$options,
									$nls['language'][$gallery->language],
									1,
									array('style' => 'font-size:8pt;', 'onChange' => 'ML_reload()')
			);

			$langSelectTable->addElement(array('content' => $content));
		}

		$html .= $langSelectTable->render();
		$html .= '</form><br clear="all">';
	}

	return $html;
}

/**
 * @return string
 * @author Jens Tkotz
 */
function langLeft() {
	global $gallery;

	if ($gallery->direction == 'ltr') {
		return 'left';
	}
	else {
		return 'right';
	}
}

/**
 * @return string
 * @author Jens Tkotz
 */
function langRight() {
	global $gallery;

	if ($gallery->direction == 'ltr') {
		return 'right';
	}
	else {
		return 'left';
	}
}
?>