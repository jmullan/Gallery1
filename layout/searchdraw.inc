<?

// If the border color is not passed in, we do a black/white combo
if ($searchdraw["bordercolor"]) {
	$borderIn = $searchdraw["bordercolor"];
} else {
	$borderIn = "#FFFFFF";
}

$imageDir = $gallery->app->photoAlbumURL."/images";
$pixelImage = "<img src=\"$imageDir/pixel_trans.gif\" width=\"1\" height=\"1\">";
$pixel30Image = "<img src=\"$imageDir/pixel_trans.gif\" width=\"30\" height=\"1\">";

?>
  <tr>
    <td width=100><?=$pixelImage?></td>
    <td width=10><?=$pixelImage?></td>
    <td bgcolor="<?=$borderIn?>"><?=$pixelImage?></td>
  </tr>
  <tr>
    <td width=100>
      <a href="<?=$searchdraw["photoURL"]?>"><?=$searchdraw["photolink"]?></a>
    </td>
    <td width=10><?=$pixelImage?></td>
    <td valign=top>
      <?=$searchdraw["Text1"]?><br>
      <?=$searchdraw["Text2"]?><br><br>
      <?=$searchdraw["Text3"]?><br>
      <?=$searchdraw["Text4"]?>
    </td>
  </tr>
  <tr>
    <td colspan=3 height=5><?=$pixelImage?></td>
  </tr>
