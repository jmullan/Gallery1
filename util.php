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
	global $app, $user;

	$buf = $album->fields[$field];
	if (!strcmp($buf, "")) {
		$buf = "<i>&lt;Empty&gt;</i>";
	}
	if ($user->canChangeTextOfAlbum($album)) {
		$url = "$app->photoAlbumURL/edit_field.php?set_albumName={$album->fields[name]}&field=$field";
		$buf .= "<span class=editlink>";
		$buf .= "<a href=" . popup($url) . ">[edit $field]</a>";
		$buf .= "</span>";
	}
	return $buf;
}

function editCaption($album, $index, $edit) {
	global $app, $user;

	$buf = $album->getCaption($index);
	if ($user->canChangeTextOfAlbum($album)) {
		if (!strcmp($buf, "")) {
			$buf = "<i>&lt;No Caption&gt;</i>";
		}
		$url = "$app->photoAlbumURL/edit_caption.php?set_albumName={$album->fields[name]}&index=$index";
		$buf .= "<span class=editlink>";
		$buf .= "<a href=" . popup($url) . ">[edit]</a>";
		$buf .= "</span>";
	}
	return $buf;
}

function error($message) {
	echo "<span class=error>Error: $message<span>";
}

function popup($url) {
	$attrs = "height=450,width=500,location=no,scrollbars=yes,menubars=no,toolbars=no,resizable=yes";
	return "javascript:void(open('$url','Edit','$attrs'))";
}

function popup_status($url) {
	$attrs = "height=150,width=350,location=no,scrollbars=no,menubars=no,toolbars=no,resizable=yes";
	return "open('$url','Status','$attrs');";
}

function popup_help($entry, $group) {
	$attrs = "height=500,width=400,location=no,scrollbars=no,menubars=no,toolbars=no,resizable=yes";
	return "javascript:void(open('http://www.menalto.com/projects/gallery/help?group=$group&entry=$entry','Help','$attrs'));";
}

function getDimensions($file) {
	global $app;				

	exec(toPnmCmd($file) . "| $app->pnmDir/pnmfile ",
	     $lines,
	     $status);

	if ($status == 0) {
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

function isImage($tag) {
	global $app; 

	return (!strcmp($tag, "jpg") ||
		!strcmp($tag, "gif") ||
		!strcmp($tag, "png"));
}

function isMovie($tag) {
	return (!strcmp($tag, "avi") ||
		!strcmp($tag, "mpg"));
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
	echo "<BODY onLoad='opener.location.reload(); parent.close()'>";
}

function reload() {
	echo "<BODY onLoad='opener.location.reload()'>";
}

function dismissAndLoad($url) {
	echo("<BODY onLoad='opener.location = \"$url\"; parent.close()'>");
}

function dismiss() {
	echo("<BODY onLoad='parent.close()'>");
}

function my_flush() {
	print str_repeat(" ", 4096);	// force a flush
}

function resize_image($src, $dest, $target) {
	global $app;				

	$err = exec_wrapper(toPnmCmd($src) .
		     "| $app->pnmDir/pnmscale -xysize $target $target".
		     "| " . fromPnmCmd($dest));

	if (file_exists("$dest") && filesize("$dest") > 0) {
		return 1;
	} else {
		return 0;
	}
}

function valid_image($file) {
	global $app;
	
	exec(toPnmCmd($file) . "| $app->pnmDir/pnmfile",
	     $results,
	     $status);

	if ($status == 0) {
		return 1;
	} else {
		return 0;
	}
}

function toPnmCmd($file) {
	global $app;

	if (preg_match("/.png/i", $file)) {
		$cmd = "pngtopnm";
	} else if (preg_match("/.jpg/i", $file)) {
		$cmd = "jpegtopnm";
	} else if (preg_match("/.gif/i", $file)) {
		$cmd = "giftopnm";
	}

	if ($cmd) {
		return "$app->pnmDir/$cmd $file";
	} else {
		error("Unknown file type: $file");
		return "";
	}
}

function fromPnmCmd($file) {
	global $app;

	if (preg_match("/.png/i", $file)) {
		$cmd = "pnmtopng";
	} else if (preg_match("/.jpg/i", $file)) {
		$cmd = "ppmtojpeg";
	} else if (preg_match("/.gif/i", $file)) {
		$cmd = "ppmquant 256| $app->pnmDir/ppmtogif";
	}

	if ($cmd) {
		return "$app->pnmDir/$cmd > $file";
	} else {
		error("Unknown file type: $file");
		return "";
	}
}

function exec_wrapper($cmd) {
	global $app;

	$debug = 0;
	if ($debug) {
		print "<p><b> About to exec [$cmd]</b>";
	}

	exec($cmd, $results, $status);

	if ($debug) {
		print "<br> Results: <pre>" . join("\n", $results);
		print "<br> Status: $status";
	}

	if ($status == 0) {
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
        global $app, $gallery, $album, $user;
	$fullname = "html_wrap/$name";

	if (file_exists($fullname)) {
		include ($fullname);
	} else {
		include ("$fullname.default");
	}

	return 1;
}

function getGalleryStyleSheetName() {
	global $app;
        $sheetname = "css/gallery_style.css";

	if ($app) {
		$base = $app->photoAlbumURL;
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
	global $userDB;
	$nobody = $userDB->getNobody();

	if (count($array) > 1) {
		unset($array[$nobody->getUid()]);
	}

	if (count($array) == 0) {
		$array[$nobody->getUid()] = $nobody->getUsername();
	}
}

function correctEverybody($array) {
	global $userDB;
	$everybody = $userDB->getEverybody();

	if ($array[$everybody->getUid()]) {
		$array = array($everybody->getUid() => $everybody->getUsername());
	}
}

function makeGalleryUrl($albumName, $photoId="", $extra="") {
	global $app;

	$url = "$app->photoAlbumURL";

	$args = array();
	if ($app->feature["rewrite"]) {
		if ($albumName) {
			$url .= "/$albumName";

			// Can't have photo without album
			if ($photoId) {
				$url .= "/$photoId";
			} 
		}
	} else {
		if ($albumName) {
			$url = "$app->photoAlbumURL/view_album.php";
			array_push($args, "set_albumName=$albumName");
		}

		if ($photoId) {
			$url = "$app->photoAlbumURL/view_photo.php";
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

function galleryInit() {
	global $GALLERY_INIT;
	$GALLERY_INIT = 1;
}

function gallerySanityCheck() {
	global $GALLERY_INIT, $app, $gallery;

	if (!file_exists("config.php") || !$app) {
		include("errors/unconfigured.php");
		exit;
	}

	if (file_exists("setup") && is_readable("setup")) {
		header("Location: setup/index.php");
		exit;
	}

	if ($app->config_version != $gallery->config_version) {
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
