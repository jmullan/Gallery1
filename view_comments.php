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
if (!$gallery->user->canReadAlbum($gallery->album)) {
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
$breadcrumb["bordercolor"] = $bordercolor;
?>

<? if (!$GALLERY_EMBEDDED_INSIDE) { ?>
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
$adminCommands = "<span class=\"admin\"";
if (!$GALLERY_EMBEDDED_INSIDE) {
	if ($gallery->user->isLoggedIn()) {
	        $adminCommands .= "<a href=" .
					//doCommand("logout", array(), "view_comments.php", array("page" => $page)) .
					doCommand("logout", array(), "view_comments.php") .
				  ">[logout]</a>";
	} else {
		$adminCommands .= '<a href="#" onClick="'.popup("login.php").'">[login]</a>';
	} 
}
$adminCommands .= "</span>";
$adminbox["text"] = $adminText;
$adminbox["commands"] = $adminCommands;
$adminbox["bordercolor"] = $bordercolor;
$adminbox["top"] = true;
include ($GALLERY_BASEDIR . "layout/adminbox.inc");
?>
<table width=100% border="0" cellspacing="0" cellpadding=0>
<tr>
<td colspan="6" bgcolor="black"><img src="<?= $GALLERY_BASEDIR ?>/images/pizel_trans.gif" width="1" height="1"></td>
</tr>
</table><?
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

includeHtmlWrap("album.footer");
?>

<? if (!$GALLERY_EMBEDDED_INSIDE) { ?>
</body>
</html>
<? } ?>
