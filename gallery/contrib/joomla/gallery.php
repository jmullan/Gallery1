<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2006 Bharat Mediratta
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
 * Gallery Component for Mambo Open Source CMS v4.5 or newer
 * Original author: Beckett Madden-Woods <beckett@beckettmw.com>
 *
 * $Id$
 */

defined( '_VALID_MOS' ) or die( 'Direct Access to this location is not allowed.' );

/* load the html drawing class */

$database->setQuery("SELECT * FROM #__gallery");
$param = $database->loadRowList();

/* extract params from the DB query */
$MOS_GALLERY_PARAMS = array();
foreach ($param as $curr) {
	$MOS_GALLERY_PARAMS[$curr[0]] = $curr[1];
}

if (!realpath($MOS_GALLERY_PARAMS['path'])) {
	echo "Security Violation";
	exit;
} else {
	if (! defined("MOS_GALLERY_PARAMS_PATH")) {
		define ("MOS_GALLERY_PARAMS_PATH",$MOS_GALLERY_PARAMS['path']);
	}
}

print '<table width="100%" cellpadding="4" cellspacing="0" border="0" align="center" class="contentpane">' . "\n<tr><td>\n";
include(MOS_GALLERY_PARAMS_PATH . 'index.php');
print "</td></tr>\n</table>\n";

?>
