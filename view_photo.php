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
// Hack check
if (!$gallery->user->canReadAlbum($gallery->album)) {
        header("Location: " . makeAlbumUrl());
	return;
}
if (isset($full) && !$gallery->user->canViewFullImages($gallery->album)) {
	header("Location: " . makeAlbumUrl($gallery->session->albumName,
				$id));
	return;
}
if (!isset($full)) {
	$full=NULL;
}

if (!isset($openAnchor)) {
	$openAnchor=0;
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

if (!function_exists('array_search')) {
        function array_search($needle, $haystack) {
                for ($x=0; $x < sizeof($haystack); $x++) {
                        if ($haystack[$x] == $needle) {
                                return $x;
                        }
                }
                return NULL;
        }
}


// is photo hidden?  should user see it anyway?
if (($gallery->album->isHidden($index))
    && (!$gallery->user->canWriteToAlbum($gallery->album))){
    header("Location: " . makeAlbumUrl($gallery->session->albumName));
    return;
}


$albumName = $gallery->session->albumName;
if (!isset($gallery->session->viewedItem[$gallery->session->albumName][$id]) 
	&& !$gallery->session->offline) {
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

$do_fullOnly = isset($gallery->session->fullOnly) &&
		!strcmp($gallery->session->fullOnly,"on") &&
               !strcmp($gallery->album->fields["use_fullOnly"],"yes");
if ($do_fullOnly) {
	$full = $gallery->user->canViewFullImages($gallery->album);
}
    
$fitToWindow = !strcmp($gallery->album->fields["fit_to_window"], "yes") && !$gallery->album->isResized($index) && !$full;

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
$breadtext[$breadCount] = _("Album") .": <a href=\"" . makeAlbumUrl($gallery->session->albumName) .
      "\">" . $gallery->album->fields['title'] . "</a>";
$breadCount++;
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
  } elseif (!$gallery->session->offline || isset($gallery->session->offlineAlbums["albums.php"])) {
    //-- we're at the top! ---
    $breadtext[$breadCount] = _("Gallery") .": <a href=\"" . makeGalleryUrl("albums.php") .
      "\">" . $gallery->app->galleryTitle . "</a>";
  }
  $breadCount++;
} while ($pAlbumName);

//-- we built the array backwards, so reverse it now ---
for ($i = count($breadtext) - 1; $i >= 0; $i--) {
    $breadcrumb["text"][] = $breadtext[$i];
}
$extra_fields=$gallery->album->getExtraFields();
$title=NULL;
if (in_array("Title", $extra_fields))
{
	$title=$gallery->album->getExtraField($index, "Title");
}
if (!$title) {
	$title=$index;
}

?>
<?php if (!$GALLERY_EMBEDDED_INSIDE) { ?>
<html> 
<head>
  <title><?php echo $gallery->app->galleryTitle ?> :: <?php echo $gallery->album->fields["title"] ?> :: <?php echo $title ?></title>
  <?php echo getStyleSheetLink() ?>
  <?php /* prefetch/navigation */
  $navcount = sizeof($navigator['allIds']);
  $navpage = $navcount - 1; 
  while ($navpage > 0) {
      if (!strcmp($navigator['allIds'][$navpage], $id)) {
	  break;
      }
      $navpage--;
  }
  if ($navigator['allIds'][0] != $id) {
      if ($navigator['allIds'][0] != 'unknown') { ?>
          <link rel="first" href="<?php echo makeAlbumUrl($gallery->session->albumName, $navigator['allIds'][0]) ?>" />
      <?php }
      if ($navigator['allIds'][$navpage-1] != 'unknown') { ?>
          <link rel="prev" href="<?php echo makeAlbumUrl($gallery->session->albumName, $navigator['allIds'][$navpage-1]) ?>" />
      <?php }
  }
  if ($navigator['allIds'][$navcount - 1] != $id) {
      if ($navigator['allIds'][$navpage+1] != 'unknown') { ?>
          <link rel="next" href="<?php echo makeAlbumUrl($gallery->session->albumName, $navigator['allIds'][$navpage+1]) ?>" />
      <?php }
      if ($navigator['allIds'][$navcount-1] != 'unknown') { ?>
          <link rel="last" href="<?php echo makeAlbumUrl($gallery->session->albumName, $navigator['allIds'][$navcount - 1]) ?>" />
      <?php }
  } ?>
  <link rel="up" href="<?php echo makeAlbumUrl($gallery->session->albumName) ?>">
	  <?php if ($gallery->album->isRoot() &&
			  (!$gallery->session->offline ||
			   isset($gallery->session->offlineAlbums["albums.php"]))) { ?>
  <link rel="top" href="<?php echo makeGalleryUrl('albums.php', array('set_albumListPage' => 1)) ?>">	 
	  <?php }?>
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
if (isset($gallery->album->fields["background"])) {
        echo "BODY { background-image:url(".$gallery->album->fields['background']."); } ";
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
	var imageHeight = <?php echo $imageHeight ?>;
	var imageWidth = <?php echo $imageWidth ?>;
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
			document.write('<a href="'+ document.getElementById("page_url").href+ '">');
		}
		src= document.getElementById("photo_url").href;
		document.write('<img name=photo src="'+src +
			'" border=0 + width=' + imageWidth +
				' height=' + imageHeight + '>');
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
	<body dir=<?php echo $gallery->direction ?> onResize='doResize()'>
<?php } else { ?>
	<body dir=<?php echo $gallery->direction ?>>
<?php } ?>
<?php } # if not embedded ?>
<?php
includeHtmlWrap("photo.header");
?>

<!-- Top Nav Bar -->
<table border=0 width=<?php echo $mainWidth ?> cellpadding=0 cellspacing=0>

<tr>
<td>
<?php

$adminCommands = '';
if (!$gallery->album->isMovie($id)) {
	print "<a id=\"photo_url\" href=\"$photoURL\" ></a>\n";
	print '<a id="page_url" href="'. 
		makeAlbumUrl($gallery->session->albumName, $id, 
			array("full" => 1)).'"></a>'."\n";
	if ($gallery->user->canWriteToAlbum($gallery->album)) {
		$adminCommands .= popup_link("[" . _("resize photo") ."]", 
			"resize_photo.php?index=$index");
	}

	if ($gallery->user->canDeleteFromAlbum($gallery->album) || 
	    ($gallery->album->getItemOwnerDelete() && $gallery->album->isItemOwner($gallery->user->getUid(), $index))) {
		$nextId = ($index >= $numPhotos ? $index - 1 : $index);
		$adminCommands .= popup_link("[" . _("delete photo") ."]", 
			"delete_photo.php?id=$id&id2=$nextId");
	}

	if (!strcmp($gallery->album->fields["use_fullOnly"], "yes") &&
			$gallery->user->canViewFullImages($gallery->album)) {
		if (!$gallery->session->offline) {
			$link = doCommand("", 
				array("set_fullOnly" => 
					(strcmp($gallery->session->fullOnly,"on") 
					? "on" : "off")),
				"view_photo.php", 
				array("id" => $id));
              	}
              	else {
                      $link = makeAlbumUrl($gallery->session->albumName, $id,
                              array("set_fullOnly" => 
                                      (strcmp($gallery->session->fullOnly,
                                              "on") ?
                                      "on" : "off"))); 
              	}

		$adminCommands .= "<nobr>". _("View Images") .": [ ";
		if (strcmp($gallery->session->fullOnly,"on"))
		{
			$adminCommands .= _("normal") ." | <a href=\"$link\">" . _("full") ."</a> ]";
		} else {
			$adminCommands .= "<a href=\"$link\">" . _("normal") .'</a> | '. _("full") ." ]";
		}
		$adminCommands .= "</nobr>";
	} 
	
	$field="EXIF";
	$key=array_search($field, $extra_fields);
	if (!is_int($key) &&
	    !strcmp($gallery->album->fields["use_exif"],"yes") &&
	    (eregi("jpe?g\$", $photo->image->type)) &&
	    ($gallery->app->use_exif)) {
		$albumName = $gallery->session->albumName;
		$adminCommands .= popup_link("[" . _("photo properties") ."]", "view_photo_properties.php?set_albumName=$albumName&index=$index", 0, false);
	}

	if (strcmp($gallery->album->fields["print_photos"],"none") &&
		!$gallery->session->offline &&
		!$gallery->album->isMovie($id)){

		$photo = $gallery->album->getPhoto($GLOBALS["index"]);
		$photoPath = $gallery->album->getAlbumDirURL("full");
		$rawImage = $photoPath . "/" . $photo->image->name . "." . $photo->image->type;

		$thumbImage= $photoPath . "/";
		if ($photo->thumbnail) {
			$thumbImage .= $photo->image->name . "." . "thumb" . "." . $photo->image->type;
		} else if ($photo->image->resizedName) {
			$thumbImage .= $photo->image->name . "." . "sized" . "." . $photo->image->type;
		} else {
			$thumbImage .= $photo->image->name . "." . $photo->image->type;
		}
		list($imageWidth, $imageHeight) = $photo->image->getRawDimensions();
		if (strlen($adminCommands) > 0) {
			$adminCommands .="<br>";
		}
		$printService = $gallery->album->fields["print_photos"];
		if (!strncmp($printService, "shutterfly", 10)) {
		    $adminCommands .= "<a href=\"#\" onClick=\"document.sflyc4p.returl.value=document.location; document.sflyc4p.submit();\">[". sprintf(_("print this photo on %s"), "Shutterfly") . "]</a>";
		    $printShutterflyForm = 1;
		} else if (!strncmp($printService, "fotokasten", 10)) {
		    $adminCommands .= popup_link("[". sprintf(_("print this photo on %s"), "Fotokasten") . "]", "'http://1071.partner.fotokasten.de/affiliateapi/standard.php?add=" . $rawImage . '&thumbnail=' . $thumbImage . '&height=' . $imageHeight . '&width=' . $imageWidth . "'", 1);
		} else if (!strncmp($printService, 'photoaccess', 11)) {
		    $adminCommands .= "<a href=\"#\" onClick=\"document.photoAccess.returnUrl.value=document.location; document.photoAccess.submit()\">[". sprintf(_("print this photo on %s"), "PhotoAccess") . "]</a>";
		    $printPhotoAccessForm = 1;
		}
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
$breadcrumb['bottom'] = false;
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
<table border=0 width=<?php echo $mainWidth ?> cellpadding=0 cellspacing=0>
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
echo("<td colspan=3 height=$borderwidth><img src=\"$top/images/pixel_trans.gif\"></td>");
echo("</tr><tr>");
echo("<td $bordercolor width=$borderwidth>");
echo("<img src=\"$top/images/pixel_trans.gif\" width=$borderwidth height=1>");
echo("</td><td align='center'>");

$photoTag = $gallery->album->getPhotoTag($index, $full);
if (!$gallery->album->isMovie($id)) {
	if ($gallery->album->isResized($index) && !$do_fullOnly) { 
		if ($full) { 
			echo "<a href=\"" . makeAlbumUrl($gallery->session->albumName, $id) . "\">";
	 	} else if ($gallery->user->canViewFullImages($gallery->album)) {
			echo "<a href=\"" . makeAlbumUrl($gallery->session->albumName, $id, array("full" => 1)) . "\">";
		}
		$openAnchor = 1;
	}
} else {
	echo "<a href=\"" . $gallery->album->getPhotoPath($index) . "\" target=\"other\">";
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
echo("<img src=\"$top/images/pixel_trans.gif\" width=$borderwidth height=1>");
echo("</td>");
echo("</tr>");
echo("<tr $bordercolor>");
echo("<td colspan=3 height=$borderwidth><img src=\"$top/images/pixel_trans.gif\"></td>");
?>
</tr>
</table>

<table border=0 width=<?php echo $mainWidth ?> cellpadding=0 cellspacing=0>
<!-- caption -->
<tr>
<td colspan=3 align=center>
<span class="caption"><?php echo editCaption($gallery->album, $index) ?></span>
<br><br>
<table>
<?php

$automaticFields=automaticFieldsList();
$field="Upload Date";
$key=array_search($field, $extra_fields);
if (is_int($key))
{
	print "<tr><td valign=top align=right><b>".$automaticFields[$field].":<b></td><td>".
		strftime("%c" , $gallery->album->getUploadDate($index)).
		"</td></tr>";
	unSet($extra_fields[$key]);
}

$field="Capture Date";
$key=array_search($field, $extra_fields);
if (is_int($key))
{
	$itemCaptureDate = $gallery->album->getItemCaptureDate($index);
	print "<tr><td valign=top align=right><b>".$automaticFields[$field].":<b></td><td>".
		strftime("%c" , mktime ($itemCaptureDate['hours'],
					$itemCaptureDate['minutes'],
					$itemCaptureDate['seconds'],
					$itemCaptureDate['mon'],
					$itemCaptureDate['mday'],
					$itemCaptureDate['year'])).  
		"</td></tr>";
	unSet($extra_fields[$key]);
}

$field="Dimensions";
$key=array_search($field, $extra_fields);
if (is_int($key))
{

	$dimensions=$photo->getDimensions($full);
	print "<tr><td valign=top align=right><b>".$automaticFields[$field].":<b></td><td>".
	$dimensions[0]." x ".$dimensions[1]." (".round($photo->getFileSize($full)/1000)."k)</td></tr>";
	unSet($extra_fields[$key]);
}

// skip title - only for header display
$field="Title";
$key=array_search($field, $extra_fields);
if (is_int($key))
{
	unSet($extra_fields[$key]);
}
$field="EXIF";
$do_exif=false;
$key=array_search($field, $extra_fields);
if (is_int($key))
{
	unSet($extra_fields[$key]);
	if ( ($gallery->album->fields["use_exif"] === "yes") 
		&& $gallery->app->use_exif &&
		(eregi("jpe?g\$", $photo->image->type))) {
		$do_exif=true;
	}

}

foreach ($extra_fields as $field)
{
	$value=$gallery->album->getExtraField($index, $field);
	if ($value)
	{
		print "<tr><td valign=top align=right><b>$field:<b></td><td>".
			str_replace("\n", "<p>", $value).
			"</td></tr>";
	}
}
if ($do_exif) {
	$myExif = $gallery->album->getExif($index, isset($forceRefresh));
	foreach ($myExif as $field => $value) {
		print "<tr><td valign=top align=right><b>$field:<b></td><td>".
			str_replace("\n", "<p>", $value).
			"</td></tr>";
	}
}
?>
</table>
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
<?php if (isset($printShutterflyForm)) { ?>
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
  <?php
     /* Print the caption on back of photo. If no caption,
      * then print the URL to this page. Shutterfly cuts
      * the message off at 80 characters. */
     $imbkprnt = $gallery->album->getCaption($index);
     if (empty($imbkprnt)) {
        $imbkprnt = makeAlbumUrl($gallery->session->albumName, $id);
     }
  ?>
  <input type=hidden name=imbkprnta-1 value="<?php echo strip_tags($imbkprnt) ?>">
</form>
<?php } ?>
<?php if (isset($printPhotoAccessForm)) { ?>
  <form method="post" name="photoAccess" action="http://www.photoaccess.com/buy/anonCart.jsp">
  <input type="hidden" name="cb" value="CB_GP">
  <input type="hidden" name="redir" value="true">
  <input type="hidden" name="returnUrl" value="this-gets-set-by-javascript-in-onClick">
  <input type="hidden" name="imageId" value="<?php echo $photo->image->name . '.' . $photo->image->type; ?>">
  <input type="hidden" name="imageUrl" value="<?php echo $rawImage ?>">
  <input type="hidden" name="thumbUrl" value="<?php echo $thumbImage ?>">
  <input type="hidden" name="imgWidth" value="<?php echo $imageWidth ?>">
  <input type="hidden" name="imgHeight" value="<?php echo $imageHeight ?>">
</form>
<?php } ?> 
<?php

echo("<tr><td colspan=3 align=center>");
includeHtmlWrap("inline_photo.footer");
echo("</td></tr>");
?>

</table>
<table border=0 width=<?php echo $mainWidth ?> cellpadding=0 cellspacing=0>
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

<?php
include($GALLERY_BASEDIR . "layout/ml_pulldown.inc");
includeHtmlWrap("photo.footer");
?>

<?php if (!$GALLERY_EMBEDDED_INSIDE) { ?>
</body>
</html>
<?php } ?>

