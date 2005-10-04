<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2005 Bharat Mediratta
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

list($page, $perPage, $save, $next, $prev, $cancel) = getRequestVar(array('page', 'perPage', 'save', 'next', 'prev', 'cancel'));
list($captionedAlbum, $extra_fields) = getRequestVar(array('captionedAlbum', 'extra_fields'));

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
        echo gallery_error(_("Captioned album does not match current album - aborting changes!"));
        echo '<br><br>';
        echo '<input type="submit" onclick="window.location=\'' . makeAlbumUrl($captionedAlbum) . '\'" value="Exit">';
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
    echo "<br><a href='" . makeAlbumUrl($captionedAlbum) . "'>" . _("Debugging: Click here to return to the album") . "</a><br>";
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

$pixelImage = "<img src=\"" . getImagePath('pixel_trans.gif') . "\" width=\"1\" height=\"1\" alt=\"spacer\">";

$bordercolor = $gallery->album->fields["bordercolor"];

if (!$GALLERY_EMBEDDED_INSIDE) {
    doctype();
?>
<html> 
<head>
  <title><?php echo $gallery->app->galleryTitle ?> :: <?php echo $gallery->album->fields["title"] ?> :: <?php echo _("Captionator") ?></title>
  <?php common_header(); ?>
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

<body dir="<?php echo $gallery->direction ?>">
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

$adminText = _("Multiple Caption Editor.") . " ";
if ($numPhotos == 1) {
    $adminText .= _("1 photo in this album") ;
} else {
    $adminText .= "$numPhotos ". _("items in this album") ;
    if ($maxPages > 1) {
        $adminText .= " " . _("on") . " " . pluralize_n2(ngettext("one page", "%d pages", $maxPages), $maxPages);
    }
}

$adminbox['text'] = $adminText;
$adminbox['bordercolor'] = $bordercolor;
$upArrowURL = '<img src="' . getImagePath('nav_home.gif') . '" width="13" height="11" ' . 
  'alt="' . _("navigate UP") .'" title="' . _("navigate UP") .'" border="0">';
$breadcrumb['text'][] = _("Album: ") .'<a class="bread" href="'. makeAlbumUrl($gallery->album->albumName) .'">'. $gallery->album->fields['title'] . '&nbsp;' 
  . $upArrowURL . '</a>';

includeLayout('navtablebegin.inc');
includeLayout('adminbox.inc');
includeLayout('navtablemiddle.inc');
includeLayout('breadcrumb.inc');
includeLayout('navtableend.inc');

echo makeFormIntro("captionator.php") ?>
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="perPage" value="<?php echo $perPage ?>">
<input type="hidden" name="captionedAlbum" value="<?php echo $gallery->album->fields['name']; ?>">

<div align="right">
	<input type="submit" name="save" value="<?php echo _("Save and Exit") ?>">
<?php 
if (!isset($last)) {
    echo '<input type="submit" name="next" value="'. sprintf(_("Save and Edit Next %d"),$perPage) .'">';
}

if ($page != 1) {
    echo '<input type="submit" name="prev" value="'. sprintf(_("Save and Edit Previous %d"), $perPage) .'">';
}
?>

<input type="submit" name="cancel" value="<?php echo _("Exit") ?>">
</div>

<!-- image grid table -->
<table width="100%" border="0" cellspacing="4" cellpadding="0">
<?php
if ($numPhotos) {

    // Find the correct starting point, accounting for hidden photos
    $i = 0;
    while ($i < $start) {
        $i++;
    }

    $count = 0;

    // Go trough the album
    while ($count < $perPage && $i <= $numPhotos) {
?>  
	<!-- Picture #<?php echo $i-1 ?> -->  
<tr>
	<td height="1" colspan="2"><?php echo $pixelImage ?></td>
	<td bgcolor="<?php echo $bordercolor ?>" height="1"><?php echo $pixelImage ?></td>
</tr>
<tr>
	<td width="<?php echo $thumbSize ?>" align="center" valign="top" class="modcaption"><br>
<?php
$photo = $gallery->album->getPhoto($i);
list($width, $height) = $photo->getDimensions();
if (!($photo->isMovie())) {
    echo popup_link($gallery->album->getThumbnailTag($i, $thumbSize).
    "<br />"._("(click to enlarge)"),
    $gallery->album->getPhotoPath($i),1,false,
    $height+20,$width+20,
    'modcaption');
} else {
    echo $gallery->album->getThumbnailTag($i,$thumbSize);
}
if ($gallery->album->isHidden($i) && !$gallery->session->offline) {
    echo "<br>(" . _("hidden") .")<br>";
}
?>	
	</td>
	<td height="1"><?php echo $pixelImage ?></td>
	<td valign="top"><?php
	if ($gallery->album->isAlbum($i)) {
	    // Found Element is an album
	    $myAlbumName = $gallery->album->getAlbumName($i);
	    $myAlbum = new Album();
	    $myAlbum->load($myAlbumName);
	    $oldCaption = $myAlbum->fields['description'];

	    echo "\n\t\t". '<p class="admin">'. _("Album Caption") . ': ';
	    echo '<br><textarea name="new_captions_'. $i .'" rows="3" cols="60">'. $oldCaption .'</textarea></p>';
	} else {
	    $oldCaption = $gallery->album->getCaption($i);
	    $oldKeywords = $gallery->album->getKeywords($i);

	    if ($gallery->album->photos[$i-1]->isMovie()) {
	        echo "\n\t\t". '<p class="admin">'. _("Movie Caption") . ': ';
	    } else {
	        echo "\n\t\t". '<p class="admin">'. _("Photo Caption") . ': ';
	    }
	    echo '<br><textarea name="new_captions_'. $i .'" rows="3" cols="60">'. $oldCaption .'</textarea></p>';
	    foreach ($gallery->album->getExtraFields() as $field) {
	        if (in_array($field, array_keys(automaticFieldsList()))) {
	            continue;
	        }
	        $value=$gallery->album->getExtraField($i, $field);
	        if ($field == "Title") {
	            echo "\n\t\t". '<div class="admin">' . _("Title") .': </div>';
	            echo "\n\t\t<input type=\"text\" name=\"extra_fields[$i][$field]\" value=\"$value\" size=\"40\">";
	        }
	        else {
	            echo "\n\t\t". '<br><span class="admin">'. $field .': </span><br>';
	            echo "\n\t\t<textarea name=\"extra_fields[$i][$field]\" rows=\"2\" cols=\"60\">$value</textarea>";
	        }
	    }

	    echo "\n\t\t". '<p class="admin">'. _("Keywords") .': <br>';
	    echo "\n\t\t". '<input type="text" name="new_keywords_'. $i .'" size="65" value="'. $oldKeywords .'"></p>';

	    $itemCaptureDate = $gallery->album->getItemCaptureDate($i);
	    $capturedate=strftime($gallery->app->dateTimeString , $itemCaptureDate);

	    echo "\n\t\t". '<p class="admin">'. _("Capture Date") . ': '. $capturedate. '</p><br>';
	}
	echo "\n\t</td>";
	echo "\n</tr>";

	$i++;
	$count++;
    }
} else {
    echo "\n<tr>";
    echo "\n\t<td>". _("NO PHOTOS!") ."\n\t</td>";
    echo "\n</tr>";
}
?>
</table>

<p align="right">
	<input type="submit" name="save" value="<?php echo _("Save and Exit") ?>">
<?php 
if (!isset($last)) {
    echo '<input type="submit" name="next" value="'. sprintf(_("Save and Edit Next %d"),$perPage) .'">';
}

if ($page != 1) {
    echo '<input type="submit" name="prev" value="'. sprintf(_("Save and Edit Previous %d"), $perPage) .'">';
}
?>

<input type="submit" name="cancel" value="<?php echo _("Exit") ?>">
</p>

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
