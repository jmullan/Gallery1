<?php

// $Id$
// Note that this box is assumed to be attached to a navigator and uses the navigator's
// width parameters .

if ($gallery->ML->mode == 3 and $adminbox["top"]) {
	include ($GALLERY_BASEDIR . "layout/ML_reload.js.php");
}

// If the border color is not passed in, we do a black/white combo
if ($adminbox["bordercolor"]) {
	$borderIn = $adminbox["bordercolor"];
} else {
	$borderIn = "#FFFFFF";
}
$imageDir = $gallery->app->photoAlbumURL."/images";
$pixelImage = "<img src=\"$imageDir/pixel_trans.gif\" width=\"1\" height=\"1\">";

?>
<table width="<?php echo $navigator["fullWidth"] . $navigator["widthUnits"] ?>" border="0" cellspacing="0" cellpadding="0">
<?php
if ($adminbox["top"]) {
?>
  <tr> 
    <td colspan="4" bgcolor="<?php echo $borderIn ?>"><?php echo $pixelImage ?></td>
  </tr>
<?php
}
?>
  <tr> 
    <td bgcolor="<?php echo $borderIn ?>" width="1" height="18"><?php echo $pixelImage ?></td>
    <td align="left" valign="middle" width="3000" height="18">&nbsp;<?php echo $adminbox["text"] ?></td>
    <td align="right" valign="middle" width="3000" height="18">&nbsp;<?php echo $adminbox["commands"] ?>&nbsp;</td>
<?php 
	if ($gallery->ML->mode == 3 and $adminbox["top"]) {
		include ($GALLERY_BASEDIR . "layout/ML_adminbox_addon.inc"); }
?>
<td bgcolor="<?php echo $borderIn ?>" width="1" height="18"><?php echo $pixelImage ?></td>
  </tr>
<?php
if (!$adminbox["top"]) {
?>
  <tr> 
    <td colspan="4" height="1" bgcolor="<?php echo $borderIn ?>"><?php echo $pixelImage ?></td>
  </tr>
<?php
}
?>
</table>    
