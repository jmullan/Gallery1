<?php
/* $Id$ */

global $gallery, $comment, $index, $j;

$fromString = wordwrap($comment->getName(), 50, " ", 1) .'&nbsp;&nbsp;'. $comment->getDatePosted();

if ($gallery->user->isAdmin() || $gallery->user->isOwnerOfAlbum($gallery->album)) {
	$fromString .= ' (@'. strip_tags($comment->getIPNumber()) .')';
}
?>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" class="albumdesc">
		<tr>
		  <td width="75%" class="admin"><?php printf(gTranslate('core', "From: %s"), $fromString); ?></td>
		  <td><?php
		  if ($gallery->user->canWriteToAlbum($gallery->album)) {
		  	echo '<span class="admin">'. gTranslate('core', "Delete") .'<input type="checkbox" name="comment_index['. $j .']"></span>';
		  }
	?></td>
		</tr>
		<tr>
		  <td style="border-top:1px solid black" width="75%" height="20" valign="top" class="albumdesc"><?php echo wordwrap($comment->getCommentText(), 100, " ", 1) ?></td>
		</tr>
		</table>
		<br>
