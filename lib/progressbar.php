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
 * $Id: content.php 17321 2007-12-29 07:17:50Z JensT $
*/

/**
 * @package	Layout
 * @author	Jens Tkotz
 */

/**
 * Returns the HTML code for a progressbar.
 *
 * @param string  $id       HTML ID you want to assing to the progressbar
 * @param string  $label    A descriptive Label
 * @return string $html
 * @author Jens Tkotz
 */
function addProgressbar($id, $label = '') {
	global $gallery;
	static $jsSet = false;

	$html = '';

	if(!$jsSet) {
		$html .= jsHtml('progressbar.js');
	}

	$html .= "\n<div class=\"g-emphasis\">$label</div>\n";
	$html .= "<div id=\"$id\" class=\"progressBar\"><div id=\"progressBarDone_$id\" class=\"progressBarDone\"></div></div>\n";
	$html .= "<div id=\"progressDescription_$id\"></div>\n";
	$html .= "<div id=\"progressAddText_$id\"></div>\n";

	return $html;
}


/**
 * Wrapper around js function addProgressBarText. Updates a progressbar.
 *
 * @param string  $htmlId    HTML ID of the progressbar you want to update.
 * @param string  $text
 */
function addProgressBarText($htmlId, $text) {
	echo "\n<script type=\"text/javascript\">addProgressBarText('$htmlId', '$text')</script>";
	my_flush();
}

/**
 * Wrapper around js function updateProgressBar. Updates a progressbar.
 *
 * @param string  $htmlId       HTML ID of the progressbar you want to update.
 * @param string  $status       Optional text you want to write in the description field.
 * @param float   $percentDone
 */
function updateProgressBar($htmlId, $status, $percentDone) {
	echo "\n<script type=\"text/javascript\">updateProgressBar('$htmlId', '$status', $percentDone)</script>";
	my_flush();
}


