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
 * @package Item
 */

if (!isset($gallery) || !function_exists('gTranslate')) {
	exit;
}

$ratioOptions = array(
	'0|0'	=> gTranslate('core', "No Ratio"),
	'1|1'	=> gTranslate('core', "1:1 (Square)"),
	'1|3'	=> gTranslate('core', "1:3 (Letterbox)"),
	'9|16'	=> gTranslate('core', "9:16 (HDTV)"),
	'3|5'	=> gTranslate('core', "3:5 (Photo)"),
	'4|6'	=> gTranslate('core', "4:6 = 10:15 (Photo)"),
	'9|13'	=> gTranslate('core', "9:13 (Photo)"),
	'5|7'	=> gTranslate('core', "5:7 (Photo)"),
	'3|4'	=> gTranslate('core', "3:4 (Screen)"),
	'8|10'	=> gTranslate('core', "8:10 (Photo)"),
	"$imageWidth|$imageHeight" => gTranslate('core', "Like the Image")
);

$ratioDirections = array(
	'1'	=> gTranslate('core', "Portrait"),
	'-1'	=> gTranslate('core', "Landscape"),
);

?>