<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2005 Bharat Mediratta
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

/*
** This function is a wrapper around ngettext for two reasons
** 1.) We can use %s and %d in translation
** 2.) We can use a special "none" without modifying the plural definition.
** Note: The redundant $count is always needed, when you use %d
**
** Example of normal use:
** pluralize_n2(ngettext("1 car", "5 cars", $numCars), $numCars, _("No cars"));
*/
function pluralize_n2($singPlu, $count, $none='') {
	if ($count == 0 && $none != '') {
		return $none;
	} else {
		return sprintf($singPlu, $count);
	}
}


/* Detect the first Language of users Browser
** Some Browser only send 2 digits like he or de.
** This is caught later with the aliases
*/
function getBrowserLanguage() {
	if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
		$lang = explode (",", $_SERVER["HTTP_ACCEPT_LANGUAGE"]);

		/* Maybe there are some extra infos we dont need, so we strip them. */
		$spos=strpos($lang[0],";");
		if ($spos >0) {
			$lang[0]=substr($lang[0],0,$spos);
		}
		
		/* browser may send aa-bb, then we convert to aa_BB */
		$lang_pieces=explode ("-",$lang[0]);
		if (strlen($lang[0]) >2) {
			$browserLang=strtolower($lang_pieces[0]). "_".strtoupper($lang_pieces[1]) ;
		} else {
			$browserLang=$lang[0];
		}
	}
	else {
		$browserLang=false;
	}
	
	return $browserLang;
}


/*
** Set Gallery Default:
** - language
** - charset
** - direction
** - alignment
*/
function setLangDefaults($nls) {
	global $gallery;

	$gallery->language 	= 'en_US';
	$gallery->charset  	= $nls['default']['charset'];
	$gallery->direction	= $nls['default']['direction'];
	$gallery->align		= $nls['default']['alignment'];
}

/*
** This function tries to get the languge given by the Environment.
** if no language is found, or Gallery was not able to get it, NULL is returned.
*/
function getEnvLang() {

	global $GALLERY_EMBEDDED_INSIDE_TYPE;

	global $board_config;				/* Needed for phpBB2 	*/
	global $_CONF;					/* Needed for GeekLog	*/
	global $mosConfig_locale;			/* Needed for Mambo	*/
	global $currentlang;				/* Needed for CPGNuke	*/

	$envLang = NULL;

	switch ($GALLERY_EMBEDDED_INSIDE_TYPE) {
		case 'postnuke':
			if (isset($_SESSION['PNSVlang'])) {
				$envLang = $_SESSION['PNSVlang'];
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
			if (isset($mosConfig_locale)){
				$envLang = $mosConfig_locale;
			}				
		break;

		case 'cpgnuke':
			if (isset($currentlang)){
				$envLang = $currentlang;
			}				
		break;

		default:
			return NULL;
		break;
	}

	return $envLang;
}

/*
** In some Environments we dont want to allow the user
** to change the language.
** In this case we override Mode 3 with Mode 1 and
** Gallery runs in the language the Environment use.
*/
function forceStaticLang() {
	global $GALLERY_EMBEDDED_INSIDE_TYPE;
	global $gallery;

	$useStatic=array('mambo', 'phpBB2', 'GeekLog');

	if (in_array($GALLERY_EMBEDDED_INSIDE_TYPE, $useStatic)) {
		$gallery->app->ML_mode=1;
	}
}	

function initLanguage($sendHeader=true) {
	static $languages_initialized = false;

	global $gallery, $GALLERY_EMBEDDED_INSIDE, $GALLERY_EMBEDDED_INSIDE_TYPE;

	// $locale is *NUKEs locale var
	global $locale ;

	$nls = getNLS();

	/* Set Defaults, they may be overwritten. */
	setLangDefaults($nls);

	// before we do any tests or settings test if we are in mode 0
	// If so, we skip language settings at all

	// Mode 0 means no Multilanguage at all.
	if (isset($gallery->app->ML_mode) && $gallery->app->ML_mode == 0 && !$languages_initialized) {
		// Maybe PHP has no (n)gettext, then we have to substitute _() and ngettext
		if (!gettext_installed()) {
			function _($string) {
				return $string ;
			}
		}
		if (!ngettext_installed()) {
			function ngettext($singular, $quasi_plural,$num=0) {
                       		if ($num == 1) {
                               		return $singular;
	                        } else {
       		                        return $quasi_plural;
               		        }
			}
		}

		/* Skip rest*/
		$languages_initialized = true;
		return;
	}


	/* 
	** Does the user wants a new lanuage ?
	** This is used in Standalone and *Nuke
	*/
	if (isset($_GET['newlang'])) {
		$newlang=$_GET['newlang'];
	}

	/**
	 ** Note: ML_mode is only used when not embedded
	 **/

	if (isset($GALLERY_EMBEDDED_INSIDE_TYPE)) {
		/* Gallery is embedded

		/* Gallery can set nukes language.
		** For phpBB2, GeekLog and Mambo this is not possible, Gallery will always use their language.
		*/
		forceStaticLang();

		if (!empty($newlang)) {
			// Set Language to the User selected language.
			$gallery->language=$newlang;
		} else {
			/* No new language.
			** Lets see in which Environment were are and look for a language.
			** Lets try to determ the used language
			*/ 
			$gallery->language = getEnvLang();
		}
	} else {
		// We're not embedded.
		// If we got a ML_mode from config.php we use it
		// If not we use Mode 2 (Browserlanguage)

		if (isset($gallery->app->ML_mode)) {
			$ML_mode=$gallery->app->ML_mode;
		} else {
			$ML_mode=2;
		}

		switch ($ML_mode) {
			case 1:
				//Static Language
				$gallery->language = $gallery->app->default_language;
				break;
			case 3:
				// Does the user want a new language ?
				if (!empty($newlang)) {
					// Set Language to the User selected language.
					$gallery->language=$newlang;
				} elseif (isset($gallery->session->language)) {
					//maybe we already have a language
					$gallery->language=$gallery->session->language;
				} elseif (isset($gallery->app->default_language)) {
					// Maybe we have a defaultlanguage set in config.php
		                        $gallery->language = $gallery->app->default_language;
				}
				break;
			default:
				// Use Browser Language or Userlanguage 
				// when mode 2 or any other (wrong) mode

				$gallery->browser_language=getBrowserLanguage();

				if (!empty($gallery->user) && $gallery->user->getDefaultLanguage() != "") {
					$gallery->language = $gallery->user->getDefaultLanguage();
				} elseif (isset($gallery->browser_language)) {
					$gallery->language=$gallery->browser_language;
				}
				break;
		}
	}

	// if an alias for the (new or Env) language is given, use it
	if (isset($nls['alias'][$gallery->language])) {
		$gallery->language = $nls['alias'][$gallery->language] ;
	}

	/**
	 **  Fall back to Default Language if :
	 **	- we cant detect Language
	 **	- Nuke/phpBB2 sent an unsupported
	 **	- User sent an undefined
	 **/

	if (! isset($nls['language'][$gallery->language])) {
		if (isset($gallery->app->default_language)) {
			$gallery->language = $gallery->app->default_language;
		} elseif(isset($gallery->browser_language)) {
			$gallery->language = $gallery->browser_language;
		} else {
			// when we REALLY REALLY cant detect a language
			$gallery->language="en_US";
		}
	}

	// And now set this language into session
	$gallery->session->language = $gallery->language;

	// locale
	if (isset($gallery->app->locale_alias[$gallery->language])) {
		$gallery->locale=$gallery->app->locale_alias["$gallery->language"];
	} else {
		$gallery->locale=$gallery->language;
	}

	// Override NUKEs locale :)))	
	$locale=$gallery->locale;

	// Check defaults :
	$checklist=array('direction', 'charset', 'alignment') ;

	/*
	** This checks wether the previously defined values are available.
	** All available values are in $nls
	** If they are not defined we used the defaults from nls.php
	*/
	foreach($checklist as $check) {
		// if no ... is given, use default
		if ( !isset($nls[$check][$gallery->language])) {
			$gallery->$check = $nls['default'][$check] ;
		} else {
			$gallery->$check = $nls[$check][$gallery->language] ;
		}
	}

	// When all is done do the settings
	
	// There was previously a != SUNOS check around the LANG= line.  We've determined that it was
	// probably a bogus bug report, since all documentation says this is fine.
	putenv("LANG=". $gallery->language);
	putenv("LANGUAGE=". $gallery->language);
	// This line was added in 1.5-cvs-b190 to fix problems on FreeBSD 4.10
	putenv("LC_ALL=". $gallery->language);

	// Set Locale
	setlocale(LC_ALL,$gallery->locale);

	/* 
	** Set Charset header
	** We do this only if we are not embedded and the "user" wants it.
	** Because headers might be sent already.
	*/
	if (! isset($GALLERY_EMBEDDED_INSIDE) || $sendHeader == false) {
		header('Content-Type: text/html; charset=' . $gallery->charset);
	}

	/*
	** Test if we're using gettext.
	** if yes, do some gettext settings.
	** if not emulate _() function or ngettext()
	**/

	if (gettext_installed()) {
		$bindtextdomain=bindtextdomain($gallery->language. "-gallery_". where_i_am(), dirname(dirname(__FILE__)) . '/locale');
		textdomain($gallery->language. "-gallery_". where_i_am());

	} elseif (!$languages_initialized) {
		emulate_gettext();
	}

	// We test this separate because ngettext() is only available in PHP >=4.2.0 but _() in all PHP4
	if (!ngettext_installed() && !$languages_initialized) {
		emulate_ngettext();
	}

	$languages_initialized = true;
}


function getTranslationFile() {

	global $gallery;
	static $translationfile;

	if (empty($translationfile)) {
		$filename=dirname(dirname(__FILE__)) . '/locale/' . $gallery->language . '/'. $gallery->language . '-gallery_' .  where_i_am()  . '.po';
		$translationfile=file($filename);
	}

return $translationfile;
}

/* Substitute ngettext function
** NOTE: this is the first primitive Step !!
** It fully ignores the plural definition !!
*/
function emulate_ngettext() {


	global $translation;
	global $gallery;

	if (in_array($gallery->language,array_keys(gallery_languages())) &&
		$gallery->language != 'en_US') {
		$lines=getTranslationFile();
		foreach ($lines as $key => $value) {
		//We trim the String to get rid of cr/lf
			$value=trim($value);
			if (stristr($value, "msgid") && ! stristr($lines[$key-1],"fuzzy") && !stristr($value,"msgid_plural")) {
//				echo "\n<br>---SID". $value;
//					echo "\n<br>---PID". $lines[$key+1];
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
		// Substitute ngettext() function
		function ngettext($singular, $quasi_plural,$num=0) {
//			echo "\n<br>----";
//			echo "\nSL: $singular, PL: $quasi_plural, N: $num";
			if ($num == 1) {
				if (! empty($GLOBALS['translation'][$singular])) {
					return $GLOBALS['translation'][$singular] ;
				} else {
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
	else {
		// There is no translation file or we are using original (en_US), so just return what we got
		function ngettext($singular, $quasi_plural,$num=0) {
			if ($num == 1) {
				return $singular;
			} else {
				return $quasi_plural;
			}
		}
	}
}

function emulate_gettext() {
	global $translation;
	global $gallery;

	if (in_array($gallery->language,array_keys(gallery_languages())) &&
		$gallery->language != 'en_US') {
		$filename=dirname(dirname(__FILE__)) . '/locale/' . $gallery->language . '/'. $gallery->language . '-gallery_' .  where_i_am()  . '.po';
		$lines=file($filename);

		foreach ($lines as $key => $value) {
			/* We trim the String to get rid of cr/lf */
			$value=trim($value);
			if (stristr($value, "msgid") 
				&& ! stristr($lines[$key-1],"fuzzy") 
				&& ! stristr($lines[$key],"msgid_plural")
				&& ! stristr($value,"msgid_plural")) {
				$new_key=substr($value, 7,-1);
				$translation[$new_key]=substr(trim($lines[$key+1]),8,-1);
//		echo "\n<br>NK". $new_key;
//		echo "\n<br>NT". $translation[$new_key];
			}
		}
		// Substitute _() gettext function
		function _($search) {
			if (! empty($GLOBALS['translation'][$search])) {
				return $GLOBALS['translation'][$search] ;
			}
			else {
				return $search;
			}
		}
	}
	// There is no translation file or we are using original (en_US), so just return what we got
	else {
		function _($search) {
			return $search;
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
	if (in_array("ngettext", get_loaded_extensions()) || function_exists('ngettext')) {
		return true;
	}
	else {
		return false;
	}
}


/* returns all languages in this gallery installation */
function gallery_languages() {
	$nls = getNLS();
	return $nls['language'];
}

/* returns all language relative that gallery could collect. */
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
			} else {
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
		'KOI8-R'
	);

        /*
        ** Check if we are using PHP >= 4.1.0
        ** If yes, we can use 3rd Parameter so e.g. titles in chinese BIG5 or UTF8 are displayed correct.
        ** Otherwise they are messed.
        ** Not all Gallery Charsets are supported by PHP, so only thoselisted are recognized.
        */
	if (function_exists('version_compare')) {
		if ( (version_compare(phpversion(), "4.1.0", ">=") && in_array($charset, $supportedCharsets)) ||
		     (version_compare(phpversion(), "4.3.2", ">=") && in_array($charset, $supportedCharsetsNewerPHP)) ) {
			return true;
		} else {
			// Unsupported Charset
			return false;
		}
	} else {
		// PHP too old
		return false;
	}
}
	
/* Gallery Version of htmlentities
** Enhancement: Depending on PHP Version and Charset use 
** optional 3rd Parameter of php's htmlentities
*/
function gallery_htmlentities($string) {
	global $gallery;

	if (isSupportedCharset($gallery->charset)) {
		return htmlentities($string,ENT_COMPAT ,$gallery->charset);
	} else {
		return htmlentities($string);
        }
}

/*
** Convert all HTML entities to their applicable characters
*/
function unhtmlentities ($string) {
	global $gallery;

	if (empty($string)) {
		return "";
	}

	if (function_exists('html_entity_decode')) {
		$nls=getNLS();
		if (isset ($nls['charset'][$gallery->language])) {
			$charset=$nls['charset'][$gallery->language];
		} else {
			$charset=$nls['default']['charset'];
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
	} else {
		// For users with PHP prior to 4.3.0 you may do this:
		$trans_tbl = get_html_translation_table (HTML_ENTITIES);
		$trans_tbl = array_flip ($trans_tbl);
		$return = strtr ($string, $trans_tbl);
	}

return $return;
}

/* These are custom fields that are turned on and off at an album
 * level, and are populated for each photo automatically, without the
 * user typing values.  The $value of each pair should be translated
 * as appropriate in the ML version.
 */
function automaticFieldsList() {
        return array(
		'Upload Date' 	=> _("Upload Date"),
                'Capture Date' 	=> _("Capture Date"),
                'Dimensions' 	=> _("Image Size"),
                'EXIF' 		=> _("Additional EXIF Data"));
}

/* These are custom fields which can be entered manual by the User
** Since they are used often, we translated them.
*/
function translateableFields() {
	return array(
		'Title'		=> _("Title"),
		'Description'	=> _("Description"),
		'description'	=> _("description"),
		'AltText'	=> _("Alt Text / onMouseOver")
	);
}

?>
