<?php

/**
 * Prints a wrapped image. Wrapped inside a given frame and/or link.
 *
 * @package  Gallery
 * @todo     Cleanup
 *
 * @version $Id: inline_imagewrap.inc 13817 2006-06-13 11:11:49Z jenst $
 */

//-- shorten the variables for convinience ---
$borderWidth	= $gallery->html_wrap['borderWidth'];
$borderColor	= $gallery->html_wrap['borderColor'];
$pixelImage	= getImagePath('pixel_trans.gif');
$imageWidth	= $gallery->html_wrap['imageWidth'];
$imageHeight	= $gallery->html_wrap['imageHeight'];
$href		= $gallery->html_wrap['imageHref'];
$tag		= $gallery->html_wrap['imageTag'];
$frame		= $gallery->html_wrap['frame'];
$attrlist	= isset($gallery->html_wrap['attrlist']) ? $gallery->html_wrap['attrlist'] : array();

$base = '';

if (isset($gallery->html_wrap['base'])) {
    $base = $gallery->html_wrap['base'];
}
else {
    $base = getGalleryBaseUrl();
}

$href_tag = trim(galleryLink($href, $tag, $attrlist, '', false, false));

if (!in_array($frame, array('none', 'dots', 'solid', 'siriux')) &&
    !fs_file_exists(dirname(__FILE__) . "/frames/$frame/frame.def"))
{
	$frame = 'none';
}


global $fitToWindow;
$idImage	= isset($fitToWindow) ? 'id="galleryImageFrame"' : '';
$idFrameLL	= isset($fitToWindow) ? 'id="frameLL"' : '';
$idFrameRR	= isset($fitToWindow) ? 'id="frameRR"' : '';

switch ($frame) {
    // special cases
    case "none":
	   echo $href_tag;
    break;

    case "solid":
        $width = $imageWidth + $borderWidth;
        echo "<div $idImage style=\"margin: 0 auto; width: ${imageWidth}px; border: ${borderWidth}px solid $borderColor;\">$href_tag</div>";
    break;

    case "dots":
		echo "<div $idImage style=\"margin: 0 auto; width: ${imageWidth}px; border:${borderWidth}px dashed $borderColor;\">$href_tag</div>";
    break;

    case "siriux":
	/**
 	 * Horde Browser class to detect the browser and its abilities.
 	*/
	include_once(GALLERY_BASE .'/classes/horde/Browser.php');
   	$browser = &Browser::singleton();

	$margin = 0;
    	if($imageHeight < $imageWidth) {
    		if ($browser->getBrowser()== 'msie' && $browser->getMajor() < 7) {
			$margin = ($imageWidth - $imageHeight)/2;
    		}
    		else {
	   		$marginTop = ($imageWidth - $imageHeight)/2;
    			echo "\n\t<div style=\"height: ${marginTop}px;\"></div>\n";
    		}
    	}

        echo "\n\t<div $idImage style=\"margin: ${margin}px;\" class=\"g-siriux\">$href_tag</div>\n";
    break;

    default:
    	/**
    	 * If given frame name is valid, include the infos, else use no frame.
    	 */
        if(array_key_exists($frame, available_frames())) {
            $base .= "/layout/frames/$frame";
            require(dirname(__FILE__) . "/frames/$frame/frame.def");
            // Safari/Opera ignore height="100%" for table, but this ensures HTML
            // standards, so do it for all browsers
            $hLL = $imageHeight - $heightLLT - $heightLLB;
            $hRR = $imageHeight - $heightRRT - $heightRRB;
?>
  <table cellspacing="0" cellpadding="0" dir="ltr" style="margin: 0 auto">
  <tr>
	<!-- TL -->
	<td width="<?php echo $widthTL ?>" height="<?php echo $heightTL ?>"><img src="<?php echo "$base/$imageTL" ?>" width="<?php echo $widthTL ?>" height="<?php echo $heightTL ?>" alt="TL"></td>
	<td><table width="100%" cellspacing="0" cellpadding="0">
	    <tr>
		<?php if ($widthTTL > 0) { ?>
		<!-- TTL -->
		<td width="<?php echo $widthTTL ?>"><img src="<?php echo "$base/$imageTTL" ?>" width="<?php echo $widthTTL ?>" height="<?php echo $heightTTL ?>" alt=""></td>
		<?php } ?>
		<!-- TT -->
		<td height="<?php echo $heightTT ?>" style="background-image:url(<?php echo "$base/$imageTT" ?>)"><img src="<?php echo $pixelImage ?>" width="1" height="1" alt=""></td>
		<?php if ($widthTTR > 0) { ?>
		<!-- TTR -->
		<td width="<?php echo $widthTTR ?>"><img src="<?php echo "$base/$imageTTR" ?>" width="<?php echo $widthTTR ?>" height="<?php echo $heightTTR ?>" alt=""></td>
		<?php } ?>
	    </tr>
	    </table></td>
	<!-- TR -->
	<td><img src="<?php echo "$base/$imageTR" ?>" width="<?php echo $widthTR ?>" height="<?php echo $heightTR ?>" alt=""></td>
  </tr>
  <tr>
	<td height="100%"><table cellspacing="0" cellpadding="0">
	    <?php if ($heightLLT > 0) { ?>
	    <!-- LLT -->
	    <tr>
		<td height="<?php echo $heightLLT ?>"><img src="<?php echo "$base/$imageLLT" ?>" width="<?php echo $widthLLT ?>" height="<?php echo $heightLLT ?>" alt=""></td>
	    </tr>
	    <?php } ?>
	    <tr>
		<!-- LL -->
		<td width="<?php echo $widthLL ?>" style="background-image:url(<?php echo "$base/$imageLL" ?>)"><img src="<?php echo $pixelImage ?>" width="1" style="height:<?php echo $hLL ?>px;" alt="" <?php echo $idFrameLL; ?>></td>
	    </tr>
	    <?php if ($heightLLB > 0) { ?>
	    <!-- LLB -->
	    <tr>
		<td height="<?php echo $heightLLB ?>"><img src="<?php echo "$base/$imageLLB" ?>" width="<?php echo $widthLLB ?>" height="<?php echo $heightLLB ?>" alt=""></td>
	    </tr>
	    <?php } ?>
	    </table></td>
	<td><table cellspacing="0" cellpadding="0">
	     <tr>
		<td align="center"><?php echo $href_tag ?></td>
	     </tr>
	    </table></td>
	<td height="100%"><table cellspacing="0" cellpadding="0">
	    <?php if ($heightRRT > 0) { ?>
	    <!-- RRT -->
	    <tr>
		<td height="<?php echo $heightRRT ?>"><img src="<?php echo "$base/$imageRRT" ?>" width="<?php echo $widthRRT ?>" height="<?php echo $heightRRT ?>" alt=""></td>
	    </tr>
	    <?php } ?>
	    <tr>
		<!-- RR -->
		<td width="<?php echo $widthRR ?>" style="background-image:url(<?php echo "$base/$imageRR" ?>)"><img src="<?php echo $pixelImage ?>" width="1" style="height:<?php echo $hRR ?>px" alt="" <?php echo $idFrameRR; ?>></td>
	    </tr>
	    <?php if ($heightRRB > 0) { ?>
	    <tr>
		<!-- RRB -->
		<td height="<?php echo $heightRRB ?>"><img src="<?php echo "$base/$imageRRB" ?>" width="<?php echo $widthRRB ?>" height="<?php echo $heightRRB ?>" alt=""></td>
	    </tr>
	    <?php } ?>
	    </table></td>
  </tr>
  <tr>
	<!-- BL -->
	<td width="<?php echo $widthBL ?>" height="<?php echo $heightBL ?>"><img src="<?php echo "$base/$imageBL" ?>" width="<?php echo $widthBL ?>" height="<?php echo $heightBL ?>" alt=""></td>
	<td><table width="100%" cellspacing="0" cellpadding="0">
	    <tr>
		<?php if ($widthBBL > 0) { ?>
		<!-- BBL -->
		<td width="<?php echo $widthBBL ?>"><img src="<?php echo "$base/$imageBBL" ?>" width="<?php echo $widthBBL ?>" height="<?php echo $heightBBL ?>" alt=""></td>
		<?php } ?>
		<!-- BB -->
		<td height="<?php echo $heightBB ?>" style="background-image:url(<?php echo "$base/$imageBB" ?>)"><img src="<?php echo $pixelImage ?>" width="1" height="1" alt="BB"></td>
		<?php if ($widthBBR > 0) { ?>
		<!-- BBR -->
		<td width="<?php echo $widthBBR ?>"><img src="<?php echo "$base/$imageBBR" ?>" width="<?php echo $widthBBR ?>" height="<?php echo $heightBBR ?>" alt=""></td>
		<?php } ?>
	    </tr>
	    </table></td>
	<!-- BR -->
	<td><img src="<?php echo "$base/$imageBR" ?>" width="<?php echo $widthBR ?>" height="<?php echo $heightBR ?>" alt=""></td>
  </tr>
  </table>
<?php
    }
    else {
	   echo $href_tag;
    }
    break;
}
?>
