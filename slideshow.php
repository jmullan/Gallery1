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


  <script language="JavaScript" SRC="<? echo $gallery->app->photoAlbumURL ?>/js/client_sniff.js">
  </script>
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
var transitionNames = new Array;
var transitions = new Array;
var current_transition = 1;
<?php

$numPhotos = $gallery->album->numPhotos($gallery->user->canWriteToAlbum($gallery->album));
$numVisible = 0; 

// Find the correct starting point, accounting for hidden photos
$index = getNextPhoto(0);
$photo_count = 0;
while ($index <= $numPhotos) {
    $photo = $gallery->album->getPhoto($index);

    // Skip movies and nested albums
    if ($photo->isMovie() || $photo->isAlbumName) {
	$index = getNextPhoto($index);
	continue;
    }

    $photo_count++;
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

    // Print out the entry for this image as Javascript
    print "photo_urls[$photo_count] = \"$photoURL\";\n";
    print "photo_captions[$photo_count] = \"$caption\";\n";

    // Go to the next photo
    $index = getNextPhoto($index);
}

$transitionNames[] = '!! Random !!';
$transitions[] = 'special case';
$transitionNames[] = 'Blend';
$transitions[] = 'progid:DXImageTransform.Microsoft.Fade(duration=1)';
$transitionNames[] = 'Blinds';
$transitions[] = 'progid:DXImageTransform.Microsoft.Blinds(Duration=1,bands=20)';
$transitionNames[] = 'Checkerboard';
$transitions[] = 'progid:DXImageTransform.Microsoft.Checkerboard(Duration=1,squaresX=20,squaresY=20)';
$transitionNames[] = 'Crazy Bars';
$transitions[] = 'progid:DXImageTransform.Microsoft.RandomBars(Duration=1,orientation=vertical)';
$transitionNames[] = 'Crazy Pixels';
$transitions[] = 'progid:DXImageTransform.Microsoft.RandomDissolve(Duration=1,orientation=vertical)';
$transitionNames[] = 'Diagonal';
$transitions[] = 'progid:DXImageTransform.Microsoft.Strips(Duration=1,motion=rightdown)';
$transitionNames[] = 'Doors';
$transitions[] = 'progid:DXImageTransform.Microsoft.Barn(Duration=1,orientation=vertical)';
$transitionNames[] = 'Gradient';
$transitions[] = 'progid:DXImageTransform.Microsoft.GradientWipe(duration=1)';
$transitionNames[] = 'Iris';
$transitions[] = 'progid:DXImageTransform.Microsoft.Iris(Duration=1,motion=out)';
$transitionNames[] = 'Pinwheel';
$transitions[] = 'progid:DXImageTransform.Microsoft.Wheel(Duration=1,spokes=12)';
$transitionNames[] = 'Pixelate';
$transitions[] = 'progid:DXImageTransform.Microsoft.Pixelate(maxSquare=10,duration=1)';
$transitionNames[] = 'Radial';
$transitions[] = 'progid:DXImageTransform.Microsoft.RadialWipe(Duration=1,wipeStyle=clock)';
$transitionNames[] = 'Slide';
$transitions[] = 'progid:DXImageTransform.Microsoft.Slide(Duration=1,slideStyle=push)';
$transitionNames[] = 'Spiral';
$transitions[] = 'progid:DXImageTransform.Microsoft.Spiral(Duration=1,gridSizeX=40,gridSizeY=40)';
$transitionNames[] = 'Stretch';
$transitions[] = 'progid:DXImageTransform.Microsoft.Stretch(Duration=1,stretchStyle=push)';

$transitionCount = sizeof($transitions) - 1;


$trans_i = 0;
foreach ($transitions as $definition) {
    print "transitions[$trans_i] = \"$definition\";\n";
    $trans_i++;
} 
print "var transition_count = $transitionCount;\n";
?>
var photo_count = <?=$photo_count?>; 

var slideShowLow = "<?= makeGalleryUrl('slideshow_low.php',
                           array('set_albumName' => $gallery->session->albumName)); ?>";

// Browser capabilities detection ---
// - assume only IE4+ and NAV6+ can do image resizing, others redirect to low 
if (is_ie4up || is_nav6up) {
    //-- it's all good ---
} else {
    //-- any other browser we go low-tech ---
    document.location = slideShowLow;
}

// - IE5.5 and up can do the blending transition.
var browserCanBlend = (is_ie5_5up);

function stop() {
    onoff = 0;
    status = "The slide show is stopped, Click Fwd or Rev to resume.";
    clearTimeout(timer);
}

function change_direction(newDir) {
    onoff = 1;
    direction = newDir;
    go_to_next_photo();
}

function skip_to() {
    clearTimeout(timer);
    next_location = document.TopForm.currentPhoto.selectedIndex+1;
    go_to_next_photo();
}

function change_transition() {
    current_transition = document.TopForm.transitionType.selectedIndex;
}

function preload_complete() {
}

function reset_timer() {
    clearTimeout(timer);
    if (onoff) {
	timeout_value = document.TopForm.time.options[document.TopForm.time.selectedIndex].value * 1000;
	timer = setTimeout('go_to_next_photo()', timeout_value);
    }
}

function wait_for_current_photo() {

    /* Show the current photo */
    if (!show_current_photo()) {

	/*
	 * The current photo isn't loaded yet.  Set a short timer just to wait
	 * until the current photo is loaded.
	 */
	status = "Picture is loading...(" + current_location + " of " + photo_count +
		").  Please Wait..." ;
	clearTimeout(timer);
	timer = setTimeout('wait_for_current_photo()', 500);
	return 0;
    } else {
	preload_next_photo();
	reset_timer();
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
	stop();
    }
    if (next_location == 0) {
	next_location = photo_count;
	stop();
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
    
    status = "Slide show running...(" + current_location + " of " + photo_count + ")...";

    /* Update our current location in the dropdown */
    document.TopForm.currentPhoto.selectedIndex = current_location-1;

    /* transistion effects */
    if (browserCanBlend){
	var do_transition;
	if (current_transition == 0) {
	    do_transition = Math.floor(Math.random() * transition_count) + 1;
	} else {
	    do_transition = current_transition;
	}
	document.images.slide.style.filter=transitions[do_transition];
	document.images.slide.filters[0].Apply();
    }
    document.slide.src = images[current_location].src;
    setCaption(photo_captions[current_location]);

    if (browserCanBlend) {
	document.images.slide.filters[0].Play();
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
	    images[index].src = photo_urls[index];
	    pics_loaded++;
	}
    } 
}

function setCaption(text) {
    captionBlock = document.getElementById("caption");
    captionBlock.innerHTML = text;
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
    <td>
    <span class=desc>
    &nbsp;&nbsp;Slide Show:&nbsp;&nbsp;
    </span>
    <span class="admin">
    [not working for you? Try the <a href="<?=makeGalleryUrl("slideshow_low.php",
                                 array("set_albumName" => $gallery->session->albumName))?>">low-tech slide show</a>]
    </span>
    </td>
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
    <td colspan="2"><?= $pixelImage ?></td>
    <td bgcolor="<?= $borderColor ?>"><?= $pixelImage ?></td>
  </tr>
  <tr>
    <td bgcolor="<?= $borderColor ?>"><?= $pixelImage ?></td>
    <td colspan="2" valign="bottom" align="left">&nbsp;
    <span class=admin>
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
	   2, // default value
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
?> (of <?= $numVisible ?>).

    <script language="Javascript">
    /* show the blend select if appropriate */
    if (browserCanBlend) {
	document.write('&nbsp;&nbsp;Transition <? 
	    print ereg_replace("\n", ' ', drawSelect("transitionType",
		$transitionNames,
		1,
		1,
		array('onchange' => 'change_transition()', 'style' => 'font-size=10px;')));
	?>');
    }
    
    </script>
     </span>
    </td>
    <td bgcolor="<?= $borderColor ?>"><?= $pixelImage ?></td>
  </tr>
  <tr>
    <td height=3 bgcolor="<?= $borderColor ?>"><?= $pixelImage ?></td>
    <td colspan="2"><?= $pixelImage ?></td>
    <td bgcolor="<?= $borderColor ?>"><?= $pixelImage ?></td>
  </tr>
  <tr>
    <td colspan="3" bgcolor="<?= $borderColor ?>"><?= $pixelImage ?></td>
  </tr>
</table>

<br>
<div align="center">

<?
if ($photo_count > 0) {
?>

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

<script language="Javascript">
/* show the caption */
document.write("<div class='desc' id='caption'></div>");

/* Load the first picture */
setCaption(photo_captions[1]);
preload_photo(1);

/* Start the show. */
change_direction(1);

</script>

<?
} else {
?>

<br><b>This album has no photos to show in a slide show.</b>
<br><br>
<span class="admin">
<a href="<?=makeGalleryUrl("view_album.php",
               array("set_albumName" => $gallery->session->albumName))?>">[back to album]</a>
</span>

<?
}
?> 

</div>
</form>


<? includeHtmlWrap("slideshow.footer"); ?>

<? if (!$GALLERY_EMBEDDED_INSIDE) { ?>
</body>
</html>
<? } ?>
