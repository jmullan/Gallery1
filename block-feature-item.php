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
 * $Id: block-feature-item.php 16788 2007-07-24 09:08:45Z jenst $
 *
 * Featured item block for Gallery
 * Beckett Madden-Woods (beckett@beckettmw.com)
 * Edited by Jens Tkotz
*/

/*
 * This block showes the thumbnail of the photo, or album that an admin selected to be featured.
 *
 * If your Gallery is embedded and you call it via an URL,
 * make sure you are giving the needed paramters.
 *
 * *Nuke:
 * http://<URL to your Nuke>/modules.php?op=modload&name=gallery&file=index&include=block-feature-item.php
 *
 * Mambo / Joomla :
 * http://<URL to Mambo>/index.php?option=com_gallery&Itemid=XXX
 */
?>

<style type="text/css">
	img { border: none; }
</style>

<?php
require(dirname(__FILE__) . "/init.php");

define('FEATURE_CACHE', $gallery->app->albumDir . '/featured-item.cache');

list($albumName, $index) = explode('/', getFile(FEATURE_CACHE));

if (!empty($albumName) && isValidGalleryInteger($index)) {
	$album = new Album();
	$ret = $album->load($albumName);

	if(!$ret) {
		echo infoBox(array(array(
			'type' => 'information',
			'text' => gTranslate('core', "It seems the album where the featured used to be, was deleted.")
		)));
		exit;
	}

	$item	= $album->getPhoto($index);

	if(empty($item)) {
		echo infoBox(array(array(
			'type' => 'information',
			'text' => gTranslate('core', "It seems the featured item was deleted.")
		)));
		exit;
	}

	$id			= $item->getPhotoId();

	$caption	= $item->getCaption() ? $item->getCaption() : '';
	$imageUrl	= $album->getThumbnailTag($index);

	list($iWidth, $iHeight) = $album->getThumbDimensions($index);
	$gallery->html_wrap['imageWidth']	= $iWidth;
	$gallery->html_wrap['imageHeight']	= $iHeight;

	if($item->isAlbum()) {
		$subAlbumName	= $album->getAlbumName($index);
		$itemUrl		= makeAlbumUrl($subAlbumName);
	}
	else {
		$itemUrl = makeAlbumUrl($album->fields['name'], $id);
	}

	switch($gallery->app->featureBlockFrame) {
		case 'albumImageFrame' :
			$frame = $album->fields['image_frame'];
			break;
		case 'albumThumbFrame' :
			$frame = $album->fields['thumb_frame'];
			break;
		case 'mainThumbFrame':
			$frame = $gallery->app->gallery_thumb_frame_style;
			break;
		default:
			$frame = $gallery->app->featureBlockFrame;
			break;
	}

	$gallery->html_wrap['imageHref']	= $itemUrl;
	$gallery->html_wrap['imageTag']		= $imageUrl;
	$gallery->html_wrap['borderColor']	= $gallery->app->featureBlockFrameBorderColor;
	$gallery->html_wrap['borderWidth']	= $gallery->app->featureBlockFrameBorderWidth;
	$gallery->html_wrap['frame']		= $frame;
	$gallery->html_wrap['attr']			= '';

	echo getStyleSheetLink();
	echo "\n<div class=\"g-feature-block\">";
	echo "\n  <div class=\"g-feature-block-item\">";

	includeLayout('inline_imagewrap.inc');

	if (!in_array($frame, array('dots', 'solid')) &&
		!fs_file_exists(dirname(__FILE__) . "/layout/frames/$frame/frame.def")) {
		echo "\n<br>";
	}
	echo $caption;

	echo "\n  </div>";

	$albumUrl	= makeAlbumUrl($album->fields['name']);
	$albumTitle	= $album->fields['title'];

	if($item->isAlbum()) {
		printf ("\n" . gTranslate('core', "This is a featured subalbum from album: %s"), "<a href=\"$albumUrl\">$albumTitle</a>");
	}
	else {
		printf ("\n  ". gTranslate('core', "This featured photo is from album: %s"), "<a href=\"$albumUrl\">$albumTitle</a>");
	}

	echo "\n</div>";
}
else {
	echo infoBox(array(array(
		'type' => 'information',
		'text' => gTranslate('core', "Currently no item is featured by this Gallery, or invalid parameters were given.")
	)));
}

?>
