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

function build_popup_url($url, $url_is_complete = false) {
	/* Separate the target from the arguments */
	$result = explode('?', $url);
	$target = $result[0];

	if (isset($result[1])) {
		$arglist = $result[1];
	}
	else {
		$arglist = '';
	}

	/* Parse the query string arguments */
	$args = array();
	parse_str($arglist, $args);
	$args['gallery_popup'] = true;

	if (!$url_is_complete) {
		$url = makeGalleryUrl($target, $args);
	}

	return $url;
}

function popup($url, $url_is_complete=0, $height=550, $width=600) {
	// Force int data type
	$height = (int)$height;
	$width = (int)$width;

	$url = build_popup_url($url, $url_is_complete);
	return popup_js($url, "GalleryPopup","height=$height,width=$width,location=no,scrollbars=yes,menubars=no,toolbars=no,resizable=yes");
}

function popup_js($url, $window, $attrs) {
	if (ereg("^http|^ftp|&amp;", $url)) {
		$url = "'$url'";
	}

	return "nw=window.open($url,'$window','$attrs'); nw.opener=self; return false;";
}

function popup_status($url, $height=150, $width=350) {
	// Force int data type
	$height = (int)$height;
	$width = (int)$width;

	$attrs = "height=$height,width=$width,location=no,scrollbars=no,menubars=no,toolbars=no,resizable=yes";
	return "open('" . unhtmlentities(build_popup_url($url)) . "','Status','$attrs');";
}

function popup_link($title, $url, $url_is_complete = 0, $online_only = true, $height = 550, $width = 600, $cssclass='', $extraJS = '', $icon ='', $addBrackets = true, $accesskey = true) {
	global $gallery;
	global $specialIconMode;

	if ( !empty($gallery->session->offline) && $online_only ) {
		return null;
	}

	$iconMode = isset($specialIconMode) ? $specialIconMode : '';

	$url = build_popup_url($url, $url_is_complete);
	// Force int data type
	$height = (int)$height;
	$width = (int)$width;

	$attrList = array(
		'class' => "g-popuplink $cssclass",
		'onClick' => "javascript:". $extraJS . popup_js("this.href", "Edit", "height=$height,width=$width,location=no,scrollbars=yes,menubars=no,toolbars=no,resizable=yes")
	);

	if(!empty($icon)) {
		$html = galleryIconLink($url, $icon, $title, $iconMode, $attrList, $accesskey);
	}
	else {
		$html = galleryLink($url, $title, $attrList, $icon, $addBrackets, $accesskey);
	}

	return $html;
}

function popup_link2($title, $url, $args = array()) {
	global $gallery;

	$url_is_complete	= isset($args['url_is_complete'])	? $args['url_is_complete']	: true;
	$online_only		= isset($args['online_only'])		? $args['online_only']		: true;
	$height			= isset($args['height'])		? $args['height']		: 550;
	$width			= isset($args['width'])			? $args['width']		: 600;
	$cssclass		= isset($args['cssclass'])		? $args['cssclass']		: '';
	$extraJS		= isset($args['extraJS'])		? $args['extraJS']		: '';
	$addBrackets		= isset($args['addBrackets'])		? $args['addBrackets']		: false;
	$accesskey		= isset($args['accesskey'])		? $args['accesskey']		: true;
	$icon			= isset($args['icon'])			? $args['icon']			: '';

	if ( !empty($gallery->session->offline) && $online_only ) {
		return null;
	}

	$args['gallery_popup'] = true;

	$url = build_popup_url($url, $url_is_complete);

	// Force int data type
	$height = (int)$height;
	$width = (int)$width;

	$attrList = array(
		'class' => "g-popuplink $cssclass",
		'onClick' => "javascript:". $extraJS .popup_js("this.href", "Edit", "height=$height,width=$width,location=no,scrollbars=yes,menubars=no,toolbars=no,resizable=yes")
	);

	$html = galleryLink($url, $title, $attrList, $icon, $addBrackets, $accesskey);

	return $html;
}

/**
 * This function outputs the HTML start elements of an popup.
 * It was made to beautify php code ;)
 *
 * @param string $title
 * @param string $header
 * @param string $align
 */
function printPopupStart($title = '', $header = '', $align = 'center') {
	global $gallery;

	if (!empty($title) && empty($header)) {
		$header = $title;
	}

	doctype();
?>
<html>
<head>
  <title><?php echo strip_tags($title); ?></title>
  <?php common_header(); ?>
</head>
<body class="g-popup">
<div class="g-header-popup">
  <div class="g-pagetitle-popup"><?php echo $header ?></div>
</div>
<div class="g-content-popup <?php echo $align; ?>">

<?php
}
?>
