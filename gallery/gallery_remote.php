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
		}
		else {
			$returnval = "Login Incorrect";
		}
	}
	else {
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
		$error = gTranslate('core', "User cannot add to album");
	}
	else if (!$userfile_name) {
		$error = gTranslate('core', "No file specified");
	}
	else {

		$name = $userfile_name;
		$file = $userfile;
		$tag = ereg_replace(".*\.([^\.]*)$", "\\1", $name);
		$tag = strtolower($tag);

		if ($name) {
			$error = process($userfile, $tag, $userfile_name, $setCaption);
		}

		if(empty($error)) {
			$gallery->album->save(array(i18n("Image added")));
		}

		if ($temp_files) {
			/* Clean up the temporary url file */
			foreach ($temp_files as $tf => $junk) {
				fs_unlink($tf);
			}
		}

	}

	if ($error) {
		echo ("ERROR: $error");
	}
	else {
		echo ("SUCCESS");
	}

}

//------------------------------------------------
//-- this process function is identical to that in save_photos.
//-- Ugh.

function process($file, $tag, $name, $setCaption="") {
	global $gallery;
	global $temp_files;

	$error = '';

	if (isAcceptableArchive($tag)) {
		processingMsg(sprintf(gTranslate('core', "Processing file '%s' as archive"), $name));
		$tool = canDecompressArchive($tag);
		if (!$tool) {
			$error = sprintf(gTranslate('core', "Skipping '%s' (%s support not enabled)"), $name, $tag);
			return $error;
		}

		$temp_filename	= tempnam($gallery->app->tmpDir, 'g1_tmp_');
		$temp_dirname	= $temp_filename . '.dir';

		if (fs_is_dir($temp_dirname)) {
			$error = gTranslate('core', "Error occured before extracting the archive. Temporary destination exists.");
			return $error;
		}

		if (! fs_mkdir($temp_dirname)) {
			$error = gTranslate('core', "Error occured before extracting the archive. Temporary destination could not be created.");
			return $error;
		}

		if(! extractArchive($file, $tag, $temp_dirname)) {
			$error = gTranslate('core', "Extracting archive failed.");
			return $error;
		}

		echo debugMessage(gTranslate('core', "Processing archive content."), __FILE__, __LINE__);

		$files_to_process	= array();
		$dir_handle		= fs_opendir($temp_dirname);
		$invalid_files 		= 0;

		while (false !== ($content_filename = readdir($dir_handle))) {
			if($content_filename == "." || $content_filename == '..') {
				continue;
			}

			if(! isXSSclean($content_filename)) {
				$invalid_files++;
				continue;
			}

			$content_file_ext	= getExtension($content_filename);
			$fullpath_content_file	= $temp_dirname .'/' . $content_filename;

			if (isAcceptableFormat($content_file_ext) ||
			    isAcceptableArchive($content_file_ext))
			{
				$files_to_process[] = array(
					'filename'	=> $fullpath_content_file,
					'ext'		=> $content_file_ext
				);
			}
			else {
				$invalid_files++;
				continue;
			}
		}

		closedir($dir_handle);

		/* Now process all valid files we found */
		echo debugMessage(
			gTranslate(
				'core',
				"Processing %d valid file from archive.",
				"Processing %d valid files from archive.",
				sizeof($files_to_process),
				gTranslate('core', "The archive contains no valid files!"),
				true),
			__FILE__,
			__LINE__
		);

		$loop = 0;
		foreach ($files_to_process as $current_file) {
			$current_file_name = basename($current_file['filename']);
			$current_file_ext  = basename($current_file['ext']);

			process($current_file['filename'],
				$current_file_ext,
				$current_file_name,
				$caption,
				$setCaption
			);
		}
		/* End of archive processing */
		rmdirRecursive($temp_dirname);
		fs_unlink($temp_filename);
	}
	else {
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
		if (isAcceptableFormat($tag) || isAcceptableArchive($tag)) {
			if ($setCaption) {
				$caption = $originalFilename;
			}
			else {
				$caption = '';
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

			$err = $gallery->album->addPhoto(
							$file,
							$tag,
							$mangledFilename,
							$caption,
							array(),
							$gallery->user->getUid()
			);

			if ($err) {
				$error = "$err";
			}
		}
		else {
			$error = "Skipping $name (can't handle '$tag' format)";
		}
	}

	return $error;
}

?>
