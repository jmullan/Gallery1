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
?>
<?php
// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
	print _("Security violation") ."\n";
	exit;
}
require($GALLERY_BASEDIR."nls.php");

function editField($album, $field, $link=null) {
	global $gallery;
	$buf = "";
	if ($link) {
		$buf .= "<a href=\"$link\">";
	}
	$buf .= $album->fields[$field];
	if ($link) {
		$buf .= "</a>";
	}
	if ($gallery->user->canChangeTextOfAlbum($album)) {
		if (!strcmp($buf, "")) {
			$buf = "<i>&lt;". _("Empty") . "&gt;</i>";
		}
		$url = "edit_field.php?set_albumName={$album->fields['name']}&field=$field"; // should replace with &amp; for validatation
		$buf .= " <span class=editlink>";
		$buf .= popup_link( "[". sprintf(_("edit %s"), _($field)) . "]", $url) ;
		$buf .= "</span>";
	}
	return $buf;
}

function editCaption($album, $index) {
	global $gallery;

	$buf = nl2br($album->getCaption($index));
	$buf .= $album->getCaptionName($index);
	if (($gallery->user->canChangeTextOfAlbum($album) ||
               ($gallery->album->getItemOwnerModify() && 
	         $gallery->album->isItemOwner($gallery->user->getUid(), $index))) 
		&& !$gallery->session->offline) {
		if (!strcmp($buf, "")) {
			$buf = "<i>&lt;". _("No Caption") ."&gt;</i>";
		}
		$url = "edit_caption.php?set_albumName={$album->fields['name']}&index=$index";
		$buf .= "<span class=\"editlink\">";
		$buf .= popup_link("[". _("edit") ."]", $url);
		$buf .= "</span>";
	}
	return $buf;
}

function viewComments($index, $addComments) {
        global $gallery;
	global $GALLERY_BASEDIR;
	global $commentdraw;

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
		includeLayout('commentdraw.inc');
	}

	if ($addComments)
       	{
		$id = $gallery->album->getPhotoId($index);
	       	$url = "add_comment.php?set_albumName={$gallery->album->fields['name']}&id=$id";
	       	$buf = "<tr><td><span class=editlink>";
	       	$buf .= popup_link('[' . _("add comment") . ']', $url, 0);
	       	$buf .= "</span></td></tr>";
	       	echo $buf;
       	}
}

function center($message) {
	return "<center>$message</center>";
}

function gallery_error($message) {
	echo error_format($message);
}

function error_format($message) {
	return "<span class=\"error\">". _("Error:") . " $message</span>";
}

function build_popup_url($url, $url_is_complete=0) {

	/* Separate the target from the arguments */
	$result = explode('?', $url);
	$target = $result[0];
	if (isset($result[1])) {
		$arglist = $result[1];
	} else {
		$arglist = "";
	}

	/* Parse the query string arguments */
	parse_str($arglist, $args);
	
	if (!$url_is_complete) {
		$url = makeGalleryUrl($target, $args);
		$url = "'$url'";
	}

	return $url;
}

function popup($url, $url_is_complete=0, $height=500,$width=500) {

        $url = build_popup_url($url, $url_is_complete);
	return popup_js($url, "Edit", 
		"height=$height,width=$width,location=no,scrollbars=yes,menubars=no,toolbars=no,resizable=yes");
}

function popup_js($url, $window, $attrs) {
	return "nw=window.open($url,'$window','$attrs');nw.opener=self;return false;";
}

function popup_status($url, $height=150, $width=350) {
	$attrs = "height=$height,width=$width,location=no,scrollbars=no,menubars=no,toolbars=no,resizable=yes";
	return "open('" . makeGalleryUrl($url) . "','Status','$attrs');";
}

function popup_link($title, $url, $url_is_complete=0, $online_only=true, $height=500,$width=500) {
    static $popup_counter = 0;
    global $gallery;

    if ( !empty($gallery->session->offline) && $online_only ) {
	return;
    }

    $popup_counter++;

    $link_name = "popuplink_".$popup_counter;
    $url = build_popup_url($url, $url_is_complete);
    
    $a1 = "<a id=\"$link_name\" target=\"Edit\" href=$url onClick=\"javascript:".
	popup_js("document.getElementById('$link_name').href", "Edit",
		 "height=$height,width=$width,location=no,scrollbars=yes,menubars=no,toolbars=no,resizable=yes").
	"\">";
    
    return "$a1<span style=\"white-space:nowrap;\">$title</span></a>";
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
		print "<br>" . _("Results:") ." <pre>";
		if ($results) {
			print join("\n", $results);
		} else {
			print "<b>" ._("none") ."</b>";
		}
		print "</pre>";

		if (file_exists($debugfile)) {
			print "<br> ". _("Error messages:") .": <pre>";
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
		print "<br> ". sprintf(_("Status: %s (expected %s)"),
				$status, $gallery->app->expectedExecStatus);
	}

	return array($results, $status);
}

function getDimensions($file, $regs=false) {
	global $gallery;				

	if ($regs === false)
		$regs = getimagesize($file);
	if (($regs[0] > 1) && ($regs[1] > 1))
		return array($regs[0], $regs[1]);
	else if (isDebugging())
		echo "<br>" .sprintf(_("PHP's %s unable to determine dimensions."),
				"getimagesize()") ."<br>";
		

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
			echo "<br>" . _("You have no graphics package configured for use!") ."<br>";
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

/*
   $opts is now a name/value array, where $key is the value returned, and $name 
   is the value displayed (and translated).
 */
function selectOptions($album, $field, $opts) {
	foreach ($opts as $key => $value) {
		$sel = "";
		if (!strcmp($key, $album->fields[$field])) {
			$sel = "selected";
		}
		echo "\n<option value=\"$key\" $sel>" . _($value) ."</option>";
	}
}

function acceptableFormat($tag) {
	return (isImage($tag) || isMovie($tag));
}

function acceptableFormatRegexp() {
	return "(" . join("|", acceptableFormatList()) . ")";
}

function acceptableMovieList() {
    return array('avi', 'mpg', 'mpeg', 'wmv', 'mov', 'swf', 'mp4');
}

function acceptableImageList() {
    return array('jpg', 'jpeg', 'gif', 'png');
}

function acceptableFormatList() {
    return array_merge(acceptableImageList(), acceptableMovieList());
}

/* These are custom fields that are turned on and off at an album
 * level, and are populated for each photo automatically, without the
 * user typing values.  The $value of each pair should be translated
 * as appropriate in the ML version.
 */

function automaticFieldsList() {
	return array("Upload Date" => _("Upload Date"),
		"Capture Date" => _("Capture Date"),
		"Dimensions" => _("Image Size"),
		"EXIF" => _("Additional EXIF Data"));
}

function isImage($tag) {
    return in_array($tag, acceptableImageList());
}

function isMovie($tag) {
    return in_array($tag, acceptableMovieList());
}

function getFile($fname, $legacy=false) {
	$tmp = "";

	if (!fs_file_exists($fname) || broken_link($fname)) {
		return $tmp;
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

function dismissAndReload() {
	if (isDebugging()) {
		echo "<BODY onLoad='opener.location.reload();'>";
		echo("<center><b>" ._("Not closing this window because debug mode is on") ."</b></center>");
		echo("<hr>");
	} else {
		echo "<BODY onLoad='opener.location.reload(); parent.close()'>";
	}
}

function reload() {
	echo '<script language="javascript1.2" type="text/JavaScript">';
	echo 'opener.location.reload()';
	echo '</script>';
}

function dismissAndLoad($url) {
	if (isDebugging()) {
		echo("<BODY onLoad='opener.location = \"$url\"; '>");
		echo("<center><b>" . _("Not closing this window because debug mode is on") ."</b></center>");
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

function resize_image($src, $dest, $target=0, $target_fs=0, $keepProfiles=0) {
	global $gallery;				

	if (!strcmp($src,$dest)) {
		$useTemp = true;
		$out = "$dest.tmp";
	}
	else {
		$out = $dest;
		$useTemp = false;
	}

	$regs = getimagesize($src);
	if ($regs[2] !== 2 && $regs[2] !== 3) {
		$target_fs = 0; // can't compress other images
	}
	if ($target === 'off') {
	    $target = 0;
	}
		
	/* Check for images smaller then target size, don't blow them up. */
	$regs = getDimensions($src, $regs);
	if ((empty($target) || ($regs[0] <= $target && $regs[1] <= $target))
			&& (empty($target_fs) || ((int) fs_filesize($src) >> 10) <= $target_fs)) {
		if ($useTemp == false) {
			fs_copy($src, $dest);
		}
		processingMsg("No resizing required");
		return 1;
	}
	$target=min($target, max($regs[0],$regs[1]));

	if ($target_fs == 0) {
		compress_image($src, $out, $target, $gallery->app->jpegImageQuality, $keepProfiles);
	} else {
		$filesize = (int) fs_filesize($src) >> 10;
		$max_quality=$gallery->app->jpegImageQuality;
		$min_quality=5;
		$max_filesize=$filesize;
		$min_filesize=0;
		if (!isset($quality)) {
			$quality=$gallery->album->fields['last_quality'];
		}
		processingMsg(sprintf(_("target file size %d kbytes"), 
					$target_fs)."\n");

		do {
			compress_image($src, $out, $target, $quality, $keepProfiles);
			$prev_quality=$quality;
			printf(_("- file size %d kbytes"), round($filesize));
			processingMsg(sprintf(_("trying quality %d%%"), 
						$quality));
			clearstatcache();
			$filesize= (int) fs_filesize($out) >> 10;
			if ($filesize < $target_fs) {
				$min_quality=$quality;
				$min_filesize=$filesize;
			} else if ($filesize > $target_fs){
				$max_quality=$quality;
				$max_filesize=$filesize;
			} else if ($filesize == $target_fs){
				$min_quality=$quality;
				$max_quality=$quality;
				$min_filesize=$filesize;
				$max_filesize=$filesize;
			}
			$quality=($max_quality + $min_quality)/2;
			$quality=round($quality);
			if ($quality==$prev_quality) {
				if ($filesize==$max_filesize) {
					$quality--;
				} else {
					$quality++;
				}
			}
		} while ($max_quality-$min_quality > 2 && 
				abs(($filesize-$target_fs)/$target_fs) > .02 );

		print "<br>";
		$gallery->album->fields['last_quality']=$prev_quality;
		printf(_("- file size %d kbytes"), round($filesize));
		processingMsg(_("Done."));
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

function rotate_image($src, $dest, $target, $type) {
	global $gallery;

	if (!strcmp($src,$dest)) {
		$useTemp = true;
		$out = "$dest.tmp";
	}
	else {
		$out = $dest;
	}

        $outFile = fs_import_filename($out, 1);
        $srcFile = fs_import_filename($src, 1);

	$type = strtolower($type);
	if (isset($gallery->app->use_jpegtran) && !empty($gallery->app->use_jpegtran) && ($type === 'jpg' || $type === 'jpeg')) {
	    	if (!strcmp($target, '-90')) {
			$args = '-rotate 90';
		} else if (!strcmp($target, '180')){
			$args = '-rotate 180';
		} else if (!strcmp($target, '90')) {
			$args = '-rotate 270';
		} else if (!strcmp($target, 'fv')) {
			$args = '-flip vertical';
		} else if (!strcmp($target, 'fh')) {
			$args = '-flip horizontal';
		} else if (!strcmp($target, 'tr')) {
			$args = '-transpose';
		} else if (!strcmp($target, 'tv')) {
			$args = '-transverse';
		} else {
			$args = '';
		}

		$path = $gallery->app->use_jpegtran;
		// -copy all ensures all headers (i.e. EXIF) are copied to the rotated image
		exec_internal(fs_import_filename($path, 1) . " $args -copy all -outfile $outFile $srcFile");
	} else {
		switch($gallery->app->graphics)
		{
		case "NetPBM":
			$args2 = '';
			if (!strcmp($target, '-90')) {
				/* NetPBM's docs mix up CW and CCW...
				 * We'll do it right. */
				$args = '-r270';
			} else if (!strcmp($target, '180')) {
				$args = '-r180';
			} else if (!strcmp($target, '90')) {
				$args = '-r90';
			} else if (!strcmp($target, 'fv')) {
				$args = '-tb';
			} else if (!strcmp($target, 'fh')) {
				$args = '-lr';
			} else if (!strcmp($target, 'tr')) {
				$args = '-xy';
			} else if (!strcmp($target, 'tv')) {
				/* Because of NetPBM inconsistencies, the only
				 * way to do this transformation on *all* 
				 * versions of NetPBM is to pipe two separate
				 * operations in sequence. Versions >= 10.13
				 * have the new -xform flag, and versions <=
				 * 10.6 could take the '-xy -r180' commands in
				 * sequence, but versions 10.7--> 10.12 can't
				 * do *either*, so we're left with this little
				 * workaround. -Beckett 9/9/2003 */
			    $args = '-xy';
			    $args2 = ' | ' . NetPBM('pnmflip', '-r180');
			} else {
				$args = '';
			}		

			$err = exec_wrapper(toPnmCmd($src) . ' | ' .
					    NetPBM('pnmflip', $args) .
					    $args2 .
					    ' | ' . fromPnmCmd($out));	

			// copy exif headers from original image to rotated image	
			if (isset($gallery->app->use_exif)) {
				$path = $gallery->app->use_exif;
				exec_internal(fs_import_filename($path, 1) . " -te $srcFile $outFile");
			}
			break;
		case "ImageMagick":
		        if (!strcmp($target, '-90')) {
			    $im_cmd = '-rotate 90';             
			} else if (!strcmp($target, '180')) {
			    $im_cmd = '-rotate 180';
			} else if (!strcmp($target, '90')) {
			    $im_cmd = '-rotate -90';
			} else if (!strcmp($target, 'fv')) {
			    $im_cmd = '-flip';
			} else if (!strcmp($target, 'fh')) {
			    $im_cmd = '-flop';
			} else if (!strcmp($target, 'tr')) {
			    $im_cmd = '-affine 0,1,1,0,0,0 -transform';
			} else if (!strcmp($target, 'tv')) {
			    $im_cmd = '-affine 0,-1,-1,0,0,0 -transform';
			} else {
			    $im_cmd = '';
			}
			
		  	
			$src = fs_import_filename($src);
			$out = fs_import_filename($out);
			$err = exec_wrapper(ImCmd('convert', "$im_cmd $srcFile $outFile"));
			break;
		default:
			if (isDebugging())
				echo "<br>". _("You have no graphics package configured for use!") ."<br>";
			return 0;
			break;
		}	
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
			echo "<br>" . _("You have no graphics package configured for use!") ."<br>";
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
	if (($type = getimagesize($file)) == FALSE) {
		if (isDebugging()) {
			echo "<br>". sprintf(_("Call to %s failed in %s for file %s!"), 'getimagesize()', 'valid_image()', $file) ."<br>";
		}
		return 0;
	}

	if (isDebugging()) {
		echo "<br>". sprintf(_("File %s type %d."), $file, $type[2]) ."<br>";
	}
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
		echo "<br>". sprintf(_("There was an unknown failure in the %s call!"), 'valid_image()') ."<br>";
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

	if (!empty($cmd)) {
		return NetPBM($cmd) .
		 	" " .
			fs_import_filename($file);
	} else {
		gallery_error(sprintf(_("Unknown file type: %s"), $file));
		return "";
	}
}

function fromPnmCmd($file, $quality=NULL) {
	global $gallery;
	if ($quality == NULL) {
		$quality=$gallery->app->jpegImageQuality;
	}

	if (eregi("\.png(\.tmp)?\$", $file)) {
		$cmd = NetPBM("pnmtopng");
	} else if (eregi("\.jpe?g(\.tmp)?\$", $file)) {
		$cmd = NetPBM($gallery->app->pnmtojpeg,
			      "--quality=" . $quality);
	} else if (eregi("\.gif(\.tmp)?\$", $file)) {
		$cmd = NetPBM("ppmquant", "256") . " | " . NetPBM("ppmtogif");
	}

	if ($cmd) {
		return "$cmd > " . fs_import_filename($file);
	} else {
		gallery_error(sprintf(_("Unknown file type: %s"), $file));
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

function getImagePath($name, $skinname='') {
	global $gallery;

	if (!$skinname) {
                $skinname = $gallery->app->skinname;
        }

	$defaultname = $gallery->app->photoAlbumURL . "/skins/default/images/$name";
	$fullname = $gallery->app->photoAlbumURL . "/skins/$skinname/images/$name";

	if (fs_file_exists($fullname) && !broken_link($fullname)) {
		return "$fullname";
	} else {
		return "$defaultname";
	}
}

function includeLayout($name, $skinname='') {
	global $GALLERY_BASEDIR;
	global $HTTP_SERVER_VARS;
	global $gallery;

	if (!$skinname) {
                $skinname = $gallery->app->skinname;
        }

	$defaultname = $GALLERY_BASEDIR . "layout/$name";
	$fullname = $GALLERY_BASEDIR . "skins/$skinname/layout/$name";

	if (fs_file_exists($fullname) && !broken_link($fullname)) {
		include ($fullname);
	} else {
		include ($defaultname);
	}
}

function includeHtmlWrap($name, $skinname='') {
	global $GALLERY_BASEDIR;

	// define these globals to make them available to custom text
        global $gallery;

	global $HTTP_SERVER_VARS;
	$domainname = $GALLERY_BASEDIR . "html_wrap/" . $HTTP_SERVER_VARS['HTTP_HOST'] . "/$name";

	if (!$skinname) {
		$skinname = $gallery->app->skinname;
	}

	if (fs_file_exists($domainname) && !broken_link($domainname)) {
	    include ($domainname);
	} else {
	    $defaultname = $GALLERY_BASEDIR . "html_wrap/$name";
	    $fullname = $GALLERY_BASEDIR . "skins/$skinname/html_wrap/$name";
	    
	    if (fs_file_exists($fullname) && !broken_link($fullname)) {
		include ($fullname);
	    } elseif (fs_file_exists($defaultname) && !broken_link($defaultname)) {
		include ($defaultname);
	    } else {
		include ("$defaultname.default");
	    }
	}

	return 1;
}

function getStyleSheetLink() {
	global $GALLERY_EMBEDDED_INSIDE;
	global $GALLERY_OK;

	if (isset($GALLERY_OK)) {
		if ($GALLERY_OK == false) {
			return _getStyleSheetLink("config");
		}
	}
	if ($GALLERY_EMBEDDED_INSIDE) {
		return _getStyleSheetLink("embedded_style");
	} else {
		return _getStyleSheetLink("embedded_style") . 
			"\n" .
		       _getStyleSheetLink("standalone_style");
	}
}

function _getStyleSheetLink($filename, $skinname='') {
	global $gallery;
	global $GALLERY_BASEDIR;
	global $HTTP_SERVER_VARS;

        $sheetdomainname = "css/$HTTP_SERVER_VARS[HTTP_HOST]/$filename.css";
	$sheetdomainpath = "${GALLERY_BASEDIR}$sheetdomainname";

	if (!$skinname) {
		if (isset($gallery->app) && isset($gallery->app->skinname)) {
			$skinname = $gallery->app->skinname;
		} else {
			$skinname = "default";
		}
	}

        $sheetname = "skins/$skinname/css/$filename.css";
	$sheetpath = "${GALLERY_BASEDIR}$sheetname";
	$sheetdefaultname = "skins/default/css/$filename.css";
	$sheetdefaultpath = "${GALLERY_BASEDIR}$sheetname";

	if (isset($gallery->app) && isset($gallery->app->photoAlbumURL)) {
		$base = $gallery->app->photoAlbumURL;
	} elseif (stristr($HTTP_SERVER_VARS['REQUEST_URI'],"setup")) {
		$base = "..";
	} else {
		$base = ".";
	}

	if (fs_file_exists($sheetdomainpath) && !broken_link($sheetdomainpath)) {
		$url = "$base/$sheetdomainname";
	} else {
	    if (fs_file_exists($sheetpath) && !broken_link($sheetpath)) {
		$url = "$base/$sheetname";
	    } elseif (fs_file_exists($sheetdefaultpath) && !broken_line($sheetdefaultpath)) {
		$url = "$base/$sheetdefaultpath";
	    } else {
		$url = "$base/$sheetdefaultname";
	    }
	}

	return '<link rel="stylesheet" type="text/css" href="' .
		$url .
		'">';
}

function pluralize_n($amt, $one, $more, $none) {
        switch ($amt) {
                case 0 :
                        return $none;
                        break;
                case 1 :
			return $one;
                        break;

                default :
                        return "$amt $more";
                        break;
        }
}

function errorRow($key) {
	global $gErrors, $GALLERY_BASEDIR;

	if (isset($gErrors[$key])) {
		$error = $gErrors[$key];
	} else {
		$error = NULL;
	}
	if ($error) {	
		include($GALLERY_BASEDIR . "html/errorRow.inc");
	}
}

function drawSelect($name, $array, $selected, $size, $attrList=array()) {
	$attrs = "";
	if (!empty($attrList)) {
	    	foreach ($attrList as $key => $value) {
			if ($value == NULL) {
				$attrs .= " $key";
			}
			else {
				$attrs .= " $key=\"$value\"";
			}
		}
	}

	$buf = "";
	$buf .= "<select name=\"$name\" size=$size $attrs>\n";
	foreach ($array as $uid => $username) {
		$sel = "";
		if (is_array($selected)) {
			if (in_array($uid, $selected)) {
				$sel = "selected";
			}
		} 
		else if (!strcmp($uid, $selected)) {
			$sel = "selected";
		} 
		$buf .= "<option value=$uid $sel>". $username ."</option>\n";
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
	$result = split("\?", $url);
	$target = $result[0];
	if (sizeof($result) > 1) {
		$tmp = $result[1];
	} else {
		$tmp = "";
	}

	$attrs = '';
	foreach ($attrList as $key => $value) {
		$attrs .= " $key=\"$value\"";
	}

	$form = "<form action=\"$target\" $attrs>\n";

	$args = split("&", $tmp);
	foreach ($args as $arg) {
		if (strlen($arg) == 0) {
			continue;
		}
		list($key, $val) = split("=", $arg);
		$form .= "<input type=\"hidden\" name=\"$key\" value=\"$val\">\n";
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

	if(stristr($GALLERY_EMBEDDED_INSIDE,"nuke")) {
			$args["op"] = "modload";
			$args["name"] = "$GALLERY_MODULENAME";
			$args["file"] = "index";

			/*
			 * include *must* be last so that the JavaScript code in 
			 * view_album.php can append a filename to the resulting URL.
			 */
			$args["include"] = $target;
			$target = "modules.php";
	} else {
			$target = $gallery->app->photoAlbumURL . "/" . $target;
	}

	$url = $target;
	if ($args) {
		$i = 0;
		foreach ($args as $key => $value) {
			if ($i++) {
				$url .= "&";  // should replace with &amp; for validatation
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
			$target = urlencode ($albumName);

			// Can't have photo without album
			if ($photoId) {
				$target .= "/".urlencode ($photoId);
			} 
		} else {
			$target = "albums.php";
		}
	} else {
		if ($albumName) {
			$args["set_albumName"] = urlencode ($albumName);
			if ($photoId) {
				$target = "view_photo.php";
				$args["id"] = urlencode ($photoId);
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
	global $GALLERY_OK;

	if (!fs_file_exists($GALLERY_BASEDIR . "config.php") ||
                broken_link($GALLERY_BASEDIR . "config.php") ||
                !$gallery->app) {
		$GALLERY_OK=false;
		return "unconfigured.php";
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
		if (strstr($perms, "755") ||
			( getOS()== OS_WINDOWS && ! fs_file_exists($GALLERY_BASEDIR. "setup/SECURE"))) {
			$GALLERY_OK=false;
			return "configmode.php";
		}
	}

	if ($gallery->app->config_version != $gallery->config_version) {
		$GALLERY_OK=false;
		return "reconfigure.php";
	}
	$GALLERY_OK=true;
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
					gallery_error("Couldn't move $tempfile -> $dir/$file");
					fs_unlink($tempfile);
				}
			} else {
				gallery_error(sprintf(_("Can't write to %s."),
							$tempfile));
			}
			chmod("$dir/$file", 0644);
		}
		fclose($fd);
	} else {
		gallery_error(sprintf(_("Can't read %s."), "$dir/$file"));
	}

	return 1;
}

function isDebugging() {
	global $gallery;
	return !strcmp($gallery->app->debug, "yes");
}

function addUrlArg($url, $arg) {
	if (strchr($url, "?")) {
		return "$url&$arg"; // should replace with &amp; for validatation
	} else {
		return "$url?$arg";
	}
}


function getNextPhoto($idx, $album=NULL) {
	global $gallery;
	if (!$album) {
		$album=$gallery->album;
	}

	$numPhotos = $album->numPhotos(1);	
	$idx++;
	if ($gallery->user->canWriteToAlbum($album)) {
		// even though a user can write to an album, they may
		// not have read authority over a specific nested album.
		if ($idx <= $numPhotos && $album->isAlbumName($idx)) {
			$myAlbumName = $album->isAlbumName($idx);
			$myAlbum = new Album();
			$myAlbum->load($myAlbumName);
			if (!$gallery->user->canReadAlbum($myAlbum)) {
				$idx = getNextPhoto($idx, $album);
			}
		}
		return $idx;
	}

	while ($idx <= $numPhotos && $album->isHidden($idx)) {
		$idx++;
	}

	if ($idx <= $numPhotos && $album->isAlbumName($idx)) {
		// do not display a nexted album if the user doesn't
		// have permission to view it.
		if ($album->isAlbumName($idx)) {
			$myAlbumName = $album->isAlbumName($idx);
			$myAlbum = new Album();
			$myAlbum->load($myAlbumName);
			if (!$gallery->user->canReadAlbum($myAlbum)) {
				$idx = getNextPhoto($idx, $album);
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

function printAlbumOptionList($rootDisplay=1, $moveRootAlbum=0, $movePhoto=0, $readOnly=false) {
	global $gallery, $albumDB, $index;

	$uptodate=true;
	
	$mynumalbums = $albumDB->numAlbums($gallery->user);

	if (!$readOnly) {
		echo "<option value=0 selected> << ". _("Select Album") ." >> </option>\n";
	}

	// create a ROOT option for the user to move the 
	// album to the main display
	if ($gallery->user->canCreateAlbums() && $rootDisplay && !$readOnly) {
		echo "<option value=ROOT>". _("Top Level") ."</option>";
	}

	// display all albums that the user can move album to
	for ($i=1; $i<=$mynumalbums; $i++) {

		$myAlbum = $albumDB->getAlbum($gallery->user, $i);
		$myAlbumName = $myAlbum->fields['name'];
		$myAlbumTitle = $myAlbum->fields['title'];

		if ($gallery->user->canWriteToAlbum($myAlbum) || 
		   ($readOnly && $gallery->user->canReadAlbum($myAlbum))) {

			if ($myAlbum->versionOutOfDate()) {
				$uptodate=false;
				continue;
			}

			if (!$readOnly && ($myAlbum == $gallery->album)) {
				// Don't allow the user to move to the current location with
				// value=0, but notify them that this is the current location
				echo "<option value=\"$myAlbumName\">-- $myAlbumTitle (". _("current location"). ")</option>\n";
			} else {
				if (sizeof($gallery->album->fields["votes"]) && $gallery->album->pollsCompatible($myAlbum)) {
					$myAlbumTitle .= " *";
				}
			       	echo "<option value=\"$myAlbumName\">-- $myAlbumTitle</option>\n";
			}
		}

		if ( !$readOnly && $moveRootAlbum && ($myAlbum == $gallery->album) && !$movePhoto )  {

			// do nothing -- we are moving a root album, and we don't
			// want to move it into its own album tree

		} elseif ( !$readOnly && ($myAlbum == $gallery->album->getNestedAlbum($index)) && !$movePhoto )  {

			// do nothing -- we are moving an album, and we don't
			// want to move it into its own album tree

		} else {
			printNestedVals(1, $myAlbumName, $movePhoto, $readOnly);
		}
	}

	return $uptodate;
}


function printNestedVals($level, $albumName, $movePhoto, $readOnly) {
	global $gallery, $index;
	
	$myAlbum = new Album();
	$myAlbum->load($albumName);
	
	$numPhotos = $myAlbum->numPhotos(1);

	for ($i=1; $i <= $numPhotos; $i++) {
		$myName = $myAlbum->isAlbumName($i);
		if ($myName) {
			$nestedAlbum = new Album();
			$nestedAlbum->load($myName);
			if ($gallery->user->canWriteToAlbum($nestedAlbum) ||
			    ($readOnly && $gallery->user->canReadAlbum($myAlbum))) {

				$val2 = str_repeat("-- ", $level+1);
				$val2 = $val2 . $nestedAlbum->fields['title'];
				
				if (!$readOnly && ($nestedAlbum == $gallery->album)) {
					// don't allow user to move to here (value=0), but
					// notify them that this is their current location
					echo "<option value=0> $val2 (". _("current location") .")</option>\n";
				} elseif (!$readOnly && $nestedAlbum == $gallery->album->getNestedAlbum($index)) {
					echo "<option value=0> $val2 (". _("self"). ")</option>\n";
				} else {
					echo "<option value=\"$myName\"> $val2</option>\n";
				}
			}

			if ( !$readOnly && ($nestedAlbum == $gallery->album->getNestedAlbum($index)) && !$movePhoto ) {

				// do nothing -- don't allow album move into its own tree

			} else {
				printNestedVals($level + 1, $myName, $movePhoto, $readOnly);
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
			if (isset($myExif[trim($explodeReturn[0])])) { 
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
		if (isset($exifData["Date/Time"])) {
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

	return !isset($HTTP_GET_VARS[$name]) && !isset($HTTP_POST_VARS[$name]);
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
			gallery_error(sprintf(_("Could not open lock file (%s)!"),
						"$file.lock"));
			return 0;
		}
		if (!flock($lockfd, LOCK_EX)) {
			gallery_error(sprintf(_("Could not acquire lock (%s)!"),
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
				    $val3 = "(" . pluralize_n($nestedAlbum->getClicks(), _("1 hit"), _("hits"), _("0 hits")) . ")";
				} else {
				    $val3 = "";
				}
				if ($depth==0 && !$printedHeader++) {
					echo "<strong>". _("Sub-albums") .":</strong>";
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

/* this function left in place to support patches that use it, but please use
   lastCommentDate functions in classes Album and AlbumItem.
 */
function mostRecentComment($album, $i)
{
        $id=$album->getPhotoId($i); 
        $index = $album->getPhotoIndex($id); 
        $recentcomment = $album->getComment($index, $album->numComments($i));
        return $recentcomment->getDatePosted();
}


/*
 * expects as input an array where the keys
 * are string labels and the values are
 * numbers.  Values must be non-negative
 * returns an HTML bar graph as a string
 * assumes bar.gif, located in images/
 * modified from example in PHP Bible
 */
function arrayToBarGraph ($array, $max_width, $table_values="CELLPADDING=5", 
	$col_1_head=null, $col_2_head=null) 
{
	foreach ($array as $value) 
	{
		if ((IsSet($max_value) && ($value > $max_value)) ||
				(!IsSet($max_value))) 
		{
			$max_value = $value;
		}
	}
	if (!isSet($max_value))
	{
		// no results!
		return null;
	}
	$string_to_return = "<TABLE $table_values>";
	if ($col_1_head || $col_2_head)
	{
		$string_to_return.="<tr><td></td><td><span class=\"admin\">$col_1_head</span></td><td><span class=\"admin\">$col_2_head</span></td></tr>";
	}
	if ($max_value > 0)
	{
		$pixels_per_value = ((double) $max_width)
			/ $max_value;
	}
	else 
	{
		$pixels_per_value = 0;
	}
	$counter = 0;
	foreach ($array as $name => $value) {
		$bar_width = $value * $pixels_per_value;
		$string_to_return .= "<tr> <td>" .
		       	(++$counter) .
		      	"</td> <td>$name ($value)</td> <td>" .
			"<IMG SRC=\"images/bar.gif\" BORDER=\"1\" " .
			"WIDTH=\"$bar_width\" HEIGHT=\"10\" alt=\"BAR\">" .
		       "</td></tr>";
	}
	$string_to_return .= "</TABLE>";
	return($string_to_return);
}

/*not used*/
function ordinal($num=1)
{
	$ords = array("th","st","nd","rd");
	$val = $num;
	if ((($num%=100)>9 && $num<20) || ($num%=10)>3) $num=0;
	return "$val" . $ords[$num];
}

function saveResults($votes)
{
	global $gallery;
	if (!$votes)
	{
		return;
	}
	if ($gallery->album->getPollType() == "critique")
	{
		foreach ($votes as $vote_key => $vote_value)
		{
			if (($vote_value === null || $vote_value == "NULL")
					&& isset($gallery->album->fields["votes"] 
					   [$vote_key][getVotingID()]) )
			{
				unset($gallery->album->fields["votes"]
					[$vote_key]
					[getVotingID()]);
			}
			else
			{
				$gallery->album->fields["votes"]
					[$vote_key]
					[getVotingID()]=intval($vote_value);
			}
		}
	}
	else
	{
		krsort($votes, SORT_NUMERIC);
		foreach ($votes as $vote_value => $vote_key)
		{
		       	if (isset($gallery->album->fields["votes"] [$vote_key] [getVotingID()]) &&
				  $gallery->album->fields["votes"] [$vote_key] [getVotingID()] ===intval($vote_value))
			{
				//vote hasn't changed, so skip to next one
				continue;
			}
			foreach ($gallery->album->fields["votes"] as $previous_key => $previous_vote)
			{
				if (isset($previous_vote[getVotingID()]) &&
						$previous_vote[getVotingID()] 
							=== intval($vote_value))
				{
					unset($gallery->album->fields["votes"]
						[$previous_key]
						[getVotingID()]);
				}
			}
			$gallery->album->fields["votes"][$vote_key][getVotingID()]
				=intval($vote_value);
		}
		
	}
	_("New vote recorded");
	$gallery->album->save(array("New vote recorded"));
}

function getVotingID()
{
	global $gallery;
	if ($gallery->album->getVoterClass() ==  "Logged in")
	{
		return $gallery->user->getUid();
	}
	else if ($gallery->album->getVoterClass() ==  "Everybody")
	{
		return session_id();
	}
	else 
	{
		return NULL;
	}

}
function canVote()
{
	global $gallery;
	if ($gallery->album->getVoterClass() == "Everybody")
	{
		return true;
	}
	if ($gallery->album->getVoterClass() == "Logged in" 
		&& $gallery->user->isLoggedIn())
	{
		return true;
	}
	return false;
}
function addPolling ($id, $form_pos=-1, $immediate=true)
{
	global $gallery;
	if ( !canVote())
	{
		return;
	}
	if (isset($gallery->album->fields["votes"][$id][getVotingID()])) {
	       	$current_vote =
			$gallery->album->fields["votes"][$id][getVotingID()];
	} else {
		$current_vote = -1;
	}
	$nv_pairs=$gallery->album->getVoteNVPairs();
	print $gallery->album->getPollHint();
	if ($gallery->album->getPollScale() == 1 && $gallery->album->getPollType() == "critique")
	{
		print "\n<input type=checkbox name=\"votes[$id]\" value=\"1\"";
		if ($current_vote > 0)
		{
			print "checked";
		}
		print ">".$nv_pairs[0]["name"];
	}
	else if ($gallery->album->getPollType() == "rank")
	{
	    if ($gallery->album->getPollHorizontal())
	    {
		print "<table><tr>";
		for ($i = 0; $i < $gallery->album->getPollScale() ; $i++)
		{
			print "\n<td align=center><input type=radio name=\"votes[$i]\" value=$id onclick=\"chooseOnlyOne($i, $form_pos,".
			$gallery->album->getPollScale().")\" ";
			if ($current_vote === $i)
			{
				print "checked";
			}
			print "></td>";
		}
		print "</tr><tr>";
		for ($i = 0; $i < $gallery->album->getPollScale() ; $i++)
		{
			print "<td align=center>".$nv_pairs[$i]["name"]."</td>";
		}
		print "</tr></table>";
	    }
	    else
	    {
		print "<table>";
		for ($i = 0; $i < $gallery->album->getPollScale() ; $i++)
		{
			print "<tr>";
			print "\n<td align=center><input type=radio name=\"votes[$i]\" value=$id onclick=\"chooseOnlyOne($i, $form_pos,".
			$gallery->album->getPollScale().")\" ";
			if ($current_vote === $i)
			{
				print "checked";
			}
			print "></td>";
			print "<td >".$nv_pairs[$i]["name"]."</td>";
			print "</tr><tr>";
		}
		print "</table>";
	    }
	}
	else // "critique"
	{
		if ($immediate)
		{
			print "\n<br><select style='FONT-SIZE: 10px;' name=\"votes[$id]\" ";
			print "onChange='this.form.submit();'>";
		}
		else
		{
			print "\n<br><select name=\"votes[$id]\">";
		}
		if ($current_vote == -1)
		{
			print "<option value=NULL><< ". _("Vote") . " >></option>\n";
		}
		for ($i = 0; $i < $gallery->album->getPollScale() ; $i++)
		{
			$sel="";
			if ($current_vote === $i) {
				$sel="selected";
			}
			print "<option value=$i $sel>".$nv_pairs[$i]["name"]."</option>\n";
		}
		print "</select>";
	}
}

function showResultsGraph($num_rows)
{
	global $gallery;
	$results=array();
	$nv_pairs=$gallery->album->getVoteNVPairs();
	$buf='';

	$voters=array();
	foreach ($gallery->album->fields["votes"] as $element => $image_votes)
	{
	    $accum_votes=0;
	    $count=0;
	    foreach ($image_votes as $voter => $vote_value )
	    {
		$voters[$voter]=true;
		if ($vote_value> $gallery->album->getPollScale()) // scale has changed
		{
			$vote_value=$gallery->album->getPollScale();
		}
		$accum_votes+=$nv_pairs[$vote_value]["value"];
		$count++;
	    }
	    if ($accum_votes > 0) 
	    {
		if ($gallery->album->getPollType() == "rank" || $gallery->album->getPollScale() == 1)
		{
	    		$results[$element]=$accum_votes;
			$summary="("._("Total points in brackets") . ")";
		}
	    	else
		{
			$results[$element]=number_format(((double)$accum_votes)/$count, 2);
			$summary="("._("Average points in brackets") . ")";
		}
	    }
	}
	arsort($results);
	$rank=0;
	$graph=array();
	$needs_saving=false;
	foreach ($results as $element => $count)
	{
		$index=$gallery->album->getIndexByVotingId($element);
		if ($index < 0) 
		{
			// image has been deleted!
			// unset($gallery->album->fields["votes"][$element]);
			continue;
		} 
		$isAlbumName=$gallery->album->isAlbumName($index);
		if ($isAlbumName) {
			$url=makeAlbumUrl($isAlbumName);
			$album=$gallery->album->getSubAlbum($index);
			$desc=sprintf(_("Album: %s"), 
					$album->fields['title']);

		} else {
			$id = $gallery->album->getPhotoId($index);
			$url=makeAlbumUrl($gallery->session->albumName, $id);
			$desc=$gallery->album->getCaption($index);
			if (trim($desc)== "") {
				$desc=$id;
			}	
		}
		$current_rank = $gallery->album->getRank($index);
		$rank++;
		if ($rank != $current_rank) {
			$needs_saving = true;
			$gallery->album->setRank($index, $rank);
		}
		if ($rank > $num_rows)
		{
			continue;
		}
		
	    	$name_string='<a href="';
		$name_string.= $url;
		$name_string.= '">';
		$name_string.= $desc;
		$name_string.= "</a>";
		$graph[$name_string]=$count;
	}
	if ($needs_saving)
	{
		$gallery->album->save();
	}
	$graph=arrayToBarGraph($graph, 300, "border=0");
	if ($graph)
        {
                $buf .= "<span class=\"title\">".
			sprintf(_("Results from %s."),
					pluralize_n(sizeof($voters), 
						_("1 voter"), _("voters"), 
						_("0 voters"))).
                        "</span>";
                if ($gallery->album->getPollType() == "critique")
                {
                        $key_string="";
                        foreach ($nv_pairs as $nv_pair)
                        {
				if (empty($nv_pair["name"])) {
					continue;
				}
				$key_string .= sprintf(_("%s: %s points; "), 
						$nv_pair["name"],
						$nv_pair["value"]);
			}
                        if (strlen($key_string) > 0)
                        {       
                                $buf .= "<br>". sprintf(_("Key - %s"), 
						$key_string)." $summary<br>";
                        }
                }
                $buf .= $graph;
        } else if ($num_rows > 0 && 
			$gallery->user->canWriteToAlbum($gallery->album)) {
		$buf .= "<span class=\"title\">"._("No votes so far.")."<br></span>";
	}
	return array($buf, $results);
}
function showResults($id)
{
	global $gallery;
	$vote_tally=array();
	$nv_pairs=$gallery->album->getVoteNVPairs();
	$buf='';
	if (isSet ($gallery->album->fields["votes"][$id]))
	{
	    foreach ($gallery->album->fields["votes"][$id] as $vote)
	    {
		if (!isSet($vote_tally[$vote]))
		{
			$vote_tally[$vote]=1;
		}
		else
		{
			$vote_tally[$vote]++;
		}
	    }
	}
	$buf .= "<span class=\"admin\">"._("Poll results:")."</span> ";
	if (sizeof($vote_tally) === 0)
	{
		$buf .= _("No votes")."<br>";
		return;
	}
	$index=$gallery->album->getIndexByVotingId($id);
	$buf .= sprintf(_("Number %d overall."), 
			$gallery->album->getRank($index)) ."<br>";
	ksort($vote_tally);
	foreach ($vote_tally as $key => $value)
	{
		$buf .= sprintf(_("%s: %s"), $nv_pairs[$key]["name"],
			pluralize_n($value, _("1 vote"), _("votes"), _("0 votes"))). "<br>";
	}
	return $buf;
}

function processNewImage($file, $tag, $name, $caption, $setCaption="", $extra_fields=array()) {
	global $gallery;
	global $temp_files;

	if (!strcmp($tag, "zip")) {
		if (!$gallery->app->feature["zip"]) {
			processingMsg(sprintf(_("Skipping %s (ZIP support not enabled)"), $name));
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
					     ' ' .
					     fs_import_filename($cmd_pic_path) .
					     ' -d ' .
					     fs_import_filename($gallery->app->tmpDir, 1));
					     
					     /*
					      Don't use the second argument for $cmd_pic_path, because it is
					      already quoted.
					     */
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
				$temp_files[$newFile]=1;
			}
		    
			processingMsg("- ". sprintf(_("Adding %s"),$name));
			if ($setCaption and $caption == "") {
				$caption = strtr($originalFilename, '_', ' ');
			}
	
			if (!$extra_fields) {
			    $extra_fields=array();
			}
			$err = $gallery->album->addPhoto($file, $tag, $mangledFilename, $caption, "", $extra_fields, $gallery->user->uid);
			if ($err) {
				processingMsg("<font color=red>" . 
						sprintf(_("Error: %s!"), $err) .
						"</font>");
				processingMsg("<b>". sprintf(_("Need help?  Look in the  %s%s FAQ%s"),
				    '<a href="http://gallery.sourceforge.net/faq.php" target=_new>', 
				    Gallery(),
				    '</a>')."</b>");
			}
		} else {
			processingMsg(sprintf(_("Skipping %s (can't handle %s format)"),
						$name, $tag));
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

function createNewAlbum( $parentName, $newAlbumName="", $newAlbumTitle="", $newAlbumDesc="") {
        global $gallery;

        // get parent album name
        $albumDB = new AlbumDB(FALSE);

        // set new album name from param or default
	$gallery->session->albumName = $albumDB->newAlbumName($newAlbumName);

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
		$gallery->album->fields['extra_fields']    = $parentAlbum->fields['extra_fields'];		
                $gallery->album->fields["bgcolor"]         = $parentAlbum->fields["bgcolor"];
                $gallery->album->fields["textcolor"]       = $parentAlbum->fields["textcolor"];
                $gallery->album->fields["linkcolor"]       = $parentAlbum->fields["linkcolor"];
		$gallery->album->fields['background']      = $parentAlbum->fields['background'];
                $gallery->album->fields["font"]            = $parentAlbum->fields["font"];
                $gallery->album->fields["border"]          = $parentAlbum->fields["border"];
                $gallery->album->fields["bordercolor"]     = $parentAlbum->fields["bordercolor"];
                $gallery->album->fields["thumb_size"]      = $parentAlbum->fields["thumb_size"];
                $gallery->album->fields["resize_size"]     = $parentAlbum->fields["resize_size"];
                $gallery->album->fields["resize_file_size"]     = $parentAlbum->fields["resize_file_size"];
		$gallery->album->fields['max_size']        = $parentAlbum->fields['max_size'];
		$gallery->album->fields['max_file_size']   = $parentAlbum->fields['max_file_size'];
		$gallery->album->fields['returnto']        = $parentAlbum->fields['returnto'];
                $gallery->album->fields["rows"]            = $parentAlbum->fields["rows"];
                $gallery->album->fields["cols"]            = $parentAlbum->fields["cols"];
                $gallery->album->fields["fit_to_window"]   = $parentAlbum->fields["fit_to_window"];
                $gallery->album->fields["use_fullOnly"]    = $parentAlbum->fields["use_fullOnly"];
                $gallery->album->fields["print_photos"]    = $parentAlbum->fields["print_photos"];
		$gallery->album->fields['slideshow_type']  = $parentAlbum->fields['slideshow_type'];
		$gallery->album->fields['slideshow_recursive'] = $parentAlbum->fields['slideshow_recursive'];
		$gallery->album->fields['slideshow_length'] = $parentAlbum->fields['slideshow_length'];
		$gallery->album->fields['slideshow_loop'] = $parentAlbum->fields['slideshow_loop'];
		$gallery->album->fields['album_frame']    = $parentAlbum->fields['album_frame'];
		$gallery->album->fields['thumb_frame']    = $parentAlbum->fields['thumb_frame'];
		$gallery->album->fields['image_frame']    = $parentAlbum->fields['image_frame'];
                $gallery->album->fields["use_exif"]        = $parentAlbum->fields["use_exif"];
                $gallery->album->fields["display_clicks"]  = $parentAlbum->fields["display_clicks"];
		$gallery->album->fields["item_owner_display"] = $parentAlbum->fields["item_owner_display"];
		$gallery->album->fields["item_owner_modify"]  = $parentAlbum->fields["item_owner_modify"];
		$gallery->album->fields["item_owner_delete"]  = $parentAlbum->fields["item_owner_delete"];
		$gallery->album->fields["add_to_beginning"]   = $parentAlbum->fields["add_to_beginning"];
		$gallery->album->fields['showDimensions']  = $parentAlbum->fields['showDimensions'];
		
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

        if ($returnVal) {
		return $gallery->session->albumName;
	} else {
		return 0;
	}
}

function escapeEregChars($string)
{
	return ereg_replace('(\.|\\\\|\+|\*|\?|\[|\]|\^|\$|\(|\)|\{|\}|\=|\!|<|>|\||\:)', '\\\\1', $string);
}

function findInPath($program)
{
	$path = explode(':', getenv('PATH'));
	
	foreach ($path as $dir) {
		if (fs_file_exists("$dir/$program")) {
			return "$dir/$program";
		}
	}
	
	return false;
}

function initLanguage() {

	global $gallery, $GALLERY_BASEDIR, $GALLERY_EMBEDDED_INSIDE, $GALLERY_EMBEDDED_INSIDE_TYPE;
	global $HTTP_SERVER_VARS, $HTTP_COOKIE_VARS, $HTTP_GET_VARS, $HTTP_SESSION_VARS;

	// $locale is *NUKEs locale var
	global $locale ;

       $nls = getNLS();

       // before we do any tests or settings test if we are in mode 0
       // If so, we skip language settings at all

       $gallery->direction=$nls['default']['direction'];
       $gallery->align=$nls['default']['alignment'];
       if (isset($gallery->app->ML_mode)) {
		if($gallery->app->ML_mode == 0) {
			return;
		}
       }
       // Detect Browser Language

       if (isset($HTTP_SERVER_VARS["HTTP_ACCEPT_LANGUAGE"])) {
		$lang = explode (",", $HTTP_SERVER_VARS["HTTP_ACCEPT_LANGUAGE"]);
		$spos=strpos($lang[0],";");
		if ($spos >0) {
			$lang[0]=substr($lang[0],0,$spos);
		}
		$lang_pieces=explode ("-",$lang[0]);

		if (strlen($lang[0]) ==2) {
			$gallery->browser_language=$lang[0] ."_".strtoupper($lang[0]);
		} else {
			$gallery->browser_language=
				strtolower($lang_pieces[0]).
				"_".strtoupper($lang_pieces[1]) ;
		}
	}

	// Does the user wants a new lanuage ?
	if (isset($HTTP_GET_VARS['newlang'])) {
		$newlang=$HTTP_GET_VARS['newlang'];
	}

	/**
	 ** We have now 2+1 Ways. PostNuke, phpNuke or not Nuke
	 ** Now we (try) to do the language settings
	 ** 
	 ** Note: ML_mode is only used when not in *Nuke
	 **/

	if (isset($GALLERY_EMBEDDED_INSIDE)) {
		//We're in NUKE";
		if (!empty($newlang)) {
			// if there was a new language given, use it
			$gallery->nuke_language=$newlang;
		} else {
			//No new language. Lets see which Nuke we use and look for a language
			if ($GALLERY_EMBEDDED_INSIDE_TYPE == 'postnuke') {
				/* postnuke */
				if (isset($HTTP_SESSION_VARS['PNSVlang'])) {
					$gallery->nuke_language=$HTTP_SESSION_VARS['PNSVlang'];
				}
			}
			else {
				/* phpnuke */
				if (isset($HTTP_COOKIE_VARS['lang'])) {
					$gallery->nuke_language=$HTTP_COOKIE_VARS['lang'];
				}
			}
		}

		if (isset ($gallery->session->language) && ! isset($gallery->nuke_language)) {
			$gallery->language = $gallery->session->language;
		} else {
			$gallery->language=$nls['alias'][$gallery->nuke_language];
		}
	} else {
		// We're not in Nuke
		// If we got a ML_mode from config.php we use it
		// If not we use Mode 2 (Browserlanguage)

		if (isset($gallery->app->ML_mode)) {
			$ML_mode=$gallery->app->ML_mode;
		} else {
			$ML_mode=2;
		}

		switch ($ML_mode) {
			case 1:
				//Static Language
				$gallery->language = $gallery->app->default_language;
				break;
			case 3:
				// Does the user want a new language ?
				if (!empty($newlang)) {
					// Use Alias if
					if (isset($nls['alias'][$newlang])) $newlang=$nls['alias'][$newlang] ;
					// Set Language to the User selected language (if this language is defined)
					if (isset($nls['language'][$newlang])) {
						$gallery->language=$newlang;
					}
				} elseif (isset($gallery->session->language)) {
					//maybe we already have a language
					$gallery->language=$gallery->session->language;
				}
				break;
			default:
				// Use Browser Language or Userlanguage 
				// when mode 2 or any other (wrong) mode
				if (!empty($gallery->user) && 
						$gallery->user->getDefaultLanguage() != "") {
					$gallery->language = $gallery->user->getDefaultLanguage();
				} elseif (isset($gallery->browser_language)) {
					$gallery->language=$gallery->browser_language;
				}
				break;
		}
	}

	/**
	 **  Fall back to Default Language if :
	 **	- we cant detect Language
	 **	- Nuke sent an unsupported
	 **	- User sent an undefined
	 **/
	if (empty($gallery->language)) {
		if (isset($gallery->app->default_language)) {
			$gallery->language = $gallery->app->default_language;
		} elseif(isset($gallery->browser_language)) {
			$gallery->language = $gallery->browser_language;
		} else {
			// when we REALLY REALLY cant detect a language
			$gallery->language="en_US";
		}
	}

	// if an alias for a language is given, use it
	//
	if (isset($nls['alias'][$gallery->language])) {
		$gallery->language = $nls['alias'][$gallery->language] ;
	}

	// And now set this language into session
	$gallery->session->language = $gallery->language;

	// locale
	if (isset($gallery->app->locale_alias[$gallery->language])) {
		$gallery->locale=$gallery->app->locale_alias["$gallery->language"];
	} else {
		$gallery->locale=$gallery->language;
	}

	// Override NUKEs locale :)))	
	$locale=$gallery->locale;


	// Check defaults :
	$checklist=array('direction', 'charset', 'alignment') ;

	foreach($checklist as $check) {
		// if no ... is given, use default
		if ( !isset($nls[$check][$gallery->language])) {
			$gallery->$check = $nls['default'][$check] ;
		} else {
			$gallery->$check = $nls[$check][$gallery->language] ;
		}
	}

	// When all is done do the settings
	//
	if (getOS() != OS_SUNOS) {
		putenv("LANG=". $gallery->language);
	}
	putenv("LANGUAGE=". $gallery->language);

	// Set Locale
	setlocale(LC_ALL,$gallery->locale);

	// Set Charset
	// Only when we're not in nuke, because headers might be sent already.
	if (! isset($GALLERY_EMBEDDED_INSIDE)) {
		header('Content-Type: text/html; charset=' . $gallery->charset);
	}


	/**
	 ** Test if we're using gettext.
	 ** if yes, do some gettext settings.
	 ** if not emulate _() function
	 **/

	if (gettext_installed()) {
		$bindtextdomain=bindtextdomain($gallery->language. "-gallery_". where_i_am(), $GALLERY_BASEDIR."locale");
		textdomain($gallery->language. "-gallery_". where_i_am());

	}  else {
		emulate_gettext();
	}
}

function emulate_gettext() {
	global $translation;
	global $GALLERY_BASEDIR, $gallery;

	if (in_array($gallery->language,array_keys(gallery_languages())) &&
		$gallery->language != 'en_US') {
		$filename=$GALLERY_BASEDIR ."locale/". $gallery->language ."/". $gallery->language ."-gallery_". where_i_am()  .".po";
		$lines=file($filename);

		foreach ($lines as $key => $value) {
			if (stristr($value, "msgid") && ! stristr($lines[$key-1],"fuzzy")) {
				$new_key=substr($value, 7,-2);
				$translation[$new_key]=substr($lines[$key+1],8,-2);
			}
		}
		// Substitute _() gettext function
		function _($search) {
			if (! empty($GLOBALS['translation'][$search])) {
				return $GLOBALS['translation'][$search] ;
			}
			else {
				return $search;
			}
		}
	}
	// There is no translation file or we are using original (en_US), so just return what we got
	else {
		function _($search) {
			return $search;
		}
	}
}

/* little function useful for debugging.  no calls to this should be in 
   committed code. */

function vd($x, $string="") {
	print "<pre>\n$string: ";
	var_dump($x);
	print "</pre>\n";
}       

/* returns the offical name of the gallery */
function Gallery() {
	return "Gallery";
}

/*returns a link to the docs, if present, or NULL */
function galleryDocs() {
	global $GALLERY_BASEDIR;
	if (fs_file_exists($GALLERY_BASEDIR."docs/index.html")) {
		return "<a href=\"${GALLERY_BASEDIR}docs/index.html\">" .  _("documentation").'</a>';
	}
	return NULL;
}

function compress_image($src, $out, $target, $quality, $keepProfiles=false) {
	global $gallery;

	if ($target === 'off') {
		$target = '';
	}
	switch($gallery->app->graphics)	{
		case "NetPBM":
			$err = exec_wrapper(toPnmCmd($src) .
				(($target > 0) ? (' | ' .NetPBM('pnmscale',
				" -xysize $target $target")) : '')
				. ' | ' . fromPnmCmd($out, $quality));
			/* copy over EXIF data if a JPEG if $keepProfiles is
			 * set. Unfortunately, we can't also keep comments. */ 
			if ($keepProfiles && eregi('\.jpe?g$', $src)) {
				if (isset($gallery->app->use_exif)) {
					exec_wrapper(fs_import_filename($gallery->app->use_exif, 1) . ' -te '
						. fs_import_filename($src, 1) . ' '
						. fs_import_filename($out, 1));
				} else {
					processingMsg(_('Unable to preserve EXIF data (jhead not installed)') . "\n");
				}
			}
			break;
		case "ImageMagick":
			$src = fs_import_filename($src);
			$out = fs_import_filename($out);
			/* Preserve comment, EXIF data if a JPEG if $keepProfiles is set. */
			$err = exec_wrapper(ImCmd('convert', "-quality $quality "
					. ($target ? "-size ${target}x${target} " : '')
					. $src
					. ($target ? " -geometry ${target}x${target}" : '')
					. ($keepProfiles ? ' ' : ' +profile \'*\' ')
					. $out));
			break;
		default:
			if (isDebugging())
				echo "<br>" . _("You have no graphics package configured for use!")."<br>";
			return 0;
			break;
	}
}

function poweredBy () {
	global $gallery;
	$link = '<a href="'.$gallery->url.'">'.Gallery().'</a>';
	if ($gallery->session->offline) {
		return sprintf(_("Generated by %s v%s"), $link, $gallery->version);
	} else {
		return sprintf(_("Powered by %s v%s"), $link, $gallery->version);
	}
}

define("OS_WINDOWS", "win");
define("OS_LINUX", "linux");
define("OS_SUNOS", "SunOS");
define("OS_OTHER", "other");

function getOS () {
	if(substr(PHP_OS, 0, 3) == 'WIN') {
		return OS_WINDOWS;
	} else if ( stristr(PHP_OS, "linux")) {
		return OS_LINUX;
	} else if ( stristr(PHP_OS, "SunOS")) {
		return OS_SUNOS;
	} else {
		return OS_OTHER;
	}
}

// if $multiples is true, look to see if it's a list of comma separated addresses.
function validate_email($email, $multiples=false)
{
       	if (eregi('^([a-z0-9_]|\-|\.)+@(([a-z0-9_]|\-)+\.)+[a-z]{2,4}$', 
				$email)) {
	       	return $email;
       	} else if (!$multiples) {
	       	return false;
	} else {
		$email = ereg_replace('([[:space:]]+)', '', $email);
		$emails = array_filter(explode(',', $email));
		$size  = sizeof($emails);
		if ($size < 1) {
			return false;
		} else {
			$email="";
			$join="";
		       	foreach ($emails as $email) {
			       	if (validate_email($email)) {
				       	$email .= "$join$email";
				       	$join=", ";
			       	} else {
					if (isDebugging()) {
						print sprintf(_("Skipping invalid email address %s"), $email);
					}
			       	}
		       	}
			return $email;
	       	}
	}
}

function generate_password($len = 10)
{
	$result = '';
	$alpha  = 'abcdefghijklmnopqrstuvwxyz' .
			  '0123456789' .
			  'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

	srand((double)microtime() * 1000000);
	$size = strlen($alpha) - 1;
	$used = array();

	while($len--) {
		$random  = rand(0, $size);
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

function pretty_password($pass, $print, $pre = '    ', $post = '')
{
	$idx = -1;
	$len = strlen($pass);

	if ($print === true) {
		$result = "Your password is:  $pass\n\n";
	} else {
		$result = '';
	}

	while(++$idx < $len) {
		if (ereg('[[:upper:]]', $pass[$idx])) {
			$result .= $pre . $pass[$idx] .
				      ' = Uppercase letter ' . $pass[$idx] . "\n";
		} else if (ereg('[[:lower:]]', $pass[$idx])) {
			$result .= $pre . $pass[$idx] .
				      ' = Lowercase letter ' . $pass[$idx] . "\n";
		} else if (ereg('[[:digit:]]', $pass[$idx])) {
			$result .= $pre . $pass[$idx] .
				      ' = Numerical number ' . $pass[$idx] . "\n";
		} else {
			$result .= $pre . $pass[$idx] .
				      ' = ASCII Character  ' . $pass[$idx] . "\n";
		}
	}
	return "$result\n";
}

function emailDisclaimer() {
	global $gallery;
	$msg=sprintf(_("Note: This is an automatically generated email message sent from the website %s.  If you have received this in error, please ignore this message."),$gallery->app->photoAlbumURL).
	     "  \r\n".
	     sprintf(_("Report abuse to %s."),$gallery->app->adminEmail);
	$msg2=sprintf("Note: This is an automatically generated email message sent from the website %s.  If you have received this in error, please ignore this message.  \r\nReport abuse to %s.", 
		$gallery->app->photoAlbumURL, $gallery->app->adminEmail);
	if ($msg != $msg2) {
		return "[$msg\r\n$msg2]\r\n\r\n";
	} else {
		return "[$msg]\r\n\r\n";
	}
}



function gallery_mail($to, $subject, $msg, $logmsg, 
		$hide_recipients = false, $from = NULL) {
	global $gallery;
	if ($gallery->app->emailOn == "no") {
	       	gallery_error(_("Email not sent as it is disabled for this gallery"));
		return false;
	}
       	if (!$to) {
	       	gallery_error(sprintf(_("Email not sent as no address provided"),
				       	"<i>" . $to . "</i>"));
		return false;
	}
       	if (!validate_email($to, true)) {
	       	gallery_error(sprintf(_("Email not sent to %s as it is not a valid address"),
				       	"<i>" . $to . "</i>"));
		return false;
	}
	if ($hide_recipients) {
		$bcc=$to;
		$to="";
		$join=",";
	} else {
		$bcc="";
		$join="";
	}
	global $gallery, $HTTP_SERVER_VARS;
	if (!validate_email($from)) {
		if (isDebugging() && $from) {
			gallery_error( sprintf(_("Sender address %s is invalid, using %s."),
				       	$from, $gallery->app->senderEmail));
	       	}
		$from = $gallery->app->senderEmail;
		$reply_to = $gallery->app->adminEmail;
	} else {
		$reply_to = $from;
	}
	if (isset($gallery->app->email_notification) &&
			in_array("bcc", $gallery->app->email_notification)) {
		$bcc .= $join.$gallery->app->adminEmail;
	}
	$additional_headers = "From: $from\r\nReply-To: $reply_to\r\n";
	$additional_headers .= "X-GalleryRequestIP: " . $HTTP_SERVER_VARS['REMOTE_ADDR'] . "\r\n";
	if ($bcc) {
		$additional_headers .= "Bcc: " . $bcc. "\r\n";
	}
	if (get_magic_quotes_gpc() ) {
		$msg = stripslashes($msg);
	}
	$result=mail($to, $gallery->app->emailSubjPrefix." ".$subject, emailDisclaimer().$msg, $additional_headers);
	if (isDebugging()) {
		print "<table>";
		print "<tr><td valign=\"top\">To:</td><td valign=\"top\">&lt;" .
			_("not shown") . "&gt;</td></tr>";
		print "<tr><td valign=\"top\">Subject:</td><td valign=\"top\">$subject</td></tr>";
		print "<tr><td valign=\"top\">";
		print str_replace(":", ":</td><td valign=\"top\">", 
				ereg_replace(":[^:\n]*\n", ":&lt;" . 
					_("not shown") . 
					"&gt;</td></tr><tr><td valign=\"top\">",
					$additional_headers));
		print "</td></tr>";
		print '<tr><td valign="top">' . _("Message") . 
			':</td><td valign="top">'. str_replace("\n", "<br>", $msg). '</td></tr>';
		print "</table>";
	       	if ($result) {
			print _("Email sent")."<br>";
		} else {
		       	gallery_error(_("Email not sent"));
	       	}
	}
	emailLogMessage($logmsg, $result);
	return $result;
}

/* Formats a nice string to print below an item with comments */
function lastCommentString($lastCommentDate, &$displayCommentLegend) {
	global $gallery;
	if ($lastCommentDate  <= 0) {
		return  "";
	}
	if ($gallery->app->comments_indication_verbose=="yes") {
		$ret = "<br>".sprintf(_("Last comment %s."), 
				strftime($gallery->app->dateString, $lastCommentDate));
	} else {
		$ret= "<span class=error>*</span>";
		$displayCommentLegend = 1;
	}
	return $ret;
}
function emailLogMessage($logmsg, $result) {
	global $gallery;
	if (!$result) {
		$logmsg = _("FAILED")."/FAILED: $logmsg";
	}
	if (isset($gallery->app->email_notification) &&
			in_array("logfile", $gallery->app->email_notification)) {
		$logfile=$gallery->app->albumDir."/email.log";
		logMessage($logmsg, $logfile);
	}
	if (isset($gallery->app->email_notification) &&
			in_array("email", $gallery->app->email_notification)) {
		$subject = _("Email activity");
		if ($subject != "Email activity") {
			$subject .= "/Email activity";
		}
		$subject .= ": ".  $gallery->app->galleryTitle;
		mail($gallery->app->adminEmail, 
			$subject,
			emailDisclaimer().$logmsg,
			"From: " . $gallery->app->senderEmail);
	}
}
function logMessage ($msg, $logfile) {
	
	if ($fd = fs_fopen($logfile, "a")) {
		fwrite($fd, strftime("%Y/%m/%d %H:%M.%S: $msg\n"));
		fclose($fd);
	}
	else if (isDebugging()) {
		print sprintf(_("Cannot open logfile: %s"), $logfile);
	}
}

function welcome_email($show_default=false) {
	global $gallery;

	$default=_("Hi !!FULLNAME!!,  

Congratulations.  You have just been subscribed to %s at %s.  Your account name is !!USERNAME!!.  Please visit the gallery soon, and create a password by clicking this link:

!!NEWPASSWORDLINK!!

Gallery @ %s Administrator.");
	if ($show_default) {
		return sprintf($default, 
		       	"<b><nobr>&lt;" . _("gallery title") . "&gt;</nobr></b>", 
		       	"<b><nobr>&lt;" . _("gallery URL") . "&gt;</nobr></b>", 
		       	"<b><nobr>&lt;" . _("gallery title") . "&gt;</nobr></b>");
	} else if (empty($gallery->app->emailGreeting)) {
		return sprintf($default, 
			$gallery->app->galleryTitle,
			$gallery->app->photoAlbumURL,
			$gallery->app->galleryTitle);
	} else {
		return $gallery->app->emailGreeting;
	}

}

function gettext_installed() {
	if (in_array("gettext", get_loaded_extensions()) && function_exists('gettext')) {
		return true;
	}
	else {
		return false;
	}
}

function available_skins($description_only=false) {
	global $GALLERY_BASEDIR;

	$dir = "${GALLERY_BASEDIR}skins";
	$opts['default'] = 'default';
	$descriptions="<dl>";
	if (fs_is_dir($dir) && is_readable($dir) && $fd = fs_opendir($dir)) {
                while ($file = readdir($fd)) {
			$subdir="$dir/$file/css";
			$skininc="$dir/$file/style.def";
		       	$name="";
		       	$description="";
			$skincss="$subdir/embedded_style.css";
			if (fs_is_dir($subdir) && fs_file_exists($skincss)) {
				if (fs_file_exists($skininc)) {
				       	require($skininc);
			       	}
			       	if (empty($name )) {
				       	$name=$file;
			       	}
				$opts[$file]=$name;
				if (fs_file_exists("$dir/$file/images/screenshot.jpg")) {
					$screenshot="$dir/$file/images/screenshot.jpg";
				} else if (fs_file_exists("$dir/$file/images/screenshot.gif")) {
					$screenshot="$dir/$file/images/screenshot.gif";
				} else {
					$screenshot="";
				}
				if ($screenshot) {
					$description .=
						" (" .
						popup_link(_("screenshot"), 
								$screenshot, 1, false) .
					       ")";	
				}
				if ($description) {
				       	$descriptions.="<dt>$name</dt><dd>$description</dd>";
				}
			}
		}
	} else {
		gallery_error(sprintf(_("Can't open %s"), $dir));
	}
	if ($description_only) {
		return $descriptions;
	} else {
	       	return $opts;
	}
}

function available_frames($description_only=false) {
	global $GALLERY_BASEDIR;

	$opts=array(
			'none' => _("None"), 
			'dots' => _("Dots"), 
			'solid' => _("Solid"), 
			);
	$descriptions="<dl>" .
		"<dt>"._("None")."</dt><dd>"._("No frames")."</dd>" .
		"<dt>"._("Dots")."</dt><dd>"._("Just a simple dashed border around the thumb.")."</dd>" .
		"<dt>"._("Solid")."</dt><dd>"._("Just a simple solid border around the thumb.")."</dd>" ;
	$dir = "${GALLERY_BASEDIR}html_wrap/frames";
       	if (fs_is_dir($dir) && is_readable($dir) && $fd = fs_opendir($dir)) {
	       	while ($file = readdir($fd)) {
			$subdir="$dir/$file";
			$frameinc="$subdir/frame.def";
			if (fs_is_dir($subdir) && fs_file_exists($frameinc)) {
				$name=NULL;
				$description=NULL;
				require($frameinc);
				if (empty($name )) {
					$name=$file;
				}
				if (empty($description )) {
					$description=$file;
				}
				$opts[$file]=$name;
				$descriptions.="<dt>$name</dt><dd>$description</dd>";
			} else {
				if (false && isDebugging()) 
				{
					gallery_error(sprintf(_("Skipping %s."),
								$subdir));
				}
			}
		}
	} else {
		gallery_error(sprintf(_("Can't open %s"), $dir));
	}
	if ($description_only) {
		return $descriptions;
	} else {
	       	return $opts;
	}
}



/* simplify condition tests */
function testRequirement($test) {
    global $gallery;
    switch ($test) {
    case 'isAdminOrAlbumOwner':
	return $gallery->user->isAdmin() || $gallery->user->isOwnerOfAlbum($gallery->album);
	break;
    case 'allowComments':
	return $gallery->album->fields["perms"]['canAddComments'];
       	break;
    case 'canAddToAlbum':
	return $gallery->user->canAddToAlbum($gallery->album);
	break;
    case 'extraFieldsExist':
	$extraFields = $gallery->album->getExtraFields();
	return !empty($extraFields);
	break;
    case 'isAlbumOwner':
	return $gallery->user->isOwnerOfAlbum($gallery->album);
	break;
    case 'canCreateSubAlbum':
	return $gallery->user->canCreateSubAlbum($gallery->album);
	break;
    case 'notOffline':
	return !$gallery->session->offline;
	break;
    case 'canChangeText':
	return $gallery->user->canChangeTextOfAlbum($gallery->album);
	break;
    case 'canWriteToAlbum':
	return $gallery->user->canWriteToAlbum($gallery->album);
	break;
    case 'photosExist':
	return $gallery->album->numPhotos(true);
	break;
    default:
	return false;
    }
    return false;
}

function doctype() {
	?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
	<?php
}

// uses makeGalleryURL
function gallery_validation_link($file, $args=array()) {
	$args['PHPSESSID']=session_id();
	$link='<a href="http://validator.w3.org/check?uri='.
		urlencode(eregi_replace("&amp;", "&",
					makeGalleryURL($file, $args))) .
		'"> <img border="0" src="http://www.w3.org/Icons/valid-html401" alt="Valid HTML 4.01!" height="31" width="88"></a>';
	return $link;

}

// uses makeAlbumURL
function album_validation_link($album, $photo='', $args=array()) {
	$args['PHPSESSID']=session_id();
	$link='<a href="http://validator.w3.org/check?uri='.
		urlencode(eregi_replace("&amp;", "&", 
					makeAlbumURL($album, $photo, $args))).
		'"> <img border="0" src="http://www.w3.org/Icons/valid-html401" alt="Valid HTML 4.01!" height="31" width="88"></a>';
	return $link;
}

//returns all languages in this gallery installation
function gallery_languages() {
	$nls=getNLS();
	return $nls['language'];
}

function getNLS() {
	global $GALLERY_BASEDIR;
	static $nls;

	if (empty($nls)) {

		$nls=array();
		// Load defaults
		include ($GALLERY_BASEDIR. "nls.php");

		$modules=array('config','core');
		$handle=fs_opendir($GALLERY_BASEDIR. "locale");
	
		while ($dirname = readdir($handle)) {
			if (ereg("^([a-z]{2}_[A-Z]{2})", $dirname)) {
				$locale=$dirname;
				$fc=0;
				foreach ($modules as $module) {
					if (gettext_installed()) {
						if (fs_file_exists($GALLERY_BASEDIR . "locale/$dirname/$locale-gallery_$module.po")) $fc++;
					} else {
						if (fs_file_exists($GALLERY_BASEDIR . "locale/$dirname/LC_MESSAGES/$locale-gallery_$module.mo")) $fc++;
					}
				}
				if (fs_file_exists($GALLERY_BASEDIR . "locale/$dirname/$locale-nls.php") && $fc==sizeof($modules)) {
					include ($GALLERY_BASEDIR . "locale/$dirname/$locale-nls.php");
				}
			}
		}
		closedir($handle);
	}

//print_r($nls);
return $nls;
}


function where_i_am() {
	global $GALLERY_OK;

	if ($GALLERY_OK == true && strpos($_SERVER['REQUEST_URI'],"setup") == 0) {
		return "core";
	} else {
		return "config";
	}

}
function commenter_name_string($uid) {
       	global $gallery;
       	$user=$gallery->userDB->getUserByUid($uid);
       	if (!$user || $user->isPseudo()) {
	       	return "";
       	} else if ($user->getFullName()) {
	       	$string= sprintf("%s (%s)",
				$user->getFullName(),
			       	$user->getUsername()); 
       	} else {
	       	$string = $user->getUsername();
       	}
	return $string;
}

?>
