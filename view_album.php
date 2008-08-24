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

list($page, $votes, $Vote) = getRequestVar(array('page', 'votes', 'Vote'));

// Hack check and prevent errors
if (empty($gallery->session->albumName) ||
	!$gallery->user->canReadAlbum($gallery->album) ||
	!$gallery->album->isLoaded())
{
	$gallery->session->gRedirDone = false;
	header('Location: ' . makeAlbumHeaderUrl('', '', array('gRedir' => 1)));
	return;
}

$gallery->session->offlineAlbums[$gallery->album->fields['name']] = true;

$albumTitle = clearGalleryTitle(strip_tags($gallery->album->fields['title']));

$albumName = $gallery->session->albumName;

$albumRSSURL = $gallery->app->photoAlbumURL . "/rss.php?set_albumName=$albumName";

$g_theme = $gallery->app->theme;

/* save the info that this album was viewed in this session and increment clickcounter */
if (!isset($gallery->session->viewedAlbum[$albumName]) && !$gallery->session->offline) {
	$gallery->session->viewedAlbum[$albumName] = 1;
	$gallery->album->incrementClicks();
}

/* Vote was given */
if (!empty($Vote) && canVote()) {
	if ($gallery->album->getPollScale() == 1 && $gallery->album->getPollType() != 'rank') {
		for ($index = $start; $index < $start+$perPage; $index ++) {
			$id = $gallery->album->getPhotoId($index);
			if (!$votes[$id]) {
				$votes[$id] = null;
			}
		}
	}
	saveResults($votes);
}

/* Notifications */
if ($gallery->user->isLoggedIn() &&
	$gallery->user->getEmail() &&
	!$gallery->session->offline &&
	$gallery->app->emailOn == "yes")
{
    if (getRequestVar('submitEmailMe')) {
        if (getRequestVar('comments')) {
            $gallery->album->setEmailMe('comments', $gallery->user, null, getRequestVar('recursive'));
        }
        else {
            $gallery->album->unsetEmailMe('comments', $gallery->user, null, getRequestVar('recursive'));
        }

        if (getRequestVar('other')) {
            $gallery->album->setEmailMe('other', $gallery->user, null, getRequestVar('recursive'));
        }
        else {
            $gallery->album->unsetEmailMe('other', $gallery->user, null, getRequestVar('recursive'));
        }
    }
}

$page = intval($page);
if (empty($page) || $page < 0) {
	if (isset($gallery->session->albumPage[$gallery->album->fields['name']])) {
		$page = $gallery->session->albumPage[$gallery->album->fields['name']];
	}
	else {
		$page = 1;
	}
}
else {
	$gallery->session->albumPage[$gallery->album->fields['name']] = $page;
}

$rows = $gallery->album->fields['rows'];
$cols = $gallery->album->fields['cols'];

list ($numPhotos, $numAlbums, $visibleItems) = $gallery->album->numVisibleItems($gallery->user, 1);

$numVisibleItems = $numPhotos + $numAlbums;
$perPage = $rows * $cols;
$maxPages = max(ceil(($numPhotos + $numAlbums) / $perPage), 1);

if ($page > $maxPages) {
	$page = $maxPages;
}

$currentUrl = makeAlbumHeaderUrl($gallery->session->albumName,'', array('page' => $page));

$start = ($page - 1) * $perPage + 1;
$end = $start + $perPage;

$nextPage = $page + 1;
if ($nextPage > $maxPages) {
	$nextPage = 1;
	$last = 1;
}

$previousPage = $page - 1;
if ($previousPage == 0) {
	$previousPage = $maxPages;
	$first = 1;
}

$bordercolor = $gallery->album->fields['bordercolor'];

$imageCellWidth = floor(100 / $cols) . '%';

$navigator['page']		= $page;
$navigator['pageVar']		= 'page';
$navigator['maxPages']		= $maxPages;
$navigator['url']		= makeAlbumUrl($gallery->session->albumName);
$navigator['spread']		= 5;
$navigator['bordercolor']	= $bordercolor;

$breadcrumb['text']		= returnToPathArray($gallery->album, false);
$breadcrumb['bordercolor']	= $bordercolor;

$adminText	= '';
$albums_str	= gTranslate('core', "1 sub-album", "%d sub-albums", $numAlbums, gTranslate('core', "No albums"), true);
$imags_str	= gTranslate('core', "1 image", "%d images", $numPhotos, gTranslate('core', "No images"), true);
$pages_str	= gTranslate('core', "1 page", "%d pages", $maxPages, gTranslate('core', "0 pages"), true);

if ($numAlbums && $maxPages > 1) {
	$adminText .= sprintf(gTranslate('core', "%s and %s in this album on %s."),
	$albums_str, $imags_str, $pages_str);
}
else if ($numAlbums) {
	$adminText .= sprintf(gTranslate('core', "%s and %s in this album."),
	$albums_str, $imags_str);
}
else if ($maxPages > 1) {
	$adminText .= sprintf(gTranslate('core', "%s in this album on %s."),
	$imags_str, $pages_str);
}
else {
	$adminText .= sprintf(gTranslate('core', "%s in this album."),
	$imags_str);
}

if ($gallery->user->canWriteToAlbum($gallery->album) && !$gallery->session->offline) {
	$hidden = $gallery->album->numHidden();
	if($hidden > 0) {
		$adminText .= ' '. gTranslate('core', "%s element is hidden.", "%s elements are hidden.", $hidden, '', true);
	}
}

$va_javascript = jsHTML('jopen.js');

$iconElements = array();

if($gallery->app->theme == 'classic_sidebar') {
	$adminCommandsCaption = false;
	if( $gallery->app->useIcons == 'both') {
		$specialIconMode = 'lined';
	}

}
else {
	$adminCommandsCaption = true;
}

$adminCommands = getAlbumCommands($gallery->album, $adminCommandsCaption, false);

if ($gallery->album->fields['slideshow_type'] != "off" &&
	($numPhotos != 0 ||
	($numVisibleItems != 0 && $gallery->album->fields['slideshow_recursive'] == "yes")))
{
	$iconElements[] = galleryLink(
		makeGalleryUrl("slideshow.php", array("set_albumName" => $albumName)),
		gTranslate('core', "Slidesho_w"),
		array(),
		'presentation.gif',
		true
	);
}

/* User is allowed to view ALL comments */
if (checkRequirements('comments_overview_for_all')) {
	$iconElements[] = galleryLink(
		makeGalleryUrl("view_comments.php", array("set_albumName" => $gallery->session->albumName)),
		gTranslate('core', "View&nbsp;_comments"),
		array(),
		'view_comment.gif',
		true
	);
}

$iconElements[] = LoginLogoutButton($currentUrl);

$specialIconMode = '';

// -- if borders are off, just make them the bgcolor ----
$borderwidth = $gallery->album->fields['border'];

if (($gallery->album->getPollType() == 'rank') && canVote()) {
	$my_choices = array();
	if ( $gallery->album->fields['votes']) {
		foreach ($gallery->album->fields['votes'] as $id => $image_votes) {
			$index = $gallery->album->getIndexByVotingId($id);
			if ($index < 0) {
				// image has been deleted!
				unset($gallery->album->fields['votes'][$id]);
				continue;
			}
			if (isset($image_votes[getVotingID()])) {
				$my_choices[$image_votes[getVotingID()]] = $id;
			}
		}
	}

	if (sizeof($my_choices) > 0) {
		ksort($my_choices);
		$nv_pairs = $gallery->album->getVoteNVPairs();

		$va_poll_box1 = gTranslate('core', "Your votes are:");

		$pollInfoTable = new galleryTable();
		foreach ($my_choices as $key => $id) {
			$index = $gallery->album->getIndexByVotingId($id);

			$pollInfoTable->addElement(array('content' => "- ". $nv_pairs[$key]["name"]));
			$pollInfoTable->addElement(array('content' => ':'));
			if ($gallery->album->isAlbum($index)) {
				$albumName = $gallery->album->getAlbumName($index);
				$myAlbum = new Album();
				$myAlbum->load($albumName);

				$pollInfoTable->addElement(array('content' =>
					galleryLink(
					   makeAlbumUrl($albumName),
					   sprintf(gTranslate('core', "Album: %s"), $myAlbum->fields['title']))
					)
				);
			}
			else {
				$desc = $gallery->album->getCaption($index);
				if (trim($desc) == '') {
					$desc = $gallery->album->getPhotoId($index);
				}

				$photoId = str_replace('item.', '', $id);
				$pollInfoTable->addElement(array('content' =>
				   galleryLink(makeAlbumUrl($gallery->session->albumName, $photoId), $desc)
				));
			}
		}
		$va_poll_box1 .= $pollInfoTable->render();
	}
}

list($va_poll_result, $results) = showResultsGraph( $gallery->album->getPollNumResults());

$va_poll_box2 = '';
if ($gallery->album->getPollShowResults()) {
	$va_poll_box2 = $va_poll_result;
}

if (canVote()) {
	$nv_pairs = $gallery->album->getVoteNVPairs();

	if ($gallery->album->getPollScale() == 1) {
		$options = $nv_pairs[0]['name'];
	}
	else {
		/** note to translators:
		 * This produces (in English) a list of the form: "a, b, c or d".  Correct translation
		 * of ", " and " or  " should produce a version that makes sense in your language.
		 */
		$options = '';
		for ($count=0; $count < $gallery->album->getPollScale()-2 ; $count++) {
			$options .= $nv_pairs[$count]['name'] .gTranslate('core', ", ");
		}
		$options .= $nv_pairs[$count++]['name'] .gTranslate('core', " or ");
		$options .= $nv_pairs[$count]['name'];
	}

	$va_poll_box3 = sprintf(gTranslate('core', "To vote for an image, click on %s."), $options);
	$va_poll_box3 .= ' ';
	$va_poll_box3 .= sprintf(
		gTranslate('core', "You MUST click on %s for your vote to be recorded."),
		'<b>' .gTranslate('core', "Vote") .'</b> '
	);

	if ($gallery->album->getPollType() == 'rank') {
		$voteCount = $gallery->album->getPollScale();
		$va_poll_box3 .= gTranslate('core',
			"You have a total of %d vote and can change it later if you wish.",
			"You have a total of %d votes and can change them later if you wish.",
			$voteCount, '', true);
	}
	else {
		$va_poll_box3 .= gTranslate('core', "You can change your votes later, if you wish.");
	}
}

// <!-- image grid table -->

$numPhotos = $gallery->album->numPhotos(1);
/* this determines if we display "* Item contains a comment" at end of page */
$displayCommentLegend = false;
$nr = 0;
$wz_tooltips = '';
$embeddedLightboxCSS = '';

if ($numPhotos) {
	$rowCount = 0;

	// Find the correct starting point, accounting for hidden photos
	$rowStart = 0;
	$cnt = 0;
	$form_pos = 0; // counts number of images that have votes below, ie without albums;
	$rowStart = $start;

	$albumItems = array();

	/**
	 * Loop through the images row by row
	 */
	while ($rowCount < $rows) {
		/* Do the inline_albumthumb header row */
		$visibleItemIndex = $rowStart;
		$i = $visibleItemIndex <= $numVisibleItems ? $visibleItems[$visibleItemIndex] : $numPhotos+1;
		$j = 1;

		while ($j <= $cols && $i <= $numPhotos) {
			$j++;
			$visibleItemIndex++;
			$i = $visibleItemIndex <= $numVisibleItems ? $visibleItems[$visibleItemIndex] : $numPhotos+1;
		}

		/* Do the picture row */
		$visibleItemIndex = $rowStart;
		$i = $visibleItemIndex <= $numVisibleItems ? $visibleItems[$visibleItemIndex] : $numPhotos+1;
		$j = 1;

		while ($j <= $cols && $i <= $numPhotos) {
			unset($gallery->html_wrap);

			$photo	= $gallery->album->getPhoto($i);

			//-- put some parameters for the wrap files in the global object ---
			$gallery->html_wrap['borderColor'] = $bordercolor;
			$borderwidth = $gallery->html_wrap['borderWidth'] = $borderwidth;

			if ($photo->isAlbum()) {
				$scaleTo = 0; //$gallery->album->fields["thumb_size"];
				$myAlbum = $gallery->album->getNestedAlbum($i);
				list($iWidth, $iHeight) = $myAlbum->getHighlightDimensions($scaleTo);
			}
			else {
				unset($myAlbum);
				$scaleTo = 0;  // thumbs already the right
				// size for this album
				list($iWidth, $iHeight) = $gallery->album->getThumbDimensions($i, $scaleTo);
			}

			if ($iWidth == 0) {
				$iWidth = $gallery->album->fields['thumb_size'];
			}

			if ($iHeight == 0) {
				$iHeight = 100;
			}

			$tooltipWidth = (2*$iWidth <= 300) ? 2*$iWidth : 300;

			$gallery->html_wrap['imageWidth'] = $iWidth;
			$gallery->html_wrap['imageHeight'] = $iHeight;

			$id = $gallery->album->getPhotoId($i);

			++$nr;

			$albumItems[$nr]['note']		= '';
			$albumItems[$nr]['dimensions']		= '';
			$albumItems[$nr]['infos']		= array();
			$albumItems[$nr]['caption']		= '';
			$albumItems[$nr]['clickcounter']	= '';
			$albumItems[$nr]['options']		= '';
			$caption				= '';
			$description				= '';
			$downloadLink				= '';

			/* Caption for Photo or Movie, Caption for album is done below */
			if (!isset($myAlbum)) {
				$caption = nl2br($photo->getCaption());

				$albumItems[$nr]['caption'] = $caption;
				$albumItems[$nr]['caption'] .= $gallery->album->getCaptionName($i) . ' ';

				// indicate with * if we have a comment for a given photo
				if ($gallery->user->canViewComments($gallery->album) &&
					$gallery->app->comments_enabled == "yes")
				{
					// If comments indication for either photos or both
					switch ($gallery->app->comments_indication) {
						case 'photos':
						case 'both':
							$lastCommentDate = $gallery->album->itemLastCommentDate($i);
							$albumItems[$nr]['caption'] .=
								lastCommentString($lastCommentDate, $displayCommentLegend);
							break;
					}

				}

				$description = nl2br($photo->getDescription());
				if(!empty($description)) {
					$header = gTranslate('core' ,"Description for"). sprintf("<br>'%s'", $caption);
					$label = gTranslate('core' ,"... show full description");

					list($needJavascript, $albumItems[$nr]['description']) =
						readMoreBox("description$nr", $header, $description, 0, $label, "thumb$nr");
				}

				if ($gallery->album->fields['display_clicks'] == "yes" &&
					!$gallery->session->offline &&
					$photo->getItemClicks() > 0)
				{
					$albumItems[$nr]['clickcounter'] =
						gTranslate('core', "Viewed: 1 time.", "Viewed: %d times.", $gallery->album->getItemClicks($i), '', true);
				}

			}

			/**
			 * Element is a movie
			 */
			if ($gallery->album->isMovieByIndex($i)) {
				$gallery->html_wrap['imageTag']		= $gallery->album->getThumbnailTag($i);
				$gallery->html_wrap['imageHref']	= makeAlbumUrl($gallery->session->albumName, $id);
				$gallery->html_wrap['type']		= 'inline_moviethumb.frame';
				$gallery->html_wrap['frame']		= $gallery->album->fields['thumb_frame'];
			}
			/**
			 * Element is a subalbum
			 */
			elseif (isset($myAlbum)) {
				// Caption
				if ($gallery->user->canDownloadAlbum($myAlbum) && $myAlbum->numPhotos(1)) {
					$iconText = gImage('icons/compressed.gif', gTranslate('core', "Download entire album as archive"));
					$downloadLink = popup_link(
						$iconText,
						'download.php?set_albumName='. $gallery->album->getAlbumName($i),
						false, true, 500, 500, '', '', '', false, false
					);
				}

				$caption ='<span class="g-emphasis">';
				$caption .= sprintf(gTranslate('core', "Album: %s"),
							galleryLink(makeAlbumUrl($gallery->album->getAlbumName($i)), $myAlbum->fields['title'])
				);
				$caption .= '</span> ';

				$albumItems[$nr]['caption'] = $caption . $downloadLink;

				$tooltip = sprintf(gTranslate('core', "Album: %s"), $myAlbum->fields['title']);

				// Description
				$myDescription = $myAlbum->fields['description'];
				if (!empty($myDescription) &&
					$myDescription != gTranslate('core', "No description") &&
					$myDescription != "No description")
				{
					$albumItems[$nr]['description'] = nl2br($myDescription);
					$tooltip .= '<br>' . $myDescription;
				}

				$wz_tooltips .= "\n <div id=\"wzTooltip_$i\" class=\"g-tooltip\" style=\"display: none; \">" . nl2br($tooltip) . '</div>';
				$gallery->html_wrap['imageTag']	= $myAlbum->getHighlightTag(
									$scaleTo,
									array('id' => "thumbnail_$i",
									      'onmouseover' => "TagToTip('wzTooltip_$i', COPYCONTENT, false)",
									      'onmouseout' => "UnTip()",
									      'title' => '',
									      'alt' => ''),
									false
				);

				if ($gallery->album->fields['lightbox'] == "yes") {
					list($album, $highlight) = $myAlbum->getHighlightedItem();

					$gallery->html_wrap['imageHref']	= $highlight->getPhotoPath($album->getAlbumDirURL("full"));
					$gallery->html_wrap['attrlist']['rel']  = 'lightbox[gallery]';
					$gallery->html_wrap['attrlist']['id'] 	= "thumblink_$i";
				}
				else {
					$gallery->html_wrap['imageHref']	= makeAlbumUrl($gallery->album->getAlbumName($i));
				}

				$gallery->html_wrap['frame']		= $gallery->album->fields['album_frame'];
				$gallery->html_wrap['type']		= 'inline_albumthumb.frame';

				// Further album infos
				$albumItems[$nr]['infos'][] =
					sprintf (gTranslate('core', "Last change: %s"), $myAlbum->getLastModificationDate());

				$visItems = array_sum($myAlbum->numVisibleItems($gallery->user));
				$contains = gTranslate('core',"Contains: One Item","Contains %d items", $visItems, '', true) .'. ';
				// If comments indication for either albums or both
				switch ($gallery->app->comments_indication) {
					case 'albums':
					case 'both':
						$lastCommentDate =
							$myAlbum->lastCommentDate($gallery->app->comments_indication_verbose);
						if ($lastCommentDate > 0) {
							$contains .= lastCommentString($lastCommentDate, $displayCommentLegend);
						}
						break;
				}

				$albumItems[$nr]['infos'][] = $contains;

				if ($gallery->album->fields['display_clicks'] == "yes" &&
				    !$gallery->session->offline &&
				    $myAlbum->getClicks() > 0)
				{
					$albumItems[$nr]['clickcounter'] = gTranslate('core', "Viewed: Once.", "Viewed: %d times.", $myAlbum->getClicks(), '', true);
				}

			}
			/**
			 * Element is a picture
			 */
			else {
				$tooltip = $gallery->album->getAltText($i);

				if(!empty($tooltip)) {
					$gallery->html_wrap['imageTag']	= $gallery->album->getThumbnailTag(
										$i,
										0,
										array('onmouseover' => "TagToTip('wzTooltip_$i', COPYCONTENT, false)",
										      'onmouseout' => "UnTip()")
					);
					$wz_tooltips .= "\n <div id=\"wzTooltip_$i\" class=\"g-tooltip\" style=\"display: none; \">" . nl2br($tooltip) . '</div>';
				}
				else {
					$gallery->html_wrap['imageTag']		= $gallery->album->getThumbnailTag($i);
				}


				if ($gallery->album->fields['lightbox'] == "yes") {
					$gallery->html_wrap['imageHref']	= $gallery->album->getPhotoPath($i);
					$gallery->html_wrap['attrlist']['rel']  = 'lightbox[gallery]';
					$gallery->html_wrap['attrlist']['id'] 	= "thumblink_$i";
				}
				else {
					$gallery->html_wrap['imageHref']	= makeAlbumUrl($gallery->session->albumName, $id);
				}


				$gallery->html_wrap['frame']		= $gallery->album->fields['thumb_frame'];
				$gallery->html_wrap['type']		= 'inline_photothumb.frame';

				/* Do the clickable-dimensions line */
				if ($gallery->album->fields['showDimensions'] == "yes") {
					$image	= $photo->image;

					if (!empty($image)) {
						list($wr, $hr) = $image->getDimensions();
						list($wf, $hf) = $image->getRawDimensions();

						$viewFull = $gallery->user->canViewFullImages($gallery->album);
						$fullOnly = (isset($gallery->session->fullOnly) &&
							!strcmp($gallery->session->fullOnly, 'on') &&
							!strcmp($gallery->album->fields['use_fullOnly'], "yes"));


						/* display file sizes if dimensions are identical */
						if ($wr == $wf && $hr == $hf && $viewFull && $photo->isResized()) {
							$fsr = ' ' . sprintf(gTranslate('core', '%dkB'), (int) $photo->getFileSize(0) >> 10);
							$fsf = ' ' . sprintf(gTranslate('core', '%dkB'), (int) $photo->getFileSize(1) >> 10);
						}
						else {
							$fsr = '';
							$fsf = '';
						}

						$attrlist = array();
						if (($photo->isResized() && !$fullOnly) || !$viewFull) {
							if($gallery->album->fields['dimensionsAsPopup'] == "yes") {
								$sizedImageUrl	= $gallery->album->getPhotoPath($i);
								$attrlist	= array('onClick' => popup($sizedImageUrl, true, $hr, $wr),
											'title' => sprintf(gTranslate('core', "Opens picture in %s x %s in a popup."), $wr, $hr)
								);

							}
							else {
								$sizedImageUrl = makeAlbumUrl($gallery->session->albumName, $image->name);
							}

							$albumItems[$nr]['dimensions'] = galleryLink(
								$sizedImageUrl,
								"[${wr}x{$hr}${fsr}] ",
								$attrlist
							);
						}

						$attrlist = array();
						if ($viewFull) {
							if(isset($gallery->album->fields['dimensionsAsPopup']) &&
								   $gallery->album->fields['dimensionsAsPopup'] == "yes")
							{
								$fullImageUrl	= $gallery->album->getPhotoPath($i, true);
								$attrlist	= array(
									'onClick' => popup($fullImageUrl, true, $hf, $wf),
									'title' => sprintf(gTranslate('core', "Opens picture in %s x %s in a popup."), $wf, $hf)
								);
							}
							else {
								$fullImageUrl = makeAlbumUrl($gallery->session->albumName, $image->name, array('full' => true));
							}

							$albumItems[$nr]['dimensions'] .= galleryLink(
								$fullImageUrl,
								"[${wf}x${hf}${fsf}]",
								$attrlist
							);
						}
					}
				}

			}

			$frame = $gallery->html_wrap['frame'];
			list($divCellWidth, $divCellHeight, $padding) =
				calcVAdivDimension($frame, $iHeight, $iWidth, $borderwidth);
			// If there is only one column, we don't need to try and match row heights
			if ($cols == 1) {
				$padding = 0;
			}

			$gallery->html_wrap['style'] = "style=\"padding-top: {$padding}px; padding-bottom:{$padding}px; width: {$divCellWidth}px; height: {$divCellHeight}px;\"";
			// Note: Normally all we need for the thumb is already there, but for the lightbox we need the caption

			if (canVote()){
				if ($gallery->album->fields['poll_type'] == 'rank' && $divCellWidth < 200) {
					$divCellWidth = 200;
				}
			}

			if ($photo->isHidden() && !$gallery->session->offline) {
				$albumItems[$nr]['note'] .= '(' . gTranslate('core', "hidden") .')<br>';
			}

			if ($gallery->user->canWriteToAlbum($gallery->album) &&
				$photo->isHighlight() && !$gallery->session->offline)
			{
				$albumItems[$nr]['note'] .= '(' . gTranslate('core', "highlight") .')<br>';
			}


			if (canVote()) {
				$albumItems[$nr]['voting'] =
					addPolling($gallery->album->getVotingIdByIndex($i), $form_pos, false);
				$form_pos++;
			}

			$albumItemOptions = getItemActions($i, true, true, true);

			if (sizeof($albumItemOptions) > 3) {
				$albumItems[$nr]['options'] = drawSelect2(
					"s$i",
					$albumItemOptions,
					array(
						'onChange' => "jopen(this)",
						'class' => 'g-admin')
				);
			}
			/*
			 * uncomment this part if you want tiny icons
			 * for photo properties and ecards in the thumbs view.
			else {
				$specialIconMode = "yes";

				// Show item options. Such as eCard or photo properties link.
				foreach ($albumItemOptions as $key => $option) {
					if (!isset($option['separate'])) continue;

					if(!empty($option['value'])) {
						if (stristr($option['value'], 'popup')) {
							$content = popup_link(
								$option['text'],
								$option['value'],
								true, false, 550, 600, '', '', $option['icon'], true, false
							);
						}
						else {
							$content = galleryIconLink(
									$option['value'],
									$option['icon'],
									$option['text']
							);
						}
						$albumItems[$nr]['options'] .= $content . "&nbsp;\n";
					}
				}
			}
			*/

			$albumItems[$nr]['thumb'] = $gallery->html_wrap;


			$j++;
			$visibleItemIndex++;
			$i = $visibleItemIndex<=$numVisibleItems ? $visibleItems[$visibleItemIndex] : $numPhotos+1;
		}
		/* End of Picture Row */

		/* Now do the inline_albumthumb footer row */
		$visibleItemIndex = $rowStart;
		$i = $visibleItemIndex <= $numVisibleItems ? $visibleItems[$visibleItemIndex] : $numPhotos+1;
		$j = 1;

		while ($j <= $cols && $i <= $numPhotos) {
			$j++;
			$visibleItemIndex++;
			$i = $visibleItemIndex <= $numVisibleItems ? $visibleItems[$visibleItemIndex] : $numPhotos+1;
		}

		$rowCount++;
		$rowStart = $visibleItemIndex;
	}
	/* End of picture table */

	if(empty($albumItems)) {
		$va_notice = gTranslate('core', "This album is empty.");
	}
	else {
		if ($gallery->album->fields['lightbox'] == "yes") {
			$va_javascript .= jsHTML('lightbox2/prototype.js');
			$va_javascript .= jsHTML('lightbox2/scriptaculous.js?load=effects,builder');
			$va_javascript .= jsHTML('lightbox2/lightbox.js.php');

			if($GALLERY_EMBEDDED_INSIDE) {
				$embeddedLightboxCSS = _getStyleSheetLink("lightbox");
			}
		}
	}
}
else {
	if ($gallery->user->canAddToAlbum($gallery->album) && !$gallery->session->offline) {
		$url = makeGalleryUrl(
			'add_photos_frame.php',
			array('set_albumName' => $gallery->session->albumName, 'type' => 'popup')
		);

		$va_notice = popup_link(gTranslate('core', "Hey! Add some photos."), $url, 1, true, 500, 600);
	}
	else {
		$va_notice = gTranslate('core', "This album is empty.");
	}
}

if(!fs_file_exists(GALLERY_BASE . "/templates/$g_theme/album.tpl.default")) {
	$g_theme = 'classic';
}

define('READY_TO_INCLUDE', 'DISCO');
$templateFile = getDefaultFilename(GALLERY_BASE ."/templates/$g_theme/album.tpl");

require($templateFile);

?>
