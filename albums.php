<?
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000 Bharat Mediratta
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */
?>
<? require($GALLERY_BASEDIR . "init.php"); ?>

<?
/* Read the album list */
$albumDB = new AlbumDB();
$gallery->session->albumName = "";
$page = 1;

/* If there are albums in our list, display them in the table */
$numAlbums = $albumDB->numAlbums($gallery->user);

if (!$gallery->session->albumListPage) {
	$gallery->session->albumListPage = 1;
}
$perPage = $gallery->app->default["albumsPerPage"];
$maxPages = max(ceil($numAlbums / $perPage), 1);

if ($gallery->session->albumListPage > $maxPages) {
	$gallery->session->albumListPage = $maxPages;
}

$borderColor = $gallery->app->default["bordercolor"];

$navigator["page"] = $gallery->session->albumListPage;
$navigator["pageVar"] = "set_albumListPage";
$navigator["url"] = makeGalleryUrl("");
$navigator["maxPages"] = $maxPages;
$navigator["spread"] = 6;
$navigator["fullWidth"] = 100;
$navigator["widthUnits"] = "%";
$navigator["bordercolor"] = $borderColor;

?>

<? if (!$GALLERY_EMBEDDED_INSIDE) { ?>
<html>
<head>
  <title><?= $gallery->app->galleryTitle ?></title>
  <?= getStyleSheetLink() ?>
</head>
<body>
<? } ?>

<!-- gallery.header begin -->
<?
includeHtmlWrap("gallery.header");
?>
<!-- gallery.header end -->
<?
if (!strcmp($gallery->app->default["showSearchEngine"], "yes")) {
?>
<table width=100% border=0 cellspacing=0>
<tr><?= makeSearchFormIntro(); ?>
<td valign="middle" align="right">
<span class="admin"> Search: </span>
<input style="font-size=10px;" type="text" name="searchstring" value="" size="25">
</td>
</form>
</tr>
<tr><td height=2><img src=<?= $GALLERY_BASEDIR ?>images/pixel_trans.gif></td></tr></table>
<?
}
?>
<!-- admin section begin -->
<? 
$adminText = "<span class=\"admin\">";
$adminText .= pluralize($numAlbums, "album", "no");
if ($maxPages > 1) {
	$adminText .= " on " . pluralize($maxPages, "page", "no") . "&nbsp;";
}
$adminText .= "</span>";
$adminCommands = "<span class=\"admin\">";

if ($gallery->user->isLoggedIn()) {
	$adminCommands .= "Welcome, " . $gallery->user->getFullname() . "&nbsp;&nbsp;<br>";
}

if ($gallery->user->canCreateAlbums()) { 
	$adminCommands .= "<a href=" . doCommand("new-album", "", "view_album.php") . ">[new album]</a>&nbsp;";
}

if ($gallery->user->isAdmin()) {
	$adminCommands .= '<a href="#" onClick="'.popup("manage_users.php").'">[manage users]</a>&nbsp;';
}

if ($gallery->user->isLoggedIn()) {
	$adminCommands .= '<a href="#" onClick="'.popup("user_preferences.php").'">[preferences]</a>&nbsp;';
	$adminCommands .= "<a href=". doCommand("logout", "", "albums.php"). ">[logout]</a>";
} else {
	$adminCommands .= '<a href="#" onClick="'.popup("login.php").'">[login]</a>';
}
/*
$adminCommands .= '<a href="#" onClick="'.popup_help("commands", "gallery").'"><img src="images/question_mark.gif" border=0></a>';
*/
$adminCommands .= "</span>";
$adminbox["text"] = $adminText;
$adminbox["commands"] = $adminCommands;
$adminbox["bordercolor"] = $borderColor;
$adminbox["top"] = true;
include ($GALLERY_BASEDIR . "layout/adminbox.inc");
?>

<!-- top nav -->
<?
include($GALLERY_BASEDIR . "layout/navigator.inc");
?>


<!-- album table begin -->
<table width=100% border=0 cellspacing=7>


<?
$start = ($gallery->session->albumListPage - 1) * $perPage + 1;
$end = min($start + $perPage - 1, $numAlbums);
for ($i = $start; $i <= $end; $i++) {
        $gallery->album = $albumDB->getAlbum($gallery->user, $i);
	$isRoot = $gallery->album->isRoot(); // Only display album if it is a root album
	if($isRoot) {
		$owner = $gallery->album->getOwner();
        	$tmpAlbumName = $gallery->album->fields["name"];
        	$albumURL = makeGalleryUrl($tmpAlbumName);
?>     

  <!-- Begin Album Column Block -->
  <tr>
  <!-- Begin Image Cell -->
  <td width=<?=$gallery->app->highlight_size?> align=center valign=middle>
  <a href=<?=$albumURL?>>
  <?   
        if ($gallery->album->numPhotos(1)) {
                echo $gallery->album->getHighlightTag();
        } else {
                echo "<span class=title>Empty!</span>";
        }
  ?></a> </td>
  <!-- End Image Cell -->
  <!-- Begin Text Cell -->
  <td align=left valign=top>
  <hr size=1>
  <span class="title">
  <a href=<?=$albumURL?>>
  <?= editField($gallery->album, "title", $edit) ?></a>
  </span>
  <br>
  <span class="desc">
  <?= editField($gallery->album, "description", $edit) ?>
  </span>
  <br>
  <? if (strcmp($gallery->app->default["showOwners"], "no")) { ?>
  <span class="desc">
  Owner: <a href=mailto:<?=$owner->getEmail()?>><?=$owner->getFullName()?></a>
  </span>
  <br>
  <? } ?>

  <? if ($gallery->user->canDeleteAlbum($gallery->album)) { ?>
   <span class="admin">
    <a href="#" onClick="<?= popup("delete_album.php?set_albumName={$tmpAlbumName}")?>">[delete album]</a>
   </span>
  <? } ?>

  <? if ($gallery->user->canWriteToAlbum($gallery->album)) { ?>
   <span class="admin">
    <a href="#" onClick="<?= popup("move_album.php?set_albumName={$tmpAlbumName}&index=$i")?>">[move album]</a>
    <a href="#" onClick="<?= popup("rename_album.php?set_albumName={$tmpAlbumName}&index=$i")?>">[rename album]</a>
   </span>
  <? } ?>

  <? if ($gallery->user->isAdmin() || $gallery->user->isOwnerOfAlbum($gallery->album)) { ?>
   <span class="admin">
    <a href="#" onClick="<?= popup("album_permissions.php?set_albumName={$tmpAlbumName}")?>">[permissions]</a>
   </span>

  <br>
  url: <a href=<?=$albumURL?>><?=$albumURL?></a>
   <? if (ereg("album[[:digit:]]+$", $albumURL)) { ?>
	<br>
        <span class="error">
         Hey!
         <a href="#" onClick="<?= popup("rename_album.php?set_albumName={$tmpAlbumName}&index=$i")?>">Rename</a> 
         this album so that the URL is not so generic!
        </span>
   <? } ?>
   <? if ($gallery->album->versionOutOfDate()) { ?>
  <br>
  <span class="error">
   Note:  This album is out of date! <a href="#" onClick="<?= popup("upgrade_album.php")?>">[upgrade album]</a>
  </span>
   <? } ?>
  <? } ?>

  <br>
  <span class="fineprint">
   Last changed on <?=$gallery->album->getLastModificationDate()?>.  
   This album contains <?=pluralize($gallery->album->numPhotos(0), "item", "no")?>.
<?
if (!($gallery->album->fields["display_clicks"] == "no")) {
?>
   <br><br>This album has been viewed <?=pluralize($gallery->album->getClicks(), "time", "0")?> since <?=$gallery->album->getClicksDate()?>.
<?
}
if ($gallery->user->canWriteToAlbum($gallery->album)) {
	$albumName=$gallery->album->fields["name"];
?>
<a href="#" onClick="<?=popup("do_command.php?cmd=reset-album-clicks&albumName=$albumName&return=albums.php")?>">[reset counter]</a>
<?
}
?>
  </span>
  </td>
  </tr>
  <!-- End Text Cell -->
  <!-- End Album Column Block -->

<?
} // if isRoot() close
} // for() loop      
?>
</table>
<!-- album table end -->
<!-- bottom nav -->
<?
include($GALLERY_BASEDIR . "layout/navigator.inc");
?>

<!-- gallery.footer begin -->
<?
includeHtmlWrap("gallery.footer");
?>
<!-- gallery.footer end -->

<? if (!$GALLERY_EMBEDDED_INSIDE) { ?>
</body>
</html>
<? } ?>

