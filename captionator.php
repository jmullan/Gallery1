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


if (!$page) {
	$page = 1;
}

$albumDB = new albumDB();
$numPhotos = $gallery->album->numPhotos($gallery->user->canWriteToAlbum($gallery->album));

if (!$perPage) {
	$perPage = 5;
}

#-- save the captions from the previous page ---
if ($save || $next || $prev) {

	$i = 0;
	$start = ($page - 1) * $perPage + 1;
	while ($i < $start) {
		$i++;
	}
   
	$count = 0;
	while ($count < $perPage && $i <= $numPhotos) {
		$gallery->album->setCaption($i, stripslashes($new_captions[$count]));
		$gallery->album->setKeywords($i, stripslashes($new_keywords[$count]));
		$i++;
		$count++;
	}

	$gallery->album->save();

}

if ($cancel || $save) {
	header("Location: " . makeGalleryUrl("view_album.php"));
	return;
}

#-- did they hit next? ---
if ($next) {
	$page++;
} else if ($prev) {
	$page--;
}

$start = ($page - 1) * $perPage + 1;
$maxPages = max(ceil($numPhotos / $perPage), 1);

if ($page > $maxPages) {
	$page = $maxPages;
}
$end = $start + $perPage;

$nextPage = $page + 1;
if ($nextPage > $maxPages) {
	$nextPage = 1;
	$last = 1;
}

$thumbSize = $gallery->app->default["thumb_size"];
$imageDir = $gallery->app->photoAlbumURL."/images";
$pixelImage = "<img src=\"$imageDir/pixel_trans.gif\" width=\"1\" height=\"1\">";

$bordercolor = $gallery->album->fields["bordercolor"];
?>

<? if (!$GALLERY_EMBEDDED_INSIDE) { ?>
<head>
  <title><?= $gallery->app->galleryTitle ?> :: <?= $gallery->album->fields["title"] ?> :: Captionator</title>
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
  // --> 
  </script>

<? 
includeHtmlWrap("album.header");

$adminText = "<span class=\"admin\">Multiple Caption Editor. ";
if ($numPhotos == 1) {  
	$adminText .= "1 photo in this album";
} else {
	$adminText .= "$numPhotos items in this album";
	if ($maxPages > 1) {
		$adminText .= " on " . pluralize($maxPages, "page");
	}
}

$adminText .="</span>";
$adminCommands = "";
$adminCommands .= "</span>";
$adminbox["text"] = $adminText;
$adminbox["commands"] = $adminCommands;
$adminbox["bordercolor"] = $bordercolor;
$adminbox["top"] = true;
include ($GALLERY_BASEDIR . "layout/adminbox.inc");

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
<?= makeFormIntro("captionator.php", array("method" => "POST")) ?>
<center>
<input type=submit name="save" value="Save and Exit">

<? if (!$last) { ?>
	<input type=submit name="next" value="Save and Edit Next <?= $perPage ?>">
<? } ?>

<? if ($page != 1) { ?>
	<input type=submit name="prev" value="Save and Edit Previous <?= $perPage ?>">
<? } ?>

<input type=submit name="cancel" value="Cancel">
</center>
<input type=hidden name=page value=<?= $page ?>>
<table width=100% border=0>
<?
if ($numPhotos) {


	// Find the correct starting point, accounting for hidden photos
	$i = 0;
	while ($i < $start) {
		$i++;
	}

	$count = 0;
	while ($count < $perPage && $i <= $numPhotos) {
?>
	<tr>
	  <td width=<?= $thumbSize ?> align=center>
	  <?= $gallery->album->getThumbnailTag($i, $thumbSize); ?>
	  </td width=10>
	  <td>
	  <?= $pixelImage ?>
	  </td>

	  <td valign=top>
	  <hr size=1>
	  <span class="fineprint">Caption:</span><br>
      <textarea name="new_captions[]" rows=3 cols=60><?= $gallery->album->getCaption($i) ?></textarea><br>
	  <span class="fineprint">Keywords:</span><br>
	  <input type=text name="new_keywords[]" size=65 value="<?= $gallery->album->getKeywords($i) ?>">
	  </td>
	</tr>
<?
		$i++;
		$count++;
	}
} else {
	echo("<tr>");
	echo("  <td>");
	echo("  NO PHOTOS!");
    echo("  </td>");
	echo("</tr>");
}
?>


</table>
<center>
<input type=submit name="save" value="Save and Exit">

<? if (!$last) { ?>
	<input type=submit name="next" value="Save and Edit Next <?= $perPage ?>">
<? } ?>

<? if ($page != 1) { ?>
	<input type=submit name="prev" value="Save and Edit Previous <?= $perPage ?>">
<? } ?>

<input type=submit name="cancel" value="Cancel">
</center>
</form>

<br>

<?
includeHtmlWrap("album.footer");
?>

<? if (!$GALLERY_EMBEDDED_INSIDE) { ?>
</body>
</html>
<? } ?>
