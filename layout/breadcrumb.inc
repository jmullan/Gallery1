<?

// If the border color is not passed in, we do a black/white combo
if ($breadcrumb["bordercolor"]) {
	$borderIn = $breadcrumb["bordercolor"];
} else {
	$borderIn = "#FFFFFF";
}
$imageDir = $gallery->app->photoAlbumURL."/images";
$pixelImage = "<img src=\"$imageDir/pixel_trans.gif\" width=\"1\" height=\"1\">";

?>

<table width="<?=$navigator["fullWidth"] . $navigator["widthUnits"]?>" border="0" cellspacing="0" cellpadding="0">
<?
if ($breadcrumb["top"]) {
?>
  <tr> 
    <td colspan="3" bgcolor="<?=$borderIn?>"><?=$pixelImage?></td>
  </tr>
<?
}
?>
  <tr> 
    <td bgcolor="<?=$borderIn?>" width="1" height="18"><?=$pixelImage?></td>
    <td align="right" width="3000" height="18">
      <span class="bread">
<?
$i = 0;
while ($breadcrumb["text"][$i]) {
	if ($i > 0) {
		//echo "<img src=\"$imageDir/nav_dot_left.gif\" width=8 height=11>";
		echo "<img src=\"$imageDir/nav_home.gif\" width=13 height=11>";
	}
	echo "&nbsp;".$breadcrumb["text"][$i]."&nbsp";
	$i++;
}
?>
      <img src="<?=$imageDir?>/nav_home.gif" width="13" height="11">&nbsp;
      </span>
    </td> 
                
    <td bgcolor="<?=$borderIn?>" width="1" height="18"><?=$pixelImage?></td>
  </tr>
<?
if (!$breadcrumb["top"] || $breadcrumb["bottom"]) {
?>
  <tr> 
    <td colspan="3" height="1" bgcolor="<?=$borderIn?>"><?=$pixelImage?></td>
  </tr>
<?
}
?>
</table>    
