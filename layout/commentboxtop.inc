<table width=100% border="0" cellspacing="0" cellpadding=0>
<tr>
<td colspan="6" bgcolor="<?=$commentbox["bordercolor"] ?>"><img src="<?= $GALLERY_BASEDIR ?>/images/pizel_trans.gif" width="1" height="1"></td>
</tr>
<tr>
<td bgcolor="<?=$commentbox["bordercolor"] ?>"><img src="<?= $GALLERY_BASEDIR ?>/images/pizel_trans.gif" width="1" height="1"></td>
<?if ($embeddedAlbum == 1)
{
    ?>
    <td width="150"align="center" ><a href="<?= makeAlbumUrl($myAlbumName)?>"><?= $myHighlightTag ?></a></td>
    <td width="20"></td>
    <td valign="top">
    <table width=100% height=95% border="0" cellspacing="0" cellpadding=0 valign="top">
    <tr>
    <td valign="top" width="3000"><span class="title"><a href="<?= makeAlbumUrl($myAlbumName) ?>"><?=$myAlbum->fields["title"] ?></a></span>
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
    <br><a href="<?= $GALLERY_BASEDIR ?>view_comments.php?set_albumName=<?= $myAlbumName ?>">View All Comments</a>
    </span>

    </td>
    </tr>
    </table>
    </td>
    <?
}
else
{
?>
<td width="150"align="center" ><a href="<?= makeAlbumUrl($gallery->session->albumName, $id) ?>"><?= $gallery->album->getThumbnailTag($i) ?></a></td>
<td width="20"></td>
<td valign="top">
<?if ($gallery->album->isHidden($i)) 
{
    echo "(hidden)";
}?>
<br/>
<table width=95% border="0" cellspacing="0" cellpadding=0>
<tr>
<td colspan="5" bgcolor="<?=$commentbox["bordercolor"] ?>"><img src="<?= $GALLERY_BASEDIR ?>/images/pizel_trans.gif" width="1" height="1"></td>
</tr>
<tr>
<td bgcolor="<?=$commentbox["bordercolor"] ?>"><img src="<?= $GALLERY_BASEDIR ?>/images/pizel_trans.gif" width="1" height="1"></td>
<td>
<?}?>
