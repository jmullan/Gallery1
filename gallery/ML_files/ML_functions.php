<?php

require_once ($gallery->path ."ML_files/ML_lang_alias.php");
global $HTTP_SERVER_VARS;

// Detec Browser Language
//
	$lang = explode (",", $HTTP_SERVER_VARS["HTTP_ACCEPT_LANGUAGE"]);
        $lang_pieces=explode ("-",$lang[0]);

        if (strlen($lang[0]) ==2) {
                $gallery->ML->browser_language=$lang[0] ."_".strtoupper($lang[0]);
        }
        else {
		$gallery->ML->browser_language=strtolower($lang_pieces[0])."_".strtoupper($lang_pieces[1]) ;
        }
	//echo "<br>Browser-Language:" . $gallery->ML->browser_language;

// Check if we already have a language
// Use this only if user dont want Browserlanguage only

//echo "<br>Session-Language:" . $gallery->session->language;
if ($gallery->session->language and $gallery->ML->mode > 1) {
	$gallery->ML->language=$gallery->session->language;
}


// Check in wich Mode or Nuke and set language
//
//echo "<br>Language:" . $gallery->ML->language;
//echo "<br>Mode:" . $gallery->ML->mode;
	if ($gallery->ML->mode == 2 and ! $gallery->ML->language) {
		// Use Browser Language
		$gallery->ML->language=$gallery->ML->browser_language;
	//	echo "<br>Use Browserlanguage =>" . $gallery->ML->language;
	} 
	elseif ($gallery->ML->mode == 3 and ($newlang)) {
		// Check New language
		// Use Alias if
		if ($nls['alias'][$newlang]) $newlang=$nls['alias'][$newlang] ;
		// use Language if its okay, otherwise use default
		// Set Language to the User selected language
		if ($nls['languages'][$newlang] ||$nuke_langname[$newlang]) {
			$gallery->ML->language=$newlang;
		}
		else {
			$gallery->ML->language = $gallery->ML->default['language'];
		}	
	//	echo "<br>Use User Language => " . $gallery->ML->language;
	} 
	elseif ($GALLERY_EMBEDDED_INSIDE) {
		// We're in NUKE ... so there should be an alias
		$gallery->ML->nuke_language=$HTTP_COOKIE_VARS['lang'];
		$gallery->ML->language=$langalias[$gallery->ML->nuke_language];
	}

/* Fall back to Default Language if :
	- we cant detect Language
	- Nuke sends an unsupported
	- We are in Config Mode
*/			
	if (! $gallery->ML->language) {
		$gallery->ML->language = $gallery->ML->default['language'];
//		echo "<br>Fall back to default => " . $gallery->ML->language;
	}

// if an alias for a language is given, use it
//
	if ($nls['alias'][$gallery->ML->language]) {
		$gallery->ML->language = $nls['alias'][$gallery->ML->language] ;
	}

// And now set this language into session
	$gallery->session->language= $gallery->ML->language;
	//echo "<br>Final Language => " . $gallery->ML->language;
	//echo "<br>Final Session Language => " . $gallery->session->language;


require_once($gallery->path ."ML_files/ML_checks.php") ;

// locale

	if ($gallery->ML->locale_ok == 0) {
		$gallery->ML->error="There is a locale Problem !!";
	} else {
		//locale okay
		if ($gallery->ML->locale_alias[$gallery->ML->language]) {
			$gallery->ML->locale=$gallery->ML->locale_alias[$gallery->ML->language];
		} else {
			$gallery->ML->locale=$gallery->ML->language;
		}
		// Override NUKES locale :)))	
		$locale=$gallery->ML->locale;
	}

// if no direction is present, use default
        if ( ! $nls['direction'][$gallery->ML->language]) {
		$gallery->ML->direction=$nls['default']['direction'] ;
	} else {
		$gallery->ML->direction = $nls['direction'][$gallery->ML->language] ;
	}

//If no Charset is given use default
        if ( ! $nls['charset'][$gallery->ML->language] ) {
		$gallery->ML->charset=$nls['default']['charset'];
	} else {
		$gallery->ML->charset=$nls['charset'][$gallery->ML->language] ;
	}

// if no alignment is given use default
        if ( ! $nls['align'][$gallery->ML->language] ) {
		$gallery->ML->align=$nls['default']['align'];
	} else {
		$gallery->ML->align=$nls['align'][$gallery->ML->language] ; 
	}

// When all is done do the settings
require($gallery->path . "ML_files/ML_settings.inc"); 
?>