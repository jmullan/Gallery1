<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2003 Bharat Mediratta
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
 *
 * $Id$
 */
?>
<?php
// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
	print _("Security violation") ."\n";
	exit;
}
?>
<?php if (!isset($GALLERY_BASEDIR)) {
    $GALLERY_BASEDIR = './';
}
require($GALLERY_BASEDIR . 'init.php'); ?>
<?php
$gallery->session->offlineAlbums["albums.php"]=true;

/* Read the album list */
$albumDB = new AlbumDB(FALSE);
$gallery->session->albumName = "";
$page = 1;

/* If there are albums in our list, display them in the table */
$numAlbums = $albumDB->numAlbums($gallery->user);
$numPhotos = $albumDB->getCachedNumPhotos($gallery->user);
$numAccess = $albumDB->numAccessibleAlbums($gallery->user);

if (empty($gallery->session->albumListPage)) {
	$gallery->session->albumListPage = 1;
}
$perPage = $gallery->app->albumsPerPage;
$maxPages = max(ceil($numAlbums / $perPage), 1);

if ($gallery->session->albumListPage > $maxPages) {
	$gallery->session->albumListPage = $maxPages;
}

$imageDir = $gallery->app->photoAlbumURL . '/images';
$pixelImage = "<img src=\"$imageDir/pixel_trans.gif\" width=\"1\" height=\"1\" alt=\"pixel_trans\">";
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
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <title><?php echo $gallery->app->galleryTitle ?></title>
  <?php echo getStyleSheetLink() ?>
  <?php /* prefetching/navigation */
  if ($navigator['page'] > 1) { ?>
      <link rel="top" href="<?php echo makeGalleryUrl('albums.php', array('set_albumListPage' => 1)) ?>" />
      <link rel="first" href="<?php echo makeGalleryUrl('albums.php', array('set_albumListPage' => 1)) ?>" />
      <link rel="prev" href="<?php echo makeGalleryUrl('albums.php', array('set_albumListPage' => $navigator['page']-1)) ?>" />
  <?php }
  if ($navigator['page'] < $maxPages) { ?>
      <link rel="next" href="<?php echo makeGalleryUrl('albums.php', array('set_albumListPage' => $navigator['page']+1)) ?>" />
      <link rel="last" href="<?php echo makeGalleryUrl('albums.php', array('set_albumListPage' => $maxPages)) ?>" />
  <?php } ?>
</head>
<body dir="<?php echo $gallery->direction ?>">
<?php } ?>
<?php
includeHtmlWrap("gallery.header");
?>
<?php
if (!$gallery->session->offline && !strcmp($gallery->app->showSearchEngine, "yes")) {
?>
<table width="100%" border=0 cellspacing=0>
<tr>
<td valign="middle" align="right">
<?php echo makeFormIntro("search.php"); ?>
<span class="admin"> <?php echo _("Search") ?>: </span>
<input style="font-size=10px;" type="text" name="searchstring" value="" size="25">
</form>
</td>
</tr>
<tr><td height="2"><img src="<?php echo $gallery->app->photoAlbumURL ?>/images/pixel_trans.gif" alt="pixel_trans"></td></tr></table>
<?php
}
?>
<!-- admin section begin -->
<?php 
$adminText = "<span class=\"admin\">";
$toplevel_str= pluralize_n($numAlbums, ($numAccess != $numAlbums) ? _("top-level album") : _("album"), ($numAccess != $numAlbums) ? _("top-level albums") : _("albums"), _("No albums"));
$total_str= sprintf(_("%d total"), $numAccess); 
$image_str= pluralize_n($numPhotos, _("image"), _("images"), _("no image"));
$page_str= pluralize_n($maxPages, _("page"), _("pages"), _("no pages"));

if (($numAccess != $numAlbums) && $maxPages > 1) {
	$adminText .= sprintf(_("%s (%s), %s on %s"), $toplevel_str, $total_str, $image_str, $page_str);
}
else if ($numAccess != $numAlbums) {
	$adminText .= sprintf(_("%s (%s), %s"), $toplevel_str, $total_str, $image_str);
} else if ($maxPages > 1) {
	$adminText .= sprintf(_("%s, %s on %s"), $toplevel_str, $image_str, $page_str);
} else {
	$adminText .= sprintf(_("%s, %s"), $toplevel_str, $image_str);
}
$adminText .= "</span>";
$adminCommands = "<span class=\"admin\">";

if ($gallery->user->isLoggedIn() && !$gallery->session->offline) {
	$displayName = $gallery->user->getFullname();
	if (empty($displayName)) {
		$displayName = $gallery->user->getUsername();
	}
	$adminCommands .= sprintf(_("Welcome, %s"), $displayName) . "&nbsp;&nbsp;<br>";
}

if ($gallery->user->isAdmin()) {
	$doc = galleryDocs();
	if ($doc) {
		$adminCommands .= "[$doc]&nbsp;";
	}
}
if ($gallery->user->canCreateAlbums() && !$gallery->session->offline) { 
	$adminCommands .= "<a href=\"" . doCommand("new-album", array(), "view_album.php") . "\">[". _("new album") ."]</a>&nbsp;";
}

if ($gallery->user->isAdmin()) {
	if ($gallery->userDB->canModifyUser() ||
	    $gallery->userDB->canCreateUser() ||
	    $gallery->userDB->canDeleteUser()) {
		$adminCommands .= popup_link("[" . _("manage users") ."]", 
			"manage_users.php");
	}
}

if ($gallery->user->isLoggedIn() && !$gallery->session->offline) {
	if ($gallery->userDB->canModifyUser()) {
		$adminCommands .= popup_link("[". _("preferences") ."]", 
			"user_preferences.php");
	}
	
	if (!$GALLERY_EMBEDDED_INSIDE) {
		$adminCommands .= "<a href=\"". doCommand("logout", array(), "albums.php"). "\">[". _("logout") ."]</a>";
	}
} else {
	if (!$GALLERY_EMBEDDED_INSIDE) {
	        $adminCommands .= popup_link("[" . _("login") . "]", "login.php", 0);
	        // $adminCommands .= popup_link("[" . _("login") . "]", "login.php", 0, true, 250, 500);
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
include($GALLERY_BASEDIR . "layout/ml_pulldown.inc");
?>


<!-- album table begin -->
<table width="100%" border=0 cellpadding=0 cellspacing=7>

<?php
/* Display warnings about broken albums */
if (sizeof($albumDB->brokenAlbums) && $gallery->user->isAdmin()) {
    print "<tr>";
    print "<td colspan=\"3\" align=\"center\">";
    print "<table bordercolor=\"red\" border=\"2\" cellpadding=\"2\" cellspacing=\"2\"><tr><td>";
    print "<center><b><u>". _("Attention Gallery Administrator!") ."</u></b></center><br>";
    $broken_albums = '';
    foreach ($albumDB->brokenAlbums as $tmpAlbumName) {
	$broken_albums .= "$tmpAlbumName<br>";
    }
    print sprintf(_("%s has detected the following directories: %s in your albums directory (%s)."),
		    Gallery(),
		    "<br><br> <center>$broken_albums</center>",
		    $gallery->app->albumDir);
    print "<br>";
    print _("These are not valid albums.  Please move them out of the albums directory.") ;
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
		if (strcmp($gallery->app->showOwners, "no")) {
			$owner = $gallery->album->getOwner();
		}
        	$tmpAlbumName = $gallery->album->fields["name"];
        	$albumURL = makeAlbumUrl($tmpAlbumName);
?>     

  <!-- Begin Album Column Block -->
  <tr>
  <td height="1"><?php echo $pixelImage ?></td>
  <td bgcolor="<?php echo $borderColor ?>" height="1"><?php echo $pixelImage ?></td>
<?php
  if (!strcmp($gallery->app->showAlbumTree, "yes")) {
?>
  <td bgcolor="<?php echo $borderColor ?>" height="1"><?php echo $pixelImage ?></td>

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
      $gallery->html_wrap['thumbTag'] = $gallery->album->getHighlightTag($scaleTo,'', _("Highlight for Album: "). $gallery->album->fields["title"]);
      $gallery->html_wrap['thumbHref'] = $albumURL;
      includeHtmlWrap('inline_gallerythumb.frame');
?>
  </td>
  <!-- End Image Cell -->
  <!-- Begin Text Cell -->
  <td align="<?php echo $gallery->alignment ?>" valign="top">
  <span class="title">
  <a href="<?php echo $albumURL ?>">
  <?php _("title") ?>
  <?php echo editField($gallery->album, "title") ?></a>
  </span>
  <br>
  <span class="desc">
  <?php _("description") ?>
  <?php echo editField($gallery->album, "description") ?>
  </span>
  <br>
  <?php if (strcmp($gallery->app->showOwners, "no")) { ?>
	  <span class="desc">
		  <?php 
		  echo _("Owner:") . " ";
		  if (!$owner->getEmail()) {
			  echo $owner->getFullName();
		  } else {
			  echo "<a href=\"mailto:" . $owner->getEmail() . "\">" . $owner->getFullName() . "</a>";
		  }
		  ?> 
		  </span>
		  <br>
  <?php } ?>

  <?php if ($gallery->user->canDeleteAlbum($gallery->album)) { ?>
   <span class="admin">
    <?php echo popup_link("[". _("delete album") ."]", 
    	"delete_album.php?set_albumName={$tmpAlbumName}"); ?>
   </span>
  <?php } ?>

  <?php if ($gallery->user->canWriteToAlbum($gallery->album)) { ?>
   <span class="admin">
    <?php echo popup_link("[". _("move album") ."]", 
    	"move_album.php?set_albumName={$tmpAlbumName}&index=$i"); ?>
    <?php echo popup_link("[" . _("rename album") ."]", "rename_album.php?set_albumName={$tmpAlbumName}&index=$i"); ?>
   </span>
  <?php } ?>

  <?php if ($gallery->user->canChangeTextOfAlbum($gallery->album) 
  	&& !$gallery->session->offline) { ?>
   <span class="admin">
    <a href="<?php echo makeGalleryUrl("captionator.php", array("set_albumName" => $tmpAlbumName)) ?>">[<?php echo _("edit captions") ?>]</a>
   </span>
  <?php } ?>

  <?php if ($gallery->user->isAdmin() || $gallery->user->isOwnerOfAlbum($gallery->album)) { ?>
   <span class="admin">
    <?php echo popup_link("[" . _("permissions") ."]", "album_permissions.php?set_albumName={$tmpAlbumName}"); ?>
   <?php if (!strcmp($gallery->album->fields["public_comments"],"yes")) { ?>
    <a href="<?php echo makeGalleryUrl("view_comments.php", array("set_albumName" => $tmpAlbumName)) ?>">[<?php echo _("view&nbsp;comments") ?>]</a>
   <?php } ?>
   </span>
  <br>
  <?php echo _("url:") ?> <a href="<?php echo $albumURL ?>">
  	<?php if (!$gallery->session->offline) {
		echo breakString($albumURL, 60, '&', 5);
	} else {
		echo $tmpAlbumName;
	}
	?>
	</a>
   <?php if (ereg("album[[:digit:]]+$", $albumURL)) { ?>
	<?php if (!$gallery->session->offline) { ?>
	<br>
        <span class="error">
         <?php echo _("Hey!") ?>
	 <?php echo sprintf(_("%s this album so that the URL is not so generic!"),
			 popup_link(_("Rename"), "rename_album.php?set_albumName={$tmpAlbumName}&index=$i")) ?>
        </span>
   	<?php } ?>
   <?php } ?>
   <?php if ($gallery->album->versionOutOfDate()) { ?>
    <?php if ($gallery->user->isAdmin()) { ?>
  <br>
  <span class="error">
   <?php echo _("Note:  This album is out of date!") ?> <?php echo popup_link("[" . _("upgrade album") ."]", "upgrade_album.php") ?>
  </span>
    <?php } ?>
   <?php } ?>
  <?php } ?>

  <br>
  <span class="fineprint">
   <?php echo sprintf(_("Last changed on %s."), $gallery->album->getLastModificationDate() )?>  
   <?php echo sprintf(_("This album contains %s." ), pluralize_n($gallery->album->numPhotos(0), _("item"), _("items"), _("no items")));
if (!($gallery->album->fields["display_clicks"] == "no") && 
	!$gallery->session->offline) {
?>
   <br><br><?php echo sprintf(_("This album has been viewed %s since %s."),
		   pluralize_n($gallery->album->getClicks(), _("time"), _("times") , _("0 times")),
		   $gallery->album->getClicksDate() );
}
$albumName=$gallery->album->fields["name"];
if ($gallery->user->canWriteToAlbum($gallery->album) &&
   (!($gallery->album->fields["display_clicks"] == "no"))) {
?>
<?php echo popup_link("[" . _("reset counter") ."]", "'" . doCommand("reset-album-clicks", array("set_albumName" => $albumName), "albums.php") . "'" , 1) ?>

<?php
}
?>
  </span>
  </td>
<?php if (!strcmp($gallery->app->showAlbumTree, "yes")) { ?>
  <td valign="top">
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

