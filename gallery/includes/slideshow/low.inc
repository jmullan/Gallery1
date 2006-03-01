<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2006 Bharat Mediratta
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
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * $Id$
 */

/*
 * DEVELOPERS.  PLEASE READ!!!
 *
 *    If you are changing any Javascript in this file please be very
 *    careful.  This is designed to work with the oldest, bodgiest
 *    browsers, so try not to introduce any new functions.  It's better
 *    to have reduced functionality here than have it broken on some
 *    browsers.  If you MUST add new JS, research it thoroughly to make
 *    sure it should work with ancient browsers, but better to only 
 *    change the JS to fix bugs.
 *
 *       Thanks
 */
?>
<?php

list($slide_index, $slide_pause, $slide_full, $slide_dir, $slide_loop) = 
  getRequestVar(array('slide_index', 'slide_pause', 'slide_full', 'slide_dir', 'slide_loop'));

function slideshow_initialize() {
	global $defaultLoop, $defaultPause, $defaultFull, $defaultDir,
			$slide_index, $slide_pause, $slide_loop, $slide_full,
			$slide_dir, $borderColor, $borderwidth, $loop,
			$bgcolor, $title, $gallery;
	// default settings ---
	$defaultLoop = 0;
	$defaultPause = 3;
	$defaultFull = 0;
	$defaultDir = 1;
	
	if (!isset($slide_index)) {
	    $slide_index = 1;
	}
	if (!isset($slide_pause)) {
	    $slide_pause = $defaultPause;
	}
	if (!isset($slide_loop)) {
	    $slide_loop = $defaultLoop;
	}
	if (!isset($slide_full)) {
	    $slide_full = $defaultFull;
	}
	if (!isset($slide_dir)) {
	    $slide_dir = $defaultDir;
	}
	
	if ($slide_full && !$gallery->user->canViewFullImages($gallery->album)) {
	    $slide_full = 0;
	}
	
	$bgcolor = $gallery->album->fields['bgcolor'];
	$title = _("Slide Show for album") ." :: ". $gallery->album->fields["title"];
}

function makeSlideLowUrl($index, $loop, $pause, $full, $dir) {
	global $gallery;

    return makeGalleryUrl('slideshow.php',
	array('set_albumName' => $gallery->session->albumName,
	      'slide_index' => $index,
	      'slide_loop' => $loop,
	      'slide_pause' => $pause,
	      'slide_full' => $full,
	      'slide_dir' => $dir,
		  'mode' => 'low'));
}

function slideshow_body() {
	global $defaultLoop, $defaultPause, $defaultFull, $defaultDir,
			$slide_index, $slide_pause, $slide_loop, $slide_full,
			$slide_dir, $borderColor, $borderwidth, $loop,
			$bgcolor, $title, $gallery, $numPhotos, $numDisplayed,
			$index, $photo_count, $photo, $image, $thumbImage,
			$photoURL, $caption, $imageDir, $pixelImage, $number;
?>
<script type="text/javascript">
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
<?php

$numPhotos = $gallery->album->numPhotos(1);
$numDisplayed = 0; 

// A slideshow_length of 0 means "show all"
// as does a length which is greater than the number of photos
if ($number == 0 || $number > $numPhotos) {
        $number = $numPhotos; 
}       

// Find the correct starting point, accounting for hidden photos
$index = getNextPhoto(0);
$photo_count = 0;
while ($numDisplayed < $numPhotos && $numDisplayed < $number) { 
    if ($index > $numPhotos) {
	/*
	 * We went past the end -- this can happen if the last element is an
	 * album that we can't read.
	 */
	break;
    }
    $photo = $gallery->album->getPhoto($index);
    $numDisplayed++;

    // Skip movies and nested albums
    if ($photo->isMovie() || $photo->isAlbum()) {
	$index = getNextPhoto($index);
	continue;
    }

    $photo_count++;

    $image = $photo->image;
    if ($photo->image->resizedName) {
        $thumbImage = $photo->image->resizedName;
    } else {
        $thumbImage = $photo->image->name;
    }
    $photoURL = $gallery->album->getPhotoPath($index, $slide_full);

    // Now lets get the captions
    $caption = $gallery->album->getCaption($index);
    $caption .= $gallery->album->getCaptionName($index);

    $caption = str_replace("\"", " ", $caption);
    $caption = str_replace("\n", " ", $caption);
    $caption = str_replace("\r", " ", $caption);

    // Print out the entry for this image as Javascript
    print "photo_urls[$photo_count] = \"$photoURL\";\n";
    print "photo_captions[$photo_count] = \"$caption\";\n";

    // Go to the next photo
    $index = getNextPhoto($index);
}
?>
var photo_count = <?php echo $photo_count ?>; 

function stop() {
    onoff = 0;
    status = "<?php echo unhtmlentities(_("The slide show is stopped, Click [play] to resume.")) ?>";
    clearTimeout(timer);
}

function play() {
    onoff = 1;
    status = "<?php echo unhtmlentities(_("Slide show is running...")) ?>";
    wait_for_current_photo();
}

function toggleLoop() {
    if (loop) {
        loop = 0;
    } else {
        loop = 1;
    }
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
	 status = "<?php echo unhtmlentities(_("Picture is loading...")) ?>(" + current_location + " <?php echo unhtmlentities(_("of")) ?> " + photo_count +
		 ").  <?php echo unhtmlentities(_("Please Wait...")) ?>" ;

	clearTimeout(timer);
	timer = setTimeout('wait_for_current_photo()', 500);
	return 0;
    } else {
	preload_next_photo();
	reset_timer();
    }
}

function go_to_next_page() {

    var slideShowUrl = "<?php echo makeGalleryUrl('slideshow.php',
				array('set_albumName' => $gallery->session->albumName, 'mode' => 'low')); ?>";

    document.location = slideShowUrl + "&slide_index=" + next_location + "&slide_full=" + full
	+ "&slide_loop=" + loop + "&slide_pause=" + (timeout_value / 1000) 
	+ "&slide_dir=" + direction;
    return 0;
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
    if (next_location < 1) {
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
    
    return 1;
}

function preload_photo(index) {

    /* Load the next picture */
    if (pics_loaded < photo_count) {

	/* not all the pics are loaded.  Is the next one loaded? */
	if (!images[index]) {
	    images[index] = new Image;
	    images[index].src = photo_urls[index];
	    pics_loaded++;
	}
    } 
}

</script>

<?php
}

function slideshow_controlPanel() {
    global $defaultLoop, $defaultPause, $defaultFull, $defaultDir,
		$slide_index, $slide_pause, $slide_loop, $slide_full,
		$slide_dir, $borderColor, $borderwidth, $loop,
		$bgcolor, $title, $gallery, $numPhotos, $numDisplayed,
		$index, $photo_count, $photo, $image, $thumbImage,
		$photoURL, $caption, $imageDir, $pixelImage;
     global $playIconText,$stopIconText,$delayIconText, $forwardIconText, 
		$backwardIconText, $normalSizeIconText, $fullSizeIconText,
		$loopIconText;
?>
<form name="TopForm" style="margin:0px">
<?php
    $iconElements[] = '<a href="#" onClick="stop(); return false;">'. $stopIconText .'</a>';
    $iconElements[] = '<a href="#" onClick="play(); return false;">'. $playIconText .'</a>';

if ($slide_dir == 1) {
    $iconElements[] = '<a href="'. makeSlideLowUrl($slide_index, $slide_loop, $slide_pause, $slide_full, -1) .'">'. $forwardIconText .'</a>';
} else {
    $iconElements[] = '<a href="'. makeSlideLowUrl($slide_index, $slide_loop, $slide_pause, $slide_full, 1) .'">'. $backwardIconText .'</a>';
}

if ($gallery->user->canViewFullImages($gallery->album)) {
    if ($slide_full) {
        $iconElements[] = '<a href="'. makeSlideLowUrl($slide_index, $slide_loop, $slide_pause, 0, $slide_dir) .'">'. $normalSizeIconText .'</a>';
    } else {
	$iconElements[] = '<a href="'. makeSlideLowUrl($slide_index, $slide_loop, $slide_pause, 1, $slide_dir) .'">'. $fullSizeIconText .'</a>';
    }
}

    $iconElements[] = $delayIconText .":";
    $iconElements[] = drawSelect("time", array(1 => _("1 second"),
				2 => _("2 seconds"),
				3 => _("3 seconds"),
				5 => _("5 seconds"),
				10 => _("10 seconds"),
				15 => _("15 seconds"),
				30 => _("30 seconds"),
				45 => _("45 seconds"),
				60 => _("1 Minute")),
				$defaultPause, // default value
				1, // select size
				array('onchange' => 'reset_timer()', 'class' => 'adminform')
			);

if ($loop) {
    $checked = ($defaultLoop) ? 'checked' : '';
    $iconElements[] = $loopIconText;
    $iconElements[] = '<input type="checkbox" name="loopCheck"'. $checked .'onclick="toggleLoop();">';
}

    echo makeIconMenu($iconElements, 'right');
?>
</form>
<?php
}

function slideshow_image() {
	global $defaultLoop, $defaultPause, $defaultFull, $defaultDir,
			$slide_index, $slide_pause, $slide_loop, $slide_full,
			$slide_dir, $borderColor, $borderwidth, $loop,
			$bgcolor, $title, $gallery, $numPhotos, $numDisplayed,
			$index, $photo_count, $photo, $image, $thumbImage,
			$photoURL, $caption, $imageDir, $pixelImage;
?>
<div align="center">

<?php
if ($photo_count > 0) {
?>

<table border="0" cellspacing="0" cellpadding="0">
<tr>
	<td>
	<script type="text/javascript">
	    document.write("<img border==<?php echo $borderwidth ?> bgcolor=<?php echo $borderColor ?> src="+photo_urls[<?php echo $slide_index ?>]+" name=slide>");
	</script>
</tr>
</table>

<br>

<script type="text/javascript">
/* show the caption either in a nice div or an ugly form textarea */
document.write("<div class='pcaption'>" + "[" + current_location + " <?php echo _("of") ?> " + photo_count + "] " + photo_captions[<?php echo $slide_index ?>] + "</div>");

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

<?php } ?>
