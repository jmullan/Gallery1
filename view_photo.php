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

if ($id) {
	$index = $album->getPhotoIndex($id);
	if ($index == -1) {
		// That photo no longer exists.
		header("Location: $app->photoAlbumURL/$albumName");
	}
} else {
	$id = $album->getPhotoId($index);
}

if ($full) {
	$fullTag = "?full=1";
}

$numPhotos = $album->numPhotos($user->canWriteToAlbum($album));
$next = $index+1;
if ($next > $numPhotos) {
	//$next = 1;
        $last = 1;
}
$prev = $index-1;
if ($prev <= 0) {
	//$prev = $numPhotos;
        $first = 1;
}

if ($index > $numPhotos) {
	$index = $numPhotos;
}

/*
 * We might be prev/next navigating using this page
 *  so recalculate the 'page' variable
 */
$rows = $album->fields["rows"];
$cols = $album->fields["cols"];
$perPage = $rows * $cols;
$page = ceil($index / ($rows * $cols));

/*
 * Relative URLs are tricky if we don't know if we're rewriting
 * URLs or not.  If we're rewriting, then the browser will think
 * we're down 1 dir farther than we really are.  Use absolute 
 * urls wherever possible.
 */
$top = $app->photoAlbumURL;

#-- if borders are off, just make them the bgcolor ----
if (!strcmp($album->fields["border"], "off")) {
        $bordercolor = $album->fields["bgcolor"];
        $borderwidth = 4;
} else {
        $bordercolor = $album->fields["bordercolor"];
        $borderwidth = $album->fields["border"];
}

if (!strcmp($album->fields["resize_size"], "off")) {
        $mainWidth = 0;
} else {
	$mainWidth = $album->fields["resize_size"] + ($borderwidth*2);


}

$navigator["page"] = $index;
$navigator["pageVar"] = "index";
$navigator["maxPages"] = $numPhotos;
$navigator["fullWidth"] = "100";
$navigator["widthUnits"] = "%";
$navigator["url"] = ".";
$navigator["spread"] = 5;
$navigator["bordercolor"] = $bordercolor;
$navigator["noIndivPages"] = true; 

#-- breadcrumb text ---
if (strcmp($album->fields["returnto"], "no")) {
	$breadtext[0] = "Gallery: <a href=$top/albums.php>".$app->galleryTitle."</a>";
	$breadtext[1] = "Album: <a href=$top/view_album.php>".$album->fields["title"]."</a>";
} else {
	$breadtext[0] = "Album: <a href=$top/view_album.php>".$album->fields["title"]."</a>";
}
?>

<head>
  <title><?= $app->galleryTitle ?> :: <?= $album->fields["title"] ?> :: <?= $index ?></title>
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
</head>

<body>

<?
includeHtmlWrap("photo.header");
?>

<!-- Top Nav Bar -->
<table border=0 width=<?=$mainWidth?> cellpadding=0 cellspacing=0>
<tr>
<td>
<?
if (!$album->isMovie($index)) {
	if ($user->canWriteToAlbum($album)) {
		$adminCommands .= "<a href=".popup("$top/resize_photo.php?index=$index").">[resize photo]</a>";
	}

	if ($user->canDeleteFromAlbum($album)) {
		$adminCommands .= "<a href=".popup("$top/delete_photo.php?index=$index").">[delete photo]</a>";
	}

	$adminbox["text"] = "&nbsp;";
	if ($adminCommands) {
		$adminCommands = "<span class=\"admin\">$adminCommands</span>";
		$adminbox["commands"] = $adminCommands;
	}

	$adminbox["bordercolor"] = $bordercolor;
	$adminbox["top"] = true;
	include ("layout/adminbox.inc");
}

$breadcrumb["text"] = $breadtext;
$breadcrumb["bordercolor"] = $bordercolor;
$breadcrumb["top"] = true;

include("layout/breadcrumb.inc");
?>
</td>
</tr>
<tr>
<td>
<?

include("layout/navphoto.inc");
?>
<br>
</td>
</tr>


</table>
<table border=0 width=<?=$mainWidth?> cellpadding=0 cellspacing=0>
<!-- image row -->
<tr>
<td colspan=3 align=center>
<?


                        echo("<table width=1% border=0 cellspacing=0 cellpadding=0>");
                        echo("<tr bgcolor=$bordercolor>");
                        echo("<td height=$borderwidth width=$borderwidth><img src=$top/images/pixel_trans.gif></td>");
                        echo("<td height=$borderwidth><img src=$top/images/pixel_trans.gif></td>");
                        echo("<td height=$borderwidth width=$borderwidth><img src=$top/images/pixel_trans.gif></td>");
                        echo("</tr>");
                        echo("<tr>");
			echo("<td bgcolor=$bordercolor width=$borderwidth>");
			for ($k=0; $k<$borderwidth; $k++) {
				echo("<img src=$top/images/pixel_trans.gif>");
			}
                        echo("</td>");
                        echo("<td>");

if (!$album->isMovie($index)) {
	if ($album->isResized($index)) { 
		if ($full) { 
			echo "<a href=" . makeGalleryUrl($albumName, $id) . ">";
	 	} else {
			echo "<a href=" . makeGalleryUrl($albumName, $id, "full=1") . ">";
		}
		$openAnchor = 1;
	}
} else {
	echo "<a href=" . $album->getPhotoPath($index) . " target=other>";
	$openAnchor = 1;
}
?>
<?=$album->getPhotoTag($index, $full)?>
<?
if ($openAnchor) {
	echo "</a>";
 	$openAnchor = 0;
}

			echo("</td>");
			echo("<td bgcolor=$bordercolor width=$borderwidth>");
			for ($k=0; $k<$borderwidth; $k++) {
				echo("<img src=$top/images/pixel_trans.gif>");
			}
                        echo("</td>");
			echo("</tr>");
                        echo("<tr bgcolor=$bordercolor>");
                        echo("<td height=$borderwidth width=$borderwidth><img src=$top/images/pixel_trans.gif></td>");
                        echo("<td height=$borderwidth><img src=$top/images/pixel_trans.gif></td>");
                        echo("<td height=$borderwidth width=$borderwidth><img src=$top/images/pixel_trans.gif></td>");
                        echo("</tr>");
                        echo("</table>");

?>

</td>
</tr>
<tr>
<td colspan=3 align=center>
<span class="caption">
<?= editCaption($album, $index, $edit) ?>
</span>
<br>
<br>
</td>
</tr>
</table>
<table border=0 width=<?=$mainWidth?> cellpadding=0 cellspacing=0>
<tr>
<td>
<?
include("layout/navphoto.inc");
$breadcrumb["top"] = false;
include("layout/breadcrumb.inc");
?>
</td>
</tr>
</table>
</center>
<?
includeHtmlWrap("photo.footer");
?>

</body>
</html>
