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

function getBrowserLanguage() {
	/* Detect the first Language of users Browser
	** Some Browser only send 2 digits like he or de.
	** The we generate he_HE, de_DE and so on. if this is wrong, 
	** like he_HE, this is catched later with the aliases
	*/

	global $HTTP_SERVER_VARS;

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

function setLangDefaults($nls) {
	/*
	** Set Gallery Default:
	** - language
	** - charset
	** - direction
	** - alignment
	*/

	global $gallery;

	$gallery->language 	= 'en_US';
	$gallery->charset  	= $nls['default']['charset'];
	$gallery->direction	= $nls['default']['direction'];
	$gallery->align		= $nls['default']['alignment'];
}

function getEnvLang() {

	global $GALLERY_EMBEDDED_INSIDE_TYPE;

	global $HTTP_SESSION_VARS;		/* Needed for PostNuke 	*/
	global $HTTP_COOKIE_VARS;		/* Needed for phpNuke 	*/
	global $board_config;			/* Needed for phpBB2 	*/
	global $_CONF;				/* Needed for GeekLog	*/

	switch ($GALLERY_EMBEDDED_INSIDE_TYPE) {
		case 'postnuke':
			if (isset($HTTP_SESSION_VARS['PNSVlang'])) {
				return $HTTP_SESSION_VARS['PNSVlang'];
			}

		break;

		case 'phpnuke':
		case 'nsnnuke':
			if (isset($HTTP_COOKIE_VARS['lang'])) {
				return $HTTP_COOKIE_VARS['lang'];
			}

		break;

		case 'phpBB2':
			if (isset($board_config['default_lang'])) {
				return $board_config['default_lang'];
			}				
		break;

		case 'GeekLog':
			if (isset($_CONF['language'])) {
				return $_CONF['language'];
			} else if (isset($_CONF['locale'])) {
				return $_CONF['locale'];
			}				
		break;

		default:
			echo "false";
			return FALSE;
		break;
	}
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

	$useStatic=array('phpBB2', 'GeekLog');

	if (in_array($GALLERY_EMBEDDED_INSIDE_TYPE, $useStatic)) {
		$gallery->app->ML_mode=1;
	}
}	

function initLanguage() {

	global $gallery, $GALLERY_EMBEDDED_INSIDE, $GALLERY_EMBEDDED_INSIDE_TYPE;
	global $HTTP_SERVER_VARS, $HTTP_COOKIE_VARS, $HTTP_GET_VARS, $HTTP_SESSION_VARS;

	// $locale is *NUKEs locale var
	global $locale ;

	$nls = getNLS();

	/* Set Defaults, they may be overwritten. */
	setLangDefaults($nls);

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

	/* 
	** Does the user wants a new lanuage ?
	** This is used in Standalone and *Nuke
	*/
	if (isset($HTTP_GET_VARS['newlang'])) {
		$newlang=$HTTP_GET_VARS['newlang'];
	}

	/**
	 ** Note: ML_mode is only used when not embedded
	 **/

	if (isset($GALLERY_EMBEDDED_INSIDE_TYPE)) {
		/* Gallery is embedded

		/* Gallery can set nukes language, for phpBB2, GeekLog etc. this is not possible.
		** So gallery will always use their language.
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
	//
	if (getOS() != OS_SUNOS) {
		putenv("LANG=". $gallery->language);
	}
	putenv("LANGUAGE=". $gallery->language);

	// Set Locale
	setlocale(LC_ALL,$gallery->locale);

	/* 
	** Set Charset header
	** Only when we're not embedded, because headers might be sent already.
	*/
	if (! isset($GALLERY_EMBEDDED_INSIDE)) {
		header('Content-Type: text/html; charset=' . $gallery->charset);
	}


	/*
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
		'Description'	=> _("Description"));
}

?>
