<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2003 Bharat Mediratta
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

// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
	print "Security violation\n";
	exit;
}

if (!isset($GALLERY_BASEDIR)) {
    $GALLERY_BASEDIR = '';
}

require($GALLERY_BASEDIR . "init.php");
require($GALLERY_BASEDIR . "classes/remote/GalleryRemoteProperties.php");

//---------------------------------------------------------
//-- check that we are not being called from the browser --
if (empty ($cmd)) {
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
 * Gallery remote protocol version 2.3
 */
$GR_VER['MAJ'] = 2;
$GR_VER['MIN'] = 3;


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

$GR_STAT['NO_CREATE_ALBUM_PERMISSION']	= 501;
$GR_STAT['CREATE_ALBUM_FAILED']			= 502;


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

$response = new Properties();
check_proto_version( $response );

// some debug output
//$response->setProperty( "debug_session_albumName", $gallery->session->albumName);
$response->setProperty( "debug_album", $gallery->album->fields["name"]);

// -- Handle request --

if (!strcmp($cmd, "login")) {
	//---------------------------------------------------------
	//-- login --

	if ($uname && $password) {
		//echo $gallery->user->getUsername()."\n";
		//echo $gallery->user->isLoggedIn()."\n";

		if ($gallery->user->isLoggedIn()) {
			// we're embedded and the user is authenticated

			$response->setProperty( "debug_user", $gallery->user->getUsername());
			$response->setProperty( "debug_user_type", get_class($gallery->user));

			$response->setProperty( "server_version", $GR_VER['MAJ'].".".$GR_VER['MIN'] );
			$response->setProperty( "status", $GR_STAT['SUCCESS'] );
			$response->setProperty( "status_text", "Login successful." );

			// return the response
			echo $response->listprops();
			exit;
		}

		// try to log in using URL parameters (probably not embedded)
		$tmpUser = $gallery->userDB->getUserByUsername($uname);

		if ($tmpUser) {
			$response->setProperty( "debug_user", $tmpUser->getUsername());
			$response->setProperty( "debug_user_type", get_class($tmpUser));
		}

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

} else if (!strcmp($cmd, "fetch-albums")) {
	//---------------------------------------------------------
	//-- fetch-albums --

	$albumDB = new AlbumDB(FALSE);

		//$list = array();
		foreach ($albumDB->albumList as $album) {
			echo $album->fields[name];
		}

		//return $list;

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

} else if (!strcmp($cmd, "fetch-albums-prune")) {
	//---------------------------------------------------------
	//-- fetch-albums-prune --

	$albumDB = new AlbumDB(FALSE);
	$album_count = 0;

	mark_and_sweep($albumDB);

	foreach ($albumDB->albumList as $album) {
		if ($myMark[$album->fields["name"]]) {
			add_album( $album, $album_count, $album->fields[parentAlbumName], $response );
		}
	}

    // add album count
	$response->setProperty( "album_count", $album_count );

	// add status and repond
	$response->setProperty( "status", $GR_STAT['SUCCESS'] );
	$response->setProperty( "status_text", "Fetch albums successful." );

} else if (!strcmp($cmd, "add-item")) {
	//---------------------------------------------------------
	//-- add-item --

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
	}

} else if (!strcmp($cmd, "album-properties")) {
	//---------------------------------------------------------
	//-- album-properties --

	// current album is set by the "set_albumName" form data and session.php

	$max_dimension = $gallery->album->fields["resize_size"];
	if ( $max_dimension == "off" ) {
		$max_dimension = 0;
	}

	$response->setProperty( "auto_resize", $max_dimension );
	$response->setProperty( "extra_fields", $gallery->album->getExtraFields() );

	$response->setProperty( "status", $GR_STAT['SUCCESS'] );
	$response->setProperty( "status_text", "Album properties retrieved successfully." );
} else if (!strcmp($cmd, "new-album")) {
	//---------------------------------------------------------
	//-- new-album --

	// Hack: detect this magic name that means the albumName should be null
	//if ($gallery->session->albumName == "hack_null_albumName") {
	//	$gallery->session->albumName = "";
	//}

	// Hack check
	if ( $gallery->user->canCreateAlbums()
			&& $gallery->user->canCreateSubAlbum($gallery->album) ) {
		// add the album
		if (createNewAlbum( $gallery->session->albumName,
				$newAlbumName, $newAlbumTitle, $newAlbumDesc )) {
			// set status and message
			$response->setProperty( "status", $GR_STAT['SUCCESS'] );
			$response->setProperty( "status_text", "New album created successfully." );
		} else {
			// set status and message
			$response->setProperty( "status", $GR_STAT['CREATE_ALBUM_FAILED'] );
			$response->setProperty( "status_text", "Create album failed." );
		}
	} else {
		$response->setProperty( "status", $GR_STAT['NO_CREATE_ALBUM_PERMISSION'] );
		$response->setProperty( "status_text", "A new album could not be created because the user does not have permission to do so." );
	}
} else {
	// if the command hasn't been handled yet, we don't recognize it
	$response->setProperty( "status", $GR_STAT['UNKNOWN_COMMAND'] );
	$response->setProperty( "status_text", "Command '$cmd' unknown." );
}

echo $response->listprops();


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

			// add the extra fields
			$myExtraFields = array();
			foreach ($gallery->album->getExtraFields() as $field) {
				global $HTTP_POST_VARS;
				//$fieldname = "extrafield_$field";
				//echo "Looking for extra field $fieldname\n";
				$value = $HTTP_POST_VARS[("extrafield_".$field)];
				//echo "Got extra field $field = $value\n";
				if ($value) {
					//echo "Setting field $field\n";
					$myExtraFields[$field] = $value;
				}
			}
			//echo "Extra fields ". implode("/", array_keys($myExtraFields)) ." -- ". implode("/", array_values($myExtraFields)) ."\n";

	        $err = $gallery->album->addPhoto($file, $tag, $mangledFilename, $caption, "", $myExtraFields, $gallery->user->getUid());
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

function mark_and_sweep(&$albumDB) {
	global $gallery, $myMark;

	foreach ($albumDB->albumList as $myAlbum) {
		//echo "mark_and_sweep: ".$myAlbum->fields["name"]."\n";
		if ($gallery->user->canAddToAlbum($myAlbum)) {
			sweep($albumDB, $myAlbum);
			//echo "mark_and_sweep: ".$myMark[$myAlbum->fields["name"]]."\n";
		}
	}
}

function sweep(&$albumDB, &$myAlbum) {
global $myMark;
	//echo "sweep: ".$myMark[$myAlbum->fields["name"]]."\n";
	if (! $myMark[$myAlbum->fields["name"]]) {
		//echo "sweep: ".$myAlbum->fields["name"]." is not marked: marking\n";
		$myMark[$myAlbum->fields["name"]] = TRUE;
		//echo "sweep: ".$myMark[$myAlbum->fields["name"]]."\n";

		$parentName = $myAlbum->fields["parentAlbumName"];
		if ($parentName) {
			//echo "sweep: got parent ".$parentName."\n";
			$parentAlbum = $albumDB->getAlbumByName($parentName, FALSE);

			sweep($albumDB, $parentAlbum);
		}
	}
}

?>
