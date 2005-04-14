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
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 * $Id$
 */
?>
<?php

require_once(dirname(__FILE__) . '/init.php');
require(dirname(__FILE__) . '/classes/remote/GalleryRemoteProperties.php');

//---------------------------------------------------------
//-- check that we are not being called from the browser --
if (!getRequestVar('cmd')) {
	echo 'This page is not meant to be accessed from your browser.  ';
	echo 'If you would like to use Gallery Remote, please refer to ';
	echo 'Gallery\'s website located at ';
	echo "<a href='$gallery->url'>$gallery->url</a>";
	exit;
}


/*
 * Set content type
 */
header("Content-type: text/plain");

/*
 * Start buffering output
 */
//if($gallery->app->debug == "no") {
//	@ob_start();
//}

/*
 * Gallery remote protocol version 2.10
 */
$GR_VER['MAJ'] = 2;
$GR_VER['MIN'] = 15;


/*
 * Protocol result codes
 */

$GR_STAT['SUCCESS']             = 0;

$GR_STAT['PROTOCOL_MAJOR_VERSION_INVALID'] 	= 101;
$GR_STAT['PROTOCOL_MINOR_VERSION_INVALID'] 	= 102;
$GR_STAT['PROTOCOL_VERSION_FORMAT_INVALID'] = 103;
$GR_STAT['PROTOCOL_VERSION_MISSING']   		= 104;

$GR_STAT['PASSWORD_WRONG']      = 201;
$GR_STAT['LOGIN_MISSING']       = 202;

$GR_STAT['UNKNOWN_COMMAND']		= 301;

$GR_STAT['NO_ADD_PERMISSION']	= 401;
$GR_STAT['NO_FILENAME']			= 402;
$GR_STAT['UPLOAD_PHOTO_FAIL']	= 403;
$GR_STAT['NO_WRITE_PERMISSION']	= 404;

$GR_STAT['NO_CREATE_ALBUM_PERMISSION']	= 501;
$GR_STAT['CREATE_ALBUM_FAILED']			= 502;
$GR_STAT['MOVE_ALBUM_FAILED']	= 503;
$GR_STAT['ROTATE_IMAGE_FAILED'] = 504;




$response = new Properties();
$protocol_version = getRequestVar('protocol_version');
check_proto_version( $response );

// some debug output
//$response->setProperty( "debug_session_albumName", $gallery->session->albumName);
$response->setProperty( "debug_album", empty($gallery->album->fields["name"]) ? '' : $gallery->album->fields["name"]);
$response->setProperty( "debug_gallery_version", $gallery->version);

if ($gallery->user) {
	$response->setProperty( "debug_user", $gallery->user->getUsername());
	$response->setProperty( "debug_user_type", get_class($gallery->user));
	$response->setProperty( "debug_user_already_logged_in", $gallery->user->isLoggedIn());
} else {
	$response->setProperty( "debug_user", "NO_USER");
}

// -- Handle request --

switch(getRequestVar('cmd') === 0 ? '' : getRequestVar('cmd')) {
	case 'login':
		gr_login( $gallery, $response, getRequestVar('uname'), getRequestVar('password') );
		break;
	case 'fetch-albums':
		gr_fetch_albums( $gallery, $response );
		break;
	case 'fetch-albums-prune':
		gr_fetch_albums_prune( $gallery, $response, getRequestVar('check_writeable') );
		break;
	case 'add-item':
		gr_add_item( $gallery, $response, $_FILES['userfile']['tmp_name'], $_FILES['userfile']['name'],
getRequestVar('caption'), getRequestVar('force_filename'), getRequestVar('auto_rotate') );
		break;
	case 'album-properties':
		gr_album_properties( $gallery, $response );
		break;
	case 'new-album':
		gr_new_album( $gallery, $response, getRequestVar('newAlbumName'), getRequestVar('newAlbumTitle'),
getRequestVar('newAlbumDesc') );
		break;
	case 'fetch-album-images':
		gr_fetch_album_images( $gallery, $response, getRequestVar('albums_too') );
		break;
	case 'move-album':
		gr_move_album( $gallery, $response, getRequestVar('set_destalbumName') );
		break;
	default:
		$response->setProperty( 'status', $GR_STAT['UNKNOWN_COMMAND'] );
		$response->setProperty( 'status_text', "Command '" . getRequestVar('cmd') . "' unknown." );
		break;
}

//@ob_end_clean();
echo $response->listprops();
//end of processing



function gr_login( &$gallery, &$response, &$uname, &$password ) {
	global $GR_STAT, $GR_VER;

	if (!$uname | !$password) {
		$response->setProperty( "server_version", $GR_VER['MAJ'].".".$GR_VER['MIN'] );
		$response->setProperty( 'status', $GR_STAT['LOGIN_MISSING'] );
		$response->setProperty( 'status_text', 'Login parameters not found.' );
		return 0;
	}

	if ($gallery->user->isLoggedIn()) {
		$response->setProperty( 'server_version', $GR_VER['MAJ'].'.'.$GR_VER['MIN'] );
		$response->setProperty( 'status', $GR_STAT['SUCCESS'] );
		$response->setProperty( 'status_text', 'Login successful.' );
		return 1;
	}

	$tmpUser = $gallery->userDB->getUserByUsername($uname);

	if ($tmpUser && $tmpUser->isCorrectPassword($password)) {
		// log user in
		$gallery->session->username = $uname;
 
		$response->setProperty( 'debug_user', $tmpUser->getUsername());
		$response->setProperty( 'debug_user_type', get_class($tmpUser));

		$response->setProperty( 'server_version', $GR_VER['MAJ'].'.'.$GR_VER['MIN'] );
		$response->setProperty( 'status', $GR_STAT['SUCCESS'] );
		$response->setProperty( 'status_text', 'Login successful.' );
		return 1;
	} else {
		$response->setProperty( 'status', $GR_STAT['PASSWORD_WRONG'] );
		$response->setProperty( 'status_text', 'Password incorrect.' );
		return 0;
	}

}

function gr_fetch_albums( &$gallery, &$response ) {

	global $GR_STAT;

	$albumDB = new AlbumDB(FALSE);
	$mynumalbums = $albumDB->numAlbums($gallery->user);
	$album_index = 0;

	// display all albums that the user can move album to
	for ($i=1; $i<=$mynumalbums; $i++) {
		$myAlbum=$albumDB->getAlbum($gallery->user, $i);
		// if readable, add this plus readable nested albums
		if ($gallery->user->canReadAlbum($myAlbum)) {
			add_album( $myAlbum, $album_index, 0, $response );
			appendNestedAlbums( $myAlbum, $album_index, $response );
		}
	}

	// add album count
	$response->setProperty( 'album_count', $album_index );

	$response->setProperty( 'can_create_root', $gallery->user->canCreateAlbums() ? 'yes' : 'no' );

	// add status and repond
	$response->setProperty( 'status', $GR_STAT['SUCCESS'] );
	$response->setProperty( 'status_text', 'Fetch albums successful.' );
	return 1;

}

function gr_fetch_albums_prune( &$gallery, &$response, $check_writeable ) {

	global $GR_STAT, $myMark;

	$albumDB = new AlbumDB(FALSE);
	$album_count = 0;

	if ($check_writeable != 'no') {
	    mark_and_sweep($albumDB, TRUE);
	} else {
		mark_and_sweep($albumDB, FALSE);
	}

	foreach ($albumDB->albumList as $album) {
		if ($myMark[$album->fields["name"]]) {
			add_album( $album, $album_count, $album->fields['parentAlbumName'], $response );
		}
	}

	// add album count
	$response->setProperty( "album_count", $album_count );

	$response->setProperty( 'can_create_root', $gallery->user->canCreateAlbums() ? 'yes' : 'no' );

	// add status and repond
	$response->setProperty( "status", $GR_STAT['SUCCESS'] );
	$response->setProperty( "status_text", "Fetch albums successful." );
	return 1;

}

function gr_add_item( &$gallery, &$response, &$userfile, &$userfile_name, &$caption, &$force_filename, &$auto_rotate ) {

	global $GR_STAT, $temp_files;

	if (!$gallery->user->canAddToAlbum($gallery->album)) {
		$response->setProperty( 'status', $GR_STAT['NO_ADD_PERMISSION'] );
		$response->setProperty( 'status_text', 'User cannot add to album.' );
		return 0;
	}

	if(!empty($auto_rotate)) {
		if($auto_rotate == 'yes') {
			$gallery->app->autorotate = 'yes';
		} else {
			$gallery->app->autorotate = 'no';
		}
	}

	if(strtolower(substr($userfile,0,7)) == 'http://') {
		if(!$gallery->user->isAdmin()) {
			$response->setProperty( 'status', $GR_STAT['NO_ADD_PERMISSION'] );
			$response->setProperty( 'status_text', 'Only administrators can fetch remote images.' );
			return 0;
		}
		$acceptable = array_merge(acceptableMovieList(),acceptableImageList());
		foreach($acceptable as $imagetype) {
			if(strtolower(substr($userfile,strlen($imagetype) * -1)) == $imagetype) {
				$userfile_name = substr(md5(microtime()),0,8).'.'.$imagetype;
			}
		}
		if($tmpImage = fs_file_get_contents($userfile)) {
			$userfile = tempnam($gallery->app->tmpDir,'img');
			if(!$fhandle = fopen($userfile,'wb')) {
				$response->setProperty( 'status', $GR_STAT['UPLOAD_PHOTO_FAIL'] );
				$response->setProperty( 'status_text', 'Could not open tmp file to write remote image.' );
				return 0;
			}
			if(!fwrite($fhandle,$tmpImage)) {
				$response->setProperty( 'status', $GR_STAT['UPLOAD_PHOTO_FAIL'] );
				$response->setProperty( 'status_text', 'Could not write to tmp file.' );
				fclose($fhandle);
				return 0;
			}
			fclose($fhandle);
		} else {
			$response->setProperty( 'status', $GR_STAT['UPLOAD_PHOTO_FAIL'] );
			$response->setProperty( 'status_text', 'Could not fetch image, HTTP wrapper may be disabled or invalid URL' );
			return 0;
		}
	}

	if (!$userfile_name) {
		$response->setProperty( 'status', $GR_STAT['NO_FILENAME'] );
		$response->setProperty( 'status_text', 'Filename not specified.' );
		return 0;
	}

	if(!empty($force_filename)) {
		$name = $force_filename;
	} else {
		$name = $userfile_name;
	}
	$tag = ereg_replace(".*\.([^\.]*)$", "\\1", $userfile_name);
	$tag = strtolower($tag);

	if ($name) {
		$error = processFile($userfile, $tag, $name, $caption);
	}

	if ($temp_files) {
		/* Clean up the temporary url file */
		foreach ($temp_files as $tf => $junk) {
			fs_unlink($tf);
		}
	}
	@fs_unlink($userfile);

	if ($error) {
		$response->setProperty( 'status', $GR_STAT['UPLOAD_PHOTO_FAIL'] );
		$response->setProperty( 'status_text', 'Upload failed: \''.$error.'\'.' );
		return 0;
	} else {
		$gallery->album->save(array(i18n('Image added')));
		$response->setProperty( 'status', $GR_STAT['SUCCESS'] );
		$response->setProperty( 'status_text', 'Add photo successful.' );
		return 1;
	}

}

function gr_album_properties( &$gallery, &$response ) {

	global $GR_STAT;

	$resize_dimension = $gallery->album->fields['resize_size'];
	if ($resize_dimension == 'off') {
		$resize_dimension = 0;
	}

	$response->setProperty( 'auto_resize', $resize_dimension );

	$max_dimension = $gallery->album->fields['max_size'];
	if ($max_dimension == 'off') {
		$max_dimension = 0;
	}

	$response->setProperty( 'max_size', $max_dimension );

	$extrafields = $gallery->album->getExtraFields();
	if ($extrafields) {
		$response->setProperty( 'extra_fields', implode(",", $extrafields) );
	}
	$response->setProperty( 'add_to_beginning', $gallery->album->fields['add_to_beginning'] );

	$response->setProperty( 'status', $GR_STAT['SUCCESS'] );
	$response->setProperty( 'status_text', 'Album properties retrieved successfully.' );
	return 1;

}

function gr_new_album( &$gallery, &$response, &$newAlbumName, &$newAlbumTitle, &$newAlbumDesc ) {

	global $GR_STAT;

	if(get_magic_quotes_gpc()) {
		$newAlbumName = stripslashes($newAlbumName);
		$newAlbumTitle = stripslashes($newAlbumTitle);
		$newAlbumDesc = stripslashes($newAlbumDesc);
	}

	if(isset($gallery->album) && isset($gallery->album->fields["name"])) {
		$canAddAlbum = $gallery->user->canCreateSubAlbum($gallery->album);
	} else {
		$canAddAlbum = $gallery->user->canCreateAlbums();
	}

	if(!$canAddAlbum) {
		$response->setProperty( 'status', $GR_STAT['NO_CREATE_ALBUM_PERMISSION'] );
		$response->setProperty( 'status_text', 'A new album could not be created because the user does not have permission to do so.' );
		return 0;
	}

	if ($returnVal = createNewAlbum( $gallery->session->albumName,
		$newAlbumName, $newAlbumTitle, $newAlbumDesc )) {
		// set status and message
		$response->setProperty( 'status', $GR_STAT['SUCCESS'] );
		$response->setProperty( 'status_text', 'New album created successfully.' );
		$response->setProperty( 'album_name', $returnVal );
		return 1;
	} else {
		// set status and message
		$response->setProperty( 'status', $GR_STAT['CREATE_ALBUM_FAILED'] );
		$response->setProperty( 'status_text', 'Create album failed.' );
		return 0;
	}

}

function gr_fetch_album_images( &$gallery, &$response, $albums_too ) {

	global $GR_STAT;
	
	if ($albums_too == 'yes') {
	    $albums_too = TRUE;
	} else {
		$albums_too = FALSE;
	}

	$tmpImageNum = 0;
	
	if (isset($gallery->album)) {
		if (isset($gallery->album->photos)) {
			foreach($gallery->album->photos as $albumItemObj) {
				if(!$albumItemObj->isAlbum()) { //Make sure this object is a picture, not an album
					$tmpImageNum++;

					if ($gallery->user->canViewFullImages($gallery->album) || !$albumItemObj->isResized()) {
						$response->setProperty( 'image.name.'.$tmpImageNum, $albumItemObj->image->name.'.'.$albumItemObj->image->type );
						$fullSize = $albumItemObj->getDimensions(1);
						$response->setProperty( 'image.raw_width.'.$tmpImageNum, $fullSize[0] );
						$response->setProperty( 'image.raw_height.'.$tmpImageNum, $fullSize[1] );
						$response->setProperty( 'image.raw_filesize.'.$tmpImageNum, $albumItemObj->getFileSize(1) );
					}

					if ($albumItemObj->isResized()) {
						$response->setProperty( 'image.resizedName.'.$tmpImageNum, $albumItemObj->image->resizedName.'.'.$albumItemObj->image->type );
						$resizedSize = $albumItemObj->getDimensions(0);
						$response->setProperty( 'image.resized_width.'.$tmpImageNum, $resizedSize[0] );
						$response->setProperty( 'image.resized_height.'.$tmpImageNum, $resizedSize[1] );
					}

					$response->setProperty( 'image.thumbName.'.$tmpImageNum, $albumItemObj->thumbnail->name.'.'.$albumItemObj->image->type );
					$thumbnailSize = $albumItemObj->getThumbDimensions();
					$response->setProperty( 'image.thumb_width.'.$tmpImageNum, $thumbnailSize[0] );
					$response->setProperty( 'image.thumb_height.'.$tmpImageNum, $thumbnailSize[1] );

					$response->setProperty( 'image.caption.'.$tmpImageNum, $albumItemObj->caption );
					if(count($albumItemObj->extraFields)) { //if there are extra fields for this image
						foreach($albumItemObj->extraFields as $extraFieldKey => $extraFieldName) {
							if(strlen($extraFieldName)) {
								$response->setProperty( 'image.extrafield.'.$extraFieldKey.'.'.$tmpImageNum, $extraFieldName );
							}
						}
					}
					$response->setProperty( 'image.clicks.'.$tmpImageNum, $albumItemObj->clicks );
					$response->setProperty( 'image.capturedate.year.'.$tmpImageNum, $albumItemObj->itemCaptureDate['year'] );
					$response->setProperty( 'image.capturedate.mon.'.$tmpImageNum, $albumItemObj->itemCaptureDate['mon'] );
					$response->setProperty( 'image.capturedate.mday.'.$tmpImageNum, $albumItemObj->itemCaptureDate['mday'] );
					$response->setProperty( 'image.capturedate.hours.'.$tmpImageNum, $albumItemObj->itemCaptureDate['hours'] );
					$response->setProperty( 'image.capturedate.minutes.'.$tmpImageNum, $albumItemObj->itemCaptureDate['minutes'] );
					$response->setProperty( 'image.capturedate.seconds.'.$tmpImageNum, $albumItemObj->itemCaptureDate['seconds'] );
					$response->setProperty( 'image.hidden.'.$tmpImageNum, $albumItemObj->isHidden()?"yes":"no" );
				} else {
					if ($albums_too) {
						if (! isset($albumDB)) {
							$albumDB = new AlbumDB(FALSE);
						}

						$myAlbum = $albumDB->getAlbumByName($albumItemObj->getAlbumName(), FALSE);

						if ($gallery->user->canReadAlbum($myAlbum)) {
							$tmpImageNum++;

							$response->setProperty( 'album.name.'.$tmpImageNum, $albumItemObj->getAlbumName() );
							$response->setProperty( 'album.hidden.'.$tmpImageNum, $myAlbum->isHiddenRecurse()?'yes':'no' );
						}
					}
				}
			}
		}
	} else {
		// we're in the root album: just list root albums
		if ($albums_too) {
			$albumDB = new AlbumDB(FALSE);
			foreach ($albumDB->albumList as $myAlbum) {
				if ($myAlbum->isRoot() && $gallery->user->canReadAlbum($myAlbum)) {
				    $tmpImageNum++;
		 
					$response->setProperty( 'album.name.'.$tmpImageNum, $myAlbum->fields['name'] );
					// root albums can't be hidden
					$response->setProperty( 'album.hidden.'.$tmpImageNum, 'no' );
				}
			}
		}
	}
	
	$response->setProperty( 'image_count', $tmpImageNum );
	if (isset($gallery->album)) {
	    $response->setProperty( 'baseurl', $gallery->album->getAlbumDirURL('full').'/' );
	}

	$response->setProperty( 'status', $GR_STAT['SUCCESS'] );
	$response->setProperty( 'status_text', 'Fetch images successful.' );
	return 1;

}

function gr_move_album( &$gallery, &$response, &$set_destalbumName ) {

	global $GR_STAT;

	// check that source and destination albums exist
	$albumDB = new AlbumDB(FALSE);
	$sourceAlbum = $albumDB->getAlbumByName($gallery->album->fields["name"]);

	if (empty($sourceAlbum)) {
		$response->setProperty( 'status', $GR_STAT['MOVE_ALBUM_FAILED'] );
		$response->setProperty( 'status_text', 'Source album doesnt exist' );
		return 0;
	}

	if ($set_destalbumName != '0') {
		$destAlbum = $albumDB->getAlbumByName($set_destalbumName);

		if (empty($destAlbum)) {
			$response->setProperty( 'status', $GR_STAT['MOVE_ALBUM_FAILED'] );
			$response->setProperty( 'status_text', 'Destination album doesnt exist' );
			return 0;
		}
	}

	if(empty($set_destalbumName) && $set_destalbumName != '0') {
		$response->setProperty( 'status', $GR_STAT['MOVE_ALBUM_FAILED'] );
		$response->setProperty( 'status_text', 'You must specify a destination album.' );
		return 0;
	}
	if($set_destalbumName == $gallery->album->fields['name']) {
		$response->setProperty( 'status', $GR_STAT['MOVE_ALBUM_FAILED'] );
		$response->setProperty( 'status_text', 'Album and destination album cannot be the same.' );
		return 0;
	}

	if($gallery->album->isRoot()) {
		if($set_destalbumName == '0') {
			$response->setProperty( 'status', $GR_STAT['MOVE_ALBUM_FAILED'] );
			$response->setProperty( 'status_text', 'Album is already in specified destination album.' );
			return 0;
		}
		if(checkIfNestedAlbum($gallery->album,$destAlbum)) {
			$response->setProperty( 'status', $GR_STAT['MOVE_ALBUM_FAILED'] );
			$response->setProperty( 'status_text', 'Cannot move album into a sub-album of itself.' );
			return 0;
		}
		if($gallery->user->canWriteToAlbum($gallery->album) && $gallery->user->canWriteToAlbum($destAlbum)) {
			$gallery->album->fields['parentAlbumName'] = $set_destalbumName;
			$gallery->album->save();
			$destAlbum->addNestedAlbum($gallery->album->fields['name']);
			$destAlbum->save();
			$response->setProperty( 'status', $GR_STAT['SUCCESS'] );
			$response->setProperty( 'status_text', 'Move album successful.' );
			return 1;
		} else {
			$response->setProperty( 'status', $GR_STAT['NO_WRITE_PERMISSION'] );
			$response->setProperty( 'status_text', 'No write permission to album or destination.' );
			return 0;
		}
	}

	if(checkIfNestedAlbum($gallery->album,$destAlbum)) {
		$response->setProperty( 'status', $GR_STAT['MOVE_ALBUM_FAILED'] );
		$response->setProperty( 'status_text', 'Cannot move album into a sub-album of itself.' );
		return 0;
	}
	if($gallery->album->fields['parentAlbumName'] == $set_destalbumName) {
		$response->setProperty( 'status', $GR_STAT['MOVE_ALBUM_FAILED'] );
		$response->setProperty( 'status_text', 'Album is already in specified destination album.' );
		return 0;
	}

	if($set_destalbumName == '0') {
		$parentAlbum = new Album();
		$parentAlbum->load($gallery->album->fields['parentAlbumName']);
		if($gallery->user->canWriteToAlbum($parentAlbum) && $gallery->user->canCreateAlbums()) {
			$gallery->album->fields['parentAlbumName'] = 0;
			$parentAlbum->deletePhoto($parentAlbum->getAlbumIndex($gallery->album->fields['name']),0,0);
			$parentAlbum->save();
			$gallery->album->save();
			$response->setProperty( 'status', $GR_STAT['SUCCESS'] );
			$response->setProperty( 'status_text', 'Move album successful.' );
			return 1;
		} else {
			$response->setProperty( 'status', $GR_STAT['NO_WRITE_PERMISSION'] );
			$response->setProperty( 'status_text', 'No write permission to album or destination.' );
			return 0;
		}
	} else {
		if($gallery->user->canWriteToAlbum($gallery->album) && $gallery->user->canWriteToAlbum($destAlbum)) {
			$parentAlbum = new Album();
			$parentAlbum->load($gallery->album->fields['parentAlbumName']);
			$destAlbum->addNestedAlbum($gallery->album->fields['name']); //add album to new
			$gallery->album->fields['parentAlbumName'] = $destAlbum->fields['name'];
			$parentAlbum->deletePhoto($parentAlbum->getAlbumIndex($gallery->album->fields['name']),0,0); //delete album from old
			$parentAlbum->save();
			$destAlbum->save();
			$gallery->album->save();
			$response->setProperty( 'status', $GR_STAT['SUCCESS'] );
			$response->setProperty( 'status_text', 'Move album successful.' );
			return 1;
		} else {
			$response->setProperty( 'status', $GR_STAT['NO_WRITE_PERMISSION'] );
			$response->setProperty( 'status_text', 'No write permission to album or destination.' );
			return 0;
		}
	}

}

/*
function gr_move_image( &$gallery, &$response ) {

}

function gr_change_index( &$gallery, &$response ) {

	global $GR_STAT;

	if(!isset($gallery->album)) { //if reordering root album
		if($gallery->user->canCreateAlbums() or $gallery->user->isAdmin()) {
			$albumDB = new AlbumDB(FALSE);
			$albumDB->moveAlbum($gallery->user, $index, $newIndex);
			$albumDB->save();
			$response->setProperty( 'status', $GR_STAT['SUCCESS'] );
			$response->setProperty( 'status_text', 'Change index successful.' );
		} else {
			$response->setProperty( 'status', $GR_STAT['NO_WRITE_PERMISSION'] );
			$response->setProperty( 'status_text', 'No write permission.' );
		}
	} else {
		if($gallery->user->canWriteToAlbum($gallery->album)) {
			$gallery->album->movePhoto($index,$newIndex-1);
			$gallery->album->save();
			$response->setProperty( 'status', $GR_STAT['SUCCESS'] );
			$response->setProperty( 'status_text', 'Change index successful.' );
		} else {
			$response->setProperty( 'status', $GR_STAT['NO_WRITE_PERMISSION'] );
			$response->setProperty( 'status_text', 'No write permission.' );
		}
	}
}

function gr_rotate_image( &$gallery, &$response ) {

	global $GR_STAT;

	if(is_object($gallery->album->photos[$index-1]->image)) {
		if($gallery->user->canWriteToAlbum($gallery->album)) {
			if(isset($index) && $gallery->session->albumName) {
				set_time_limit($gallery->app->timeLimit);
				$gallery->album->rotatePhoto($index,$rotate);
				$gallery->album->save();
				$response->setProperty( 'status', $GR_STAT['SUCCESS'] );
				$response->setProperty( 'status_text', 'Image successfully rotated.' );
			}
		} else {
			$response->setProperty( 'status', $GR_STAT['NO_WRITE_PERMISSION'] );
			$response->setProperty( 'status_text', 'No write permission.' );
		}
	} else {
		$response->setProperty( 'status', $GR_STAT['ROTATE_IMAGE_FAILED'] );
		$response->setProperty( 'status_text', 'Specified index is not an image.' );
	}
}
*/



function check_proto_version( &$response ) {
	global $protocol_version, $GR_STAT, $GR_VER;

	// this method returns without modifying the $response if the version
	// presented by the client is acceptable.  otherwise, it returns directly
	// with a status code appropriate to the problem.

	if ( isset( $protocol_version ) ) {
		// check version format
		if ( eregi( "^([2-9]{1,2})\.([0-9]{1,2})$", $protocol_version, $ver_regs ) ) {
			// version string is valid
			$major_ver = $ver_regs[1];
			$minor_ver = $ver_regs[2];
			if ( $major_ver != $GR_VER['MAJ'] ) {
				$response->setProperty( "status", $GR_STAT['PROTOCOL_MAJOR_VERSION_INVALID'] );
				$response->setProperty( "status_text", "Protocol major version invalid." );

				// return the response
				echo $response->listprops();
				exit;
			}
		// else version compatible, proceed
		} else {
			$response->setProperty( "status", $GR_STAT['PROTOCOL_VERSION_FORMAT_INVALID'] );
			$response->setProperty( "status_text", "Protocol version format invalid." );

			// return the response
			echo $response->listprops();
			exit;
		}
	} else {
		// must specify protocol_version
		$response->setProperty( "status", $GR_STAT['PROTOCOL_VERSION_MISSING'] );
		$response->setProperty( "status_text", "Protocol version not found." );

		// return the response
		echo $response->listprops();
		exit;
	}
}


function checkIfNestedAlbum(&$startAlbum,&$possibleSub) {
	if(count($startAlbum->photos) < 1) {
		return FALSE;
	}
	foreach($startAlbum->photos as $subItem) {
		if($subItem->isAlbum()) { //if it's an album
			if($subItem->getAlbumName() == $possibleSub->fields['name']) {
				return TRUE; //possible sub was found to be a sub of startalbum
			} else {
				$subAlbum = new Album();
				$subAlbum->load($subItem->getAlbumName());
				if(checkIfNestedAlbum($subAlbum,$possibleSub)) {
					return TRUE;
				}
			}
		}
	}
	return FALSE;
}
//------------------------------------------------
//-- FUNCTIONS
//--
function appendNestedAlbums( &$myAlbum, &$album_index, &$response ) {
    global $gallery;
	
	$parent_index = $album_index;

	$numPhotos = $myAlbum->numPhotos(1);
    
    for ($i=1; $i <= $numPhotos; $i++) {
        if ($myAlbum->isAlbum($i)) {
            $myName = $myAlbum->getAlbumName($i);
            $nestedAlbum = new Album();
            $nestedAlbum->load($myName);
            if ($gallery->user->canReadAlbum($nestedAlbum)) {
            	// if readable, add this album plus readable nested albums
				add_album( $nestedAlbum, $album_index, $parent_index, $response );
                appendNestedAlbums($nestedAlbum, $album_index, $response );
            }
        }
    }
}

function add_album( &$myAlbum, &$album_index, $parent_index, &$response ){
	global $gallery;
	
	// increment index
	$album_index++;

	// fetch name & title
	$albumName = $myAlbum->fields['name'];
	$albumTitle = $myAlbum->fields['title'];
	
	// write name, title and parent
	$response->setProperty( "album.name.$album_index", $albumName );
	$response->setProperty( "album.title.$album_index", $albumTitle );
	$response->setProperty( "album.summary.$album_index", $myAlbum->fields['summary'] );
	$response->setProperty( "album.parent.$album_index", $parent_index );
	$response->setProperty( "album.resize_size.$album_index", $myAlbum->fields['resize_size'] == 'off' ? 0 : $myAlbum->fields['resize_size'] );
	$response->setProperty( "album.max_size.$album_index", $myAlbum->fields['max_size'] == 'off' ? 0 : $myAlbum->fields['max_size'] );
	$response->setProperty( "album.thumb_size.$album_index", $myAlbum->fields['thumb_size'] );

	// write permissions
	$can_add = $gallery->user->canAddToAlbum($myAlbum) ? "true" : "false";
	$can_write = $gallery->user->canWriteToAlbum($myAlbum) ? "true" : "false";
	$can_delete_from = $gallery->user->canDeleteFromAlbum($myAlbum) ? "true" : "false";
	$can_delete_alb = $gallery->user->canDeleteAlbum($myAlbum) ? "true" : "false";
	$can_create_sub = $gallery->user->canCreateSubAlbum($myAlbum) ? "true" : "false";
	
	$response->setProperty( "album.perms.add.$album_index", $can_add );
	$response->setProperty( "album.perms.write.$album_index", $can_write );
	$response->setProperty( "album.perms.del_item.$album_index", $can_delete_from );
	$response->setProperty( "album.perms.del_alb.$album_index", $can_delete_alb );
	$response->setProperty( "album.perms.create_sub.$album_index", $can_create_sub );

	$extrafields = $myAlbum->getExtraFields();
	if ($extrafields) {
		$response->setProperty( "album.info.extrafields.$album_index", implode(",", $extrafields) );
	}
}

//------------------------------------------------
//-- this process function is identical to that in save_photos.
//-- Ugh.

//-- Renamed this function because it conflicts with
//-- another one that happens when upgrading the album
//-- Speaking of which, trying to upload a picture to 
//-- an album which is not yet upgraded fails. Need warning
//-- in the docs.

function processFile($file, $tag, $name, $setCaption="") {
    global $gallery;
    global $temp_files;

    if (!strcmp($tag, "zip")) {
        if (!$gallery->app->feature["zip"]) {
            $error = "Zip not supported";
            continue;
        }
        /* Figure out what files we can handle */
        list($files, $status) = exec_internal(
            fs_import_filename($gallery->app->zipinfo, 1) .
            " -1 " .
            fs_import_filename($file, 1));
        sort($files);
        foreach ($files as $pic_path) {
            $pic = basename($pic_path);
            $tag = ereg_replace(".*\.([^\.]*)$", "\\1", $pic);
            $tag = strtolower($tag);

            if (acceptableFormat($tag) || !strcmp($tag, "zip")) {
                $cmd_pic_path = str_replace("[", "\[", $pic_path);
                $cmd_pic_path = str_replace("]", "\]", $cmd_pic_path);
                exec_wrapper(fs_import_filename($gallery->app->unzip, 1) .
                         " -j -o " .
                         fs_import_filename($file, 1) .
                         " \"" .
                         fs_import_filename($cmd_pic_path, 1) .
                         "\" -d " .
                         fs_import_filename($gallery->app->tmpDir, 1));
                processFile($gallery->app->tmpDir . "/$pic", $tag, $pic, $setCaption);
                fs_unlink($gallery->app->tmpDir . "/$pic");
            }
        }
    } else {
        // remove %20 and the like from name
        $name = urldecode($name);
	if (get_magic_quotes_gpc()) {
		$name = stripslashes($name);
	}
        // parse out original filename without extension
        $originalFilename = eregi_replace(".$tag$", "", $name);
        // replace multiple non-word characters with a single "_"
        $mangledFilename = ereg_replace("[^[:alnum:]]", "_", $originalFilename);

        /* Get rid of extra underscores */
        $mangledFilename = ereg_replace("_+", "_", $mangledFilename);
        $mangledFilename = ereg_replace("(^_|_$)", "", $mangledFilename);
   
        /*
        need to prevent users from using original filenames that are purely numeric.
        Purely numeric filenames mess up the rewriterules that we use for mod_rewrite
        specifically:
        RewriteRule ^([^\.\?/]+)/([0-9]+)$  /~jpk/gallery/view_photo.php?set_albumName=$1&index=$2  [QSA]
        */
   
        if (ereg("^([0-9]+)$", $mangledFilename)) {
            $mangledFilename .= "_G";
        }
   
        set_time_limit($gallery->app->timeLimit);
        
        if (acceptableFormat($tag)) {
   
		    /*
		     * Move the uploaded image to our temporary directory
		     * using move_uploaded_file so that we work around
		     * issues with the open_basedir restriction.
		     */
		    if (function_exists('move_uploaded_file')) {
				$newFile = tempnam($gallery->app->tmpDir, "gallery");
				if (move_uploaded_file($file, $newFile)) {
				    $file = $newFile;
		
				    /* Make sure we remove this file when we're done */
				    $temp_files[$file] = 1;
				}
		    }

            if ($setCaption) {
		if (get_magic_quotes_gpc()) {
			$setCaption = stripslashes($setCaption);
		}
                $caption = $setCaption;
            } else {
                $caption = "";
            }

			// add the extra fields
			$myExtraFields = array();
			foreach ($gallery->album->getExtraFields() as $field) {
				//$fieldname = "extrafield_$field";
				//echo "Looking for extra field $fieldname\n";

				// The way it should be done now
				$value = isset($_POST[("extrafield.".$field)]) ? $_POST[("extrafield.".$field)] : '';
				//echo "Got extra field $field = $value\n";
				if ($value) {
					if (get_magic_quotes_gpc()) {
						$value = stripslashes($value);
					}
					//echo "Setting field $field\n";
					$myExtraFields[$field] = $value;
				}

				// Deprecated
				$value = isset($_POST[("extrafield_".$field)]) ? $_POST[("extrafield_".$field)] : '';
				//echo "Got extra field $field = $value\n";
				if ($value) {
					if (get_magic_quotes_gpc()) {
						$value = stripslashes($value);
					}
					//echo "Setting field $field\n";
					$myExtraFields[$field] = $value;
				}
			}
			//echo "Extra fields ". implode("/", array_keys($myExtraFields)) ." -- ". implode("/", array_values($myExtraFields)) ."\n";

	        $err = $gallery->album->addPhoto($file, $tag, $mangledFilename, $caption, "", $myExtraFields, $gallery->user->getUid());
	        if ($err)  {
	        	$error = "$err";
	        }
	    } else {
	    	$error = "Skipping $name (can't handle '$tag' format)";
	    }
    }
    
    return empty($error) ? '' : $error;
}

function mark_and_sweep(&$albumDB, $checkWriteable = TRUE) {
	global $gallery, $myMark;

	foreach ($albumDB->albumList as $myAlbum) {
		// echo "mark_and_sweep: ".$myAlbum->fields["name"]."\n";
		if (!$checkWriteable || $gallery->user->canAddToAlbum($myAlbum)) {
			sweep($albumDB, $myAlbum);
			// echo "mark_and_sweep: ".$myMark[$myAlbum->fields["name"]]."\n";
		}
	}
}

function sweep(&$albumDB, &$myAlbum) {
	global $myMark;
	// echo "sweep: ".$myMark[$myAlbum->fields["name"]]."\n";
	if (empty($myMark[$myAlbum->fields["name"]])) {
		// echo "sweep: ".$myAlbum->fields["name"]." is not marked: marking\n";
		$myMark[$myAlbum->fields["name"]] = TRUE;
		// echo "sweep: ".$myMark[$myAlbum->fields["name"]]."\n";

		$parentName = $myAlbum->fields["parentAlbumName"];
		if ($parentName) {
			// echo "sweep: got parent ".$parentName."\n";
			$parentAlbum = $albumDB->getAlbumByName($parentName, FALSE);

			sweep($albumDB, $parentAlbum);
		}
	}
}

?>
