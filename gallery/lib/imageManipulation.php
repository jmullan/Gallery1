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
	debugMessage(sprintf(gTranslate('common', "Resizing Image: %s."), $src), __FILE__, __LINE__);

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
	if ((empty($target) || ($width <= $target && $height <= $target)) &&
	    (empty($target_fs) || ((int) fs_filesize($src) >> 10) <= $target_fs))
	{
		echo debugMessage("&nbsp;&nbsp;&nbsp;". gTranslate('common', "No resizing required."), __FILE__, __LINE__,1);

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
	}
	else {
		$filesize = (int) fs_filesize($src) >> 10;
		$max_quality = $gallery->app->jpegImageQuality;
		$min_quality = 5;
		$max_filesize = $filesize;

		if (!isset($quality)) {
			$quality = $gallery->album->fields['last_quality'];
		}

		processingMsg("&nbsp;&nbsp;&nbsp;". sprintf(gTranslate('common', "Target file size: %d kbytes."), $target_fs)."\n");

		$loop = 0;
		do {
			$loop ++;
			processingMsg("Loop: $loop");
			compressImage($src, $out, $target, $quality, $keepProfiles, $createThumbnail);

			$prev_quality = $quality;
			printf(gTranslate('common', "-> File size: %d kbytes"), round($filesize));
			processingMsg("&nbsp;&nbsp;&nbsp;" . sprintf(gTranslate('common', "Trying quality: %d%%."), $quality));
			clearstatcache();
			$filesize = (int)fs_filesize($out) >> 10;
			
			if ($filesize < $target_fs) {
				$min_quality = $quality;
			}
			elseif ($filesize > $target_fs){
				$max_quality = $quality;
				$max_filesize = $filesize;
			}
			elseif ($filesize == $target_fs){
				$min_quality = $quality;
				$max_quality = $quality;
				$max_filesize = $filesize;
			}
			
			$quality = ($max_quality + $min_quality)/2;
			$quality = round($quality);
			
			if ($quality == $prev_quality) {
				if ($filesize == $max_filesize) {
					$quality--;
				}
				else {
					$quality++;
				}
			}
		} while ($max_quality-$min_quality > 2 && abs(($filesize-$target_fs)/$target_fs) > .02 );

		$gallery->album->fields['last_quality'] = $prev_quality;
		
		printf(gTranslate('common', "-> File size: %d kbytes"), round($filesize));
		processingMsg(gTranslate('common', "Done."));
	}

	if (fs_file_exists("$out") && fs_filesize("$out") > 0) {
		if ($useTemp) {
			fs_copy($out, $dest);
			fs_unlink($out);
		}
		return 1;
	}
	else {
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
	
	$overlay	= tempnam($gallery->app->tmpDir, "netpbm_");
	$alpha		= tempnam($gallery->app->tmpDir, "netpbm_");

	switch ($format) {
		case 'png':
			$getOverlay = netpbm("pngtopnm", "$input > $overlay");
			$getAlpha   = netpbm("pngtopnm", "-alpha $input > $alpha");
			break;

		case 'gif':
			$getOverlay = netpbm("giftopnm", "--alphaout=$alpha $input > $overlay");
			break;

		case 'tif':
			$getOverlay = netpbm("tifftopnm", "-alphaout=$alpha $input > $overlay");
			break;
	}

	exec_wrapper($getOverlay);

	if (isset($getAlpha)) {
		exec_wrapper($getAlpha);
	}

	return array($overlay, $alpha);
}

function watermark_image($src, $dest, $wmName, $wmAlphaName, $wmAlign, $wmAlignX, $wmAlignY) {
	global $gallery;

	if (!strcmp($src,$dest)) {
		$useTemp = true;
		$out = "$dest.tmp";
	}
	else {
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

			case 'Netpbm':
				if (eregi('\.png$',$wmName, $regs)) {
					list ($overlayFile, $alphaFile) = netpbm_decompose_image($wmName, "png");
					$tmpOverlay = 1;
				}
				elseif (eregi('\.tiff?$',$wmName, $regs)) {
					list ($overlayFile, $alphaFile) = netpbm_decompose_image($wmName, "tif");
					$tmpOverlay = 1;
				}
				elseif (eregi('\.gif$',$wmName, $regs)) {
					list ($overlayFile, $alphaFile) = netpbm_decompose_image($wmName, "gif");
					$tmpOverlay = 1;
				}
				else {
					$alphaFile = $wmName;
					if (strlen($wmAlphaName)) {
						$overlayFile = $wmAlphaName;
					}
				}
				break;

			default:
				echo debugMessage(gTranslate('common', "You have no graphics package configured for use!"), __FILE__, __LINE__);
				return false;
		}
	}
	else {
		echo gallery_error(gTranslate('common', "No watermark name specified!"));
		return false;
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
				}
				else {
					$wmAlignX = $regs[1];
				}
			}
			else {
				$wmAlignX = 0;
			}
	
			if (ereg('([0-9]+)(\%?)', $wmAlignY, $regs)) {
				if ($regs[2] == '%') {
					$wmAlignY = round($regs[1] / 100 * ($srcSize[1] - $overlaySize[1]));
				}
				else {
					$wmAlignY = $regs[1];
				}
			}
			else {
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

	if ($wmAlignX >= 0) {
		$wmAlignX = "+$wmAlignX";
	}

	if ($wmAlignY >= 0) {
		$wmAlignY = "+$wmAlignY";
	}

	// Execute
	switch($gallery->app->graphics) {
		case 'ImageMagick':
			$srcOperator = "-geometry $wmAlignX$wmAlignY $overlayFile";
			exec_wrapper(ImCmd(fs_executable('composite'), $srcOperator, $src, '', $out));
		break;

		case 'Netpbm':
			$args  = "-yoff=$wmAlignY -xoff=$wmAlignX ";
			if ($alphaFile) {
				$args .= "-alpha=$alphaFile ";
			}
			$args .= $overlayFile;
			exec_wrapper(toPnmCmd($src) ." | ". netpbm($gallery->app->pnmcomp, $args) ." | " . fromPnmCmd($out));
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
	}
	else {
		return 0;
	}
} // end watermark_image()

/**
 * Rotates an images.
 *
 * @param string $src	   filename of the source image.
 * @param string $dest	  filename of the destination image (can be the same).
 * @param string $target	degree of rotation.
 * @param string $type	  filetype.
 * @return boolean		  true if successfully rotated.
 */
function rotate_image($src, $dest, $target, $type) {
	global $gallery;

	if (!strcmp($src, $dest)) {
		$useTemp = true;
		$out = "$dest.tmp";
	}
	else {
		$out = $dest;
	}

	$outFile = fs_import_filename($out, 1);
	$srcFile = fs_import_filename($src, 1);

	$type = strtolower($type);
	if (!empty($gallery->app->use_jpegtran) &&
	    ($type === 'jpg' || $type === 'jpeg')) 
	{
		debugMessage(gTranslate('common', "Using jpegtran for rotation"), __FILE__, __LINE__, 3);

		if (!strcmp($target, '90')) {
			$args = '-rotate 90';
		}
		elseif (!strcmp($target, '180')){
			$args = '-rotate 180';
		}
		elseif (!strcmp($target, '-90')) {
			$args = '-rotate 270';
		}
		elseif (!strcmp($target, 'fv')) {
			$args = '-flip vertical';
		}
		elseif (!strcmp($target, 'fh')) {
			$args = '-flip horizontal';
		}
		elseif (!strcmp($target, 'tr')) {
			$args = '-transpose';
		}
		elseif (!strcmp($target, 'tv')) {
			$args = '-transverse';
		}
		else {
			$args = '';
		}

		$path = $gallery->app->use_jpegtran;
		// -copy all ensures all headers (i.e. EXIF) are copied to the rotated image
		exec_internal(fs_import_filename($path, 1) . " $args -trim -copy all -outfile $outFile $srcFile");
	}
	else {
		switch($gallery->app->graphics) {
			case "Netpbm":
				debugMessage(gTranslate('common', "Using Netpbm for rotation"), __FILE__, __LINE__, 3);

				if (!strcmp($target, '90')) {
					$args = '-cw';
				}
				elseif (!strcmp($target, '180')) {
					$args = '-r180';
				}
				elseif (!strcmp($target, '-90')) {
					$args = '-ccw';
				}
				elseif (!strcmp($target, 'fv')) {
					$args = '-tb';
				}
				elseif (!strcmp($target, 'fh')) {
					$args = '-lr';
				}
				elseif (!strcmp($target, 'tr')) {
					$args = '-transpose';
				}
				elseif (!strcmp($target, 'tv')) {
					// Requires Netpbm 10.13 and higher
					$args = '-xform=transpose';
				}
				else {
					$args = '';
				}

				exec_wrapper(toPnmCmd($src) . ' | ' .
					     netpbm('pnmflip', $args) .
					     ' | ' . fromPnmCmd($out)
				);

				// copy exif headers from original image to rotated image
				if (isset($gallery->app->use_exif)) {
					$path = $gallery->app->use_exif;
					exec_internal(fs_import_filename($path, 1) . " -te $srcFile $outFile");
				}

			break;

			case "ImageMagick":
				debugMessage(gTranslate('common', "Using ImageMagick for rotation"), __FILE__, __LINE__, 3);
				if (!strcmp($target, '90')) {
					$destOperator = '-rotate 90';
				}
				elseif (!strcmp($target, '180')) {
					$destOperator = '-rotate 180';
				}
				elseif (!strcmp($target, '-90')) {
					$destOperator = '-rotate -90';
				}
				elseif (!strcmp($target, 'fv')) {
					$destOperator = '-flip';
				}
				elseif (!strcmp($target, 'fh')) {
					$destOperator = '-flop';
				}
				elseif (!strcmp($target, 'tr')) {
					$destOperator = '-affine 0,1,1,0,0,0 -transform';
				}
				elseif (!strcmp($target, 'tv')) {
					$destOperator = '-affine 0,-1,-1,0,0,0 -transform';
				}
				else {
					$destOperator = '';
				}

				$status = exec_wrapper(ImCmd(fs_executable('convert'), '', $srcFile, $destOperator, $outFile));

			break;

			default:
				if (isDebugging()) {
					echo "<br>". gTranslate('common', "You have no graphics package configured for use!") ."<br>";
				}
				return false;
			break;
		}
	}

	if (fs_file_exists("$out") && fs_filesize("$out") > 0) {
		if ($useTemp) {
			fs_copy($out, $dest);
			fs_unlink($out);
		}
		return true;
	}
	else {
		return false;
	}
}

/**
 * The width and height give the size of the image that remains after cropping
 * The offsets specify the location of the upper left corner of the cropping region
 * measured downward and rightward with respect to the upper left corner of the image.
 *
 * @param string	$src	absolute path to the source image.
 * @param string	$dest	absolute path to the destination image. Can be the same as $src
 * @param int		$offsetX
 * @param int		$offsetY
 * @param int		$width
 * @param int		$height
 * @return boolean			true if successfull, otherwise false
 */
function cut_image($src, $dest, $offsetX, $offsetY, $width, $height) {
	echo debugMessage(gTranslate('common', "Cropping Image"),__FILE__, __LINE__);
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
		case "Netpbm":
			exec_wrapper(toPnmCmd($src) .
			" | " .
			Netpbm("pnmcut") .
			" $offsetX $offsetY $width $height" .
			" | " .
			fromPnmCmd($out));
		break;

		case "ImageMagick":
			if (floor(getImVersion()) < 6) {
				$repage = "-page +0+0";
			}
			else {
				$repage = "+repage";
			}
			exec_wrapper(ImCmd(fs_executable('convert'), '', $srcFile, "-crop ${width}x${height}+${offsetX}+${offsetY} $repage", $outFile));
		break;

		default:
			if (isDebugging()) {
				echo "<br>" . gTranslate('common', "You have no graphics package configured for use!") ."<br>";
				return false;
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
		return true;
	}
	else {
		return false;
	}
}

function cropImageToRatio($src, $dest, $destSize, $ratio) {
	list($width, $height) = getDimensions($src);
	$size = 0;

	switch($ratio) {
		case '1/1':
			debugMessage(sprintf(gTranslate('common', "Generating squared version to %d pixels."), $destSize), __FILE__, __LINE__);

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
						$size
				);
			}
			else {
				debugMessage(gTranslate('common', "No Cropping Done."), __FILE__, __LINE__);
				$ret = false;
			}
			break;
	}
	return $ret;
}

function valid_image($file) {
	if (($type = getimagesize($file)) == FALSE) {
		debugMessage(sprintf(gTranslate('common', "Call to %s failed in %s for file %s!"), 'getimagesize()', 'valid_image()', $file), __FILE__, __LINE__);
		return 0;
	}

	debugMessage(sprintf(gTranslate('common', "File %s type %d."), $file, $type[2]), __FILE__, __LINE__);

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

	$type = getExtension($file);
	
	switch($type) {
		case 'png':
			$cmd = "pngtopnm";
		break;
		
		case 'jpg':
		case 'jpeg':
			$cmd = "jpegtopnm";
		break;
		
		case 'gif':
			$cmd = "giftopnm";
		break;
	}

	if (!empty($cmd)) {
		return netpbm($cmd) .' '. fs_import_filename($file);
	}
	else {
		echo gallery_error(
				sprintf(gTranslate('common', "Files with type %s are not supported by Gallery with Netpbm."), $type)
		);
		
		return '';
	}
}

function fromPnmCmd($file, $quality = NULL) {
	global $gallery;
	
	if ($quality == NULL) {
		$quality = $gallery->app->jpegImageQuality;
	}

	if (eregi("\.png(\.tmp)?\$", $file)) {
		$cmd = netpbm("pnmtopng");
	}
	elseif (eregi("\.jpe?g(\.tmp)?\$", $file)) {
		$cmd = netpbm($gallery->app->pnmtojpeg, "--quality=$quality");
	}
	elseif (eregi("\.gif(\.tmp)?\$", $file)) {
		$cmd = netpbm("ppmquant", "256") . " | " . netpbm("ppmtogif");
	}

	if (!empty($cmd)) {
		return "$cmd > " . fs_import_filename($file);
	}
	else {
		echo gallery_error(
			sprintf(gTranslate('common', "Files with type %s are not supported by Gallery with Netpbm."),
			getExtension($file))
		);
		return '';
	}
}

function netpbm($cmd, $args = '') {
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
function ImCmd($cmd, $srcOperator, $src, $destOperator, $dest) {
	global $gallery;
	static $ImVersion;

	if(empty($ImVersion)) {
		$ImVersion = floor(getImVersion());
	}
	$cmd = fs_import_filename($gallery->app->ImPath . "/$cmd");

	if($ImVersion < 6) {
		$cmdLine = "$cmd $srcOperator $destOperator $src $dest";
	}
	else {
		$cmdLine = "$cmd $srcOperator $src $destOperator $dest";
	}

	return $cmdLine;
}

function compressImage($src = '', $dest = '', $targetSize = 0, $quality, $keepProfiles = false, $createThumbnail = false) {
	debugMessage(sprintf(gTranslate('common', "Compressing image: %s"), $src), __FILE__, __LINE__);
	
	global $gallery;
	static $ImVersion;

	if (empty($src) || empty($dest)) {
		echo gallery_error(gTranslate('common', "Not all necessary params for resizing given."));
		echo debugMessage(sprintf(gTranslate('common', "Resizing params: src: %s, dest : %s, targetSize: %s"), $src, $dest, $targetSize), __FILE__, __LINE__);
		return false;
	}

	$stripProfiles = '';

	if(empty($ImVersion)) {
		$ImVersion = floor(getImVersion());
	}

	if ($targetSize === 'off') {
		$targetSize = 0;
	}

	$srcFile	= fs_import_filename($src);
	$destFile	= fs_import_filename($dest);

	switch($gallery->app->graphics)	{
		case "Netpbm":
			if ($targetSize) {
				$result = exec_wrapper(toPnmCmd($src) .' | '.
				netpbm('pnmscale', " -xysize $targetSize $targetSize")  .' | '.
			  		fromPnmCmd($dest, $quality)
				);
			}
			else {
				/* If no targetSize is given, then this is just for setting (decreasing) quality */
				$result = exec_wrapper(toPnmCmd($src) .' | '. fromPnmCmd($dest, $quality));
			}

			if (!$result) {
				return false;
			}

			/* copy over EXIF data if a JPEG if $keepProfiles is set.
			*  Unfortunately, we can't also keep comments.
			*/
			if ($keepProfiles && eregi('\.jpe?g$', $src)) {
				if (isset($gallery->app->use_exif)) {
					exec_wrapper(fs_import_filename($gallery->app->use_exif, 1) . 
								 ' -te ' . $srcFile . ' ' . $destFile);
					return true;
				}
				else {
					processingMsg(gTranslate('common', "Unable to preserve EXIF data (jhead not installed).") . "\n");
					return true;
				}
			}
		break;

		case "ImageMagick":
			/* Set the stripProfiles parameter based on the version of ImageMagick being used.
			* 6.0.0 changed the parameters.
			* Preserve comment, EXIF data if a JPEG if $keepProfiles is set.
			*/
			if(!$keepProfiles || $createThumbnail) {
				switch ($ImVersion) {
					case '5':
						$stripProfiles = ' +profile \'*\' ';
					break;
					
					case '6':
						$stripProfiles = ' -strip ';
					break;
				}
			}

			$destOperator = '';
			$srcOperator = '';

			/* If no targetSize is given, then this is just for setting (decreasing) quality */
			$destOperator = "-quality $quality";

			if ($targetSize) {
				if ($createThumbnail) {
					if ($ImVersion < 6) {
						$destOperator .= " -resize ${targetSize}x${targetSize}";
					}
					else {
						$srcOperator = "-size ${targetSize}x${targetSize}";
						$destOperator .= " -thumbnail ${targetSize}x${targetSize}";
					}
				}
				else {
					if ($ImVersion < 6) {
						$destOperator .= " -resize ${targetSize}x${targetSize} $stripProfiles";
					}
					else {
						if($gallery->app->IM_HQ == 'yes') {
							echo debugMessage(gTranslate('common', "Using IM high quality."), __FILE__, __LINE__, 3);
						}
						else {
							$srcOperator = "-size ${targetSize}x${targetSize}";
							echo debugMessage(gTranslate('common', "Not using IM high quality."), __FILE__, __LINE__, 3);
						}
						$destOperator .= " -resize ${targetSize}x${targetSize} $stripProfiles";
					}
				}
				//$geometryCmd = "-coalesce -geometry ${targetSize}x${targetSize} ";
			}

			return exec_wrapper(ImCmd(fs_executable('convert'), $srcOperator, $srcFile, $destOperator, $destFile));
		break;

		default:
			echo debugMessage(gTranslate('common', "You have no graphics package configured for use!"), __FILE__, __LINE__);
			return false;
		break;
	}
	return false;
}
?>
