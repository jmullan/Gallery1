<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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

require_once(dirname(__FILE__) . '/init.php');

/* urldecode, remove tags, and then encode htmlspecial chars to make string display-safe */
$searchstring = htmlspecialchars(strip_tags(urldecode(getRequestVar('searchstring'))));

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
  <title><?php echo $gallery->app->galleryTitle ?> :: <?php echo gTranslate('core', "Search") ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>">
<?php }

includeHtmlWrap("gallery.header");

if (!empty($searchstring)) {
	echo addSearchForm($searchstring, langRight());
}

$adminbox['text'] = '<span class="head">'. gTranslate('core', "Search") .'</span>';
$adminbox['commands'] = '[<a href="'. makeAlbumUrl() .'">'. gTranslate('core', "Return to gallery") .'</a>] ';

$breadcrumb["text"][] = sprintf(gTranslate('core', "Gallery: %s"), '<a class="bread" href="'. makeGalleryUrl("albums.php") . '">'.$gallery->app->galleryTitle .'</a>');

includeLayout('navtablebegin.inc');
includeLayout('adminbox.inc');
includeLayout('navtablemiddle.inc');
includeLayout('breadcrumb.inc');
includeLayout('navtableend.inc');
echo languageSelector();

$albumDB = new AlbumDB();
$list = $albumDB->albumList;
$numAlbums = count($list);
$photoMatch = 0;
$albumMatch = 0;
$skip = array();
$text = array();
if (!empty($searchstring)) {
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

		// Build navigationslinks to the album.
		$parentNameArray = $searchAlbum->getParentAlbums(false);
		$parentURLString = '';
		if(count($parentNameArray) != 0) {
			foreach($parentNameArray as $nr => $pAlbum) {
				$parentURLString .= '<a href="'. $pAlbum['url'] .'">' .$pAlbum['title'] .'</a> &raquo; ';
			}
		}
		// initialize values
		unset($text);

		$searchTitle = $searchAlbum->fields['title'];
		$searchDescription = $searchAlbum->fields['description'];
		$searchSummary = $searchAlbum->fields['summary'];
		$searchName = $searchAlbum->fields['name'];

		$matchTitle = eregi("$searchstring", $searchTitle);
		$matchDescription = eregi("$searchstring", $searchDescription);
		$matchSummary = eregi("$searchstring", $searchSummary);
		$matchName = eregi("$searchstring", $searchName);

		if ($matchTitle || $matchDescription || $matchSummary | $matchName) {
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
				'photolink' => $searchAlbum->getHighlightTag($thumbSize),
				'photoURL'	=> $photoURL,
				'Text'		=> $text
			);
		}

		/* now search for photos .. */
		$numPhotos = $searchAlbum->numPhotos(1);
		for ($j = 1; $j <= $numPhotos; $j++) {
			if ($searchAlbum->isHidden($j)) {
				continue;
			}

			$photo = $searchAlbum->getPhoto($j);

			/* Search through comments */
			$commentMatch = 0;
			$commentText = '';
			if ($searchAlbum->canViewComments($uid) || $gallery->user->isAdmin()) {
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
							$commentText = gTranslate('core', "Matching Comments").":<br>";
							$commentMatch = 1;
						}

						$searchComment = preg_replace($searchExpr, $searchRepl, $searchComment);
						$commentText .= "\n". $searchComment . "<br><br>";
					}
				}
			}

			/* Search through extrafields */
			$extraFieldsMatch = 0;
			$extraFieldsText = '';
			foreach ($searchAlbum->getExtraFields() as $field) {
				$fieldValue=$searchAlbum->getExtraField($j, $field);
				if (eregi($searchstring, $fieldValue)) {
					$fieldValue = preg_replace($searchExpr, $searchRepl, $fieldValue);
					$extraFieldsText .= "<b>$field:</b> $fieldValue<br><br>";
					$extraFieldsMatch = 1;
				}
			}

			/* Search through caption */
			$searchCaption = gTranslate('core', "Caption: ") . $photo->getCaption();
			$searchCaption .= $searchAlbum->getCaptionName($j);
			$captionMatch = eregi($searchstring, $searchCaption);

			/* Search through keywords */
			$searchKeywords = $photo->getKeywords();
			if(!empty($searchKeywords)) {
				$keywordMatch = eregi($searchstring, $searchKeywords);
			}
			else {
				$keywordMatch = false;
			}

			/* Search through imagename */
			if(!empty($photo->image->name)) {
				$searchName = $photo->image->name;
				$nameMatch = eregi($searchstring, $searchName);
			}
			else {
				$nameMatch = false;
			}

			unset($text);

			if ($captionMatch || $keywordMatch || $commentMatch || $extraFieldsMatch || $nameMatch) {
				$id = $searchAlbum->getPhotoId($j);
				// cause search word to be bolded
				$searchCaption = preg_replace($searchExpr, $searchRepl, $searchCaption);
				$searchKeywords = preg_replace($searchExpr, $searchRepl, $searchKeywords);

				$text[] = '<div class="desc">'. gTranslate('core', "From Album") .":&nbsp;&nbsp;".
				$parentURLString .
				"<a href=\"" .
					makeAlbumUrl($searchAlbum->fields['name']) . "\">" .
					$searchAlbum->fields['title'] .
				"</a></div>";

				$text[] = '<div class="desc">'. $searchCaption .'</div>';
				if ($keywordMatch) { // only display Keywords if there was a keyword match
					$text[] = '<div class="fineprint">'. gTranslate('core', "KEYWORDS") .":&nbsp;&nbsp; $searchKeywords</div><br>";
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
	$resultTexts = array(
		'albums' => array(
			'found' => sprintf(gTranslate('core', "Albums containing %s"), "\"$origstr\""),
			'none'	=> gTranslate('core', "No Album Matches")
		),
		'images' => array(
			'found'	=> sprintf(gTranslate('core', "Photos containing %s in caption, comment or name."), "\"$origstr\""),
			'none'	=> gTranslate('core', "No Photo Matches")
		)
	);

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
		}
		else {
			echo '<div class="desc">'. $text['none'] . '</div>';
		}
	}

	if (sizeof($skip) > 0) {
		echo gallery_error(sprintf(gTranslate('core', "Some albums not searched as they require upgrading to the latest version of %s first."),Gallery()));
		if ($gallery->user->isAdmin()) {
			print "<br>";
			echo popup_link(gTranslate('core', "Upgrade all albums."), "upgrade_album.php");
			print "<br>(";
			$join_text='';
			foreach($skip as $album) {
				$link = makeGalleryUrl("view_album.php",
				array("set_albumName" => $album->fields["name"]));
				echo $join_text."<a href=\"$link\">".$album->fields["name"] ."</a>";
				$join_text=", ";
			}
			print ")";
		}
		echo "<p>";
	}
}
else {
	/* No searchstring was given */
	echo "\n<p align=\"center\">";
	echo gTranslate('core', "Search the Gallery's Album and Photo titles, descriptions and comments.");
	echo "\n</p>\n";
	echo '<div class="right">'. addSearchForm($searchstring) . "\n</div>";
}

echo '<hr width="100%">';
includeHtmlWrap("gallery.footer");

if (!$GALLERY_EMBEDDED_INSIDE) {
?>
</body>
</html>
<?php } ?>

