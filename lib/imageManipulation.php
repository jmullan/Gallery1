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
 * Functions that provide possibilities to manipulate images
 *
 * @package	ImageManipulation
 */

/**
 *  Valid return codes:
 *  0:  File was not resized, no processing to be done
 *  1:  File resized, process normally
 *  2:  Existing resized file should be removed
 */
function resize_image($src, $dest, $target = 0, $target_fs = 0, $keepProfiles = 0, $createThumbnail = false, $quality = 0) {
    debugMessage(sprintf(_("Resizing Image: %s"), $src), __FILE__, __LINE__);

    global $gallery;

    if (!strcmp($src,$dest)) {
        $useTemp = true;
        $out = "$dest.tmp";
    }
    else {
        $out = $dest;
        $useTemp = false;
    }

    $type = getExtension($src);
    list($width, $height) = getDimensions($src);

    if ($type != 'jpg' && $type != 'png') {
        $target_fs = 0; // can't compress other images
    }

    if ($target === 'off') {
        $target = 0;
    }

    if ($quality == 0) {
	$quality = $gallery->app->jpegImageQuality;
    }

    /* Check for images smaller then target size, don't blow them up. */
    if ((empty($target) || ($width <= $target && $height <= $target))
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
    $target = min($target, max($width, $height));

    if ($target_fs == 0) {
        compressImage($src, $out, $target, $quality, $keepProfiles, $createThumbnail);
    } else {
        $filesize = (int) fs_filesize($src) >> 10;
        $max_quality = $gallery->app->jpegImageQuality;
        $min_quality = 5;
        $max_filesize = $filesize;

        if (!isset($quality)) {
            $quality = $gallery->album->fields['last_quality'];
        }
        processingMsg("&nbsp;&nbsp;&nbsp;". sprintf(_("target file size %d kbytes"), $target_fs)."\n");

        do {
            compressImage($src, $out, $target, $quality, $keepProfiles, $createThumbnail);

            $prev_quality = $quality;
            printf(_("-> file size %d kbytes"), round($filesize));
            processingMsg("&nbsp;&nbsp;&nbsp;" . sprintf(_("trying quality %d%%"), $quality));
            clearstatcache();
            $filesize = (int)fs_filesize($out) >> 10;
            if ($filesize < $target_fs) {
                $min_quality = $quality;
            } elseif ($filesize > $target_fs){
                $max_quality = $quality;
                $max_filesize = $filesize;
            } elseif ($filesize == $target_fs){
                $min_quality = $quality;
                $max_quality = $quality;
                $max_filesize = $filesize;
            }
            $quality = ($max_quality + $min_quality)/2;
            $quality = round($quality);
            if ($quality == $prev_quality) {
                if ($filesize == $max_filesize) {
                    $quality--;
                } else {
                    $quality++;
                }
            }
        } while ($max_quality-$min_quality > 2 &&
        abs(($filesize-$target_fs)/$target_fs) > .02 );

        $gallery->album->fields['last_quality'] = $prev_quality;
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

/**
 * In order for pnmcomp to support watermarking from formats other than pnm, the watermark
 * first needs to be converted to .pnm. Second the alpha channel needs to be decomposed as a
 * second image
 *
 * Returns a list of 2 temporary files (overlay, and alphamask), these files should be deleted (unlinked)
 * by the calling function
 */
function netpbm_decompose_image($input, $format) {
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
    if (isDebugging()) {
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
    if (strlen($wmName)) {
        switch($gallery->app->graphics) {
            case 'ImageMagick':
                $overlayFile = $wmName;
            break;

            case 'NetPBM':
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
                echo debugMessage(_("You have no graphics package configured for use!"), __FILE__, __LINE__);
            return 0;
        }
    } else {
        echo gallery_error(_("No watermark name specified!"));
        return 0;
    }

    // Set or Clip $wmAlignX and $wmAlignY
    switch ($wmAlign) {
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
    
            // clip left side
            if ($wmAlignX < 1) {
                $wmAlignX = 0;
            }
            // clip right side
            elseif ($wmAlignX > ($srcSize[0] - $overlaySize[0])) {
                $wmAlignX = ($srcSize[0] - $overlaySize[0]);
            }
            // clip top
            if ($wmAlignY < 1) {
                $wmAlignY = 0;
            }
            // clip bottom
            elseif ($wmAlignY > ($srcSize[1] - $overlaySize[1])) {
                $wmAlignY = ($srcSize[1] - $overlaySize[1]);
            }
        break;
    } // end switch ($wmAlign)

    $wmAlignX = floor($wmAlignX);
    $wmAlignY = floor($wmAlignY);

    // Execute
    switch($gallery->app->graphics) {
        case 'ImageMagick':
            $settings = "-geometry +$wmAlignX+$wmAlignY $overlayFile";
            exec_wrapper(ImCmd('composite', '', $src, '', $out, $settings));
        break;

        case 'NetPBM':
            $args  = "-yoff=$wmAlignY -xoff=$wmAlignX ";
            if ($alphaFile) {
                $args .= "-alpha=$alphaFile ";
            }
            $args .= $overlayFile;
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
        switch($gallery->app->graphics) {
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
                    $destOperator = '-rotate 90';
                } elseif (!strcmp($target, '180')) {
                    $destOperator = '-rotate 180';
                } elseif (!strcmp($target, '90')) {
                    $destOperator = '-rotate -90';
                } elseif (!strcmp($target, 'fv')) {
                    $destOperator = '-flip';
                } elseif (!strcmp($target, 'fh')) {
                    $destOperator = '-flop';
                } elseif (!strcmp($target, 'tr')) {
                    $destOperator = '-affine 0,1,1,0,0,0 -transform';
                } elseif (!strcmp($target, 'tv')) {
                    $destOperator = '-affine 0,-1,-1,0,0,0 -transform';
                } else {
                    $destOperator = '';
                }
    
                exec_wrapper(ImCmd('convert', '', $srcFile, $destOperator, $outFile));
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

function cut_image($src, $dest, $offsetX, $offsetY, $width, $height) {
    echo debugMessage(_("Cropping Image"),__FILE__, __LINE__);
    global $gallery;

    if (!strcmp($src,$dest)) {
        $useTemp = true;
        $out = "$dest.tmp";
    }
    else {
        $out = $dest;
    }

    $srcFile = fs_import_filename($src);
    $outFile = fs_import_filename($out);

    switch($gallery->app->graphics) {
        case "NetPBM":
            exec_wrapper(toPnmCmd($src) .
            " | " .
            NetPBM("pnmcut") .
            " $offsetX $offsetY $width $height" .
            " | " .
            fromPnmCmd($out));
        break;
        case "ImageMagick":
	    if (floor(getImVersion()) < 6) {
		$repage = "-page +0+0";
	    } else {
		$repage = "+repage";
	    }
            exec_wrapper(ImCmd('convert', '', $srcFile, "-crop ${width}x${height}+${offsetX}+${offsetY} $repage", $outFile));
        break;
        default:
            if (isDebugging()) {
                echo "<br>" . _("You have no graphics package configured for use!") ."<br>";
                return 0;
            }
        break;
    }

    if (isDebugging(2)) {
        echo "Source";
        getDimensions($src);
        echo "Dest";
        getDimensions($dest);
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

function cropImageToRatio($src, $dest, $destSize, $ratio) {
    list($width, $height) = getDimensions($src);
    $size = 0;

    switch($ratio) {
        case '1/1':
        debugMessage(sprintf(_("Generating squared Version to %dpx"), $destSize), __FILE__, __LINE__);

        if($width > $height && $height > $destSize) {
            $offsetX = round(($width - $height)/2);
            $offsetY = 0;
            $size = $height;
        }
        elseif ($height > $width && $width > $destSize) {
            $offsetX = 0;
            $offsetY = round(($height - $width)/2);
            $size = $width;
        }

        if($size >0) {
            $ret = cut_image($src, $dest,
            $offsetX,
            $offsetY,
            $size,
            $size);
        }
        else {
            debugMessage(_("No Cropping Done"), __FILE__, __LINE__);
            $ret = false;
        }
        break;
    }
    return $ret;
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
        $cmd = "jpegtopnm";
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
		$quality = $gallery->app->jpegImageQuality;
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
		return '';
	}
}

function netPbm($cmd, $args = '') {
	global $gallery;

	$cmd = fs_import_filename($gallery->app->pnmDir . "/$cmd");
	if (!isDebugging() && $cmd != 'ppmquant') {
		// ppmquant doesn't like --quiet for some reason
		$cmd  .= " --quiet";
	}
	$cmd .= " $args";
	return $cmd;
}

/**
 * Returns the command line command for ImageMagick depending on Version.
 * If no Version is detected, we assume Version 5.x
 * @param   string  $cmd	  The command, e.g. convert
 * @param   string  $srcOperator
 * @param   string  $src	  The sourcefile the command is perfomed on
 * @param   string  $dest 	  Optional destination file
 * @param   string  $destOperator
 * @return  $string $cmdLine	  The complete commandline
 */
function ImCmd($cmd, $srcOperator, $src, $destOperator, $dest, $settings = '') {
    global $gallery;
    static $ImVersion;
    
    if(empty($ImVersion)) {
        $ImVersion = floor(getImVersion());
    }
    $cmd = fs_import_filename($gallery->app->ImPath . "/$cmd");
    
    if($ImVersion < 6) {
        $cmdLine = "$cmd $settings $src $dest";
    }
    else {
        $cmdLine = "$cmd $settings $srcOperator $src $destOperator $dest";
    }
    
    return $cmdLine;
}

function compressImage($src = '', $dest = '', $targetSize = 0, $quality, $keepProfiles = false, $createThumbnail = false) {
    global $gallery;
    static $ImVersion;

    if (empty($src) || empty($dest) || empty($targetSize)) {
        echo gallery_error(_("Not all necessary params for resizing given."));
        echo debugMessage(sprintf(_("Resizing params: src: %s, dest : %s, targetSize: %s"), $src, $dest, $targetSize), __FILE__, __LINE__);
        return false;
    }

    if(empty($ImVersion)) {
        $ImVersion = floor(getImVersion());
    }

    if ($targetSize === 'off') {
        $targetSize = '';
    }
    $srcFile = fs_import_filename($src);
    $destFile = fs_import_filename($dest);

    switch($gallery->app->graphics)	{
        case "NetPBM":
            exec_wrapper(toPnmCmd($src) . ' | ' .
            NetPBM('pnmscale', " -xysize $targetSize $targetSize")  . ' | ' .
            fromPnmCmd($dest, $quality)
            );
            /* copy over EXIF data if a JPEG if $keepProfiles is
            * set. Unfortunately, we can't also keep comments. */
            if ($keepProfiles && eregi('\.jpe?g$', $src)) {
                if (isset($gallery->app->use_exif)) {
                    exec_wrapper(fs_import_filename($gallery->app->use_exif, 1) . ' -te '
                    . $srcFile . ' ' . $destFile);
                } else {
                    processingMsg(_('Unable to preserve EXIF data (jhead not installed)') . "\n");
                }
            }
        break;
        case "ImageMagick":
            if(!$createThumbnail) {
                /* Set the keepProfiles parameter based on the version of ImageMagick being used.
                * 6.0.0 changed the parameters again.
                * Preserve comment, EXIF data if a JPEG if $keepProfiles is set.
                */
                switch ($ImVersion) {
                    case '5':
                        $keepProfiles = ($keepProfiles) ? '' : ' +profile \'*\' ';
                    break;
                    case '6':
                        $keepProfiles = ($keepProfiles) ? '' : ' -strip ';
                    break;
                    default:
                        $keepProfiles = '';
                    break;
                }
            }

	    $settings = '';
	    $destOperator = '';
	    $srcOperator = '';
	    /* If not targetSize is given, then this is just for setting (decreasing) quality */
	    if ($ImVersion < 6) {
                $settings = "-quality $quality";
	    }
            else {
	        $destOperator = "-quality $quality";
            }

            if ($targetSize) {
                if ($createThumbnail) {
		    if ($ImVersion < 6) {
                        $settings .= " -resize ${targetSize}x${targetSize}";
                    }
		    else {
                        $srcOperator = "-size ${targetSize}x${targetSize}";
                        $destOperator .= " -thumbnail ${targetSize}x${targetSize}";
                    }
                }
                else {
		    if ($ImVersion < 6) {
                        $settings .= " -resize ${targetSize}x${targetSize} $keepProfiles";
                    }
		    else {
			if($gallery->app->IM_HQ == 'yes') {
			    echo debugMessage(_("Using IM high quality"), __FILE__, __LINE__, 3);
			}
			else {
			    $srcOperator = "-size ${targetSize}x${targetSize}";
			    echo debugMessage(_("Not using IM high quality"), __FILE__, __LINE__, 3);
			}
                        $destOperator .= " -resize ${targetSize}x${targetSize} $keepProfiles";
                    }
                }
                //$geometryCmd = "-coalesce -geometry ${targetSize}x${targetSize} ";
            }
	

            return exec_wrapper(ImCmd('convert',$srcOperator, $srcFile, $destOperator, $destFile, $settings));
            //return ImCmd('convert', $srcFile, $destFile, "-quality $quality $sizeCmd $keepProfiles $geometryCmd");
        break;
        default:
            echo debugMessage(_("You have no graphics package configured for use!"), __FILE__, __LINE__);
            return false;
        break;

    }
}
?>
