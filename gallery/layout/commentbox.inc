<?php /* $Id$ */ ?>
<?php 
	global $comment, $index, $j;
?>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" class="albumdesc">
		<tr>
			<td width="75%">
		  	<span class="admin">
			<?php echo _("From:") ?> <?php echo wordwrap($comment->getName(), 50, " ", 1) ?> @ <?php echo removeTags($comment->getIPNumber())?> (<?php echo $comment->getDatePosted() ?>)
			</span>

			<td><?php
if ($gallery->user->canWriteToAlbum($gallery->album)) {
	//$url = doCommand("delete-comment", array("index" => $index, "comment_index" => $j), "view_comments.php", array("set_albumName" => $gallery->session->albumName));
	//$buf = '<span class="admin"> <a href="#" onClick="javascript:' . popup($url, 1) . '">[' . _("delete comment") .']</a></span>';
	//echo $buf;
	echo '<span class="admin">'. _("Delete") .'<input type="checkbox" name="comment_index['. $j .']"></span>';
}
	?></td>
		</tr>
		<tr>
			<td style="border-top:1px solid black" width="75%" height="20" valign="top" class="albumdesc"><?php echo wordwrap($comment->getCommentText(), 100, " ", 1) ?></td>
		</tr>
		</table>
		<br>
