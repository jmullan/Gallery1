<?
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000 Bharat Mediratta
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
?>
<?

function editField($album, $field, $edit) {
	global $gallery;

	$buf = $album->fields[$field];
	if (!strcmp($buf, "")) {
		$buf = "<i>&lt;Empty&gt;</i>";
	}
	if ($gallery->user->canChangeTextOfAlbum($album)) {
		$url = "edit_field.php?set_albumName={$album->fields[name]}&field=$field";
		$buf .= "<span class=editlink>";
		$buf .= '<a href="#" onClick="' . popup($url) . "\">[edit $field]</a>";
		$buf .= "</span>";
	}
	return $buf;
}

function editCaption($album, $index, $edit) {
	global $gallery;

	$buf = $album->getCaption($index);
	if ($gallery->user->canChangeTextOfAlbum($album)) {
		if (!strcmp($buf, "")) {
			$buf = "<i>&lt;No Caption&gt;</i>";
		}
		$url = "edit_caption.php?set_albumName={$album->fields[name]}&index=$index";
		$buf .= "<span class=editlink>";
		$buf .= '<a href="#" onClick="' . popup($url) . '">[edit]</a>';
		$buf .= "</span>";
	}
	return $buf;
}

function viewComments($index) {
        global $gallery;

	// get number of comments to use as counter for display loop
	$numComments = $gallery->album->numComments($index);
	$borderColor = $gallery->app->default["bordercolor"];
	for ($i=1; $i <= $numComments; $i++) {
		// get comments in this loop and then use layout/commentdraw.inc to display
		$comment = $gallery->album->getComment($index, $i);
		$commentdraw["comment"] = $comment->getCommentText();
		$commentdraw["IPNumber"] = $comment->getIPNumber();
		$commentdraw["datePosted"] = $comment->getDatePosted();
		$commentdraw["name"] = $comment->getName();
		$commentdraw["UID"] = $comment->getUID();
		$commentdraw["bordercolor"] = $borderColor;
		include("layout/commentdraw.inc");
	}
        $url = "add_comment.php?set_albumName={$album->fields[name]}&index=$index";
        $buf = "<span class=editlink>";
        $buf .= '<a href="#" onClick="' . popup($url) . '">[add comment]</a>';
        $buf .= "</span>";
        echo "<tr align=center><td colspan=3>$buf<br><br></td></tr>";
}


function error($message) {
	echo error_format($message);
}

function error_format($message) {
	return "<span class=error>Error: $message</span>";
}

function popup($url, $no_expand_url=0) {
	global $GALLERY_BASEDIR;

	$dir = $GALLERY_BASEDIR;
	if (!$dir) {
		global $gallery;
		$dir = $gallery->app->photoAlbumURL . "/";
	}

	if (!$no_expand_url) {
		$url = "'$dir$url'";
	}
	$attrs = "height=500,width=500,location=no,scrollbars=yes,menubars=no,toolbars=no,resizable=yes";
	return "javascript:nw=window.open($url,'Edit','$attrs');nw.opener=self;return false;";
}

function popup_status($url) {
	$attrs = "height=150,width=350,location=no,scrollbars=no,menubars=no,toolbars=no,resizable=yes";
	return "open('$url','Status','$attrs');";
}

function popup_help($entry, $group) {
	$attrs = "height=500,width=400,location=no,scrollbars=no,menubars=no,toolbars=no,resizable=yes";
	return "javascript: nw=window.open('http://www.menalto.com/projects/gallery/help?group=$group&entry=$entry','Help','$attrs'); nw.opener=self; return false;";
}

function exec_internal($cmd) {
	global $gallery;
	if (isDebugging()) {
		print "<p><b>Executing:<ul>$cmd</ul></b>";
	}

	fs_exec($cmd, $results, $status);

	if (isDebugging()) {
		print "<br> Results: <pre>";
		if ($results) {
			print join("\n", $results);
		} else {
			print "<b>none</b>";
		}
		print "</pre>";
		print "<br> Status: $status (expected " . $gallery->app->expectedExecStatus . ")";
	}

	return array($results, $status);
}

function getDimensions($file) {
	global $gallery;				

	list($lines, $status) = 
		exec_internal(toPnmCmd($file) . 
			" | " .
			NetPBM("pnmfile", "--allimages")); 

	if ($status == $gallery->app->expectedExecStatus) {
		foreach ($lines as $line) {
			if (ereg("([0-9]+) by ([0-9]+)", $line, $regs)) {
				return array($regs[1], $regs[2]);
			}
		}
	}

	return array(0, 0);
}

function selectOptions($album, $field, $opts) {
	foreach ($opts as $opt) {
		$sel = "";
		if (!strcmp($opt, $album->fields[$field])) {
			$sel = "selected";
		}
		echo "<option $sel>$opt";
	}
}

function acceptableFormat($tag) {
	return (isImage($tag) || isMovie($tag));
}

function acceptableFormatRegexp() {
	return "(" . join("|", acceptableFormatList()) . ")";
}

function acceptableFormatList() {
	return array("jpg", "gif", "png", "avi", "mpg", "wmv", "mov");
}

function isImage($tag) {
	global $gallery; 

	return (!strcmp($tag, "jpg") ||
		!strcmp($tag, "jpeg") ||
		!strcmp($tag, "gif") ||
		!strcmp($tag, "png"));
}

function isMovie($tag) {
	return (!strcmp($tag, "avi") ||
		!strcmp($tag, "mpg") ||
		!strcmp($tag, "mov") ||
		!strcmp($tag, "wmv"));
}

function getFile($fname) {
	$tmp = "";

	if (!fs_file_exists($fname)) {
		return $tmp;
	}

	if ($fd = fs_fopen($fname, "r")) {
		while (!feof($fd)) {
			$tmp .= fread($fd, 65536);
		}
		fclose($fd);
	}
	return $tmp;
}

function dismissAndReload() {
	if (isDebugging()) {
		echo "<BODY onLoad='opener.location.reload();'>";
		echo("<b>Not closing this window because debug mode is on</b>");
	} else {
		echo "<BODY onLoad='opener.location.reload(); parent.close()'>";
	}
}

function reload() {
	echo "<BODY onLoad='opener.location.reload()'>";
}

function dismissAndLoad($url) {
	if (isDebugging()) {
		echo("<BODY onLoad='opener.location = \"$url\"; '>");
		echo("<b>Not closing this window because debug mode is on</b>");
	} else {
		echo("<BODY onLoad='opener.location = \"$url\"; parent.close()'>");
	}
}

function dismiss() {
	echo("<BODY onLoad='parent.close()'>");
}

function my_flush() {
	print str_repeat(" ", 4096);	// force a flush
}

function resize_image($src, $dest, $target) {
	global $gallery;				
	if (!strcmp($src,$dest)) {
		$useTemp = true;
		$out = "$dest.tmp";
	}
	else {
		$out = $dest;
	}
	$err = exec_wrapper(toPnmCmd($src) .
		     " | " . 
		     NetPBM("pnmscale", 
				(!isDebugging() ? " --quiet" : " ") .
				" -xysize $target $target") .
		     " | " . fromPnmCmd($out));

	if (fs_file_exists("$out") && fs_filesize("$out") > 0) {
		if ($useTemp) {
			fs_copy($out, $dest);
			fs_unlink($out);
		}
		return 1;
	} else {
		return 0;
	}
}

function rotate_image($src, $dest, $target) {
	global $gallery;				
	if (!strcmp($src,$dest)) {
		$useTemp = true;
		$out = "$dest.tmp";
	}
	else {
		$out = $dest;
	}

	if (!strcmp($target, "90")) {
		$args = "-r90";
	} else if (!strcmp($target, "-90")) {
		$args = "-r270";
	} else {
		$args = "-r180";
	}

	$err = exec_wrapper(toPnmCmd($src) .
		     " | " .
		     NetPBM("pnmflip", $args) .
		     " | " . fromPnmCmd($out));

	if (fs_file_exists("$out") && fs_filesize("$out") > 0) {
		if ($useTemp) {
			fs_copy($out, $dest);
			fs_unlink($out);
		}
		return 1;
	} else {
		return 0;
	}
}

function cut_image($src, $dest, $x, $y, $width, $height) {
	global $gallery;				
	if (!strcmp($src,$dest)) {
		$useTemp = true;
		$out = "$dest.tmp";
	}
	else {
		$out = $dest;
	}
	$err = exec_wrapper(toPnmCmd($src) .
			" | " .
			NetPBM("pnmcut") .
			" $x $y $width $height" .
			" | " . 
			fromPnmCmd($out));

	if (fs_file_exists("$out") && fs_filesize("$out") > 0) {
		if ($useTemp) {
			fs_copy($out, $dest);
			fs_unlink($out);
		}
		return 1;
	} else {
		return 0;
	}
}

function valid_image($file) {
	global $gallery;
	
	list($results, $status) = 
		exec_internal(toPnmCmd($file) . 
			" | " .
			NetPBM("pnmfile", "--allimages"));

	if ($status == $gallery->app->expectedExecStatus) {
		return 1;
	} else {
		return 0;
	}
}

function toPnmCmd($file) {
	global $gallery;

	if (preg_match("/.png/i", $file)) {
		$cmd = "pngtopnm";
	} else if (preg_match("/.(jpg|jpeg)/i", $file)) {
		if (isDebugging()) {
			$cmd = "jpegtopnm";
		} else {
			$cmd = "jpegtopnm";
		}
	} else if (preg_match("/.gif/i", $file)) {
		$cmd = "giftopnm";
	}

	if (!isDebugging()) {
		$args = "--quiet";
	}

	if ($cmd) {
		return NetPBM($cmd, $args) .
		 	" " .
			fs_import_filename($file);
	} else {
		error("Unknown file type: $file");
		return "";
	}
}

function fromPnmCmd($file) {
	global $gallery;

	if (preg_match("/.png/i", $file)) {
		$cmd = NetPBM("pnmtopng");
	} else if (preg_match("/.(jpg|jpeg)/i", $file)) {
		$cmd = NetPBM("ppmtojpeg");
	} else if (preg_match("/.gif/i", $file)) {
		$cmd = NetPBM("ppmquant", "256") . " | " . NetPBM("ppmtogif");
	}

	if ($cmd) {
		return "$cmd > " . fs_import_filename($file);
	} else {
		error("Unknown file type: $file");
		return "";
	}
}

function netPbm($cmd, $args="") {
	global $gallery;

	$cmd = fs_import_filename($gallery->app->pnmDir . "/$cmd");
	$cmd .= " $args";
	return $cmd;
}

function exec_wrapper($cmd) {
	global $gallery;

	list($results, $status) = exec_internal($cmd);

	if ($status == $gallery->app->expectedExecStatus) {
		return 0;
	} else {
		if ($results) {
			error(join("<br>", $results));
		}
		return 1;
	}
}
function includeHtmlWrap($name) {
	global $GALLERY_BASEDIR;

	// define these globals to make them available to custom text
        global $gallery;
	$fullname = $GALLERY_BASEDIR . "html_wrap/$name";

	if (fs_file_exists($fullname)) {
		include ($fullname);
	} else {
		include ("$fullname.default");
	}

	return 1;
}

function getStyleSheetLink() {
	global $GALLERY_EMBEDDED_INSIDE;

	if ($GALLERY_EMBEDDED_INSIDE) {
		return _getStyleSheetLink("embedded_style");
	} else {
		return _getStyleSheetLink("embedded_style") . 
			"\n" .
		       _getStyleSheetLink("standalone_style");
	}
}

function _getStyleSheetLink($filename) {
	global $gallery;

        $sheetname = "css/$filename.css";

	if ($gallery->app) {
		$base = $gallery->app->photoAlbumURL;
	} else {
		$base = ".";
	}

	if (fs_file_exists($sheetname)) {
		$url = "$base/$sheetname";
	} else {
		$url = "$base/$sheetname.default";
	}

	return '<link rel="stylesheet" type="text/css" href="' .
		$url .
		'">';

	return 1;
}

function pluralize($amt, $noun, $none="") {
	if ($amt == 1) {
		return "$amt $noun";
	}

	if ($amt == 0 && $none) {
		$amt = $none;
	}

	return "$amt ${noun}s";
}

function errorRow($key) {
	global $gErrors;

	$error = $gErrors[$key];
	if ($error) {	
		include($GALLERY_BASEDIR . "html/errorRow.inc");
	}
}

function drawSelect($name, $array, $selected, $size) {

	$buf = "";
	$buf .= "<select name=\"$name\" size=$size>\n";
	foreach ($array as $uid => $username) {
		$sel = "";
		if (!strcmp($uid, $selected)) {
			$sel = "selected";
		} 
		$buf .= "<option value=$uid $sel> $username\n";
	}
	$buf .= "</select>\n";

	return $buf;
}

function correctNobody($array) {
	global $gallery;
	$nobody = $gallery->userDB->getNobody();

	if (count($array) > 1) {
		unset($array[$nobody->getUid()]);
	}

	if (count($array) == 0) {
		$array[$nobody->getUid()] = $nobody->getUsername();
	}
}

function correctEverybody($array) {
	global $gallery;
	$everybody = $gallery->userDB->getEverybody();

	if ($array[$everybody->getUid()]) {
		$array = array($everybody->getUid() => $everybody->getUsername());
	}
}

function makeSearchFormIntro() {
	global $gallery;
	global $GALLERY_EMBEDDED_INSIDE;
	global $GALLERY_MODULENAME;
	switch ($GALLERY_EMBEDDED_INSIDE) {
		case "nuke":
            $form = "<form action=modules.php>";
			$form .= "<input type=hidden name=op value=modload>";
			$form .= "<input type=hidden name=file value=index>";
			$form .= "<input type=hidden name=name value=$GALLERY_MODULENAME>";
			$form .= "<input type=hidden name=include value=search.php>";
			break;
		default:
			$form = "<form action=search.php>";
	}
	return $form;
}


function makeGalleryUrl($albumName="", $photoId="", $extra="") {
	global $gallery;
	global $GALLERY_EMBEDDED_INSIDE;
	global $GALLERY_MODULENAME;

	switch ($GALLERY_EMBEDDED_INSIDE) {
		case "nuke":
			$url = "modules.php?op=modload&name=$GALLERY_MODULENAME&file=index";
			if ($albumName) {
				$inc = "&include=view_album.php";
				$url .= "&set_albumName=$albumName";
			}
			if ($photoId) {
				$inc = "&include=view_photo.php";
				$url .= "&id=$photoId";
			}
			$url .= $inc;
			$url .= "&" . $extra;
			break;

		default:
			$url = $gallery->app->photoAlbumURL;

			$args = array();
			if ($gallery->app->feature["rewrite"]) {
				if ($albumName) {
					$url .= "/$albumName";
		
					// Can't have photo without album
					if ($photoId) {
						$url .= "/$photoId";
					} 
				} else {
					$url .= "/albums.php";
				}
			} else {
				if ($albumName) {
					$url = $gallery->app->photoAlbumURL . "/view_album.php";
					array_push($args, "set_albumName=$albumName");
				} else {
					$url .= "/albums.php";
				}
		
				if ($photoId) {
					$url = $gallery->app->photoAlbumURL . "/view_photo.php";
					array_push($args, "id=$photoId");
				}
			}

			if ($extra) {
				array_push($args, $extra);
			}

			if (count($args)) {
				$url .= "?" . join("&", $args);
			} 
		
	}

	return $url;
}

function gallerySanityCheck() {
	global $gallery;
	global $GALLERY_BASEDIR;

	if (!fs_file_exists($GALLERY_BASEDIR . "config.php") || !$gallery->app) {
		include($GALLERY_BASEDIR . "errors/unconfigured.php");
		exit;
	}

	if (fs_file_exists($GALLERY_BASEDIR . "setup") && 
		is_readable($GALLERY_BASEDIR . "setup")) {
		/* 
		 * on some systems, PHP's is_readable returns false
		 * positives.  Make extra sure.
		 *
		 * Note: it's not possible for a win32 directory to
		 *       have 755 perms which is fine, since on win32
		 *       we don't actually change the permissions of
		 *       the directory anyway.
		 */
		$perms = sprintf("%o", fileperms($GALLERY_BASEDIR . "setup"));
		if (strstr($perms, "755")) {
			include($GALLERY_BASEDIR . "errors/configmode.php");
			exit;
		}
	}

	if ($gallery->app->config_version != $gallery->config_version) {
		include($GALLERY_BASEDIR . "errors/reconfigure.php");
		exit;
	}
}

function preprocessImage($dir, $file) {

	if (!fs_file_exists("$dir/$file")) {
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

	if ($fd = fs_fopen("$dir/$file", "r")) {
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
			if ($newfd = fs_fopen($tempfile, "w", 0777)) {
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
					error("Couldn't move $tempfile -> $dir/$file");
					fs_unlink($tempfile);
				}
			} else {
				error("Can't write to $tempfile");
			}
			chmod("$dir/$file", 0644);
		}
		fclose($fd);
	} else {
		error("Can't read $dir/$file");
	}

	return 1;
}

function isDebugging() {
	global $gallery;
	return !strcmp($gallery->app->debug, "yes");
}

function addUrlArg($url, $arg) {
	if (strchr($url, "?")) {
		return "$url&$arg";
	} else {
		return "$url?$arg";
	}
}

function getNextPhoto($idx) {
	global $gallery, $albumDB;

	$idx++;
	if ($gallery->user->canWriteToAlbum($gallery->album)) {
		// even though a user can write to an album, they may
		// not have read authority over a specific nested album.
		if ($gallery->album->isAlbumName($idx)) {
			$myAlbumName = $gallery->album->isAlbumName($idx);
			$myAlbum = $albumDB->getAlbumbyName($myAlbumName);
			if (!$gallery->user->canReadAlbum($myAlbum)) {
				$idx = getNextPhoto($idx);
			}
		}
		return $idx;
	}

	$numPhotos = $gallery->album->numPhotos(1);
	while ($idx <= $numPhotos && $gallery->album->isHidden($idx)) {
		$idx++;
	}

	if ($gallery->album->isAlbumName($idx)) {
		// do not display a nexted album if the user doesn't
		// have permission to view it.
		if ($gallery->album->isAlbumName($idx)) {
			$myAlbumName = $gallery->album->isAlbumName($idx);
			$myAlbum = $albumDB->getAlbumbyName($myAlbumName);
			if (!$gallery->user->canReadAlbum($myAlbum)) {
				$idx = getNextPhoto($idx);
			}
		}
	}

	return $idx;
}

function printAlbumOptionList($rootDisplay=1, $moveRootAlbum=0, $movePhoto=0) {
	global $gallery, $albumDB, $index;
	
	$mynumalbums = $albumDB->numAlbums($gallery->user);

	// create a ROOT option for the user to move the 
	// album to the main display
	echo "<option value=0 selected> << Select Album >> </option>\n";
	if ($gallery->user->canCreateAlbums() && $rootDisplay) {
		echo "<option value=ROOT>Top Level</option>";
	}
	$rootAlbumName = $gallery->album->getRootAlbumName();	
	// display all albums that the user can move album to
	for ($i=1; $i<=$mynumalbums; $i++) {
		$myAlbum=$albumDB->getAlbum($gallery->user, $i);
		if ($gallery->user->canWriteToAlbum($myAlbum) && 
			($rootAlbumName != $myAlbum->fields[name] || !$moveRootAlbum) ) {
			$albumName = $myAlbum->fields[name];
			$albumTitle = $myAlbum->fields[title];
			if ($myAlbum != $gallery->album) {
				echo "<option value=\"$albumName\">-- $albumTitle</option>\n";
			}
			printNestedVals(1, $albumName, $albumTitle, $movePhoto);
		}
	}
}


function printNestedVals($level, $albumName, $val, $movePhoto) {
	global $gallery, $albumDB, $index;
	
	$myAlbum = $albumDB->getAlbumbyName($albumName);
	
	$numPhotos = $myAlbum->numPhotos(1);

	for ($i=1; $i <= $numPhotos; $i++) {
		$myName = $myAlbum->isAlbumName($i);
		if ($myName) {
			$nestedAlbum = $albumDB->getAlbumbyName($myName);
			if ($gallery->user->canWriteToAlbum($nestedAlbum)) {
				#$val2 = $val . " -> " . $nestedAlbum->fields[title];
				$val2 = "";
				for ($j=0; $j<=$level; $j++) {
					$val2 = $val2 . "-- ";
				}
				$val2 = $val2 . $nestedAlbum->fields[title];
				if (($nestedAlbum != $gallery->album) && 
				   ($nestedAlbum != $gallery->album->getNestedAlbum($index))) {
					echo "<option value=\"$myName\"> $val2</option>\n";
					printNestedVals($level + 1, $myName, $val2, $movePhoto);
				} elseif ($movePhoto) {
					printNestedVals( $level + 1, $myName, $val2, $movePhoto);
				}
			}
		}
	}
}

function getExif($file) {
		global $gallery;

        $return = array();
        $path = $gallery->app->use_exif;
        exec("$path $file",$return);
        while (list($key,$value) = each ($return)) {
            $explodeReturn = explode(':', $value, 2);
            $myExif[trim($explodeReturn[0])] = trim($explodeReturn[1]);
        }

        return $myExif;
}

function getItemCaptureDate($file) {
	global $gallery;

	$success = 0;
	if ($gallery->app->use_exif) {
		$exifData = getExif($file);
		if ($exifData["Date/Time"]) {
			$success = 1;
			$tempDate = split(" ", $exifData["Date/Time"], 2);
			$tempDay = split(":" , $tempDate[0], 3);
			$tempTime = split(":", $tempDate[1], 3);
			$hours = "$tempTime[0]";
			$minutes = "$tempTime[1]";
			$seconds = "$tempTime[2]";
			$mday = "$tempDay[2]";
			$mon = "$tempDay[1]";
			$year = "$tempDay[0]";

			$itemCaptureDate[hours] = $hours;
			$itemCaptureDate[minutes] = $minutes;
			$itemCaptureDate[seconds] = $seconds;
			$itemCaptureDate[mday] = $mday;
			$itemCaptureDate[mon] = $mon;
			$itemCaptureDate[year] = $year;
		}
	}
	if (!$success) { // we were not able to get the capture date from exif... use file creation time
		$itemCaptureDate = getdate(filemtime($file));
	}

	// make sure everything (other than year) is 2 digits so we can do sorts with
	// the resulting concatenated data i.e.:  20010708123412
	if (strlen($itemCaptureDate["mon"]) == 1) {
		$itemCaptureDate["mon"] = "0" . $itemCaptureDate["mon"];
	}
	if (strlen($itemCaptureDate["mday"]) == 1) {
		$itemCaptureDate["mday"] = "0" . $itemCaptureDate["mday"];
	}
	if (strlen($itemCaptureDate["hours"]) == 1) {
		$itemCaptureDate["hours"] = "0" . $itemCaptureDate["hours"];
	}
	if (strlen($itemCaptureDate["minutes"]) == 1) {
		$itemCaptureDate["minutes"] = "0" . $itemCaptureDate["minutes"];
	}
	if (strlen($itemCaptureDate["seconds"]) == 1) {
		$itemCaptureDate["seconds"] = "0" . $itemCaptureDate["seconds"];
	}
	print "IN UTIL ITEMCAPTUREDATE = $itemCaptureDate[year]<br>";
	return $itemCaptureDate;
}

function doCommand($command, $args="", $returnFile="", $returnArgs="") {
	global $GALLERY_EMBEDDED_INSIDE;
	global $GALLERY_MODULENAME;

	switch ($GALLERY_EMBEDDED_INSIDE) {
		case "nuke":
			$url = "modules.php?op=modload&name=$GALLERY_MODULENAME&file=index" .
				"&include=do_command.php&cmd=$command";

			if ($returnFile) {
				$returnFile = "modules.php?op=modload&name=$GALLERY_MODULENAME&file=index" .
				"&include=$returnFile&$returnArgs";
			}
			break;

		default:
			global $gallery;
			$url = $gallery->app->photoAlbumURL . 
				"/do_command.php?cmd=$command";
			if ($returnFile && $returnArgs) {
				$returnFile = $returnFile . "?" . $returnArgs;
			}

			break;
	}

	if ($args) {
		$url .= "&$args";
	}

	if ($returnFile) {
		$url .= "&return=" . urlencode($returnFile);
	}

	return $url;
}

function formVar($name) {
	global $HTTP_GET_VARS;
	global $HTTP_POST_VARS;

	if (!empty($HTTP_GET_VARS[$name])) {
		return($HTTP_GET_VARS[$name]);
	}

	if (!empty($HTTP_POST_VARS[$name])) {
		return($HTTP_POST_VARS[$name]);
	}
}

function emptyFormVar($name) {
	global $HTTP_GET_VARS;
	global $HTTP_POST_VARS;

	return empty($HTTP_GET_VARS[$name]) && empty($HTTP_POST_VARS[$name]);
}
