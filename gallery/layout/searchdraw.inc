<?php /* $Id$ */ ?>
<?php

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
    <td width=100><?php echo $pixelImage ?></td>
    <td width=10><?php echo $pixelImage ?></td>
    <td bgcolor="<?php echo $borderIn ?>"><?php echo $pixelImage ?></td>
  </tr>
  <tr>
    <td width=100>
      <a href="<?php echo $searchdraw["photoURL"] ?>"><?php echo $searchdraw["photolink"] ?></a>
    </td>
    <td width=10><?php echo $pixelImage ?></td>
    <td valign=top>
      <?php echo $searchdraw["Text1"] ?><br>
      <?php echo $searchdraw["Text2"] ?><br><br>
      <?php echo $searchdraw["Text3"] ?><br>
      <?php echo $searchdraw["Text4"] ?><br>
      <?php echo $searchdraw["Text5"] ?>
    </td>
  </tr>
  <tr>
    <td colspan=3 height=5><?php echo $pixelImage ?></td>
  </tr>
