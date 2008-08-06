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
 * $Id: stats.php 15842 2007-02-21 17:46:18Z jenst $
*/
?>
<?php

function getmicrotime() {
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

function recurseAlbums( $parentAlbum) {
	global $list, $gallery;
	if ($parentAlbum) {
		debugMessage(sprintf(gTranslate('core', "Recursing album: %s"),  $parentAlbum->fields['name']),__FILE__, __LINE__, 2);

		$numPhotos = $parentAlbum->numPhotos(1);
		for ($j = 1; $j <= $numPhotos; $j++) {
			if ($parentAlbum->isAlbum($j) &&
			(!$parentAlbum->isHidden($j) || $gallery->user->isAdmin()) ) {
				$childAlbumName = $parentAlbum->getAlbumName($j);
				$childAlbum = new Album();
				$childAlbum->load($childAlbumName);
				$list[] = $childAlbum;
				recurseAlbums($childAlbum);
			}
		}
	}
}

function myFlock($fd, $op) {
	global $gallery;
	if (!strcmp($gallery->app->use_flock, "yes")) {
		$res = flock($fd, $op);
	}
	else {
		$res = 1;
	}

	return $res;
}

function readCacheNumPhotos( $cacheFilename ) {
	$numPhotos = -1;
	if ($fd = fs_fopen($cacheFilename, "rb")) {
		if (myFlock($fd, LOCK_SH)) {
			$numPhotos = fgets($fd);
			myFlock($fd, LOCK_UN);
		}
		else {
			debugMessage(gTranslate('core', "Read cache num photos lock failed."), __FILE__, __LINE__, 2);
		}
		fclose($fd);
	}

	return $numPhotos;
}

function readGalleryStatsCache( $cacheFilename, $start, $numPhotos ) {
	global $arrPhotos;

	$size = filesize($cacheFilename) + 1;
	if ($fd = fs_fopen($cacheFilename, "rb")) {
		if (myFlock($fd, LOCK_SH)) {
			fgets($fd);
			$posIndex = fgets($fd);
			$posData = ftell($fd);
			fseek( $fd, $posIndex + ($start * (CACHE_INDEX_FIELD_WIDTH + 1)), SEEK_CUR );
			$index = fgets($fd);
			fseek( $fd, $posData + $index );

			for ( $i = 0; $i < $numPhotos; ++$i ) {
				$data = fgetcsv($fd,$size,'|');
				if ($data ) {
					debugMessage(sprintf(gTranslate('core', "Album name : %s ; index: %s"), $data[0], $data[1]), __FILE__, __LINE__, 1);

					$arrPhotos[$start+$i] = array("albumName" => $data[0],
					'photoId' => $data[1],
					'rating' => $data[2],
					'ratingcount' => $data[3] );
				}
			}
			myFlock($fd, LOCK_UN);
		}
		else {
			debugMessage(gTranslate('core', "Read cache lock failed."), __FILE__, __LINE__, 2);
		}
		fclose($fd);
	}
}

function writeGalleryStatsCache( $cacheFilename ) {
	global $arrPhotos;

	if ($fd = fs_fopen($cacheFilename, "wb")) {
		if (myFlock($fd, LOCK_EX)) {
			// Write the number of photos on the first line of the cache.
			fwrite( $fd, sizeof($arrPhotos));
			fwrite( $fd, "\n" );

			// Write a blank line to the cache. This will eventually be used to store a pointer to the index.
			$fileSecondLine = ftell($fd);
			$filepos = 0;
			fwrite( $fd, sprintf("%".CACHE_INDEX_FIELD_WIDTH."d\n",$filepos));
			$index = "";

			// Write data that is required to be cached.
			for ($i = 0; $i < sizeof($arrPhotos); ++$i ) {
				$photoInfo = $arrPhotos[$i];
				$lineout = $photoInfo['albumName']. "|". $photoInfo['photoId']. "|". $photoInfo['rating']. "|". $photoInfo['ratingcount']."\n";
				fwrite( $fd, $lineout);
				$index = $index . sprintf("%".CACHE_INDEX_FIELD_WIDTH."d" ,$filepos) . "\n";
				$filepos += strlen($lineout);
			}

			// Write the index to the end of the cache.
			fwrite( $fd, $index );

			// Move back to the second line and write a pointer to the index.
			fseek( $fd, $fileSecondLine);
			fwrite( $fd, sprintf("%".CACHE_INDEX_FIELD_WIDTH."d",$filepos));
			myFlock($fd, LOCK_UN);
		}
		else {
			debugMessage(gTranslate('core', "Read cache lock failed."), __FILE__, __LINE__, 2);
		}
		fclose($fd);
	}
}

function makeStatsUrl($urlpage) {
	global $type, $period, $album, $thumbSize;
	global $showCaption, $showAlbumLink, $showDescription;
	global $showUploadDate, $showViews, $showVotes;
	//	global $showRatings;
	global $showComments, $showCaptureDate;
	global $showAddComment, $showAddVote, $showAlbumOwner, $showGrid, $numRows, $cols;
	global $photosPerPage, $totalPhotosReq, $reverse;
	global $timeMonth, $timeYear, $timeDay;

	$urlParams = array(
		"type" => $type,
		"page" => $urlpage,
		"sca" => $showCaption,
		"sal" => $showAlbumLink,
		"sde" => $showDescription,
		"sud" => $showUploadDate,
		"svi" => $showViews,
		"svo" => $showVotes,
		//		"sra" => $showRatings,
		"sco" => $showComments,
		"scd" => $showCaptureDate,
		"sac" => $showAddComment,
		"sav" => $showAddVote,
		"sao" => $showAlbumOwner,
		"showGrid" => $showGrid,
		"reverse" => $reverse,
		"tsz" => $thumbSize,
		"ppp" => $photosPerPage,
		"rows" => $numRows,
		"cols" => $cols,
		"total" => $totalPhotosReq
	);

	if (isset($period) ) {
		$urlParams["period"] = $period;
	}
	if (isset($album) ) {
		$urlParams["album"] = $album;
	}
	if (isset($timeYear) ) {
		$urlParams["ty"] = $timeYear;
	}
	if (isset($timeMonth) ) {
		$urlParams["tm"] = $timeMonth;
	}
	if (isset($timeDay) ) {
		$urlParams["td"] = $timeDay;
	}

	$msStatsUrl = makeGalleryUrl( "stats.php", $urlParams);

	return $msStatsUrl;
}

// Get rating average value for all pics in statsalbum
function getRatingAverage() {
	global $statsAlbum;

	$results = array();
	$ratings = array();
	$results_count = array();
	$nv_pairs = $statsAlbum->getVoteNVPairs();
	$voters = array();

	foreach ($statsAlbum->fields["votes"] as $element => $image_votes) {
		$accum_votes = 0;
		$count = 0;
		foreach ($image_votes as $voter => $vote_value ) {
			$voters[$voter] = true;
			if ($vote_value> $statsAlbum->getPollScale()) { // scale has changed
				$vote_value = $statsAlbum->getPollScale();
			}

			$accum_votes += $nv_pairs[$vote_value]["value"];
			$count++;
		}
		if ($accum_votes > 0) {
			$results_count[$element]=$count;
			if ($statsAlbum->getPollType() == "rank" || $statsAlbum->getPollScale() == 1) {
				$results[$element]=$accum_votes;
			}
			else {
				$results[$element]=number_format(((double)$accum_votes)/$count, 2);
			}
		}
		else {
			$results[$element] = 0;
		}

		$index = $statsAlbum->getIndexByVotingId($element);
		$ratings[$index] = array('average' => $results[$element], 'count' => $count);
	}
	return $ratings;
}

// Show the add comment link
function showAddCommentLink($photoId) {
	global $statsAlbum;

	$url = "add_comment.php?set_albumName={$statsAlbum->fields['name']}&id=$photoId";
	return popup_link(gTranslate('core',"add comment"), $url, 0, false, 500, 500, 'g-small', '', '', false);
}

// Show the add vote link
function showAddVoteLink( $photoId, $page ) {
	global $statsAlbum;

	$urlargs['set_albumName'] = $statsAlbum->fields['name'];
	$urlargs['id'] = $photoId;
	$urlargs['url'] = urlencode(makeStatsUrl( $page ));
	$url = makeGalleryUrl("vote.php", $urlargs);

	return galleryLink($url, gTranslate('core', "add vote"), array('class' => 'g-small'));
}


function getHeightFromTag($str) {
	$start = 'height="';
	$end = '"  border';
	$lenStr= strpos($str,$end) -strpos($str, $start);

	return substr(substr($str, strpos($str,$start), $lenStr), 8);
}

function getWidthFromTag($str) {
	$start = 'width="';
	$end = '" height="';
	$lenStr= strpos($str,$end) -strpos($str, $start);

	return substr(substr($str, strpos($str,$start), $lenStr), 7);
}

function showAddVoteAddCommentLinks($photoId, $page) {
	global $showAddComment, $showAddVote;

	$text = '';

	if ($showAddComment || $showAddVote) {
		if ($showAddComment) {
			$text .= showAddCommentLink($photoId);
		}
		if ($showAddVote) {
			$text .= (empty($text)) ? '' : '&nbsp;';
			$text .= showAddVoteLink($photoId, $page);
		}
	}

	return $text;
}

function displayTextCell($statsAlbum, $photoIndex, $photoId, $rating, $ratingcount ) {
	global $addLinksPos, $showAddComment, $showAddVote, $page, $showAlbumOwner, $showCaptureDate, $showUploadDate;
	global $showViews, $gallery, $showVotes;
	//	global $showRatings;
	global $showComments;
	global $showCaption, $showAlbumLink, $showDescription, $showGrid;

	if ($showCaption) {
		$captionTable = new galleryTable();
		$captionTable->setAttrs(array(
			'width' => '100%',
			'border' => 0,
			'cellspacing' => 0,
			'cellpadding' => 0,
			'class' => 'mod_title')
		);

		$statsCaption = $statsAlbum->getCaption($photoIndex);
		$statsCaption .= $statsAlbum->getCaptionName($photoIndex);
		$statsUrl = makeAlbumUrl($statsAlbum->fields['name'], $photoId);

		$captionText = '<a href="'. $statsUrl .'">'. $statsCaption .'</a>&nbsp;&nbsp;';

		if ($addLinksPos == 'oncaptionline' ) {
			$captionText .= showAddVoteAddCommentLinks($photoId, $page);
		}

		$captionTextTable = new galleryTable();
		$captionTextTable->setAttrs(array(
			'border' => 0,
			'cellspacing' => 0,
			'cellpadding' => 0,
			'class' => 'mod_title_bg')
		);

		$captionTextTable->addElement(array(
			'content' => '',
			'cellArgs' => array('class' => 'mod_title_left', 'align' => 'right'))
		);

		$captionTextTable->addElement(array(
			'content' => $captionText,
			'cellArgs' => array('class' => 'title', 'align' => 'left'))
		);

		$captionTextTable->addElement(array(
			'content' => '',
			'cellArgs' => array('class' => 'mod_title_right', 'align' => 'left'))
		);

		$captionTable->addElement(array(
			'content' => $captionTextTable->render(2),
			'cellArgs' => '')
		);

		$html = $captionTable->render(1);
	}

	// End Caption

	if ($showAlbumLink ) {
		$albumLink = sprintf(gTranslate('core', "From album: %s"),
		'<a href="'. makeAlbumUrl($statsAlbum->fields['name']) .'">'. $statsAlbum->fields['title'] . '</a>');

		$owner_var = '';
		if ($showAlbumOwner == 1 ) {
			$owner_var = '<br>' . sprintf(gTranslate('core', "Owned by: %s"), showOwner($statsAlbum->getOwner()));
		}

		$html .= "\n	" . '<div class="g-small">'. $albumLink . $owner_var . '</div>';
	}

	if ($showDescription) {
		$description = $statsAlbum->getExtraField($photoIndex, "Description");
		if ($description != "") {
			$html .= "\n	". '<div class="g-small" style="margin-top:10px;">'. $description .'</div>';
		}
	}

	if ($addLinksPos == 'abovestats' ) {
		$html .= showAddVoteAddCommentLinks($photoId, $page);
	}

	/* Begin Inner Stats */

	$innerStatsTable = new galleryTable();
	$innerStatsTable->setAttrs(array(
		'cellspacing' => 0,
		'cellpadding' => 0,
		'class' => 'g-small')
	);

	$innerStatsTable->setColumnCount(2);

	if ($showCaptureDate) {
		$captureDate = strftime($gallery->app->dateTimeString, $statsAlbum->getItemCaptureDate($photoIndex));

		$innerStatsTable->addElement(array(
			'content' => gTranslate('core', "Capture date:"),
			'cellArgs' => array('width' => 100))
		);

		$innerStatsTable->addElement(array(
			'content' => $captureDate,
			'cellArgs' => array('class' => 'g-small'))
		);
	}

	if ($showUploadDate) {
		$time = $statsAlbum->getUploadDate($photoIndex);
		// Older albums may not have this field.
		if ($time) {
			$time = strftime($gallery->app->dateString,$time);
			$innerStatsTable->addElement(array(
				'content' => gTranslate('core', "Upload date:"),
				'cellArgs' => array('width' => 100))
			);

			$innerStatsTable->addElement(array(
				'content' => $time,
				'cellArgs' => array('class' => 'g-small'))
			);
		}
	}

	if ($showViews &&
		($statsAlbum->fields["display_clicks"] == 'yes' || $gallery->user->isAdmin()) &&
		!$gallery->session->offline)
	{

		$innerStatsTable->addElement(array(
			'content' => gTranslate('core', "Viewed:"),
			'cellArgs' => array('width' => 100))
		);

		$innerStatsTable->addElement(array(
			'content' => gTranslate(
				'core',
				"Once",
				"%d times",
				$statsAlbum->getItemClicks($photoIndex),
				gTranslate('core', "Never viewed"),
		true),
			'cellArgs' => array('class' => 'g-small'))
		);
	}

	if (!empty($showVotes )) {
		$innerStatsTable->addElement(array(
			'content' => gTranslate('core', "Votes:"),
			'cellArgs' => array('width' => 100))
		);

		$innerStatsTable->addElement(array(
			'content' => $statsAlbum->getItemSVotes($photoIndex),
			'cellArgs' => array('class' => 'g-small'))
		);
	}

	if (!empty($showRatings)) {
		switch ($rating) {
			case -2:
				$photoRateCounts = '';
				$photoRate = gTranslate('core', "not rated");
				break;
			case -1:
				$photoRateCounts = '';
				$photoRate =  $ratingcount;
				$photoRate .= $ratingcount == 1 ? " vote" : " votes";
				$photoRate .= ' cast, more required';
				break;
			default:
				$photoRateCounts = $ratingcount == 1 ? " (".$ratingcount." vote)" : " (".$ratingcount." votes)";
				$photoRate = $rating;
		}

		$innerStatsTable->addElement(array(
			'content' => gTranslate('core', "Rating:"),
			'cellArgs' => array('width' => 100))
		);

		$innerStatsTable->addElement(array(
			'content' => $photoRate .' | '. $photoRateCounts,
			'cellArgs' => array('class' => 'g-small'))
		);
	}

	$html .= $innerStatsTable->render(1);
	// End Innerstats

	if ($addLinksPos == 'abovecomments' ) {
		$html .= showAddVoteAddCommentLinks($photoId, $page);
	}

	if ($showComments &&
		$statsAlbum->numComments($photoIndex) > 0 &&
		$statsAlbum->canViewComments($gallery->user->getUid()) )
	{

		$gallery->album = $statsAlbum;
		$html .= '<br clear="all">'. showComments($photoIndex, $statsAlbum->fields['name']);
	}

	if ($addLinksPos == 'belowcomments' ) {
		$html .= showAddVoteAddCommentLinks($photoId, $page);
	}

	return $html;
}

?>