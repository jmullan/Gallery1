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

###################################################################
# IBPS E-C@ard for Gallery           Version 1                    #
# Copyright 2002 IBPS Friedrichs     info@ibps-friedrichs.de      #
# Ported for Gallery By freenik      webmaster@cittadipolicoro.com#
###################################################################

*/

require_once(dirname(dirname(__FILE__)) . '/init.php');

$ecard = getRequestVar('ecard');
if (empty($ecard) || empty($ecard['image_name']) || empty($ecard["template_name"])) {
	$error = true;
}
else if(!isset($gallery->album)) {
	$pieces = explode('/', $ecard['image_name']);
	$gallery->album = new Album;
	$loadOk = $gallery->album->load($pieces[0]);
}

if(isXSSclean($ecard["template_name"])) {
	list($error,$ecard_data_to_parse) = get_ecard_template($ecard["template_name"]);
}
else {
	$error = true;
}

if (!empty($error) || ! $loadOk) {
	if (!$gallery->user->canDeleteAlbum($gallery->album)) {
		printPopupStart(gTranslate('core', "Gallery eCard"));
		echo gallery_error(gTranslate('core', "Gallery could not process your ecard! Please close this window and try again later, thanks!"));
		includeTemplate("overall.footer");
		exit;
	}
}
else {
	echo parse_ecard_template($ecard,$ecard_data_to_parse, true);
}

?>
