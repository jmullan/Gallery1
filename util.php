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
 * @package Utils
 */

/**
 * First include some necessary files
 */
require_once(dirname(__FILE__) . '/nls.php');
require_once(dirname(__FILE__) . '/lib/url.php');
require_once(dirname(__FILE__) . '/lib/popup.php');
require_once(dirname(__FILE__) . '/classes/Mail/htmlMimeMail.php');
require_once(dirname(__FILE__) . '/classes/HTML/table.php');
require_once(dirname(__FILE__) . '/lib/valchecks.php');
require_once(dirname(__FILE__) . '/lib/messages.php');
require_once(dirname(__FILE__) . '/lib/content.php');

function getRequestVar($str) {
    if (!is_array($str)) {
        if (!isset($_REQUEST[$str])) {
            return null;
        }
        $ret = &$_REQUEST[$str];
        if (get_magic_quotes_gpc() && !is_array($ret)) {
            $ret = stripslashes($ret);
        }
    }
    else {
        foreach ($str as $reqvar) {
            $ret[] = getRequestVar($reqvar);
        }
    }
    return $ret;
}

function getFilesVar($str) {
    if (!is_array($str)) {
        if (!isset($_FILES[$str])) {
            return null;
        }
        $ret = &$_FILES[$str];
    }
    else {
        foreach ($str as $reqvar) {
            $ret[] = getFilesVar($reqvar);
        }
    }
    return $ret;
}

function getEnvVar($str) {
    if (!is_array($str)) {
        if (!isset($_ENV[$str])) {
            return null;
        }
        $ret = &$_ENV[$str];
    }
    else {
        foreach ($str as $reqvar) {
            $ret[] = getEnvVar($reqvar);
        }
    }
    return $ret;
}

function stripslashes_deep($value) {
    $value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
    return $value;
}


function getBlacklistFilename() {
    global $gallery;
    return sprintf("%s/blacklist.dat", $gallery->app->albumDir);
}

function loadBlacklist() {
    static $blacklist;

    if (!isset($blacklist)) {
        $tmp = getFile(getBlacklistFilename());
        $blacklist = unserialize($tmp);

        if (empty($blacklist)) {
            // Initialize the blacklist
            $blacklist = array();
            $blacklist['entries'] = array();
        }
    }

    return $blacklist;
}

function isBlacklistedComment(&$comment, $existingComment = true) {
	$blacklist = loadBlacklist();
	if ($existingComment) {
		foreach ($blacklist['entries'] as $key => $entry) {
			if (ereg($entry, $comment->getCommentText()) ||
			    ereg($entry, $comment->getName())) {
				return true;
			}
		}
	} else {
		foreach ($blacklist['entries'] as $entry) {
			if (ereg($entry, $comment['commenter_name']) ||
			    ereg($entry, $comment['comment_text'])) {
				return true;
			}
		}
	}
		
	return false;
}

function gallery_syslog($message) {
	global $gallery;
	if (isset($gallery->app->useSyslog) && $gallery->app->useSyslog == "yes") {
		define_syslog_variables();
		openlog("gallery", LOG_NDELAY | LOG_PID, LOG_USER);
		syslog(LOG_NOTICE, "(" . $gallery->app->photoAlbumURL . " [" . $gallery->version . "]) " . $message);
		closelog();
	}
}

function exec_internal($cmd) {
	global $gallery;

	$debugfile = '';
	$status = '';
	$results = array();
	
	if (isDebugging()) {
	    debugMessage(sprintf(_("Executing: %s"), $cmd), __FILE__, __LINE__);
	    $debugfile = tempnam($gallery->app->tmpDir, "dbg");
	}

	fs_exec($cmd, $results, $status, $debugfile);

	if (isDebugging()) {
		print "\n<br>". _("Results:") ."<pre>";
		if ($results) {
			print join("\n", $results);
		} else {
			print "<b>" ._("none") ."</b>";
		}
		print "</pre>";

		if (file_exists($debugfile)) {
			print "\n<br> ". _("Debug messages:") ." <pre>";
			if ($fd = fs_fopen($debugfile, "r")) {
				while (!feof($fd)) {
					$buf = fgets($fd, 4096);
					print $buf;
				}
				fclose($fd);
			}
			unlink($debugfile);
			print "</pre>";
		}
		print "\n<br> ". sprintf(_("Status: %s (expected %s)"),
				$status, $gallery->app->expectedExecStatus);
	}

	return array($results, $status);
}

function exec_wrapper($cmd) {
    global $gallery;

    list($results, $status) = exec_internal($cmd);

    if ($status == $gallery->app->expectedExecStatus) {
	return true;
    } else {
	if ($results) {
	    echo gallery_error(join("<br>", $results));
	}
        return false;
    }
}

function getDimensions($file) {
    global $gallery;				

    debugMessage(sprintf(_("Getting Dimension of file: %s"), $file), __FILE__, __LINE__, 2);

    if (! fs_file_exists($file)) {
        debugMessage(_("The file does not exist ?!"), __FILE__, __LINE__);
        return array(0, 0);
    }
    
    list($width, $height) = getimagesize($file);
    
    if ($width > 1 && $height > 1) {
        debugMessage(sprintf(_("Dimensions: x: %d y: %d"), $width, $height), __FILE__, __LINE__, 3);
		  
        return array($width, $height);
    }

    debugMessage(sprintf(_("PHP's %s function is unable to determine dimensions."), "getimagesize()"), __FILE__, __LINE__);
		
    /* Just in case php can't determine dimensions. */
    switch($gallery->app->graphics) {
        case 'NetPBM':
            list($lines, $status) = exec_internal(toPnmCmd($file) ." | ". 
                NetPBM('pnmfile', '--allimages'));
            break;
        case "ImageMagick":
            /* This fails under windows, IM isn't returning parsable status output. */
              list($lines, $status) = exec_internal(ImCmd('identify', '', fs_import_filename($file)));
        break;
        
	default:
	    echo debugMessage(_("You have no graphics package configured for use!"));
	    return array(0, 0);
        break;
    }

    if ($status == $gallery->app->expectedExecStatus) {
        foreach ($lines as $line) {
            switch($gallery->app->graphics) {
                case 'NetPBM':
                    if (ereg("([0-9]+) by ([0-9]+)", $line, $regs)) {
                        return array($regs[1], $regs[2]);
                    }
                break;
                
                case 'ImageMagick':
                    if (ereg("([0-9]+)x([0-9]+)", $line, $regs)) {
                        return array($regs[1], $regs[2]);
                    }
                break;
            }
        }
    }

    debugMessage(_("Unable to determine image dimensions!"), __FILE__, __LINE__);
    
    return array(0, 0);
}

function acceptableFormat($tag) {
	return (isImage($tag) || isMovie($tag));
}

function acceptableFormatRegexp() {
	return "(?:" . join("|", acceptableFormatList()) . ")";
}

function acceptableMovieList() {
    return array('asx', 'asf', 'avi', 'mpg', 'mpeg', 'mp2', 'wmv', 'mov', 'qt', 'swf', 'mp4', 'rm', 'ram');
}

function acceptableImageList() {
    return array('jpg', 'jpeg', 'gif', 'png');
}

function acceptableFormatList() {
    return array_merge(acceptableImageList(), acceptableMovieList());
}

function isImage($tag) {
    $tag = strtolower($tag);
    return in_array($tag, acceptableImageList());
}

function isMovie($tag) {
    $tag = strtolower($tag);
    return in_array($tag, acceptableMovieList());
}

function getFile($fname, $legacy=false) {
	$tmp = "";

	if (!fs_file_exists($fname) || broken_link($fname)) {
		return $tmp;
	}

	if (function_exists("file_get_contents")) {
		return fs_file_get_contents($fname);
	}

	if ($legacy) {
	    $modes = "rt";
	} else {
	    $modes = "rb";
	}
	
	if ($fd = fs_fopen($fname, $modes)) {
		while (!feof($fd)) {
			$tmp .= fread($fd, 65536);
		}
		fclose($fd);
	}
	return $tmp;
}

function my_flush() {
    print str_repeat(" ", 4096);	// force a flush
}

function correctPseudoUsers(&$array, $ownerUid) {
	global $gallery;

	/*
	 * If EVERYBODY is in the list, reduce it to just that entry.
	 */
	$everybody = $gallery->userDB->getEverybody();
	if (!empty($array[$everybody->getUid()])) {
	        $array = array($everybody->getUid() => $everybody->getUsername());
		return;
	}

	/*
	 * If LOGGEDIN is in the list, reduce it to just that entry.
	 */
	$loggedIn = $gallery->userDB->getLoggedIn();
	if (!empty($array[$loggedIn->getUid()])) {
		$array = array($loggedIn->getUid() => $loggedIn->getUsername());
		return;
	}

	/*
	 * If the list has more than one entry, remove the NOBODY user.
	 */
	$nobody = $gallery->userDB->getNobody();
	if (count($array) > 1) {
		unset($array[$nobody->getUid()]);
	}

	/*
	 * If the list has no entries, insert the NOBODY user *unless* the
	 * owner is the EVERYBODY user, in which case specify EVERYBODY.
	 */
	if (count($array) == 0) {
		if (!strcmp($ownerUid, $everybody->getUid())) {
		        $array = array($everybody->getUid() => $everybody->getUsername());
		} else {
			$array[$nobody->getUid()] = $nobody->getUsername();
		}
	}
}

function gallerySanityCheck() {
	global $gallery;
	global $GALLERY_OK;
       	if (!empty($gallery->backup_mode)) {
	       	return NULL;
       	}

	setGalleryPaths();

	if (!fs_file_exists(GALLERY_CONFDIR . "/config.php") ||
                broken_link(GALLERY_CONFDIR . "config.php") ||
                !$gallery->app) {
		$GALLERY_OK = false;
		return "unconfigured.php";
	}

	if ($gallery->app->config_version != $gallery->config_version) {
		$GALLERY_OK = false;
		return "reconfigure.php";
	}
	$GALLERY_OK = true;
	return NULL;
}

function preprocessImage($dir, $file) {

	if (!fs_file_exists("$dir/$file") || broken_link("$dir/$file")) {
		return 0;
	}

	/*
	 * Check to see if it starts with a mime-type header, eg:
	 *
	 * 	Content-Type: image/pjpeg\n\n
	 *
	 * If so, remove everything up to and including the last 
	 * newline
	 */

	if ($fd = fs_fopen("$dir/$file", "rb")) {
		// Read the first line
		$line = fgets($fd, 4096);

		// Does it look like a content-type string?
		if (strstr($line, "Content-Type:")) {
			// Skip till we find a line by itself.
			do {
				$line = fgets($fd, 4096);
			} while (!feof($fd) && ord($line) != 13 && ord($line) != 10);

			// Dump the rest to a file
			$tempfile = tempnam($dir, $file);
			if ($newfd = fs_fopen($tempfile, "wb", 0755)) {
				while (!feof($fd)) {
					/*
					 * Copy the rest of the file.  Specify a length
					 * to fwrite so that we ignore magic_quotes.
					 */
					fwrite($newfd, fread($fd, 64*1024), 64*1024+1);
				}
				fclose($newfd);
				$success = fs_rename($tempfile, "$dir/$file");
				if (!$success) {
					echo gallery_error("Couldn't move $tempfile -> $dir/$file");
					fs_unlink($tempfile);
				}
			} else {
				echo gallery_error(sprintf(_("Can't write to %s."),
							$tempfile));
			}
			chmod("$dir/$file", 0644);
		}
		fclose($fd);
	} else {
		echo gallery_error(sprintf(_("Can't read %s."), "$dir/$file"));
	}

	return 1;
}

/**
 * This function checks wether we are debugging with a given level.
 * If no level is given, it just returns wether we are debugging or not.
 * Debug is indicated by a debuglevel greater then 0
 * @param  integer   $level
 * @return boolean
 */
function isDebugging($level = NULL) {
	global $gallery;

	if (isset($gallery->app->debuglevel)) {
		if($gallery->app->debuglevel > 0) {
			if(isset($level) && $gallery->app->debuglevel < $level) {
				return false;
			}
			return true;
		}
		else {
			return false;
		}
	}
	else {
		return false;
	}
}

function getNextPhoto($idx, $album=NULL) {
	global $gallery;

	if (!$album) {
		$album = $gallery->album;
	}

	$numPhotos = $album->numPhotos(1);
	$idx++;

	if ($idx > $numPhotos) {
		return $idx;
	}

	// If it's not an album or hidden, or the user is an admin, show it to them.
	if ((!$album->isAlbum($idx) && !$album->isHidden($idx)) || $gallery->user->isAdmin()) {
		return $idx;
	}

	// Check rights to album
	if ($album->isAlbum($idx)) {
		$myAlbum =& $album->getNestedAlbum($idx, false);

		// Owners can always see their own content
		if ($gallery->user->isOwnerOfAlbum($myAlbum)) {
			return $idx;
		}

		// No rights?  getNextPhoto
		if (!$gallery->user->canReadAlbum($myAlbum)) {
			return getNextPhoto($idx, $album);
		}
	}

	// Visible Album or Hidden Photo/Album
	if (!$album->isHidden($idx)) {
		// Visible album - allow all
		return $idx;
	} else {
		if ($gallery->user->isOwnerOfAlbum($album)) {
			// Does the user own the current album?
			// Owners can always see at least the first level of sub-content
			return $idx;
		} elseif ($album->getItemOwnerModify() && $album->isItemOwner($gallery->user->getUid(), $idx)) {
			// Hidden photo - allow the owner to see it (hidden sub-albums are covered 
			// in the album rights block by isOwnerOfAlbum)
			return $idx;
		} else {
			// Hidden photo or album - disallow all others
			return getNextPhoto($idx, $album);
		}
	}
}

/**
 * This function checks which tool
 * can we use for getting exif data from a photo.
 * returns false when no way works.
 * @return mixed
 * @author Jens Tkotz <jens@peino.de
 */
function getExifDisplayTool() {
    global $gallery;

    if(isset($gallery->app->exiftags)) {
        return 'exiftags';
    } elseif (isset($gallery->app->use_exif)) {
        return 'jhead';
    } else {
        return false;
    }
}

/**
 * This function does not really looks if EXIF Data is there or not.
 * It just looks at the extension.
 * @package string  $file
 * @return  boolean
 * @author  Jens Tkotz <jens@peino.de>
 */
function hasExif($file) {
    if(eregi('jpe?g$', $file)) {
        return true;
    } else {
        return false;
    }
}

/**
 * If an exiftool is installed then gallery tries to pull out EXIF Data.
 * Only fields with data are returned.
 */
function getExif($file) {
    global $gallery;

    $return = array();
    $myExif = array();
    $unwantedFields = array();

    switch(getExifDisplayTool()) {
        case 'exiftags':
            if (empty($gallery->app->exiftags)) {
                break;
            }
            $path = $gallery->app->exiftags;
            list($return, $status) = @exec_internal(fs_import_filename($path, 1) .' -au '.
                fs_import_filename($file, 1));
        break;

        case 'jhead':
            if (empty($gallery->app->use_exif)) {
                break;
            }
            $path = $gallery->app->use_exif;
            list($return, $status) = @exec_internal(fs_import_filename($path, 1) .' '. //. ' -v ' .
            fs_import_filename($file, 1));
    
            $unwantedFields = array('File name');
        break;

        default:
            return array(false,'');
        break;
    }

    if ($status == 0) {
        foreach ($return as $value) {
            $value = trim($value);
            if (!empty($value)) {
                $explodeReturn = explode(':', $value, 2);
                $exifDesc = trim(htmlentities($explodeReturn[0]));
                $exifData = trim(htmlentities($explodeReturn[1]));
                if(!empty($exifData) && !in_array($exifDesc, $unwantedFields)) {
                    if (isset($myExif[$exifDesc])) {
                        $myExif[$exifDesc] .= "<br>";
                    } else {
                        $myExif[$exifDesc] = '';
                    }

                    $myExif[$exifDesc] .= trim($exifData);
                }
            }
        }
    }

    return array($status, $myExif);
}

/**
 * This function tries to get the ItemCaptureDate from Exif Data.
 * If exif is not supported, or no date was gotten, then the file creation date is returned.
 * Note: i used switch/case because this is easier to extend later.
 */
function getItemCaptureDate($file) {
	$success = false;
	$exifSupported = getExifDisplayTool();

	if ($exifSupported) {
		$return = getExif($file);
		$exifData = $return[1];
		switch($exifSupported) {
                	case 'exiftags':
				if (isset($exifData['Image Created'])) {
					$tempDate = split(" ", $exifData['Image Created'], 2);
				}
                        	break;
	                case 'jhead':
				if (isset($exifData['Date/Time'])) {
					$tempDate = split(" ", $exifData['Date/Time'], 2);
				}
        	                break;
        	}
		if (isset($tempDate)) {
			$tempDay = strtr($tempDate[0], ':', '-');
			$tempTime = $tempDate[1];

			$itemCaptureTimeStamp = strtotime("$tempDay $tempTime");

			if ($itemCaptureTimeStamp != 0) {
				$success = true;
			}
		}
	}

	// we were not able to get the capture date from exif... use file creation time
	if (!$success) {
		$itemCaptureTimeStamp = filemtime($file);
	}

	if (!isDebugging()) {
		sprintf (_("Item Capture Date : %s"), strftime('%Y', $itemCaptureTimeStamp));
	}

	return $itemCaptureTimeStamp;
}

function doCommand($command, $args = array(), $returnTarget = '', $returnArgs = array()) {

	if ($returnTarget) {
		$args["return"] = urlencode(makeGalleryHeaderUrl($returnTarget, $returnArgs));
	}
	$args["cmd"] = $command;
	return makeGalleryUrl("do_command.php", $args);
}

function breakString($buf, $desired_len=40, $space_char=' ', $overflow=5) {
	$result = '';
	$col = 0;
	for ($i = 0; $i < strlen($buf); $i++, $col++) {
		$result .= $buf{$i};
		if (($col > $desired_len && $buf{$i} == $space_char) ||
		    ($col > $desired_len + $overflow)) {
			$col = 0;
			$result .= '<br>';
		}
	}
	return $result;
}

function padded_range_array($start, $end) {
	$arr = array();
	for ($i = $start; $i <= $end; $i++) {
		$val = sprintf("%02d", $i);
		$arr[$val] = $i;
	}
	return $arr;
}

function safe_serialize($obj, $file) {
	global $gallery;

	if (!strcmp($gallery->app->use_flock, "yes")) {
		/* Acquire an advisory lock */
		$lockfd = fs_fopen("$file.lock", "a+");
		if (!$lockfd) {
			echo gallery_error(sprintf(_("Could not open lock file (%s) for writing!"),
						"$file.lock"));
			return 0;
		}
		if (!flock($lockfd, LOCK_EX)) {
			echo gallery_error(sprintf(_("Could not acquire lock (%s)!"),
						"$file.lock"));
			return 0;
		}
	}

	/*
	 * Don't use tempnam because it may create a file on a different
	 * partition which would cause rename() to fail.  Instead, create our own 
	 * temporary file.
	 */
	$i = 0;
	do {
		$tmpfile = "$file.$i";
		$i++;
	} while (fs_file_exists($tmpfile));

	if ($fd = fs_fopen($tmpfile, "wb")) {
	        $buf = serialize($obj);
		$bufsize = strlen($buf);
		$count = fwrite($fd, $buf);
		fclose($fd);

		if ($count != $bufsize || fs_filesize($tmpfile) != $bufsize) {
			/* Something went wrong! */
			$success = 0;
		} else {
			/* 
			 * Make the current copy the backup, and then 
			 * write the new current copy.  There's a
			 * potential race condition here if the
			 * advisory lock (above) fails; two processes
			 * may try to do the initial rename() at the
			 * same time.  In that case the initial rename
			 * will fail, but we'll ignore that.  The
			 * second rename() will always go through (and
			 * the second process's changes will probably
			 * overwrite the first process's changes).
			 */
			if (fs_file_exists($file)) {
				fs_rename($file, "$file.bak");
			}
			fs_rename($tmpfile, $file);
			$success = 1;
		}
	} else {
		$success = 0;
	}

	if (!strcmp($gallery->app->use_flock, "yes")) {
		flock($lockfd, LOCK_UN);
	}
	return $success;
}

/**
 * This function left in place to support patches that use it, but please use
 * lastCommentDate functions in classes Album and AlbumItem.
 */
function mostRecentComment($album, $i) {
        $id = $album->getPhotoId($i); 
        $index = $album->getPhotoIndex($id); 
        $recentcomment = $album->getComment($index, $album->numComments($i));
        return $recentcomment->getDatePosted();
}

function ordinal($num = 1) {
	$ords = array("th","st","nd","rd");
	$val = $num;
	if ((($num%=100)>9 && $num<20) || ($num%=10)>3) $num=0;
	return "$val" . $ords[$num];
}

/**
 * Extracts the extension of a given filename and returns it in lower chars.
 * @param  string   $filename
 * @return string   $ext
 * @author Jens Tkotz <jens@peino.de>
 */ 
function getExtension($filename) {
	$ext = ereg_replace(".*\.([^\.]*)$", "\\1", $filename);
	$ext = strtolower($ext);
	
	echo debugMessage(sprintf(_("extension of file %s is %s"), basename($filename), $ext), __FILE__, __LINE__, 3);
	return $ext;
}

function acceptableArchiveList() {
	return array('zip', 'rar', 'cab', 'arj', 'lzh', 'tar', 'gz', 'bz2', 'ace', 'uue', 'jar', 'z');
}

function AcceptableArchive($ext) {
	if (in_array($ext, acceptableArchiveList())) {
		return true;
	} else {
		return false;
	}
}

/** 
 * This function checks wether an archive can be decompressed via Gallery
 * It just uses the filename extension.
 * If the extension is handable the de/compressing tool is returned
 * @param  string   $ext
 * @return mixed
 * @author Jens Tkotz <jens@peino.de>
 */
function canDecompressArchive($ext) {
	global $gallery;

	$ext = strtolower($ext);
	switch ($ext) {
		case 'zip':
			if ($gallery->app->feature["zip"] == 1) {
				return 'zip';
			}

		case 'rar':
		case 'cab':
		case 'arj':
		case 'lzh':
		case 'tar':
		case 'gz' :
		case 'bz2':
		case 'ace':
		case 'uue':
		case 'jar':
		case 'z':
			if (!empty($gallery->app->rar)) {
				return 'rar';
			}
			
			/* No suitable tool found */
			return false;
		break;

		/* Extension not supported */
		default:
			return false;
		break;
	}
}
/** 
 * This function checks wether an archive can be created via Gallery
 * It just uses the filename extension.
 * If the extension is handable the de/compressing tool is returned
 * @param   string $ext
 * @return  mixed
 * @author  Jens Tkotz <jens@peino.de>
 */
function canCreateArchive($ext = 'zip') {
	global $gallery;

	$ext = strtolower($ext);
	if ($ext == 'zip' && !empty($gallery->app->zip)) {
		return 'zip';
	}
	elseif ($ext == 'rar' && !empty($gallery->app->rar)) {
		return 'rar';
	}
	else {		
	    /* No suitable tool found */
	    return false;
	}

/*
 maybe later: 'tar', 'gz', 'bz2', 'ace'
*/
}

function getArchiveFileNames($archive, $ext) {
	global $gallery;

	$cmd = '';
	$files = array();
	
	switch (canDecompressArchive($ext)) {
		case 'zip':
			$cmd = fs_import_filename($gallery->app->zipinfo, 1) ." -1 ". fs_import_filename($archive, 1);
		break;

		case 'rar':
			$cmd = fs_import_filename($gallery->app->rar, 1) ." lb ". fs_import_filename($archive, 1);
		break;
	}
	
	list($files, $status) = exec_internal($cmd);

	if (!empty($files)) {
		sort($files);
	}

	return $files;
}

/* extract a file into Gallery temp dir */
function extractFileFromArchive($archive, $ext, $file) {
	global $gallery;

	$cmd_pic_path = str_replace("[", "\[", $file);
	$cmd_pic_path = str_replace("]", "\]", $cmd_pic_path);
	
	$tool = canDecompressArchive($ext);
	switch($tool) {
		case 'zip':
			$cmd = fs_import_filename($gallery->app->unzip, 1) . " -j -o " .
				fs_import_filename($archive, 1) . ' ' . fs_import_filename($cmd_pic_path) .
				' -d ' . fs_import_filename($gallery->app->tmpDir, 1);
		break;
		
		case 'rar':
			$cmd = fs_import_filename($gallery->app->rar, 1) ." x ".
				fs_import_filename($archive, 1) .' -x '. fs_import_filename($cmd_pic_path) .' '.
				fs_import_filename($gallery->app->tmpDir, 1);
		break;
	}
	exec_wrapper($cmd);
}

function createZip($folderName = '', $zipName = '', $deleteSource = true) {
    global $gallery;
    
    if ($folderName == '') {
	   return false;
    }

    $tool = canCreateArchive('zip');
    
    if (! $tool) {
        debugMessage(_("No Support for creating Zips"), __FILE__, __LINE__, 2);
        return false;
    } else {
        debugMessage(sprintf(_("Creating Zipfile with %s"), $tool), __FILE__, __LINE__, 2);
    }
           
    $tmpDir = $gallery->app->tmpDir .'/'. uniqid(rand());
    
    if ($zipName == '') {
	   $fullZipName = 'gallery_zip.zip';
    }
    else {
        $fullZipName = "$tmpDir/$zipName.zip";
    }

    if(! fs_mkdir($tmpDir)) {
        echo gallery_error(
          sprintf(_("Your tempfolder is not writeable! Please check permissions of this dir: %s"),
          $gallery->app->tmpDir));
        return false;
    }
    $currentDir = getcwd();
    chdir($folderName);
    
    switch($tool) {
		case 'zip':
			$cmd = fs_import_filename($gallery->app->zip) ." -r $fullZipName *";
		break;
		
		case 'rar':
			$cmd = '';
		break;
	}
    
    if (! exec_wrapper($cmd)) {
	   echo gallery_error("Zipping failed");
	   chdir($currentDir);
	   return false;
    }
    else {
	   chdir($currentDir);
	   if($deleteSource) {
        rmdirRecursive($folderName);
	   }
	
	return $fullZipName;
    }
}

function processNewImage($file, $ext, $name, $caption, $setCaption = '', $extra_fields=array(), $wmName="", $wmAlign=0, $wmAlignX=0, $wmAlignY=0, $wmSelect=0) {
    global $gallery;
    global $temp_files;

    /* Begin of code for the case the uploaded file is an archive */
    if (acceptableArchive($ext)) {
        $tool = canDecompressArchive($ext);
        if (empty($tool)) {
            processingMsg(sprintf(_("Skipping %s (%s support not enabled)"), $name, $ext));
            return;
        }

        /*
         * Figure out what files inside the archive we can handle.
         * Put all Filenames into $files.
        */
        $files = getArchiveFileNames($file, $ext);

        /* Get meta data */

        $image_info = array();
        foreach ($files as $pic_path) {
            $pic = basename($pic_path);
            $tag = ereg_replace(".*\.([^\.]*)$", "\\1", $pic);
            $tag = strtolower($tag);
            if (!strcmp($tag, "csv")) {
                extractFileFromArchive($file, $ext, $pic_path);
                $image_info = array_merge($image_info, parse_csv($gallery->app->tmpDir . "/$pic",";"));
            }
        }

        debugMessage(printMetaData($image_info), __FILE__, __LINE__);

        /* Now process all valid files we found */
        foreach ($files as $pic_path) {
            $pic = basename($pic_path);
            $tag = getExtension($pic);
            if (acceptableFormat($tag) || acceptableArchive($tag)) {
                extractFileFromArchive($file, $ext, $pic_path);

                /* Now process the metadates. */
                $extra_fields = array();

                /* Find in meta data array */
                $firstRow = 1;
                $fileNameKey = "File Name";

                /* $captionMetaFields will store the names (in order of priority to set caption to) */
                $captionMetaFields = array("Caption", "Title", "Description", "Persons");
                foreach ( $image_info as $info ) {
                    if ($firstRow) {
                        /* Find the name of the file name field */
                        foreach (array_keys($info) as $currKey) {
                            if (eregi("^\"?file\ ?name\"?$", $currKey)) {
                                $fileNameKey = $currKey;
                            }
                        }
                        $firstRow = 0;
                    }

                    if ($info[$fileNameKey] == $pic) {
                        /* Loop through fields */
                        foreach ($captionMetaFields as $field) {
                            /* If caption isn't populated and current field is */
                            if (!strlen($caption) && strlen($info[$field])) {
                                $caption = $info[$field];
                            }
                        }

                        $extra_fields = $info;
                    }
                }
                /* Don't use the second argument for $cmd_pic_path, because it is already quoted. */

                processNewImage($gallery->app->tmpDir . "/$pic", $tag, $pic, $caption, $setCaption, $extra_fields, $wmName, $wmAlign, $wmAlignX, $wmAlignY, $wmSelect);
                fs_unlink($gallery->app->tmpDir . "/$pic");
            }
        }
    } else {
        /* Its a single file
         * remove %20 and the like from name
         */
        $name = urldecode($name);

        /* parse out original filename without extension */
        $originalFilename = eregi_replace(".$ext$", "", $name);

        /* replace multiple non-word characters with a single "_" */
        $mangledFilename = ereg_replace("[^[:alnum:]]", "_", $originalFilename);

        /* Get rid of extra underscores */
        $mangledFilename = ereg_replace("_+", "_", $mangledFilename);
        $mangledFilename = ereg_replace("(^_|_$)", "", $mangledFilename);
        if (empty($mangledFilename)) {
            $mangledFilename = $gallery->album->newPhotoName();
        }

        /*
         * need to prevent users from using original filenames that are purely numeric.
         * Purely numeric filenames mess up the rewriterules that we use for mod_rewrite
         * specifically:
         * RewriteRule ^([^\.\?/]+)/([0-9]+)$	/~jpk/gallery/view_photo.php?set_albumName=$1&index=$2	[QSA]
        */

        if (ereg("^([0-9]+)$", $mangledFilename)) {
            $mangledFilename .= "_G";
        }

        set_time_limit($gallery->app->timeLimit);
        if (acceptableFormat($ext)) {
            /*
             * Move the uploaded image to our temporary directory
             * using move_uploaded_file so that we work around
             * issues with the open_basedir restriction.
            */
            if (function_exists('move_uploaded_file')) {
                $newFile = tempnam($gallery->app->tmpDir, "gallery");
                if (move_uploaded_file($file, $newFile)) {
                    $file = $newFile;
                }

                /* Make sure we remove this file when we're done */
                $temp_files[$newFile] = 1;
            }

            /* What should the caption be, if no caption was given by user ?
             * See captionOptions.inc.php for options
            */

            if (isset($gallery->app->dateTimeString)) {
                $dateTimeFormat = $gallery->app->dateTimeString;
            } else {
                $dateTimeFormat = "%D %T";
            }

            if (empty($caption)) {
                switch ($setCaption) {
                    case 0:
			$caption = '';
		    break;
                    case 1:
                    default:
                        /* Use filename */
                        $caption = strtr($originalFilename, '_', ' ');
                    break;
                    case 2:
                        /* Use file cration date */
                        $caption = strftime($dateTimeFormat, filectime($file));
                    break;
                    case 3:
                        /* Use capture date */
                        $caption = strftime($dateTimeFormat, getItemCaptureDate($file));
                    break;
                }
            }

            echo "\n<p><b>******". sprintf(_("Adding %s"), $name) ."*****<b></p>";
            
            /* After all the preprocessing, NOW ADD THE element
             * function addPhoto($file, $tag, $originalFilename, $caption, $pathToThumb="", $extraFields=array(), $owner="", $votes=NULL,
             *                   $wmName="", $wmAlign=0, $wmAlignX=0, $wmAlignY=0, $wmSelect=0)
            */
            $err = $gallery->album->addPhoto( 
                $file,
                $ext,
                $mangledFilename,
                $caption,
                '',
                $extra_fields,
                $gallery->user->uid,
                NULL,
                $wmName, $wmAlign, $wmAlignX, $wmAlignY, $wmSelect
            );
            
            if ($err) {
                processingMsg(gallery_error($err));
                processingMsg("<b>". sprintf(_("Need help?  Look in the  %s%s FAQ%s"),
                '<a href="http://gallery.sourceforge.net/faq.php" target=_new>', Gallery(), '</a>') .
                "</b>");
            }
        } else {
            processingMsg(sprintf(_("Skipping %s (can't handle %s format)"), $name, $ext));
        }
    }
}

function escapeEregChars($string) {
	return ereg_replace('(\.|\\\\|\+|\*|\?|\[|\]|\^|\$|\(|\)|\{|\}|\=|\!|<|>|\||\:)', '\\\\1', $string);
}

function findInPath($program) {
	$path = explode(':', getenv('PATH'));
	
	foreach ($path as $dir) {
		if (fs_file_exists("$dir/$program")) {
			return "$dir/$program";
		}
	}
	
	return false;
}

/**
 * Return the Version number of ImageMagick, identified by "convert -version"
 * @return $version	string	Versionnumber as string
*/
function getImVersion() {
    global $gallery;
    $version = array();

    exec($gallery->app->ImPath .'/convert -version', $results);

    $pieces = explode(' ', $results[0]);
    $version = $pieces[2];

    return $version;
}

define("OS_WINDOWS", "win");
define("OS_LINUX", "linux");
define("OS_SUNOS", "SunOS");
define("OS_OTHER", "other");

function getOS () {
	if (substr(PHP_OS, 0, 3) == 'WIN') {
		return OS_WINDOWS;
	} elseif ( stristr(PHP_OS, "linux")) {
		return OS_LINUX;
	} elseif ( stristr(PHP_OS, "SunOS")) {
		return OS_SUNOS;
	} else {
		return OS_OTHER;
	}
}

function generate_password($len = 10) {
	$result = '';
	$alpha  = 'abcdefghijklmnopqrstuvwxyz' .
			  '0123456789' .
			  'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

	$size = strlen($alpha) - 1;
	$used = array();

	while ($len--) {
		$random  = mt_rand(0, $size);
		$char    = $alpha[$random];

		// No duplicate characters.
		if (in_array($char, $used, true)) {
			$len++;
			continue;
		}
		$used[]  = $char;
		$result .= $char;
	}
	return $result;
}

function pretty_password($pass, $print, $pre = '    ') {
	$idx = -1;
	$len = strlen($pass);

	if ($print === true) {
		$result = "Your password is:  $pass\n\n";
	} else {
		$result = '';
	}

	while (++$idx < $len) {
		if (ereg('[[:upper:]]', $pass[$idx])) {
			$result .= $pre . $pass[$idx] .
				      ' = Uppercase letter ' . $pass[$idx] . "\n";
		} elseif (ereg('[[:lower:]]', $pass[$idx])) {
			$result .= $pre . $pass[$idx] .
				      ' = Lowercase letter ' . $pass[$idx] . "\n";
		} elseif (ereg('[[:digit:]]', $pass[$idx])) {
			$result .= $pre . $pass[$idx] .
				      ' = Numerical number ' . $pass[$idx] . "\n";
		} else {
			$result .= $pre . $pass[$idx] .
				      ' = ASCII Character  ' . $pass[$idx] . "\n";
		}
	}
	return "$result\n";
}

function logMessage ($msg, $logfile) {
	
	if ($fd = fs_fopen($logfile, "a")) {
		fwrite($fd, strftime("%Y/%m/%d %H:%M.%S: $msg\n"));
		fclose($fd);
	}
	elseif (isDebugging()) {
		print sprintf(_("Cannot open logfile: %s"), $logfile);
	}
}

/* simplify condition tests */
function testRequirement($test) {
    global $gallery;
    if(substr($test, 0,1 ) == "!") {
        $test = substr($test, 1);
        $negativeTest = true;
    }
    else {
        $negativeTest = false;
    }

    switch ($test) {
        case 'albumIsRoot':
            $result = $gallery->album->isRoot();
        break;
        case 'isAdminOrAlbumOwner':
            $result = $gallery->user->isAdmin() || $gallery->user->isOwnerOfAlbum($gallery->album);
        break;

        case 'comments_enabled':
            $result = $gallery->app->comments_enabled == 'yes';
        break;

        case 'allowComments':
            $result = $gallery->album->fields["perms"]['canAddComments'];
        break;

        case 'hasComments':
            $result = ($gallery->album->lastCommentDate("no") != -1);
        break;

        case 'canAddToAlbum':
            $result = $gallery->user->canAddToAlbum($gallery->album);
        break;

        case 'canDeleteAlbum':
            $result = $gallery->user->canDeleteAlbum($gallery->album);
        break;

        case 'extraFieldsExist':
            $extraFields = $gallery->album->getExtraFields();
            $result = !empty($extraFields);
        break;

        case 'isAlbumOwner':
            $result = $gallery->user->isOwnerOfAlbum($gallery->album);
        break;

        case 'canCreateSubAlbum':
            $result = $gallery->user->canCreateSubAlbum($gallery->album);
        break;

        case 'notOffline':
            $result = !$gallery->session->offline;
        break;

        case 'canChangeText':
            $result = $gallery->user->canChangeTextOfAlbum($gallery->album);
        break;

        case 'canWriteToAlbum':
            $result = $gallery->user->canWriteToAlbum($gallery->album);
        break;

        case 'photosExist':
            $result = $gallery->album->numPhotos(true);
        break;

        case 'watermarkingEnabled':
            $result = isset($gallery->app->watermarkDir);
        break;

        default:
            $result = false;
        break;
    }
    if ($negativeTest) {
        $result = ! $result;
    }
    
    return $result;
}

/**
 * @return string	$location	Location where Gallery assmes the user. Can be 'core' or 'config'
 * @author Jens Tkotz <jens@peino.de>
 */
function where_i_am() {
    global $GALLERY_OK;

    if (!stristr($_SERVER['REQUEST_URI'],'setup') || $GALLERY_OK) {
        $location = 'core';
    } else {
        $location = 'config';
    }
    return $location;
}

// Returns the CVS version as a string, NULL if file can't be read, or "" 
// if version can't be found.

function getCVSVersion($file) {

    $path= dirname(__FILE__) . "/$file";
    if (!fs_file_exists($path)) {
        return NULL;
    }
    if (!fs_is_readable($path)) {
        return NULL;
    }
    $contents=file($path);
    foreach ($contents as $line) {
        if (ereg("\\\x24\x49\x64: [A-Za-z_.0-9]*,v ([0-9.]*) .*\x24$", trim($line), $matches) ||
        ereg("\\\x24\x49\x64: [A-Za-z_.0-9]*,v ([0-9.]*) .*\x24 ", trim($line), $matches)) {
            if ($matches[1]) {
                return $matches[1];
            }
        }

    }
    return '';
}

/* Return -1 if old version is greater than new version, 0 if they are the 
   same and 1 if new version is greater.
 */
function compareVersions($old_str, $new_str) {
	if ($old_str === $new_str) { 
		return 0;
	}
	$old=explode('.', $old_str);
	$new=explode('.', $new_str);
	foreach ($old as $old_number) {
		$old_number=0+$old_number;
		$new_number=0+array_shift($new);
		if ($new_number  == null) {
			return -1;
		}
		if ($old_number == $new_number) {
			continue;
		}
		if ($old_number > $new_number) {
			return -1;
		}
		// if ($old_number < $new_number)
		return 1;
	}
	if (count($new) == 0) {
		return 0;
	}
	return 1;
}

function contextHelp ($link) {
	global $gallery;
	
	if ($gallery->app->showContextHelp == 'yes') {
		return popup_link ('?', 'docs/context-help/' . $link, false, true, 500, 500);
	} else {
		return null;
	}
}

function parse_csv ($filename, $delimiter=";") {
    echo debugMessage(sprintf(_("Parsing for csv data in file: %s"), $filename), __FILE__, __LINE__);
	$maxLength = 1024;
	$return_array = array();
	if ($fd = fs_fopen($filename, "rt")) {
		$headers = fgetcsv($fd, $maxLength, $delimiter);
		while ($columns = fgetcsv($fd, $maxLength, $delimiter)) {
			$i = 0;
			$current_image = array();
			foreach ($columns as $column) {
				$current_image[$headers[$i++]] = $column;
			}
			$return_array[] = $current_image;
		}
		fclose($fd);
	}
	if(isDebugging()){
	   echo _("csv result:");
	   print_r($return_array);
	}
	return $return_array;	
}

/**
 * This function strips slashes from an array Key
 * e.g. $foo[\'bar\'] will become $foo['bar']
 *
 * @param  array $arr
 * @author Andrew Lindeman, 02/2004
*/
function key_strip_slashes (&$arr) {
    $keys = array_keys ($arr);

    foreach ($keys as $val) {
        $tmpVal = stripslashes ($val);

        if ($tmpVal != $val) {
            $arr[$tmpVal] = $arr[$val];
            unset ($arr[$val]);
        }

        if (is_array ($arr[$tmpVal])) {
            key_strip_slashes ($arr[$tmpVal]);
        }
    }
}

function getExtraFieldsValues($index, $extra_fields, $full) {
    global $gallery;
    $photo = $gallery->album->getPhoto($index);
    $automaticFields = automaticFieldsList();

    $table = array();

    foreach ($extra_fields as $key) {
        if (isset($automaticFields[$key]) && $key != 'EXIF') {
            if ($key == 'Upload Date') {
                $table[$automaticFields[$key]] = strftime($gallery->app->dateTimeString , $gallery->album->getUploadDate($index));
            }

            if ($key == 'Capture Date') {
                $itemCaptureDate = $gallery->album->getItemCaptureDate($index);
                $table[$automaticFields[$key]] = strftime($gallery->app->dateTimeString , $itemCaptureDate);
            }

            if ($key == 'Dimensions') {
                $dimensions = $photo->getDimensions($full);
                $table[$automaticFields[$key]] = $dimensions[0]." x ".$dimensions[1]." (". ((int) $photo->getFileSize($full) >> 10) ."k)";
            }
        }
        else {
            $value = $gallery->album->getExtraField($index, $key);
            if (!empty($value)) {
                /* Might be look strange, but $key could be in translateableFields() */
                $table[_($key)] = str_replace("\n", "<br>", $value);
            }
        }
    }
    return $table;
}

if (!function_exists('glob')) {
	function glob($pattern) {
		$path_parts = pathinfo($pattern);
		$pattern = '^' . str_replace(array('*',  '?'), array('(.+)', '(.)'), $path_parts['basename'] . '$');
		$dir = fs_opendir($path_parts['dirname']);
		while ($file = readdir($dir)) {
			if (ereg($pattern, $file)) {
				$result[] = "{$path_parts['dirname']}/$file";
			}
		}
		closedir($dir);

		// my changes here
		if (isset($result))
			return $result;

		return (array)null;
	} 
}

function genGUID() {
	return md5(uniqid(mt_rand(), true));
}

function calcVAdivDimension($frame, $iHeight, $iWidth, $borderwidth) {
	global $gallery;
	$thumbsize = $gallery->album->fields["thumb_size"];

	// If the user has set their Gallery to display larger images,
	// accomodate for it.
	if (!($iHeight < $thumbsize && $iWidth < $thumbsize)) {
	    $thumbsize = max($iHeight, $iWidth);
	}
	    

	switch ($frame) {
		// special cases
		case "none":
			$divCellWidth = $thumbsize + 3;
			$divCellAdd =  3;
		break;

		case "dots":
			$divCellWidth = $thumbsize + 7;
			$divCellAdd =  7;
		break;
	
		case "solid":
			$divCellWidth = $thumbsize + $borderwidth +3;
			$divCellAdd =  $borderwidth +3;
		break;
                  

		default: // use frames directory
			require(dirname(__FILE__) . "/html_wrap/frames/$frame/frame.def");
                    
			$divCellWidth = $thumbsize + $widthTL + $widthTR;
			$divCellAdd = $heightTT + $heightBB;
		break;
	} // end of switch

	// This is needed to keep smaller images centered
	$padding=round(($thumbsize-$iHeight)/2,0);
	$divCellHeight=$thumbsize-$padding*2+$divCellAdd;

	/* For Debugging */
	// echo "$divCellWidth, $divCellHeight, $padding";
	return array ($divCellWidth, $divCellHeight, $padding);
}

/* 
** Counts all Elements of an Array, but not the array(s) itself
** Code by A. Lindeman
*/
function recursiveCount (&$arr) {
	$count = 0;
	foreach ($arr as $element) {
		if (is_array ($element)) {
			$count += recursiveCount ($element);
		} else {
			$count++;
		}
	}

	return $count;
}

/**
 * Loads an array on extensions mapping to the mimetype
 * Returns the mimetype according to the extension of given filename
 * @param  string   $filename
 * @return string   $mimetype
 * @author Jens Tkotz <jens@peino.de
 */
function getMimeType($filename) {
    static $mime_extension_map;
    
    if (empty($mime_extension_map)) {
        require(dirname(__FILE__) . '/includes/definitions/mime.mapping.php');
    }
    
    $extension = getExtension($filename);
    $mimetype = $mime_extension_map[$extension];
    
    echo debugMessage(sprintf(_("MIMEtype of file %s is %s"), basename($filename), $mimetype), __FILE__, __LINE__, 2);
    return $mimetype;
}
    
/* Ecard Function begin */
function get_ecard_template($template_name) {
    global $gallery;

    $error = false;
    $file_data = '';
    $fpread = @fopen(dirname(__FILE__) . '/includes/ecard/templates/'. $template_name, 'r');
    if (!$fpread) {
        $error = true;
    } else {
        while(! feof($fpread) ) {
            $file_data .= fgets($fpread, 4096);
        }
        fclose($fpread);
    }
    return array($error,$file_data);
}

/**
 * This function parses template and substitutes placeholders
 * @param    array    $ecard		array which contains infos about the ecard
 * @param    string   $ecard_data	string containing the slurped template
 * @param    boolean  $preview		image source is different for preview or final card.
 * @return   string   $ecard_data	modified template data
 */
function parse_ecard_template($ecard,$ecard_data, $preview = true) {
    global $gallery;

    $imagePath = $gallery->album->getAbsolutePhotoPath($ecard['photoIndex'], false);
    $photo = $gallery->album->getPhoto($ecard['photoIndex']);
    if($preview) {
	$imageName = $gallery->album->getPhotoPath($ecard['photoIndex'], false);
	$stampName = getImagePath('ecard_images/'. $ecard['stamp'] .'.gif');
    }
    else {
	$imageName = $photo->getImageName(false);
	$stampName = $ecard['stamp'] .'.gif';
    }

    list ($width, $height) = getDimensions($imagePath);
    $widthReplace = ($width < 200) ? 'width="500"' : '';

    $ecard_data = preg_replace ("/<%ecard_sender_email%>/", $ecard["email_sender"], $ecard_data);
    $ecard_data = preg_replace ("/<%ecard_sender_name%>/", $ecard["name_sender"], $ecard_data);
    $ecard_data = preg_replace ("/<%ecard_image_name%>/", $imageName, $ecard_data);
    $ecard_data = preg_replace ("/<%ecard_message%>/", preg_replace ("/\r?\n/", "<BR>\n", htmlspecialchars($ecard["message"])), $ecard_data);
    $ecard_data = preg_replace ("/<%ecard_reciepient_email%>/", $ecard["email_recepient"], $ecard_data);
    $ecard_data = preg_replace ("/<%ecard_reciepient_name%>/", $ecard["name_recepient"], $ecard_data);
    $ecard_data = preg_replace ("/<%ecard_stamp%>/", $stampName, $ecard_data);
    $ecard_data = preg_replace ("/<%ecard_width%>/", $widthReplace, $ecard_data);
	
    return $ecard_data;
  }

  function send_ecard($ecard,$ecard_HTML_data,$ecard_PLAIN_data) {
      global $gallery;

      $ecard_pictures = array();
      $photo = $gallery->album->getPhoto($ecard['photoIndex']);
      $ecard_mail = new htmlMimeMail();

      $imagePath = $gallery->album->getAbsolutePhotoPath($ecard['photoIndex'], false);
      $imageName = $photo->getImageName(false);

      $stampName = $ecard['stamp'] .'.gif';
      $stampPath = getAbsoluteImagePath("ecard_images/$stampName");

      $ecard_pictures[$imageName] = $imagePath;
      $ecard_pictures[$stampName] = $stampPath;

      foreach ($ecard_pictures as $pictureName => $picturePath) {
          $picture = $ecard_mail->getFile($picturePath);
          $ecard_mail->addHtmlImage($picture, $pictureName, getMimeType($picturePath));
      }

      /*
       * Currently all other images in the template are ignored.
      if (preg_match_all("/(<IMG.*SRC=\")(.*)(\".*>)/Uim", $ecard_HTML_data, $matchArray)) {
        for ($i = 0; $i < count($matchArray[0]); ++$i) {
            $ecard_image = $ecard_mail->getFile($matchArray[2][$i]);
        }
      }
      */
      $ecard_mail->setHtml($ecard_HTML_data, $ecard_PLAIN_data);
      $ecard_mail->setFrom($ecard["name_sender"] .' <'. $ecard["email_sender"] .'>');
      if (empty($ecard['subject'])) {
          $ecard['subject'] = sprintf(_("%s sent you an E-C@rd."), $ecard["name_sender"]);
      }

      $ecard_mail->setSubject($ecard['subject']);
      $ecard_mail->setReturnPath($ecard["email_sender"]);

      $result = $ecard_mail->send(array($ecard["name_recepient"] .' <'. $ecard["email_recepient"] .'>'));

      return $result;
  }
  
/**
 * This function is taken from
 * http://www.phpinsider.com/smarty-forum/viewtopic.php?t=1079
 *
 * @param	array	$data		The array that is going to be sorted.
 * @param	string	$sortby		Field which the array is sorted by
 * @param	string	$order		Either 'asc' or 'desc'
 * @param	boolean	$caseSensitive
 * @param	boolean	$keepIndexes	if set to true, then uasort instead of usort is used.
 */
function array_sort_by_fields(&$data, $sortby, $order = 'asc', $caseSensitive = true, $keepIndexes = false, $special = false) {
	static $sort_funcs = array();
	static $code;

	$order = ($order == 'asc') ? 1 : -1;

	if (empty($sort_funcs[$sortby])) {

	    if ($special) {
		$a = "\$a->fields[\"$sortby\"]";
		$b = "\$b->fields[\"$sortby\"]";
	    }
	    else {
		$a = "\$a['$sortby']";
		$b = "\$b['$sortby']";
	    }

	    if ($caseSensitive) {
			$code = "
	    if( $a == $b ) { 
	        return 0;
	    };
	    if ( $a > $b ) {
	        return $order;
	    } else {
	        return -1 * $order;
	    }";
		}
	    else {
			$code = "
	    if(strtoupper($a) == strtoupper($b)) { 
	        return 0;
	    };
	    if (strtoupper($a) > strtoupper($b)) {
	        return $order;
	    } else {
	        return -1 * $order;
	    }";
	    }
	
	    $sort_func = $sort_funcs[$sortby] = create_function('$a, $b', $code);
	} else {
	    $sort_func = $sort_funcs[$sortby];
	}

	debugMessage($code, __FILE__, __LINE__, 3);

	if($keepIndexes) {
		uasort($data, $sort_func);
	} else {
		usort($data, $sort_func);
	}
}

/**
 * creates a copy of a album structure
 * @param    array	$albumItemNames	Array containing an albumstructure with absolute filenames.
 * @param    string	$dir		Optional dir, which can be used for recursice purpose.
 * @return   string	$mixed		In success the dirname as string, where the files copied to. Otherwise false.
 * @author   Jens Tkotz <jens@peino.de>
 */
function createTempAlbum($albumItemNames = array(), $dir = '') {
    global $gallery;

    if(empty($albumItemNames)) {
        return false;
    }

    $prefix = 'gallery_download_';

    if (empty($dir)) {
        $token = uniqid(rand());
        $dir = $gallery->app->tmpDir .'/'. $prefix . $token;
    }

    if(! fs_mkdir($dir)) {
        echo gallery_error(
        sprintf(_("Your tempfolder is not writeable! Please check permissions of this dir: %s"),
        $gallery->app->tmpDir));
        return false;
    }

    //echo "<h3>Created dir: $dir</h3>";

    foreach($albumItemNames as $possibleAlbumName => $filename) {
        if(is_array($filename)) {
            //echo "\n<ul>";
            createTempAlbum($filename, "$dir/$possibleAlbumName");
            //echo "\n</ul>";
        }
        else {
            $destination = $dir .'/'. basename ($filename);
            //echo "\n\t<li>$filename -->  $destination</li>";
            if(! fs_copy($filename, $destination)) {
                echo gallery_error("Copy Failed");
            }
        }
    }

    return $dir;
}

function rmdirRecursive($dir) {
   if($objs = glob($dir."/*")){
       foreach($objs as $obj) {
           is_dir($obj)? rmdirRecursive($obj) : unlink($obj);
       }
   }
   rmdir($dir);
}

function downloadFile($filename) {
    $contentType = getMimeType($filename);
    $size = fs_filesize($filename);

    $fp = fopen($filename, 'r');
    $filedata = fread($fp, $size);
    fclose($fp);

    header('Pragma: private');
    header('Cache-control: private, must-revalidate');
    header("Content-type: $contentType");

    if ($size > 0) {
       header("Content-Length: $size");
    }

    header('Content-Disposition: attachment; filename="'. basename($filename) .'"');

    echo $filedata;
    
    /* Now delete the file */
    rmdirRecursive(dirname($filename));
    
    return true;
}

/**
 * flats a multidimensional array done to a one-dimension array.
 * keys get lost.
 * @param  array $array
 * @return array $flatArray
 * @author Jens Tkotz <jens@peino.de>
 */
function array_flaten($array) {
    $flatArray = array();
    foreach($array as $value) {
        if(is_array($value)) {
            $flatArray = array_merge($flatArray, array_flaten($value));
        }
        else {
            $flatArray[] = $value;
        }
    }
    return $flatArray;
}

require_once(dirname(__FILE__) . '/lib/lang.php');
require_once(dirname(__FILE__) . '/lib/Form.php');
require_once(dirname(__FILE__) . '/lib/voting.php');
require_once(dirname(__FILE__) . '/lib/album.php');
require_once(dirname(__FILE__) . '/lib/albumItem.php');
require_once(dirname(__FILE__) . '/lib/imageManipulation.php');
require_once(dirname(__FILE__) . '/lib/mail.php');

?>
