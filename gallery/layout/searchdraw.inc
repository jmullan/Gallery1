<?php /* $Id$ */ ?>
<?php
global $searchdraw;

// If the border color is not passed in, we do a black/white combo
if ($searchdraw["bordercolor"]) {
	$borderIn = $searchdraw["bordercolor"];
} else {
	$borderIn = "#FFFFFF";
}

$pixelImage = "<img src=\"" . getImagePath('pixel_trans.gif') . "\" width=\"0\" height=\"0\" alt=\"\">";

?>
  <tr>
    <td width=100><?php echo $pixelImage ?></td>
    <td width=10><?php echo $pixelImage ?></td>
    <td><?php echo $pixelImage ?></td>
  </tr>
  <tr>
    <td width=100>
      <a href="<?php echo $searchdraw["photoURL"] ?>"><?php echo $searchdraw["photolink"] ?></a>
    </td>
    <td width=10><?php echo $pixelImage ?></td>
    <td valign=top class="albumdesc">
      <?php echo $searchdraw["Text1"] ?><br>
      <?php echo $searchdraw["Text2"] ?><br><br>
      <?php echo $searchdraw["Text3"] ?><br>
      <?php echo $searchdraw["Text4"] ?><br>
      <?php echo $searchdraw["Text5"] ?>
    </td>
  </tr>
  <tr>
    <td colspan=3 height=5></td>
  </tr>
