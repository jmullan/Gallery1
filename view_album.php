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
// Hack check
if (!$user->canReadAlbum($album)) {
	header("Location: albums.php");
	return;
}

if (!$album->isLoaded()) {
	header("Location: albums.php");
	return;
}

$numPhotos = $album->numPhotos($user->canWriteToAlbum($album));
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

#-- if borders are off, just make them the bgcolor ----
if (!strcmp($album->fields["border"], "off")) {
	$bordercolor = $album->fields["bgcolor"];
	$borderwidth = 4;
} else {
	$bordercolor = $album->fields["bordercolor"];
	$borderwidth = $album->fields["border"];
}

$imageCellWidth = floor(100 / $cols) . "%";
$fullWidth = $cols * $album->fields["thumb_size"];

// Account for cell spacing/padding
$fullWidth += ($cols * 5); 

$navigator["page"] = $page;
$navigator["pageVar"] = "page";
$navigator["maxPages"] = $maxPages;
$navigator["fullWidth"] = $fullWidth;
$navigator["url"] = "view_album.php";
$navigator["spread"] = 5;
$navigator["bordercolor"] = $bordercolor;

$breadcrumb["text"][0] = "Gallery: <a href=albums.php>".$app->galleryTitle."</a>";
$breadcrumb["bordercolor"] = $bordercolor;
?>

<head>
  <title><?= $app->galleryTitle ?> :: <?= $album->fields["title"] ?></title>
  <link rel="stylesheet" type="text/css" href="<?= getGalleryStyleSheetName() ?>">  
  <style type="text/css">
<?
// the link colors have to be done here to override the style sheet 
if ($album->fields["linkcolor"]) {
?>
    A:link, A:visited, A:active
      { color: <?= $album->fields[linkcolor] ?>; }
    A:hover
      { color: #ff6600; }
<?
}
if ($album->fields["bgcolor"]) {
	echo "BODY { background-color:".$album->fields[bgcolor]."; }";
}
if ($album->fields["background"]) {
	echo "BODY { background-image:".$album->fields[background]."; } ";
}
if ($album->fields["textcolor"]) {
	echo "BODY, TD {color:".$album->fields[textcolor]."; }";
	echo ".head {color:".$album->fields[textcolor]."; }";
	echo ".headbox {background-color:".$album->fields[bgcolor]."; }";
}
?>
  </style>
  <script language="javascript1.2">
  // <!--
  var statusWin;
  function showProgress() {
	statusWin = <?=popup_status("progress_uploading.php");?>
  }

  function hideProgress() {
	if (statusWin != undefined) {
		statusWin.close();
		statusWin = null;
	}
  }

  // --> 
  </script>
</head>

<body onUnload='hideProgress()'> 

<? 
includeHtmlWrap("album.header");

$adminText = "<span class=\"admin\">";
if ($numPhotos == 1) {  
	$adminText .= "1 photo in this album";
} else {
	$adminText .= "$numPhotos photos in this album on $maxPages pages";
}

if ($user->canWriteToAlbum($album)) {
	$hidden = $album->numHidden();
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
if ($user->canAddToAlbum($album)) {
	$adminCommands .= "<a href=".popup("add_photos.php?albumName=$albumName").">[Add Photos]</a>&nbsp;";
}

if ($user->canWriteToAlbum($album)) {
	if ($album->numPhotos(1)) {
	        $adminCommands .= "<a href=".popup("shuffle_album.php?albumName=$albumName").">[Shuffle]</a>&nbsp;";
	        $adminCommands .= "<a href=".popup("resize_photo.php?albumName=$albumName&index=all").">[Resize All]</a>&nbsp;";
	        $adminCommands .= "<a href=".popup("do_command.php?cmd=remake-thumbnail&albumName=$albumName&index=all").">[Rebuild Thumbs]</a>&nbsp;&nbsp;<br>"; 
	}
        $adminCommands .= "<a href=".popup("edit_appearance.php?albumName=$albumName").">[Properties]</a>&nbsp;";
        $adminCommands .= "<a href=".popup("album_permissions.php?set_albumName=$albumName").">[Permissions]</a>&nbsp;";
}


if ($user->isLoggedIn()) {
        $adminCommands .= "<a href=do_command.php?cmd=logout&return=view_album.php>[Logout]</a>";
} else {
	$adminCommands .= "<a href=".popup("login.php").">[Login]</a>";
} 
$adminCommands .= "</span>";
$adminbox["text"] = $adminText;
$adminbox["commands"] = $adminCommands;
$adminbox["bordercolor"] = $bordercolor;
$adminbox["top"] = true;
include ("layout/adminbox.inc");
?>

<!-- top nav -->
<?
$breadcrumb["top"] = true;
if (strcmp($album->fields["returnto"], "no")) {
	include("layout/breadcrumb.inc");
}
include("layout/navigator.inc");
?>


<!-- image grid table -->
<br>
<table width=400 border=0>
<?
$numPhotos = $album->numPhotos(1);
if ($numPhotos) {

	$rowCount = 0;
	while ($rowCount < $rows) {
		/* Do the inline_photo header row */
		echo("<tr>");
		$i = $start + $rowCount * $cols;
		$j = 1;
		while ($j <= $cols && $i <= $numPhotos) {
			echo("<td>");
			includeHtmlWrap("inline_photo.header");
			echo("</td>");
			$j++; $i++;
		}
		echo("</tr>");

		/* Do the picture row */
		echo("<tr>");
		$i = $start + $rowCount * $cols;
		$j = 1;
		while ($j <= $cols && $i <= $numPhotos) {
			if (!$user->canWriteToAlbum($album) && $album->isHidden($i)) {
				$i++;
				if ($i >= $numPhotos) {
					break;
				}
			}

			echo("<td width=$imageCellWidth align=center valign=middle>");

			echo("<table width=1% border=0 cellspacing=0 cellpadding=0>");
			echo("<tr bgcolor=$bordercolor>"); 
			echo("<td height=$borderwidth width=$borderwidth><img src=images/pixel_trans.gif></td>");
			echo("<td height=$borderwidth><img src=images/pixel_trans.gif></td>");
			echo("<td height=$borderwidth width=$borderwidth><img src=images/pixel_trans.gif></td>");
			echo("</tr>");
			echo("<tr>");
			echo("<td bgcolor=$bordercolor width=$borderwidth>");
			for ($k=0; $k<$borderwidth; $k++) {
				echo("<img src=images/pixel_trans.gif>");
			}
			echo("</td>");
			echo("<td>");

			if ($album->isMovie($i)) {
				echo("<a href=" . $album->getPhotoPath($i) . " target=other>" . 
					$album->getThumbnailTag($i) .
					"</a>");
			} else {
				echo("<a href=" . makeGalleryUrl($albumName, $album->getPhotoId($i)) . ">" .
					$album->getThumbnailTag($i) .
					"</a>");
			}
			echo("</td>");
			echo("<td bgcolor=$bordercolor width=$borderwidth>");
			for ($k=0; $k<$borderwidth; $k++) {
				echo("<img src=images/pixel_trans.gif>");
			}
			echo("</td>");
			echo("</tr>");	
			echo("<tr bgcolor=$bordercolor>"); 
			echo("<td height=$borderwidth width=$borderwidth><img src=images/pixel_trans.gif></td>");
			echo("<td height=$borderwidth><img src=images/pixel_trans.gif></td>");
			echo("<td height=$borderwidth width=$borderwidth><img src=images/pixel_trans.gif></td>");
			echo("</tr>");
			echo("</table>");


			echo("</td>");
			$j++; $i++;
		}
		echo("</tr>");
	
		/* Now do the caption row */
		echo("<tr>");
		$i = $start + $rowCount * $cols;
		$j = 1;
		while ($j <= $cols && $i <= $numPhotos) {
			if (!$user->canWriteToAlbum($album) && $album->isHidden($i)) {
				$i++;
				if ($i >= $numPhotos) {
					break;
				}
			}

			echo("<td width=$imageCellWidth valign=top align=center>");
			echo "<center><span class=\"caption\">";
			echo(editCaption($album, $i, $edit));
			echo "</span>";
			if ($user->canDeleteFromAlbum($album)) {
				echo("<a href=");
				echo(popup("delete_photo.php?index=$i"));
				echo("><br><img src=\"images/admin_delete.gif\" width=11 height=11 border=0 alt=\"Delete Photo\"></a>");
				if (!$album->isMovie($i)) {
					//echo(" <a href=");
					//echo(popup("do_command.php?cmd=remake-thumbnail&index=$i"));
					//echo(">[Thumbnail]/a>");
				}
			}

			if ($user->canWriteToAlbum($album)) {
				echo(" <a href=");
				echo(popup("move_photo.php?index=$i"));
				echo("><img src=\"images/admin_move.gif\" width=11 height=11 border=0 alt=\"Move Photo\"></a>");
				if (!$album->isMovie($i)) {
					echo(" <a href=");
					echo(popup("rotate_photo.php?index=$i"));
					echo("><img src=\"images/admin_rotate.gif\" width=11 height=11 border=0 alt=\"Rotate Photo\"></a>");
					echo(" <a href=");
					echo(popup("highlight_photo.php?index=$i"));
					echo("><img src=\"images/admin_highlight.gif\" width=11 height=11 border=0 alt=\"Highlight Photo\"></a>");
				}
				if ($album->isHidden($i)) {
					echo("<a href=do_command.php?cmd=show&index=$i&return=view_album.php><img src=\"images/admin_unhide.gif\" width=11 height=11 border=0 alt=\"Show Photo\"></a>");
				} else {
					echo("<a href=do_command.php?cmd=hide&index=$i&return=view_album.php><img src=\"images/admin_hide.gif\" width=11 height=11 border=0 alt=\"Hide Photo\"></a>");
				}
			}
			echo("</td>");
			$j++; $i++;
		}
		echo "</tr>";

		/* Now do the inline_photo footer row */
		echo("<tr>");
		$i = $start + $rowCount * $cols;
		$j = 1;
		while ($j <= $cols && $i <= $numPhotos) {
			echo("<td>");
			includeHtmlWrap("inline_photo.footer");
			echo("</td>");
			$j++; $i++;
		}
		echo("</tr>");
		$rowCount++;
	}
} else {
?>

	<td colspan=$rows align=center class="headbox">
<? if ($user->canAddToAlbum($album)) { ?>
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
<br>
<!-- bottom nav -->
<? 
include("layout/navigator.inc");
if (strcmp($album->fields["returnto"], "no")) {
	$breadcrumb["top"] = false;
	include("layout/breadcrumb.inc");
}


includeHtmlWrap("album.footer");
?>
</body>
</html>


