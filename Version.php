<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2005 Bharat Mediratta
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
 * Protect against very old versions of 4.0 (like 4.0RC1) which
 * don't implicitly create a new stdClass() when you use a variable
 * like a class.
 */
if (!isset($gallery)) {
	$gallery = new stdClass();
}

$gallery->version = '1.5-RC2-cvs-b30';
$gallery->config_version = 90;
$gallery->album_version = 33;
$gallery->user_version = 5;
$gallery->url = "http://gallery.sourceforge.net";

/* do not edit the date!! modified by CVS */
$gallery->last_change=strtotime(substr('$Date$', 7, -1));
/*
 * PostNuke version info
 */
$modversion['name'] = 'Gallery'; // Module name
$modversion['version'] = $gallery->version; // Version Number
$modversion['description'] = 'Photo and Movie gallery'; // Module Description
$modversion['credits'] = 'AUTHORS'; // Credits File
$modversion['help'] = 'README'; // Help File
$modversion['changelog'] = 'ChangeLog'; //Change Log File
$modversion['license'] = 'LICENSE.txt'; // License File
$modversion['official'] = 0; // Official PostNuke Approved Module? 1 = yes, 0 = no
$modversion['author'] = 'Bharat Mediratta'; // Author
$modversion['contact'] = $gallery->url; // The Authors Website or Contact Email Address
$modversion['admin'] = 0; // Leave at 0
if (!isset($modname) && isset($name)) {
	$modname = $name;
} else {
	$modname = '';
}
$modversion['securityschema'] = array("$modname::" => '::'); // Permission Component
?>
