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
// Hack check
if (!$gallery->user->canReadAlbum($gallery->album)) {
	header("Location: albums.php");
	return;
}

if (!$gallery->album->isLoaded()) {
	header("Location: albums.php");
	return;
}
if (!$page) {
	$page = 1;
}
$albumName = $gallery->session->albumName;

if (!$viewedAlbum[$albumName]) {
	setcookie("viewedAlbum[$albumName]","1");
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

if ($gallery->album->fields[parentAlbumName]) {
	$top = $gallery->app->photoAlbumURL;
	$myAlbum= new Album();
	$myAlbum->load($gallery->album->fields[parentAlbumName]);
	$breadtext[0] = "Gallery: <a href=". makeGalleryUrl("albums.php") . ">".$gallery->app->galleryTitle."</a>";
	$breadtext[1] = "Album: <a href=". makeAlbumUrl($gallery->album->fields[parentAlbumName]).">".$myAlbum->fields["title"]."</a>";
} else {
	$breadtext[0] = "Gallery: <a href=". makeGalleryUrl("albums.php") .">".$gallery->app->galleryTitle."</a>";
}

$breadcrumb["text"] = $breadtext;
$breadcrumb["bordercolor"] = $bordercolor;
?>

<? if (!$GALLERY_EMBEDDED_INSIDE) { ?>
<head>
  <title><?= $gallery->app->galleryTitle ?> :: <?= $gallery->album->fields["title"] ?></title>
  <?= getStyleSheetLink() ?>
  <style type="text/css">
<?
// the link colors have to be done here to override the style sheet 
if ($gallery->album->fields["linkcolor"]) {
?>
    A:link, A:visited, A:active
      { color: <?= $gallery->album->fields[linkcolor] ?>; }
    A:hover
      { color: #ff6600; }
<?
}
if ($gallery->album->fields["bgcolor"]) {
	echo "BODY { background-color:".$gallery->album->fields[bgcolor]."; }";
}
if ($gallery->album->fields["background"]) {
	echo "BODY { background-image:url(".$gallery->album->fields[background]."); } ";
}
if ($gallery->album->fields["textcolor"]) {
	echo "BODY, TD {color:".$gallery->album->fields[textcolor]."; }";
	echo ".head {color:".$gallery->album->fields[textcolor]."; }";
	echo ".headbox {background-color:".$gallery->album->fields[bgcolor]."; }";
}
?>
  </style>
</head>

<body> 
<? } ?>

  <script language="javascript1.2">
  // <!--
  var statusWin;
  function showProgress() {
	statusWin = <?=popup_status("progress_uploading.php");?>
  }

  function hideProgress() {
	if (typeof(statusWin) != "undefined") {
		statusWin.close();
		statusWin = void(0);
	}
  }

  function hideProgressAndReload() {
	hideProgress();
	document.location.reload();
  }

  function imageEditChoice(selected_select) {
	  var sel_index = selected_select.selectedIndex;
	  var sel_value = selected_select.options[sel_index].value;
	  selected_select.options[0].selected = true;
	  selected_select.blur();
	  <?= popup(sel_value, 1) ?>
  } 
  // --> 
  </script>

<? 
includeHtmlWrap("album.header");

function showChoice($label, $target, $args) {
	echo "<option value='" . makeGalleryUrl($target, $args) . "'>$label</option>";
}

$adminText = "<span class=\"admin\">";
if ($numPhotos == 1) {  
	$adminText .= "1 photo in this album";
} else {
	$adminText .= "$numPhotos items in this album";
	if ($maxPages > 1) {
		$adminText .= " on " . pluralize($maxPages, "page");
	}
}

if ($gallery->user->canWriteToAlbum($gallery->album)) {
	$hidden = $gallery->album->numHidden();
	$verb = "are";
	if ($hidden == 1) {
		$verb = "is";
	}
	if ($hidden) {
		$adminText .= " ($hidden $verb hidden)";
	}
} 
$adminText .="</span>";
$adminCommands = "<span class =\"admin\">";

if ($gallery->user->canAddToAlbum($gallery->album)) {
	$adminCommands .= '<a href="#" onClick="'.popup("add_photos.php?albumName=" .
				$gallery->session->albumName).'">[add photos]</a>&nbsp;';
}
if ($gallery->user->canCreateAlbums()) {
	$adminCommands .= '<a href="' . doCommand("new-album", 
						array("parentName" => $gallery->session->albumName),
						 "view_album.php") .
						 '">[new nested album]</a>&nbsp;<br>';
}

if ($gallery->user->canWriteToAlbum($gallery->album)) {
	if ($gallery->album->numPhotos(1)) {
	        $adminCommands .= '<a href="#" onClick="'.popup("sort_album.php?albumName=" .
				$gallery->session->albumName).
				'">[sort]</a>&nbsp;';
	        $adminCommands .= '<a href="#" onClick="'.popup("resize_photo.php?albumName=" .
				$gallery->session->albumName . "&index=all").
				'">[resize all]</a>&nbsp;';
	        $adminCommands .= '<a href="#" onClick="'.popup("do_command.php?cmd=remake-thumbnail&albumName=" .
				$gallery->session->albumName . "&index=all").
				'">[rebuild thumbs]</a>&nbsp;&nbsp;<br>'; 
	}
        $adminCommands .= '<a href="#" onClick="'.popup("edit_appearance.php?albumName=" .
			$gallery->session->albumName).
			'">[properties]</a>&nbsp;';
}

if ($gallery->user->isAdmin() || $gallery->user->isOwnerOfAlbum($gallery->album)) {
        $adminCommands .= '<a href="#" onClick="'.popup("album_permissions.php?set_albumName=" .
			$gallery->session->albumName).
			'">[permissions]</a>&nbsp;';
}



if (!$GALLERY_EMBEDDED_INSIDE) {
	if ($gallery->user->isLoggedIn()) {
	        $adminCommands .= "<a href=" .
					doCommand("logout", array(), "view_album.php", array("page" => $page)) .
				  ">[logout]</a>";
	} else {
		$adminCommands .= '<a href="#" onClick="'.popup("login.php").'">[login]</a>';
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
<?
$breadcrumb["top"] = true;
if (strcmp($gallery->album->fields["returnto"], "no")) {
	include($GALLERY_BASEDIR . "layout/breadcrumb.inc");
}
include($GALLERY_BASEDIR . "layout/navigator.inc");

#-- if borders are off, just make them the bgcolor ----
$borderwidth = $gallery->album->fields["border"];
if (!strcmp($borderwidth, "off")) {
	$bordercolor = $gallery->album->fields["bgcolor"];
	$borderwidth = 1;
}
if ($bordercolor) {
	$bordercolor = "bgcolor=$bordercolor";
}
?>

<!-- image grid table -->
<br>
<table width=<?=$fullWidth?> border=0>
<?
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
			echo("<table width=1% border=0 cellspacing=0 cellpadding=0>");
			echo("<tr $bordercolor>"); 
			echo("<td colspan=3 height=$borderwidth><img src=${GALLERY_BASEDIR}images/pixel_trans.gif></td>");
			echo("</tr><tr>");
			echo("<td $bordercolor width=$borderwidth>");
			echo("<img src=${GALLERY_BASEDIR}images/pixel_trans.gif width=$borderwidth height=1>");
			echo("</td><td>");

			$id = $gallery->album->getPhotoId($i);
			if ($gallery->album->isMovie($id)) {
				echo("<a href=" . $gallery->album->getPhotoPath($i) . " target=other>" . 
					$gallery->album->getThumbnailTag($i) .
					"</a>");
			} elseif ($gallery->album->isAlbumName($i)) {
				$myAlbumName = $gallery->album->isAlbumName($i);
				$myAlbum = new Album();
				$myAlbum->load($myAlbumName);
				if ($myAlbum->numPhotos(1)) {
					$highlight = $myAlbum->getPhoto($myAlbum->getHighlight());
					$myHighlightTag = 
						$highlight->getThumbnailTag($myAlbum->getAlbumDirURL("highlight"));
				} else {
					$myHighlightTag = "<span class=title>Empty!</span>";
				}
				echo("<a href=" . makeAlbumUrl($myAlbumName) . ">" . 
					$myHighlightTag . "</a>");
			} else {
				echo("<a href=" . makeAlbumUrl($gallery->session->albumName, $id) . ">" .
					$gallery->album->getThumbnailTag($i) .
					"</a>");
			}
			echo("</td>");
			echo("<td $bordercolor width=$borderwidth>");
			echo("<img src=${GALLERY_BASEDIR}images/pixel_trans.gif width=$borderwidth height=1>");
			echo("</td>");
			echo("</tr>");	
			echo("<tr $bordercolor>"); 
			echo("<td colspan=3 height=$borderwidth><img src=${GALLERY_BASEDIR}images/pixel_trans.gif></td>");
			echo("</tr>");
			echo("</table>");


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
			echo("<td width=$imageCellWidth valign=bottom align=center>");

			// put form outside caption to compress lines
			echo makeFormIntro("view_album.php", array("name" => "image_form_$i")); 

			echo "<center><span class=\"caption\">";
			$id = $gallery->album->getPhotoId($i);
			if ($gallery->album->isHidden($i)) {
				echo "(hidden)<br>";
			}
			if ($gallery->album->isAlbumName($i)) {
				$myAlbum = new Album();
				$myAlbum->load($gallery->album->isAlbumName($i));
				$myDescription = $myAlbum->fields[description];
				$buf = "";
				$buf = $buf."<b>Album: ".$myAlbum->fields[title]."</b>";
				if ($myDescription != "No description") {
					$buf = $buf."<br>".$myDescription."";
				}
				echo($buf."<br>");
?>
				<br>
				<span class="fineprint">
				   Changed: <?=$myAlbum->getLastModificationDate()?>.  <br>
				   Contains: <?=pluralize($myAlbum->numPhotos($gallery->user->canWriteToAlbum($myAlbum)), "item", "no")?>.<br>
				   <? if (!(strcmp($gallery->album->fields["display_clicks"] , "yes")) && ($myAlbum->getClicks() > 0)) { ?>
				   	Viewed: <?=pluralize($myAlbum->getClicks(), "time", "0")?>.<br>
				   <? } ?>
				</span>
<?
			} else {
				echo($gallery->album->getCaption($i));
				// indicate with * if we have a comment for a given photo
				if ((!strcmp($gallery->album->fields["public_comments"], "yes")) && 
				   ($gallery->album->numComments($i) > 0)) {
					echo("<span class=error>*</span>");
					$displayCommentLegend = 1;
				}
				echo("<br>");
				if (!(strcmp($gallery->album->fields["display_clicks"] , "yes")) && ($gallery->album->getItemClicks($i) > 0)) {
					echo("Viewed: ".pluralize($gallery->album->getItemClicks($i), "time", "0").".<br>");
				}
			}
			echo "</span>";

			if (($gallery->user->canDeleteFromAlbum($gallery->album)) || 
				    ($gallery->user->canWriteToAlbum($gallery->album)) ||
				    ($gallery->user->canChangeTextOfAlbum($gallery->album))) {
				if ($gallery->album->isMovie($id)) {
					$label = "Movie";
				} elseif ($gallery->album->isAlbumName($i)) {
					$label = "Album";
				} else {
					$label = "Photo";
				}
				echo("<select style='FONT-SIZE: 10px;' name='s' ".
					"onChange='imageEditChoice(document.image_form_$i.s)'>");
				echo("<option value=''><< Edit $label>></option>");
			}
			if ($gallery->user->canChangeTextOfAlbum($gallery->album)) {
				if ($gallery->album->isAlbumName($i)) {
					if ($gallery->user->canChangeTextOfAlbum($myAlbum)) {	
						showChoice("Edit Title", 
							"edit_field.php", 
							array("set_albumName" => $myAlbum->fields[name],
								"field" => "title")) . 
						showChoice("Edit Description",
							"edit_field.php",
							array("set_albumName" => $myAlbum->fields[name],
								"field" => "description"));
					}
					if ($gallery->user->isAdmin() || $gallery->user->isOwnerOfAlbum($myAlbum)) {
						showChoice("Rename Album",
							"rename_album.php",
							array("set_albumName" => $myAlbum->fields[name],
								"index" => $i));
					}
				} else {
					showChoice("Edit Caption", "edit_caption.php", array("index" => $i));
				}
			}
			if ($gallery->user->canWriteToAlbum($gallery->album)) {
				if (!$gallery->album->isMovie($id) && !$gallery->album->isAlbumName($i)) {
					showChoice("Edit Thumbnail", "edit_thumb.php", array("index" => $i));
					showChoice("Rotate $label", "rotate_photo.php", array("index" => $i));
				}
				if (!$gallery->album->isMovie($id)) {
					showChoice("Highlight $label", "highlight_photo.php", array("index" => $i));
				}
				if ($gallery->album->isAlbumName($i)) {
					$albumName=$gallery->album->isAlbumName($i);
					showChoice("Reset Counter", "do_command.php",
						array("albumName" => $albumName,
							"cmd" => "reset-album-clicks",
							"return" => urlencode(makeGalleryUrl("view_album.php"))));
					showChoice("Highlight $label", "highlight_photo.php", array("index" => $i));
				}
				showChoice("Move $label", "move_photo.php", array("index" => $i));
				if ($gallery->album->isHidden($i)) {
					showChoice("Show $label", "do_command.php", array("cmd" => "show", "index" => $i));
				} else {
					showChoice("Hide $label", "do_command.php", array("cmd" => "hide", "index" => $i));
				}
			}
			if ($gallery->user->canDeleteFromAlbum($gallery->album)) {
				if($gallery->album->isAlbumName($i)) { 
					if($gallery->user->canDeleteAlbum($myAlbum)) {
						showChoice("Delete $label", "delete_photo.php",
							array("index" => $i, "albumDelete" => 1));
					}
				} else {
					showChoice("Delete $label", "delete_photo.php", array("index" => $i));
				}
			}
			if (($gallery->user->canDeleteFromAlbum($gallery->album)) || 
					($gallery->user->canWriteToAlbum($gallery->album)) ||
					($gallery->user->canChangeTextOfAlbum($gallery->album))) {
				echo('</select>');
			}
			echo('</form></td>');
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
<? if ($gallery->user->canAddToAlbum($gallery->album)) { ?>
	<span class="head">Hey! Add some photos.</span> 
<? } else { ?>
	<span class="head">This album is empty.</span> 
<? } ?>
	</td>
	</tr>
<?
}
?>

</table>

<? if (!strcmp($gallery->album->fields["public_comments"], "yes") && $displayCommentLegend) { //display legend for comments ?>
<span class=error>*</span><span class=fineprint> Comments available for this item.</span>
<br><br>
<? } ?>

<!-- bottom nav -->
<? 
include($GALLERY_BASEDIR . "layout/navigator.inc");
if (strcmp($gallery->album->fields["returnto"], "no")) {
	$breadcrumb["top"] = false;
	include($GALLERY_BASEDIR . "layout/breadcrumb.inc");
}


includeHtmlWrap("album.footer");
?>

<? if (!$GALLERY_EMBEDDED_INSIDE) { ?>
</body>
</html>
<? } ?>
