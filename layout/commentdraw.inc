<?
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
    <td width=15%><?=$pixelImage?></td>
    <td width=70% bgcolor="<?=$borderIn?>"><?=$pixelImage?></td>
    <td width=15%><?=$pixelImage?></td>
  </tr>
  <tr>
    <td><?=$pixelImage?></td>
    <td>
      <span class=caption>
      <br>
      From: &nbsp <b><?=$commentdraw["name"]?></b> &nbsp
        @ &nbsp <?=$commentdraw["IPNumber"]?> &nbsp
        (<?=$commentdraw["datePosted"]?>)
      <br>
      <?=$commentdraw["comment"]?> 
<?
if ($gallery->user->canWriteToAlbum($gallery->album)) {
	$url = "do_command.php?cmd=delete-comment&index=$index&comment_index=$i";
	$buf = '<a href="#" onClick="' . popup($url) . '">[delete comment]</a>';
	echo "$buf";
}
?>
      <br><br>
      </span>
    </td>
    <td><?=$pixelImage?></td>
  </tr>
