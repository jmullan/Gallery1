<?php
// $Id$

//-- shorten the variables for convinience ---
if (!empty($GLOBALS['HTTP_GET_VARS']["GALLERY_BASEDIR"]) ||
	       	!empty($GLOBALS['HTTP_POST_VARS']["GALLERY_BASEDIR"]) ||
	       	!empty($GLOBALS['HTTP_COOKIE_VARS']["GALLERY_BASEDIR"])) {
       	print _("Security violation") ."\n";
       	exit;
}

$borderWidth = $gallery->html_wrap['borderWidth'];
$borderColor = $gallery->html_wrap['borderColor'];
$pixelImage = isset($gallery->html_wrap['pixelImage']) ?
    $gallery->html_wrap['pixelImage'] :
    "${GALLERY_BASEDIR}images/pixel_trans.gif";
$imageWidth = $gallery->html_wrap['imageWidth'];
$imageHeight = $gallery->html_wrap['imageHeight'];
$href = $gallery->html_wrap['imageHref'];
$tag = $gallery->html_wrap['imageTag'];
$frame=$gallery->html_wrap['frame'];

if (!empty($href) ) {
  $href_tag= "<a href=\"$href\">$tag</a>";
} else {
  $href_tag=$tag;
}

switch ($frame) {

// special cases
case "none":
  echo $href_tag;
  break;
case "dots":
?>
  <table style="border-width:1px; border-style:dashed; border-color:$borderColor">
    <tr>
      <td>
        <?php echo $href_tag ?>
      </td>
    </tr>
  </table>
<?php
  break;
case "solid":
?>
  <table width="1%" border="0" cellspacing="0" cellpadding="0">
    <tr bgcolor="<?php echo $borderColor ?>">
      <td colspan="3" height="<?php echo $borderWidth ?>"><img src="<?php echo $pixelImage ?>" height="1" alt=""></td>
    </tr>
    <tr>
      <td bgcolor="<?php echo $borderColor ?>" width="<?php echo $borderWidth ?>"><img src="<?php echo $pixelImage ?>" width="<?php echo $borderWidth ?>"  alt=""></td>
      <td align="center"><?php echo $href_tag ?></td>
      <td bgcolor="<?php echo $borderColor ?>" width="<?php echo $borderWidth ?>"><img src="<?php echo $pixelImage ?>" width="<?php echo $borderWidth ?>" height="1" alt=""></td>
    </tr>
    <tr bgcolor="<?php echo $borderColor ?>">
      <td colspan="3" height="<?php echo $borderWidth ?>"><img src="<?php echo $pixelImage ?>" width="<?php echo $borderWidth ?>" height="1" alt=""></td>
    </tr>
  </table>
  <?php
  break;
default: // use frames directory

  $base = $gallery->app->photoAlbumURL . "/html_wrap/frames/$frame";
  include "${GALLERY_BASEDIR}html_wrap/frames/$frame/frame.def";
  ?>

  <table width="1" border="0" cellspacing="0" cellpadding="0">
  <tr>
	<td width="<?php echo $widthTL ?>" height="<?php echo $heightTL ?>"><img src="<?php echo "$base/$imageTL" ?>" border="0" width="<?php echo $widthTL ?>" height="<?php echo $heightTL ?>" alt="FRAME"></td>
	<td><table width="1" border="0" cellspacing="0" cellpadding="0">
	    <tr>
		<td><img src="<?php echo "$base/$imageTTL" ?>" border="0" width="<?php echo $widthTTL ?>" height="<?php echo $heightTTL ?>" alt="FRAME"></td>
		<td><img src="<?php echo "$base/$imageTT" ?>" border="0" width="<?php echo $imageWidth - $widthTTL - $widthTTR ?>" height="<?php echo $heightTT ?>" alt="FRAME"></td>
		<td><img src="<?php echo "$base/$imageTTR" ?>" border="0" width="<?php echo $widthTTR ?>" height="<?php echo $heightTTR ?>" alt="FRAME"></td>
	    </tr>
	    </table></td>
	<td><img src="<?php echo "$base/$imageTR" ?>" border="0" width="<?php echo $widthTR ?>" height="<?php echo $heightTR ?>" alt="FRAME"></td>
  </tr>
  <tr>
	<td><table border="0" cellspacing="0" cellpadding="0">
	    <tr>
		<td><img src="<?php echo "$base/$imageLLT" ?>" border="0" width="<?php echo $widthLLT ?>" height="<?php echo $heightLLT ?>" alt="FRAME"></td>
	    </tr>
	    <tr>
		<td width="<?php echo $widthLL ?>" height="<?php echo $imageHeight - $heightLLT - $heightLLB ?>" style="background-image:url(<?php echo "$base/$imageLL" ?>)"><img src="<?php echo $pixelImage ?>" border="0" width="1" height="1" alt="FRAME"></td>
	    </tr>
	    <tr>
		<td><img src="<?php echo "$base/$imageLLB" ?>" border="0" width="<?php echo $widthLLB ?>" height="<?php echo $heightLLB ?>" alt="FRAME"></td>
	    </tr></table></td>
	<td><table cellspacing="0" cellpadding="0">
	     <tr>
		<td align="center" width="<?php echo $imageWidth ?>" height="<?php echo $imageHeight ?>"><?php echo $href_tag ?></td>
	     </tr>
	    </table></td>
	<td><table border="0" cellspacing="0" cellpadding="0">
	    <tr>
		<td><img src="<?php echo "$base/$imageRRT" ?>" border="0" width="<?php echo $widthRRT ?>" height="<?php echo $heightRRT ?>" alt="FRAME"></td>
	    </tr>
	    <tr>
		<td width="<?php echo $widthRR ?>" height="<?php echo $imageHeight - $heightRRT - $heightRRB ?>" style="background-image:url(<?php echo "$base/$imageRR" ?>)"><img src="<?php echo $pixelImage ?>" border="0" width="1" height="1" alt="FRAME"></td>
	    </tr>
	    <tr>
		<td><img src="<?php echo "$base/$imageRRB" ?>" border="0" width="<?php echo $widthRRB ?>" height="<?php echo $heightRRB ?>" alt="FRAME"></td>
	    </tr></table></td>
  </tr>
  <tr>
	<td width="<?php echo $widthBL ?>" height="<?php echo $heightBL ?>"><img src="<?php echo "$base/$imageBL" ?>" border="0" width="<?php echo $widthBL ?>" height="<?php echo $heightBL ?>" alt="FRAME"></td>
	<td><table width="1" border="0" cellspacing="0" cellpadding="0">
	    <tr>
		<td><img src="<?php echo "$base/$imageBBL" ?>" border="0" width="<?php echo $widthBBL ?>" height="<?php echo $heightBBL ?>" alt="FRAME"></td>
		<td><img src="<?php echo "$base/$imageBB" ?>" border="0" width="<?php echo $imageWidth - $widthBBL - $widthBBR ?>" height="<?php echo $heightBB ?>" alt="FRAME"></td>
		<td><img src="<?php echo "$base/$imageBBR" ?>" border="0" width="<?php echo $widthBBR ?>" height="<?php echo $heightBBR ?>" alt="FRAME"></td>
	    </tr>
	    </table></td>
	<td><img src="<?php echo "$base/$imageBR" ?>" border="0" width="<?php echo $widthBR ?>" height="<?php echo $heightBR ?>" alt="FRAME"></td>
  </tr>
  </table>
<?php } // end of switch ?>
