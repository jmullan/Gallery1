<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000 Bharat Mediratta
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
if (!$gallery->user->canChangeTextOfAlbum($gallery->album)) {
    header("Location: albums.php");
    return;
}


if (!$page) {
    $page = 1;
}

$numPhotos = $gallery->album->numPhotos($gallery->user->canWriteToAlbum($gallery->album));

if (!$perPage) {
    $perPage = 5;
}

#-- save the captions from the previous page ---
if ($save || $next || $prev) {

    $i = 0;
    $start = ($page - 1) * $perPage + 1;
    while ($i < $start) {
        $i++;
    }
   
    $count = 0;
    while ($count < $perPage && $i <= $numPhotos) {
      if ($gallery->album->isAlbumName($i)) {
        $myAlbumName = $gallery->album->isAlbumName($i);
        $myAlbum = new Album();
        $myAlbum->load($myAlbumName);
        $myAlbum->fields['description'] = stripslashes(${"new_captions_" . $i});
	$myAlbum->save();

      } else {
        $gallery->album->setCaption($i, stripslashes(${"new_captions_" . $i}));
        $gallery->album->setKeywords($i, stripslashes(${"new_keywords_" . $i}));
      }

      $i++;
      $count++;
    }

    $gallery->album->save();

}

if ($cancel || $save) {
    header("Location: " . makeGalleryUrl("view_album.php"));
    return;
}

#-- did they hit next? ---
if ($next) {
    $page++;
} else if ($prev) {
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
$imageDir = $gallery->app->photoAlbumURL."/images";
$pixelImage = "<img src=\"$imageDir/pixel_trans.gif\" width=\"1\" height=\"1\">";

$bordercolor = $gallery->album->fields["bordercolor"];
?>

<?php if (!$GALLERY_EMBEDDED_INSIDE) { ?>
<html> 
<head>
  <title><?php echo $gallery->app->galleryTitle ?> :: <?php echo $gallery->album->fields["title"] ?> :: Captionator</title>
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

<?php 
includeHtmlWrap("album.header");

#-- if borders are off, just make them the bgcolor ----
$pixelImage = "<img src=\"$imageDir/pixel_trans.gif\" width=\"1\" height=\"1\">";
$borderwidth = $gallery->album->fields["border"];
if (!strcmp($borderwidth, "off")) {
    $bordercolor = $gallery->album->fields["bgcolor"];
    $borderwidth = 1;
} else {
    $bordercolor = "black";
}

$adminText = "<span class=\"admin\">Multiple Caption Editor. ";
if ($numPhotos == 1) {  
    $adminText .= "1 photo in this album";
} else {
    $adminText .= "$numPhotos items in this album";
    if ($maxPages > 1) {
        $adminText .= " on " . pluralize($maxPages, "page");
    }
}

$adminText .="</span>";
$adminCommands = "";
$adminbox["text"] = $adminText;
$adminbox["commands"] = $adminCommands;
$adminbox["bordercolor"] = $bordercolor;
$adminbox["top"] = true;
include ($GALLERY_BASEDIR . "layout/adminbox.inc");

$adminbox["text"] = "";
$adminbox["commands"] = "";
$adminbox["bordercolor"] = $bordercolor;
$adminbox["top"] = false;
include ($GALLERY_BASEDIR . "layout/adminbox.inc");

?>


<!-- image grid table -->
<br>
<?php echo makeFormIntro("captionator.php", array("method" => "POST")) ?>
<input type=hidden name=page value=<?php echo $page ?>>
<input type=hidden name=perPage value=<?php echo $perPage ?>>
<table width=100% border=0 cellspacing=4 cellpadding=0>
<tr>
<td colspan="3" align="right">
<input type=submit name="save" value="Save and Exit">

<?php if (!$last) { ?>
    <input type=submit name="next" value="Save and Edit Next <?php echo $perPage ?>">
<?php } ?>

<?php if ($page != 1) { ?>
    <input type=submit name="prev" value="Save and Edit Previous <?php echo $perPage ?>">
<?php } ?>

<input type=submit name="cancel" value="Exit">
</td>
</tr>
<?php
if ($numPhotos) {


    // Find the correct starting point, accounting for hidden photos
    $i = 0;
    while ($i < $start) {
        $i++;
    }

    $count = 0;
    while ($count < $perPage && $i <= $numPhotos) {


?>    
    <tr>
      <td height="1"><?php echo $pixelImage?></td>
      <td height="1"><?php echo $pixelImage?></td>
      <td bgcolor="<?php echo $bordercolor?>" height="1"><?php echo $pixelImage?></td>
    </tr>
    <tr>
      <td width=<?php echo $thumbSize ?> align=center valign="top">
      <span class="admin">&nbsp;</span><br>
      <?php echo $gallery->album->getThumbnailTag($i, $thumbSize); ?>
      </td width=10>
      <td height=1>
      <?php echo $pixelImage ?>
      </td>

      <td valign=top>
<?php
    if ($gallery->album->isAlbumName($i)) {
        $myAlbumName = $gallery->album->isAlbumName($i);
        $myAlbum = new Album();
        $myAlbum->load($myAlbumName);
        $oldCaption = $myAlbum->fields['description'];
?>
      <span class="admin">Album Caption:</span><br>
      <textarea name="new_captions_<?php echo $i?>" rows=3 cols=60><?php echo $oldCaption ?></textarea><br>

<?php
    } else {
        $oldCaption = $gallery->album->getCaption($i);
        $oldKeywords = $gallery->album->getKeywords($i);
?>
      <span class="admin">Caption:</span><br>
      <textarea name="new_captions_<?php echo $i?>" rows=3 cols=60><?php echo $oldCaption ?></textarea><br>
      <span class="admin">Keywords:</span><br>
      <input type=text name="new_keywords_<?php echo $i?>" size=65 value="<?php echo $oldKeywords ?>">

<?php
    }
?>
      </td>
    </tr>
<?php
        $i++;
        $count++;
    }
} else {
    echo("<tr>");
    echo("  <td>");
    echo("  NO PHOTOS!");
    echo("  </td>");
    echo("</tr>");
}
?>

<tr>
<td colspan=3 align="right">
<input type=submit name="save" value="Save and Exit">

<?php if (!$last) { ?>
    <input type=submit name="next" value="Save and Edit Next <?php echo $perPage ?>">
<?php } ?>

<?php if ($page != 1) { ?>
    <input type=submit name="prev" value="Save and Edit Previous <?php echo $perPage ?>">
<?php } ?>

<input type=submit name="cancel" value="Exit">
</td>
</tr>
</table>
</form>

<br>

<?php
includeHtmlWrap("album.footer");
?>

<?php if (!$GALLERY_EMBEDDED_INSIDE) { ?>
</body>
</html>
<?php } ?>
