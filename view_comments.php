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
 *
 * This page Created by Joseph D. Scheve ( chevy@tnatech.com ) for the
 * very pimp application that is Gallery.
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
if (!$gallery->user->isAdmin() && !$gallery->user->isOwnerOfAlbum($gallery->album)) {
	header("Location: albums.php");
	return;
}

if (!$gallery->album->isLoaded()) {
	header("Location: albums.php");
	return;
}

$albumName = $gallery->session->albumName;

if (!$gallery->session->viewedAlbum[$albumName]) {
	$gallery->session->viewedAlbum[$albumName] = 1;
	$gallery->album->incrementClicks();
} 


$bordercolor = $gallery->album->fields["bordercolor"];

$breadCount = 0;
$breadtext = array();
$pAlbum = $gallery->album;
do {
  if (!strcmp($pAlbum->fields["returnto"], "no")) {
    break;
  }
  $pAlbumName = $pAlbum->fields['parentAlbumName'];
  if ($pAlbumName) {
    $pAlbum = new Album();
    $pAlbum->load($pAlbumName);
    $breadtext[$breadCount] = "Album: <a href=\"" . makeGalleryUrl("view_comments.php", array("set_albumName" => $pAlbumName)) .
      "\">" . $pAlbum->fields['title'] . "</a>";
  }
  $breadCount++;
} while ($pAlbumName);

//-- we built the array backwards, so reverse it now ---
for ($i = count($breadtext) - 1; $i >= 0; $i--) {
	$breadcrumb["text"][] = $breadtext[$i];
}
$breadcrumb["bordercolor"] = $bordercolor;
$breadcrumb["top"] = true;
$breadcrumb["bottom"] = true;
?>

<? if (!$GALLERY_EMBEDDED_INSIDE) { ?>
<html> 
<head>
  <title><?= $gallery->app->galleryTitle ?> :: <?= $gallery->album->fields["title"] ?></title>
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
<? } 
includeHtmlWrap("album.header");
$adminText = "<span class=\"admin\">Comments for this Album</span>";
$adminCommands = "<span class=\"admin\">";
$adminCommands .= "<a href=\"" . makeAlbumUrl($gallery->session->albumName) . "\">[return to album]</a>&nbsp;"; 
$adminCommands .= "</span>";
$adminbox["text"] = $adminText;
$adminbox["commands"] = $adminCommands;
$adminbox["bordercolor"] = $bordercolor;
$adminbox["top"] = true;
include ($GALLERY_BASEDIR . "layout/adminbox.inc");
include($GALLERY_BASEDIR . "layout/breadcrumb.inc");
?><br><?
if(strcmp($gallery->album->fields["public_comments"], "yes"))
{
    ?><br><b><span class="error">Sorry This album does not allow public comments.</span><br><br></b><?
}
else
{
    $numPhotos = $gallery->album->numPhotos(1);
    $commentbox["bordercolor"] = $bordercolor;
    $i = 1;
    while($i <= $numPhotos)
    {
	set_time_limit($gallery->app->timeLimit);
        $id = $gallery->album->getPhotoId($i);
        $index = $gallery->album->getPhotoIndex($id);
        if($gallery->album->isAlbumName($i))
        {
            $embeddedAlbum = 1;
            $myAlbumName = $gallery->album->isAlbumName($i);
            $myAlbum = new Album();
            $myAlbum->load($myAlbumName);
            $myHighlightTag = $myAlbum->getHighlightAsThumbnailTag();
            include($GALLERY_BASEDIR . "layout/commentboxtop.inc");
            include($GALLERY_BASEDIR . "layout/commentboxbottom.inc");
        }
        else
        {
            $comments = $gallery->album->numComments($i);
            if($comments > 0)
            {
                include($GALLERY_BASEDIR . "layout/commentboxtop.inc");
                for($j = 1; $j <= $comments; $j++)
                {
                    $comment = $gallery->album->getComment($index, $j);
                    include($GALLERY_BASEDIR . "layout/commentbox.inc");
                }
                include($GALLERY_BASEDIR . "layout/commentboxbottom.inc");
            }
        }
        $embeddedAlbum = 0;
        $i = getNextPhoto($i);
    }
}
$breadcrumb["top"] = true;
$breadcrumb["bottom"] = true;
include($GALLERY_BASEDIR . "layout/breadcrumb.inc");

includeHtmlWrap("album.footer");
?>

<? if (!$GALLERY_EMBEDDED_INSIDE) { ?>
</body>
</html>
<? } ?>
