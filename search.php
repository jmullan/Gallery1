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
<? require_once('init.php'); ?>

<html>
<head>
  <title>Search Results</title>
  <?= getStyleSheetLink() ?>
</head>
<body>

<!-- search.header begin -->
<? 
includeHtmlWrap("search.header");
?>
<br><br>
<!-- search.header ends -->
<!-- Top Nav -->
<?
$breadtext[0] = "Gallery: <a href=albums.php>".$gallery->app->galleryTitle."</a>";
$breadcrumb["text"] = $breadtext;
$breadcrumb["bordercolor"] = #FFFFFF;
$breadcrumb["top"] = true;
$breadcrumb["bottom"] = true;
include("layout/breadcrumb.inc");
?>
<br>
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
	for ($i = 0; $i<$numAlbums; $i++) {
       		$searchAlbum = $list[$i];
		$searchTitle = $searchAlbum->fields['title'];
		$searchDescription = $searchAlbum->fields['description'];
        	if (eregi($searchstring, $searchTitle) || eregi($searchstring, $searchDescription)) {
			$uid = $gallery->user->getUid();
			if ($searchAlbum->canRead($uid)) {
				if (!$albumMatch) {
					$searchdraw["Title"] = "Matching Albums";
				} else {
					$searchdraw["Title"] = "";
				}
                		$albumMatch = 1;
                		$searchTitle = eregi_replace($searchstring, "<b>$searchstring</b>",$searchTitle);  // cause search word to be bolded
				$searchDescription = eregi_replace($searchstring, "<b>$searchstring</b>",$searchDescription);  // cause search word to be bolded
				$photoURL = makeGalleryUrl($searchAlbum->fields['name']);
				$searchdraw["bordercolor"] = #FFFFFF;
				$searchdraw["top"] = true;
				$searchdraw["photolink"] = $searchAlbum->getHighlightTag();
				$searchdraw["photoURL"] = $photoURL;
				$searchdraw["Text1"] = "<span class=title>Album:&nbsp&nbsp<a href=$photoURL>$searchTitle</a></span>";
				$searchdraw["Text2"] = "<span class=desc><b>Description:</b>&nbsp&nbsp$searchDescription</span>";
				include("layout/searchdraw.inc");
        		}
		}
	}
	if ($albumMatch) {
		echo "</table><br>";
	}
	
	for ($i = 0; $i<$numAlbums; $i++) {
		$searchAlbum = $list[$i]; 
		$uid = $gallery->user->getUid();
		if ($searchAlbum->canRead($uid)) {
			$numPhotos = $searchAlbum->numPhotos($gallery->user->canWriteToAlbum($searchAlbum));
			for ($j = 1; $j <= $numPhotos; $j++) {
				$searchCaption = $searchAlbum->getCaption($j);
				if (eregi($searchstring, $searchCaption)) {
					if (!$searchAlbum->isHidden($j) || 
				    	$searchAlbum->isOwner($uid) || 
			    	    	$gallery->user->isAdmin()) {
						if (!$photoMatch) {
							$searchdraw["Title"] = "Matching Photos";
						} else {
							$searchdraw["Title"] = "";
						}
						$photoMatch = 1;
						$id = $searchAlbum->getPhotoId($j);
						$searchCaption = eregi_replace($searchstring, "<b>$searchstring</b>",$searchCaption);  // cause search word to be bolded
						$searchdraw["bordercolor"] = #FFFFFF;
						$searchdraw["top"] = true;
						$searchdraw["photolink"] = $searchAlbum->getThumbnailTag($j);
						$searchdraw["photoURL"] = makeGalleryUrl($searchAlbum->fields['name'], $id);
						$searchdraw["Text1"] = "<span class=desc><b>Caption:</b>&nbsp&nbsp$searchCaption";
						$searchdraw["Text2"] = "From Album:&nbsp&nbsp<span class=title><a href=" .
                                			makeGalleryUrl($searchAlbum->fields['name']) . ">" .
                                			$searchAlbum->fields['title'] . "</a></span>";
						include("layout/searchdraw.inc");
					}
				}
			}
		}
	}
	if ($photoMatch) echo "</table>";
	
	if (!$photoMatch && !$albumMatch) {
		echo "No Matches!<br>";	
	}
}
else {
	echo "No search string specified<br>";
}
echo "<br>";
$breadtext[0] = "Gallery: <a href=albums.php>".$gallery->app->galleryTitle."</a>";
$breadcrumb["text"] = $breadtext;
$breadcrumb["bordercolor"] = #FFFFFF;
$breadcrumb["top"] = true;
$breadcrumb["bottom"] = true;
include("layout/breadcrumb.inc");
?>
</body>
</html>

