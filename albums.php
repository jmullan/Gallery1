<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2002 Bharat Mediratta
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
<?php
// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
	print "Security violation\n";
	exit;
}
?>
<?php require($GALLERY_BASEDIR . "init.php"); ?>

<?php
/* Read the album list */
$albumDB = new AlbumDB();
$gallery->session->albumName = "";
$page = 1;

/* If there are albums in our list, display them in the table */
$numAlbums = $albumDB->numAlbums($gallery->user);
$numPhotos = $albumDB->numPhotos($gallery->user);

if (!$gallery->session->albumListPage) {
	$gallery->session->albumListPage = 1;
}
$perPage = $gallery->app->default["albumsPerPage"];
$maxPages = max(ceil($numAlbums / $perPage), 1);

if ($gallery->session->albumListPage > $maxPages) {
	$gallery->session->albumListPage = $maxPages;
}

$pixelImage = "<img src=\"$imageDir/pixel_trans.gif\" width=\"1\" height=\"1\">";
$borderColor = $gallery->app->default["bordercolor"];

$navigator["page"] = $gallery->session->albumListPage;
$navigator["pageVar"] = "set_albumListPage";
$navigator["url"] = makeGalleryUrl("albums.php");
$navigator["maxPages"] = $maxPages;
$navigator["spread"] = 6;
$navigator["fullWidth"] = 100;
$navigator["widthUnits"] = "%";
$navigator["bordercolor"] = $borderColor;

?>

<?php if (!$GALLERY_EMBEDDED_INSIDE) { ?>
<html>
<head>
  <title><?php echo $gallery->app->galleryTitle ?></title>
  <?php echo getStyleSheetLink() ?>
</head>
<body>
<?php } ?>

<!-- gallery.header begin -->
<?php
includeHtmlWrap("gallery.header");
?>
<!-- gallery.header end -->
<?php
if (!strcmp($gallery->app->default["showSearchEngine"], "yes")) {
?>
<table width=100% border=0 cellspacing=0>
<tr><?php echo makeFormIntro("search.php"); ?>
<td valign="middle" align="right">
<span class="admin"> Search: </span>
<input style="font-size=10px;" type="text" name="searchstring" value="" size="25">
</td>
</form>
</tr>
<tr><td height=2><img src=<?php echo $gallery->app->photoAlbumURL ?>/images/pixel_trans.gif></td></tr></table>
<?php
}
?>
<!-- admin section begin -->
<?php 
$adminText = "<span class=\"admin\">";
$adminText .= pluralize($numAlbums, "album", "no");
$adminText .= ",&nbsp;" . pluralize($numPhotos, "photo", "no");
if ($maxPages > 1) {
	$adminText .= " on " . pluralize($maxPages, "page", "no") . "&nbsp;";
}
$adminText .= "</span>";
$adminCommands = "<span class=\"admin\">";

if ($gallery->user->isLoggedIn()) {
	$adminCommands .= "Welcome, " . $gallery->user->getFullname() . "&nbsp;&nbsp;<br>";
}

if ($gallery->user->canCreateAlbums()) { 
	$adminCommands .= "<a href=" . doCommand("new-album", array(), "view_album.php") . ">[new album]</a>&nbsp;";
}

if ($gallery->user->isAdmin()) {
	if ($gallery->userDB->canModifyUser() ||
	    $gallery->userDB->canCreateUser() ||
	    $gallery->userDB->canDeleteUser()) {
		$adminCommands .= '<a href="#" onClick="'.popup("manage_users.php").'">[manage users]</a>&nbsp;';
	}
}

if ($gallery->user->isLoggedIn()) {
	if ($gallery->userDB->canModifyUser()) {
		$adminCommands .= '<a href="#" onClick="'.popup("user_preferences.php").'">[preferences]</a>&nbsp;';
	}
	
	if (!$GALLERY_EMBEDDED_INSIDE) {
		$adminCommands .= "<a href=". doCommand("logout", array(), "albums.php"). ">[logout]</a>";
	}
} else {
	if (!$GALLERY_EMBEDDED_INSIDE) {
		$adminCommands .= '<a href="#" onClick="'.popup("login.php").'">[login]</a>';
	}
}

$adminCommands .= "</span>";
$adminbox["text"] = $adminText;
$adminbox["commands"] = $adminCommands;
$adminbox["bordercolor"] = $borderColor;
$adminbox["top"] = true;
include ($GALLERY_BASEDIR . "layout/adminbox.inc");
?>

<!-- top nav -->
<?php
include($GALLERY_BASEDIR . "layout/navigator.inc");
?>


<!-- album table begin -->
<table width=100% border=0 cellpadding=0 cellspacing=7>

<?php
/* Display warnings about broken albums */
if (sizeof($albumDB->brokenAlbums) && $gallery->user->isAdmin()) {
    print "<tr>";
    print "<td colspan=3 align=center>";
    print "<table bordercolor=red border=2 cellpadding=2 cellspacing=2><tr><td>";
    print "<center><b><u>Attention Gallery Administrator!</u></b></center><br>";
    print "Gallery has detected the following directories:<br><br>";
    print "<center>";
    
    foreach ($albumDB->brokenAlbums as $tmpAlbumName) {
	print "$tmpAlbumName<br>";
    }
    print "</center>";
    print "<br>in your albums directory (" . $gallery->app->albumDir . ").<br>These ";
    print "are not valid albums.  Please move them out of the albums directory.";
    print "</td></tr></table>";
    print "</td>";
    print "</tr>";
}
?>

<?php
$start = ($gallery->session->albumListPage - 1) * $perPage + 1;
$end = min($start + $perPage - 1, $numAlbums);
for ($i = $start; $i <= $end; $i++) {
        $gallery->album = $albumDB->getAlbum($gallery->user, $i);
	$isRoot = $gallery->album->isRoot(); // Only display album if it is a root album
	if($isRoot) {
		if (strcmp($gallery->app->default["showOwners"], "no")) {
			$owner = $gallery->album->getOwner();
		}
        	$tmpAlbumName = $gallery->album->fields["name"];
        	$albumURL = makeAlbumUrl($tmpAlbumName);
?>     

  <!-- Begin Album Column Block -->
  <tr>
  <td height="1"><?php echo $pixelImage?></td>
  <td bgcolor="<?php echo $borderColor?>" height="1"><?php echo $pixelImage?></td>
<?php
  if (!strcmp($gallery->app->showAlbumTree, "yes")) {
?>
  <td bgcolor="<?php echo $borderColor?>" height="1"><?php echo $pixelImage?></td>

<?php
  }
?>
  </tr>
  <tr>
  <!-- Begin Image Cell -->
  <td align="center" valign="middle">

<?php
      $gallery->html_wrap['borderColor'] = $borderColor;
      $gallery->html_wrap['borderWidth'] = 1;
      $gallery->html_wrap['pixelImage'] = $imageDir . "/pixel_trans.gif";
      $scaleTo = $gallery->app->highlight_size;
      $highlightIndex = $gallery->album->getHighlight();
      if (isset($highlightIndex)) {
	  list($iWidth, $iHeight) = $gallery->album->getThumbDimensions($highlightIndex, $scaleTo);
      } else {
	  $iWidth = $gallery->app->highlight_size;
	  $iHeight = 100;
      }
      $gallery->html_wrap['thumbWidth'] = $iWidth;
      $gallery->html_wrap['thumbHeight'] = $iHeight;
      $gallery->html_wrap['thumbTag'] = $gallery->album->getHighlightTag($scaleTo);
      $gallery->html_wrap['thumbHref'] = $albumURL;
      includeHtmlWrap('inline_gallerythumb.frame');
?>
  </td>
  <!-- End Image Cell -->
  <!-- Begin Text Cell -->
  <td align=left valign=top>
  <span class="title">
  <a href=<?php echo $albumURL?>>
  <?php echo editField($gallery->album, "title", $edit) ?></a>
  </span>
  <br>
  <span class="desc">
  <?php echo editField($gallery->album, "description", $edit) ?>
  </span>
  <br>
  <?php if (strcmp($gallery->app->default["showOwners"], "no")) { ?>
  <span class="desc">
  Owner: <a href=mailto:<?php echo $owner->getEmail()?>><?php echo $owner->getFullName()?></a>
  </span>
  <br>
  <?php } ?>

  <?php if ($gallery->user->canDeleteAlbum($gallery->album)) { ?>
   <span class="admin">
    <a href="#" onClick="<?php echo popup("delete_album.php?set_albumName={$tmpAlbumName}")?>">[delete album]</a>
   </span>
  <?php } ?>

  <?php if ($gallery->user->canWriteToAlbum($gallery->album)) { ?>
   <span class="admin">
    <a href="#" onClick="<?php echo popup("move_album.php?set_albumName={$tmpAlbumName}&index=$i")?>">[move album]</a>
    <a href="#" onClick="<?php echo popup("rename_album.php?set_albumName={$tmpAlbumName}&index=$i")?>">[rename album]</a>
   </span>
  <?php } ?>

  <?php if ($gallery->user->canChangeTextOfAlbum($gallery->album)) { ?>
   <span class="admin">
    <a href=<?php echo makeGalleryUrl("captionator.php", array("set_albumName" => $tmpAlbumName))?>>[edit captions]</a>
   </span>
  <?php } ?>

  <?php if ($gallery->user->isAdmin() || $gallery->user->isOwnerOfAlbum($gallery->album)) { ?>
   <span class="admin">
    <a href="#" onClick="<?php echo popup("album_permissions.php?set_albumName={$tmpAlbumName}")?>">[permissions]</a>
   <?php if (!strcmp($gallery->album->fields["public_comments"],"yes")) { ?>
    <a href=<?php echo makeGalleryUrl("view_comments.php", array("set_albumName" => $tmpAlbumName))?>>[view&nbsp;comments]</a>
   <?php } ?>
   </span>
  <br>
  url: <a href=<?php echo $albumURL?>><?php echo breakString($albumURL, 60, '&', 5)?></a>
   <?php if (ereg("album[[:digit:]]+$", $albumURL)) { ?>
	<br>
        <span class="error">
         Hey!
         <a href="#" onClick="<?php echo popup("rename_album.php?set_albumName={$tmpAlbumName}&index=$i")?>">Rename</a> 
         this album so that the URL is not so generic!
        </span>
   <?php } ?>
   <?php if ($gallery->album->versionOutOfDate()) { ?>
    <?php if ($gallery->user->isAdmin()) { ?>
  <br>
  <span class="error">
   Note:  This album is out of date! <a href="#" onClick="<?php echo popup("upgrade_album.php")?>">[upgrade album]</a>
  </span>
    <?php } ?>
   <?php } ?>
  <?php } ?>

  <br>
  <span class="fineprint">
   Last changed on <?php echo $gallery->album->getLastModificationDate()?>.  
   This album contains <?php echo pluralize($gallery->album->numPhotos(0), "item", "no")?>.
<?php
if (!($gallery->album->fields["display_clicks"] == "no")) {
?>
   <br><br>This album has been viewed <?php echo pluralize($gallery->album->getClicks(), "time", "0")?> since <?php echo $gallery->album->getClicksDate()?>.
<?php
}
$albumName=$gallery->album->fields["name"];
if ($gallery->user->canWriteToAlbum($gallery->album) &&
   (!($gallery->album->fields["display_clicks"] == "no"))) {
?>
<a href="#" onClick="<?php echo popup("'" . doCommand("reset-album-clicks", array("albumName" => $albumName), "albums.php") . "'" , 1)?>">[reset counter]</a>
<?php
}
?>
  </span>
  </td>
<?php if (!strcmp($gallery->app->showAlbumTree, "yes")) { ?>
  <td valign=top>
   <?php echo printChildren($albumName); ?>
  </td>
<?php } ?>
  </tr>
  <!-- End Text Cell -->
  <!-- End Album Column Block -->

<?php
} // if isRoot() close
} // for() loop      
?>
</table>
<!-- album table end -->
<!-- bottom nav -->
<?php
include($GALLERY_BASEDIR . "layout/navigator.inc");
?>

<!-- gallery.footer begin -->
<?php
includeHtmlWrap("gallery.footer");
?>
<!-- gallery.footer end -->

<?php if (!$GALLERY_EMBEDDED_INSIDE) { ?>
</body>
</html>
<?php } ?>

