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
global $GALLERY_BASEDIR;
global $GALLERY_EMBEDDED_INSIDE;
global $GALLERY_MODULENAME;
global $op;
global $include;

/* Detect PHP-Nuke and react accordingly */
if (!strcmp($op, "modload")) {

	/* 
	 * Change this variable if your Gallery module has a different
	 * name in the Nuke modules directory.
	 */
	$GALLERY_MODULENAME = "gallery";
	$GALLERY_BASEDIR = "modules/$GALLERY_MODULENAME/";
	$GALLERY_EMBEDDED_INSIDE = "nuke";

	if (!$include) {
		$include = "albums.php";
	}

	include(${GALLERY_BASEDIR} . $include);
} else {
	include("albums.php");
}
?>
