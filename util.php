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
		$url = $gallery->app->photoAlbumURL . 
			"/edit_field.php?set_albumName={$album->fields[name]}&field=$field";
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
		$url = $gallery->app->photoAlbumURL . 
			"/edit_caption.php?set_albumName={$album->fields[name]}&index=$index";
		$buf .= "<span class=editlink>";
		$buf .= '<a href="#" onClick="' . popup($url) . '">[edit]</a>';
		$buf .= "</span>";
	}
	return $buf;
}

function error($message) {
	echo error_format($message);
}

function error_format($message) {
	return "<span class=error>Error: $message</span>";
}

function popup($url, $no_expand_url=0) {
	if (!$no_expand_url) {
		$url = "'$url'";
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
	return "javascript: nw=windown.open('http://www.menalto.com/projects/gallery/help?group=$group&entry=$entry','Help','$attrs'); nw.opener=self; return false;";
}

function exec_internal($cmd) {
	global $gallery;
	if (isDebugging()) {
		print "<p><b> About to exec [$cmd]</b>";
	}

	exec($cmd, $results, $status);

	if (isDebugging()) {
		print "<br> Results: <pre>" . join("\n", $results);
		print "<br> Status: $status (expected " . $gallery->app->expectedExecStatus . ")";
	}

	return array($results, $status);
}

function getDimensions($file) {
	global $gallery;				

	list($lines, $status) = 
		exec_internal(toPnmCmd($file) . 
			"| " .
			$gallery->app->pnmDir . 
			"/pnmfile ");

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
	return "(" . array_join("|", formatList()) . ")";
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

	if (!file_exists($fname)) {
		return $tmp;
	}

	if ($fd = fopen($fname, "r")) {
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
		echo("Not closing this window because debug mode is on");
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
		echo("Not closing this window because debug mode is on");
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
		     "| " . 
		     $gallery->app->pnmDir .
		     "/pnmscale --quiet -xysize $target $target".
		     "| " . fromPnmCmd($out));

	if (file_exists("$out") && filesize("$out") > 0) {
		if ($useTemp) {
			copy($out, $dest);
			unlink($out);
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
		     "| " .
		     $gallery->app->pnmDir .
		     "/pnmflip $args".
		     "| " . fromPnmCmd($out));

	if (file_exists("$out") && filesize("$out") > 0) {
		if ($useTemp) {
			copy($out, $dest);
			unlink($out);
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
		     "| " .
		     $gallery->app->pnmDir .
		     "/pnmcut $x $y $width $height" .
		     "| " . fromPnmCmd($out));

	if (file_exists("$out") && filesize("$out") > 0) {
		if ($useTemp) {
			copy($out, $dest);
			unlink($out);
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
			"| " .
			$gallery->app->pnmDir .
			"/pnmfile");

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
			$cmd = "jpegtopnm --quiet";
		}
	} else if (preg_match("/.gif/i", $file)) {
		$cmd = "giftopnm";
	}

	if ($cmd) {
		return $gallery->app->pnmDir . "/$cmd $file";
	} else {
		error("Unknown file type: $file");
		return "";
	}
}

function fromPnmCmd($file) {
	global $gallery;

	if (preg_match("/.png/i", $file)) {
		$cmd = "pnmtopng";
	} else if (preg_match("/.(jpg|jpeg)/i", $file)) {
		$cmd = "ppmtojpeg";
	} else if (preg_match("/.gif/i", $file)) {
		$cmd = "ppmquant 256| " . $gallery->app->pnmDir . "/ppmtogif";
	}

	if ($cmd) {
		return $gallery->app->pnmDir . "/$cmd > $file";
	} else {
		error("Unknown file type: $file");
		return "";
	}
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
	// define these globals to make them available to custom text
        global $gallery;
	$fullname = "html_wrap/$name";

	if (file_exists($fullname)) {
		include ($fullname);
	} else {
		include ("$fullname.default");
	}

	return 1;
}

function getGalleryStyleSheetName() {
	global $gallery;
        $sheetname = "css/gallery_style.css";

	if ($gallery->app) {
		$base = $gallery->app->photoAlbumURL;
	} else {
		$base = ".";
	}

	if (file_exists($sheetname)) {
		return ("$base/$sheetname");
	} else {
		return ("$base/$sheetname.default");
	}

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
		include("html/errorRow.inc");
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

function makeGalleryUrl($albumName, $photoId="", $extra="") {
	global $gallery;

	$url = $gallery->app->photoAlbumURL;

	$args = array();
	if ($gallery->app->feature["rewrite"]) {
		if ($albumName) {
			$url .= "/$albumName";

			// Can't have photo without album
			if ($photoId) {
				$url .= "/$photoId";
			} 
		}
	} else {
		if ($albumName) {
			$url = $gallery->app->photoAlbumURL . "/view_album.php";
			array_push($args, "set_albumName=$albumName");
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

	return $url;
}

function gallerySanityCheck() {
	global $gallery;

	if (!file_exists("config.php") || !$gallery->app) {
		include("errors/unconfigured.php");
		exit;
	}

	if (file_exists("setup") && is_readable("setup")) {
		/* 
		 * on some systems, PHP's is_readable returns false
		 * positives.  Make extra sure.
		 */
		$perms = sprintf("%o", fileperms("setup"));
		if (strstr($perms, "755")) {
			include("errors/configmode.php");
			exit;
		}
	}

	if ($gallery->app->config_version != $gallery->config_version) {
		include("errors/reconfigure.php");
		exit;
	}
}

function preprocessImage($dir, $file) {

	if (!file_exists("$dir/$file")) {
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

	if ($fd = fopen("$dir/$file", "r")) {
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
			if ($newfd = fopen($tempfile, "w", 0777)) {
				while (!feof($fd)) {
					/*
					 * Copy the rest of the file.  Specify a length
					 * to fwrite so that we ignore magic_quotes.
					 */
					fwrite($newfd, fread($fd, 64*1024), 64*1024+1);
				}
				fclose($newfd);
				$success = rename($tempfile, "$dir/$file");
				if (!$success) {
					error("Couldn't move $tempfile -> $dir/$file");
					unlink($tempfile);
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
		return $idx;
	}

	$numPhotos = $gallery->album->numPhotos(1);
	while ($idx <= $numPhotos && $gallery->album->isHidden($idx)) {
		$idx++;
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
