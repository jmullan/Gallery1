<?
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
<? require($GALLERY_BASEDIR . "init.php"); ?>
<?
$borderColor = $gallery->app->default["bordercolor"];
$thumbSize = $gallery->app->default["thumb_size"];
?>
<? if (!$GALLERY_EMBEDDED_INSIDE) { ?>
<html>
<head>
  <title><?= $gallery->app->galleryTitle ?> :: Search</title>
  <?= getStyleSheetLink() ?>
</head>
<body>
<? } ?>

<!-- search.header begin -->
<? 
includeHtmlWrap("search.header");
?>
<?
if ($searchstring) {
?>
<table width=100% border=0 cellspacing=0>
<tr>
<?= makeFormIntro("search.php"); ?>
<td valign="middle" align="right">
<span class="admin"> Search Again: </span>
<input style="font-size=10px;" type="text" name="searchstring" value="<?= $searchstring ?>" size="25">
</td>
</form>   
</tr>
<tr><td height=2><img src=<?= $GALLERY_BASEDIR ?>images/pixel_trans.gif></td></tr>
</table> 
<?
}
?>
<!-- search.header ends -->
<!-- Top Nav -->
<?
$breadtext[0] = "Gallery: <a href=". makeGalleryUrl("albums.php") . ">".$gallery->app->galleryTitle."</a>";
$breadcrumb["text"] = $breadtext;
$breadcrumb["bordercolor"] = $borderColor;
$breadcrumb["top"] = true;
$breadcrumb["bottom"] = true;
include($GALLERY_BASEDIR . "layout/breadcrumb.inc");
?>
<!-- end Top Nav -->
<?

$navigator["fullWidth"] = 100;
$navigator["widthUnits"] = "%";

$albumDB = new AlbumDB();
$list = $albumDB->albumList;
$numAlbums = count($list);
$photoMatch = 0;
$albumMatch = 0;
if ($searchstring) {
	$adminbox["text"] = "<span class=\"admin\">Albums containing \"$searchstring\"</span>";
    $adminbox["bordercolor"] = $borderColor; 
	$adminbox["top"] = false;
	include($GALLERY_BASEDIR . "layout/adminbox.inc");
	echo "<br>";
	echo "<table width=\"".$navigator["fullWidth"] . $navigator["widthUnits"]."\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
	for ($i = 0; $i<$numAlbums; $i++) {
    	$searchAlbum = $list[$i];
		$searchTitle = $searchAlbum->fields['title'];
		$searchDescription = $searchAlbum->fields['description'];
       	if (eregi($searchstring, $searchTitle) || eregi($searchstring, $searchDescription)) {
			$uid = $gallery->user->getUid();
			if ($searchAlbum->canRead($uid)) {
           		$albumMatch = 1;
           		$searchTitle = eregi_replace($searchstring, "<b>$searchstring</b>",$searchTitle);  // cause search word to be bolded
				$searchDescription = eregi_replace($searchstring, "<b>$searchstring</b>",$searchDescription);  // cause search word to be bolded
				$photoURL = makeAlbumUrl($searchAlbum->fields['name']);
				$searchdraw["bordercolor"] = $borderColor;
				$searchdraw["top"] = true;
				$searchdraw["photolink"] = $searchAlbum->getHighlightTag($thumbSize);
				$searchdraw["photoURL"] = $photoURL;
				$searchdraw["Text1"] = "<span class=title><a href=$photoURL>$searchTitle</a></span>";
				$searchdraw["Text2"] = "<span class=desc>$searchDescription</span>";
				include($GALLERY_BASEDIR . "layout/searchdraw.inc");
			}
		}
	
	}
	if (!$albumMatch) {
		echo "<tr><td valign=top><span class=desc>No Album Matches.</span></td></tr>";
	}
	echo "</table><br>";

	$breadtext[0] = "";
	$breadcrumb["text"] = $breadtext;
    include($GALLERY_BASEDIR . "layout/breadcrumb.inc");
	$adminbox["text"] = "<span class=\"admin\">Photos containing \"$searchstring\"</span>";
   	$adminbox["bordercolor"] = $borderColor; 
	$adminbox["top"] = false;
	include($GALLERY_BASEDIR . "layout/adminbox.inc");
	echo "<br>";
	echo "<table width=\"".$navigator["fullWidth"] . $navigator["widthUnits"]."\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
	
	for ($i = 0; $i<$numAlbums; $i++) {
		$searchAlbum = $list[$i]; 
		$uid = $gallery->user->getUid();
		if ($searchAlbum->canRead($uid)) {
			$numPhotos = $searchAlbum->numPhotos($gallery->user->canWriteToAlbum($searchAlbum));
			for ($j = 1; $j <= $numPhotos; $j++) {
				$searchCaption = $searchAlbum->getCaption($j);
				$searchKeywords = $searchAlbum->getKeywords($j);
				$commentMatch = 0;
				$commentText = "";
				for ($k = 1; $k <= $searchAlbum->numComments($j); $k++) {
					// check to see if there are any comment matches
					$comment = $searchAlbum->getComment($j, $k);
					$searchComment = $comment->getCommentText();
					if (eregi($searchstring, $searchComment)) {
						if (!$commentMatch) {
							$commentText = "Matching Comments:<br>";
							$commentMatch = 1;
						} 
						$searchComment = eregi_replace($searchstring, "<b>$searchstring</b>",$searchComment);
						$commentText .= $searchComment . "<br><br>";
					}
				}
				$captionMatch = eregi($searchstring, $searchCaption);
				$keywordMatch = eregi($searchstring, $searchKeywords);
				if ($captionMatch || $keywordMatch || $commentMatch) {
					if (!$searchAlbum->isHidden($j) || 
				    	$searchAlbum->isOwner($uid) || 
			    	    	$gallery->user->isAdmin()) {
						$photoMatch = 1;
						$id = $searchAlbum->getPhotoId($j);
						// cause search word to be bolded
						$searchCaption = eregi_replace($searchstring, "<b>$searchstring</b>",$searchCaption);
						$searchKeywords = eregi_replace($searchstring, "<b>$searchstring</b>",$searchKeywords);
						$searchdraw["bordercolor"] = $borderColor;
						$searchdraw["top"] = true;
						$searchdraw["photolink"] = $searchAlbum->getThumbnailTag($j, $thumbSize);
						$searchdraw["photoURL"] = makeAlbumUrl($searchAlbum->fields['name'], $id);
						$searchdraw["Text2"] = "<span class=desc>$searchCaption</span>";
						$searchdraw["Text1"] = "<span class=fineprint>From Album:&nbsp&nbsp<a href=" .
                                			makeAlbumUrl($searchAlbum->fields['name']) . ">" .
                                			$searchAlbum->fields['title'] . "</a></span>";
						if ($keywordMatch) { // only display Keywords if there was a keyword match
							$searchdraw["Text3"] = "<span class=fineprint>KEYWORDS:&nbsp&nbsp $searchKeywords</span><br>";
						} else {
							$searchdraw["Text3"] = "";
						}
						$searchdraw["Text4"] = $commentText;
						include($GALLERY_BASEDIR . "layout/searchdraw.inc");
					}
				}
			}
		}
	}
	if (!$photoMatch) {
		echo "<tr><td valign=top><span class=desc>No Photo Matches.</span></td></tr>";
	}
	echo "</table>";
	
}
else {
?>
<br>Search the Gallery's Album and Photo<br> titles and descriptions:<br>
	<table width=100% border=0 cellspacing=0>
	<tr><?= makeFormIntro("search.php"); ?>
	<td valign="middle" align="left">
	<input type="text" name="searchstring" value="<?= $searchstring ?>" size="25">
	<input type="submit" value="Go!">
	</td>
	</form>  
	</tr>
	</table>
<?
}
echo "<br>";
$breadtext[0] = "Gallery: <a href=". makeGalleryUrl("albums.php") . ">".$gallery->app->galleryTitle."</a>";
$breadcrumb["text"] = $breadtext;
$breadcrumb["bordercolor"] = $borderColor;
$breadcrumb["top"] = true;
$breadcrumb["bottom"] = true;
include($GALLERY_BASEDIR . "layout/breadcrumb.inc");
?>
<? 
includeHtmlWrap("search.footer");
?>
<? if (!$GALLERY_EMBEDDED_INSIDE) { ?>
</body>
</html>
<? } ?>

