<?
// Note that this box is assumed to be attached to a navigator and uses the navigator's
// width parameters .

// If the border color is not passed in, we do a black/white combo
if ($adminbox["bordercolor"]) {
	$borderIn = $adminbox["bordercolor"];
} else {
	$borderIn = "#FFFFFF";
}
$imageDir = $app->photoAlbumURL."/images";
$pixelImage = "<img src=\"$imageDir/pixel_trans.gif\" width=\"1\" height=\"1\">";

?>

<table width="<?=$navigator["fullWidth"] . $navigator["widthUnits"]?>" border="0" cellspacing="0" cellpadding="0">
<?
if ($adminbox["top"]) {
?>
  <tr> 
    <td colspan="4" bgcolor="<?=$borderIn?>"><?=$pixelImage?></td>
  </tr>
<?
}
?>
  <tr> 
    <td bgcolor="<?=$borderIn?>" width="1" height="18"><?=$pixelImage?></td>
    <td align="left" valign="middle" width="3000" height="18">&nbsp;<?=$adminbox["text"]?></td>
    <td align="right" valign="middle" width="3000" height="18">&nbsp;<?=$adminbox["commands"]?>&nbsp;</td>
    <td bgcolor="<?=$borderIn?>" width="1" height="18"><?=$pixelImage?></td>
  </tr>
<?
if (!$adminbox["top"]) {
?>
  <tr> 
    <td colspan="4" height="1" bgcolor="<?=$borderIn?>"><?=$pixelImage?></td>
  </tr>
<?
}
?>
</table>    
