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
 
if ($gallery->session->albumName == "") {
        header("Location: albums.php");
        return;
}

if (!$gallery->user->canReadAlbum($gallery->album)) {
	header("Location: " . makeAlbumUrl());
	return;
}

if (!$gallery->album->isLoaded()) {
	header("Location: " . makeAlbumUrl());
	return;
}

// default settings ---
$defaultLoop = 0;
$defaultPause = 3;
$defaultFull = 0;
$defaultDir = 1;

if (!$slide_index) {
    $slide_index = 1;
}
if (!$slide_pause) {
    $slide_pause = $defaultPause;
}
if (!$slide_loop) {
    $slide_loop = $defaultLoop;
}
if (!$slide_full) {
    $slide_full = $defaultFull;
}
if (!$slide_dir) {
    $slide_dir = $defaultDir;
}

if ($slide_full && !$gallery->user->canViewFullImages($gallery->album)) {
    $slide_full = 0;
}

function makeSlideLowUrl($index, $loop, $pause, $full, $dir) {

    return makeGalleryUrl('slideshow_low.php',
	array('set_albumName' => $gallery->session->albumName,
	      'slide_index' => $index,
	      'slide_loop' => $loop,
	      'slide_pause' => $pause,
	      'slide_full' => $full,
	      'slide_dir' => $dir));
}

$borderColor = $gallery->album->fields["bordercolor"];
$borderwidth = $gallery->album->fields["border"];
if (!strcmp($borderwidth, "off")) {
        $borderwidth = 1;
}
$bgcolor = $gallery->album->fields['bgcolor'];
$title = $gallery->album->fields["title"];

define(PHOTO_URL,         1 << 0);
define(PHOTO_CAPTION,     1 << 1);
define(PHOTO_URL_AS_HREF, 1 << 2);
define(PHOTO_ALL    ,     (1<<16)-1);      // all bits set

function printSlideshowPhotos($slide_full, $what = PHOTO_ALL) {
    global $gallery;
    
    $numPhotos = $gallery->album->numPhotos(1);
    $numDisplayed = 0; 

    // Find the correct starting point, accounting for hidden photos
    $index = getNextPhoto(0);
    $photo_count = 0;
    while ($numDisplayed < $numPhotos) {
	if ($index > $numPhotos) {
	    /*
	     * We went past the end -- this can happen if the last element is
	     * an album that we can't read.
	     */
	    break;
	}
    
	$photo = $gallery->album->getPhoto($index);
	$numDisplayed++;

	// Skip movies and nested albums
	if ($photo->isMovie() || $photo->isAlbumName) {
	    $index = getNextPhoto($index);
	    continue;
	}
	
	$photo_count++;

	if ( ($what & PHOTO_URL) != 0 ) {
	    $photoURL = $gallery->album->getPhotoPath($index, $slide_full);
	    print "photo_urls[$photo_count] = \"$photoURL\";\n";
	}

	if ( ($what & PHOTO_URL_AS_HREF) != 0 ) {
	    $photoURL = $gallery->album->getPhotoPath($index, $slide_full);
	    print "<a id=\"photo_urls_$photo_count\" href=\"$photoURL\"></a>\n";
	}

	if ( ($what & PHOTO_CAPTION) != 0 ) {
	    // Now lets get the captions
	    $caption = $gallery->album->getCaption($index);
	    $caption .= $gallery->album->getCaptionName($index);
	    $caption = str_replace("\"", " ", $caption);
	    $caption = str_replace("\n", " ", $caption);
	    $caption = str_replace("\r", " ", $caption);
	    
	    // Print out the entry for this image as Javascript
	    print "photo_captions[$photo_count] = \"$caption\";\n";
	}

	// Go to the next photo
	$index = getNextPhoto($index);
	$photosLeft--;
    }

    return $photo_count;
}

?>
<?php if (!$GALLERY_EMBEDDED_INSIDE) { ?>
<html> 
<head>
  <title><?php echo _("Slide Show for album") ?> :: <?php echo $gallery->album->fields["title"] ?></title>
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
</head>

<body dir=<?php echo $gallery->direction ?>>
<?php } ?>
<?php includeHtmlWrap("slideshow.header"); ?>

<!-- Here are the URL of the images written down as links. This is to make
     wget able to convert these links. It will not convert them, if they
     are written inside JavaScript. JavaScript will then take the images
     out of these links with "document.getElementById()". -->

<?php 
    echo "<a id=\"slideShowUrl\" href=\"".makeGalleryUrl('slideshow_low.php',
							 array('set_albumName' => $gallery->session->albumName))."\"></a>\n\n";

     printSlideshowPhotos($slide_full, PHOTO_URL_AS_HREF); ?>

<script language="JavaScript">
var timer; 
var current_location = <?php echo $slide_index ?>;
var next_location = <?php echo $slide_index ?>; 
var pics_loaded = 0;
var onoff = 0;
var timeout_value;
var images = new Array;
var photo_urls = new Array;
var photo_captions = new Array;
var loop = <?php echo $slide_loop ?>;
var full = <?php echo $slide_full ?>;
var direction = <?php echo $slide_dir ?>;
<?php $photo_count = printSlideshowPhotos($slide_full, PHOTO_CAPTION); ?>
var photo_count = <?php echo $photo_count ?>; 

function stop() {
    onoff = 0;
    status = "<?php echo _("The slide show is stopped, Click [play] to resume.") ?>";
    clearTimeout(timer);
}

function play() {
    onoff = 1;
    status = "<?php echo _("Slide show is running...") ?>";
    go_to_next_photo();
}

function toggleLoop() {
    if (loop) {
        loop = 0;
    } else {
        loop = 1;
    }
}

function preload_complete() {
}

function reset_timer() {
    clearTimeout(timer);
    if (onoff) {
	timeout_value = document.TopForm.time.options[document.TopForm.time.selectedIndex].value * 1000;
	timer = setTimeout('go_to_next_page()', timeout_value);
    }
}

function wait_for_current_photo() {

    /* Show the current photo */
    if (!show_current_photo()) {

	/*
	 * The current photo isn't loaded yet.  Set a short timer just to wait
	 * until the current photo is loaded.
	 */
	status = "<?php echo _("Picture is loading...") ?>(" + current_location + " <?php echo _("of") ?> " + photo_count + 
		").  <?php echo _("Please Wait...") ?>" ;
	clearTimeout(timer);
	timer = setTimeout('wait_for_current_photo()', 500);
	return 0;
    } else {
	preload_next_photo();
	reset_timer();
    }
}

function go_to_next_page() {

    var slideShowUrl = document.getElementById('slideShowUrl').href;

    document.location = slideShowUrl + "&slide_index=" + next_location + "&slide_full=" + full
	+ "&slide_loop=" + loop + "&slide_pause=" + (timeout_value / 1000) 
	+ "&slide_dir=" + direction;
    return 0;
}

function go_to_next_photo() {
    /* Go to the next location */
    current_location = next_location;

    /* Show the current photo */
    if (!show_current_photo()) {
	wait_for_current_photo();
	return 0;
    }

    preload_next_photo();
    reset_timer();

}

function preload_next_photo() {
    
    /* Calculate the new next location */
    next_location = (parseInt(current_location) + parseInt(direction));
    if (next_location > photo_count) {
	next_location = 1;
	if (!loop) {
	    stop();
	}
    }
    
    /* Preload the next photo */
    preload_photo(next_location);
}

function show_current_photo() {

    /*
     * If the current photo is not completely loaded don't display it.
     */
    if (!images[current_location] || !images[current_location].complete) {
	preload_photo(current_location);
	return 0;
    }
    
    return 1;
}

function preload_photo(index) {

    /* Load the next picture */
    if (pics_loaded < photo_count) {

	/* not all the pics are loaded.  Is the next one loaded? */
	if (!images[index]) {
	    images[index] = new Image;
	    images[index].onLoad = preload_complete();
	    images[index].src = document.getElementById("photo_urls_" + index).href;
	    pics_loaded++;
	}
    } 
}

</Script>

<?php
$imageDir = $gallery->app->photoAlbumURL."/images"; 
$pixelImage = "<img src=\"$imageDir/pixel_trans.gif\" width=\"1\" height=\"1\">";
?>

<form name="TopForm">

<?php

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
  if ($pAlbumName) {
    $pAlbum = new Album();
    $pAlbum->load($pAlbumName);
    $breadtext[$breadCount] = _("Album") .": <a href=\"" . makeAlbumUrl($pAlbumName) .
      "\">" . $pAlbum->fields['title'] . "</a>";
  } else {
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
$breadcrumb["bordercolor"] = $borderColor;
$breadcrumb["top"] = true;

include($GALLERY_BASEDIR . "layout/breadcrumb.inc");


$adminbox["commands"] = "";
$adminbox["text"] = _("Slide Show");
$adminbox["bordercolor"] = $borderColor;
$adminbox["top"] = true;
include ($GALLERY_BASEDIR . "layout/adminbox.inc");

?>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td colspan="3" bgcolor="<?php echo $borderColor ?>"><?php echo $pixelImage ?></td>
  </tr>
  <tr>
    <td height="25" width="1" bgcolor="<?php echo $borderColor ?>"><?php echo $pixelImage ?></td>
    <td width="5000" align="left" valign="middle">
    <span class=admin>
    &nbsp;<a href="#" onClick='stop(); return false;'>[<?php echo _("stop") ?>]</a>
    <a href="#" onClick='play(); return false;'>[<?php echo _("play") ?>]</a>
<?php
if ($gallery->user->canViewFullImages($gallery->album)) {
    if ($slide_full) {
	echo "<a href=\"" . makeSlideLowUrl($slide_index, $slide_loop, $slide_pause, 0, $slide_dir) 
	    . "\">[". _("normal size") ."]</a>";
    } else {
	echo "<a href=\"" . makeSlideLowUrl($slide_index, $slide_loop, $slide_pause, 1, $slide_dir)
	    . "\">[". _("full size") ."]</a>";
    }
}
?>
<?php
if ($slide_dir == 1) {
    echo "&nbsp;<a href=\"" . makeSlideLowUrl($slide_index, $slide_loop, $slide_pause, $slide_full, -1) 
	. "\">[". _("reverse direction") ."]</a>";
} else {
    echo "&nbsp;<a href=\"" . makeSlideLowUrl($slide_index, $slide_loop, $slide_pause, $slide_full, 1)
	. "\">[". _("forward direction") ."]</a>";
}
?>
    &nbsp;&nbsp;||
    &nbsp;<?php echo _("Delay:") ?>
<?php echo 
drawSelect("time", array(1 => "1 ". _("second"),
                         2 => "2 ". _("seconds"),
                         3 => "3 ". _("seconds"),
                         4 => "4 ". _("seconds"),
                         5 => "5 ". _("seconds"),
                         10 => "10 ". _("seconds"),
                         15 => "15 ". _("seconds"),
                         30 => "30 ". _("seconds"),
                         45 => "45 ". _("seconds"),
                         60 => "60 ". _("seconds")),
           $slide_pause, // default value
           1, // select size
           array('onchange' => 'reset_timer()', 'style' => 'font-size=10px;' ));
?>
    &nbsp;<?php echo _("Loop") ?>:<input type="checkbox" name="loopCheck" <?php echo ($slide_loop) ? "checked" : "" ?> onclick='toggleLoop();'>
    </span>
    </td>
    <td width="1" bgcolor="<?php echo $borderColor ?>"><?php echo $pixelImage ?></td>
  </tr>
  <tr>
    <td colspan="3" bgcolor="<?php echo $borderColor ?>"><?php echo $pixelImage ?></td>
  </tr>
</table>

<br>
<div align="center">

<?php
if ($photo_count > 0) {
?>

<table width=1% border=0 cellspacing=0 cellpadding=0>
  <tr bgcolor="<?php echo $borderColor ?>">
    <td colspan=3 height=<?php echo $borderwidth ?>><?php echo $pixelImage ?></td>
  </tr>
  <tr>
    <td bgcolor="<?php echo $borderColor ?>" width=<?php echo $borderwidth ?>><?php echo $pixelImage ?></td>
    <script language="JavaScript">
    document.write("<td><img border=0 src="+document.getElementById("photo_urls_<?php echo $slide_index ?>").href+" name=slide></td>");
    </script>
    <td bgcolor="<?php echo $borderColor ?>" width=<?php echo $borderwidth ?>><?php echo $pixelImage ?></td>
  </tr>
  <tr bgcolor="<?php echo $borderColor ?>">
    <td colspan=3 height=<?php echo $borderwidth ?>><?php echo $pixelImage ?></td>
  </tr>
</table>
<br>

<script language="Javascript">
/* show the caption either in a nice div or an ugly form textarea */
document.write("<div class='desc'>" + "[" + current_location + " <?php echo _("of") ?> " + photo_count + "] " + photo_captions[<?php echo $slide_index ?>] + "</div>");

/* Load the first picture */
preload_photo(<?php echo $slide_index ?>);

/* Start the show. */
play();

</script>

<?php
} else {
?>

<br><b><?php echo _("This album has no photos to show in a slide show.") ?></b>
<br><br>
<span class="admin">
<a href="<?php echo makeGalleryUrl("view_album.php",
array("set_albumName" => $gallery->session->albumName)) ?>">[<?php echo _("back to album") ?>]</a>
</span>

<?php
}
?> 

</div>
</form>


<?php 
include($GALLERY_BASEDIR . "layout/ml_pulldown.inc");
includeHtmlWrap("slideshow.footer"); ?>

<?php if (!$GALLERY_EMBEDDED_INSIDE) { ?>
</body>
</html>
<?php } ?>
