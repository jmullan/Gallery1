<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2005 Bharat Mediratta
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

?>