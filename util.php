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
		$buf .= ' <span class="editlink">';
		$buf .= popup_link( "[". sprintf(_("edit %s"), _($field)) . "]", $url) ;
		$buf .= '</span>';
	}
	return $buf;
}

function editCaption($album, $index) {
	global $gallery;

	$abuf='';
	$buf = nl2br($album->getCaption($index));

	if (($gallery->user->canChangeTextOfAlbum($album) ||
               ($gallery->album->getItemOwnerModify() && 
	         $gallery->album->isItemOwner($gallery->user->getUid(), $index))) 
		&& !$gallery->session->offline) {

		if (empty($buf)) {
			$buf = "<i>&lt;". _("No Caption") ."&gt;</i>";
		}
		$url = "edit_caption.php?set_albumName={$album->fields['name']}&index=$index";
		$abuf = '<span class="editlink">';
		$abuf .= popup_link("[". _("edit") ."]", $url);
		$abuf .= '</span>';
	}
	$buf .= $album->getCaptionName($index);
	$buf .= $abuf;

	return $buf;
}

function viewComments($index, $addComments, $page_url, $newestFirst = false, $addType = '', $album=false) {
	global $gallery;
	global $commentdraw;
	global $i;
	global $commenter_name;

	$html = '';

	echo showComments($index, $album, $newestFirst);

	if ($addComments) {
		/** Default is the popup link.
		 * addType given through function call overrides default.
		 */
		if (empty($addType)) {
			$addType = (isset($gallery->app->comments_addType) ? $gallery->app->comments_addType : "popup");
		}
		if ($addType == 'inside') {
		    echo makeFormIntro($page_url, array('name' => 'theform'));
			drawCommentAddForm($commenter_name);
			echo "</form>";
		}
		else {
			$id = $gallery->album->getPhotoId($index);
			$url = "add_comment.php?set_albumName={$gallery->album->fields['name']}&id=$id";
			echo "\n" .'<div align="center" class="editlink">' .
			  popup_link('[' . _("add comment") . ']', $url, 0) .
			  '</div><br>';
		}
	}
}

function drawCommentAddForm($commenter_name = '', $cols = 50) {
    global $gallery;
    if ($gallery->user->isLoggedIn() &&
      (empty($commenter_name) || $gallery->app->comments_anonymous == 'no')) {
        $commenter_name = $gallery->user->printableName($gallery->app->comments_display_name);
    }
?>
<table class="commentbox" cellpadding="0" cellspacing="0">
<tr>
	<td colspan="2" class="commentboxhead"><?php echo _("Add your comment") ?></td>
</tr>
<tr>
	<td class="commentboxhead"><?php echo _("Commenter:") ?></td>
	<td class="commentboxhead">
<?php

if (!$gallery->user->isLoggedIn() ) {
	echo '<input name="commenter_name" value="'. $commenter_name .'" size="30">';
} else {
	if ($gallery->app->comments_anonymous == 'yes') {
		echo '<input name="commenter_name" value="'. $commenter_name. '" size="30">';
	} else {
		echo $commenter_name;
		echo '<input type="hidden" name="commenter_name" value="'. $commenter_name .'" size="30">';
	}
}
?>
</td>
</tr>
<tr>
	<td class="commentlabel" valign="top"><?php echo _("Message:") ?></td>
	<td><textarea name="comment_text" cols="<?php echo $cols ?>" rows="5"></textarea></td>
</tr>
<tr>
	<td colspan="2" class="commentboxfooter" align="right"><input name="save" type="submit" value="<?php echo _("Post comment") ?>"></td>
</tr>
</table>
<?php 
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
		foreach ($blacklist['entries'] as $key => $entry) {
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

function getDimensions($file, $regs=false) {
    global $gallery;				

    debugMessage(sprintf(_("Getting Dimension of file: %s"), $file), __FILE__, __LINE__,2);

    if (! fs_file_exists($file)) {
        debugMessage(_("The file does not exist ?!"), __FILE__, __LINE__);
        return array(0, 0);
    }
    if ($regs === false) {
        $regs = getimagesize($file);
    }
    
    if (($regs[0] > 1) && ($regs[1] > 1)) {
        return array($regs[0], $regs[1]);
    }

    debugMessage(sprintf(_("PHP's %s unable to determine dimensions."), "getimagesize()"), __FILE__, __LINE__);
		
    /* Just in case php can't determine dimensions. */
    switch($gallery->app->graphics) {
        case "NetPBM":
            list($lines, $status) = exec_internal(toPnmCmd($file) ." | ". 
                                      NetPBM("pnmfile", "--allimages"));
            break;
        case "ImageMagick":
            /* This fails under windows, IM isn't returning parsable status output. */
              list($lines, $status) = exec_internal(ImCmd("identify", fs_import_filename($file)));
        break;
        
	default:
	    echo debugMessage(_("You have no graphics package configured for use!"));
	    return array(0,0);
        break;
    }

    if ($status == $gallery->app->expectedExecStatus) {
        foreach ($lines as $line) {
            switch($gallery->app->graphics) {
                case "NetPBM":
                    if (ereg("([0-9]+) by ([0-9]+)", $line, $regs)) {
                        return array($regs[1], $regs[2]);
                    }
                break;
                
                case "ImageMagick":
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

function dismissAndReload() {
	if (isDebugging()) {
		echo "\n<body onLoad='opener.location.reload();'>\n";
		echo '<p align="center" class="error">';
		echo _("Not closing this window because debug mode is on") ;
		echo "\n<hr>\n</p>";
		echo "\n</body>";
	} else {
		echo "<body onLoad='opener.location.reload(); parent.close()'></body>";
	}
	echo "\n</html>";
}

function reload() {
	echo '<script language="javascript1.2" type="text/JavaScript">';
	echo 'opener.location.reload()';
	echo '</script>';
}

function dismissAndLoad($url) {
	if (isDebugging()) {
		echo("<BODY onLoad='opener.location = \"$url\"; '>");
		echo("Loading URL: $url");
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

function resize_image($src, $dest, $target = 0, $target_fs = 0, $keepProfiles = 0) {

    debugMessage(sprintf(_("Resizing Image: %s"), $src), __FILE__, __LINE__);

    /*
     *  Valid return codes:
     *  0:  File was not resized, no processing to be done
     *  1:  File resized, process normally
     *  2:  Existing resized file should be removed
    */
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
		processingMsg("&nbsp;&nbsp;&nbsp;". _("No resizing required"));

		/* If the file is already smaller than the target filesize, don't
		 * create a new sized image.  return 2 indicates that the current .sized.
		 * needs to be removed */
		if ($useTemp == false && !strstr($dest, ".sized.")) {
			fs_copy($src, $dest);
			return 1;
		}
		elseif (fs_file_exists($dest) && strstr($dest, ".sized.")) {
			return 2;
		}
		return 0;
	}
	$target = min($target, max($regs[0],$regs[1]));

	/* Jens Tkotz, 02.10.2004.
	** Lines with $min_filesize commented because never used.
	*/
	if ($target_fs == 0) {
		compress_image($src, $out, $target, $gallery->app->jpegImageQuality, $keepProfiles);
	} else {
		$filesize = (int) fs_filesize($src) >> 10;
		$max_quality=$gallery->app->jpegImageQuality;
		$min_quality=5;
		$max_filesize=$filesize;
		//$min_filesize=0;
		if (!isset($quality)) {
			$quality=$gallery->album->fields['last_quality'];
		}
		processingMsg("&nbsp;&nbsp;&nbsp;". sprintf(_("target file size %d kbytes"), 
					$target_fs)."\n");

		do {
			compress_image($src, $out, $target, $quality, $keepProfiles);
			$prev_quality=$quality;
			printf(_("-> file size %d kbytes"), round($filesize));
			processingMsg("&nbsp;&nbsp;&nbsp;" . sprintf(_("trying quality %d%%"), 
						$quality));
			clearstatcache();
			$filesize= (int) fs_filesize($out) >> 10;
			if ($filesize < $target_fs) {
				$min_quality=$quality;
				//$min_filesize=$filesize;
			} elseif ($filesize > $target_fs){
				$max_quality=$quality;
				$max_filesize=$filesize;
			} elseif ($filesize == $target_fs){
				$min_quality=$quality;
				$max_quality=$quality;
				// $min_filesize=$filesize;
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

		$gallery->album->fields['last_quality']=$prev_quality;
		printf(_("-> file size %d kbytes"), round($filesize));
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

function netpbm_decompose_image($input, $format)
/*
In order for pnmcomp to support watermarking from formats other than pnm, the watermark
first needs to be converted to .pnm. Second the alpha channel needs to be decomposed as a
second image

Returns a list of 2 temporary files (overlay, and alphamask), these files should be deleted (unlinked)
  by the calling function
*/
{

	global $gallery;	
   $overlay = tempnam($gallery->app->tmpDir, "netpbm_");
   $alpha = tempnam($gallery->app->tmpDir, "netpbm_");
   switch ($format) {
   case "png":
      $getOverlay = netPbm("pngtopnm", "$input > $overlay");
      $getAlpha   = netPbm("pngtopnm", "-alpha $input > $alpha");
      break;
   case "gif":
      $getOverlay = netPbm("giftopnm", "--alphaout=$alpha $input > $overlay");
      break;
   case "tif":
      $getOverlay = netPbm("tifftopnm", "-alphaout=$alpha $input > $overlay");
      break;
   }
   list($results, $status) = exec_internal($getOverlay);
   if (isset($getAlpha)) {
      list($results, $status) = exec_internal($getAlpha);
   }
   return array($overlay, $alpha);
}

function watermark_image($src, $dest, $wmName, $wmAlphaName, $wmAlign, $wmAlignX, $wmAlignY) {
   global $gallery;
   if (!strcmp($src,$dest)) {
      $useTemp = true;
      $out = "$dest.tmp";
   } else {
      $useTemp = false;
      $out = $dest;
   }
   if (isDebugging())
   {
      print "<table border=\"1\">";
      print "<tr><td>src</td><td>$src</td></tr>";
      print "<tr><td>dest</td><td>$dest</td></tr>";
      print "<tr><td>wmName</td><td>$wmName</td></tr>";
      print "<tr><td>wmAlign</td><td>$wmAlign</td></tr>";
      print "<tr><td>wmAlignX</td><td>$wmAlignX</td></tr>";
      print "<tr><td>wmAlignY</td><td>$wmAlignY</td></tr>";
      print "</table>";
   }

   $srcSize = getDimensions($src);
   $overlaySize = getDimensions($wmName);
   if (strlen($wmName))
   {
      switch($gallery->app->graphics)
      {
      case "ImageMagick":
         $overlayFile = $wmName;
         break;
      case "NetPBM":
         if (eregi("\.png$",$wmName, $regs)) {
            list ($overlayFile, $alphaFile) = netpbm_decompose_image($wmName, "png");
            $tmpOverlay = 1;
	 } elseif (eregi("\.tiff?$",$wmName, $regs)) {
            list ($overlayFile, $alphaFile) = netpbm_decompose_image($wmName, "tif");
            $tmpOverlay = 1;
	 } elseif (eregi("\.gif$",$wmName, $regs)) {
            list ($overlayFile, $alphaFile) = netpbm_decompose_image($wmName, "gif");
            $tmpOverlay = 1;
         } else {
            $alphaFile = $wmName;
            if (strlen($wmAlphaName)) {
                $overlayFile = $wmAlphaName;
            }
         }
         break;
      default:
         echo debugMessage(_("You have no graphics package configured for use!"));
         return 0;
      }
   } else {
	echo gallery_error(_("No watermark name specified!"));
	return 0;
   }

   // Set or Clip $wmAlignX and $wmAlignY
   switch ($wmAlign)
   {
   case 1: // Top - Left
      $wmAlignX = 0;
      $wmAlignY = 0;
      break;
   case 2: // Top
      $wmAlignX = ($srcSize[0] - $overlaySize[0]) / 2;
      $wmAlignY = 0;
      break;
   case 3: // Top - Right
      $wmAlignX = ($srcSize[0] - $overlaySize[0]);
      $wmAlignY = 0;
      break;
   case 4: // Left
      $wmAlignX = 0;
      $wmAlignY = ($srcSize[1] - $overlaySize[1]) / 2;
      break;
   case 5: // Center
      $wmAlignX = ($srcSize[0] - $overlaySize[0]) / 2;
      $wmAlignY = ($srcSize[1] - $overlaySize[1]) / 2;
      break;
   case 6: // Right
      $wmAlignX = ($srcSize[0] - $overlaySize[0]);
      $wmAlignY = ($srcSize[1] - $overlaySize[1]) / 2;
      break;
   case 7: // Bottom - Left
      $wmAlignX = 0;
      $wmAlignY = ($srcSize[1] - $overlaySize[1]);
      break;
   case 8: // Bottom
      $wmAlignX = ($srcSize[0] - $overlaySize[0]) / 2;
      $wmAlignY = ($srcSize[1] - $overlaySize[1]);
      break;
   case 9: // Bottom Right
      $wmAlignX = ($srcSize[0] - $overlaySize[0]);
      $wmAlignY = ($srcSize[1] - $overlaySize[1]);
      break;
   case 10: // Other
      // Check for percents
      if (ereg('([0-9]+)(\%?)', $wmAlignX, $regs)) {
         if ($regs[2] == '%') {
            $wmAlignX = round($regs[1] / 100 * ($srcSize[0] - $overlaySize[0]));
         } else {
            $wmAlignX = $regs[1];
         }
      } else {
         $wmAlignX = 0;
      }
                                                                                                                    
      if (ereg('([0-9]+)(\%?)', $wmAlignY, $regs)) {
         if ($regs[2] == '%') {
            $wmAlignY = round($regs[1] / 100 * ($srcSize[1] - $overlaySize[1]));
         } else {
            $wmAlignY = $regs[1];
         }
      } else {
         $wmAlignY = 0;
      }

      if ($wmAlignX < 1)
      { // clip left side
         $wmAlignX = 0;
      }
      elseif ($wmAlignX > ($srcSize[0] - $overlaySize[0]))
      { // clip right side
        $wmAlignX = ($srcSize[0] - $overlaySize[0]);
      }
      if ($wmAlignY < 1)
      { // clip top
         $wmAlignY = 0;
      }
      elseif ($wmAlignY > ($srcSize[1] - $overlaySize[1]))
      { // clip bottom
        $wmAlignY = ($srcSize[1] - $overlaySize[1]);
      }
      break;
   } // end switch ($wmAlign)

   $wmAlignX = floor($wmAlignX);
   $wmAlignY = floor($wmAlignY);

   // Build command lines arguements
   switch($gallery->app->graphics) {
	case "ImageMagick":
	    $args = "-geometry +$wmAlignX+$wmAlignY $overlayFile $src $out";
	    break;
	case "NetPBM":
	    $args  = "-yoff=$wmAlignY -xoff=$wmAlignX ";
	    if ($alphaFile) {
		$args .= "-alpha=$alphaFile ";
	    }
	    $args .= $overlayFile;
	    break;
    }

    debugMessage("args = $args", __FILE__, __LINE__);    

   // Execute
   switch($gallery->app->graphics)
   {
   case "ImageMagick":
      exec_wrapper(ImCmd("composite", $args));
      break;
   case "NetPBM":
      exec_wrapper(toPnmCmd($src) ." | ". NetPBM($gallery->app->pnmcomp, $args) ." | " . fromPnmCmd($out));
      break;
   }

   // copy exif headers from original image to rotated image
   if (isset($gallery->app->use_exif)) {
      $path = $gallery->app->use_exif;
      exec_internal(fs_import_filename($path, 1) . " -te $src $out");
   }
   
   // Test to see if it worked, and copy Temp file if needed
   if (fs_file_exists("$out") && fs_filesize("$out") > 0) {
      if ($useTemp) {
         fs_copy($out, $dest);
         fs_unlink($out);
      }
      if (!empty($tmpOverlay)) {
         fs_unlink($overlayFile);
         if ($alphaFile) {
            fs_unlink($alphaFile);
         }
      }
      return 1;
   } else {
      return 0;
   }
} // end watermark_image()

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
		} elseif (!strcmp($target, '180')){
			$args = '-rotate 180';
		} elseif (!strcmp($target, '90')) {
			$args = '-rotate 270';
		} elseif (!strcmp($target, 'fv')) {
			$args = '-flip vertical';
		} elseif (!strcmp($target, 'fh')) {
			$args = '-flip horizontal';
		} elseif (!strcmp($target, 'tr')) {
			$args = '-transpose';
		} elseif (!strcmp($target, 'tv')) {
			$args = '-transverse';
		} else {
			$args = '';
		}

		$path = $gallery->app->use_jpegtran;
		// -copy all ensures all headers (i.e. EXIF) are copied to the rotated image
		exec_internal(fs_import_filename($path, 1) . " $args -trim -copy all -outfile $outFile $srcFile");
	} else {
		switch($gallery->app->graphics)
		{
		case "NetPBM":
			$args2 = '';
			if (!strcmp($target, '-90')) {
				/* NetPBM's docs mix up CW and CCW...
				 * We'll do it right. */
				$args = '-r270';
			} elseif (!strcmp($target, '180')) {
				$args = '-r180';
			} elseif (!strcmp($target, '90')) {
				$args = '-r90';
			} elseif (!strcmp($target, 'fv')) {
				$args = '-tb';
			} elseif (!strcmp($target, 'fh')) {
				$args = '-lr';
			} elseif (!strcmp($target, 'tr')) {
				$args = '-xy';
			} elseif (!strcmp($target, 'tv')) {
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

			exec_wrapper(toPnmCmd($src) . ' | ' .
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
			} elseif (!strcmp($target, '180')) {
			    $im_cmd = '-rotate 180';
			} elseif (!strcmp($target, '90')) {
			    $im_cmd = '-rotate -90';
			} elseif (!strcmp($target, 'fv')) {
			    $im_cmd = '-flip';
			} elseif (!strcmp($target, 'fh')) {
			    $im_cmd = '-flop';
			} elseif (!strcmp($target, 'tr')) {
			    $im_cmd = '-affine 0,1,1,0,0,0 -transform';
			} elseif (!strcmp($target, 'tv')) {
			    $im_cmd = '-affine 0,-1,-1,0,0,0 -transform';
			} else {
			    $im_cmd = '';
			}
			
			exec_wrapper(ImCmd('convert', "$im_cmd $srcFile $outFile"));
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
		exec_wrapper(toPnmCmd($src) .
				" | " .
				NetPBM("pnmcut") .
				" $x $y $width $height" .
				" | " .
				fromPnmCmd($out));
		break;
	case "ImageMagick":
		$srcFile = fs_import_filename($src);
		$outFile = fs_import_filename($out);
		exec_wrapper(ImCmd("convert", "-crop " .
				$width ."x". $height ."+". $x ."+". $y .
				" $srcFile $outFile"));
		break;
	default:
		if (isDebugging())
			echo "<br>" . _("You have no graphics package configured for use!") ."<br>";
		return 0;
		break;
	}

	if (fs_file_exists("$out") && fs_filesize("$out") > 0) {
		if (isset($useTemp)) {
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
        debugMessage(sprintf(_("Call to %s failed in %s for file %s!"), 'getimagesize()', 'valid_image()', $file), __FILE__, __LINE__);
	return 0;
    }

    debugMessage(sprintf(_("File %s type %d."), $file, $type[2]), __FILE__, __LINE__);

    switch($type[2]) {
	case 1: // GIF
	case 2: // JPEG
	case 3: // PNG
	    return 1;
	    break;

	default:
	    return 0;
	break;
    }
}

function toPnmCmd($file) {
	global $gallery;

	if (eregi("\.png\$", $file)) {
		$cmd = "pngtopnm";
	} elseif (eregi("\.jpe?g\$", $file)) {
		if (isDebugging()) {
			$cmd = "jpegtopnm";
		} else {
			$cmd = "jpegtopnm";
		}
	} elseif (eregi("\.gif\$", $file)) {
		$cmd = "giftopnm";
	}

	if (!empty($cmd)) {
		return NetPBM($cmd) .
		 	" " .
			fs_import_filename($file);
	} else {
		echo gallery_error(sprintf(_("Unknown file type: %s"), $file));
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
	} elseif (eregi("\.jpe?g(\.tmp)?\$", $file)) {
		$cmd = NetPBM($gallery->app->pnmtojpeg,
			      "--quality=" . $quality);
	} elseif (eregi("\.gif(\.tmp)?\$", $file)) {
		$cmd = NetPBM("ppmquant", "256") . " | " . NetPBM("ppmtogif");
	}

	if ($cmd) {
		return "$cmd > " . fs_import_filename($file);
	} else {
		echo gallery_error(sprintf(_("Unknown file type: %s"), $file));
		return "";
	}
}

function netPbm($cmd, $args="") {
	global $gallery;

	$cmd = fs_import_filename($gallery->app->pnmDir . "/$cmd");
	if (!isDebugging() && $cmd != "ppmquant") {
		// ppmquant doesn't like --quiet for some reason
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

function includeLayout($name, $skinname='') {
    global $gallery;

    if (!$skinname) {
        $skinname = $gallery->app->skinname;
    }

    $defaultname = dirname(__FILE__) . "/layout/$name";
    $fullname = dirname(__FILE__) . "/skins/$skinname/layout/$name";

    if (fs_file_exists($fullname) && !broken_link($fullname)) {
	include ($fullname);
    } elseif (fs_file_exists($defaultname) && !broken_link($defaultname)) {
	include ($defaultname);
    } else {
	echo gallery_error(sprintf(_("Problem including file %s"), $name));
    }
}

function includeHtmlWrap($name, $skinname='', $adds='') {

	// define these globals to make them available to custom text
	global $gallery;

	$domainname = dirname(__FILE__) . '/html_wrap/' . $_SERVER['HTTP_HOST'] . "/$name";

	if (!$skinname) {
		$skinname = $gallery->app->skinname;
	}

	if (fs_file_exists($domainname) && !broken_link($domainname)) {
		include ($domainname);
	}
	else {
		$defaultname = dirname(__FILE__) . "/html_wrap/$name";
		$fullname = dirname(__FILE__) . "/skins/$skinname/html_wrap/$name";
	    
		if (fs_file_exists($fullname) && !broken_link($fullname)) {
			include ($fullname);
		}
		elseif (fs_file_exists($defaultname) && !broken_link($defaultname)) {
			include ($defaultname);
		} else {
			include ("$defaultname.default");
		}
	}

	return 1;
}

/**
 * Wrapper around _getStyleSheetLink, its defines which stylesheet link is generated.
 * @return	string	$styleSheetLinks	The generated HTML <LINK> to load the stylesheets. Empty when already loaded.
 */
function getStyleSheetLink() {
    global $GALLERY_EMBEDDED_INSIDE;
    global $GALLERY_OK;

    static $styleSheetSet;

    $styleSheetLinks = '';
    
    if(! $styleSheetSet) {
	if (isset($GALLERY_OK) && $GALLERY_OK == false) {
	    $styleSheetLinks = _getStyleSheetLink("config");
	} else {
	    $styleSheetLinks = _getStyleSheetLink("base");

	    if ($GALLERY_EMBEDDED_INSIDE) {
		$styleSheetLinks .= _getStyleSheetLink("embedded_style");
	    } else {
		$styleSheetLinks .= _getStyleSheetLink("screen");
	    }
	}
	
	$styleSheetSet = true;
    }

    return $styleSheetLinks;
}

/**
 * Generates a HTML <link> to a css file.
 *
 * @param	string	$filename	Name of css file.
 * @param	string	$skinname	Optional skinname, if omitted and not embedded, default skin is used.
 * @return	string
 */
function _getStyleSheetLink($filename, $skinname='') {
    global $gallery;
    global $GALLERY_EMBEDDED_INSIDE;

    if (!$skinname && isset($gallery->app) && isset($gallery->app->skinname) && !$GALLERY_EMBEDDED_INSIDE) {
	$skinname = $gallery->app->skinname;
    }

    $sheetname = "skins/$skinname/css/$filename.css";
    $sheetpath = dirname(__FILE__) . "/$sheetname";

    $sheetdefaultdomainname = 'css/'. $_SERVER['HTTP_HOST'] ."/$filename.css";
    $sheetdefaultname = "css/$filename.css";
    $sheetdefaultpath = dirname(__FILE__) . '/' . $sheetdefaultname;

    if (fs_file_exists($sheetpath) && !broken_link($sheetpath)) {
	$file = $sheetname;
    } elseif (fs_file_exists($sheetdefaultpath) && !broken_link($sheetdefaultpath)) {
	$file = $sheetdefaultname;
    } elseif (fs_file_exists($sheetdefaultdomainname) && !broken_link($sheetdefaultdomainname)) {
	$file = $sheetdefaultdomainname;
    } else {
	$file = $sheetdefaultname. '.default';
    }

    $url = getGalleryBaseUrl() ."/$file";

    return "\n". '  <link rel="stylesheet" type="text/css" href="' .$url . '">';
}

function drawApplet($width, $height, $code, $archive, $album, $defaults, $overrides, $configFile, $errorMsg) {
	global $gallery, $GALLERY_EMBEDDED_INSIDE, $GALLERY_EMBEDDED_INSIDE_TYPE;
	global $_CONF; // for geeklog
	global $board_config; // for phpBB2

	if (file_exists($configFile)) {
		include($configFile);

		if (isset($configDefaults)) {
			$defaults = array_merge($defaults, $configDefaults);
		}
		if (isset($configOverrides)) {
			$overrides = array_merge($overrides, $configOverrides);
		}
	}

	$cookieInfo = session_get_cookie_params();

	$cookie_name = session_name();
	$cookie_value = session_id();
	$cookie_domain = $cookieInfo['domain'];
	$cookie_path = $cookieInfo['path'];

	// handle CMS-specific overrides
	if (isset($GALLERY_EMBEDDED_INSIDE)) {
		if ($GALLERY_EMBEDDED_INSIDE_TYPE == 'phpnuke') {
			$cookie_name = 'user';
			$cookie_value = $_COOKIE[$cookie_name];
		} elseif ($GALLERY_EMBEDDED_INSIDE_TYPE == 'GeekLog') {
			$cookie_name = $_CONF['cookie_session'];
			$cookie_value = $_COOKIE[$cookie_name];
		} elseif ($GALLERY_EMBEDDED_INSIDE_TYPE == 'phpBB2') {
			$cookie_name = $board_config['cookie_name'] . '_sid';
			$cookie_value = $_COOKIE[$cookie_name];
		 } elseif ($GALLERY_EMBEDDED_INSIDE_TYPE == 'mambo') {
			$cookie1_name = 'sessioncookie';
			$cookie1_value = $_COOKIE[$cookie1_name];
		}
	}

	$defaults['uiLocale'] = $gallery->language;
?>
	<object
		classid="clsid:8AD9C840-044E-11D1-B3E9-00805F499D93"
		codebase="http://java.sun.com/products/plugin/autodl/jinstall-1_4-windows-i586.cab#Version=1,4,0,0"
		width="<?php echo $width ?>" height="<?php echo $height ?>">
	<param name="code" value="<?php echo $code ?>">
	<param name="archive" value="<?php echo $archive ?>">
	<param name="type" value="application/x-java-applet;version=1.4">
	<param name="scriptable" value="false">
	<param name="progressbar" value="true">
	<param name="boxmessage" value="Downloading the Gallery Remote Applet">
	<param name="gr_url" value="<?php echo $gallery->app->photoAlbumURL ?>">
<?php if (isset($GALLERY_EMBEDDED_INSIDE)) { ?>
	<param name="gr_url_full" value="<?php echo makeGalleryUrl('gallery_remote2.php') ?>">
<?php } ?>
	<param name="gr_cookie_name" value="<?php echo $cookie_name ?>">
	<param name="gr_cookie_value" value="<?php echo $cookie_value ?>">
	<param name="gr_cookie_domain" value="<?php echo $cookie_domain ?>">
	<param name="gr_cookie_path" value="<?php echo $cookie_path ?>">
<?php if (isset($cookie1_name)) { ?>
	<param name="gr_cookie1_name" value="<?php echo $cookie1_name ?>">
	<param name="gr_cookie1_value" value="<?php echo $cookie1_value ?>">
<?php } ?>
	<param name="gr_album" value="<?php echo $album ?>">
<?php
	foreach ($defaults as $key => $value) {
		echo "\t<param name=\"GRDefault_". $key ."\" value=\"". $value ."\">\n";
	}

	foreach ($overrides as $key => $value) {
		echo "\t<param name=\"GROverride_". $key ."\" value=\"". $value ."\">\n";
	}
?>

	<comment>
		<embed
				type="application/x-java-applet;version=1.4"
				code="<?php echo $code ?>"
				archive="<?php echo $archive ?>"
				width="<?php echo $width ?>"
				height="<?php echo $height ?>"
				scriptable="false"
				progressbar="true"
				boxmessage="Downloading the Gallery Remote Applet"
				pluginspage="http://java.sun.com/j2se/1.4.2/download.html"
				gr_url="<?php echo $gallery->app->photoAlbumURL ?>"
<?php if (isset($GALLERY_EMBEDDED_INSIDE)) { ?>
				gr_url_full="<?php echo makeGalleryUrl('gallery_remote2.php') ?>"
<?php } ?>
				gr_cookie_name="<?php echo $cookie_name ?>"
				gr_cookie_value="<?php echo $cookie_value ?>"
				gr_cookie_domain="<?php echo $cookie_domain ?>"
				gr_cookie_path="<?php echo $cookie_path ?>"
<?php if (isset($cookie1_name)) { ?>
				gr_cookie1_name="<?php echo $cookie1_name ?>"
				gr_cookie1_value="<?php echo $cookie1_value ?>"
<?php } ?>
				gr_album="<?php echo $album ?>"
<?php
	foreach ($defaults as $key => $value) {
		echo "\t\t\t\tGRDefault_". $key ."=\"". $value ."\"\n";
	}

	foreach ($overrides as $key => $value) {
		echo "\t\t\t\tGROverride_". $key ."=\"". $value ."\"\n";
	}
?>
			<noembed
					alt="<?php echo $errorMsg ?>">
				<?php echo $errorMsg ?>
			</noembed>
		</embed>
	</comment>
</object>
<?php
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
		echo "<option value=\".root\">". _("Top Level") ."</option>";
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

		} elseif (!$readOnly && !$gallery->album->isRoot() && 
			 ($myAlbum == $gallery->album->getNestedAlbum($index)) && !$movePhoto )  {

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
		if ($myAlbum->isAlbum($i)) {
			$myName = $myAlbum->getAlbumName($i);
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
				} elseif (!$readOnly && !$gallery->album->isRoot() && 
					 ($nestedAlbum == $gallery->album->getNestedAlbum($index))) {
					echo "<option value=0> $val2 (". _("self"). ")</option>\n";
				} else {
					echo "<option value=\"$myName\"> $val2</option>\n";
				}
			}

			if (!$readOnly && !$gallery->album->isRoot() && 
			   ($nestedAlbum == $gallery->album->getNestedAlbum($index)) && !$movePhoto ) {

				// do nothing -- don't allow album move into its own tree

			} else {
				printNestedVals($level + 1, $myName, $movePhoto, $readOnly);
			}
		}
	}
}

/**
 * This function checks which tool
 * can we use for getting exif data from a photo.
 * returns false when no way works.
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

/** This function does not really looks if EXIF Data is there or not.
 * It just looks at the extension
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

	if (!$success) { // we were not able to get the capture date from exif... use file creation time
		$itemCaptureTimeStamp = filemtime($file);
	}

	if (!isDebugging()) {
		sprintf (_("Item Capture Date : %s"), strftime('%Y', $itemCaptureTimeStamp));
	}

	return $itemCaptureTimeStamp;
}

function doCommand($command, $args=array(), $returnTarget="", $returnArgs=array()) {

	if ($returnTarget) {
		$args["return"] = urlencode(makeGalleryHeaderUrl($returnTarget, $returnArgs));
	}
	$args["cmd"] = $command;
	return makeGalleryUrl("do_command.php", $args);
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

	if ($depth >= $gallery->app->albumTreeDepth) {
		return;
	}

	for ($i=1; $i <= $numPhotos; $i++) {
		set_time_limit($gallery->app->timeLimit);
		if ($myAlbum->isAlbum($i) && !$myAlbum->isHidden($i)) {
			$myName = $myAlbum->getAlbumName($i, false);
		        $nestedAlbum = new Album();
			$nestedAlbum->load($myName);
			if ($gallery->user->canReadAlbum($nestedAlbum)) {
				$val2 = $nestedAlbum->fields['title'];
				if (!strcmp($nestedAlbum->fields['display_clicks'], 'yes')
					&& !$gallery->session->offline) {
				    $val3 = "(" . pluralize_n2(ngettext("1 hit", "%d hits", $nestedAlbum->getClicks()), $nestedAlbum->getClicks()) . ")";
				} else {
				    $val3 = "";
				}
				if ($depth==0 && !$printedHeader++) {
					echo "<strong>". _("Sub-albums") .":</strong>\n";
				}
				echo "<div class='fineprint' style=\"white-space:nowrap; margin: 0px 0px 0px " . 20 * ($depth + 1) . "px\">";
				echo "<a href=\"";
				echo makeAlbumUrl($myName);
				echo "\">$val2 $val3</a>";
				echo "</div>\n";
				printChildren($myName, $depth+1);
			}
		}
	}
}

function printMicroChildren($albumName,$depth=0) {
        global $gallery;
        $printedHeader = 0;
        $myAlbum = new Album();
        $myAlbum->load($albumName);
        $numPhotos = $myAlbum->numPhotos(1);

        if ($depth >= $gallery->app->albumTreeDepth) {
                return;
        }
        for ($i=1; $i <= $numPhotos; $i++) {
                //set_time_limit($gallery->app->timeLimit);
                if ($myAlbum->isAlbum($i) && !$myAlbum->isHidden($i)) {
                        $myName = $myAlbum->getAlbumName($i, false);
                        $nestedAlbum = new Album();
                        $nestedAlbum->load($myName);
                        if ($gallery->user->canReadAlbum($nestedAlbum)) {
				if (strstr($nestedAlbum->gethighlightTag(), "img") === false) { continue; }
                                $val2 = $nestedAlbum->fields['title'];
                                if (!strcmp($nestedAlbum->fields['display_clicks'], 'yes')
                                        && !$gallery->session->offline) {
				    $val3 = "(" . pluralize_n2(ngettext("1 hit", "%d hits", $nestedAlbum->getClicks()), $nestedAlbum->getClicks()) . ")";
                                } else {
                                    $val3 = "";
                                }
				if ($depth==0 && !$printedHeader++) {
					echo "<br /><br /><strong>". _("Sub-albums") .":</strong><br />";
				}
                                echo "<a href=\"";
                                echo makeAlbumUrl($myName);
                                echo "\">" . $nestedAlbum->getHighlightTag($gallery->app->default["nav_thumbs_size"],
				  "class=\"nav_micro_img\"","$val2 $val3") . "</a>&nbsp";
                                printMicroChildren($myName, $depth+1);
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

function ordinal($num=1)
{
	$ords = array("th","st","nd","rd");
	$val = $num;
	if ((($num%=100)>9 && $num<20) || ($num%=10)>3) $num=0;
	return "$val" . $ords[$num];
}

function printMetaData($image_info) {
	// Print meta data
	print "<table border=\"1\">\n";
	$row = 0;
	foreach ($image_info as $info) {
		print "<tr>";
		if ($row == 0) {
			$keys = array_keys($info);
			foreach ($keys as $key) {
				print "<th>$key</th>";
			}
			print "</tr>\n<tr>";
		}
		
		foreach ($keys as $key) {
			print "<td>".$info[$key]."</td>";
		}
		$row++;
		print "</tr>\n";
	}
	print "</table>\n";
}	
function getExtension($filename) {
	$ext = ereg_replace(".*\.([^\.]*)$", "\\1", $filename);
	$ext = strtolower($ext);
	
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

/* This function checks wether and archive can be handled via Gallery
** It just uses the filename extension.
** If the extension is handable the decompressing tool is returned
** This was done, because e.g. zipfiles can be handled by rar also.
** So zipinfo and unzip are not needed.
*/
function canHandleArchive($ext) {
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
		break;

		default:
			return false;
		break;
	}
}

function getArchiveFileNames($archive, $ext) {
	global $gallery;

	$cmd="";
	$files=array();
	
	switch (canHandleArchive($ext)) {
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
	
	$tool=canHandleArchive($ext);
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

function processNewImage($file, $ext, $name, $caption, $setCaption="", $extra_fields=array(), $wmName="", $wmAlign=0, $wmAlignX=0, $wmAlignY=0, $wmSelect=0) {
    global $gallery;
    global $temp_files;

    /* Begin of code for the case the uploade file is an archive */
    if (acceptableArchive($ext)) {
	$tool = canHandleArchive($ext);
	if (empty($tool)) {
	    processingMsg(sprintf(_("Skipping %s (%s support not enabled)"), $name, $ext));
	    return;
	}

	/* Figure out what files inside the archive we can handle.
	** Put all Filenames into $files.
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
	/* Its a single file */
	/* remove %20 and the like from name */
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
	** need to prevent users from using original filenames that are purely numeric.
	** Purely numeric filenames mess up the rewriterules that we use for mod_rewrite 
	** specifically:
	** RewriteRule ^([^\.\?/]+)/([0-9]+)$	/~jpk/gallery/view_photo.php?set_albumName=$1&index=$2	[QSA]
	*/
	
	if (ereg("^([0-9]+)$", $mangledFilename)) {
	    $mangledFilename .= "_G";
	}
	
	set_time_limit($gallery->app->timeLimit);
	if (acceptableFormat($ext)) {
	    /*
	    ** Move the uploaded image to our temporary directory
	    ** using move_uploaded_file so that we work around
	    ** issues with the open_basedir restriction.
	    */
	    if (function_exists('move_uploaded_file')) {
		$newFile = tempnam($gallery->app->tmpDir, "gallery");
		if (move_uploaded_file($file, $newFile)) {
		    $file = $newFile;
		}
				
		/* Make sure we remove this file when we're done */
		$temp_files[$newFile] = 1;
	    }
		    
	    echo "\n<h3>******". sprintf(_("Adding %s"), $name) ."*****</h3>";

	    /* What should the caption be, if no caption was given by user ?
	    ** See captionOptions.inc.php for options
	    */
			
	    if (isset($gallery->app->dateTimeString)) {
		$dateTimeFormat = $gallery->app->dateTimeString;
	    } else {
		$dateTimeFormat = "%D %T";
	    }
	
	    if (empty($caption)) {
		switch ($setCaption) {
		    case 1:
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
	
	    if (!$extra_fields) {
		$extra_fields = array();
	    }

	    /* After all the preprocessing, NOW ADD THE element */		
	    $err = $gallery->album->addPhoto($file, $ext, $mangledFilename, $caption, "", $extra_fields, $gallery->user->uid, NULL, $wmName, $wmAlign, $wmAlignX, $wmAlignY, $wmSelect);
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

function createNewAlbum( $parentName, $newAlbumName = '', $newAlbumTitle = '', $newAlbumDesc = '') {
	global $gallery;

	// get parent album name
	$albumDB = new AlbumDB(FALSE);

	// set new album name from param or default
	$gallery->session->albumName = $albumDB->newAlbumName($newAlbumName);

	$gallery->album = new Album();
	$gallery->album->fields["name"] = $gallery->session->albumName;

	// guid is not created during new Album() as a performance optimization
	// it only needs to be created when an album is created or modified by adding or deleting photos
	$gallery->album->fields['guid'] = genGUID();

	// set title and description
	if (!empty($newAlbumTitle)) {
		$gallery->album->fields["title"] = $newAlbumTitle;
	}
	if (!empty($newAlbumDesc)) {
		$gallery->album->fields["description"] = $newAlbumDesc;
	}

	$gallery->album->setOwner($gallery->user->getUid());

	/* if this is a nested album, set nested parameters */
	if (!empty($parentName)) {
		$gallery->album->fields['parentAlbumName'] = $parentName;
		$parentAlbum = $albumDB->getAlbumByName($parentName);
		$parentAlbum->addNestedAlbum($gallery->session->albumName);
		$parentAlbum->save(array(i18n("Album \"{$gallery->album->fields['name']}\" created as a sub-album of \"$parentName\".")));
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

		$returnVal = $gallery->album->save(array(i18n("Album \"{$gallery->album->fields['name']}\" created as a sub-album of \"$parentName\".")));
	} else {
		$gallery->album->save(array(i18n("Root album \"{$gallery->album->fields['name']}\" created.")));
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

	if (!empty($returnVal)) {
		return $gallery->session->albumName;
	} else {
		return 0;
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

/* little function useful for debugging.  no calls to this should be in 
   committed code. */

function vd($x, $string="") {
	print "<pre>\n$string: ";
	var_dump($x);
	print "</pre>\n";
}       

/*returns a link to the docs, if present, or NULL */
function galleryDocs() {
	global $gallery;

	if (fs_file_exists(dirname(__FILE__) .'/docs/index.html')) {
		if (isset($gallery->app->photoAlbumURL)) {
			$url=$gallery->app->photoAlbumURL . '/docs/index.html';
		}
		else {  // When first time config without $gallery set.
			$url='../docs/index.html';
		}
		return $url;
	} else {
		return NULL;
	}
}

function getImVersion() {
	global $gallery;
	$version = array();

	exec($gallery->app->ImPath .'/convert -version', $results);

	$pieces = explode(' ', $results[0]);
	$version = $pieces[2];

	return $version[0];  // Only the first character
}
function compress_image($src, $out, $target, $quality, $keepProfiles=false) {
	global $gallery;

	if ($target === 'off') {
		$target = '';
	}
	$srcFile = fs_import_filename($src);
	$outFile = fs_import_filename($out);
	
	switch($gallery->app->graphics)	{
		case "NetPBM":
			exec_wrapper(toPnmCmd($src) .
				(($target > 0) ? (' | ' .NetPBM('pnmscale',
				" -xysize $target $target")) : '')
				. ' | ' . fromPnmCmd($out, $quality));
			/* copy over EXIF data if a JPEG if $keepProfiles is
			 * set. Unfortunately, we can't also keep comments. */ 
			if ($keepProfiles && eregi('\.jpe?g$', $src)) {
				if (isset($gallery->app->use_exif)) {
					exec_wrapper(fs_import_filename($gallery->app->use_exif, 1) . ' -te '
						. $srcFile . ' ' . $outFile);
				} else {
					processingMsg(_('Unable to preserve EXIF data (jhead not installed)') . "\n");
				}
			}
			break;
		case "ImageMagick":
			/* we just need the first digit = major version */
			$ImVersion = floor(getImVersion());
			// Set the keepProfiles parameter based on the version
			// of ImageMagick being used.  6.0.0 changed the
			// parameters again.
			switch ($ImVersion) {
			    case '5':
				$keepProfiles = ($keepProfiles) ? '' : ' +profile \'*\' ';
				break;
			    case '6':
				$keepProfiles = ($keepProfiles) ? '' : ' -strip ';
				break;
			    default:
				$keepProfiles = '';
			}

			/* Preserve comment, EXIF data if a JPEG if $keepProfiles is set. */

			$sizeCmd = '';
			$geometryCmd = '';
			if ($target) {
			    $sizeCmd = "-size ${target}x${target} ";
			    $geometryCmd = "-geometry ${target}x${target} ";
			}

			exec_wrapper(ImCmd('convert',
			   $srcFile .
			   " -quality $quality $sizeCmd $keepProfiles -coalesce $geometryCmd $outFile"
			));

			break;
		default:
		    echo debugMessage(_("You have no graphics package configured for use!"));
		break;
			
	}
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
		$ret= "<span class=\"commentIndication\">*</span>";
		$displayCommentLegend = 1;
	}
	return $ret;
}

function available_skins($description_only = false) {
	global $gallery;
	$version ='';
	$last_update ='';
	$possibleSkins = array();

	if (isset($gallery->app->photoAlbumURL)) {
		$base_url = $gallery->app->photoAlbumURL;
	}
	else {
		$base_url = "..";
	}

	$dir = dirname(__FILE__) . '/skins';
	$opts['none'] = 'No Skin';
	$descriptions="<dl>";
	$name = "<a href \"#\" onClick=\"document.config.skinname.options[0].selected=true; return false;\">". _("No Skin") ."</a>";
	$descriptions .= sprintf (_('<dt>%s</dt><dd>The original look and feel.</dd>'), $name);
	$skincount = 0;

	if (fs_is_dir($dir) && is_readable($dir) && $fd = fs_opendir($dir)) {
		while ($file = readdir($fd)) {
			$subdir="$dir/$file/css";
			$skincss="$subdir/screen.css";
			if (fs_is_dir($subdir) && fs_file_exists($skincss)) {
				$possibleSkins[] = $file;
			}
		}

		sort($possibleSkins);
		foreach($possibleSkins as $file) {
			$subdir="$dir/$file/css";
			$skininc="$dir/$file/style.def";
			$name="";
			$description="";
			$skincss="$subdir/screen.css";
			$skincount++;

			if (fs_file_exists($skininc)) {
				require($skininc);
			}

			if (empty($name)) {
				$name = $file;
			}

			$opts[$file]=$name;
			if (fs_file_exists("$dir/$file/images/screenshot.jpg")) {
				$screenshot = $base_url . "/skins/$file/images/screenshot.jpg";
			} elseif (fs_file_exists("$dir/$file/images/screenshot.gif")) {
				$screenshot = $base_url . "/skins/$file/images/screenshot.gif";
			} else {
				$screenshot = "";
			}

			if ($screenshot) {
				$name = popup_link($name, $screenshot, 1, false,
				500, 800, '', 'document.config.skinname.options['. $skincount. '].selected=true; ');
			}

			$descriptions.="\n<dt style=\"margin-top:5px;\">$name";
			if (!isset ($version)) {
				$version = _("unknown");
			}

			if (!isset($last_update)) {
				$last_update = _("unknown");
			}

			$descriptions .= '<span style="margin-left:10px; font-size:x-small">';
			$descriptions .= _("Version") .": $version";
			$descriptions .= "&nbsp;&nbsp;&nbsp;";
			$descriptions .= _("Last Update") . ": $last_update</span></dt>";
			$descriptions .= "<dd style=\"font-weight:bold; background-color:white;\">$description<br></dd>";
		}

		$descriptions .="\n</dl>";

		if ($description_only) {
			return $descriptions;
		} else {
			return $opts;
		}
	}
}

function available_frames($description_only=false) {

	$GALLERY_BASE=dirname(__FILE__);

	$opts=array(
			'none' => _("None"), 
			'dots' => _("Dots"), 
			'solid' => _("Solid"), 
			);
	$descriptions="<dl>" .
		"<dt>" . popup_link(_("None"), "frame_test.php?frame=none", 1)  . "</dt><dd>". _("No frames")."</dd>" .
		"<dt>" . popup_link(_("Dots"), "frame_test.php?frame=dots", 1)  . "</dt><dd>". _("Just a simple dashed border around the thumb.")."</dd>" .
		"<dt>" . popup_link(_("Solid"), "frame_test.php?frame=solid", 1) . "</dt><dd>". _("Just a simple solid border around the thumb.")."</dd>" ;
	$dir = $GALLERY_BASE . '/html_wrap/frames';
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
				$descriptions.="\n<dt>" . popup_link($name, "frame_test.php?frame=$file", 1) . "</a></dt><dd>$description</dd>";
			} else {
				if (false && isDebugging()) 
				{
					echo gallery_error(sprintf(_("Skipping %s."),
								$subdir));
				}
			}
		}
	} else {
		echo gallery_error(sprintf(_("Can't open %s"), $dir));
	}

	$descriptions.="\n</dl>";
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
		
		case 'comments_enabled':
			return $gallery->app->comments_enabled == 'yes';
		break;
		
		case 'allowComments':
			return $gallery->album->fields["perms"]['canAddComments'];
		break;
		
		case 'hasComments':
			return ($gallery->album->lastCommentDate("no") != -1);
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
		
		case 'watermarkingEnabled':
			return isset($gallery->app->watermarkDir);
		break;
		
		default:
			return false;
	}
}

function doctype() {
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">' . "\n";
}

function common_header($adds = array()) {
    $metaTagAdds = array();
    if(isset($adds['metaTags'])) {
	$metaTagAdds = $adds['metaTags'];
    }

// Do some meta tags
    metatags($metaTagAdds);

// Import CSS Style_sheet
    echo getStyleSheetLink();
	
// Set the Gallery Icon 
    echo "\n  <link rel=\"shortcut icon\" href=\"". makeGalleryUrl('images/favicon.ico') . "\">\n";
}

function metatags($adds = array()) {
    global $gallery;

    echo '<meta http-equiv="content-style-type" content="text/css">';
    echo "\n  ". '<meta http-equiv="content-type" content="Mime-Type; charset='. $gallery->charset .'">';
    echo "\n  ". '<meta name="content-language" content="' . str_replace ("_","-",$gallery->language) . '">';

    if(!empty($adds)) {
	foreach ($adds as $name => $content) {
	    echo "\n  ". '<meta name="'. $name .'" content="'. $content .'">';
	}
    } 
    echo "\n";
}

/**
 * Generates a link to w3c validator
 * 
 * @param	string	$file	file to validate, relative to gallery dir
 * @param	boolean	$valid	true/false wether we know the result ;)
 * @param	array	$arg	optional array with urlargs
 * @return	string	$link	HTML hyperlink
 */
function gallery_validation_link($file, $valid=true, $args = array()) {
    global $gallery;

    if (isset($gallery->app->devMode) && $gallery->app->devMode == "no") {
        return '';
    }

    $args['PHPSESSID'] = session_id();
    $url = makeGalleryURL($file, $args);

    if (!empty($file) && isset($gallery->app->photoAlbumURL)) {
        $uri = urlencode(eregi_replace("&amp;", "&", $url));
    }
    else {
        $uri = 'referer&amp;PHPSESSID='. $args['PHPSESSID'];
    }

    $link = '<a href="http://validator.w3.org/check?uri='. $uri .'">'.
    '<img border="0" src="http://www.w3.org/Icons/valid-html401" alt="Valid HTML 4.01!" height="31" width="88"></a>';

    if (!$valid) {
        $link .= _("Not valid yet");
    }

    return $link;
}

// uses makeAlbumURL
function album_validation_link($album, $photo='', $valid=true) {
	global $gallery;
	if ($gallery->app->devMode == "no") {
		return "";
	}
	$args=array();
	$args['PHPSESSID']=session_id();
	$link='<a href="http://validator.w3.org/check?uri='.
		urlencode(eregi_replace("&amp;", "&", 
					makeAlbumURL($album, $photo, $args))).
		'"> <img border="0" src="http://www.w3.org/Icons/valid-html401" alt="Valid HTML 4.01!" height="31" width="88"></a>';
	if (!$valid) {
		$link .= _("Not valid yet");
	}
	return $link;
}

/**
 * @return string	$location	Location where Gallery assmes the user. Can be 'core' or 'config'
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
	return "";
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
	return $return_array;	
}

/*
** This function strips slashes from an array Key
** 
** e.g. $foo[\'bar\'] will become $foo['bar']
**
** Created by Andrew Lindeman, 02/2004
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

function showOwner($owner) {
    global $GALLERY_EMBEDDED_INSIDE_TYPE;
    global $_CONF;				/* Needed for GeekLog */

	switch ($GALLERY_EMBEDDED_INSIDE_TYPE) {
		case 'GeekLog':
			return '<a href="'. $_CONF['site_url'] .'/users.php?mode=profile&uid='. $owner->uid .'">'. $owner->displayName() .'</a>';
		break;
		
		default:
			$name = $owner->displayName();

			if (!$owner->getEmail()) {
				return $name;
			} else {
				return '<a href="mailto:' . $owner->getEmail() . '">' . $name . '</a>';
			}
		break;
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

/**
 * This function displays tables with the Fields of an Photo
 * @param	integer	$index				Fields of this photo are displayed.
 * @param	array	$extra_fields		You need to give the extrafields ; hint: use getExtraFields()
 * @param	boolean	$withExtraFields	if true, then the extra fields are displayed
 * @param 	boolean	$withExif			if true, then the EXIF Data are displayed
 * @param	mixed	$full				Needed for getting dimensions of the photo
 * @param	boolean	$forceRefresh		Needed for getting EXIF Data
 */
function displayPhotoFields($index, $extra_fields, $withExtraFields=true, $withExif=true, $full=NULL, $forceRefresh=0) {
	global $gallery;

	$photo = $gallery->album->getPhoto($index);

	// if we have extra fiels and we want to show them, then get the values
	if (isset($extra_fields) && $withExtraFields) {
		$CF = getExtraFieldsValues($index, $extra_fields, $full);
		if (!empty($CF)) {
			$tables = array('' => $CF);
		}
	}

	if ($withExif && (isset($gallery->app->use_exif) || isset($gallery->app->exiftags)) &&
	(eregi("jpe?g\$", $photo->image->type))) {
		$myExif = $gallery->album->getExif($index, isset($forceRefresh));
		if (!empty($myExif) && !isset($myExif['Error'])) {

			$tables[_("EXIF Data")]  = $myExif;
		} elseif (isset($myExif['status']) && $myExif['status'] == 1) {
			echo '<p class="warning">'. _("Display of EXIF data enabled, but no data found.") .'</p>';
		}
	}

	if (!isset($tables)) {
		return;
	}

	foreach ($tables as $caption => $fields) {
		$customFieldsTable = new galleryTable();
		$customFieldsTable->setAttrs(array('class' => 'customFieldsTable'));
		$customFieldsTable->setCaption($caption, 'customFieldsTableCaption');

		foreach ($fields as $key => $value) {
			$customFieldsTable->addElement(array('content' => $key));
			$customFieldsTable->addElement(array('content' => ':'));
			$customFieldsTable->addElement(array('content' => $value));
		}
		echo $customFieldsTable->render();
	}
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

function includeTemplate($tplName, $skinname='') {
	global $gallery;

	if (!$skinname) {
		$skinname = $gallery->app->skinname;
	}

	$filename = dirname(__FILE__) . "/skins/$skinname/tpl/$tplName";
	if (fs_is_readable($filename)) {
		include($filename);
		return true;
	} else {
		return false;
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

/*
 * Returns an array of the parent album names for a given child
 * album.
 * Array is reverted, so the first Element is the topalbum.
 * If you set $addChild true, then the child album itself is added as last Element.
 * Based on code by: Dariush Molavi
 */

function getParentAlbums($childAlbum, $addChild=false) {
	$pAlbum = $childAlbum;
	$parentNameArray = array();

	if ($addChild == true) {
		$parentNameArray[$pAlbum->fields['name']] = $pAlbum->fields['title'];
	}

	while ($pAlbum = $pAlbum->getParentAlbum(FALSE)) {
		$parentNameArray[$pAlbum->fields['name']] = $pAlbum->fields['title'];
	}

	$parentNameArray = array_reverse($parentNameArray);

	return $parentNameArray;
}

function getIconText($iconName = '', $altText = '', $overrideMode = '', $useBrackets = true) {
	global $gallery;
	$text = $altText;

	if (!empty($overrideMode)) {
		$iconMode = $overrideMode;
	} elseif (isset($gallery->app->useIcons)) {
		$iconMode = $gallery->app->useIcons;
	} else {
		$iconMode = 'no';
	}

	if ($iconMode != "no" && $iconName != '') {
		if ($iconMode == 'both') {
			$altText = '';
		}

		if (file_exists(dirname(__FILE__) .'/images/icons/'. $iconName)) {
			$imgSrc = $gallery->app->photoAlbumURL .'/images/icons/'. $iconName;
			$linkText = "<img src=\"$imgSrc\" title=\"$altText\" alt=\"$altText\" style=\"border: none;\">";

			if ($iconMode == "both") {
				$linkText .= "<br>$text";
			}
		}
	}

	if (empty($linkText)) {
		if($useBrackets) {
			$linkText = '['. $text . ']';
		} else {
			$linkText = $text;
		}
	}

	return $linkText;
}

function makeIconMenu($iconElements, $align = 'left', $closeTable = true, $linebreak = false) {
	global $gallery;

	if (empty($iconElements)) {
		return "";
	}

	// For rtl/ltr stuff
	if ($gallery->direction == 'rtl') {
		$align = ($align == 'left') ? 'right' : 'left';
	}

	$html = "\n". '<table id="menu" align="'. $align .'"><tr>';
	$i = 0;
	foreach ($iconElements as $element) {
		$i++;
		if (stristr($element,'</a>')) {
			$html .= "\n\t". '<td>'. $element .'</td>';
		} else {
			$html .= "\n\t". '<td class="noLink">'. $element .'</td>';
		}
		if($i > sizeof($iconElements)/2 && $linebreak) {
			$html .= "\n</tr>\n</tr>";
			$i=0;
		}
	}

	if ($closeTable == true) {
		$html .= "</tr>\n</table>";
	}

	return $html;
}

/* Ecard Function begin */ 
function get_ecard_template($template_name) {
    global $gallery;

    $error = false;
    $file_data = "";
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

function parse_ecard_template($ecard,$ecard_data) {
    global $gallery;

    $ecard_data = preg_replace ("/<%ecard_sender_email%>/", $ecard["email_sender"], $ecard_data);
    $ecard_data = preg_replace ("/<%ecard_sender_name%>/", $ecard["name_sender"], $ecard_data);
    $ecard_data = preg_replace ("/<%ecard_image_name%>/", $ecard["image_name"], $ecard_data);
    $ecard_data = preg_replace ("/<%ecard_message%>/", preg_replace ("/\r?\n/", "<BR>\n", htmlspecialchars($ecard["message"])), $ecard_data);
    $ecard_data = preg_replace ("/<%ecard_reciepient_email%>/", $ecard["email_recepient"], $ecard_data);
    $ecard_data = preg_replace ("/<%ecard_reciepient_name%>/", $ecard["name_recepient"], $ecard_data);
    $ecard_data = preg_replace ("/<%ecard_stamp%>/", $ecard["stamp"], $ecard_data);


    $imagePath = $gallery->app->albumDir . str_replace ($gallery->app->albumDirURL, '', $ecard["image_name"]);
    list ($width, $height) = getDimensions(urldecode($imagePath));
    $widthReplace = ($width < 200) ? 'width="500"' : '';
    $ecard_data = preg_replace ("/<%ecard_width%>/", $widthReplace, $ecard_data);
	
    return $ecard_data;
  }

  function send_ecard($ecard,$ecard_HTML_data,$ecard_PLAIN_data) {
  	$ecard_mail = new htmlMimeMail();
  	$ecard_image = $ecard_mail->getFile($ecard["image_name"]);

  	if (preg_match_all("/(<IMG.*SRC=\")(.*)(\".*>)/Uim", $ecard_HTML_data, $matchArray)) {
  		for ($i=0; $i<count($matchArray[0]); ++$i) {
  			$ecard_image = $ecard_mail->getFile($matchArray[2][$i]);
  		}
  	}
  	$ecard_mail->setHtml($ecard_HTML_data, $ecard_PLAIN_data,'./');
  	$ecard_mail->setFrom($ecard["name_sender"].'<'.$ecard["email_sender"].'>');
  	if (empty($ecard['subject'])) {
  		$ecard['subject'] = sprintf(_("%s sent you an E-C@rd."), $ecard["name_sender"]);
  	}
  	$ecard_mail->setSubject($ecard['subject']);
  	$ecard_mail->setReturnPath($ecard["email_sender"]);

  	$result = $ecard_mail->send(array($ecard["email_recepient"]));

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
function array_sort_by_fields(&$data, $sortby, $order = 'asc', $caseSensitive = true, $keepIndexes = false) {
	static $sort_funcs = array();
	static $code;

	$order = ($order == 'asc') ? 1 : -1;

	if (empty($sort_funcs[$sortby])) {
		if ($caseSensitive) {
			$code = "
	    if( \$a['$sortby'] == \$b['$sortby'] ) { 
	        return 0;
	    };
	    if ( \$a['$sortby'] > \$b['$sortby'] ) {
	        return $order;
	    } else {
	        return -1 * $order;
	    }";
		}
		else {
			$code = "
	    if(strtoupper(\$a['$sortby']) == strtoupper(\$b['$sortby'])) { 
	        return 0;
	    };
	    if (strtoupper(\$a['$sortby']) > strtoupper(\$b['$sortby'])) {
	        return $order;
	    } else {
	        return -1 * $order;
	    }";
		}

		$sort_func = $sort_funcs[$sortby] = create_function('$a, $b', $code);
	} else {
		$sort_func = $sort_funcs[$sortby];
	}

	debugMessage($code, __FILE__, __LINE__,3);

	if($keepIndexes) {
		uasort($data, $sort_func);
	} else {
		usort($data, $sort_func);
	}
}

/**
 * @param	string	Optional former searchh string
 * @param	string	Optional alignment
 * @return	string	HTML code that contains a form for entering the searchstring
 * @author	Jens Tkotz
 */
function addSearchForm($formerSearchString = '', $align = '') {
    $html = '';

    $html .= makeFormIntro('search.php', array(
    	'name'     => 'search_form',
    	'style'    => "text-align: $align",
    	'class'   => 'search')
    );
                
    $html .= "\t". _("Search:");
    $html .= '<input class="searchform" type="text" name="searchstring" value="'. $formerSearchString .'" size="25">';
    $html .= "\n</form>\n";

    return $html;
}

require_once(dirname(__FILE__) . '/lib/lang.php');
require_once(dirname(__FILE__) . '/lib/Form.php');
require_once(dirname(__FILE__) . '/lib/voting.php');
require_once(dirname(__FILE__) . '/lib/albumItem.php');
require_once(dirname(__FILE__) . '/lib/mail.php');

?>
