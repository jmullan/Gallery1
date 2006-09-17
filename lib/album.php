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
 */

/**
 * @package	Album
 * @author	Jens Tkotz
 */

/**
 * Returns the default value for an album property. Either for a specific album, or global
 * @param   string    $property
 * @param   object    $album
 * @param   boolean   $global
 * @return  mixed     $retPoperty
 * @author Jens Tkotz <jens@peino.de>
 */
function getPropertyDefault($property, $album = false, $global = false) {
    global $gallery;

    $retProperty = false;

    if ($album) {
        if ($global) {
            if(isset($gallery->app->default[$property])) {
                $retProperty = $gallery->app->default[$property];
            }
        }
        else {
            if(isset($album->fields[$property])) {
                $retProperty = $album->fields[$property];
            }
        }
    }
    elseif ($global) {
        $retProperty = $gallery->app->$property;
    }

    return $retProperty;
}

function createNewAlbum( $parentName, $newAlbumName = '', $newAlbumTitle = '', $newAlbumDesc = '') {
	global $gallery;

	// get parent album name
	$albumDB = new AlbumDB(FALSE);

	// set new album name from param or default
	$gallery->session->albumName = $albumDB->newAlbumName($newAlbumName);

	$gallery->album = new Album();
	$gallery->album->fields["name"] = $gallery->session->albumName;

	// guid is not created during new Album() as a performance optimization
	// it only needs to be created when an album is created or modified by adding or deleting photos
	$gallery->album->fields['guid'] = genGUID();

	// set title and description
	if (!empty($newAlbumTitle)) {
		$gallery->album->fields["title"] = $newAlbumTitle;
	}
	if (!empty($newAlbumDesc)) {
		$gallery->album->fields["description"] = $newAlbumDesc;
	}

	$gallery->album->setOwner($gallery->user->getUid());
	$gallery->album->fields['creation_date']  = time();

	/* if this is a nested album, set nested parameters */
	if (!empty($parentName)) {
		$gallery->album->fields['parentAlbumName'] = $parentName;
		$parentAlbum = $albumDB->getAlbumByName($parentName);
		$parentAlbum->addNestedAlbum($gallery->session->albumName);
		$parentAlbum->save(array(i18n("Album \"{$gallery->album->fields['name']}\" created as a sub-album of \"$parentName\".")));
		// Set default values in nested album to match settings of parent.
		$gallery->album->fields["perms"]           = $parentAlbum->fields["perms"];
		$gallery->album->fields['extra_fields']    = $parentAlbum->fields['extra_fields'];
		$gallery->album->fields["bgcolor"]         = $parentAlbum->fields["bgcolor"];
		$gallery->album->fields["textcolor"]       = $parentAlbum->fields["textcolor"];
		$gallery->album->fields["linkcolor"]       = $parentAlbum->fields["linkcolor"];
		$gallery->album->fields['background']      = $parentAlbum->fields['background'];
		$gallery->album->fields["font"]            = $parentAlbum->fields["font"];
		$gallery->album->fields["border"]          = $parentAlbum->fields["border"];
		$gallery->album->fields["bordercolor"]     = $parentAlbum->fields["bordercolor"];
		$gallery->album->fields["thumb_size"]      = $parentAlbum->fields["thumb_size"];
		$gallery->album->fields["resize_size"]     = $parentAlbum->fields["resize_size"];
		$gallery->album->fields["resize_file_size"]     = $parentAlbum->fields["resize_file_size"];
		$gallery->album->fields['max_size']        = $parentAlbum->fields['max_size'];
		$gallery->album->fields['max_file_size']   = $parentAlbum->fields['max_file_size'];
		$gallery->album->fields['returnto']        = $parentAlbum->fields['returnto'];
		$gallery->album->fields["rows"]            = $parentAlbum->fields["rows"];
		$gallery->album->fields["cols"]            = $parentAlbum->fields["cols"];
		$gallery->album->fields["fit_to_window"]   = $parentAlbum->fields["fit_to_window"];
		$gallery->album->fields["use_fullOnly"]    = $parentAlbum->fields["use_fullOnly"];
		$gallery->album->fields["print_photos"]    = $parentAlbum->fields["print_photos"];
		$gallery->album->fields['slideshow_type']  = $parentAlbum->fields['slideshow_type'];
		$gallery->album->fields['slideshow_recursive'] = $parentAlbum->fields['slideshow_recursive'];
		$gallery->album->fields['slideshow_length'] = $parentAlbum->fields['slideshow_length'];
		$gallery->album->fields['slideshow_loop'] = $parentAlbum->fields['slideshow_loop'];
		$gallery->album->fields['album_frame']    = $parentAlbum->fields['album_frame'];
		$gallery->album->fields['thumb_frame']    = $parentAlbum->fields['thumb_frame'];
		$gallery->album->fields['image_frame']    = $parentAlbum->fields['image_frame'];
		$gallery->album->fields["use_exif"]        = $parentAlbum->fields["use_exif"];
		$gallery->album->fields["display_clicks"]  = $parentAlbum->fields["display_clicks"];
		$gallery->album->fields["item_owner_display"] = $parentAlbum->fields["item_owner_display"];
		$gallery->album->fields["item_owner_modify"]  = $parentAlbum->fields["item_owner_modify"];
		$gallery->album->fields["item_owner_delete"]  = $parentAlbum->fields["item_owner_delete"];
		$gallery->album->fields["add_to_beginning"]   = $parentAlbum->fields["add_to_beginning"];
		$gallery->album->fields['showDimensions']  = $parentAlbum->fields['showDimensions'];

		$returnVal = $gallery->album->save(array(i18n("Album \"{$gallery->album->fields['name']}\" created as a sub-album of \"$parentName\".")));
	} else {
		$gallery->album->save(array(i18n("Root album \"{$gallery->album->fields['name']}\" created.")));
		/*
		* Get a new albumDB because our old copy is not up to
		* date after we created a new album
		*/
		$albumDB = new AlbumDB(FALSE);

		/* move the album to the top if not a nested album*/
		$numAlbums = $albumDB->numAlbums($gallery->user);
		$albumDB->moveAlbum($gallery->user, $numAlbums, 1);
		$returnVal = $albumDB->save();
	}

	if (!empty($returnVal)) {
		return $gallery->session->albumName;
	} else {
		return 0;
	}
}

/**
 * Returns an array of the parent album names for a given child
 * album.
 * Array is reverted, so the first Element is the topalbum.
 * If you set $addChild true, then the child album itself is added as last Element.
 * Based on code by: Dariush Molavi
 */
function getParentAlbums($childAlbum, $addChild = false) {
	$pAlbum = $childAlbum;
	$parentNameArray = array();

	if ($addChild == true) {
		$parentNameArray[$pAlbum->fields['name']] = $pAlbum->fields['title'];
	}

	while ($pAlbum = $pAlbum->getParentAlbum(FALSE)) {
		$parentNameArray[$pAlbum->fields['name']] = $pAlbum->fields['title'];
	}

	$parentNameArray = array_reverse($parentNameArray);

	return $parentNameArray;
}

/**
 * This function just displays the prefetching navigation for an the mainpages
 *
 */
function prefetchRootAlbumNav() {
    global $gallery, $navigator, $maxPages;

    if ($navigator['page'] > 1) {
?>
  <link rel="top" href="<?php echo makeGalleryUrl('albums.php', array('set_albumListPage' => 1)) ?>">
  <link rel="first" href="<?php echo makeGalleryUrl('albums.php', array('set_albumListPage' => 1)) ?>">
  <link rel="prev" href="<?php echo makeGalleryUrl('albums.php', array('set_albumListPage' => $navigator['page']-1)) ?>">
<?php
    }

    if ($navigator['page'] < $maxPages) {
?>
  <link rel="next" href="<?php echo makeGalleryUrl('albums.php', array('set_albumListPage' => $navigator['page']+1)) ?>">
  <link rel="last" href="<?php echo makeGalleryUrl('albums.php', array('set_albumListPage' => $maxPages)) ?>">
<?php
    }
}

/**
 * This function just displays the RSS Link on the mainpages
 */
function rootRSSLink() {
    global $gallery, $galleryTitle;

    if ($gallery->app->rssEnabled == "yes" && !$gallery->session->offline) {
        $rssTitle = sprintf(gTranslate('core', "%s RSS"), $galleryTitle);
        $rssHref = $gallery->app->photoAlbumURL . "/rss.php";

        echo "  <link rel=\"alternate\" title=\"$rssTitle\" href=\"$rssHref\" type=\"application/rss+xml\">\n";
    }
}

/**
 * This function just displays the prefetching navigation for the albumpages
 */
function prefetchAlbumNav() {
    global $gallery;
    global $first, $previousPage, $last, $nextPage, $maxPages;

    /* prefetching/navigation */
    if (!isset($first)) { ?>
  <link rel="first" href="<?php echo makeAlbumUrl($gallery->session->albumName, '', array('page' => 1)) ?>" >
  <link rel="prev" href="<?php echo makeAlbumUrl($gallery->session->albumName, '', array('page' => $previousPage)) ?>" >
<?php
    }
    if (!isset($last)) { ?>
  <link rel="next" href="<?php echo makeAlbumUrl($gallery->session->albumName, '', array('page' => $nextPage)) ?>" >
  <link rel="last" href="<?php echo makeAlbumUrl($gallery->session->albumName, '', array('page' => $maxPages)) ?>" >
<?php }

    if ($gallery->album->isRoot() &&
        (!$gallery->session->offline ||
	    isset($gallery->session->offlineAlbums["albums.php"]))) { ?>
  <link rel="up" href="<?php echo makeAlbumUrl(); ?>" >
<?php
    }
    else if (!$gallery->session->offline ||
             isset($gallery->session->offlineAlbums[$pAlbum->fields['parentAlbumName']])) { ?>
  <link rel="up" href="<?php echo makeAlbumUrl($gallery->album->fields['parentAlbumName']); ?>" >
<?php
    }
    if (!$gallery->session->offline ||
        isset($gallery->session->offlineAlbums["albums.php"])) { ?>
  <link rel="top" href="<?php echo makeGalleryUrl('albums.php', array('set_albumListPage' => 1)) ?>" >
<?php
    }
}

/**
 * This function just displays the RSS Link on the albumpages
 *
 */
function albumRSSLink() {
    global $gallery, $albumTitle, $albumRSSURL;

    if ($gallery->app->rssEnabled == 'yes' && !$gallery->session->offline) {
        $rssTitle = sprintf(gTranslate('core', "%s RSS"), $albumTitle);

        echo "  <link rel=\"alternate\" title=\"$rssTitle\" href=\"$albumRSSURL\" type=\"application/rss+xml\">\n";
    }
}

/**
 * This function returns the CSS for the settings a user did in the album appearance
 *
 */
function customCSS() {
    global $gallery;

    $customCSS = '';

    // the link colors have to be done here to override the style sheet
    if ($gallery->album->fields["linkcolor"]) {
        $customCSS .= "  a:link, a:visited, a:active { color: ".$gallery->album->fields['linkcolor'] ."; }\n";
        $customCSS .= "  a:hover { color: #ff6600; }\n";
    }

    if ($gallery->album->fields["bgcolor"]) {
        $customCSS .= "  body { background-color:".$gallery->album->fields['bgcolor']."; }\n";
    }

    if (isset($gallery->album->fields['background']) && $gallery->album->fields['background']) {
        $customCSS .= "  body { background-image:url(".$gallery->album->fields['background']."); }\n";
    }

    if ($gallery->album->fields["textcolor"]) {
        $customCSS .= "  body, tf { color:".$gallery->album->fields['textcolor']."; } \n";
    }

    return $customCSS;
}

/**
 * returns the a HTML string containg links to the upper albums
 *
 * @param object    $album
 * @param boolean   $withCurrentAlbum
 * @return string   $pathArray
 */
function returnToPathArray($album = NULL, $withCurrentAlbum = true) {
    global $gallery;

    $pathArray = array();

    $upArrowAltText = gTranslate('common', "navigate _UP");
    $upArrow = gImage('icons/navigation/nav_home.gif', $upArrowAltText);

    $accesskey = getAccessKey($upArrowAltText);
    $lastUpArrowAltText = $upArrowAltText . ' '.
	   sprintf(gTranslate('common', "(accesskey '%s')"), $accesskey);

    $lastUpArrow = gImage('icons/navigation/nav_home.gif', $lastUpArrowAltText);

    if (!empty($album)) {
        if ($album->fields['returnto'] != 'no') {
            $parents = $album->getParentAlbums($withCurrentAlbum);
            $numParents = sizeof($parents);
            $i = 0;
            foreach ($parents as $navAlbum) {
                $i++;
                $link = $navAlbum['prefixText'] .': ';
                if($i == $numParents) {
                    $link .= galleryLink($navAlbum['url'], $navAlbum['title'] ."&nbsp;$lastUpArrow",
                    array('accesskey' => $accesskey), '', false, false);
                }
                else {
                    $link .= galleryLink($navAlbum['url'], $navAlbum['title'] ."&nbsp;$upArrow",
                    array(), '', false, false);
                }
                $pathArray[] = $link;
            }
        }
    }
    else {
        $pathArray[] = sprintf(
            gTranslate('common', "Gallery: %s"),
            galleryLink(
                makeGalleryUrl("albums.php"),
                clearGalleryTitle() ."&nbsp;$lastUpArrow",
                array('accesskey' => $accesskey), '', false, false)
        );
    }

    return $pathArray;
}
?>