<?php /* $Id$ */ ?>
<?php global $commentdraw, $i, $index;
// If the border color is not passed in, we do a black/white combo
if ($commentdraw["bordercolor"]) {
        $borderIn = $commentdraw["bordercolor"];
} else {
        $borderIn = "#FFFFFF";
}

$pixelImage = "<img src=\"" . getImagePath('pixel_trans.gif') . "\" width=\"1\" height=\"1\">";

?>
  <tr>
    <td>
      <span class=modcaption>
      <br>
<?php echo _("From:") ?> <b><?php echo $commentdraw["name"] ?></b> &nbsp;
<?php
  if ($gallery->user->isAdmin()) {
      echo "@ &nbsp;";
      echo $commentdraw["IPNumber"];
      echo "&nbsp;";
  }
?>
        (<?php echo $commentdraw["datePosted"] ?>)
      <br>
      <?php echo $commentdraw["comment"] ?> 
<?php
if ($gallery->user->canWriteToAlbum($gallery->album)) {
	$url = "do_command.php?cmd=delete-comment&index=$index&comment_index=$i";
	$buf = '<a href="#" onClick="javascript:' . popup($url) . '">['. _("delete comment") . ']</a>';
	echo "$buf";
}
?>
      <br><br>
      </span>
    </td>
  </tr>
