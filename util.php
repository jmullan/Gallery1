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
 */
?>
<?php
// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
	print "Security violation\n";
	exit;
}
?>
<?php

function editField($album, $field) {
	global $gallery;

	$buf = $album->fields[$field];
	if (!strcmp($buf, "")) {
		$buf = "<i>&lt;Empty&gt;</i>";
	}
	if ($gallery->user->canChangeTextOfAlbum($album)) {
		$url = "edit_field.php?set_albumName={$album->fields['name']}&field=$field";
		$buf .= "<span class=editlink>";
		$buf .= popup_link( "[edit $field]", $url) ;
		$buf .= "</span>";
	}
	return $buf;
}

function editCaption($album, $index) {
	global $gallery;

	$buf = $album->getCaption($index);
	if ($gallery->user->canChangeTextOfAlbum($album) 
		&& !$gallery->session->offline) {
		if (!strcmp($buf, "")) {
			$buf = "<i>&lt;No Caption&gt;</i>";
		}
		$url = "edit_caption.php?set_albumName={$album->fields['name']}&index=$index";
		$buf .= "<span class=editlink>";
		$buf .= popup_link("[edit]", $url);
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
        $url = "add_comment.php?set_albumName={$gallery->album->fields['name']}&index=$index";
        $buf = "<span class=editlink>";
        $buf .= popup_link('[add comment]', $url, 0);
        $buf .= "</span>";
        echo "<tr align=center><td colspan=3>$buf<br><br></td></tr>";
}

function center($message) {
	return "<center>$message</center>";
}

function gallery_error($message) {
	echo error_format($message);
}

function error_format($message) {
	return "<span class=error>Error: $message</span>";
}

function build_popup_url($url, $url_is_complete=0) {

	/* Separate the target from the arguments */
	list($target, $arglist) = explode('?', $url);

	/* Parse the query string arguments */
	parse_str($arglist, $args);
	
	if (!$url_is_complete) {
		$url = makeGalleryUrl($target, $args);
		$url = "'$url'";
	}

	return $url;
}

function popup($url, $url_is_complete=0) {

        $url = build_popup_url($url, $url_is_complete);
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

function popup_link($title, $url, $url_is_complete=0, $online_only=true) {
    static $popup_counter = 0;
    global $gallery;

    if ( $gallery->session->offline && $online_only ) {
	return;
    }

    $popup_counter++;

    $link_name = "popuplink_".$popup_counter;
    $url = build_popup_url($url, $url_is_complete);
    
    $a1 = "<a id=\"$link_name\" target=\"Edit\" href=$url onClick=\"".
	popup_js("document.getElementById('$link_name').href", "Edit",
		 "height=500,width=500,location=no,scrollbars=yes,menubars=no,toolbars=no,resizable=yes").
	"\">";
    
    return "$a1<nobr>$title</nobr></a> ";
}

function exec_internal($cmd) {
	global $gallery;

	$debugfile = "";
	if (isDebugging()) {
		print "<p><b>Executing:<ul>$cmd</ul></b>";
		$debugfile = tempnam($gallery->app->tmpDir, "dbg");
	}

	fs_exec($cmd, $results, $status, $debugfile);

	if (isDebugging()) {
		print "<br> Results: <pre>";
		if ($results) {
			print join("\n", $results);
		} else {
			print "<b>none</b>";
		}
		print "</pre>";

		if (file_exists($debugfile)) {
			print "<br> Error messages: <pre>";
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
		print "<br> Status: $status (expected " . $gallery->app->expectedExecStatus . ")";
	}

	return array($results, $status);
}

function getDimensions($file) {
	global $gallery;				

	$regs = getimagesize($file);
	if (($regs[0] > 1) && ($regs[1] > 1))
		return array($regs[0], $regs[1]);
	else if (isDebugging())
		echo "<br>PHP's getimagesize() unable to determine dimensions.<br>";
		

	/* Just in case php can't determine dimensions. */
	switch($gallery->app->graphics)
	{
	case "NetPBM":
		list($lines, $status) =
			exec_internal(toPnmCmd($file) .
				" | " .
				NetPBM("pnmfile", "--allimages"));
		break;
	case "ImageMagick":
		/* This fails under windows, IM isn't returning parsable status output. */
		list($lines, $status) = 
			exec_internal(ImCmd("identify", fs_import_filename($file)));
		break;
	default:
		if (isDebugging())
			echo "<br>You have no graphics package configured for use!<br>";
		return array(0, 0);
		break;
	}

	if ($status == $gallery->app->expectedExecStatus) {
		foreach ($lines as $line) {
			switch($gallery->app->graphics)
			{
			case "NetPBM":
				if (ereg("([0-9]+) by ([0-9]+)", $line, $regs))
					return array($regs[1], $regs[2]);
				break;
			case "ImageMagick":
				if (ereg("([0-9]+)x([0-9]+)", $line, $regs))
					return array($regs[1], $regs[2]);
				break;
			}
		}
	}

	if (isDebugging())
		echo "<br>Unable to determine image dimensions!<br>";

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

function acceptableMovieList() {
    return array('avi', 'mpg', 'mpeg', 'wmv', 'mov', 'swf');
}

function acceptableImageList() {
    return array('jpg', 'jpeg', 'gif', 'png');
}

function acceptableFormatList() {
    return array_merge(acceptableImageList(), acceptableMovieList());
}

function isImage($tag) {
    return in_array($tag, acceptableImageList());
}

function isMovie($tag) {
    return in_array($tag, acceptableMovieList());
}

function getFile($fname) {
	$tmp = "";

	if (!fs_file_exists($fname) || broken_link($fname)) {
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

	/* Check for images smaller then target size, don't blow them up. */
    $regs = getDimensions($src);
	if ($regs[0] <= $target && $regs[1] <= $target) {
		if ($useTemp == false) {
			fs_copy($src, $dest);
		}
		return 1;
    }

	switch($gallery->app->graphics)
	{
	case "NetPBM":
		$err = exec_wrapper(toPnmCmd($src) .
				" | " . 
				NetPBM("pnmscale",
					" -xysize $target $target") .
				" | " . fromPnmCmd($out));
		break;
	case "ImageMagick":
		$src = fs_import_filename($src);
		$out = fs_import_filename($out);
		$err = exec_wrapper(ImCmd("convert", "-quality ".
			$gallery->app->jpegImageQuality . 
			" -size ". $target ."x". $target ." $src".
			" -geometry ". $target ."x" . $target .
			" +profile '\*' $out"));
		break;
	default:
		if (isDebugging())
			echo "<br>You have no graphics package configured for use!<br>";
		return 0;
		break;
	}

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

	switch($gallery->app->graphics)
	{
	case "NetPBM":
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

		// copy exif headers from original image to rotated image
		if (isset($gallery->app->use_exif)) {
			$path = $gallery->app->use_exif;
			exec_internal(fs_import_filename($path, 1) . " -te $src $out");
		}
		break;
	case "ImageMagick":
	        if (!strcmp($target, "90")) {
		    $target = "-90";
		} else if (!strcmp($target, "-90")) {
		    $target = "90";
		}
	  
		$src = fs_import_filename($src);
		$out = fs_import_filename($out);
		$err = exec_wrapper(ImCmd("convert", "-rotate $target $src $out"));
		break;
	default:
		if (isDebugging())
			echo "<br>You have no graphics package configured for use!<br>";
		return 0;
		break;
	}

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

	switch($gallery->app->graphics)
	{
	case "NetPBM":
		$err = exec_wrapper(toPnmCmd($src) .
				" | " .
				NetPBM("pnmcut") .
				" $x $y $width $height" .
				" | " .
				fromPnmCmd($out));
		break;
	case "ImageMagick":
		$src = fs_import_filename($src);
		$out = fs_import_filename($out);
		$err = exec_wrapper(ImCmd("convert", "-crop " .
				$width ."x". $height ."+". $x ."+". $y .
				" $src $out"));
		break;
	default:
		if (isDebugging())
			echo "<br>You have no graphics package configured for use!<br>";
		return 0;
		break;
	}

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
    if (($type = getimagesize($file)) == FALSE)
        return 0;

    switch($type[2])
    {
    case 1: // GIF
    case 2: // JPEG
    case 3: // PNG
        return 1;
        break;
    default:
        return 0;
        break;
    }

	if (isDebugging())
		echo "<br>There was an unknown failure in the valid_image() call!<br>";
    return 0;
}

function toPnmCmd($file) {
	global $gallery;

	if (eregi("\.png\$", $file)) {
		$cmd = "pngtopnm";
	} else if (eregi("\.jpe?g\$", $file)) {
		if (isDebugging()) {
			$cmd = "jpegtopnm";
		} else {
			$cmd = "jpegtopnm";
		}
	} else if (eregi("\.gif\$", $file)) {
		$cmd = "giftopnm";
	}

	if ($cmd) {
		return NetPBM($cmd, $args) .
		 	" " .
			fs_import_filename($file);
	} else {
		gallery_error("Unknown file type: $file");
		return "";
	}
}

function fromPnmCmd($file) {
	global $gallery;

	if (eregi("\.png(\.tmp)?\$", $file)) {
		$cmd = NetPBM("pnmtopng");
	} else if (eregi("\.jpe?g(\.tmp)?\$", $file)) {
		$cmd = NetPBM($gallery->app->pnmtojpeg,
			      "--quality=" . $gallery->app->jpegImageQuality);
	} else if (eregi("\.gif(\.tmp)?\$", $file)) {
		$cmd = NetPBM("ppmquant", "256") . " | " . NetPBM("ppmtogif");
	}

	if ($cmd) {
		return "$cmd > " . fs_import_filename($file);
	} else {
		gallery_error("Unknown file type: $file");
		return "";
	}
}

function netPbm($cmd, $args="") {
	global $gallery;

	$cmd = fs_import_filename($gallery->app->pnmDir . "/$cmd");
	if (!isDebugging()) {
		$cmd  .= " --quiet";
	}
	$cmd .= " $args";
	return $cmd;
}

function ImCmd($cmd, $args = "") {
	global $gallery;

	$cmd = fs_import_filename($gallery->app->ImPath . "/$cmd");
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
			gallery_error(join("<br>", $results));
		}
		return 1;
	}
}
function includeHtmlWrap($name) {
	global $GALLERY_BASEDIR;

	// define these globals to make them available to custom text
        global $gallery;

	global $HTTP_SERVER_VARS;
	$domainname = $GALLERY_BASEDIR . "html_wrap/" . $HTTP_SERVER_VARS['HTTP_HOST'] . "/$name";
	if (fs_file_exists($domainname) && !broken_link($domainname)) {
	    include ($domainname);
	} else {
	    $fullname = $GALLERY_BASEDIR . "html_wrap/$name";
	    
	    if (fs_file_exists($fullname) && !broken_link($fullname)) {
		include ($fullname);
	    } else {
		include ("$fullname.default");
	    }
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
	global $GALLERY_BASEDIR;
	global $HTTP_SERVER_VARS;

        $sheetdomainname = "css/$HTTP_SERVER_VARS[HTTP_HOST]/$filename.css";
	$sheetdomainpath = "${GALLERY_BASEDIR}$sheetdomainname";

        $sheetname = "css/$filename.css";
	$sheetpath = "${GALLERY_BASEDIR}$sheetname";

	if ($gallery->app && $gallery->app->photoAlbumURL) {
		$base = $gallery->app->photoAlbumURL;
	} else {
		$base = ".";
	}

	if (fs_file_exists($sheetdomainpath) && !broken_link($sheetdomainpath)) {
		$url = "$base/$sheetdomainname";
	} else {
	    if (fs_file_exists($sheetpath) && !broken_link($sheetpath)) {
		$url = "$base/$sheetname";
	    } else {
		$url = "$base/$sheetname.default";
	    }
	}

	return '<link rel="stylesheet" type="text/css" href="' .
		$url .
		'">';
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

function drawSelect($name, $array, $selected, $size, $attrList=array()) {

	if (!empty($attrList)) {
	    	foreach ($attrList as $key => $value) {
			$attrs .= " $key=\"$value\"";
		}
	}

	$buf = "";
	$buf .= "<select name=\"$name\" size=$size $attrs>\n";
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

function correctPseudoUsers(&$array, $ownerUid) {
	global $gallery;

	/*
	 * If EVERYBODY is in the list, reduce it to just that entry.
	 */
	$everybody = $gallery->userDB->getEverybody();
	if ($array[$everybody->getUid()]) {
	        $array = array($everybody->getUid() => $everybody->getUsername());
		return;
	}

	/*
	 * If LOGGEDIN is in the list, reduce it to just that entry.
	 */
	$loggedIn = $gallery->userDB->getLoggedIn();
	if ($array[$loggedIn->getUid()]) {
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

	$attrs = '';
	foreach ($attrList as $key => $value) {
		$attrs .= " $key=\"$value\"";
	}

	$form = "<form action=\"$target\" $attrs>\n";

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

	if (!fs_file_exists($GALLERY_BASEDIR . "config.php") ||
                broken_link($GALLERY_BASEDIR . "config.php") ||
                !$gallery->app) {
		include($GALLERY_BASEDIR . "errors/unconfigured.php");
		exit;
	}

	if (fs_file_exists($GALLERY_BASEDIR . "setup") && 
                !broken_link($GALLERY_BASEDIR . "setup") &&
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
					gallery_error("Couldn't move $tempfile -> $dir/$file");
					fs_unlink($tempfile);
				}
			} else {
				gallery_error("Can't write to $tempfile");
			}
			chmod("$dir/$file", 0644);
		}
		fclose($fd);
	} else {
		gallery_error("Can't read $dir/$file");
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

	$numPhotos = $gallery->album->numPhotos(1);	
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

// The following 2 functions, printAlbumOptionList and printNestedVals provide
// a html options list for moving photos and albums around within gallery.  There
// were some defects in the original implimentation (I take full credit for the
// defects), and thus on 5/22/03, I rewrote the 2 functions to conform to the
// following requirements:
//
// For moving albums, there are 2 cases:
// 1. moving root albums:  the user should be able to move a
//    root album to any album to which they have write permissions
//    AND not to an album nested beneath it in the same tree
//    AND not to itself.
// 2. moving nested albums:  the user should be able to move a
//    nested album to any album to which they have write permissions
//    AND not to an album nested beneath it in the same tree
//    AND not to itself
//    AND not to its parent album.
//    The user should also be able to move it to the ROOT level
//    with appropriate permissions.
//
// For moving pictures, there is 1 case:
// 1. moving pictures:  the user should be able to move a picture
//    to any album to which they have write permissions
//    AND not to the album to which it already belongs.
//
// -jpk

function printAlbumOptionList($rootDisplay=1, $moveRootAlbum=0, $movePhoto=0) {
	global $gallery, $albumDB, $index;

	$uptodate=true;
	
	$mynumalbums = $albumDB->numAlbums($gallery->user);

	echo "<option value=0 selected> << Select Album >> </option>\n";

	// create a ROOT option for the user to move the 
	// album to the main display
	if ($gallery->user->canCreateAlbums() && $rootDisplay) {
		echo "<option value=ROOT>Top Level</option>";
	}

	// display all albums that the user can move album to
	for ($i=1; $i<=$mynumalbums; $i++) {

		$myAlbum = $albumDB->getAlbum($gallery->user, $i);
		$myAlbumName = $myAlbum->fields['name'];
		$myAlbumTitle = $myAlbum->fields['title'];

		if ($gallery->user->canWriteToAlbum($myAlbum)) {

			if ($myAlbum->versionOutOfDate()) {
				$uptodate=false;
				continue;
			}

			if ($myAlbum == $gallery->album) {
				// Don't allow the user to move to the current location with
				// value=0, but notify them that this is the current location
				echo "<option value=0>-- $myAlbumTitle (current location)</option>\n";
			} else {
				echo "<option value=\"$myAlbumName\">-- $myAlbumTitle</option>\n";
			}
		}

		if ( $moveRootAlbum && ($myAlbum == $gallery->album) && !$movePhoto )  {

			// do nothing -- we are moving a root album, and we don't
			// want to move it into its own album tree

		} elseif ( ($myAlbum == $gallery->album->getNestedAlbum($index)) && !$movePhoto )  {

			// do nothing -- we are moving an album, and we don't
			// want to move it into its own album tree

		} else {
			printNestedVals(1, $myAlbumName, $myAlbumTitle, $movePhoto);
		}
	}

	return $uptodate;
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
				$val2 = $val2 . $nestedAlbum->fields['title'];
				
				if ($nestedAlbum == $gallery->album) {
					// don't allow user to move to here (value=0), but
					// notify them that this is their current location
					echo "<option value=0> $val2 (current location)</option>\n";
				} elseif ($nestedAlbum == $gallery->album->getNestedAlbum($index)) {
					echo "<option value=0> $val2 (self)</option>\n";
				} else {
					echo "<option value=\"$myName\"> $val2</option>\n";
				}
			}

			if ( ($nestedAlbum == $gallery->album->getNestedAlbum($index)) && !$movePhoto ) {

				// do nothing -- don't allow album move into its own tree

			} else {
				printNestedVals($level + 1, $myName, $val2, $movePhoto);
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

	$myExif = array();
	if ($status == 0) {
	        while (list($key,$value) = each ($return)) {
		    if (trim($value)) {
			$explodeReturn = explode(':', $value, 2);
			if ($myExif[trim($explodeReturn[0])]) { 
			    $myExif[trim($explodeReturn[0])] .= "<br>" . 
				    trim($explodeReturn[1]);
			} else {
			    $myExif[trim($explodeReturn[0])] = 
				    trim($explodeReturn[1]);
			}
		    }
	        }
	}

        return array($status, $myExif);
}

function getItemCaptureDate($file) {
	global $gallery;

	$success = 0;
	if ($gallery->app->use_exif) {
		$return = getExif($file);
		$exifData = $return[1];
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

			$itemCaptureDate['hours'] = $hours;
			$itemCaptureDate['minutes'] = $minutes;
			$itemCaptureDate['seconds'] = $seconds;
			$itemCaptureDate['mday'] = $mday;
			$itemCaptureDate['mon'] = $mon;
			$itemCaptureDate['year'] = $year;
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
		print "IN UTIL ITEMCAPTUREDATE = ${itemCaptureDate['year']}<br>";
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
	if (!strncmp($HTTP_GET_VARS[$name], 'false', 5)) {
	    return false;
	} else {
	    return($HTTP_GET_VARS[$name]);
	}
    }

    if (!empty($HTTP_POST_VARS[$name])) {
	if (!strncmp($HTTP_POST_VARS[$name], 'false', 5)) {
	    return false;
	} else {
	    return($HTTP_POST_VARS[$name]);
	}
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
	global $gallery;

	if (!strcmp($gallery->app->use_flock, "yes")) {
		/* Acquire an advisory lock */
		$lockfd = fs_fopen("$file.lock", "a+");
		if (!$lockfd) {
			gallery_error("Could not open lock file ($file.lock)!");
			return 0;
		}
		if (!flock($lockfd, LOCK_EX)) {
			gallery_error("Could not acquire lock ($file.lock)!");
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

	if ($fd = fs_fopen($tmpfile, "w")) {
		fwrite($fd, serialize($obj));
		fclose($fd);

		if (fs_filesize($tmpfile) == 0) {
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

function removeTags($msg) {
    $msg = strip_tags($msg);
    return $msg;
}

function broken_link($file) {
    if (fs_is_link($file)) {
	return !fs_is_file($file);
    } else {
	return 0;
    }
}

function printChildren($albumName,$depth=0) {
	global $gallery;
	$printedHeader = 0;
	$myAlbum = new Album();
	$myAlbum->load($albumName);
	$numPhotos = $myAlbum->numPhotos(1);
	for ($i=1; $i <= $numPhotos; $i++) {
		$myName = $myAlbum->isAlbumName($i);
		if ($myName && !$myAlbum->isHidden($i)) {
		        $nestedAlbum = new Album();
			$nestedAlbum->load($myName);
			if ($gallery->user->canReadAlbum($nestedAlbum)) {
				$val2 = $nestedAlbum->fields['title'];
				if (!strcmp($nestedAlbum->fields['display_clicks'], 'yes')
					&& !$gallery->session->offline) {
				    $val3 = "(" . pluralize($nestedAlbum->getClicks(), "hit", "0") . ")";
				} else {
				    $val3 = "";
				}
				if ($depth==0 && !$printedHeader++) {
					echo "<strong>Sub-albums:</strong>";
				}
				echo "<div style=\"margin: 0px 0px 0px 20px\">";
				echo "<span class=fineprint>";
				echo "<a href=\"";
				echo makeAlbumUrl($myName);
				echo "\">$val2 $val3</a>\n";
				printChildren($myName,$depth+1);
				echo "</span>";
				echo "</div>";
			}
		}
	}
}

function mostRecentComment($album, $i)
{
        $id=$album->getPhotoId($i); 
        $index = $album->getPhotoIndex($id); 
        $recentcomment = $album->getComment($index, $album->numComments($i));
        return $recentcomment->getDatePosted();
}

function processNewImage($file, $tag, $name, $caption, $setCaption="") {
	global $gallery;
	global $temp_files;

	if (!strcmp($tag, "zip")) {
		if (!$gallery->app->feature["zip"]) {
			processingMsg("Skipping $name (ZIP support not enabled)");
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
				processNewImage($gallery->app->tmpDir . "/$pic", $tag, $pic, $caption, $setCaption);
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
		RewriteRule ^([^\.\?/]+)/([0-9]+)$	/~jpk/gallery/view_photo.php?set_albumName=$1&index=$2	[QSA]
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
				}
				
				/* Make sure we remove this file when we're done */
				$temp_files[$newFile]++;
			}
		    
			processingMsg("- Adding $name");
			if ($setCaption and $caption == "") {
				$caption = $originalFilename;
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
						processingMsg("- Resizing $name"); 
						$gallery->album->resizePhoto($index, $gallery->album->fields["resize_size"]);
					}
				}
				
				/* auto-rotate the photo if needed */
				if (!strcmp($gallery->app->autorotate, 'yes') && $gallery->app->use_exif) {
					$index = $gallery->album->numPhotos(1);
					$exifData = $gallery->album->getExif($index);
					if ($orientation = trim($exifData['Orientation'])) {
						$photo = $gallery->album->getPhoto($index);
						switch ($orientation) {
						case "rotate 90":
							$rotate = -90;
							break;
						case "rotate 180":
							$rotate = 180;
							break;
						case "rotate 270":
							$rotate = 90;
							break;
						default:
							$rotate = 0;
						}
						if ($rotate) {
							$gallery->album->rotatePhoto($index, $rotate);
							processingMsg("- Photo auto-rotated ${rotate}&deg;");
						}
					}
				}
			} else {
				processingMsg("<font color=red>Error: $err!</font>");
				processingMsg("<b>Need help?  Look in the " .
				    "<a href=http://gallery.sourceforge.net/faq.php target=_new>Gallery FAQ</a></b>");
			}
		} else {
			processingMsg("Skipping $name (can't handle '$tag' format)");
		}
	}
}

function processingMsg($buf) {
        global $msgcount;

        if ($msgcount) {
                print "<br>";
        }
        print $buf;
        my_flush();
        $msgcount++;
}

function createNewAlbum( $parentName, $newAlbumName="", $newAlbumTitle="", $newAlbumDesc="" ) {
        global $gallery;

        // get parent album name
        $albumDB = new AlbumDB(FALSE);

        // Make sure no album $newAlbumName exists
        if ($newAlbumName && $albumDB->getAlbumbyName($newAlbumName)) {
                $newAlbumName = null;
        }

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
                $gallery->album->fields['parentAlbumName'] = $parentName;
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
		$gallery->album->fields["extra_fields"]    = $parentAlbum->fields["extra_fields"];

                $returnVal = $gallery->album->save();
        } else {
                /*
                 * Get a new albumDB because our old copy is not up to
                 * date after we created a new album
                 */
                $albumDB = new AlbumDB(FALSE);

                /* move the album to the top if not a nested album*/
                $numAlbums = $albumDB->numAlbums($gallery->user);
                $albumDB->moveAlbum($gallery->user, $numAlbums, 1);
                $returnVal = $albumDB->save();
        }

        return $returnVal;
}
function stripQuotes($string)
{
	if (!$string)
		return $string;
	return ereg_replace('"', "&quot;", $string);
}
function restoreQuotes($string)
{
	if (!$string)
		return $string;
	return ereg_replace("&quot;", '"', $string);
}

?>
