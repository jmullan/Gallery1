<?php

// Version
// Minimum is 4.1.x
//
        $version = explode(".", phpversion());
        if ($version[0] < 4) {
                $gallery->ML->version_ok=0;
		$gallery->ML->error="Your PHP is too old";
        } elseif ($version[1] <1) {
                $gallery->ML->version_ok=0;
		$gallery->ML->error="Your PHP is too old";
	} else{
		$gallery->ML->version_ok=2;
	}

// path to gallery locale

	$gallery->ML->locale_path=$gallery->path . "locale";

	if (is_dir($gallery->ML->locale_path)) {
		$gallery->ML->locale_path_ok = 2;
	} else {
		$gallery->ML->locale_path_ok=0;
		$gallery->ML->error ="Path to Gallery locales not found (" . $gallery->ML->locale_path .")";
	}

// *****************************
// Check the local Support

if (stristr(PHP_OS,"linux") or stristr(PHP_OS,"unix")) {
	# Unix / Linux
	# Check wich locales are installed
	if (is_readable("/etc/locale.gen")) {
		$list=split("\n",shell_exec('cat /etc/locale.gen | cut -d " " -f 1'));
	}
	else {
		$list=split("\n", shell_exec("locale -a")) ;
	}

	foreach ($nls['languages'] as $key => $value) {

//		echo "<br>*********** Testing : $key *****************";

		if (in_array($key,$nls['aliases'])) {
			$keylist=array_keys($nls['aliases'],$key); 

/*
			echo "<br> Keyliste : ";
			foreach ($keylist as $value3) {
				echo $value3 .", " ;
			}
*/

			$gallery->ML->working_locale_alias[$key] = preg_grep ("/^(". substr($key,0,2) ."|" . implode ("|", $keylist) .")/",$list);
		}
		else {
//			echo "<br> No Keylist !";
			$gallery->ML->working_locale_alias[$key]= preg_grep ("/^". substr($key,0,2) ."/",$list);
		}

/*
		if ($gallery->ML->working_locale_alias[$key]) {
			echo "<br><----><br>";
			foreach ($gallery->ML->working_locale_alias[$key] as $value2) {
				echo " ". $value2 . ", ";
			}	
		}
*/
		if (in_array ($key, $list)) {
			$gallery->ML->working_locales[]=$key;
//			echo "<br>____________<br>ok : " . $key;
		}
		elseif ($gallery->ML->working_locale_alias[$key]) {
			$gallery->ML->maybe_working_locales[]=$key;
//			echo "<br>____________<br>maybe ok : " . $key;
		}
		else {
			$gallery->ML->non_working_locales[]=$key;
//			echo "<br>____________<br>non ok : " . $key;
		}

	}
}
else {
	#Unknown OS (or Windows)
	$gallery->ML->locale_ok=1;
	$gallery->ML->ML_warning="I cant detect your OS right now. There might be a locale Problem";
	// Override NUKES locale :)))	
	$locale=$gallery->ML->locale;
}

if ($gallery->ML->working_locales) {
	$gallery->ML->locale_ok=2;
	if ($gallery->ML->non_working_locales) {
		$gallery->ML->locale_ok=1;
	}
}
else {
	// Gettext works fine :)
	$gallery->ML->locale_ok=0;
	// Override NUKES locale :)))	
	$locale=$gallery->ML->locale;
}

// Gettext Support ?
	global $translation;

        if (! in_array ("gettext", get_loaded_extensions()) and $gallery->ML->gettext_ok != 1) {
		// No php with gettext
                $gallery->ML->gettext_ok=1;

                $filename=$gallery->path ."po/" . $gallery->ML->language . "-gallery.po";

		if (file_exists($filename)) {
	                $lines=file($filename);
			
			foreach ($lines as $key => $value) {
                                if (stristr($value, "msgid")) {
					$new_key=substr($value, 7,-2);
					$translation[$new_key]=substr($lines[$key+1],8,-2);
                        	}	
			}
		
			// Substitute _() gettext function
	                function _($search) {
				if ($GLOBALS['translation'][$search]) {
					return $GLOBALS['translation'][$search] ;
				}
				else {
					return $search;
				}
        	        }
		}
		else {
		        function _($search) {
				return $search;
        	        }
		}

		$gallery->ML->warning="\n". _("You have a PHP without gettext support !!!") .
		"\n<br>". _("The Multilanguage Version offers you a substitution for this functions since version 1.3.4-RC3_ML28.") .
		"\n<br>". _("But the creation of your Gallery Pages will last definitely longer !!!") .
		"\n<br>". _("Even in certain cases there can be failures. In this case please") .
		' <a href="jens@f2h9.de">' . _("Send a mail to me") . '</a></b></span>';
        }
	else {
		// gettext is working fine :)
                $gallery->ML->gettext_ok=2;
        }

?>