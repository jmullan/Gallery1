<!-- $Id$ -->
<table width=100% border="0" cellspacing="0" cellpadding=0>
<tr>
<td colspan="2"><img src="<?php echo $gallery->app->photoAlbumURL ?>/images/pixel_trans.gif" width="1" height="1"></td>
<td colspan="2" bgcolor="<?php echo $commentbox["bordercolor"] ?>"><img src="<?php echo $gallery->app->photoAlbumURL ?>/images/pixel_trans.gif" width="1" height="1"></td>
</tr>
<tr>
<?php if ($embeddedAlbum == 1)
{
    ?>
    <td width="150" valign="top" ><a href="<?php echo makeAlbumUrl($myAlbumName)?>"><?php echo $myHighlightTag ?></a></td>
    <td width="20">&nbsp;</td>
    <td valign="top">
    <table width=100% height=95% border="0" cellspacing="0" cellpadding=0 valign="top">
    <tr>
    <td valign="top" width="3000"><span class="title">Album: <?php echo $myAlbum->fields["title"] ?></span>
    <?php if ($gallery->album->isHidden($i)) 
    {
        echo "  (hidden)";
    }?>
    <?php if ($gallery->user->isAdmin() || $gallery->user->isOwnerOfAlbum($myAlbum)) { ?>
      <span class="admin">										    
       <br><a href=<?php echo makeGalleryUrl("view_comments.php", array("set_albumName" => $myAlbumName))?>>[view comments for this nested album]</a>
      </span>
    <?php } ?>

    </td>
    </tr>
    </table>
    </td>
    <?php
}
else
{
?>
<td width="150" valign="top" ><a href="<?php echo makeAlbumUrl($gallery->session->albumName, $id) ?>"><?php echo $gallery->album->getThumbnailTag($i) ?></a></td>
<td width="20">&nbsp;</td>
<td valign="top">
<?php if ($gallery->album->isHidden($i)) 
{
    echo "(hidden)";
}?>
<table width=100% border="0" cellspacing="0" cellpadding=0>
<tr>
<td colspan="2"><img src="<?php echo $gallery->app->photoAlbumURL ?>/images/pixel_trans.gif" width="1" height="1"></td>
<td colspan="2" bgcolor="<?php echo $commentbox["bordercolor"] ?>"><img src="<?php echo $gallery->app->photoAlbumURL ?>/images/pixel_trans.gif" width="1" height="1"></td>
</tr>
<tr>
<td>
<?php } ?>
