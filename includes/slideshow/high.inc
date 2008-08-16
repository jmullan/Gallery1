<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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

/**
 * Functions for the 'normal' slideshow for modern browser.
 *
 * @package Slideshow
 *
 */

/**
 * Horde Browser class to detect the browser and its abilities.
 */
include_once(GALLERY_BASE .'/classes/horde/Browser.php');

if (class_exists('Browser')) {
    $browser = &Browser::singleton();
}

function slideshow_initialize() {
    global $gallery, $albumName, $defaultLoop, $defaultPause, $defaultTransition, $full_option,
			$album, $recursive, $number, $random, $loop, $borderColor, $borderwidth, $bgcolor,
			$url, $full_urls, $urls, $caption, $captions, $photo_count, $title;

    // default settings ---
    $defaultLoop = 0;
    $defaultTransition = 0;
    $defaultPause = 3;

    $full_option = false;

    define('PHOTO_URL', 1 << 0);
    define('PHOTO_CAPTION', 1 << 1);
    define('PHOTO_URL_AS_HREF', 1 << 2);
    define('PHOTO_ALL', (1 << 16)-1); // all bits set
    $url = array();
    $full_urls = array();
    $caption = array();
    $photo_count = buildSlideshowPhotos($full_urls, $urls, $captions, $album, $recursive);

    if ($photo_count > 0) {
        if ($number == 0 || $number > $photo_count) {
            $number = $photo_count;
        }
        if ($random) {
            $random_full_urls = array();
            $random_photos = array();
            $rand_keys = array_rand ($urls, $number);
            if ($number == 1) {
                $rand_keys = array($rand_keys);
            }
            foreach ($rand_keys as $key) {
                $random_urls[] = $urls[$key];
                $random_full_urls[] = $full_urls[$key];
                $random_captions[] = $captions[$key];
            }
            $urls = $random_urls;
            $full_urls = $random_full_urls;
            $captions = $random_captions;
        }
        if (empty($albumName)) {
            if ($random) {
                $title = sprintf(gTranslate('core', "%s Random Images from %s"),
                    $number,
                    $gallery->app->galleryTitle);
            } else {
                $title = sprintf(gTranslate('core', "Slideshow for Gallery :: %s"),
                    $gallery->app->galleryTitle);
            }
        } else {
            if ($random) {
                $title = sprintf(gTranslate('core', "%d Random Images from album :: %s"),
                    $number,
                    $gallery->album->fields["title"]);
            } else {
                $title = sprintf(gTranslate('core', "Slideshow for album :: %s"), $gallery->album->fields["title"]);
            }
        }
    }
}

function buildSlideshowPhotos(&$full_urls, &$urls, &$captions, $album = null, $recursive = true) {
    global $gallery, $full_option;
    $photo_count = 0;

    if (!$album) {
        // Top level
        $albumDB = new AlbumDB(false);
        $numAlbums = $albumDB->numAlbums($gallery->user);

        for ($i = 1; $i <= $numAlbums; $i++) {
            $subAlbum = $albumDB->getAlbum($gallery->user, $i);
            if (!$gallery->user->canReadAlbum($subAlbum)) {
                continue;
            }
            $photo_count += buildSlideshowPhotos($full_urls, $urls, $captions, $subAlbum, $recursive);
        }
        return $photo_count;
    }
    if (!$gallery->user->canReadAlbum($album)) {
        return $photo_count;
    }
    $numPhotos = $album->numPhotos(1);
    $numDisplayed = 0;
    // Find the correct starting point, accounting for hidden photos
    $index = getNextPhoto(0, $album);
    $photo_count = 0;
    while ($numDisplayed < $numPhotos) {
        if ($index > $numPhotos) {
            /*
  * We went past the end -- this can happen if the last element is
  * an album that we can't read.
  */
            break;
        }

        $photo = $album->getPhoto($index);
        $numDisplayed++;
        // Skip movies
        if ($photo->isMovie()) {
            $index = getNextPhoto($index, $album);
            continue;
        }
        if ($photo->isAlbum()) {
            if ($recursive) {
                $subAlbumName = $album->getAlbumName($index);

                $subAlbum = new Album();
                $subAlbum->load($subAlbumName);
                if ($gallery->user->canReadAlbum($subAlbum)) {
                    $photo_count += buildSlideshowPhotos($full_urls, $urls, $captions, $subAlbum, $recursive);
                }
            }
            $index = getNextPhoto($index, $album);
            continue;
        }
        $photo_count++;

        $urls[] = $album->getPhotoPath($index, 0);
        if ($gallery->user->canViewFullImages($album)) {
            $full_option = true;
            $full_urls[] = $album->getPhotoPath($index, 1);
        } else {
            $full_urls[] = $album->getPhotoPath($index, 0);
        }

        $caption = $album->getCaption($index);
        if ($recursive) {
            $caption .= ' (<a href="' . makeAlbumURL($album->fields["name"]) . '">' . $album->fields['title'] . '<\/a>)';
        }
        $caption .= $album->getCaptionName($index);
        $caption = str_replace("\"", " ", $caption);
        $caption = str_replace("\n", " ", $caption);
        $caption = str_replace("\r", " ", $caption);
        // Print out the entry for this image as Javascript
        $captions[] = $caption;
        // Go to the next photo
        $index = getNextPhoto($index, $album);
    }

    return $photo_count;
}

function printSlideshowPhotos($full_urls, $urls, $captions, $what, $photo_count) {
    $count = 0;
    for ($index = 0; $index < $photo_count; $index++) {
        $count++;
        if (($what &PHOTO_URL) != 0) {
            print "photo_urls[$count] = \"" . $urls[$index] . "\";\n";
        }

        if (($what &PHOTO_URL_AS_HREF) != 0) {
            print "<a id=\"photo_urls_$count\" href=\"" . $urls[$index] . "\"></a>\n";
            print "<a id=\"full_photo_urls_$count\" href=\"" . $full_urls[$index] . "\"></a>\n";
        }
        if (($what &PHOTO_CAPTION) != 0) {
            // Print out the entry for this image as Javascript
            print "photo_captions[$count] = \"" . $captions[$index] . "\";\n";
        }
    }
}

function slideshow_body() {
    global $gallery, $albumName, $defaultLoop, $defaultPause, $defaultTransition, $full_option,
			$album, $recursive, $number, $random, $loop, $borderColor, $borderwidth, $bgcolor,
			$url, $full_urls, $urls, $caption, $captions, $photo_count, $title, $transitionNames,
			$transitionCount, $pixelImage;

    global $playIconText, $stopIconText, $normalSizeIconText, $fullSizeIconText, $forwardIconText, $backwardIconText;
    $photo_count = $number;

/* Here are the URLs of the images written down as links.
** This is to make wget able to convert these links.
** It will not convert them, if they are written inside JavaScript.
** JavaScript will then take the images out of these links with "document.getElementById()".
*/

    printSlideshowPhotos($full_urls, $urls, $captions, PHOTO_URL_AS_HREF,
        $photo_count);

    ?>

<?php // </script> ?>

<script type="text/javascript">
<!--
var timer;
var current_location = 1;
var next_location = 1;
var pics_loaded = 0;
var onoff = 0;
var fullsized = 0;
var direction = 1;
var timeout_value;
var images = new Array;
var photo_urls = new Array;
var full_photo_urls = new Array;
var photo_captions = new Array;
var transitionNames = new Array;
var transitions = new Array;
var current_transition = <?php echo $defaultTransition ?>;
var loop = <?php echo $defaultLoop ?>;
<?php
    printSlideshowPhotos($full_urls, $urls, $captions, PHOTO_CAPTION, $photo_count);

    $transitionNames[] = gTranslate('core', "Blend");
    $transitions[] = 'progid:DXImageTransform.Microsoft.Fade(duration=1)';
    $transitionNames[] = gTranslate('core', "Blinds");
    $transitions[] = 'progid:DXImageTransform.Microsoft.Blinds(Duration=1,bands=20)';
    $transitionNames[] = gTranslate('core', "Checkerboard");
    $transitions[] = 'progid:DXImageTransform.Microsoft.Checkerboard(Duration=1,squaresX=20,squaresY=20)';
    $transitionNames[] = gTranslate('core', "Diagonal");
    $transitions[] = 'progid:DXImageTransform.Microsoft.Strips(Duration=1,motion=rightdown)';
    $transitionNames[] = gTranslate('core', "Doors");
    $transitions[] = 'progid:DXImageTransform.Microsoft.Barn(Duration=1,orientation=vertical)';
    $transitionNames[] = gTranslate('core', "Gradient");
    $transitions[] = 'progid:DXImageTransform.Microsoft.GradientWipe(duration=1)';
    $transitionNames[] = gTranslate('core', "Iris");
    $transitions[] = 'progid:DXImageTransform.Microsoft.Iris(Duration=1,motion=out)';
    $transitionNames[] = gTranslate('core', "Pinwheel");
    $transitions[] = 'progid:DXImageTransform.Microsoft.Wheel(Duration=1,spokes=12)';
    $transitionNames[] = gTranslate('core', "Pixelate");
    $transitions[] = 'progid:DXImageTransform.Microsoft.Pixelate(maxSquare=10,duration=1)';
    $transitionNames[] = gTranslate('core', "Radial");
    $transitions[] = 'progid:DXImageTransform.Microsoft.RadialWipe(Duration=1,wipeStyle=clock)';
    $transitionNames[] = gTranslate('core', "Rain");
    $transitions[] = 'progid:DXImageTransform.Microsoft.RandomBars(Duration=1,orientation=vertical)';
    $transitionNames[] = gTranslate('core', "Slide");
    $transitions[] = 'progid:DXImageTransform.Microsoft.Slide(Duration=1,slideStyle=push)';
    $transitionNames[] = gTranslate('core', "Snow");
    $transitions[] = 'progid:DXImageTransform.Microsoft.RandomDissolve(Duration=1,orientation=vertical)';
    $transitionNames[] = gTranslate('core', "Spiral");
    $transitions[] = 'progid:DXImageTransform.Microsoft.Spiral(Duration=1,gridSizeX=40,gridSizeY=40)';
    $transitionNames[] = gTranslate('core', "Stretch");
    $transitions[] = 'progid:DXImageTransform.Microsoft.Stretch(Duration=1,stretchStyle=push)';

    $transitionNames[] = gTranslate('core', "RANDOM!");
    $transitions[] = 'special case';

    $transitionCount = sizeof($transitions) - 1;

    $trans_i = 0;
    foreach ($transitions as $definition) {
        print "transitions[$trans_i] = \"$definition\";\n";
        $trans_i++;
    }
    print "var transition_count = $transitionCount;\n";

    ?>
var photo_count = <?php echo $photo_count ?>;

// - IE5.5 and up can do the blending transition.
var browserCanBlend = (is_ie5_5up);

function stopOrStart() {
	if (onoff) {
		stop();
	} else {
		play();
	}
}

function fullOrNormal() {
	images = new Array;
	pics_loaded=0;
	if (fullsized) {
		normal();
	} else {
		full();
	}
	next_location = current_location;
	preload_photo(next_location);
	go_to_next_photo();
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
	changeElementText("stopOrStartText", '<?php echo $playIconText; ?>');

	onoff = 0;
	status = "<?php echo unhtmlentities(gTranslate('core', "The slideshow is stopped, click [play] to resume.")) ?>";
	clearTimeout(timer);

}

function play() {
	changeElementText("stopOrStartText", '<?php echo $stopIconText; ?>');
	onoff = 1;
	status = "<?php echo unhtmlentities(gTranslate('core', "Slideshow is running...")) ?>";
	go_to_next_photo();
}

function full() {
	changeElementText("fullOrNormalText", '<?php echo $normalSizeIconText; ?>');
	fullsized = 1;
	status = "<?php echo unhtmlentities(gTranslate('core', "The slide is showing full sized images, click [normal size] to view resized images.")) ?>";
}

function normal() {
	changeElementText("fullOrNormalText", '<?php echo $fullSizeIconText; ?>');

	fullsized = 0;
	status = "<?php echo unhtmlentities(gTranslate('core', "The slide is showing normal sized images, click [full size] to view full sized images.")) ?>";
}

function changeDirection() {
	if (direction == 1) {
		direction = -1;
		changeElementText("changeDirText", '<?php echo $forwardIconText; ?>');
	} else {
		direction = 1;
		changeElementText("changeDirText", '<?php echo $backwardIconText; ?>');
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
		** The current photo isn't loaded yet.  Set a short timer just to wait
		** until the current photo is loaded.
		*/
		status = "<?php echo unhtmlentities(gTranslate('core', "Picture is loading...")) ?>(" + current_location + " <?php echo unhtmlentities(gTranslate('core', "of")) ?>" + photo_count + ").  " + "<?php echo unhtmlentities(gTranslate('core', "Please Wait...")) ?>" ;
		clearTimeout(timer);
		timer = setTimeout('wait_for_current_photo()', 500);
		return 0;
	} else {
		status = "<?php echo unhtmlentities(gTranslate('core', "Slideshow is running...")) ?>" ;
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
			if (fullsized) {
				images[index].src = document.getElementById("full_photo_urls_" + index).href;
			} else {
				images[index].src = document.getElementById("photo_urls_" + index).href;
			}
			pics_loaded++;
		}
	}
}

function setCaption(text) {
	changeElementText("caption", "[" + current_location + " <?php echo gTranslate('core', "of") ?> " + photo_count + "] " + text);
}
// -->
</script>
<?php
}

function slideshow_controlPanel() {
	global $gallery, $albumName, $defaultLoop, $defaultPause, $defaultTransition, $full_option,
			$album, $recursive, $number, $random, $loop, $borderColor, $borderwidth, $bgcolor,
			$url, $full_urls, $urls, $caption, $captions, $photo_count, $title, $transitionNames,
			$transitionCount, $pixelImage, $stopIconText, $browser;

	global $delayIconText, $loopIconText, $stopIconText, $backwardIconText, $fullSizeIconText;
?>
<form name="TopForm" action="#">
<?php
    /* First this is set to "stop" */
    $startStopIconText = $stopIconText;
    $iconElements[] = '<a href="#" onClick="stopOrStart(); return false;" style="display: block"><span id="stopOrStartText">'. $startStopIconText . '</span></a>';

    $directionIconText = $backwardIconText;
    $iconElements[] = '<a href="#" onClick="changeDirection(); return false;" style="display: block"><span id="changeDirText">'. $directionIconText .'</span></a>';

    if ($full_option) {
        $iconElements[] = '<a href="#" onClick="fullOrNormal(); return false;" style="display: block"><span id="fullOrNormalText">'. $fullSizeIconText .'</span></a>';
    }

    $iconElements[] = $delayIconText .':';
	$iconElements[] = drawSelect("time", array(1 => gTranslate('core', "1 second"),
				2 => gTranslate('core', "2 seconds"),
				3 => gTranslate('core', "3 seconds"),
				5 => gTranslate('core', "5 seconds"),
				10 => gTranslate('core', "10 seconds"),
				15 => gTranslate('core', "15 seconds"),
				30 => gTranslate('core', "30 seconds"),
				45 => gTranslate('core', "45 seconds"),
				60 => gTranslate('core', "1 Minute")),
				$defaultPause, // default value
				1, // select size
				array('onchange' => 'reset_timer()', 'class' => 'g-admin')
			);

	if ($loop) {
		$checked = ($defaultLoop) ? 'checked' : '';
		$iconElements[] = $loopIconText;
		$iconElements[] = '<input type="checkbox" name="loopCheck"'. $checked .'onclick="toggleLoop();">';
	}

	echo makeIconMenu($iconElements, 'right', false);
?>
    <td class="g-icon-nolink">
    <?php
    if ($browser->isBrowser('msie') && $browser->getMajor() >= 4) {
        echo gTranslate('core', "Transition:");
        echo "</td><td>";
        echo drawSelect("transitionType",
                  $transitionNames,
                  $defaultTransition,
                  1,
                 array('onchange' => 'change_transition()', 'class' => 'g-admin'));
    }
    ?>
    </td>
    </tr>
    </table>
</form>
<?php
    }


function slideshow_image() {
	global $gallery, $albumName, $defaultLoop, $defaultPause, $defaultTransition, $full_option,
			$album, $recursive, $number, $random, $loop, $borderColor, $borderwidth, $bgcolor,
			$url, $full_urls, $urls, $caption, $captions, $photo_count, $title, $transitionNames,
			$transitionCount, $pixelImage;

	if ($photo_count > 0) {
?>

<div class="center">
  <script type="text/javascript">
	<!--
		firstPhotoURL = document.getElementById("photo_urls_" + 1).href;
		document.write("<img border=<?php echo $borderwidth ?> bgcolor=<?php echo $borderColor ?> src=\"");
		document.write(firstPhotoURL);
		document.write("\" name=slide>");
	// -->
  </script>

<script type="text/javascript">
<!--
/* show the caption */
document.write('<div class="g-photo-caption" id="caption"><\/div>');

/* Load the first picture */
setCaption(photo_captions[1]);
preload_photo(1);

/* Start the show. */
play();
// -->
</script>
</div>

<?php
	}
	else {
		echo "<p><b>". gTranslate('core', "This album has no photos to show in a slide show.") ."</b></p>";
	}
}
?>
