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
// Hack check
if (!$gallery->user->canReadAlbum($gallery->album)) {
        header("Location: " . makeAlbumUrl());
	return;
}

if ($full && !$gallery->user->canViewFullImages($gallery->album)) {
    header("Location: " . makeAlbumUrl($gallery->session->albumName,
				       $id));
    return;
}

if ($id) {
	$index = $gallery->album->getPhotoIndex($id);
	if ($index == -1) {
		// That photo no longer exists.
	        header("Location: " . makeAlbumUrl($gallery->session->albumName));
		return;
	}
} else {
	$id = $gallery->album->getPhotoId($index);
}

// is photo hidden?  should user see it anyway?
if (($gallery->album->isHidden($index))
    && (!$gallery->user->canWriteToAlbum($gallery->album))){
    header("Location: " . makeAlbumUrl($gallery->session->albumName));
    return;
}


$albumName = $gallery->session->albumName;
if (!$gallery->session->viewedItem[$gallery->session->albumName][$id]) {
	$gallery->session->viewedItem[$albumName][$id] = 1;
	$gallery->album->incrementItemClicks($index);
}

$photo = $gallery->album->getPhoto($index);
if ($photo->isMovie()) {
	$image = $photo->thumbnail;
} else {
	$image = $photo->image;
}
$photoURL = $gallery->album->getAlbumDirURL("full") . "/" . $image->name . "." . $image->type;
list($imageWidth, $imageHeight) = $image->getRawDimensions();

$do_fullOnly = !strcmp($gallery->session->fullOnly,"on") &&
               !strcmp($gallery->album->fields["use_fullOnly"],"yes");
if ($do_fullOnly) {
	$full = 1;
}
$fitToWindow = !strcmp($gallery->album->fields["fit_to_window"], "yes") && !$gallery->album->isResized($index) && !$full;

if ($full) {
	$fullTag = "?full=1";
}

$numPhotos = $gallery->album->numPhotos($gallery->user->canWriteToAlbum($gallery->album));
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

if ($index > $gallery->album->numPhotos(1)) {
	$index = $numPhotos;
}

/*
 * We might be prev/next navigating using this page
 *  so recalculate the 'page' variable
 */
$rows = $gallery->album->fields["rows"];
$cols = $gallery->album->fields["cols"];
$perPage = $rows * $cols;
$page = ceil($index / ($rows * $cols));

/*
 * Relative URLs are tricky if we don't know if we're rewriting
 * URLs or not.  If we're rewriting, then the browser will think
 * we're down 1 dir farther than we really are.  Use absolute 
 * urls wherever possible.
 */
$top = $gallery->app->photoAlbumURL;

$bordercolor = $gallery->album
->fields["bordercolor"];
$borderwidth = $gallery->album->fields["border"];
if (!strcmp($borderwidth, "off")) {
	$borderwidth = 1;
}

if (!strcmp($gallery->album->fields["resize_size"], "off")) {
        $mainWidth = 0;
} else {
	$mainWidth = "100%"; 
}

$navigator["id"] = $id;
$navigator["allIds"] = $gallery->album->getIds($gallery->user->canWriteToAlbum($gallery->album));
$navigator["fullWidth"] = "100";
$navigator["widthUnits"] = "%";
$navigator["url"] = ".";
$navigator["bordercolor"] = $bordercolor;

#-- breadcrumb text ---
$breadCount = 0;
$breadtext[$breadCount] = "Album: <a href=\"" . makeAlbumUrl($gallery->session->albumName) .
      "\">" . $gallery->album->fields['title'] . "</a>";
$breadCount++;
$pAlbum = $gallery->album;
do {
  if (!strcmp($pAlbum->fields["returnto"], "no")) {
    break;
  }
  $pAlbumName = $pAlbum->fields['parentAlbumName'];
  if ($pAlbumName) {
    $pAlbum = new Album();
    $pAlbum->load($pAlbumName);
    $breadtext[$breadCount] = "Album: <a href=\"" . makeAlbumUrl($pAlbumName) .
      "\">" . $pAlbum->fields['title'] . "</a>";
  } else {
    //-- we're at the top! ---
    $breadtext[$breadCount] = "Gallery: <a href=\"" . makeGalleryUrl("albums.php") .
      "\">" . $gallery->app->galleryTitle . "</a>";
  }
  $breadCount++;
} while ($pAlbumName);

//-- we built the array backwards, so reverse it now ---
for ($i = count($breadtext) - 1; $i >= 0; $i--) {
    $breadcrumb["text"][] = $breadtext[$i];
}
?>

<?php if (!$GALLERY_EMBEDDED_INSIDE) { ?>
<html> 
<head>
  <title><?php echo $gallery->app->galleryTitle ?> :: <?php echo $gallery->album->fields["title"] ?> :: <?php echo $index ?></title>
  <?php echo getStyleSheetLink() ?>
  <style type="text/css">
<?php
// the link colors have to be done here to override the style sheet
if ($gallery->album->fields["linkcolor"]) {
?>      
    A:link, A:visited, A:active
      { color: <?php echo $gallery->album->fields[linkcolor] ?>; }
    A:hover
      { color: #ff6600; }
<?php 
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
  <script language="javascript1.2">
  // <!--

<?php
if ($fitToWindow) { 
?>

  function fitToWindow(do_resize) {
	var changed = 0;
	var heightMargin = 160;
	var widthMargin = 40;
	var imageHeight = <?php echo $imageHeight?>;
	var imageWidth = <?php echo $imageWidth?>;
	var aspect = imageHeight / imageWidth;

	// Get the window dimensions height.  IE and Nav use different techniques.
	var windowWidth, windowHeight;
	if (typeof(window.innerWidth) == "number") {
		windowWidth = window.innerWidth;
		windowHeight = window.innerHeight;
	} else {
		windowWidth = document.body.clientWidth;
		windowHeight = document.body.clientHeight;
	}

	// Leave a gutter around the edges
	windowWidth = windowWidth - widthMargin;
	windowHeight = windowHeight - heightMargin;

	var diffx = windowWidth - imageWidth,
	    diffy = windowHeight - imageHeight;

	if (diffx < 0 || diffy < 0) {
	    if (diffx < diffy) {
		imageWidth = windowWidth;
		imageHeight = aspect * imageWidth;
		changed = 1;
	    } else {
		imageHeight = windowHeight;
		imageWidth = imageHeight / aspect;
		changed = 1;
	    }
	}

	if (do_resize) {
		var img = document.images.photo;
		img.height = imageHeight;
		img.width = imageWidth;
	} else {
		if (changed) {
			document.write('<a href="<?php echo makeAlbumUrl($gallery->session->albumName, $id, array("full" => 1))?>">');
		}
		document.write('<img name=photo src="<?php echo $photoURL?>" border=0 width=' +
		                 imageWidth + ' height=' + imageHeight + '>');
		if (changed) {
			document.write('</a>');
		}
	}
  }

  function doResize() {
	if (document.all) {
		// We're in IE where we can just resize the image.
		fitToWindow(true);
	} else {
		// In Netscape we've got to reload the page.
		document.reload();
	}
  }

<?php 
} // if ($fitToWindow)
?>

  // -->
  </script>
</head>

<?php if ($fitToWindow) { ?>
<body onResize='doResize()'>
<?php } else { ?>
<body>
<?php } ?>
<?php } # if not embedded ?>

<?php
includeHtmlWrap("photo.header");
?>

<!-- Top Nav Bar -->
<table border=0 width=<?php echo $mainWidth?> cellpadding=0 cellspacing=0>

<tr>
<td>
<?php

if (!$gallery->album->isMovie($id)) {
	if ($gallery->user->canWriteToAlbum($gallery->album)) {
		$adminCommands .= '<a href="#" onClick="'.
			popup("resize_photo.php?index=$index").';return false"><nobr>[resize photo]</nobr></a>';
	}

	if ($gallery->user->canDeleteFromAlbum($gallery->album)) {
		$adminCommands .= '<a href="#" onClick="'.
			popup("delete_photo.php?id=$id").';return false"><nobr>[delete photo]</nobr></a>';
	}

	if (!strcmp($gallery->album->fields["use_fullOnly"], "yes")) {
		$link = doCommand("", 
			array("set_fullOnly" => (strcmp($gallery->session->fullOnly,"on") ? "on" : "off")),
			"view_photo.php", 
			array("id" => $id));
		$adminCommands .= "<nobr>View Images: [ ";
		if (strcmp($gallery->session->fullOnly,"on"))
		{
			$adminCommands .= "normal | <a href=\"$link\">full</a> ]";
		} else {
			$adminCommands .= "<a href=\"$link\">normal</a> | full ]";
		}
		$adminCommands .= "</nobr>";
	}

    
	if (!strcmp($gallery->album->fields["use_exif"],"yes") && (!strcmp($photo->image->type,"jpg")) &&
	    ($gallery->app->use_exif)) {
		$adminCommands .= "<a href=\"#\" onClick=\"".
						popup("view_photo_properties.php?index=$index").
						"\">[photo properties]</a>&nbsp;&nbsp;";
	}


	if (strcmp($gallery->album->fields["print_photos"],"none")) {
		if (strlen($adminCommands) > 0) {
			$adminCommands .="<br>";
		}
		$adminCommands .= "<a href=# onClick=\"document.sflyc4p.returl.value=document.location; document.sflyc4p.submit();return false\">[print this photo on Shutterfly]</a>";
	}


	if ($adminCommands) {
		$adminCommands = "<span class=\"admin\">$adminCommands</span>";
		$adminbox["commands"] = $adminCommands;
		$adminbox["text"] = "&nbsp;";

		$adminbox["bordercolor"] = $bordercolor;
		$adminbox["top"] = true;
		include ($GALLERY_BASEDIR . "layout/adminbox.inc");
	}
}

$breadcrumb["bordercolor"] = $bordercolor;
$breadcrumb["top"] = true;

include($GALLERY_BASEDIR . "layout/breadcrumb.inc");
?>
</td>
</tr>
<tr>
<td>
<?php
include($GALLERY_BASEDIR . "layout/navphoto.inc");

#-- if borders are off, just make them the bgcolor ----
if (!strcmp($gallery->album->fields["border"], "off")) {
	$bordercolor = $gallery->album->fields["bgcolor"];
}
if ($bordercolor) {
	$bordercolor = "bgcolor=$bordercolor";
}
?>
<br>
</td>
</tr>


</table>
<table border=0 width=<?php echo $mainWidth?> cellpadding=0 cellspacing=0>
<tr><td colspan=3>
<?php
includeHtmlWrap("inline_photo.header");
?>
</td></tr>
</table>

<!-- image -->

<table width=1% border=0 cellspacing=0 cellpadding=0>
<?php
echo("<tr $bordercolor>");
echo("<td colspan=3 height=$borderwidth><img src=$top/images/pixel_trans.gif></td>");
echo("</tr><tr>");
echo("<td $bordercolor width=$borderwidth>");
echo("<img src=$top/images/pixel_trans.gif width=$borderwidth height=1>");
echo("</td><td>");
echo "<center>";

$photoTag = $gallery->album->getPhotoTag($index, $full);
if (!$gallery->album->isMovie($id)) {
	if ($gallery->album->isResized($index) && !$do_fullOnly) { 
		if ($full) { 
			echo "<a href=" . makeAlbumUrl($gallery->session->albumName, $id) . ">";
	 	} else if ($gallery->user->canViewFullImages($gallery->album)) {
			echo "<a href=" . makeAlbumUrl($gallery->session->albumName, $id, array("full" => 1)) . ">";
		}
		$openAnchor = 1;
	}
} else {
	echo "<a href=" . $gallery->album->getPhotoPath($index) . " target=other>";
	$openAnchor = 1;
}

if ($fitToWindow && !$GALLERY_EMBEDDED_INSIDE) { ?>
<script language="javascript1.2">
	// <!--
	fitToWindow();
	// -->
</script><noscript><?php
}

echo $photoTag;

if ($fitToWindow) {
	echo "</noscript>";
}

if ($openAnchor) {
	echo "</a>";
 	$openAnchor = 0;
}

echo("</td>");
echo("<td $bordercolor width=$borderwidth>");
echo("<img src=$top/images/pixel_trans.gif width=$borderwidth height=1>");
echo("</td>");
echo("</tr>");
echo("<tr $bordercolor>");
echo("<td colspan=3 height=$borderwidth><img src=$top/images/pixel_trans.gif></td>");
?>
</tr>
</table>

<table border=0 width=<?php echo $mainWidth?> cellpadding=0 cellspacing=0>
<!-- caption -->
<tr>
<td colspan=3 align=center>
<span class="caption"><?php echo editCaption($gallery->album, $index, $edit) ?></span>
<br><br>
</td>
</tr>
<?php if (!strcmp($gallery->album->fields["public_comments"], "yes")) { ?>
<tr>
<td colspan=3 align=center>
<!-- comments -->
<span class="caption"><?php echo viewComments($index) ?></span>
<br><br>
</td>
</tr>
<?php } ?>
<?php
if (!strcmp($gallery->album->fields["print_photos"],"none") ||
    $gallery->album->isMovie($id)) {
} else {
$photo = $gallery->album->getPhoto($GLOBALS["index"]);
$photoPath = $gallery->album->getAlbumDirURL("full");
$rawImage = $photoPath . "/" . $photo->image->name . "." . $photo->image->type;

$thumbImage= $photoPath . "/";
if ($photo->image->resizedName) {
	$thumbImage .= $photo->image->resizedName . "." . $photo->image->type;
} else {
	$thumbImage .= $photo->image->name . "." . $photo->image->type;
}
list($imageWidth, $imageHeight) = $photo->image->getRawDimensions();
?>
<form name="sflyc4p" action="http://www.shutterfly.com/c4p/UpdateCart.jsp" method="post">
  <input type=hidden name=addim value="1">
  <input type=hidden name=protocol value="SFP,100">
<?php if ($gallery->album->fields["print_photos"] == "shutterfly without donation") { ?>
  <input type=hidden name=pid value="C4P">
  <input type=hidden name=psid value="AFFL">
<?php } else { ?>
  <input type=hidden name=pid value="C4PP">
  <input type=hidden name=psid value="GALL">
<?php } ?>
  <input type=hidden name=referid value="gallery">
  <input type=hidden name=returl value="this-gets-set-by-javascript-in-onClick">
  <input type=hidden name=imraw-1 value="<?php echo $rawImage ?>">
  <input type=hidden name=imrawheight-1 value="<?php echo $imageHeight ?>">
  <input type=hidden name=imrawwidth-1 value="<?php echo $imageWidth ?>">
  <input type=hidden name=imthumb-1 value="<?php echo $thumbImage ?>">
  <input type=hidden name=imbkprntb-1 value="Hi">
</form>
<?php
}
?>

<?php

echo("<tr><td colspan=3 align=center>");
includeHtmlWrap("inline_photo.footer");
echo("</td></tr>");
?>

</table>
<table border=0 width=<?php echo $mainWidth?> cellpadding=0 cellspacing=0>
<tr>
<td>
<?php
include($GALLERY_BASEDIR . "layout/navphoto.inc");
$breadcrumb["top"] = false;
include($GALLERY_BASEDIR . "layout/breadcrumb.inc");
?>
</td>
</tr>
</table>
</center>
<?php
includeHtmlWrap("photo.footer");
?>

<?php if (!$GALLERY_EMBEDDED_INSIDE) { ?>
</body>
</html>
<?php } ?>

