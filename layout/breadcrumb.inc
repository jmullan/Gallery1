<?php
// $Id$

// If the border color is not passed in, we do a black/white combo
if ($breadcrumb["bordercolor"]) {
	$borderIn = $breadcrumb["bordercolor"];
} else {
	$borderIn = "#FFFFFF";
}
$imageDir = $gallery->app->photoAlbumURL."/images";
$pixelImage = "<img src=\"$imageDir/pixel_trans.gif\" width=\"1\" height=\"1\">";

?>

<?php 
if (!isset($navigator)) {
	$navigator["fullWidth"] = 100;
	$navigator["widthUnits"] = "%";
}
?>
<table width="<?php echo $navigator["fullWidth"] . $navigator["widthUnits"] ?>" border="0" cellspacing="0" cellpadding="0">
<?php
if ($breadcrumb["top"]) {
?>
  <tr> 
    <td colspan="3" bgcolor="<?php echo $borderIn ?>"><?php echo $pixelImage ?></td>
  </tr>
<?php
}
?>
  <tr> 
    <td bgcolor="<?php echo $borderIn ?>" width="1" height="18"><?php echo $pixelImage ?></td>
    <td align="right" width="3000" height="18">
      <span class="bread">
<?php
$i = 0;
while (isset($breadcrumb["text"][$i])) {
	if ($i > 0) {
		//echo "<img src=\"$imageDir/nav_dot_left.gif\" width=8 height=11>";
		echo "<img src=\"$imageDir/nav_home.gif\" width=13 height=11>";
	}
	echo "&nbsp;".$breadcrumb["text"][$i]."&nbsp;";
	$i++;
}
?>
      <img src="<?php echo $imageDir ?>/nav_home.gif" width="13" height="11">&nbsp;
      </span>
    </td> 
                
    <td bgcolor="<?php echo $borderIn ?>" width="1" height="18"><?php echo $pixelImage ?></td>
  </tr>
<?php
if (!$breadcrumb["top"] || !empty($breadcrumb["bottom"])) {
?>
  <tr> 
    <td colspan="3" height="1" bgcolor="<?php echo $borderIn ?>"><?php echo $pixelImage ?></td>
  </tr>
<?php
}
?>
</table>    
