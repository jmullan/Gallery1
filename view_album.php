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
<? require_once('init.php'); ?>
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

if (!$page) {
	$page = 1;
}


$rows = $album->fields["rows"];
$cols = $album->fields["cols"];
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

$bordercolor = $album->fields["bordercolor"];

$imageCellWidth = floor(100 / $cols) . "%";
$fullWidth = $cols * $album->fields["thumb_size"];

// Account for cell spacing/padding
$fullWidth += ($cols * 5); 

$navigator["page"] = $page;
$navigator["pageVar"] = "page";
$navigator["maxPages"] = $maxPages;
$navigator["fullWidth"] = $fullWidth;
if ($app->feature["rewrite"]) {
	$navigator["url"] = $albumName;
} else {
	$navigator["url"] = "view_album.php";
}
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
	echo "BODY { background-image:url(".$album->fields[background]."); } ";
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
	if (typeof(statusWin) != "undefined") {
		statusWin.close();
		statusWin = void(0);
	}
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
</head>

<body onUnload='hideProgress()'> 

<? 
includeHtmlWrap("album.header");

$adminText = "<span class=\"admin\">";
if ($numPhotos == 1) {  
	$adminText .= "1 photo in this album";
} else {
	$adminText .= "$numPhotos photos in this album";
	if ($maxPages > 1) {
		$adminText .= " on " . pluralize($maxPages, "page");
	}
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
	$adminCommands .= '<a href="#" onClick="'.popup("add_photos.php?albumName=$albumName").'">[Add Photos]</a>&nbsp;';
}

if ($user->canWriteToAlbum($album)) {
	if ($album->numPhotos(1)) {
	        $adminCommands .= '<a href="#" onClick="'.popup("shuffle_album.php?albumName=$albumName").'">[Shuffle]</a>&nbsp;';
	        $adminCommands .= '<a href="#" onClick="'.popup("resize_photo.php?albumName=$albumName&index=all").'">[Resize All]</a>&nbsp;';
	        $adminCommands .= '<a href="#" onClick="'.popup("do_command.php?cmd=remake-thumbnail&albumName=$albumName&index=all").'">[Rebuild Thumbs]</a>&nbsp;&nbsp;<br>'; 
	}
        $adminCommands .= '<a href="#" onClick="'.popup("edit_appearance.php?albumName=$albumName").'">[Properties]</a>&nbsp;';
        $adminCommands .= '<a href="#" onClick="'.popup("album_permissions.php?set_albumName=$albumName").'">[Permissions]</a>&nbsp;';
}


if ($user->isLoggedIn()) {
        $adminCommands .= "<a href=do_command.php?cmd=logout&return=view_album.php?page=$page>[Logout]</a>";
} else {
	$adminCommands .= '<a href="#" onClick="'.popup("login.php").'">[Login]</a>';
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

#-- if borders are off, just make them the bgcolor ----
$borderwidth = $album->fields["border"];
if (!strcmp($borderwidth, "off")) {
	$bordercolor = $album->fields["bgcolor"];
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
$numPhotos = $album->numPhotos(1);
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
			echo("<td colspan=3 height=$borderwidth><img src=images/pixel_trans.gif></td>");
			echo("</tr><tr>");
			echo("<td $bordercolor width=$borderwidth>");
			echo("<img src=images/pixel_trans.gif width=$borderwidth height=1>");
			echo("</td><td>");

			$id = $album->getPhotoId($i);
			if ($album->isMovie($id)) {
				echo("<a href=" . $album->getPhotoPath($i) . " target=other>" . 
					$album->getThumbnailTag($i) .
					"</a>");
			} else {
				echo("<a href=" . makeGalleryUrl($albumName, $id) . ">" .
					$album->getThumbnailTag($i) .
					"</a>");
			}
			echo("</td>");
			echo("<td $bordercolor width=$borderwidth>");
			echo("<img src=images/pixel_trans.gif width=$borderwidth height=1>");
			echo("</td>");
			echo("</tr>");	
			echo("<tr $bordercolor>"); 
			echo("<td colspan=3 height=$borderwidth><img src=images/pixel_trans.gif></td>");
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
			echo("<form name='image_form_$i'>"); // put form outside caption to compress lines
			echo "<center><span class=\"caption\">";
			echo($album->getCaption($i)."<br>");
			echo "</span>";

			$id = $album->getPhotoId($i);
			if (($user->canDeleteFromAlbum($album)) || ($user->canWriteToAlbum($album)) ||
				($user->canChangeTextOfAlbum($album))) {
				$label = ($album->isMovie($id)) ? "Movie" : "Photo";
				echo("<select style='FONT-SIZE: 10px;' name='s' ".
					"onChange='imageEditChoice(document.image_form_$i.s)'>");
				echo("<option value=''><< Edit $label>></option>");
			}
			if ($user->canChangeTextOfAlbum($album)) {
				echo("<option value='edit_caption.php?index=$i'>Edit Caption</option>");
			}
			if ($user->canWriteToAlbum($album)) {
				if (!$album->isMovie($id)) {
					echo("<option value='edit_thumb.php?index=$i'>Edit Thumbnail</option>");
					echo("<option value='rotate_photo.php?index=$i'>Rotate $label</option>");
					echo("<option value='highlight_photo.php?index=$i'>Highlight $label</option>");
				}
				echo("<option value='move_photo.php?index=$i'>Move $label</option>");
				if ($album->isHidden($i)) {
					echo("<option value='do_command.php?cmd=show&index=$i'>Show $label</option>");
				} else {
					echo("<option value='do_command.php?cmd=hide&index=$i'>Hide $label</option>");
				}
			}
			if ($user->canDeleteFromAlbum($album)) {
				echo("<option value='delete_photo.php?index=$i'>Delete $label</option>");
			}
			if (($user->canDeleteFromAlbum($album)) || ($user->canWriteToAlbum($album)) ||
							($user->canChangeTextOfAlbum($album))) {
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
