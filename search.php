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

require_once(dirname(__FILE__) . '/init.php');

$searchstring = removeTags(getRequestVar('searchstring'));

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

$albumDB = new AlbumDB();
$list = $albumDB->albumList;
$numAlbums = count($list);
$photoMatch = 0;
$albumMatch = 0;
$skip = array();
$text = array();
if ($searchstring) {
	$origstr = $searchstring;
	$searchstring = escapeEregChars($searchstring);
	$searchstring = str_replace ("\\*", ".*", $searchstring);
	$searchExpr = "{(<a [^<>]*{$searchstring}[^<>]*>)(.*(?=</a>))(</a>)|({$searchstring})}Usi";
	$searchRepl = '\1<b>\2\4</b>\3';

	$uid = $gallery->user->getUid();
	for ($i = 0; $i<$numAlbums; $i++) {
		$searchAlbum = $list[$i];

		if (!$gallery->user->isAdmin() && $searchAlbum->isHiddenRecurse()) {
			// One of the parents of this album is hidden - do not show it to users
			continue;
		}
		
		if (!$gallery->user->isAdmin() && !$searchAlbum->canReadRecurse($uid)) {
			// User is not allowed to search through see album
			continue;
		}
		if ($searchAlbum->versionOutOfDate()) {
			$skip[] = $searchAlbum;
			continue;
		}

		// initialize values
		unset($text);

		$searchTitle = $searchAlbum->fields['title'];
		$searchDescription = $searchAlbum->fields['description'];
		$searchSummary = $searchAlbum->fields['summary'];

       		$matchTitle = eregi("$searchstring", $searchTitle);
		$matchDescription = eregi("$searchstring", $searchDescription);
		$matchSummary = eregi("$searchstring", $searchSummary);

       		if ($matchTitle || $matchDescription || $matchSummary) {
			$searchTitle = preg_replace($searchExpr, $searchRepl, $searchTitle); // cause search word to be bolded

			$searchDescription = preg_replace($searchExpr, $searchRepl, $searchDescription); // cause search word to be bolded
			$searchSummary = preg_replace($searchExpr, $searchRepl, $searchSummary); // cause search word to be bolded
			$photoURL = makeAlbumUrl($searchAlbum->fields['name']);

			$text[] = '<div class="desc"><a href="'. $photoURL .'">'. $searchTitle .'</a></div>';
	
			if(!empty($searchDescription)) {
				$text[] = '<div class="desc">'. $searchDescription . '</div>';
			}
			if ($matchSummary)  { // only print summary if it matches
				$text[] = '<div class="desc">'. $searchSummary .'</div>';
			}
	
			$searchResult['albums'][]=array(
				"photolink" 	=> $searchAlbum->getHighlightTag($thumbSize),
				"photoURL"	=> $photoURL,
				"Text"		=> $text
			);
		}

		/* now search for photos .. */

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
						$searchComment = preg_replace($searchExpr, $searchRepl, $searchComment);
						$commentText .= "\n". $searchComment . "<br><br>";
					}
				}
			}

			$extraFieldsText = "";
			$extraFieldsMatch = 0;
			foreach ($searchAlbum->getExtraFields() as $field) {
				$fieldValue=$searchAlbum->getExtraField($j, $field);
				if (eregi($searchstring, $fieldValue)) {
					$fieldValue = preg_replace($searchExpr, $searchRepl, $fieldValue);
					$extraFieldsText .= "<b>$field:</b> $fieldValue<br><br>";
					$extraFieldsMatch = 1;
				}
			}

			$captionMatch = eregi($searchstring, $searchCaption);
			$keywordMatch = eregi($searchstring, $searchKeywords);

			unset($text);

			if ($captionMatch || $keywordMatch || $commentMatch || $extraFieldsMatch) {
				$id = $searchAlbum->getPhotoId($j);
				// cause search word to be bolded
				$searchCaption = preg_replace($searchExpr, $searchRepl, $searchCaption);
				$searchKeywords = preg_replace($searchExpr, $searchRepl, $searchKeywords);

				$text[] = '<div class="desc">'. _("From Album") .":&nbsp;&nbsp;<a href=\"" .
		                              	makeAlbumUrl($searchAlbum->fields['name']) . "\">" .
                              			$searchAlbum->fields['title'] . "</a></div>";
				$text[] = '<div class="desc">'. $searchCaption .'</div>';
				if ($keywordMatch) { // only display Keywords if there was a keyword match
					$text[] = "<div class=fineprint>". _("KEYWORDS") .":&nbsp;&nbsp; $searchKeywords</div><br>";
				}
				$text[] = $commentText;
				$text[] = $extraFieldsText;

				$searchResult['images'][]=array(
					'photolink'	=> $searchAlbum->getThumbnailTag($j, $thumbSize),
					'photoURL'	=> makeAlbumUrl($searchAlbum->fields['name'], $id),
					'Text'		=> $text
				);
			}
		}
	}


	/* Now we show what we found ;) */
	$resultTexts=array(
			'albums' => array(
				'found' => sprintf(_("Albums containing %s"), "\"$origstr\""),
				'none'	=> _("No Album Matches")
				),
			'images' => array(
				'found'	=> sprintf(_("Photos containing %s in caption or comment"), "\"$origstr\""),
				'none'	=> _("No Photo Matches")
			));


	foreach ($resultTexts as $key => $text) {
		if (!empty($searchResult[$key])) {
			echo '<div class="vasummary">' .$text['found'] . '</div>';
			echo '<table width="'. $navigator['fullWidth'] . $navigator['widthUnits'] .'" border="0" cellspacing="0" cellpadding="0">';
				foreach ($searchResult[$key] as $searchdraw) {
					$searchdraw["bordercolor"] = $borderColor;
					$searchdraw["top"] = true;
					includeLayout('searchdraw.inc');
				}
			echo '</table>';
		} else {
			echo '<div class="desc">'. $text['none'] . '</div>';
		}
	}

	if (sizeof($skip) > 0) {
		echo gallery_error(sprintf(_("Some albums not searched as they require upgrading to the latest version of %s first."),Gallery()));
		if ($gallery->user->isAdmin()) {
			print "<br>";
			echo popup_link(_("Upgrade all albums."), "upgrade_album.php");
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
		echo "<p>";
	}

}
else {
/* No searchstring was given */
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

/* Bottom of the page */
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

