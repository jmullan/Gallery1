<?php /* $Id$ */ ?>
<?php
// If the border color is not passed in, we do a black/white combo
if ($commentdraw["bordercolor"]) {
        $borderIn = $commentdraw["bordercolor"];
} else {
        $borderIn = "#FFFFFF";
}

$imageDir = $gallery->app->photoAlbumURL."/images";
$pixelImage = "<img src=\"$imageDir/pixel_trans.gif\" width=\"1\" height=\"1\">";

?>
  <tr>
    <td width=15%><?php echo $pixelImage?></td>
    <td width=70% bgcolor="<?php echo $borderIn?>"><?php echo $pixelImage?></td>
    <td width=15%><?php echo $pixelImage?></td>
  </tr>
  <tr>
    <td><?php echo $pixelImage?></td>
    <td>
      <span class=caption>
      <br>
      From: &nbsp; <b><?php echo $commentdraw["name"]?></b> &nbsp;
<?php
  if ($gallery->user->isAdmin()) {
      echo "@ &nbsp;";
      echo $commentdraw["IPNumber"];
      echo "&nbsp;";
  }
?>
        (<?php echo $commentdraw["datePosted"]?>)
      <br>
      <?php echo $commentdraw["comment"]?> 
<?php
if ($gallery->user->canWriteToAlbum($gallery->album)) {
	$url = "do_command.php?cmd=delete-comment&index=$index&comment_index=$i";
	$buf = '<a href="#" onClick="' . popup($url) . '">[delete comment]</a>';
	echo "$buf";
}
?>
      <br><br>
      </span>
    </td>
    <td><?php echo $pixelImage?></td>
  </tr>
