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
<?php /* $Id$ */ ?>

<?php
	/* load necessary functions */
	if (stristr (__FILE__, '/var/lib/gallery/setup')) {
		/* Gallery runs on a Debian System */
		require ('/usr/share/gallery/util.php');
	} else {
		require (dirname(dirname(__FILE__)) . '/util.php');
	}

	/*
	** When we are in Windows we need a check if we secured.
	** We dont check when not in Windows, as we assume the permissions are set correct.		
	*/

	if (getOS() == OS_WINDOWS) {
		include(dirname(dirname(__FILE__)) . '/platform/fs_win32.php');
		if (fs_file_exists("SECURE")) {
			echo "You cannot access this file while gallery is in secure mode.";
			exit;
		}
	}

	phpinfo(); 
?>
