<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2004 Bharat Mediratta
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
 *
 * $Id$
 */
?>
<?php

require(dirname(__FILE__) . '/init.php');

// Hack check
if (!$gallery->user->isAdmin() && !$gallery->user->isOwnerOfAlbum($gallery->album)
	&& (! isset($gallery->app->comments_overview_for_all) || $gallery->app->comments_overview_for_all != "yes")) {
	header("Location: " . makeAlbumHeaderUrl());
	return;
}

if (!$gallery->album->isLoaded()) {
	header("Location: " . makeAlbumHeaderUrl());
	return;
}

$albumName = $gallery->session->albumName;

if (empty($gallery->session->viewedAlbum[$albumName]) && 
	!$gallery->session->offline) {
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
  if ($pAlbumName && (!$gallery->session->offline
      || $gallery->session->offlineAlbums[$pAlbumName])) {
    $pAlbum = new Album();
    $pAlbum->load($pAlbumName);
    $breadtext[$breadCount] = _("Album") .": <a class=\"bread\" href=\"" . makeGalleryUrl("view_comments.php", array("set_albumName" => $pAlbumName)) .
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

if (!$GALLERY_EMBEDDED_INSIDE) { ?>
<html> 
<head>
  <title><?php echo $gallery->app->galleryTitle ?> :: <?php echo $gallery->album->fields["title"] ?></title>
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

<body dir="<?php echo $gallery->direction ?>">
<span class="popup">
<?php } 

includeHtmlWrap("album.header");
$adminText = "<span class=\"admin\">". _("Comments for this Album") ."</span>";
$adminCommands = "<span class=\"admin\">";
$adminCommands .= "<a class=\"admin\" href=\"" . makeAlbumUrl($gallery->session->albumName) . "\">[". _("return to album") ."]</a>";
$adminCommands .= "</span>";
$adminbox["text"] = $adminText;
$adminbox["commands"] = $adminCommands;
$adminbox["bordercolor"] = $bordercolor;
$adminbox["top"] = true;
includeLayout('navtablebegin.inc');
includeLayout('adminbox.inc');
includeLayout('navtablemiddle.inc');
includeLayout('breadcrumb.inc');
includeLayout('navtableend.inc');
?><br><?php

if (!$gallery->album->fields["perms"]['canAddComments']) {
    ?></span><br><b><span class="error"><?php echo _("Sorry.  This album does not allow comments.") ?></span><span class="popup"><br><br></b><?php
} else {
    $numPhotos = $gallery->album->numPhotos(1);
    $commentbox["bordercolor"] = $bordercolor;
    $i = 1;
    while($i <= $numPhotos)
    {
	set_time_limit($gallery->app->timeLimit);
        $id = $gallery->album->getPhotoId($i);
        $index = $gallery->album->getPhotoIndex($id);
        if ($gallery->album->isAlbum($i)) {
		$myAlbumName = $gallery->album->getAlbumName($i);
		$myAlbum = new Album();
		$myAlbum->load($myAlbumName);
		if (((!$gallery->album->isHidden($i) && $gallery->user->canReadAlbum($myAlbum)) || $gallery->user->isAdmin() || 
			$gallery->user->isOwnerOfAlbum($gallery->album) || $gallery->user->isOwnerOfAlbum($myAlbum)))
		{
			$embeddedAlbum = 1;
			$myHighlightTag = $myAlbum->getHighlightTag();
			includeLayout('commentboxtop.inc');
			includeLayout('commentboxbottom.inc');
	        }
	}
        elseif (!$gallery->album->isHidden($i) || $gallery->user->isAdmin() ||  
		$gallery->user->isOwnerOfAlbum($gallery->album) || $gallery->album->isItemOwner($i))
        {
            $comments = $gallery->album->numComments($i);
            if($comments > 0)
            {
		includeLayout('commentboxtop.inc');
                for($j = 1; $j <= $comments; $j++)
                {
                    $comment = $gallery->album->getComment($index, $j);
		    includeLayout('commentbox.inc');
                }
		includeLayout('commentboxbottom.inc');
            }
        }
        $embeddedAlbum = 0;
        $i = getNextPhoto($i);
    }
}
$breadcrumb["top"] = true;
$breadcrumb["bottom"] = true;
includeLayout('navtablebegin.inc');
includeLayout('breadcrumb.inc');
includeLayout('navtableend.inc');

includeLayout('ml_pulldown.inc');
includeHtmlWrap("album.footer");
?>

<?php if (!$GALLERY_EMBEDDED_INSIDE) { ?>

</span>
</body>
</html>
<?php } ?>
