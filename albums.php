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
<? require('init.php'); ?>

<?
/* Read the album list */
$albumDB = new AlbumDB();
$albumName = "";
$page = 1;

/* If there are albums in our list, display them in the table */
$numAlbums = $albumDB->numAlbums($user);

if (!$albumListPage) {
	$albumListPage = 1;
}
$perPage = 5;
$maxPages = max(ceil($numAlbums / $perPage), 1);

if ($albumListPage > $maxPages) {
	$albumListPage = $maxPages;
}

$navigator["page"] = $albumListPage;
$navigator["pageVar"] = "albumListPage";
$navigator["url"] = "albums.php";
$navigator["maxPages"] = $maxPages;
$navigator["spread"] = 6;
$navigator["fullWidth"] = 100;
$navigator["widthUnits"] = "%";
$navigator["bordercolor"] = "#DDDDDD";

?>


<html>
<head>
<title><?= $app->galleryTitle ?></title>
<link rel="stylesheet" type="text/css" href="<?= getGalleryStyleSheetName() ?>">
</head>
<body>

<!-- gallery.header begin -->
<?
includeHtmlWrap("gallery.header");
?>
<!-- gallery.header end -->

<!-- admin section begin -->
<? 
$adminText = "<span class=\"admin\">";
$adminText .= pluralize($numAlbums, "album", "no");
$adminText .= " on " . pluralize($maxPages, "page", "no") . "&nbsp;";
$adminText .= "</span>";
$adminCommands = "<span class=\"admin\">";

if ($user->isLoggedIn()) {
	$adminCommands .= "Welcome, " . $user->getFullname() . "&nbsp;&nbsp;<br>";
}

if ($user->canCreateAlbums()) { 
	$adminCommands .= "<a href=do_command.php?cmd=new-album&return=view_album.php>[New Album]</a>&nbsp;";
}

if ($user->isAdmin()) {
	$adminCommands .= "<a href=".popup("manage_users.php").">[Manage Users]</a>&nbsp;";
}

if ($user->isLoggedIn()) {
	$adminCommands .= "<a href=".popup("user_preferences.php").">[Preferences]</a>&nbsp;";
	$adminCommands .= "<a href=do_command.php?cmd=logout&return=albums.php>[Logout]</a>";
} else {
	$adminCommands .= "<a href=".popup("login.php").">[Login]</a>";
}
/*
$adminCommands .= "<a href=".popup_help("commands", "gallery")."><img src='images/question_mark.gif' border=0></a>";
*/
$adminCommands .= "</span>";
$adminbox["text"] = $adminText;
$adminbox["commands"] = $adminCommands;
$adminbox["bordercolor"] = "#DDDDDD";
$adminbox["top"] = true;
include ("layout/adminbox.inc");
?>

<!-- top nav -->
<?
include("layout/navigator.inc");
?>

<!-- album table begin -->
<table width=100% border=0 cellspacing=7>


<?
$start = ($albumListPage - 1) * $perPage + 1;
$end = min($start + $perPage - 1, $numAlbums);

for ($i = $start; $i <= $end; $i++) {
        $album = $albumDB->getAlbum($user, $i);
	$owner = $album->getOwner();
        $tmpAlbumName = $album->fields["name"];
        $albumURL = makeGalleryUrl($tmpAlbumName);

?>     

  <!-- Begin Album Column Block -->
  <tr>
  <!-- Begin Image Cell -->
  <td width=<?=$app->highlight_size?> align=center valign=middle>
  <a href=<?=$albumURL?>>
  <?   
        if ($album->numPhotos()) {
                echo $album->getHighlightTag();
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
  <?= editField($album, "title", $edit) ?></a>
  </span>
  <br>
  <span class="desc">
  <?= editField($album, "description", $edit) ?>
  </span>
  <br>
  <? if ($app->default["showOwners"]) { ?>
  <span class="desc">
  Owner: <a href=mailto:<?=$owner->getEmail()?>><?=$owner->getFullName()?></a>
  </span>
  <br>
  <? } ?>

  <? if ($user->canDeleteAlbum($album)) { ?>
   <span class="admin">
    <a href=<?= popup("delete_album.php?set_albumName={$tmpAlbumName}")?>>[delete album]</a>
   </span>
  <? } ?>

  <? if ($user->canWriteToAlbum($album)) { ?>
   <span class="admin">
    <a href=<?= popup("move_album.php?set_albumName={$tmpAlbumName}&index=$i")?>>[move album]</a>
    <a href=<?= popup("rename_album.php?set_albumName={$tmpAlbumName}&index=$i")?>>[rename album]</a>
   </span>
  <? } ?>

  <? if ($user->isAdmin() || $user->isOwnerOfAlbum($album)) { ?>
   <span class="admin">
    <a href=<?= popup("album_permissions.php?set_albumName={$tmpAlbumName}")?>>[permissions]</a>
   </span>

  <br>
  url: <a href=<?=$albumURL?>><?=$albumURL?></a>
   <? if (preg_match("/album\d+$/", $albumURL)) { ?>
	<br>
        <span class="error">
         Hey!
         <a href=<?= popup("rename_album.php?set_albumName={$tmpAlbumName}&index=$i")?>>Rename</a> 
         this album so that the URL is not so generic!
        </span>
   <? } ?>
  <? } ?>

  <br>
  </span>
  <span class="fineprint">
   Last changed on <?=$album->getLastModificationDate()?>.  
   This album contains <?=pluralize($album->numPhotos(0), "item", "no")?>.
  </span>
  </td>
  </tr>
  <!-- End Text Cell -->
  <!-- End Album Column Block -->

<?
} // for() loop      
?>
</table>
<!-- album table end -->
<!-- bottom nav -->
<?
include("layout/navigator.inc");
?>

<!-- gallery.footer begin -->
<?
includeHtmlWrap("gallery.footer");
?>
<!-- gallery.footer end -->

</body>
</html>
