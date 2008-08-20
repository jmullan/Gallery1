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
 * $Id: edit_thumb.php 16662 2007-06-19 16:24:02Z jenst $
 */

require_once(dirname(__FILE__) . '/init.php');

list($index, $newWidth) = getRequestVar(array('index', 'newWidth'));

// Hack checks
if (! isset($gallery->album) || ! isset($gallery->session->albumName) ||
	intval($newWidth) <= 0 ||
	! $photo = $gallery->album->getPhoto($index) ||
	! $gallery->album->canViewItem($gallery->user, $index))
{
	exit;
}

$fullpath	= $gallery->album->getAbsolutePhotoPath($index, true);
list($imageWidth, $imageHeight, $IMG_TYPE) = getimagesize($fullpath);

$ratio			= $imageHeight/$imageWidth;
$newHeight		= $newWidth * $ratio;

$img_tempdest 	= imagecreatetruecolor($newWidth, $newHeight);

switch($IMG_TYPE) {
	case IMG_GIF:
		 $img_source = imagecreatefromgif($fullpath);
		imagecopyresampled($img_tempdest, $img_source, 0, 0, 0, 0, $newWidth, $newHeight, $imageWidth, $imageHeight);
		imagegif($img_tempdest);
	break;

	case IMG_JPG:
		 $img_source = imagecreatefromjpeg($fullpath);
		imagecopyresampled($img_tempdest, $img_source, 0, 0, 0, 0, $newWidth, $newHeight, $imageWidth, $imageHeight);
		imagejpeg($img_tempdest, null, 100);
	break;

	case IMG_PNG:
		 $img_source = imagecreatefrompng($fullpath);
		imagecopyresampled($img_tempdest, $img_source, 0, 0, 0, 0, $newWidth, $newHeight, $imageWidth, $imageHeight);
		imagepng($img_tempdest);
	break;

	case IMG_WBMP:
		 $img_source = imagecreatefromwbmp($fullpath);
		imagecopyresampled($img_tempdest, $img_source, 0, 0, 0, 0, $newWidth, $newHeight, $imageWidth, $imageHeight);
		imagewbmp($img_tempdest);
	break;

	case IMG_XPM:
		 $img_source = imagecreatefromxpm($fullpath);
		imagecopyresampled($img_tempdest, $img_source, 0, 0, 0, 0, $newWidth, $newHeight, $imageWidth, $imageHeight);
		imagexbm($img_tempdest);
	break;
}

imagedestroy( $img_source);
imagedestroy( $img_tempdest);

?>