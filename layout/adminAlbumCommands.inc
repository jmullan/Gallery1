
<table border="0">
<tr>
<?php

/* User is allowed to delete the album */
if ($gallery->user->canDeleteAlbum($gallery->album)) {
	echo "\n\t<td class=\"admin\">";
		echo popup_link("[". _("delete album") ."]", "delete_album.php?set_albumName={$tmpAlbumName}");
	echo "</td>";
}

/* User is allowed to change the album */
if ($gallery->user->canWriteToAlbum($gallery->album)) {
	echo "\n\t<td class=\"admin\">";
	echo popup_link("[". _("move album") ."]", "move_album.php?set_albumName={$tmpAlbumName}&index=$i&reorder=0");
	echo popup_link("[". _("reorder album") ."]", "move_album.php?set_albumName={$tmpAlbumName}&index=$i&reorder=1");
	echo popup_link("[". _("rename album") ."]", "rename_album.php?set_albumName={$tmpAlbumName}&index=$i");
	echo "</td>";
}

/* User ist allowed to change album captions */
/* Should this be into the above group ? */
if ($gallery->user->canChangeTextOfAlbum($gallery->album) && !$gallery->session->offline) {
	echo "\n\t<td class=\"admin\">";
	echo '<a href="'. makeGalleryUrl("captionator.php", array("set_albumName" => $tmpAlbumName)) . '">' . 
		'['. _("edit captions") . ']</a>';
 	echo "</td>";
}

/* User is Admin or Owner */
if ($gallery->user->isAdmin() || $gallery->user->isOwnerOfAlbum($gallery->album)) {

	/* User is allowed to change Album Permissions */
	echo "\n\t<td class=\"admin\">";
	echo popup_link("[" . _("permissions") ."]", "album_permissions.php?set_albumName={$tmpAlbumName}");
	echo "</td>";

	/* User is allowed to view ALL comments */
	if ($gallery->user->canViewComments($gallery->album)) {
		echo "\n\t<td class=\"admin\">";
	    	echo '<a href="'. makeGalleryUrl("view_comments.php", array("set_albumName" => $tmpAlbumName)) . '">' .
			'[' . _("view&nbsp;comments") . ']</a>';
		echo "</td>";
	}
}
?>
</tr>
</table>