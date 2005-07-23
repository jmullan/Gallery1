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

//---------------------------------------------------------
//-- check that we are not being called from the browser --
if (empty ($cmd)) {
	echo 'This page is not meant to be accessed from your browser.  ';
	echo 'If you would like to use Gallery Remote, please refer to ';
	echo 'Gallery\'s website located at ';
	echo "<a href='$gallery->url'>$gallery->url</a>";
	exit;
}

//---------------------------------------------------------
//-- check version --
if (strcmp($protocal_version, "1")) {
	echo "Protocol out of Date. $protocal_version < 1.";
	exit;
}

//---------------------------------------------------------
//-- login --

if (!strcmp($cmd, "login")) {

	if ($uname && $password) {
		$tmpUser = $gallery->userDB->getUserByUsername($uname);
		if ($tmpUser && $tmpUser->isCorrectPassword($password)) {
			$gallery->session->username = $uname;
			$returnval = "SUCCESS";
		} else {
			$returnval = "Login Incorrect";
		}
	} else {
		$returnval = "Missing Parameters";
	}

	echo "$returnval";
}

//---------------------------------------------------------
//-- fetch-albums --

if (!strcmp($cmd, "fetch-albums")) {

	$albumDB = new AlbumDB(FALSE);
    $mynumalbums = $albumDB->numAlbums($gallery->user);

    // display all albums that the user can move album to
    for ($i=1; $i<=$mynumalbums; $i++) {
        $myAlbum=$albumDB->getAlbum($gallery->user, $i);
        $albumName = urlencode($myAlbum->fields[name]);
        $albumTitle = urlencode($myAlbum->fields[title]);
        if ($gallery->user->canWriteToAlbum($myAlbum)) {
			echo "$albumName\t$albumTitle\n";
        }
        appendNestedAlbums(0, $albumName, $albumString);
    }
	echo "SUCCESS";

}

function appendNestedAlbums($level, $albumName, $albumString) {
    global $gallery;
 
    $myAlbum = new Album();
    $myAlbum->load($albumName);
   
    $numPhotos = $myAlbum->numPhotos(1);

    for ($i=1; $i <= $numPhotos; $i++) {
        if ($myAlbum->isAlbum($i)) {
            $myName = $myAlbum->getAlbumName($i);
            $nestedAlbum = new Album();
            $nestedAlbum->load($myName);
            if ($gallery->user->canWriteToAlbum($nestedAlbum)) {
                $nextTitle = str_repeat("-- ", $level+1);
                $nextTitle .= $nestedAlbum->fields[title];
				$nextTitle = urlencode($nextTitle);
                $nextName = urlencode($nestedAlbum->fields[name]);
				echo "$nextName\t$nextTitle\n";
                appendNestedAlbums($level + 1, $myName, $albumString);
            }
        }
    }
}

//---------------------------------------------------------
//-- add-photo --

if (!strcmp($cmd, "add-item")) {

	// Hack check
	if (!$gallery->user->canAddToAlbum($gallery->album)) {
    	$error = _("User cannot add to album");
	}

	else if (!$userfile_name) {
    	$error = _("No file specified");
	}

	else {

		$name = $userfile_name;
		$file = $userfile;
		$tag = ereg_replace(".*\.([^\.]*)$", "\\1", $name);
		$tag = strtolower($tag);

		if ($name) {
    		process($userfile, $tag, $userfile_name, $setCaption);
		}

		$gallery->album->save(array(i18n("Image added")));

		if ($temp_files) {
    		/* Clean up the temporary url file */
    		foreach ($temp_files as $tf => $junk) {
        		fs_unlink($tf);
    		}
		}

	}

	if ($error) {
    	echo ("ERROR: $error");
	} else {
    	echo ("SUCCESS");
	}

}

//------------------------------------------------
//-- this process function is identical to that in save_photos.
//-- Ugh.

function process($file, $tag, $name, $setCaption="") {
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
                         " '" .
                         fs_import_filename($cmd_pic_path, 1) .
                         "' -d " .
                         fs_import_filename($gallery->app->tmpDir, 1));
                process($gallery->app->tmpDir . "/$pic", $tag, $pic, $setCaption);
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
            if ($setCaption) {
                $caption = $originalFilename;
            } else {
                $caption = "";
            }
   
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

            $err = $gallery->album->addPhoto($file, $tag, $mangledFilename, $caption, array(), $gallery->user->getUid());
            if ($err) {
                $error = "$err";
            }
        } else {
            $error = "Skipping $name (can't handle '$tag' format)";
        }
    }
}

?>
