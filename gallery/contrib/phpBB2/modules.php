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

/*
** This file was written by Martin Smallridge <info@snailsource.com>
** Adapted for 2.0.9 by Jens Tkotz
*/

define('MODULES_PATH', './modules/');

$op = $_REQUEST['op'];
$name = $_REQUEST['name'];
$file = $_REQUEST['file'];

switch ($op) {
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
