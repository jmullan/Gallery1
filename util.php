<?
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2001 Bharat Mediratta
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
// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
	print "Security violation\n";
	exit;
}
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
	global $GALLERY_BASEDIR;

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
		include($GALLERY_BASEDIR . "layout/commentdraw.inc");
	}
        $url = "add_comment.php?set_albumName={$album->fields[name]}&index=$index";
        $buf = "<span class=editlink>";
        $buf .= '<a href="#" onClick="' . popup($url) . '">[add comment]</a>';
        $buf .= "</span>";
        echo "<tr align=center><td colspan=3>$buf<br><br></td></tr>";
}

function center($message) {
	return "<center>$message</center>";
}

function error($message) {
	echo error_format($message);
}

function error_format($message) {
	return "<span class=error>Error: $message</span>";
}

function popup($url, $url_is_complete=0) {
	/* Separate the target from the arguments */
	list($target, $arglist) = split("\?", $url);

	/* Split the arguments into an associative array */
	$tmpargs = split("&", $arglist);
	foreach ($tmpargs as $arg) {
		if (!$arg) {
			continue;
		}
		list($key, $val) = split("\=", $arg);
		$args[$key] = $val;
	}

	if (!$url_is_complete) {
		$url = makeGalleryUrl($target, $args);
		$url = "'$url'";
	} 

	return popup_js($url, "Edit", 
		"height=500,width=500,location=no,scrollbars=yes,menubars=no,toolbars=no,resizable=yes");
}

function popup_js($url, $window, $attrs) {
	return "javascript:nw=window.open($url,'$window','$attrs');nw.opener=self;return false;";
}

function popup_status($url) {
	$attrs = "height=150,width=350,location=no,scrollbars=no,menubars=no,toolbars=no,resizable=yes";
	return "open('" . makeGalleryUrl($url) . "','Status','$attrs');";
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
		echo("<center><b>Not closing this window because debug mode is on</b></center>");
		echo("<hr>");
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
		echo("<center><b>Not closing this window because debug mode is on</b></center>");
		echo("<hr>");
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

	if (eregi("\.png", $file)) {
		$cmd = "pngtopnm";
	} else if (eregi("\.(jpg|jpeg)", $file)) {
		if (isDebugging()) {
			$cmd = "jpegtopnm";
		} else {
			$cmd = "jpegtopnm";
		}
	} else if (eregi("\.gif", $file)) {
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

	if (eregi("\.png", $file)) {
		$cmd = NetPBM("pnmtopng");
	} else if (eregi("\.(jpg|jpeg)", $file)) {
		$cmd = NetPBM("ppmtojpeg", "--quality=95");
	} else if (eregi(".gif", $file)) {
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

	if ($gallery->app && $gallery->app->photoAlbumURL) {
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

function correctNobody(&$array) {
	global $gallery;
	$nobody = $gallery->userDB->getNobody();

	if (count($array) > 1) {
		unset($array[$nobody->getUid()]);
	}

	if (count($array) == 0) {
		$array[$nobody->getUid()] = $nobody->getUsername();
	}
}

function correctEverybody(&$array) {
	global $gallery;
	$everybody = $gallery->userDB->getEverybody();

	if ($array[$everybody->getUid()]) {
		$array = array($everybody->getUid() => $everybody->getUsername());
	}
}

/*
 * makeFormIntro() is a wrapper around makeGalleryUrl() that will generate
 * a <form> tag suitable for usage in either standalone or embedded mode.
 * You can specify the additional attributes you want in the optional second
 * argument.  Eg:
 *
 * makeFormIntro("add_photos.php",
 *			array("name" => "count_form",
 *				"enctype" => "multipart/form-data",
 *				"method" => "POST"));
 */
function makeFormIntro($target, $attrList=array()) {
	$url = makeGalleryUrl($target);
	list($target, $tmp) = split("\?", $url);

	foreach ($attrList as $key => $value) {
		$attrs .= " $key=\"$value\"";
	}

	$form .= "<form action=$target $attrs>\n";

	$args = split("&", $tmp);
	foreach ($args as $arg) {
		list($key, $val) = split("=", $arg);
		$form .= "<input type=hidden name=\"$key\" value=\"$val\">\n";
	}
	return $form;
}

/*
 * Any URL that you want to use can either be accessed directly
 * in the case of a standalone Gallery, or indirectly if we're
 * mbedded in another app such as Nuke.  makeGalleryUrl() will 
 * always create the appropriate URL for you.
 *
 * Usage:  makeGalleryUrl(target, args [optional])
 *
 * target is a file with a relative path to the gallery base
 *        (eg, "album_permissions.php")
 *
 * args   are extra key/value pairs used to send data
 *        (eg, array("index" => 1, "set_albumName" => "foo"))
 */
function makeGalleryUrl($target, $args=array()) {
	global $gallery;
	global $GALLERY_EMBEDDED_INSIDE;
	global $GALLERY_MODULENAME;

	switch ($GALLERY_EMBEDDED_INSIDE) {
		case "nuke":
			$args["op"] = "modload";
			$args["name"] = "$GALLERY_MODULENAME";
			$args["file"] = "index";

			/*
			 * include *must* be last so that the JavaScript code in 
			 * view_album.php can append a filename to the resulting URL.
			 */
			$args["include"] = $target;
			$target = "modules.php";
			break;

		default:
			$target = $gallery->app->photoAlbumURL . "/" . $target;
			break;
	}

	$url = $target;
	if ($args) {
		$i = 0;
		foreach ($args as $key => $value) {
			if ($i++) {
				$url .= "&";
			} else {
				$url .= "?";
			}
			$url .= "$key=$value";
		}
	}
	return $url;
}

/*
 * makeAlbumUrl is a wrapper around makeGalleryUrl.  You tell it what
 * album (and optional photo id) and it does the rest.  You can also
 * specify additional key/value pairs in the optional third argument.
 */
function makeAlbumUrl($albumName="", $photoId="", $args=array()) {
	global $GALLERY_EMBEDDED_INSIDE;
	global $gallery;

	if (!$GALLERY_EMBEDDED_INSIDE && $gallery->app->feature["rewrite"]) {
		if ($albumName) {
			$target = "$albumName";

			// Can't have photo without album
			if ($photoId) {
				$target .= "/$photoId";
			} 
		} else {
			$target = "albums.php";
		}
	} else {
		if ($albumName) {
			$args["set_albumName"] = "$albumName";
			if ($photoId) {
				$target = "view_photo.php";
				$args["id"] = "$photoId";
			} else {
				$target = "view_album.php";
			}
		} else {
			$target = "albums.php";
		}

	}
	return makeGalleryUrl($target, $args);
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
	global $gallery;

	$idx++;
	if ($gallery->user->canWriteToAlbum($gallery->album)) {
		// even though a user can write to an album, they may
		// not have read authority over a specific nested album.
		if ($idx <= $numPhotos && $gallery->album->isAlbumName($idx)) {
			$myAlbumName = $gallery->album->isAlbumName($idx);
			$myAlbum = new Album();
			$myAlbum->load($myAlbumName);
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

	if ($idx <= $numPhotos && $gallery->album->isAlbumName($idx)) {
		// do not display a nexted album if the user doesn't
		// have permission to view it.
		if ($gallery->album->isAlbumName($idx)) {
			$myAlbumName = $gallery->album->isAlbumName($idx);
			$myAlbum = new Album();
			$myAlbum->load($myAlbumName);
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
	global $gallery, $index;
	
	$myAlbum = new Album();
	$myAlbum->load($albumName);
	
	$numPhotos = $myAlbum->numPhotos(1);

	for ($i=1; $i <= $numPhotos; $i++) {
		$myName = $myAlbum->isAlbumName($i);
		if ($myName) {
			$nestedAlbum = new Album();
			$nestedAlbum->load($myName);
			if ($gallery->user->canWriteToAlbum($nestedAlbum)) {
				$val2 = str_repeat("-- ", $level+1);
				$val2 = $val2 . $nestedAlbum->fields[title];
				if ($nestedAlbum != $gallery->album &&
				    $gallery->album->numPhotos(1) <= $index ||
				    $nestedAlbum != $gallery->album->getNestedAlbum($index)) {
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
        list($return, $status) = exec_internal(fs_import_filename($path, 1) .
						" " .
						fs_import_filename($file, 1));

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

	if (isDebugging()) {
		print "IN UTIL ITEMCAPTUREDATE = $itemCaptureDate[year]<br>";
	}
	return $itemCaptureDate;
}

function doCommand($command, $args=array(), $returnTarget="", $returnArgs=array()) {

	if ($returnTarget) {
		$args["return"] = urlencode(makeGalleryUrl($returnTarget, $returnArgs));
	}
	$args["cmd"] = $command;
	return makeGalleryUrl("do_command.php", $args);
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

function breakString($buf, $desired_len=40, $space_char=' ', $overflow=5) {
	$result = "";
	$col = 0;
	for ($i = 0; $i < strlen($buf); $i++, $col++) {
		$result .= $buf{$i};
		if (($col > $desired_len && $buf{$i} == $space_char) ||
		    ($col > $desired_len + $overflow)) {
			$col = 0;
			$result .= "<br>";
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

	if ($fd = fs_fopen($tmpfile, "w")) {
		fwrite($fd, serialize($obj));
		fclose($fd);

		/* 
		 * Make the current copy the backup, and then 
		 * write the new current copy
		 */
		if (fs_file_exists($file)) {
			$success = fs_rename($file, "$file.bak") &&
				   fs_rename($tmpfile, $file);
		} else {
			$success = fs_rename($tmpfile, $file);
		}
	}

	return $success;
}

?>
