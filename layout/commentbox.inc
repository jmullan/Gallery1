<table width="100%" border="0" cellspacing="0" cellpadding=0>
<tr>
<td width="100%">&nbsp;From: &nbsp;  <?= $comment->getName() ?> &nbsp;  @  &nbsp; <?= $comment->getIPNumber()?>  &nbsp; (<?= $comment->getDatePosted() ?>)
</td>
</tr><tr>
<td width="100%" height="20" valign="center">&nbsp;<?= $comment->getCommentText() ?><td>
</tr>
<?
if ($gallery->user->canWriteToAlbum($gallery->album)) {
	$url = "do_command.php?cmd=delete-comment&index=$index&comment_index=$j";
	$buf = ' <a href="#" onClick="' . popup($url) . '">[delete comment]</a>';
	echo "<tr><td>$buf</td></tr>";
}
?>
<tr>
<td width=3000 colspan="5" bgcolor="<?=$commentbox["bordercolor"] ?>"><img src="<?= $GALLERY_BASEDIR ?>/images/pizel_trans.gif" width="1" height="1"></td>
</tr>
</table>
