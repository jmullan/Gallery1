<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
  <td width="100%">
  <span class="admin">
  From: <?= $comment->getName() ?> @ <?= $comment->getIPNumber()?> (<?= $comment->getDatePosted() ?>)
  </span>
</td>
</tr><tr>
<td width="100%" height="20" valign="top"><?= $comment->getCommentText() ?><td>
</tr>
<?
if ($gallery->user->canWriteToAlbum($gallery->album)) {
	$url = "do_command.php?cmd=delete-comment&index=$index&comment_index=$j";
	$buf = '<span class="admin"> <a href="#" onClick="' . popup($url) . '">[delete comment]</a></span>';
	echo "<tr><td>$buf</td></tr>";
}
?>
<tr>
<td width=100%><img src="<?= $GALLERY_BASEDIR ?>/images/pixel_trans.gif" width="1" height="6"></td>
</tr>
</table>
