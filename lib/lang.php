<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2004 Bharat Mediratta
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

function pluralize_n($amt, $one, $more, $none) {
        switch ($amt) {
                case 0 :
                        return $none;
                        break;
                case 1 :
			return $one;
                        break;

                default :
                        return "$amt $more";
                        break;
        }
}

function get_BrowserLanguage() {
       // Detect Browser Language

       if (isset($HTTP_SERVER_VARS["HTTP_ACCEPT_LANGUAGE"])) {
		$lang = explode (",", $HTTP_SERVER_VARS["HTTP_ACCEPT_LANGUAGE"]);
		$spos=strpos($lang[0],";");
		if ($spos >0) {
			$lang[0]=substr($lang[0],0,$spos);
		}
		$lang_pieces=explode ("-",$lang[0]);

		if (strlen($lang[0]) ==2) {
			return $lang[0] ."_".strtoupper($lang[0]);
		} else {
			return strtolower($lang_pieces[0]). "_".strtoupper($lang_pieces[1]) ;
		}
	}
}

/*
** Set Gallery Default:
** - language
** - charset
** - direction
** - alignment
*/

function setLangDefaults() {
	global $gallery;

	$gallery->language 	= 'en_US';
	$gallery->charset  	= $nls['default']['charset'];
	$gallery->direction	= $nls['default']['direction'];
	$gallery->align		= $nls['default']['alignment'];
}

function initLanguage() {

	global $gallery, $GALLERY_EMBEDDED_INSIDE, $GALLERY_EMBEDDED_INSIDE_TYPE;
	global $HTTP_SERVER_VARS, $HTTP_COOKIE_VARS, $HTTP_GET_VARS, $HTTP_SESSION_VARS;

	// $locale is *NUKEs locale var
	global $locale ;

	$nls = getNLS();

	/* Set Defaults, they may be overwritten. */
	setLangDefaults();

	// before we do any tests or settings test if we are in mode 0
	// If so, we skip language settings at all

	if (isset($gallery->app->ML_mode)) {
		// Mode 0 means no Multilanguage at all.
		if($gallery->app->ML_mode == 0) {
			// Maybe PHP has no gettext, then we have to substitute _()
			if (! gettext_installed()) {
				function _($string) {
					return $string ;
				}
			}
			/* Skip rest*/
			return;
		}
	}

	$gallery->browser_language=get_BrowserLanguage();

	// Does the user wants a new lanuage ?
	if (isset($HTTP_GET_VARS['newlang'])) {
		$newlang=$HTTP_GET_VARS['newlang'];
	}

	/**
	 ** We have now several Ways. Embedded (PostNuke, phpNuke, phpBB2) or not embedded
	 ** Now we (try) to do the language settings
	 ** 
	 ** Note: ML_mode is only used when not embedded
	 **/

	if (isset($GALLERY_EMBEDDED_INSIDE_TYPE)) {
		/* Gallery is embedded */

		/* Gallery can set nukes language, for phpBB2 this is not possible.
		** So gallery will always use phpBB2's language.
		*/

		if (!empty($newlang)) {
			// if there was a new language given, use it for nuke
			$gallery->nuke_language=$newlang;
		} else {
			/* No new language.
			** Lets see in which Environment were are and look for a language.
			*/
			
			switch ($GALLERY_EMBEDDED_INSIDE_TYPE) {
			case 'postnuke':
				if (isset($HTTP_SESSION_VARS['PNSVlang'])) {
					$gallery->nuke_language=$HTTP_SESSION_VARS['PNSVlang'];
				}

			case 'phpnuke':
				if (isset($HTTP_COOKIE_VARS['lang'])) {
					$gallery->nuke_language=$HTTP_COOKIE_VARS['lang'];
				}

				/* This is executed for both nukes */
				if (isset ($gallery->session->language) && ! isset($gallery->nuke_language)) {
					$gallery->language = $gallery->session->language;
				} else if (isset ($nls['alias'][$gallery->nuke_language])) {
					$gallery->language=$nls['alias'][$gallery->nuke_language];
				}
			break;
			case 'phpBB2':
				/* Gallery will always use phpBB2's language, so we override the mode to 1.
				** And no pulldown or flags appear.
				*/
				global $board_config;
				$gallery->app->ML_mode=1;
				if (isset($board_config['default_lang'])) {
					if (isset ($nls['alias'][$board_config['default_lang']])) {
						$gallery->language = $nls['alias'][$board_config['default_lang']];
					}
				}				
			break;
			}
		}
	} else {
		// We're not in Nuke
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
					// Use Alias if
					if (isset($nls['alias'][$newlang])) $newlang=$nls['alias'][$newlang] ;
					// Set Language to the User selected language (if this language is defined)
					if (isset($nls['language'][$newlang])) {
						$gallery->language=$newlang;
					}
				} elseif (isset($gallery->session->language)) {
					//maybe we already have a language
					$gallery->language=$gallery->session->language;
				}
				break;
			default:
				// Use Browser Language or Userlanguage 
				// when mode 2 or any other (wrong) mode
				if (!empty($gallery->user) && 
						$gallery->user->getDefaultLanguage() != "") {
					$gallery->language = $gallery->user->getDefaultLanguage();
				} elseif (isset($gallery->browser_language)) {
					$gallery->language=$gallery->browser_language;
				}
				break;
		}
	}

	/**
	 **  Fall back to Default Language if :
	 **	- we cant detect Language
	 **	- Nuke/phpBB2 sent an unsupported
	 **	- User sent an undefined
	 **/
	if (empty($gallery->language)) {
		if (isset($gallery->app->default_language)) {
			$gallery->language = $gallery->app->default_language;
		} elseif(isset($gallery->browser_language)) {
			$gallery->language = $gallery->browser_language;
		} else {
			// when we REALLY REALLY cant detect a language
			$gallery->language="en_US";
		}
	}

	// if an alias for a language is given, use it
	//
	if (isset($nls['alias'][$gallery->language])) {
		$gallery->language = $nls['alias'][$gallery->language] ;
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

	foreach($checklist as $check) {
		// if no ... is given, use default
		if ( !isset($nls[$check][$gallery->language])) {
			$gallery->$check = $nls['default'][$check] ;
		} else {
			$gallery->$check = $nls[$check][$gallery->language] ;
		}
	}

	// When all is done do the settings
	//
	if (getOS() != OS_SUNOS) {
		putenv("LANG=". $gallery->language);
	}
	putenv("LANGUAGE=". $gallery->language);

	// Set Locale
	setlocale(LC_ALL,$gallery->locale);

	// Set Charset
	// Only when we're not in nuke, because headers might be sent already.
	if (! isset($GALLERY_EMBEDDED_INSIDE)) {
		header('Content-Type: text/html; charset=' . $gallery->charset);
	}


	/**
	 ** Test if we're using gettext.
	** if yes, do some gettext settings.
	 ** if not emulate _() function
	 **/

	if (gettext_installed()) {
		$bindtextdomain=bindtextdomain($gallery->language. "-gallery_". where_i_am(), dirname(dirname(__FILE__)) . '/locale');
		textdomain($gallery->language. "-gallery_". where_i_am());

	}  else {
		emulate_gettext();
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
			if (stristr($value, "msgid") && ! stristr($lines[$key-1],"fuzzy")) {
				$new_key=substr($value, 7,-1);
				$translation[$new_key]=substr(trim($lines[$key+1]),8,-1);
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

//returns all languages in this gallery installation
function gallery_languages() {
	$nls=getNLS();
	return $nls['language'];
}

function getNLS() {
	static $nls;

	if (empty($nls)) {

		$nls=array();
		// Load defaults
		include (dirname(dirname(__FILE__)) . '/nls.php');

		$modules=array('config','core');
		$dir=dirname(dirname(__FILE__)) . '/locale';
	       	if (fs_is_dir($dir) && is_readable($dir) && $handle = fs_opendir($dir)) {
			while ($dirname = readdir($handle)) {
				if (ereg("^([a-z]{2}_[A-Z]{2})", $dirname)) {
					$locale=$dirname;
					$fc=0;
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

function unhtmlentities ($string)
{
	global $gallery;

	if (function_exists('html_entity_decode')) {
		$nls=getNLS();
		if (isset ($nls['charset'][$gallery->language])) {
			$charset=$nls['charset'][$gallery->language];
		} else {
			$charset=$nls['default']['charset'];
		}
		$return= html_entity_decode($string,ENT_COMPAT,$charset);
	} else {
		// For users prior to PHP 4.3.0 you may do this:
		$trans_tbl = get_html_translation_table (HTML_ENTITIES);
		$trans_tbl = array_flip ($trans_tbl);
		$return = strtr ($string, $trans_tbl);
	}

return $return;
}

?>
