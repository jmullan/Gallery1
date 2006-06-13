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
?>
<?php

require_once(dirname(__FILE__) . '/init.php');

list($page, $perPage, $save, $next, $prev, $cancel) =
    getRequestVar(array('page', 'perPage', 'save', 'next', 'prev', 'cancel'));
list($captionedAlbum, $extra_fields) =
    getRequestVar(array('captionedAlbum', 'extra_fields'));

// Hack check
if (!$gallery->user->canChangeTextOfAlbum($gallery->album)) {
    header("Location: " . makeAlbumHeaderUrl());
    return;
}

if (!isset($page)) {
    $page = 1;
}

$numPhotos = $gallery->album->numPhotos($gallery->user->canWriteToAlbum($gallery->album));

if (!isset($perPage)) {
    $perPage = $gallery->album->fields['rows'] * $gallery->album->fields['cols'];
    if (!$perPage) {
        $perPage = 5;
    }
}

#-- save the captions from the previous page ---
if (isset($save) || isset($next) || isset($prev)) {

    if ($captionedAlbum != $gallery->album->fields['name']) {
        echo gallery_error(gTranslate('core', "Captioned album does not match current album - aborting changes!"));
        echo '<br><br>';
        echo '<input type="submit" onclick="window.location=\'' . makeAlbumUrl($captionedAlbum) . '\'" value="Exit" class="g-button">';
        exit;
    }

    $i = 0;
    $start = ($page - 1) * $perPage + 1;
    while ($i < $start) {
        $i++;
    }

    $count = 0;
    while ($count < $perPage && $i <= $numPhotos) {
        if ($gallery->album->isAlbum($i)) {
            $myAlbumName = $gallery->album->getAlbumName($i);
            $myAlbum = new Album();
            $myAlbum->load($myAlbumName);
            $myAlbum->fields['description'] = getRequestVar("new_captions_$i");
            $myAlbum->save(array(i18n("Text has been changed")));

        } else {
            $gallery->album->setCaption($i, getRequestVar("new_captions_$i"));
            $gallery->album->setKeywords($i, getRequestVar("new_keywords_$i"));
            if (isset($extra_fields)) {
                foreach ($extra_fields[$i] as $field => $value) {
                    $gallery->album->setExtraField($i, $field, trim($value));
                }
            }
        }

        $i++;
        $count++;
    }

    $gallery->album->save(array(i18n("Text has been changed")));
}

if (isset($cancel) || isset($save)) {
    if (!isDebugging())
    header("Location: " . makeAlbumHeaderUrl($captionedAlbum));
    else
    echo "<br><a href='" . makeAlbumUrl($captionedAlbum) . "'>" . gTranslate('core', "Debugging: Click here to return to the album") . "</a><br>";
    return;
}

#-- did they hit next? ---
if (isset($next)) {
    $page++;
} else if (isset($prev)) {
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

$pixelImage = "SPACER<img src=\"" . getImagePath('pixel_trans.gif') . "\" width=\"1\" height=\"1\" alt=\"spacer\">";

$bordercolor = $gallery->album->fields["bordercolor"];

if (!$GALLERY_EMBEDDED_INSIDE) {
    doctype();
?>
<html>
<head>
  <title><?php echo $gallery->app->galleryTitle ?> :: <?php echo $gallery->album->fields["title"] ?> :: <?php echo gTranslate('core', "Captionator") ?></title>
  <?php common_header(); ?>
  <?php
    if( !empty($gallery->album->fields["linkcolor"]) ||
        !empty($gallery->album->fields["bgcolor"]) ||
        !empty($gallery->album->fields["textcolor"])) {

        echo "\n<style type=\"text/css\">";
        // the link colors have to be done here to override the style sheet
        if ($gallery->album->fields["linkcolor"]) {
                echo "\n  a:link, a:visited, a:active {";
                echo "\n        color: ".$gallery->album->fields['linkcolor'] ."; }";
                echo "\n  a:hover { color: #ff6600; }";

        }
        if ($gallery->album->fields["bgcolor"]) {
                echo "body { background-color:".$gallery->album->fields['bgcolor']."; }";
        }
        if (isset($gallery->album->fields['background']) && $gallery->album->fields['background']) {
                echo "body { background-image:url(".$gallery->album->fields['background']."); } ";
        }
        if ($gallery->album->fields["textcolor"]) {
                echo "body, tf {color:".$gallery->album->fields['textcolor']."; }";
                echo ".head {color:".$gallery->album->fields['textcolor']."; }";
                echo ".headbox {background-color:".$gallery->album->fields['bgcolor']."; }";
        }

        echo "\n  </style>";
    }
?>
</head>

<body>
<?php }

includeHtmlWrap("album.header");

#-- if borders are off, just make them the bgcolor ----
$borderwidth = $gallery->album->fields["border"];
if ($borderwidth == 0) {
    $bordercolor = $gallery->album->fields["bgcolor"];
    $borderwidth = 0;
} else {
    $bordercolor = "black";
}

$adminText = gTranslate('core', "Multiple Caption Editor.") . " ";
if ($numPhotos == 1) {
    $adminText .= gTranslate('core', "1 photo in this album") ;
} else {
    if ($maxPages > 1) {
        $adminText .= sprintf (gTranslate('core', "%s items in this album on %s"),
		$numPhotos,
		gTranslate('core',"one page", "%d pages", $maxPages));
    }
    else {
        $adminText .= sprintf (gTranslate('core', "%s items in this album"), $numPhotos);
    }
}

$adminbox['text'] = $adminText;
$adminbox['bordercolor'] = $bordercolor;

$breadcrumb["text"] = returnToPathArray($gallery->album, true);
includeLayout('adminbox.inc');

includeLayout('breadcrumb.inc');

if ($numPhotos) {

    // Find the correct starting point, accounting for hidden photos
    $i = 0;
    while ($i < $start) {
        $i++;
    }

    $count = 0;

    echo makeFormIntro("captionator.php") ?>
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="perPage" value="<?php echo $perPage ?>">
    <input type="hidden" name="captionedAlbum" value="<?php echo $gallery->album->fields['name']; ?>">

    <div class="right" style="padding-bottom: 2px;">
	<input type="submit" name="save" value="<?php echo gTranslate('core', "Save and Exit") ?>" class="g-button">
    <?php
    if (!isset($last)) {
        echo '<input type="submit" name="next" value="'. sprintf(gTranslate('core', "Save and Edit Next %d"),$perPage) .'" class="g-button">';
    }

    if ($page != 1) {
         echo '<input type="submit" name="prev" value="'. sprintf(gTranslate('core', "Save and Edit Previous %d"), $perPage) .'" class="g-button">';
    }
    ?>

    <input type="submit" name="cancel" value="<?php echo gTranslate('core', "Exit") ?>" class="g-button">
    </div>

    <!-- image grid table -->
    <table class="g-albums-table" cellspacing="0" cellpadding="0">
<?php
    // Go trough the album
    while ($count < $perPage && $i <= $numPhotos) {
	$photo = $gallery->album->getPhoto($i);
	if($photo->getAlbumName() === NULL) {
	    list($width, $height) = $photo->getDimensions();
	    list($twidth, $theight) = $photo->thumbnail->getDimensions();
	}
?>
	<!-- Picture #<?php echo $i-1 ?> -->
<tr>
	<td class="g-album-image-cell" style="border-top: 1px solid #000; vertical-align: middle;">
<?php
	if (!($photo->isMovie())) {
	    echo popup_link2(
		$gallery->album->getThumbnailTag($i, $thumbSize),
    		$gallery->album->getPhotoPath($i),
		array(
		    'height' => $height+20,
		    'width' => $width+20,
		    'accesskey' => false
		)
	    );
	    echo "<br>". gTranslate('core', "(click to enlarge)");
	}
	else {
	    echo $gallery->album->getThumbnailTag($i,$thumbSize);
	}
	if ($gallery->album->isHidden($i) && !$gallery->session->offline) {
	    echo "<br>(" . gTranslate('core', "hidden") .")<br>";
	}
?>
	</td>
	<td class="g-albumdesc-cell1">
<?php
	if ($gallery->album->isAlbum($i)) {
	    // Found Element is an album
	    $myAlbumName = $gallery->album->getAlbumName($i);
	    $myAlbum = new Album();
	    $myAlbum->load($myAlbumName);
	    $oldCaption = $myAlbum->fields['description'];

	    echo "\n\t\t". '<p class="g-admin">'. gTranslate('core', "Album Caption: ");
	    echo '<br><textarea name="new_captions_'. $i .'" rows="3" cols="60">'. $oldCaption .'</textarea></p>';
	} else {
	    $oldCaption = $gallery->album->getCaption($i);
	    $oldKeywords = $gallery->album->getKeywords($i);
	    $translateableFields = translateableFields();

	    if ($gallery->album->photos[$i-1]->isMovie()) {
	        echo "\n\t\t". '<p class="g-admin">'. gTranslate('core', "Movie Caption: ");
	    } else {
	        echo "\n\t\t". '<p class="g-admin">'. gTranslate('core', "Photo Caption: ");
	    }
	    echo '<br><textarea name="new_captions_'. $i .'" rows="3" cols="60">'. $oldCaption .'</textarea></p>';
	    foreach ($gallery->album->getExtraFields() as $field) {
	        if (in_array($field, array_keys(automaticFieldsList()))) {
	            continue;
	        }
	        $value = $gallery->album->getExtraField($i, $field);
	        if ($field == "Title") {
	            echo "\n\t\t". '<div class="g-admin">' . gTranslate('core', "Title: ") .'</div>';
	            echo "\n\t\t<input type=\"text\" name=\"extra_fields[$i][$field]\" value=\"$value\" size=\"40\">";
	        }
	        else {
	            echo "\n\t\t". '<br><span class="g-admin">'. $translateableFields[$field] .": </span><br>";
	            echo "\n\t\t<textarea name=\"extra_fields[$i][$field]\" rows=\"2\" cols=\"60\">$value</textarea>";
	        }
	    }

	    echo "\n\t\t". '<p class="g-admin">'. gTranslate('core', "Keywords: ") . '<br>';
	    echo "\n\t\t". '<input type="text" name="new_keywords_'. $i .'" size="65" value="'. $oldKeywords .'"></p>';

	    $itemCaptureDate = $gallery->album->getItemCaptureDate($i);
	    $capturedate = strftime($gallery->app->dateTimeString , $itemCaptureDate);

	    echo "\n\t\t". '<p class="g-admin">'. sprintf(gTranslate('core', "Capture Date: %s"),$capturedate) . '</p><br>';
	}
	echo "\n\t</td>";
	echo "\n</tr>";

	$i++;
	$count++;
    }
?>
    </table>

    <div class="right">
	<input type="submit" name="save" value="<?php echo gTranslate('core', "Save and Exit") ?>" class="g-button">
    <?php
    if (!isset($last)) {
        echo '<input type="submit" name="next" value="'. sprintf(gTranslate('core', "Save and Edit Next %d"),$perPage) .'" class="g-button">';
    }

    if ($page != 1) {
         echo '<input type="submit" name="prev" value="'. sprintf(gTranslate('core', "Save and Edit Previous %d"), $perPage) .'" class="g-button">';
    }
    ?>

    <input type="submit" name="cancel" value="<?php echo gTranslate('core', "Exit") ?>" class="g-button">
    </div>
<?php
} else {
    echo infoBox(array(array(
	'type' => 'information',
	'text' => gTranslate('core', "There are not elements to set a caption for.")
    )));
}
?>
</form>
<br>

<?php
echo languageSelector();

$validation_file = 'captionator.php';
includeHtmlWrap('general.footer');

if (!$GALLERY_EMBEDDED_INSIDE) { ?>
</body>
</html>
<?php } ?>
