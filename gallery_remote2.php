<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2002 Bharat Mediratta
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
 */

// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
	print "Security violation\n"; 
	exit;
}

require($GALLERY_BASEDIR . "init.php");
require($GALLERY_BASEDIR . "classes/remote/GalleryRemoteProperties.php");


/*
 * Set content type
 */
header("Content-type: text/plain");


/*
 * Gallery remote protocol version 2.0
 */
$GR_VER['MAJ'] = 2;
$GR_VER['MIN'] = 0;

$oldest_remote_protocol_version_maj = 2; // oldest proto we support (2.0)
$oldest_remote_protocol_version_min = 0;
$newest_remote_protocol_version_maj = 2; // newest proto we support (2.0)
$newest_remote_protocol_version_min = 0;


/*
 * Protocol result codes
 */

$GR_STAT['SUCCESS']             = 0;

$GR_STAT['PROTOCOL_MAJOR_VERSION_INVALID'] = 101;
$GR_STAT['PROTOCOL_MINOR_VERSION_INVALID'] = 102;
$GR_STAT['PROTOCOL_VERSION_FORMAT_INVALID'] = 103;
$GR_STAT['PROTOCOL_VERSION_MISSING']   = 104;

$GR_STAT['PASSWORD_WRONG']      = 201;
$GR_STAT['LOGIN_MISSING']       = 202;

$GR_STAT['UNKNOWN_COMMAND']		= 301;

$GR_STAT['NO_ADD_PERMISSION']	= 401;
$GR_STAT['NO_FILENAME']			= 402;
$GR_STAT['UPLOAD_PHOTO_FAIL']	= 403;

$GR_STAT['NO_CREATE_ALBUM_PERMISSION']	= 501;


/*
 * Check protocol version
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
				echo $response->listprops();
				exit;
			}/* else if ( $minor_ver > $GR_VER['MIN'] ) {
				$response->setProperty( "status", $GR_STAT['PROTOCOL_MINOR_VERSION_INVALID'] );
				$response->setProperty( "status_text", "Protocol minor version invalid." );
				echo $response->listprops();
				exit;
			}*/
			// else version compatible, proceed
		} else {
			$response->setProperty( "status", $GR_STAT['PROTOCOL_VERSION_FORMAT_INVALID'] );
			$response->setProperty( "status_text", "Protocol version format invalid." );
			echo $response->listprops();
			exit;
		}
	} else {
		// must specify protocol_version
		$response->setProperty( "status", $GR_STAT['PROTOCOL_VERSION_MISSING'] );
		$response->setProperty( "status_text", "Protocol version not found." );
		echo $response->listprops();
		exit;
	}
}

//---------------------------------------------------------
//-- login --

if (!strcmp($cmd, "login")) {
	$response = new Properties();
	check_proto_version( $response );
	
	if ($uname && $password) {
		$tmpUser = $gallery->userDB->getUserByUsername($uname);
		if ($tmpUser && $tmpUser->isCorrectPassword($password)) {
			// log user in
			$gallery->session->username = $uname;
			
			$response->setProperty( "server_version", $GR_VER['MAJ'].".".$GR_VER['MIN'] );
			$response->setProperty( "status", $GR_STAT['SUCCESS'] );
			$response->setProperty( "status_text", "Login successful." );
		} else {
			$response->setProperty( "status", $GR_STAT['PASSWORD_WRONG'] );
			$response->setProperty( "status_text", "Password incorrect." );
		}
	} else {
		$response->setProperty( "status", $GR_STAT['LOGIN_MISSING'] );
		$response->setProperty( "status_text", "Login parameters not found." );
	}
	
	echo $response->listprops();
	exit;
}

//---------------------------------------------------------
//-- fetch-albums --

if (!strcmp($cmd, "fetch-albums")) {
	$response = new Properties();
	check_proto_version( $response );
	
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
	$response->setProperty( "album_count", $album_index );
	
	// add status and repond
	$response->setProperty( "status", $GR_STAT['SUCCESS'] );
	$response->setProperty( "status_text", "Fetch albums successful." );
	echo $response->listprops();
	exit;
}


//---------------------------------------------------------
//-- add-item --

if (!strcmp($cmd, "add-item")) {
	$response = new Properties();
	check_proto_version( $response );
	
	// current album is set by the "set_albumName" form data and session.php
	
	// Hack check
	if (!$gallery->user->canAddToAlbum($gallery->album)) {
		$response->setProperty( "status", $GR_STAT['NO_ADD_PERMISSION'] );
		$response->setProperty( "status_text", "User cannot add to album." );
	} else if (!$userfile_name) {
		$response->setProperty( "status", $GR_STAT['NO_FILENAME'] );
		$response->setProperty( "status_text", "Filename not specified." );
	} else {
		$name = $userfile_name;
		$file = $userfile;
		$tag = ereg_replace(".*\.([^\.]*)$", "\\1", $name);
		$tag = strtolower($tag);
		
		if ($name) {
    		$error = processFile($userfile, $tag, $userfile_name, $caption);
		}
		
		$gallery->album->save();
		
		if ($temp_files) {
    		/* Clean up the temporary url file */
    		foreach ($temp_files as $tf => $junk) {
        		fs_unlink($tf);
    		}
		}
		
		if ($error) {
			$response->setProperty( "status", $GR_STAT['UPLOAD_PHOTO_FAIL'] );
			$response->setProperty( "status_text", "Upload failed: '$error'." );
		} else {
			$response->setProperty( "status", $GR_STAT['SUCCESS'] );
			$response->setProperty( "status_text", "Add photo successful." );
		}
		
		echo $response->listprops();
		exit;
	}
}


//---------------------------------------------------------
//-- album-properties --

if (!strcmp($cmd, "album-properties")) {
	$response = new Properties();
	check_proto_version( $response );
	
	// current album is set by the "set_albumName" form data and session.php
	
	$max_dimension = $gallery->album->fields["resize_size"];
	if ( $max_dimension == "off" ) {
		$max_dimension = 0;	
	}
	
	$response->setProperty( "auto_resize", $max_dimension );
	$response->setProperty( "status", $GR_STAT['SUCCESS'] );
	$response->setProperty( "status_text", "Album properties retrieved successfully." );
	
	echo $response->listprops();
	exit;
}

//---------------------------------------------------------
//-- new-album --

if (!strcmp($cmd, "new-album")) {
	$response = new Properties();
	check_proto_version( $response );
	
	// Hack check
	if ( $gallery->user->canCreateAlbums()
			|| $gallery->user->canCreateSubAlbum($gallery->album) ) {


		// add the album
		createNewAlbum( $newAlbumName, $newAlbumTitle, $newAlbumDesc, $response );
		
		// set status and message
		$response->setProperty( "status", $GR_STAT['SUCCESS'] );
		$response->setProperty( "status_text", "New album created successfully." );
		
	} else {
		$response->setProperty( "status", $GR_STAT['NO_CREATE_ALBUM_PERMISSION'] );
		$response->setProperty( "status_text", "A new album could not be created because the user does not have permission to do so." );
	}
	
	// return the response
	echo $response->listprops();
	exit;
}


//============================================================================

//------------------------------------------------
//-- if the command hasn't been handled yet, we don't recognize it
//--
$response = new Properties();
check_proto_version( $response );
$response->setProperty( "status", $GR_STAT['UNKNOWN_COMMAND'] );
$response->setProperty( "status_text", "Command '$cmd' unknown." );
echo $response->listprops();

exit;



//------------------------------------------------
//-- FUNCTIONS
//--
function appendNestedAlbums( &$myAlbum, &$album_index, &$response ) {
    global $gallery;
	
	$parent_index = $album_index;

	$numPhotos = $myAlbum->numPhotos(1);
    
    for ($i=1; $i <= $numPhotos; $i++) {
        $myName = $myAlbum->isAlbumName($i);
        if ($myName) {
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
    $albumName = $myAlbum->fields[name];
    $albumTitle = $myAlbum->fields[title];
	
	// write name, title and parent
	$response->setProperty( "album.name.$album_index", $albumName );
	$response->setProperty( "album.title.$album_index", $albumTitle );
	$response->setProperty( "album.parent.$album_index", $parent_index );
	
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
				    $temp_files[$file]++;
				}
		    }

            if ($setCaption) {
                $caption = $setCaption;
            } else {
                $caption = "";
            }

	        $err = $gallery->album->addPhoto($file, $tag, $mangledFilename, $caption);
	        if (!$err) {
	            /* resize the photo if needed */
	            if ($gallery->album->fields["resize_size"] > 0 && isImage($tag)) {
	                $index = $gallery->album->numPhotos(1);
	                $photo = $gallery->album->getPhoto($index);
	                list($w, $h) = $photo->image->getRawDimensions();
	                if ($w > $gallery->album->fields["resize_size"] ||
	                    $h > $gallery->album->fields["resize_size"]) {
	                    $gallery->album->resizePhoto($index, $gallery->album->fields["resize_size"]);
	                }
	            }
	        } else {
	        	$error = "$err";
	        }
	    } else {
	    	$error = "Skipping $name (can't handle '$tag' format)";
	    }
    }
    
    return $error;
}


//---------------------------------------------------------
// this is an edited version of code in do_command.php
//
function createNewAlbum( $newAlbumName, $newAlbumTitle, $newAlbumDesc, &$response ) {
	global $gallery;
	
    // get parent album name
	$parentName = $gallery->session->albumName;
	$albumDB = new AlbumDB(FALSE);
	
	// set new album name from param or default
	if ($newAlbumName) {
		$gallery->session->albumName = $newAlbumName;
	} else {
		$gallery->session->albumName = $albumDB->newAlbumName();
	}
	
	$gallery->album = new Album();
	$gallery->album->fields["name"] = $gallery->session->albumName;
	
	// set title and description
	if ($newAlbumTitle) {
		$gallery->album->fields["title"] = $newAlbumTitle;
	}
	if ($newAlbumDesc) {
		$gallery->album->fields["description"] = $newAlbumDesc;
	}
	
	$gallery->album->setOwner($gallery->user->getUid());
	$gallery->album->save();
	
	/* if this is a nested album, set nested parameters */
	if ($parentName) {
		$gallery->album->fields[parentAlbumName] = $parentName;
		$parentAlbum = $albumDB->getAlbumbyName($parentName);
		$parentAlbum->addNestedAlbum($gallery->session->albumName);
		$parentAlbum->save();
		// Set default values in nested album to match settings of parent.
		$gallery->album->fields["perms"]           = $parentAlbum->fields["perms"];
		$gallery->album->fields["bgcolor"]         = $parentAlbum->fields["bgcolor"];
		$gallery->album->fields["textcolor"]       = $parentAlbum->fields["textcolor"];
		$gallery->album->fields["linkcolor"]       = $parentAlbum->fields["linkcolor"];
		$gallery->album->fields["font"]            = $parentAlbum->fields["font"];
		$gallery->album->fields["border"]          = $parentAlbum->fields["border"];
		$gallery->album->fields["bordercolor"]     = $parentAlbum->fields["bordercolor"];
		$gallery->album->fields["returnto"]        = $parentAlbum->fields["returnto"];
		$gallery->album->fields["thumb_size"]      = $parentAlbum->fields["thumb_size"];
		$gallery->album->fields["resize_size"]     = $parentAlbum->fields["resize_size"];
		$gallery->album->fields["rows"]            = $parentAlbum->fields["rows"];
		$gallery->album->fields["cols"]            = $parentAlbum->fields["cols"];
		$gallery->album->fields["fit_to_window"]   = $parentAlbum->fields["fit_to_window"];
		$gallery->album->fields["use_fullOnly"]    = $parentAlbum->fields["use_fullOnly"];
		$gallery->album->fields["print_photos"]    = $parentAlbum->fields["print_photos"];
		$gallery->album->fields["use_exif"]        = $parentAlbum->fields["use_exif"];
		$gallery->album->fields["display_clicks"]  = $parentAlbum->fields["display_clicks"];
		$gallery->album->fields["public_comments"] = $parentAlbum->fields["public_comments"];

		$gallery->album->save();
	} else {
		/* move the album to the top if not a nested album*/
    	$numAlbums = $albumDB->numAlbums($gallery->user);
    	$albumDB->moveAlbum($gallery->user, $numAlbums, 1);
    	$albumDB->save();
	}
	
	return true;
}

?>