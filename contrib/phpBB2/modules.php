<?php

/*
** This file was written by Martin Smallridge <info@snailsource.com>
** Adapted for 2.0.9 by Jens Tkotz
*/

define('MODULES_PATH', './modules/');


$op = ( isset($_POST['op']) ) ? $_POST['op'] : (isset($_GET['op']) ? $_GET['op'] : '');
switch ($op)
{
    case 'modload':
	// Added with changes in Security for PhpBB2.
	define('IN_PHPBB', true);

        define ("LOADED_AS_MODULE","1");
	$phpbb_root_path = "./";
	// connect to phpbb
	include_once($phpbb_root_path . 'extension.inc');
	include_once($phpbb_root_path . 'common.'.$phpEx);
	include_once($phpbb_root_path . 'includes/functions.'.$phpEx);

	// Start session management
	//
	$userdata = session_pagestart($user_ip, PAGE_INDEX);
	init_userprefs($userdata);
	//
	// End session management

	$register_globals = ini_get("register_globals");
	if (empty($register_globals) || !strcasecmp($register_globals, "off") || !strcasecmp($register_globals, "false")) {
		$register_globals = 0;
	} else {
		$register_globals = 1;
	}

	/*
	 * If register_globals is off, then extract all Superglobales into the global namespace.
	 */
	if (!$register_globals) {
		/*
		** Prevent hackers from overwriting one HTTP_ global using another one.  For example,
		** appending "?HTTP_POST_VARS[gallery]=xxx" to the url would cause extract
		** to overwrite HTTP_POST_VARS when it extracts HTTP_GET_VARS
		*/
    
		$scrubList = array('HTTP_GET_VARS', 'HTTP_POST_VARS', 'HTTP_COOKIE_VARS', 'HTTP_POST_FILES');
		array_push($scrubList, "_GET", "_POST", "_COOKIE", "_FILES", "_REQUEST");

		foreach ($scrubList as $outer) {
			foreach ($scrubList as $inner) {
				unset(${$outer}[$inner]);
			}
		}

		extract($_REQUEST);
	        foreach($_FILES as $key => $value) {
	            ${$key."_name"} = $value["name"];
	            ${$key."_size"} = $value["size"];
	            ${$key."_type"} = $value["type"];
	            ${$key} = $value["tmp_name"];
		}
	}

        // Security fix
        if (ereg("\.\.",$name) || ereg("\.\.",$file)) {
            echo 'Nice try :-)';
            break;
        } else {
		include(MODULES_PATH."$name/$file.$phpEx");
        }
        break;

    default:
        die ("Sorry, you can't access this file directly...");
        break;
}
?>
