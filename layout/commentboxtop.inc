<table width=100% border="0" cellspacing="0" cellpadding=0>
<tr>
<td colspan="2"><img src="<?= $gallery->app->photoAlbumURL ?>/images/pixel_trans.gif" width="1" height="1"></td>
<td colspan="2" bgcolor="<?=$commentbox["bordercolor"] ?>"><img src="<?= $gallery->app->photoAlbumURL ?>/images/pixel_trans.gif" width="1" height="1"></td>
</tr>
<tr>
<?if ($embeddedAlbum == 1)
{
    ?>
    <td width="150" valign="top" ><a href="<?= makeAlbumUrl($myAlbumName)?>"><?= $myHighlightTag ?></a></td>
    <td width="20">&nbsp;</td>
    <td valign="top">
    <table width=100% height=95% border="0" cellspacing="0" cellpadding=0 valign="top">
    <tr>
    <td valign="top" width="3000"><span class="title">Album: <?=$myAlbum->fields["title"] ?></span>
    <?if ($gallery->album->isHidden($i)) 
    {
        echo "  (hidden)";
    }?>
    <? if ($gallery->user->isAdmin() || $gallery->user->isOwnerOfAlbum($myAlbum)) { ?>
      <span class="admin">										    
       <br><a href=<?=makeGalleryUrl("view_comments.php", array("set_albumName" => $myAlbumName))?>>[view comments for this nested album]</a>
      </span>
    <? } ?>

    </td>
    </tr>
    </table>
    </td>
    <?
}
else
{
?>
<td width="150" valign="top" ><a href="<?= makeAlbumUrl($gallery->session->albumName, $id) ?>"><?= $gallery->album->getThumbnailTag($i) ?></a></td>
<td width="20">&nbsp;</td>
<td valign="top">
<?if ($gallery->album->isHidden($i)) 
{
    echo "(hidden)";
}?>
<table width=100% border="0" cellspacing="0" cellpadding=0>
<tr>
<td colspan="2"><img src="<?= $gallery->app->photoAlbumURL ?>/images/pixel_trans.gif" width="1" height="1"></td>
<td colspan="2" bgcolor="<?=$commentbox["bordercolor"] ?>"><img src="<?= $gallery->app->photoAlbumURL ?>/images/pixel_trans.gif" width="1" height="1"></td>
</tr>
<tr>
<td>
<?}?>
