<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2004 Bharat Mediratta
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
require_once(dirname(__FILE__) . '/init.php');

if (empty($gallery->session->username)) {
    /* Get the cached version if possible */
	$cache_file = "cache.html";
	if (!getRequestVar('gallery_nocache') && fs_file_exists($cache_file)) {
	$cache_now = time();
	$cache_stat = @stat("cache.html");
	if ($cache_now - $cache_stat[9] < (20 * 60)) {
	    if ($fp = fopen("cache.html", "rb")) {
		while (!feof($fp)) {
		    print fread($fp, 4096);
		}
		fclose($fp);

		printf("<!-- From cache, created at %s -->",
		    strftime("%D %T", $cache_stat[9]));
		return;
	    }
	}
    }
}

$gallery->session->offlineAlbums["albums.php"]=true;

/* Read the album list */
$albumDB = new AlbumDB(FALSE);
$gallery->session->albumName = "";
$page = 1;

/* If there are albums in our list, display them in the table */
list ($numPhotos, $numAccess, $numAlbums) = $albumDB->numAccessibleItems($gallery->user);

if (empty($gallery->session->albumListPage)) {
	$gallery->session->albumListPage = 1;
}
$perPage = $gallery->app->albumsPerPage;
$maxPages = max(ceil($numAlbums / $perPage), 1);

if ($gallery->session->albumListPage > $maxPages) {
	$gallery->session->albumListPage = $maxPages;
}

$pixelImage = '<img src="' . getImagePath('pixel_trans.gif') . '" width="1" height="1" alt="pixel_trans">';
$borderColor = $gallery->app->default["bordercolor"];

$navigator["page"] = $gallery->session->albumListPage;
$navigator["pageVar"] = "set_albumListPage";
$navigator["url"] = makeGalleryUrl("albums.php");
$navigator["maxPages"] = $maxPages;
$navigator["spread"] = 6;
$navigator["fullWidth"] = 100;
$navigator["widthUnits"] = "%";
$navigator["bordercolor"] = $borderColor;

$displayCommentLegend = 0;  // this determines if we display "* Item contains a comment" at end of page 

/*
** when direction is ltr(left to right) everything is fine)
** when rtl(right to left), like in hebrew, we have to switch the alignment at some places.
*/
if ($gallery->direction == 'ltr') {
	$left="left";
	$right="right";
}
else {
	$left="right";
	$right="left";
}
if (!$GALLERY_EMBEDDED_INSIDE) {
	doctype();
?>
<html>
<head>
  <title><?php echo $gallery->app->galleryTitle ?></title>
  <?php
	common_header() ;

	/* prefetching/navigation */
  if ($navigator['page'] > 1) { ?>
  <link rel="top" href="<?php echo makeGalleryUrl('albums.php', array('set_albumListPage' => 1)) ?>">
  <link rel="first" href="<?php echo makeGalleryUrl('albums.php', array('set_albumListPage' => 1)) ?>">
  <link rel="prev" href="<?php echo makeGalleryUrl('albums.php', array('set_albumListPage' => $navigator['page']-1)) ?>">
<?php }
  if ($navigator['page'] < $maxPages) { ?>
  <link rel="next" href="<?php echo makeGalleryUrl('albums.php', array('set_albumListPage' => $navigator['page']+1)) ?>">
  <link rel="last" href="<?php echo makeGalleryUrl('albums.php', array('set_albumListPage' => $maxPages)) ?>">
<?php }
	if ($gallery->app->rssEnabled == "yes" && !$gallery->session->offline) {
?>
  <link rel="alternate" title="<?php echo sprintf(_("%s RSS"), $gallery->app->galleryTitle) ?>" href="<?php echo $gallery->app->photoAlbumURL . "/rss.php" ?>" type="application/rss+xml">
<?php } ?>
</head>
<body dir="<?php echo $gallery->direction ?>">
<?php }
	includeHtmlWrap("gallery.header");
	if (!$gallery->session->offline && 
		( (!strcmp($gallery->app->showSearchEngine, "yes") && $numPhotos != 0 ) || $GALLERY_EMBEDDED_INSIDE =='phpBB2')) {
?>
<table width="100%" border="0" cellspacing="0" style="margin-bottom:2px">
<tr>
<?php
	if ($GALLERY_EMBEDDED_INSIDE =='phpBB2') {
		echo '<td class="nav"><a href="index.php">'. sprintf($lang['Forum_Index'], $board_config['sitename']) . '</a></td>';
}
	if ($numPhotos != 0) {
		echo '<td valign="middle" align="right">';
		echo makeFormIntro('search.php', array(
							'name'		=> 'search_form',
							'method'	=> 'post',
							'style'		=> 'margin-bottom: 0px;'));
		echo '<span class="search">'. _("Search") .': </span>';
		echo '<input style="font-size:10px;" type="text" name="searchstring" value="" size="25">';
		echo '</form></td>';
	}
?>
</tr>
</table>
<?php	} ?>

<!-- admin section begin -->
<?php 
$adminText = "<span class=\"admin\">";
if ($numAccess == $numAlbums) {
	$toplevel_str= pluralize_n2(ngettext("1 album","%d albums",$numAlbums), $numAlbums, _("no albums"));
} else {
	$toplevel_str= pluralize_n2(ngettext("1 top-level album","%d top-level albums",$numAlbums), $numAlbums, _("No top-level albums"));
}

$total_str= sprintf(_("%d total"), $numAccess); 
$image_str= pluralize_n2(ngettext("1 image", "%d images", $numPhotos), $numPhotos, _("no images"));
$page_str= pluralize_n2(ngettext("1 page", "%d pages", $maxPages), $maxPages, _("no pages"));

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

	$displayName = $gallery->user->displayName();
	$adminCommands .= sprintf(_("Welcome, %s"), $displayName) . "&nbsp;&nbsp;<br>";
}

if ($gallery->app->gallery_slideshow_type != "off" && $numPhotos != 0) {
    	 $adminCommands .= "\n". '<a class="admin" style="white-space:nowrap;" href="' . makeGalleryUrl("slideshow.php",
	 array("set_albumName" => null)) .
	       	'">['._("slideshow") . ']</a> ';
}

if ($gallery->user->isAdmin()) {
	$doc = galleryDocs('admin');
	if ($doc) {
		$adminCommands .= "$doc ";
	}
	$adminCommands .= '<a class="admin" style="white-space:nowrap;" href="' . $gallery->app->photoAlbumURL . '/setup/index.php">[' . _("configuration wizard") .']</a> ';
	$adminCommands .= '<a class="admin" style="white-space:nowrap;" href="' . makeGalleryUrl('tools/find_orphans.php') . '">[' . _("find orphans") .']</a> ';
	$adminCommands .= '<a class="admin" style="white-space:nowrap;" href="' . makeGalleryUrl('tools/despam-comments.php') . '">[' . _("find comment spam") .']</a> ';
}

if ($gallery->user->canCreateAlbums() && !$gallery->session->offline) { 
	$adminCommands .= '<a class="admin" style="white-space:nowrap;" href="' . doCommand("new-album", array(), "view_album.php") . '">[' . _("new album") . ']</a> ';
}

if ($gallery->user->isAdmin()) {
	if ($gallery->userDB->canModifyUser() ||
	    $gallery->userDB->canCreateUser() ||
	    $gallery->userDB->canDeleteUser()) {
		$adminCommands .= popup_link("[" . _("manage users") . "]", 
			"manage_users.php", false, true, 500, 500, 'admin')
			. ' ';
	}
}

if ($gallery->user->isLoggedIn() && !$gallery->session->offline) {
	if ($gallery->userDB->canModifyUser()) {
		$adminCommands .= popup_link("[". _("preferences") ."]", 
			"user_preferences.php", false, true, 500, 500, 'admin')
			. ' ';
	}
	
	if (!$GALLERY_EMBEDDED_INSIDE) {
		$adminCommands .= '<a class="admin" style="white-space:nowrap;" href="' . doCommand("logout", array(), "albums.php"). '">[' . _("logout") .']</a>';
	}
} else {
	if (!$GALLERY_EMBEDDED_INSIDE) {
	        $adminCommands .= popup_link("[" . _("login") . "]", "login.php", false, true, 500, 500, 'admin');
		
            if (!strcmp($gallery->app->selfReg, 'yes')) {
                $adminCommands .= ' ';
                $adminCommands .= popup_link('[' . _("register") . ']', 'register.php', false, true, 500, 500, 'admin');
            }
	}
}

$adminCommands .= "</span>";
$adminbox["text"] = $adminText;
$adminbox["commands"] = $adminCommands;
$adminbox["bordercolor"] = $borderColor;
$adminbox["top"] = true;
includeLayout('navtablebegin.inc');
includeLayout('adminbox.inc');
includeLayout('navtablemiddle.inc');

echo "<!-- Begin top nav -->";

includeLayout('navigator.inc');
includeLayout('navtableend.inc');
includeLayout('ml_pulldown.inc');

echo "<!-- End top nav -->";

/* Display warnings about broken albums */
if ( (sizeof($albumDB->brokenAlbums) || sizeof($albumDB->outOfDateAlbums)) && $gallery->user->isAdmin()) {

	echo "\n<center><div style=\"width:60%; border-style:outset; border-width:5px; border-color:red; padding: 5px\">";
	echo "\n<p class=\"head\"><u>". _("Attention Gallery Administrator!") ."</u></p>";

	if (sizeof($albumDB->brokenAlbums)) {
		echo sprintf(_("%s has detected the following %d invalid album(s) in your albums directory<br>(%s):"),
		    Gallery(), sizeof($albumDB->brokenAlbums), $gallery->app->albumDir);
		echo "\n<p>";
		foreach ($albumDB->brokenAlbums as $tmpAlbumName) {
			echo "<br>$tmpAlbumName\n";
		}
	echo "\n</p>". _("Please move it/them out of the albums directory.") ;
	}

	if(sizeof($albumDB->outOfDateAlbums)) {
		echo sprintf(_("%s has detected that %d of your albums are out of date."),
			Gallery(), sizeof($albumDB->outOfDateAlbums));

		echo "\n<br>";
		echo sprintf(_("Please %s."), popup_link(_("upgrade those albums"), "upgrade_album.php"));
	}
	echo "\n</div></center>\n";
}
?>

<!-- album table begin -->
<table width="100%" border="0" cellpadding=0 cellspacing=7>

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
  <td height="1"><?php echo $pixelImage ?></td>
<?php
  if (!strcmp($gallery->app->showAlbumTree, "yes")) {
?>
  <td height="1"><?php echo $pixelImage ?></td>

<?php
  }
?>
  </tr>
  <tr>
  <!-- Begin Image Cell -->
  <td align="center" valign="top">

<?php
      $gallery->html_wrap['borderColor'] = $borderColor;
      $gallery->html_wrap['borderWidth'] = 1;
      $gallery->html_wrap['pixelImage'] = getImagePath('pixel_trans.gif');
      $scaleTo = $gallery->app->highlight_size;
      list($iWidth, $iHeight) = $gallery->album->getHighlightDimensions($scaleTo);
      if (!$iWidth) {
	  $iWidth = $gallery->app->highlight_size;
	  $iHeight = 100;
      }
      $gallery->html_wrap['imageWidth'] = $iWidth;
      $gallery->html_wrap['imageHeight'] = $iHeight;
      $gallery->html_wrap['imageTag'] = $gallery->album->getHighlightTag($scaleTo,'', _("Highlight for Album:"). " ". gallery_htmlentities(removeTags($gallery->album->fields["title"])));
      $gallery->html_wrap['imageHref'] = $albumURL;
      $gallery->html_wrap['frame'] = $gallery->app->gallery_thumb_frame_style;
      includeHtmlWrap('inline_gallerythumb.frame');
?>
  </td>
  <!-- End Image Cell -->
  <!-- Begin Text Cell -->
  <td align="<?php echo $left ?>" valign="top" class="albumdesc">
    <table cellpadding="0" cellspacing="0" width="100%" border="0" align="center" class="mod_title">
      <tr valign="middle">
        <td class="leftspacer"></td>
        <td>
          <table cellspacing="0" cellpadding="0" border="0" class="mod_title_bg">
            <tr>
              <td class="mod_title_left"></td>
              <td nowrap class="title">
                <?php _("title") ?>
                <?php echo editField($gallery->album, "title", $albumURL) ?>
              </td>
              <td class="mod_title_right"></td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
    <table width="100%" cellpadding="0" cellspacing="0" border="0">
      <tr>
        <td class="mod_titleunder_hl"></td>
      </tr>
    </table>

  <?php 
	include(dirname(__FILE__) . '/layout/adminAlbumCommands.inc');

	$description=editField($gallery->album, "description") ;
	if ($description != "") {
		echo "\n<div class=\"desc\">";
		echo "\n\t$description";
		echo "\n</div>";
  	}

	if (strcmp($gallery->app->showOwners, "no")) {
		echo "\n<div class=\"desc\">";
		echo _("Owner:") . ' '. showOwner($owner);
		echo '</div>';
	}

	if ($gallery->user->isAdmin() || $gallery->user->isOwnerOfAlbum($gallery->album)) {
		echo _("url:") . '<a href="'. $albumURL . '">';
		if (!$gallery->session->offline) {
			echo breakString(urldecode($albumURL), 60, '&', 5);
		} else {
			echo $tmpAlbumName;
		}
		echo '</a>';

		if (ereg("album[[:digit:]]+$", $albumURL)) {
			if (!$gallery->session->offline) {
				echo '<br><span class="error">' .
				_("Hey!") .
				sprintf(_("%s so that the URL is not so generic!"), 
					popup_link(_("Rename this album"), "rename_album.php?set_albumName={$tmpAlbumName}&index=$i"));
				echo '</span>';
			}
		}

	} 
	?>

  <br>
  <span class="fineprint">
   <?php 
	echo sprintf(_("Last changed on %s."), $gallery->album->getLastModificationDate() );
	$visibleItems=array_sum($gallery->album->numVisibleItems($gallery->user));
	echo " "; // Need a space between these two text blocks
	echo pluralize_n2(ngettext("This album contains 1 item", "This album contains %d items", $visibleItems), $visibleItems);
	if (!($gallery->album->fields["display_clicks"] == "no") && !$gallery->session->offline) {
?>
   <br><br><?php
	$clickCount=$gallery->album->getClicks();
	echo sprintf(_("This album has been viewed %s since %s."),
		pluralize_n2(ngettext("1 time", "%d times", $clickCount), $clickCount, _("0 times")),
		$gallery->album->getClicksDate());
}
$albumName=$gallery->album->fields["name"];
if ($gallery->user->canWriteToAlbum($gallery->album) &&
   (!($gallery->album->fields["display_clicks"] == "no"))) {
	echo " ".popup_link("[" . _("reset counter") ."]", doCommand("reset-album-clicks", array("set_albumName" => $albumName), "albums.php"), 1);
}
if($gallery->app->comments_enabled == 'yes') {
	// if comments_indication are "albums" or "both"
	switch ($gallery->app->comments_indication) {
	case "albums":
        case "both":
		$lastCommentDate = $gallery->album->lastCommentDate($gallery->app->comments_indication_verbose);
		print lastCommentString($lastCommentDate, $displayCommentLegend);
	} // end switch
}
?>

  </span>
  </td>
<?php if (!strcmp($gallery->app->showAlbumTree, "yes")) { ?>
  <td align=left valign=top class="albumdesc">
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
<?php 
if ($displayCommentLegend) { 
	//display legend for comments
	echo '<p><span class="commentIndication">*</span>';
	echo '<span class="fineprint">'. _("Comments available for this item.") .'</span></p>';
} 
?>
<!-- bottom nav -->
<?php
includeLayout('navtablebegin.inc');
includeLayout('navigator.inc');
includeLayout('navtableend.inc');
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
