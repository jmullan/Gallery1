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
 * $Id$
 */
?>
<?php

require(dirname(__FILE__) . '/init.php');

$borderColor = $gallery->app->default["bordercolor"];
$thumbSize = $gallery->app->default["thumb_size"];

if ($gallery->app->showSearchEngine == 'no' && !$gallery->user->isAdmin()) {
	header('Location: ' . makeAlbumURL());
	return;
}

if (!$GALLERY_EMBEDDED_INSIDE) { 
	doctype();
?>
<html>
<head>
  <title><?php echo $gallery->app->galleryTitle ?> :: <?php echo _("Search") ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>">
<?php }

includeHtmlWrap("search.header");

if (!isset($searchstring)) {
	$searchstring="";
}
$searchstring = removeTags($searchstring);
if ($searchstring) {
	echo makeFormIntro("search.php");
?>
<table width="100%" border="0" cellspacing="0">
<tr>
	<td valign="middle" align="right"><span class="admin"><?php echo _("Search Again") ?>: </span>
		<input style="font-size:10px;" type="text" name="searchstring" value="<?php echo $searchstring ?>" size="25">
	</td>
</tr>
</table>
</form>    
<?php
}
?>
<!-- search.header ends -->
<!-- Top Nav -->
<?php

$breadtext[0] = _("Gallery") .': <a class="bread" href="'. makeGalleryUrl("albums.php") . '">'.$gallery->app->galleryTitle .'</a>';
$breadcrumb["text"] = $breadtext;
$breadcrumb["bordercolor"] = $borderColor;
$breadcrumb["top"] = true;
$breadcrumb["bottom"] = true;
includeLayout('breadcrumb.inc');
?>
<!-- end Top Nav -->
<?php

$navigator["fullWidth"] = 100;
$navigator["widthUnits"] = "%";

$albumDB = new AlbumDB();
$list = $albumDB->albumList;
$numAlbums = count($list);
$photoMatch = 0;
$albumMatch = 0;
$skip = array();
if ($searchstring) {
	$origstr = $searchstring;
	$searchstring = escapeEregChars ($searchstring);
	$searchstring = str_replace ("\\*", ".*", $searchstring);

	$adminbox["text"] = '<span class="admin">'. sprintf(_("Albums containing %s"), "\"$origstr\"") . '</span>';
	$adminbox["bordercolor"] = $borderColor; 
	$adminbox["top"] = false;
	includeLayout('adminbox.inc');
	echo '<br>';
	echo '<table width="'. $navigator['fullWidth'] . $navigator['widthUnits'] .'" border="0" cellspacing="0" cellpadding="0">';
	for ($i = 0; $i<$numAlbums; $i++) {
		// initialize values
		$searchdraw["bordercolor"]="";
	       	$searchdraw["photoURL"]="";
	       	$searchdraw["photolink"]="";
	       	$searchdraw["Text1"]="";
	       	$searchdraw["Text2"]="";
	       	$searchdraw["Text3"]="";
	       	$searchdraw["Text4"]="";
	       	$searchdraw["Text5"]="";

		$searchAlbum = $list[$i];
		if ($searchAlbum->versionOutOfDate()) {
			$skip[] = $searchAlbum;
			continue;
		}
		$searchTitle = $searchAlbum->fields['title'];
		$searchDescription = $searchAlbum->fields['description'];
		$searchSummary = $searchAlbum->fields['summary'];
       		$matchTitle = eregi("$searchstring", $searchTitle);
		$matchDescription = eregi("$searchstring", $searchDescription);
		$matchSummary = eregi("$searchstring", $searchSummary);
       		if ($matchTitle || $matchDescription || $matchSummary) {
			$uid = $gallery->user->getUid();
			if ($searchAlbum->canReadRecurse($uid) || $gallery->user->isAdmin()) {
				if (!$gallery->user->isAdmin() && $searchAlbum->isHiddenRecurse()) {
					// One of the parents of this album is hidden - do not show it to users
					continue;
				}
           		$albumMatch = 1;
			$searchTitle = eregi_replace("($searchstring)", "<b>\\1</b>", $searchTitle); // cause search word to be bolded
			$searchDescription = eregi_replace("($searchstring)", "<b>\\1</b>", $searchDescription); // cause search word to be bolded
			$searchSummary = eregi_replace("($searchstring)", "<b>\\1</b>", $searchSummary); // cause search word to be bolded
			$photoURL = makeAlbumUrl($searchAlbum->fields['name']);
			$searchdraw["bordercolor"] = $borderColor;
			$searchdraw["top"] = true;
			$searchdraw["photolink"] = $searchAlbum->getHighlightTag($thumbSize);
			$searchdraw["photoURL"] = $photoURL;
			$searchdraw["Text1"] = '<span class="title"><a href="'. $photoURL .'">'. $searchTitle .'</a></span>';
			$searchdraw["Text2"] = '<span class="desc">'. $searchDescription . '</span>';
			if ($matchSummary)  { // only print summary if it matches
				$searchdraw["Text3"] = '<span class="desc">'. $searchSummary .'</span>';
			}
			includeLayout('searchdraw.inc');
			}
		}
	
	}
	if (!$albumMatch) {
		echo "<tr><td valign=top><span class=desc>". _("No Album Matches") .".</span></td></tr>";
	}
	echo "</table><br>";

	$breadtext[0] = "";
	$breadcrumb["text"] = $breadtext;
	includeLayout('breadcrumb.inc');
	$adminbox["text"] = '<span class="admin">'. sprintf(_("Photos containing %s"), "\"$origstr\"") .'</span>';
   	$adminbox["bordercolor"] = $borderColor; 
	$adminbox["top"] = false;
	includeLayout('adminbox.inc');
	echo '<br>';
	echo '<table width="'. $navigator['fullWidth'] . $navigator['widthUnits'] .'" border="0" cellspacing="0" cellpadding="0">';
	
	for ($i = 0; $i<$numAlbums; $i++) {
		$searchAlbum = $list[$i]; 
		if ($searchAlbum->versionOutOfDate()) {
			continue;
		}
		$uid = $gallery->user->getUid();
		if ($searchAlbum->canReadRecurse($uid) || $gallery->user->isAdmin()) {
			$numPhotos = $searchAlbum->numPhotos(1);
			for ($j = 1; $j <= $numPhotos; $j++) {
				if ($searchAlbum->isHidden($j)) {
					continue;
				}
				$searchCaption = _("Caption:") . $searchAlbum->getCaption($j);
				$searchCaption .= $searchAlbum->getCaptionName($j);
				$searchKeywords = $searchAlbum->getKeywords($j);
				$commentMatch = 0;
				$commentText = "";
				if ($searchAlbum->canViewComments($uid) ||  $gallery->user->isAdmin()) {
					for ($k = 1; $k <= $searchAlbum->numComments($j); $k++) {
						// check to see if there are any comment matches
						$comment = $searchAlbum->getComment($j, $k);
						$searchComment = $comment->getName();
						if ($gallery->user->isAdmin()) {
							$searchComment .= " @ ".$comment->getIPNumber();
						}
						$searchComment .= ": ".$comment->getCommentText();
						if (eregi($searchstring, $searchComment)) {
							if (!$commentMatch) {
								$commentText = _("Matching Comments").":<br>";
								$commentMatch = 1;
							} 
							$searchComment = eregi_replace("($searchstring)", "<b>\\1</b>", $searchComment);
							$commentText .= $searchComment . "<br><br>";
						}
					}
				}
				$extraFieldsText = "";
				$extraFieldsMatch = 0;
				foreach ($searchAlbum->getExtraFields() as $field)
				{
					$fieldValue=$searchAlbum->getExtraField($j, $field);
					if (eregi($searchstring, $fieldValue)) {
						$fieldValue = eregi_replace("($searchstring)", "<b>\\1</b>", $fieldValue);
						$extraFieldsText .= "<b>$field:</b> $fieldValue<br><br>";
						$extraFieldsMatch = 1;
					}
				}
				$captionMatch = eregi($searchstring, $searchCaption);
				$keywordMatch = eregi($searchstring, $searchKeywords);
				if ($captionMatch || $keywordMatch || $commentMatch || $extraFieldsMatch) {
					if (!$searchAlbum->isHidden($j) || 
				    	$searchAlbum->isOwner($uid) || 
			    	    	$gallery->user->isAdmin()) {
						if ($searchAlbum->isHiddenRecurse()) {
							// One of the parents of this item is hidden do not show it to users
							continue;
						}
						$photoMatch = 1;
						$id = $searchAlbum->getPhotoId($j);
						// cause search word to be bolded
						$searchCaption = eregi_replace("($searchstring)", "<b>\\1</b>", $searchCaption);
						$searchKeywords = eregi_replace("($searchstring)", "<b>\\1</b>", $searchKeywords);
						$searchdraw["bordercolor"] = $borderColor;
						$searchdraw["top"] = true;
						$searchdraw["photolink"] = $searchAlbum->getThumbnailTag($j, $thumbSize);
						$searchdraw["photoURL"] = makeAlbumUrl($searchAlbum->fields['name'], $id);
						$searchdraw["Text1"] = '<div class="desc">'. _("From Album") .":&nbsp;&nbsp;<a href=\"" .
                                			makeAlbumUrl($searchAlbum->fields['name']) . "\">" .
                                			$searchAlbum->fields['title'] . "</a></div>";
						$searchdraw["Text2"] = '<span class="desc">'. $searchCaption .'</span>';
						if ($keywordMatch) { // only display Keywords if there was a keyword match
							$searchdraw["Text3"] = "<span class=fineprint>". _("KEYWORDS") .":&nbsp;&nbsp; $searchKeywords</span><br>";
						} else {
							$searchdraw["Text3"] = "";
						}
						$searchdraw["Text5"] = $commentText;
						$searchdraw["Text4"] = $extraFieldsText;
						includeLayout('searchdraw.inc');
					}
				}
			}
		}
	}
	if (!$photoMatch) {
		echo "<tr><td valign=top><span class=desc>" . _("No Photo Matches") .".</span></td></tr>";
	}
	echo "</table>";
	
	if (sizeof($skip) > 0) {
		echo gallery_error(sprintf(_("Some albums not searched as they require upgrading to the latest version of %s first"),Gallery()));
		if ($gallery->user->isAdmin()) {
			print ":<br>";
			echo popup_link(_("upgrade all albums"), "upgrade_album.php");
			print "<br>(";
			$join_text='';
			foreach($skip as $album) {
				$link = makeGalleryUrl("view_album.php", 
						array("set_albumName" => $album->fields["name"]));
				echo $join_text."<a href=\"$link\">".$album->fields["name"]
					."</a>";
				$join_text=", ";
			}
			print ")";
		}
		else {
			print ".";
		}
		echo "<p>";
	}

}
else {
?>
<br><?php echo _("Search the Gallery's Album and Photo<br> titles, descriptions and comments") ?>:<br>
<?php echo makeFormIntro("search.php"); ?>
	<table width="100%" border="0" cellspacing="0">
	<tr>
		<td valign="middle" align="left">
			<input type="text" name="searchstring" value="<?php echo $searchstring ?>" size="25">
			<input type="submit" name="go" value="<?php echo _("Go") ?>!">
		</td>
	</tr>
	</table>
</form>
<?php
}
echo "<br>";
$breadtext[0] = _("Gallery") .": <a class=\"bread\" href=\"". makeGalleryUrl("albums.php") . "\">".$gallery->app->galleryTitle."</a>";
$breadcrumb["text"] = $breadtext;
$breadcrumb["bordercolor"] = $borderColor;
$breadcrumb["top"] = true;
$breadcrumb["bottom"] = true;
includeLayout('breadcrumb.inc');

includeLayout('ml_pulldown.inc');
includeHtmlWrap("search.footer");

if (!$GALLERY_EMBEDDED_INSIDE) {
?> 
</body>
</html>
<?php } ?>

