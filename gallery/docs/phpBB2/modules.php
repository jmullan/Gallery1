<?php
/*######################################################## 
## Mod Title:    Gallery - phpBB2 Integration 
## Mod Version:  1.4.3
##
## $Date$
## $Revision$
########################################################*/

define('MODULES_PATH', './modules/');


$op = ( isset($HTTP_POST_VARS['op']) ) ? $HTTP_POST_VARS['op'] : (isset($HTTP_GET_VARS['op']) ? $HTTP_GET_VARS['op'] : '');
switch ($op)
{
    case 'modload':
	// Added with changes in Security for PhpBB2.
	define('IN_PHPBB', true);

	// Deal with the register_globals issue temporarily
	if (!empty($_GET)) {
		extract($_GET);
	} 
	else if (!empty($HTTP_GET_VARS)) {
		extract($HTTP_GET_VARS);
	}
	if (!empty($_POST)) {
		extract($_POST);
	}
	else if (!empty($HTTP_POST_VARS)) {
		extract($HTTP_POST_VARS);
	}

	$name = ( isset($HTTP_POST_VARS['name']) ) ? $HTTP_POST_VARS['name'] : (isset($HTTP_GET_VARS['name']) ? $HTTP_GET_VARS['name'] : '');
	$file = ( isset($HTTP_POST_VARS['file']) ) ? $HTTP_POST_VARS['file'] : (isset($HTTP_GET_VARS['file']) ? $HTTP_GET_VARS['file'] : '');
	$sid = ( isset($HTTP_POST_VARS['sid']) ) ? $HTTP_POST_VARS['sid'] : (isset($HTTP_GET_VARS['sid']) ? $HTTP_GET_VARS['sid'] : '');

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

        // Security fix
        if (ereg("\.\.",$name) || ereg("\.\.",$file))
        {
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