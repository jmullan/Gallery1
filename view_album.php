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
    $GALLERY_BASEDIR = '';
}
require($GALLERY_BASEDIR . 'init.php'); ?>
<?php 
//Prevent error
if (!$gallery->session->albumName) {
	header("Location: " . makeAlbumUrl());
	return;
}

// Hack check
if (!$gallery->user->canReadAlbum($gallery->album)) {
	header("Location: " . makeAlbumUrl());
	return;
}

if (!$gallery->album->isLoaded()) {
	header("Location: " . makeAlbumUrl());
	return;
}

$gallery->session->offlineAlbums[$gallery->album->fields["name"]]=true;


if (!isset($page)) {
    if (isset($gallery->session->albumPage[$gallery->album->fields['name']])) {
	$page = $gallery->session->albumPage[$gallery->album->fields["name"]];
    } else {
	$page = 1;
    }
} else {
	$gallery->session->albumPage[$gallery->album->fields["name"]] = $page;
}

$albumName = $gallery->session->albumName;

if (!$gallery->session->viewedAlbum[$albumName] && !$gallery->session->offline) {
	$gallery->session->viewedAlbum[$albumName] = 1;
	$gallery->album->incrementClicks();
} 

$rows = $gallery->album->fields["rows"];
$cols = $gallery->album->fields["cols"];
$numPhotos = $gallery->album->numPhotos($gallery->user->canWriteToAlbum($gallery->album));
$perPage = $rows * $cols;
$maxPages = max(ceil($numPhotos / $perPage), 1);

if ($page > $maxPages) {
	$page = $maxPages;
}

$start = ($page - 1) * $perPage + 1;
$end = $start + $perPage;

$nextPage = $page + 1;
if ($nextPage > $maxPages) {
	$nextPage = 1;
        $last = 1;
}

$previousPage = $page - 1;
if ($previousPage == 0) {
	$previousPage = $maxPages;
	$first = 1;
}

$bordercolor = $gallery->album->fields["bordercolor"];

$imageCellWidth = floor(100 / $cols) . "%";
$fullWidth="100%";

$navigator["page"] = $page;
$navigator["pageVar"] = "page";
$navigator["maxPages"] = $maxPages;
$navigator["fullWidth"] = $fullWidth;
$navigator["url"] = makeAlbumUrl($gallery->session->albumName);
$navigator["spread"] = 5;
$navigator["bordercolor"] = $bordercolor;

$breadCount = 0;
$breadtext = array();
$pAlbum = $gallery->album;
do {
  if (!strcmp($pAlbum->fields["returnto"], "no")) {
    break;
  }
  $pAlbumName = $pAlbum->fields['parentAlbumName'];
  if ($pAlbumName && (!$gallery->session->offline 
     || $gallery->session->offlineAlbums[$pAlbumName])) {
	$pAlbum = new Album();
	$pAlbum->load($pAlbumName);
	$breadtext[$breadCount] = _("Album") .": <a href=\"" . makeAlbumUrl($pAlbumName) . 
	"\">" . $pAlbum->fields['title'] . "</a>";
  } elseif (!$gallery->session->offline || $gallery->session->offlineAlbums["albums.php"]) {
	//-- we're at the top! --- 
	$breadtext[$breadCount] = _("Gallery") .": <a href=\"" . makeGalleryUrl("albums.php") . 
	"\">" . $gallery->app->galleryTitle . "</a>"; 
  } 
  elseif ($gallery->session->offline) {	// test is redundant.  offline must be 
  					// true if you reach this line.
	break; 
  }

  $breadCount++;
} while ($pAlbumName);

//-- we built the array backwards, so reverse it now ---
for ($i = count($breadtext) - 1; $i >= 0; $i--) {
	$breadcrumb["text"][] = $breadtext[$i];
}
$breadcrumb["bordercolor"] = $bordercolor;
?>
<?php if (!$GALLERY_EMBEDDED_INSIDE) { ?>
<html> 
<head>
  <title><?php echo $gallery->app->galleryTitle ?> :: <?php echo $gallery->album->fields["title"] ?></title>
  <?php echo getStyleSheetLink() ?>
  <?php /* prefetching/navigation */
  if (!isset($first)) { ?>
      <link rel="first" href="<?php echo makeAlbumUrl($gallery->session->albumName, '', array('page' => 1)) ?>" />
      <link rel="prev" href="<?php echo makeAlbumUrl($gallery->session->albumName, '', array('page' => $previousPage)) ?>" />
  <?php }
  if (!isset($last)) { ?>
      <link rel="next" href="<?php echo makeAlbumUrl($gallery->session->albumName, '', array('page' => $nextPage)) ?>" />
      <link rel="last" href="<?php echo makeAlbumUrl($gallery->session->albumName, '', array('page' => $maxPages)) ?>" />
  <?php } if ($gallery->album->isRoot() && 
  	(!$gallery->session->offline || 
	 $gallery->session->offlineAlbums["albums.php"])) { ?>
  <link rel="up" href="<?php echo makeAlbumUrl(); ?>" />
      <?php
      } else if (!$gallery->session->offline || 
	 $gallery->session->offlineAlbums[$pAlbum->fields['parentAlbumName']]) { ?>
  <link rel="up" href="<?php echo makeAlbumUrl($gallery->album->fields['parentAlbumName']); ?>" />
  <?php } 
  	if (!$gallery->session->offline || 
	 $gallery->session->offlineAlbums["albums.php"]) { ?>
  <link rel="top" href="<?php echo makeGalleryUrl('albums.php', array('set_albumListPage' => 1)) ?>" />
  <?php } ?>
  <style type="text/css">
<?php
// the link colors have to be done here to override the style sheet 
if ($gallery->album->fields["linkcolor"]) {
?>
    A:link, A:visited, A:active
      { color: <?php echo $gallery->album->fields['linkcolor'] ?>; }
    A:hover
      { color: #ff6600; }
<?php
}
if ($gallery->album->fields["bgcolor"]) {
	echo "BODY { background-color:".$gallery->album->fields['bgcolor']."; }";
}
if ($gallery->album->fields["background"]) {
	echo "BODY { background-image:url(".$gallery->album->fields['background']."); } ";
}
if ($gallery->album->fields["textcolor"]) {
	echo "BODY, TD {color:".$gallery->album->fields['textcolor']."; }";
	echo ".head {color:".$gallery->album->fields['textcolor']."; }";
	echo ".headbox {background-color:".$gallery->album->fields['bgcolor']."; }";
}
?>
  </style>
</head>

<body dir=<?php echo $gallery->direction ?>>
<?php } ?>
<?php includeHtmlWrap("album.header"); ?>

<?php if (!$gallery->session->offline) { ?>

  <script language="javascript1.2">
  // <!--
  var statusWin;
  function showProgress() {
	statusWin = <?php echo popup_status("progress_uploading.php"); ?>
  }

  function hideProgress() {
	if (typeof(statusWin) != "undefined") {
		statusWin.close();
		statusWin = void(0);
	}
  }

  function hideProgressAndReload() {
	hideProgress();
	history.go(0);
  }

  function imageEditChoice(selected_select) {
	  var sel_index = selected_select.selectedIndex;
	  var sel_value = selected_select.options[sel_index].value;
	  selected_select.options[0].selected = true;
	  selected_select.blur();
	  <?php echo popup(sel_value, 1) ?>
  } 
  // --> 
  </script>
<?php } ?>

<?php 
function showChoice($label, $target, $args) {
    global $gallery, $showAdminForm;
    if (!$showAdminForm)
    	return;
    
    if (empty($args['set_albumName'])) {
	$args['set_albumName'] = $gallery->session->albumName;
    }
	echo "<option value='" . makeGalleryUrl($target, $args) . "'>$label</option>";
}

for ($i = 1, $numAlbums = 0; $i <= $numPhotos; ++$i) {
	if ($gallery->album->isAlbumName($i))
	$numAlbums++;
}

$adminText = "<span class=\"admin\">";
$albums_str= pluralize_n($numAlbums, _("sub-album"), _("sub-albums"), _("No albums"));
$imags_str= pluralize_n($numPhotos - $numAlbums, _("image"), _("images") , _("no images"));
$pages_str=pluralize_n($maxPages, _("page") , _("pages") , _("0 pages"));

if ($numAlbums && $maxPages > 1) {
	$adminText .= sprintf(_("%s and %s in this album on %s"),
			$albums_str, $imags_str, $pages_str);
} else if ($numAlbums) {
	$adminText .= sprintf(_("%s and %s in this album"),
			$albums_str, $imags_str);
} else if ($maxPages > 1) {
	$adminText .= sprintf(_("%s in this album on %s"),
			$imags_str, $pages_str);
} else {
	$adminText .= sprintf(_("%s in this album"),
			$imags_str);
}

if ($gallery->user->canWriteToAlbum($gallery->album) && 
	!$gallery->session->offline) {
	$hidden = $gallery->album->numHidden();
	$verb = _("%s are hidden");
	if ($hidden == 1) {
		$verb = _("%s is hidden");
	}
	if ($hidden) {
		$adminText .= "(".sprintf($verb, $hidden).")";
	}
} 
$adminText .="</span>";
$adminCommands = "<span class =\"admin\">";

if ($gallery->user->canAddToAlbum($gallery->album)) {
	$adminCommands .= popup_link("[". _("add photos") ."]", 
		"add_photos.php?set_albumName=" .
		$gallery->session->albumName);
	$extraFields = $gallery->album->getExtraFields();
	if (!empty($extraFields)) {
	    $adminCommands .= popup_link("[". _("add photo") ."]",
		    "add_photo.php?set_albumName=" .
		    $gallery->session->albumName);
	}
}
if ($gallery->user->isOwnerOfAlbum($gallery->album)) {
	$adminCommands .= popup_link("[" . _("rename album") ."]",
		"rename_album.php?set_albumName=" .
		$gallery->session->albumName .
		"&index=". $i ."&useLoad=1");
}
if ($gallery->user->canCreateSubAlbum($gallery->album) 
	&& !$gallery->session->offline) {
	$adminCommands .= '<a href="' . doCommand("new-album", 
						array("parentName" => $gallery->session->albumName),
						 "view_album.php") .
						 '">['. _("new nested album") . ']</a> ';
}

if ($gallery->user->canChangeTextOfAlbum($gallery->album)) {
	if (!$gallery->session->offline)
	{
		$adminCommands .= popup_link("[" . _("custom fields") ."]", 
			"extra_fields.php?set_albumName=" .
			$gallery->session->albumName);
		$adminCommands .= '<a href=' . makeGalleryUrl("captionator.php", 
			array("set_albumName" => $gallery->session->albumName, 
				"page" => $page, 
				"perPage" => $perPage)) .
			'>['. _("captions") . ']</a>&nbsp;';
	}
}

if ($gallery->user->canWriteToAlbum($gallery->album)) {
	if ($gallery->album->numPhotos(1)) {
	        $adminCommands .= popup_link("[". _("sort") . "]", "sort_album.php?set_albumName=" .
				$gallery->session->albumName);
	        $adminCommands .= popup_link("[" . _("resize all") . "]", 
			"resize_photo.php?set_albumName=" .
			$gallery->session->albumName . "&index=all");
	        $adminCommands .= popup_link("[" . _("rebuild thumbs") . "]",
			"do_command.php?cmd=remake-thumbnail&set_albumName=" .
				$gallery->session->albumName . "&index=all");
	}
        $adminCommands .= popup_link("[" . _("properties") . "]", 
				"edit_appearance.php?set_albumName=" .
				$gallery->session->albumName);
}

if ($gallery->user->isAdmin() || $gallery->user->isOwnerOfAlbum($gallery->album)) {
        $adminCommands .= popup_link("[" . _("permissions") . "]", 
			"album_permissions.php?set_albumName=" .
			$gallery->session->albumName);
}
if (($gallery->user->isAdmin() || $gallery->user->isOwnerOfAlbum($gallery->album)) &&
	!strcmp($gallery->album->fields["public_comments"],"yes")) { 
    $adminCommands .= '<a href=' . makeGalleryUrl("view_comments.php", array("set_albumName" => $gallery->session->albumName)) . '>[' . _("view&nbsp;all&nbsp;comments") . ']</a>&nbsp;';
}
$adminCommands .= '<a href=' . 
	 makeGalleryUrl("slideshow.php",
		array("set_albumName" => $albumName)) .
	'>['. _("slideshow") .']</a> ';

if (!$GALLERY_EMBEDDED_INSIDE && !$gallery->session->offline) {
	if ($gallery->user->isLoggedIn()) {
	        $adminCommands .= "<a href=" .
					doCommand("logout", array(), "view_album.php", array("page" => $page)) .
				  ">[" . _("logout") . "]</a>";
	} else {
	//	$adminCommands .= popup_link("[". _("login") ."]", "login.php", 0, true, 250, 500);
		$adminCommands .= popup_link("[". _("login") ."]", "login.php", 0);
	} 
}
$adminCommands .= "</span>";
$adminbox["text"] = $adminText;
$adminbox["commands"] = $adminCommands;
$adminbox["bordercolor"] = $bordercolor;
$adminbox["top"] = true;
include ($GALLERY_BASEDIR . "layout/adminbox.inc");
?>

<!-- top nav -->
<?php
$breadcrumb["top"] = true;
$breadcrumb['bottom'] = false;
if (strcmp($gallery->album->fields["returnto"], "no") 
   || ($gallery->album->fields["parentAlbumName"])) {
	include($GALLERY_BASEDIR . "layout/breadcrumb.inc");
}
include($GALLERY_BASEDIR . "layout/navigator.inc");

#-- if borders are off, just make them the bgcolor ----
$borderwidth = $gallery->album->fields["border"];
if (!strcmp($borderwidth, "off")) {
	$bordercolor = $gallery->album->fields["bgcolor"];
	$borderwidth = 1;
}
?>
<?php
if ($page == 1)
{
        print "<center>".$gallery->album->fields["summary"]."</center>";
}
?>

<!-- image grid table -->
<br>
<table width=<?php echo $fullWidth ?> border=0 cellspacing=0 cellpadding=7>
<?php
$numPhotos = $gallery->album->numPhotos(1);
$displayCommentLegend = 0;  // this determines if we display "* Item contains a comment" at end of page
if ($numPhotos) {

	$rowCount = 0;

	// Find the correct starting point, accounting for hidden photos
	$rowStart = 0;
	$cnt = 0;
	while ($cnt < $start) {
		$rowStart = getNextPhoto($rowStart);
		$cnt++;
	}

	while ($rowCount < $rows) {
		/* Do the inline_albumthumb header row */
		echo("<tr>");
		$i = $rowStart;
		$j = 1;

		while ($j <= $cols && $i <= $numPhotos) {
			echo("<td>");
			includeHtmlWrap("inline_albumthumb.header");
			echo("</td>");
			$j++; 
			$i = getNextPhoto($i);
		}
		echo("</tr>");

		/* Do the picture row */
		echo("<tr>");
		$i = $rowStart;
		$j = 1;
		while ($j <= $cols && $i <= $numPhotos) {
			echo("<td width=$imageCellWidth align=center valign=middle>");

			//-- put some parameters for the wrap files in the global object ---
			$gallery->html_wrap['borderColor'] = $bordercolor;
			$gallery->html_wrap['borderWidth'] = $borderwidth;
			$gallery->html_wrap['pixelImage'] = $imageDir . "/pixel_trans.gif";
			$scaleTo = $gallery->album->fields["thumb_size"];
			list($iWidth, $iHeight) = $gallery->album->getThumbDimensions($i, $scaleTo);
			if ($iWidth == 0) {
			    $iWidth = $gallery->album->fields["thumb_size"];
			}
			if ($iHeight == 0) {
			    $iHeight = 100;
			}
			$gallery->html_wrap['thumbWidth'] = $iWidth;
			$gallery->html_wrap['thumbHeight'] = $iHeight;

			$id = $gallery->album->getPhotoId($i);
			if ($gallery->album->isMovie($id)) {
				$gallery->html_wrap['thumbTag'] = $gallery->album->getThumbnailTag($i);
				$gallery->html_wrap['thumbHref'] = $gallery->album->getPhotoPath($i);
				includeHtmlWrap('inline_moviethumb.frame');
			} elseif ($gallery->album->isAlbumName($i)) {
				$myAlbumName = $gallery->album->isAlbumName($i);
				$myAlbum = new Album();
				$myAlbum->load($myAlbumName);

				$gallery->html_wrap['thumbTag'] = $myAlbum->getHighlightAsThumbnailTag($scaleTo);
				$gallery->html_wrap['thumbHref'] = makeAlbumUrl($myAlbumName);
				includeHtmlWrap('inline_albumthumb.frame');

			} else {
				$gallery->html_wrap['thumbTag'] = $gallery->album->getThumbnailTag($i);
				$gallery->html_wrap['thumbHref'] = makeAlbumUrl($gallery->session->albumName, $id);
				includeHtmlWrap('inline_photothumb.frame');
			}

			echo("</td>");
			$j++; 
			$i = getNextPhoto($i);
		}
		echo("</tr>");
	
		/* Now do the caption row */
		echo("<tr>");
		$i = $rowStart;
		$j = 1;
		while ($j <= $cols && $i <= $numPhotos) {
			
			if ($gallery->album->isAlbumName($i)) {
			    $iWidth = $gallery->album->fields['thumb_size'];
			} else {
			    list($iWidth, $iHeight) = $gallery->album->getThumbDimensions($i);
			}
			echo("<td width=$imageCellWidth valign=top align=center>");

			// put form outside caption to compress lines


                        if (!$gallery->session->offline &&
				(($gallery->user->canDeleteFromAlbum($gallery->album)) ||
                                    ($gallery->user->canWriteToAlbum($gallery->album)) ||
                                    ($gallery->user->canChangeTextOfAlbum($gallery->album)) ||
				    (($gallery->album->getItemOwnerModify() || 
				    $gallery->album->getItemOwnerDelete()) && 
				     $gallery->album->isItemOwner($gallery->user->getUid(), $i)))) {
				$showAdminForm = 1;
			} else { 
				$showAdminForm = 0;
			}
			if ($showAdminForm) {
				echo makeFormIntro("view_album.php", array("name" => "image_form_$i")); 
			}
			echo "<table width=$iWidth border=0 cellpadding=0 cellspacing=4><tr><td><span class=\"caption\">";
			$id = $gallery->album->getPhotoId($i);
			if ($gallery->album->isHidden($i) && !$gallery->session->offline) {
				echo "(" . _("hidden") .")<br>";
			}
			if ($gallery->album->isAlbumName($i)) {
				$myAlbum = new Album();
				$myAlbum->load($gallery->album->isAlbumName($i));
				$myDescription = $myAlbum->fields['description'];
				$buf = "";
				$buf = $buf."<b>". _("Album") .": ".$myAlbum->fields['title']."</b>";
				if ($myDescription != _("No description") or $myDescription != "No description") {
					$buf = $buf."<br>".$myDescription."";
				}
				echo($buf."<br>");
?>
				<br>
				<span class="fineprint">
				   <?php echo _("Changed: ") ?><?php echo $myAlbum->getLastModificationDate() ?>.  <br>
				   <?php echo _("Contains: ") ?><?php echo pluralize_n($myAlbum->numPhotos($gallery->user->canWriteToAlbum($myAlbum)), _("item"), _("items"), _("0 items")) ?>.<br>
				   <?php if (!(strcmp($gallery->album->fields["display_clicks"] , "yes")) &&  !$gallery->session->offline && ($myAlbum->getClicks() > 0)) { ?>
				   	<?php echo _("Viewed:") ?> <?php echo pluralize_n($myAlbum->getClicks(), _("time") , _("times"), _("0 times")) ?>.<br>
				   <?php } ?>
				</span>
<?php
			} else {
				echo(nl2br($gallery->album->getCaption($i)));
				echo($gallery->album->getCaptionName($i));
				// indicate with * if we have a comment for a given photo
				if ((!strcmp($gallery->album->fields["public_comments"], "yes")) && 
				   ($gallery->album->numComments($i) > 0)) {
					echo("<span class=error>*</span>");
					$displayCommentLegend = 1;
				}
				echo("<br>");
				if (!(strcmp($gallery->album->fields["display_clicks"] , "yes")) && !$gallery->session->offline && ($gallery->album->getItemClicks($i) > 0)) {
					echo _("Viewed:") ." ".pluralize_n($gallery->album->getItemClicks($i), _("time"), _("times") ,_("0 times")).".<br>";
				}
			}
			echo "</span></td></tr></table>";

			if ($showAdminForm) {
				if ($gallery->album->isMovie($id)) {
					$label = _("Movie");
				} elseif ($gallery->album->isAlbumName($i)) {
					$label = _("Album");
				} else {
					$label = _("Photo");
				}
				echo("<select style='FONT-SIZE: 10px;' name='s' ".
					"onChange='imageEditChoice(document.image_form_$i.s)'>");
				echo("<option value=''><< ". _("Edit") . " $label >></option>");
			}
			if ($gallery->album->getItemOwnerModify() && 
			    $gallery->album->isItemOwner($gallery->user->getUid(), $i) && 
			    !$gallery->album->isAlbumName($i) && 
			    !$gallery->user->canChangeTextOfAlbum($gallery->album)) {
				showChoice("Edit Text", "edit_caption.php", array("index" => $i));
			}
			if ($gallery->album->getItemOwnerModify() && 
			    $gallery->album->isItemOwner($gallery->user->getUid(), $i) && 
			    !$gallery->album->isAlbumName($i) && 
			    !$gallery->album->isMovie($id) &&
			    !$gallery->user->canWriteToAlbum($gallery->album)) {
				showChoice("Edit Thumbnail", "edit_thumb.php", array("index" => $i));
				showChoice("Rotate/Flip $label", "rotate_photo.php", array("index" => $i));
			}
			if ($gallery->album->getItemOwnerDelete() && 
			    $gallery->album->isItemOwner($gallery->user->getUid(), $i) && 
			    !$gallery->album->isAlbumName($i) &&
			    !$gallery->user->canDeleteFromAlbum($gallery->album)) {
				showChoice("Delete $label", "delete_photo.php", array("id" => $id));
			}
			if ($gallery->user->canChangeTextOfAlbum($gallery->album)) {
				if ($gallery->album->isAlbumName($i)) {
					if ($gallery->user->canChangeTextOfAlbum($myAlbum)) {	
						_("title");
						showChoice(_("Edit Title"),
							"edit_field.php", 
							array("set_albumName" => $myAlbum->fields["name"],
								"field" => "title")) . 
						_("description");
						showChoice(_("Edit Description"),
							"edit_field.php",
							array("set_albumName" => $myAlbum->fields["name"],
								"field" => "description"));
					}
					if ($gallery->user->isAdmin() || $gallery->user->isOwnerOfAlbum($myAlbum)) {
						showChoice(_("Rename Album"),
							"rename_album.php",
							array("set_albumName" => $myAlbum->fields["name"],
							      "index" => $i));
					}
				} else {
					showChoice(_("Edit Text"), "edit_caption.php", array("index" => $i));
				}
			}
			if ($gallery->user->canWriteToAlbum($gallery->album)) {
				if (!$gallery->album->isMovie($id) && !$gallery->album->isAlbumName($i)) {
					showChoice(_("Edit Thumbnail"), "edit_thumb.php", array("index" => $i));
					showChoice(_("Rotate/Flip") ." $label", "rotate_photo.php", array("index" => $i));
				}
				if (!$gallery->album->isMovie($id)) {
					showChoice(_("Highlight") . " $label", "highlight_photo.php", array("index" => $i));
				}
				if ($gallery->album->isAlbumName($i)) {
				        $myAlbumName = $gallery->album->isAlbumName($i);

					showChoice(_("Reset Counter"), "do_command.php",
						array("cmd" => "reset-album-clicks",
						      "set_albumName" => $myAlbumName,
							"return" => urlencode(makeGalleryUrl("view_album.php"))));
				}
				showChoice(_("Move ") . $label, "move_photo.php", array("index" => $i));
				if ($gallery->album->isHidden($i)) {
 					showChoice(_("Show") . " $label", "do_command.php", array("cmd" => "show", "index" => $i));
			             } else {
			                showChoice(_("Hide") . " $label", "do_command.php", array("cmd" => "hide", "index" => $i));
				}
			}
			if ($gallery->user->canDeleteFromAlbum($gallery->album)) {
				if($gallery->album->isAlbumName($i)) { 
					if($gallery->user->canDeleteAlbum($myAlbum)) {
						showChoice(_("Delete") . " $label", "delete_photo.php",
							array("id" => $myAlbum->fields["name"],
							      "albumDelete" => 1));
					}
				} else {
					showChoice(_("Delete") ." $label", "delete_photo.php",
						   array("id" => $id));
				}
			}
			if($gallery->album->isAlbumName($i)) { 
			    if ($gallery->user->isAdmin() || $gallery->user->isOwnerOfAlbum($myAlbum) && $showAdminForm) {
				showChoice(_("Permissions"), "album_permissions.php",
					   array("set_albumName" => $myAlbum->fields["name"]));
			    }
			}
                       if ($gallery->user->isAdmin())
                       {
                               showChoice(_("Change Owner"), "photo_owner.php", array("id" => $id));
                       }

			if ($showAdminForm) {
				echo('</select></form>');
			}
			echo('</td>');
			$j++;
			$i = getNextPhoto($i);
		}
		echo "</tr>";


		/* Now do the inline_albumthumb footer row */
		echo("<tr>");
		$i = $rowStart;
		$j = 1;
		while ($j <= $cols && $i <= $numPhotos) {
			echo("<td>");
			includeHtmlWrap("inline_albumthumb.footer");
			echo("</td>");
			$j++;
			$i = getNextPhoto($i);
		}
		echo("</tr>");
		$rowCount++;
		$rowStart = $i;
	}
} else {
?>

	<td colspan=$rows align=center class="headbox">
<?php if ($gallery->user->canAddToAlbum($gallery->album) && !$gallery->session->offline) { ?>
	<span class="head"><?php echo _("Hey! Add some photos.") ?></span>
<?php } else { ?>
	<span class="head"><?php echo _("This album is empty.") ?></span>
<?php } ?>
	</td>
	</tr>
<?php
}
?>

</table>

<?php if (!strcmp($gallery->album->fields["public_comments"], "yes") && $displayCommentLegend) { //display legend for comments ?>
<span class=error>*</span><span class=fineprint> <?php echo _("Comments available for this item.") ?></span>
<br><br>
<?php } ?>

<!-- bottom nav -->
<?php 
include($GALLERY_BASEDIR . "layout/navigator.inc");
if (strcmp($gallery->album->fields["returnto"], "no")) {
	$breadcrumb["top"] = false;
	include($GALLERY_BASEDIR . "layout/breadcrumb.inc");
}


include($GALLERY_BASEDIR . "layout/ml_pulldown.inc");
includeHtmlWrap("album.footer");
?>

<?php if (!$GALLERY_EMBEDDED_INSIDE) { ?>
</body>
</html>
<?php } ?>
