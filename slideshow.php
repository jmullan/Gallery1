<?
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
<?
// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
                !empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
                !empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
        print "Security violation\n";
        exit;
}
?>
<? require($GALLERY_BASEDIR . "init.php"); ?>
<?
// Hack check
 
if ($gallery->session->albumName == "") {
        header("Location: albums.php");
        return;
}

$borderColor = $gallery->album->fields["bordercolor"];
$borderwidth = $gallery->album->fields["border"];
if (!strcmp($borderwidth, "off")) {
        $borderwidth = 1;
}
$bgcolor = $gallery->album->fields['bgcolor'];
$title = $gallery->album->fields["title"];
?>
<? if (!$GALLERY_EMBEDDED_INSIDE) { ?>
<head>
  <title>Slide Show for album :: <?= $gallery->album->fields["title"] ?></title>
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


<script language="JavaScript">
var timer; 
var current_location = 1;
var next_location = 1; 
var direction = 1; 
var pics_loaded = 0;
var onoff = 0;
var timeout_value;
var images = new Array;
var photo_urls = new Array;
var photo_captions = new Array;
<?php

$numPhotos = $gallery->album->numPhotos($gallery->user->canWriteToAlbum($gallery->album));
$numVisible = 0; 

// Find the correct starting point, accounting for hidden photos
$index = getNextPhoto(0);
$photo_count = 0;
while ($index <= $numPhotos) {
    $photo_count++;
    $photo = $gallery->album->getPhoto($index);

    // Skip movies and nested albums
    if ($photo->isMovie() || $photo->isAlbumName) {
	$index = getNextPhoto($index);
	continue;
    }

    $numVisible++;

    $image = $photo->image;
    if ($photo->image->resizedName) {
        $thumbImage = $photo->image->resizedName;
    } else {
        $thumbImage = $photo->image->name;
    }
    $photoURL = $gallery->album->getAlbumDirURL("full") . "/" . $thumbImage . "." . $image->type;

    // Now lets get the captions
    $caption = $gallery->album->getCaption($index);

    /*
     * Remove unwanted Characters from the comments,
     * We don't use the array based form of str_replace
     * because it's not supported on older versions of PHP
     */
    $caption = str_replace(";", " ", $caption);
    $caption = str_replace("\"", " ", $caption);
    $caption = str_replace("\n", " ", $caption);
    $caption = str_replace("\r", " ", $caption);

    // strip_tags takes out the html tags
    $caption = strip_tags($caption);

    // Print out the entry for this  image
    print "photo_urls[$photo_count] = \"$photoURL\";\n";
    print "photo_captions[$photo_count] = \"$caption\";\n";

    // Go to the next photo
    $index = getNextPhoto($index);
}
?>
var photo_count = <?=$photo_count?>; 

function stop() {
    onoff = 0;
	status = "The slide show is stopped, click Start to resume.";
	document.TopForm.buttonForward.disabled = false;
	document.TopForm.buttonReverse.disabled = false;
	document.TopForm.buttonStop.disabled = true;
	clearTimeout(timer);
}

function change_direction(newDir) {
    onoff = 1;
    direction = newDir;
    document.TopForm.buttonStop.disabled = false;
    document.TopForm.buttonForward.disabled = true;
    document.TopForm.buttonReverse.disabled = true;
    go_to_next_photo();
}

function skip_to() {
    clearTimeout(timer);
    next_location = document.TopForm.currentPhoto.value;
    go_to_next_photo();
}

function preload_complete() {
    status = "Picture Loaded waiting for timer...";
}

function reset_timer() {
    clearTimeout(timer);
    timeout_value = document.TopForm.time.options[document.TopForm.time.selectedIndex].value * 1000;
    timer = setTimeout('go_to_next_photo()', timeout_value);
}

function wait_for_current_photo() {

    /* Show the current photo */
    if (!show_current_photo()) {

	/*
	 * The current photo isn't loaded yet.  Set a short timer just to wait
	 * until the current photo is loaded.
	 */
	status = "Picture " + current_location + " of " + photo_count +
		" is Loading.  Please Wait." ;
	clearTimeout(timer);
	timer = setTimeout('wait_for_current_photo()', 500);
	return 0;
    } else {
	preload_next_photo();

	if (onoff) {
	    reset_timer();
	} else {
	    status = "The slide show is stopped, click Start to resume.";
	}
    }
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
    
    /* Calculate the new next location based upon the direction. */
    next_location = (parseInt(current_location) + parseInt(direction));
    if (next_location > photo_count) {
	next_location = 1;
    }
    if (next_location == 0) {
	next_location = photo_count;
    }
    
    /* Preload the next photo */
    preload_photo(next_location);
}

function show_current_photo() {

    /* Update our current location in the dropdown */
    document.TopForm.currentPhoto.selectedIndex = current_location-1;

    /*
     * If the current photo is not completely loaded don't display it.
     */
    if (images[current_location] == undefined || !images[current_location].complete) {
	preload_photo(current_location);
	return 0;
    }
    
    /* transistion effects */
    if (document.all){
	document.images.slide.style.filter="blendTrans(duration=2)"
	document.images.slide.style.filter="blendTrans(duration=crossFadeDuration)"
	document.images.slide.filters.blendTrans.Apply()      
    }
    document.slide.src = images[current_location].src;
    document.TopForm.captions.value = photo_captions[current_location];
    if (document.all) {
	document.images.slide.filters.blendTrans.Play();
    }

    return 1;
}

function preload_photo(index) {

    /* Load the next picture */
    if (pics_loaded < photo_count) {

	/* not all the pics are loaded.  Is the next one loaded? */
	if (!images[index]) {
	    status = "Pre-loading Photo " + index + " of " + photo_count ;
	    images[index] = new Image;
	    images[index].onLoad = preload_complete();
	    images[index].src = photo_urls[index];
	    pics_loaded++;
	}
    } 
}

</Script>


<? includeHtmlWrap("slideshow.header"); ?>
<?
$imageDir = $gallery->app->photoAlbumURL."/images"; 
$pixelImage = "<img src=\"$imageDir/pixel_trans.gif\" width=\"1\" height=\"1\">";
?>

<form name="TopForm">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td colspan="3" bgcolor="<?= $borderColor ?>"><?= $pixelImage ?></td>
  </tr>
  <tr>
    <td bgcolor="<?= $borderColor ?>"><?= $pixelImage ?></td>
    <td align="right">
     <span class="admin">
       <a href=<?=makeGalleryUrl("view_album.php",
				 array("set_albumName" => $gallery->session->albumName))?>>[back to album]</a>
     </span>
     &nbsp;
    </td>
    <td bgcolor="<?= $borderColor ?>"><?= $pixelImage ?></td>
  </tr>
  <tr>
    <td colspan="3" bgcolor="<?= $borderColor ?>"><?= $pixelImage ?></td>
  </tr>
  <tr>
    <td height=3 bgcolor="<?= $borderColor ?>"><?= $pixelImage ?></td>
    <td><?= $pixelImage ?></td>
    <td bgcolor="<?= $borderColor ?>"><?= $pixelImage ?></td>
  </tr>
  <tr>
    <td bgcolor="<?= $borderColor ?>"><?= $pixelImage ?></td>
    <td valign="bottom" align="left">&nbsp;
    <span class=admin>
    Slide Show:&nbsp;&nbsp;
      <Input style="font-size=10px;" type="button" name="buttonReverse" value="<< Rev" onClick='change_direction(-1)'>
      <Input style="font-size=10px;" type="button" name="buttonStop" value=" Stop " onClick='stop()'>
      <Input style="font-size=10px;" type="button" name="buttonForward" value="Fwd >>" onClick='change_direction(1)'>
      &nbsp;&nbsp;
<?=
drawSelect("time", array(1 => "1 second pause",
			 2 => "2 second pause",
			 3 => "3 second pause",
			 4 => "4 second pause",
			 5 => "5 second pause",
			 10 => "10 second pause",
			 15 => "15 second pause",
			 30 => "30 second pause",
			 45 => "45 second pause",
			 60 => "60 second pause"),
	   3, // default value
	   1, // select size
	   array('onchange' => 'reset_timer()', 'style' => 'font-size=10px;' ));
?>
    &nbsp;&nbsp;Current Photo
<?
	$photoCountArray = array();
	for ($i = 1; $i <= $numVisible; $i++) {
		$photoCountArray[$i] = $i;
	}
	print drawSelect("currentPhoto", 
			$photoCountArray, 
			1, // default value 
			1, // select size
			array("onchange" => "skip_to()", 'style' => 'font-size=10px;'));
?> (of <?= $numVisible ?>)
     </span>
    </td>
    <td bgcolor="<?= $borderColor ?>"><?= $pixelImage ?></td>
  </tr>
  <tr>
    <td height=3 bgcolor="<?= $borderColor ?>"><?= $pixelImage ?></td>
    <td><?= $pixelImage ?></td>
    <td bgcolor="<?= $borderColor ?>"><?= $pixelImage ?></td>
  </tr>
  <tr>
    <td colspan="3" bgcolor="<?= $borderColor ?>"><?= $pixelImage ?></td>
  </tr>
</table>

<br>
<div align="center">


<table width=1% border=0 cellspacing=0 cellpadding=0>
  <tr bgcolor="<?=$borderColor?>">
    <td colspan=3 height=<?=$borderwidth?>><?=$pixelImage?></td>
  </tr>
  <tr>
    <td bgcolor="<?=$borderColor?>" width=<?=$borderwidth?>><?=$pixelImage?></td>
    <script language="JavaScript">
    document.write("<td><img border=0 src="+photo_urls[1]+" name=slide></td>");
    </script>
    <td bgcolor="<?=$borderColor?>" width=<?=$borderwidth?>><?=$pixelImage?></td>
  </tr>
  <tr bgcolor="<?=$borderColor?>">
    <td colspan=3 height=<?=$borderwidth?>><?=$pixelImage?></td>
  </tr>
</table>

<br>
<textarea name="captions" cols="50" rows=3 disabled="true" align="center"></textarea>
</div>

</form>

<script language="JavaScript">
if (photo_count == 0) {
    /*
     * If we don't have any picture to display, alert the user and then
     * redirect to the calling page.
     */
    document.write("This album is empty.");
} else {
    /* Load the first picture */
    document.TopForm.captions.value = photo_captions[1];
    preload_photo(1);

    /* Start the show. */
    change_direction(1);
}
</script>		  

<? includeHtmlWrap("slideshow.footer"); ?>

<? if (!$GALLERY_EMBEDDED_INSIDE) { ?>
</body>
</html>
<? } ?>
