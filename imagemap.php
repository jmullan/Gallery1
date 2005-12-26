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
/**
 * @package Item
 */

require_once(dirname(__FILE__) . '/init.php');

list($full, $index, $imageareas, $formaction) = getRequestVar(array('full', 'index', 'imageareas', 'formaction'));

// Hack check and prevent errors
if (! $gallery->user->canChangeTextOfAlbum($gallery->album)) {
    header("Location: " . makeAlbumHeaderUrl());
    return;
}

if ($index > $gallery->album->numPhotos(1)) {
    $index = 1;
}
$id = $gallery->album->getPhotoId($index);

// Determine if user has the rights to view full-sized images
if (!empty($full) && !$gallery->user->canViewFullImages($gallery->album)) {
    header("Location: " . makeAlbumHeaderUrl($gallery->session->albumName, $id));
    return;
} elseif (!$gallery->album->isResized($index) && !$gallery->user->canViewFullImages($gallery->album)) {
    header("Location: " . makeAlbumHeaderUrl($gallery->session->albumName));
    return;
}

if (!isset($full) || (isset($full) && !$gallery->album->isResized($index))) {
    $full = NULL;
}

switch($formaction) {
    case 'delete':
        if(!empty($imageareas)) {
            foreach($imageareas as $nr) {
                $gallery->album->deleteImageArea($index, $nr);
            }
            $gallery->album->save();
        }
    break;
    case 'create':
        list($xvals, $yvals, $url, $text) = getRequestVar(array('xvals', 'yvals', 'areaurl', 'areatext'));
        if (isset($xvals) && isset($yvals)) {
            $xcoords = explode(',', $xvals);
            $ycoords = explode(',', $yvals);
    
            if (!empty($xcoords)) {
                $coords = $xcoords[0] .',' . $ycoords[0];
                for ($i = 1 ; $i < sizeof($xcoords); $i++) {
                    $coords .= ','. $xcoords[$i] .',' . $ycoords[$i];
                }
                $gallery->album->addImageArea($index, array(
                    'coords'   => $coords,
                    'x_coords' => $xvals,
                    'y_coords' => $yvals,
                    'url'      => $url,
                    'hover_text' => $text)
                );
                $gallery->album->save();
            }
        }
    break;
    case 'update':
        list($url, $text) = getRequestVar(array('areaurl', 'areatext'));
        foreach($imageareas as $area_index) {
            $gallery->album->updateImageArea($index, $area_index, array(
                'url'      => $url,
                'hover_text' => $text)
            );
        }
        $gallery->album->save();
    break;
    default:
    break;
}

$photo = $gallery->album->getPhoto($index);
$image = $photo->image;

$photoURL = $gallery->album->getAlbumDirURL("full") . "/" . $image->name . "." . $image->type;
list($imageWidth, $imageHeight) = $image->getRawDimensions();

if (!$GALLERY_EMBEDDED_INSIDE) {
	doctype(); ?>
<html> 
<head>
  <title><?php echo $gallery->app->galleryTitle; ?> :: ImageMaps :: </title>
  <?php
common_header();
?>
</head>
<body dir="<?php echo $gallery->direction ?>">
<?php
} // End if ! embedded

includeHtmlWrap("photo.header");

?>
  <script language="JavaScript" type="text/javascript" src="<?php echo $gallery->app->photoAlbumURL .'/js/wz_jsgraphics.js'; ?>"></script>
  <script language="JavaScript" type="text/javascript" src="<?php echo $gallery->app->photoAlbumURL .'/js/imagemap.js'; ?>"></script>
  <script type="text/javascript">
      init_mousemove();
 </script>
 
<?php

$rows = $gallery->album->fields["rows"];
$cols = $gallery->album->fields["cols"];
$perPage = $rows * $cols;
$page = (int)(ceil($index / ($rows * $cols)));

$iconElements = array();
if (!$GALLERY_EMBEDDED_INSIDE && !$gallery->session->offline) {
    if ($gallery->user->isLoggedIn()) {
        $iconText = getIconText('exit.gif', _("logout"));
        $iconElements[] = '<a href="'.
          doCommand("logout", array(), "view_album.php", array("page" => $page)) .
          '">'. $iconText .'</a>';
    } else {
        $iconText = getIconText('identity.gif', _("login"));
        $iconElements[] = popup_link($iconText, "login.php", false);
    }
}

$navigator["id"] = $id;
$navigator["allIds"] = $gallery->album->getIds($gallery->user->canWriteToAlbum($gallery->album));
$navigator["fullWidth"] = "100";
$navigator["widthUnits"] = "%";
$navigator["url"] = ".";

#-- breadcrumb text ---
$upArrowURL = gImage('nav_home.gif', _("navigate UP"));

foreach ($gallery->album->getParentAlbums(true) as $navAlbum) {
    $breadcrumb["text"][] = $navAlbum['prefixText'] .': '.
      galleryLink($navAlbum['url'], $navAlbum['title'] .'&nbsp;'. $upArrowURL, array('class' => 'bread'));
}

$breadcrumb["text"][] = galleryLink(
	makeAlbumUrl($gallery->session->albumName, $id),
	  _("Original photo") .'&nbsp;'. $upArrowURL,
	  array('class' => 'bread')
	);

$adminbox["commands"] = makeIconMenu($iconElements, 'right');

includeLayout('navtablebegin.inc');
includeLayout('adminbox.inc');
includeLayout('navtablemiddle.inc');

$breadcrumb["bordercolor"] = $gallery->album->fields["bordercolor"];
includeLayout('breadcrumb.inc');
includeLayout('navtableend.inc');

echo "</td></tr>\n";
echo "\n<!-- End Header Part -->";

echo "\n<!-- Real Content -->";
echo "\n<tr><td>\n\t";

list($width, $height) = $photo->getDimensions($full);

//print_r($photo);

echo showImageMap($index);

$allImageAreas = $gallery->album->getAllImageAreas($index);

if (!empty($allImageAreas)) {
    echo "\n". '<script type="text/javascript">';
    echo "\n\tvar map = new Array();";
    foreach($gallery->album->getAllImageAreas($index) as $nr => $area) {
        echo "\n\t map[$nr] = new Array();";
        echo "\n\t map[$nr]['x_coords'] = new Array(". $area['x_coords'] .');';
        echo "\n\t map[$nr]['y_coords'] = new Array(". $area['y_coords'] .');';
        echo "\n\t map[$nr]['url'] = '". $area['url'] ."';";
        echo "\n\t map[$nr]['hover_text'] = '". $area['hover_text'] ."';";
    }

    echo "\n</script>";

    $photoTag = $gallery->album->getPhotoTag($index, $full,"id=\"myPic\" usemap=\"myMap\"");
}
else {
    $photoTag = $gallery->album->getPhotoTag($index, $full,"id=\"myPic\"");
}
?>

<div class="popup" style="text-align: <?php echo langLeft(); ?>">
<?php
echo _("Here you can create, edit or delete imagemaps for the selected photo.");
echo "\n<br>";
echo _("Click the questionmark icon for helpful instructions.");
echo popup_link('<img src="'. getImagePath('icons/help.gif') .'">', 'help/imagemap.php');
?>
</div>

<?php 
echo makeFormIntro('imagemap.php',
    array('name' => 'areas'),
    array('index' => $index, 'formaction' => '')
    );
?>
<table width="100%" border="0">
<tr>
  <td width="300" style="vertical-align: top;">
    <?php $type = (isDebugging()) ? 'text':'hidden'; ?>
	<input type="<?php echo $type; ?>" name="ausg" id="current_position">
	<input type="<?php echo $type; ?>" name="xvals">
	<input type="<?php echo $type; ?>" name="yvals">
	<br>
	<input type="button" onClick="resetAndClear();" value="<?php echo _("Clear and reset canvas"); ?>">
	<hr>
	<?php echo gTranslate('core', "Optional link-url"); ?><br>
	<input type="text" size="50" name="areaurl" id="areaurl"><br>
	<?php echo gTranslate('core', "Description"); ?><br>
	<textarea name="areatext" id="areatext" cols="40" rows="5"></textarea>
	<input type="submit" value="<?php echo _("Save Imagemap") ?>" onclick="document.areas.formaction.value='create'">
    <hr>
<?php
//print_r($photo);
if (!empty($allImageAreas)) {
    $selectSize = (sizeof($allImageAreas) > 10) ? 10:sizeof($allImageAreas);

    echo _("Select entries to show image area in your photo.");
    echo "<br><select id=\"imageareas\" name=\"imageareas[]\" size=\"$selectSize\" multiple onChange=\"updatePictureAndArea()\">";
    foreach($gallery->album->getAllImageAreas($index) as $nr => $coords) {
        echo "\n<option value=\"$nr\">Map $nr</option>";
    }
    echo "\n</select>";

    echo "\n<hr>";
    echo "<input type=\"submit\" value=\"". _("Delete selected ImageMap(s)") ."\" onclick=\"document.areas.formaction.value='delete'\">";

    echo "\n<hr>";
    echo "<input type=\"submit\" value=\"". _("Update selected ImageMap(s)") ."\" onclick=\"document.areas.formaction.value='update'\">";

    echo '<div class="attention">'. gTranslate('core', "Be aware, that the text of ALL selected entries will be updated!") .'</div>';
}
else {
    echo _("No ImageMaps");
}
?>
  </td>
  <td>
    <div id="myCanvas" style="border: 1px dashed red; width:<?php echo $image->width; ?>px; height:<?php echo $image->height; ?>px">
      <?php echo $photoTag; ?>
    </div>
  </td>
</tr>
</table>
</form>

  </td>
</tr>
<!-- End Real Content -->
<!-- Start Footer Part -->
<tr>
  <td>
<?php 

includeLayout('navtablebegin.inc');
includeLayout('breadcrumb.inc');
includeLayout('navtableend.inc');
echo languageSelector();

includeHtmlWrap("photo.footer");

if (!empty($allImageAreas)) {
    echo '<script language="JavaScript" type="text/javascript" src="'. $gallery->app->photoAlbumURL .'/js/wz_tooltip.js"></script>';
}
?>    
    <script type="text/javascript">
    <!--
      initPaintArea ();
    //-->
    </script>
<?php
if (!$GALLERY_EMBEDDED_INSIDE) { ?>
</body>
</html>
<?php }
?>