<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2006 Bharat Mediratta
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
 *
 *
 * Featured photo block for Gallery
 * Beckett Madden-Woods (beckett@beckettmw.com)
 * Edited by Jens Tkotz <jens@peino.de>
*/

/*
 * This block showed the thumbnail of the photo that an admin selected as featured photo.
 *
 * If your Gallery is embedded and you call it via an URL,
 * make sure you are giving the needed paramters.
 *
 * *Nuke:
 * http://<URL to your Nuke>/modules.php?op=modload&name=gallery&file=index&include=block-feature-photo.php
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

define('FEATURE_CACHE', $gallery->app->albumDir . '/featured-photo.cache');

list($albumName, $index) = explode('/', getFile(FEATURE_CACHE));

if (!empty($albumName) && !empty($index)) {
    $album = new Album();
    $album->load($albumName);

    $photo = $album->getPhoto($index);
    $id = $photo->getPhotoId();
    $caption = $photo->getCaption() ? $photo->getCaption() : '';
    $photoUrl = makeAlbumUrl($album->fields['name'], $id);
    $imageUrl = $album->getThumbnailTag($index);
    $albumUrl = makeAlbumUrl($album->fields['name']);
    $albumTitle = $album->fields['title'];
    $gallery->html_wrap['imageHref'] = $photoUrl;
    $gallery->html_wrap['imageTag'] = $imageUrl;
    $gallery->html_wrap['borderColor'] = $gallery->app->featureBlockFrameBorderColor;
    $gallery->html_wrap['borderWidth'] = $gallery->app->featureBlockFrameBorderWidth;

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
    $gallery->html_wrap['frame'] = $frame;
    $gallery->html_wrap['imageWidth'] = $photo->thumbnail->raw_width;
    $gallery->html_wrap['imageHeight'] = $photo->thumbnail->raw_height;
    $gallery->html_wrap['attr'] = '';

    echo getStyleSheetLink();
    echo "\n<div class=\"g-feature-block\">";
    echo "\n  <div class=\"g-feature-block-photo\">";

    includeLayout('inline_imagewrap.inc');

    if (!in_array($frame, array('dots', 'solid')) &&
        !fs_file_exists(dirname(__FILE__) . "/layout/frames/$frame/frame.def")) {
        echo "\n<br>";
    }
    echo $caption;

    echo "\n  </div>";
    printf ("\n  ". gTranslate('core', "From album: %s"), "<a href=\"$albumUrl\">$albumTitle</a>");
    echo "\n</div>";
}
else {
    echo infoBox(array(array(
        'type' => 'information',
        'text' => gTranslate('core', "Currently no item is featured by this Gallery.")
    )));
}

?>
