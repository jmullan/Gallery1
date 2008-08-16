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

/**
 * @package 	Libs
 * @subpackage	Filetypes
 */

/**
 * Checks whether a filenaname extension represents an acceptable archive for Gallery.
 *
 * @param string	$ext
 * @return boolean			True if the extension belongs to an archive.
 */
function acceptableArchive($ext) {
	if (in_array($ext, acceptableArchiveList())) {
		return true;
	}
	else {
		return false;
	}
}

/**
 * Returns the list of extensions that Gallery may can handle.
 *
 * @return array
 */
function acceptableArchiveList() {
	return array('zip', 'rar');
}

/**
 * Returns whether a file has a acceptable Format.
 * Test is done via the filename extension.
 *
 * @param string	$ext
 * @return boolean
 */
function acceptableFormat($ext) {
	return (isImage($ext) || isMovie($ext));
}

/**
 * Returns an array of all formats accepted by Gallery.
 *
 * @return array
 */
function acceptableFormatList() {
	return array_merge(acceptableImageList(), acceptableMovieList());
}

/**
 * Returns a regulat expression to match if a file is acceptable for Gallery
 *
 * @return string		The regexp
 */
function acceptableFormatRegexp() {
	return "(?:" . join("|", acceptableFormatList()) . ")";
}

/**
 * Returns an array containing all filename extension accepted as valid image.
 *
 * @return array
 */
function acceptableImageList() {
	return array('jpg', 'jpeg', 'gif', 'png');
}

/**
 * Returns an array containing all filename extension accepted as valid movie.
 *
 * @return array
 */
function acceptableMovieList() {
	return array('asx', 'asf', 'avi', 'mpg', 'mpeg', 'mp2', 'wmv', 'mov', 'qt', 'swf', 'mp4', 'rm', 'ram');
}

/**
 * Checks whether a filenaname extension represents an acceptable image for Gallery.
 *
 * @param string	$ext
 * @return boolean
 */
function isImage($ext) {
	$tag = strtolower($ext);

	return in_array($ext, acceptableImageList());
}

/**
 * Checks whether a filenaname extension represents an acceptable movie for Gallery.
 *
 * @param string	$ext
 * @return boolean
 */
function isMovie($ext) {
	$tag = strtolower($ext);

	return in_array($ext, acceptableMovieList());
}
?>
