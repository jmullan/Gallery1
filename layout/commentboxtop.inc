<table width=100% border="0" cellspacing="0" cellpadding=0>
<tr>
<td colspan="2"><img src="<?= $GALLERY_BASEDIR ?>/images/pixel_trans.gif" width="1" height="1"></td>
<td colspan="2" bgcolor="<?=$commentbox["bordercolor"] ?>"><img src="<?= $GALLERY_BASEDIR ?>/images/pixel_trans.gif" width="1" height="1"></td>
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
    <td valign="top" width="3000"><span class="title">Album: <a href="<?= makeAlbumUrl($myAlbumName) ?>"><?=$myAlbum->fields["title"] ?></a></span>
    <?if ($gallery->album->isHidden($i)) 
    {
        echo "  (hidden)";
    }?>
    <br>
    <span class="desc"><?= $myAlbum->fields["description"] ?></span><br>
    <span class="fineprint">
       Last changed on <?=$myAlbum->getLastModificationDate()?>.  
       This album contains <?=pluralize($myAlbum->numPhotos(0), "item", "no")?>.
    <?
    if (!($myAlbum->fields["display_clicks"] == "no")) {
    ?>
       <br><br>This album has been viewed <?=pluralize($myAlbum->getClicks(), "time", "0")?> since <?=$myAlbum->getClicksDate()?>.
    <?
    }
    if ($gallery->user->canWriteToAlbum($myAlbum) &&
       (!($gallery->album->fields["display_clicks"] == "no"))) {
        $albumName=$myAlbumName;
    ?>
        <a href="#" onClick="<?=popup("'" . doCommand("reset-album-clicks", array("albumName" => $albumName), "albums.php") . "'" , 1)?>">[reset counter]</a>
    <?
    }
    ?>
    </span>
    <? if ($gallery->user->isAdmin() || $gallery->user->isOwnerOfAlbum($myAlbum)) { ?>
      <span class="admin">										    
       <br><a href=<?=makeGalleryUrl("view_comments.php", array("set_albumName" => $myAlbumName))?>>[view comments]</a>
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
<td colspan="2"><img src="<?= $GALLERY_BASEDIR ?>/images/pixel_trans.gif" width="1" height="1"></td>
<td colspan="2" bgcolor="<?=$commentbox["bordercolor"] ?>"><img src="<?= $GALLERY_BASEDIR ?>/images/pixel_trans.gif" width="1" height="1"></td>
</tr>
<tr>
<td>
<?}?>
