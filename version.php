<?
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2001 Bharat Mediratta
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
 */
?>
<?
// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
	print "Security violation\n";
	exit;
}
?>
<? 

/*
 * Protect against very old versions of 4.0 (like 4.0RC1) which 
 * don't implicitly create a new stdClass() when you use a variable
 * like a class.
 */
if (!$gallery) {
	$gallery = new stdClass();
}

$gallery->version = "1.2.3-cvs-b8";
$gallery->config_version = 26;
$gallery->album_version = 4;
$gallery->remote_protocol_version = 1;
$gallery->url = "http://gallery.sourceforge.net";

/*
 * PostNuke version info
 */
$modversion['name'] = 'gallery'; // Module name
$modversion['version'] = $gallery->version; // Version Number
$modversion['description'] = 'Gallery is a slick web based photo album written using PHP. Easy to install (it includes a config wizard), it provides users with the ability to create and maintain their own albums in the album collection via an intuitive web interface. Photo management includes automatic thumbnail creation, image resizing, rotation, ordering, captioning, searching and more. Albums can have read, write and caption permissions per individual authenticated user for an additional level of privacy. '; // Module Description
$modversion['credits'] = ''; // Credits File
$modversion['help'] = 'README'; // Help File
$modversion['changelog'] = 'ChangeLog'; //Change Log File
$modversion['license'] = 'LICENSE.txt'; // License File
$modversion['official'] = 1; // Official PostNuke Approved Module? 1 = yes, 0 = no
$modversion['author'] = 'Bharat Mediratta'; // Author
$modversion['contact'] = $gallery->url; // The Authors Website or Contact Email Address
$modversion['admin'] = 0; // Leave at 0
?>


