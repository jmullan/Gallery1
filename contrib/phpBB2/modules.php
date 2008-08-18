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

/*
 * This file was written by Martin Smallridge <info@snailsource.com>
 * Adapted for 2.0.9 by Jens Tkotz
 */

// $phpEx is set in phpBBs extension.inc, so it should be set in a request
if (isset($_REQUEST['phpEx'])) {
	echo "Security violation! Override attempt.\n";
	exit;
}

define('MODULES_PATH', './modules/');

switch ($_REQUEST['op']) {
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

		// phpBB may unset() these if we set them before loading
		// their include files.
		$name = $_REQUEST['name'];
		$file = $_REQUEST['file'];

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
