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
$defaultTransition = 0;
$defaultPause = 3;
$defaultFull = 0;

if (!slide_full) {
    $slide_full = $defaultFull;
}

$borderColor = $gallery->album->fields["bordercolor"];
$borderwidth = $gallery->album->fields["border"];
if (!strcmp($borderwidth, "off")) {
        $borderwidth = 1;
}
$bgcolor = $gallery->album->fields['bgcolor'];
$title = $gallery->album->fields["title"];
?>
<?php if (!$GALLERY_EMBEDDED_INSIDE) { ?>
<html> 
<head>
  <title>Slide Show for album :: <?php echo $gallery->album->fields["title"] ?></title>
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

<body>
<?php } ?>


  <script language="JavaScript" SRC="<?php echo $gallery->app->photoAlbumURL ?>/js/client_sniff.js">
  </script>
<script language="JavaScript">
var timer; 
var current_location = 1;
var next_location = 1; 
var pics_loaded = 0;
var onoff = 0;
var direction = 1;
var timeout_value;
var images = new Array;
var photo_urls = new Array;
var photo_captions = new Array;
var transitionNames = new Array;
var transitions = new Array;
var current_transition = <?php echo $defaultTransition ?>;
var loop = <?php echo $defaultLoop ?>;
<?php

$numPhotos = $gallery->album->numPhotos($gallery->user->canWriteToAlbum($gallery->album));
$numDisplayed = 0; 

// Find the correct starting point, accounting for hidden photos
$index = getNextPhoto(0);
$photo_count = 0;
while ($numDisplayed < $numPhotos) { 
    $photo = $gallery->album->getPhoto($index);
    $numDisplayed++;

    // Skip movies and nested albums
    if ($photo->isMovie() || $photo->isAlbumName) {
	$index = getNextPhoto($index);
	continue;
    }

    $photo_count++;

    $photoURL = $gallery->album->getPhotoPath($index, $slide_full);

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
    $photosLeft--;
}

$transitionNames[] = 'Blend';
$transitions[] = 'progid:DXImageTransform.Microsoft.Fade(duration=1)';
$transitionNames[] = 'Blinds';
$transitions[] = 'progid:DXImageTransform.Microsoft.Blinds(Duration=1,bands=20)';
$transitionNames[] = 'Checkerboard';
$transitions[] = 'progid:DXImageTransform.Microsoft.Checkerboard(Duration=1,squaresX=20,squaresY=20)';
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
$transitionNames[] = 'Rain';
$transitions[] = 'progid:DXImageTransform.Microsoft.RandomBars(Duration=1,orientation=vertical)';
$transitionNames[] = 'Slide';
$transitions[] = 'progid:DXImageTransform.Microsoft.Slide(Duration=1,slideStyle=push)';
$transitionNames[] = 'Snow';
$transitions[] = 'progid:DXImageTransform.Microsoft.RandomDissolve(Duration=1,orientation=vertical)';
$transitionNames[] = 'Spiral';
$transitions[] = 'progid:DXImageTransform.Microsoft.Spiral(Duration=1,gridSizeX=40,gridSizeY=40)';
$transitionNames[] = 'Stretch';
$transitions[] = 'progid:DXImageTransform.Microsoft.Stretch(Duration=1,stretchStyle=push)';

$transitionNames[] = 'RANDOM!';
$transitions[] = 'special case';

$transitionCount = sizeof($transitions) - 1;


$trans_i = 0;
foreach ($transitions as $definition) {
    print "transitions[$trans_i] = \"$definition\";\n";
    $trans_i++;
} 
print "var transition_count = $transitionCount;\n";
?>
var photo_count = <?php echo $photo_count?>; 

var slideShowLow = "<?php echo makeGalleryUrl('slideshow_low.php',
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

function stopOrStart() {
    if (onoff) {
	stop();
    } else {
	play();
    }
}

function toggleLoop() {
    if (loop) {
	loop = 0;
    } else {
	loop = 1;
    }
}

function changeElementText(id, newText) {
    element = document.getElementById(id);
    element.innerHTML = newText;
}

function stop() {
    changeElementText("stopOrStartText", "play");

    onoff = 0;
    status = "The slide show is stopped, Click [play] to resume.";
    clearTimeout(timer);

}

function play() {
    changeElementText("stopOrStartText", "stop");

    onoff = 1;
    status = "Slide show is running...";
    go_to_next_photo();
}

function changeDirection() {
    if (direction == 1) {
	direction = -1;
	changeElementText("changeDirText", "forward");
    } else {
	direction = 1;
	changeElementText("changeDirText", "reverse");
    }
    preload_next_photo();

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
	status = "Slide show is running...";
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
    
    /* Calculate the new next location */
    next_location = (parseInt(current_location) + parseInt(direction));
    if (next_location > photo_count) {
	next_location = 1;
	if (!loop) {
	    stop();
	}
    }
    if (next_location == 0) {
        next_location = photo_count;
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
    
    /* transistion effects */
    if (browserCanBlend){
	var do_transition;
	if (current_transition == (transition_count)) {
	    do_transition = Math.floor(Math.random() * transition_count);
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
    changeElementText("caption", "[" + current_location + " of " + photo_count + "] " + text);
}

</Script>


<?php includeHtmlWrap("slideshow.header"); ?>
<?php
$imageDir = $gallery->app->photoAlbumURL."/images"; 
$pixelImage = "<img src=\"$imageDir/pixel_trans.gif\" width=\"1\" height=\"1\">";

?>
<form name="TopForm">
<?php

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
$breadcrumb["bordercolor"] = $borderColor;
$breadcrumb["top"] = true;

include($GALLERY_BASEDIR . "layout/breadcrumb.inc");


$adminbox["commands"] = "<span class=\"admin\">";
$adminbox["commands"] .= "&nbsp;<a href=\"" . makeGalleryUrl("slideshow_low.php",
        array("set_albumName" => $gallery->session->albumName)) . 
	"\">[not working for you? try the low-tech]</a>";
$adminbox["commands"] .= "</span>";
$adminbox["text"] = "Slide Show";
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
    <td align="left" valign="middle">
    <span class=admin>

<?php
echo "&nbsp;<a href='#' onClick='stopOrStart(); return false;'>[<span id='stopOrStartText'>stop</span>]</a>";
echo "&nbsp;<a href='#' onClick='changeDirection(); return false;'>[<span id='changeDirText'>reverse</span> direction]</a>";
if ($slide_full) {
    echo "&nbsp;<a href=\"" . makeGalleryUrl("slideshow.php",
        array("set_albumName" => $gallery->session->albumName)) . "\">[normal size]</a>";
} else {
    echo "&nbsp;<a href=\"" . makeGalleryUrl("slideshow.php",
        array("set_albumName" => $gallery->session->albumName, "slide_full" => 1))
        . "\">[full size]</a>";
}
echo "&nbsp;&nbsp;||";
?>

    &nbsp;Delay: 
<?php echo 
drawSelect("time", array(1 => "1 second",
			 2 => "2 second",
			 3 => "3 second",
			 4 => "4 second",
			 5 => "5 second",
			 10 => "10 second",
			 15 => "15 second",
			 30 => "30 second",
			 45 => "45 second",
			 60 => "60 second"),
	   $defaultPause, // default value
	   1, // select size
	   array('onchange' => 'reset_timer()', 'style' => 'font-size=10px;' ));
?>
    <script language="Javascript">
    /* show the blend select if appropriate */
    if (browserCanBlend) {
	document.write('&nbsp;Transition: <?php 
	    print ereg_replace("\n", ' ', drawSelect("transitionType",
		$transitionNames,
		$defaultTransition,
		1,
		array('onchange' => 'change_transition()', 'style' => 'font-size=10px;')));
	?>');
    }
    
    </script>
    &nbsp;Loop:<input type="checkbox" name="loopCheck" <?php echo ($defaultLoop) ? "checked" : "" ?> onclick='toggleLoop();'>
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
  <tr bgcolor="<?php echo $borderColor?>">
    <td colspan=3 height=<?php echo $borderwidth?>><?php echo $pixelImage?></td>
  </tr>
  <tr>
    <td bgcolor="<?php echo $borderColor?>" width=<?php echo $borderwidth?>><?php echo $pixelImage?></td>
    <script language="JavaScript">
    document.write("<td><img border=0 src="+photo_urls[1]+" name=slide></td>");
    </script>
    <td bgcolor="<?php echo $borderColor?>" width=<?php echo $borderwidth?>><?php echo $pixelImage?></td>
  </tr>
  <tr bgcolor="<?php echo $borderColor?>">
    <td colspan=3 height=<?php echo $borderwidth?>><?php echo $pixelImage?></td>
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
play();

</script>

<?php
} else {
?>

<br><b>This album has no photos to show in a slide show.</b>
<br><br>
<span class="admin">
<a href="<?php echo makeGalleryUrl("view_album.php",
               array("set_albumName" => $gallery->session->albumName))?>">[back to album]</a>
</span>

<?php
}
?> 

</div>
</form>


<?php includeHtmlWrap("slideshow.footer"); ?>

<?php if (!$GALLERY_EMBEDDED_INSIDE) { ?>
</body>
</html>
<?php } ?>
