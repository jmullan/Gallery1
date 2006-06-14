<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2006 Bharat Mediratta
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
?>
<?php

require_once(dirname(__FILE__) . '/init.php');

list($page,$votes, $Vote) = getRequestVar(array('page', 'votes', 'Vote'));

// Hack check and prevent errors
if (empty($gallery->session->albumName) || !$gallery->user->canReadAlbum($gallery->album) || !$gallery->album->isLoaded()) {
	$gallery->session->gRedirDone = false;
	header("Location: " . makeAlbumHeaderUrl('', '', array('gRedir' => 1)));
	return;
}

$gallery->session->offlineAlbums[$gallery->album->fields["name"]]=true;

$albumName = $gallery->session->albumName;

/* save the info that this album was viewed in this session and increment clickcounter */
if (!isset($gallery->session->viewedAlbum[$albumName]) && !$gallery->session->offline) {
	$gallery->session->viewedAlbum[$albumName] = 1;
	$gallery->album->incrementClicks();
}

/* Vote was given */
if (!empty($Vote)) {
	if ($gallery->album->getPollScale() == 1 && $gallery->album->getPollType() != "rank") {
		for ($index=$start; $index < $start+$perPage; $index ++) {
			$id = $gallery->album->getPhotoId($index);
			if (!$votes[$id]) {
				$votes[$id] = null;
			}
		}
	}
	saveResults($votes);
}

/* Notifications */
if ($gallery->user->isLoggedIn() && $gallery->user->getEmail() &&
  !$gallery->session->offline & $gallery->app->emailOn == 'yes') {
    if (getRequestVar('submitEmailMe')) {
        if (getRequestVar('comments')) {
            $gallery->album->setEmailMe('comments', $gallery->user);
        } else {
            $gallery->album->unsetEmailMe('comments', $gallery->user);
        }
        if (getRequestVar('other')) {
            $gallery->album->setEmailMe('other', $gallery->user);
        } else {
            $gallery->album->unsetEmailMe('other', $gallery->user);
        }
    }
}

$page = intval($page);
if (empty($page) || $page < 0) {
	if (isset($gallery->session->albumPage[$gallery->album->fields['name']])) {
		$page = $gallery->session->albumPage[$gallery->album->fields["name"]];
	} else {
		$page = 1;
	}
} else {
	$gallery->session->albumPage[$gallery->album->fields["name"]] = $page;
}

$rows = $gallery->album->fields["rows"];
$cols = $gallery->album->fields["cols"];
list ($numPhotos, $numAlbums, $visibleItems) = $gallery->album->numVisibleItems($gallery->user, 1);
$numVisibleItems = $numPhotos + $numAlbums;
$perPage = $rows * $cols;
$maxPages = max(ceil(($numPhotos + $numAlbums) / $perPage), 1);

if ($page > $maxPages) {
	$page = $maxPages;
}

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

$bordercolor = $gallery->album->fields["bordercolor"];

$imageCellWidth = floor(100 / $cols) . "%";

$navigator["page"] = $page;
$navigator["pageVar"] = "page";
$navigator["maxPages"] = $maxPages;
$navigator["url"] = makeAlbumUrl($gallery->session->albumName);
$navigator["spread"] = 5;
$navigator["bordercolor"] = $bordercolor;

$breadcrumb["text"] = returnToPathArray($gallery->album, false);
$breadcrumb["bordercolor"] = $bordercolor;

$adminText = '';
$albums_str = gTranslate('core', "1 sub-album", "%d sub-albums", $numAlbums, "No albums", true);
$imags_str = gTranslate('core', "1 image", "%d images", $numPhotos, "no images", true);
$pages_str = gTranslate('core', "1 page", "%d pages", $maxPages, "0 pages", true);

if ($numAlbums && $maxPages > 1) {
	$adminText .= sprintf(gTranslate('core', "%s and %s in this album on %s."),
	$albums_str, $imags_str, $pages_str);
} else if ($numAlbums) {
	$adminText .= sprintf(gTranslate('core', "%s and %s in this album."),
	$albums_str, $imags_str);
} else if ($maxPages > 1) {
	$adminText .= sprintf(gTranslate('core', "%s in this album on %s."),
	$imags_str, $pages_str);
} else {
	$adminText .= sprintf(gTranslate('core', "%s in this album."),
	$imags_str);
}

if ($gallery->user->canWriteToAlbum($gallery->album) && !$gallery->session->offline) {
    $hidden = $gallery->album->numHidden();
    if($hidden > 0) {
	$adminText .= ' '. gTranslate('core', "%s element is hidden.", "%s elements are hidden.", $hidden, '', true);
    }
}

/* admin items for drop-down menu */
$adminOptions = array(
	'add_photos'	=> array(
		'name' 	=> gTranslate('core', "Add photos"),
		'requirements' => array('canAddToAlbum'),
		'action' 	=> 'popup',
		'value' 	=> makeGalleryUrl('add_photos_frame.php',
			array('set_albumName' => $gallery->session->albumName, 'type' => 'popup'))
	),
	'delete_root_album'    => array(
		'name' => gTranslate('core', "Delete this album"),
		'requirements' => array('canDeleteAlbum', 'albumIsRoot'),
		'action' => 'popup',
		'value' => makeGalleryUrl('delete_album.php', array('type' => 'popup'))
	),
	'delete_sub_album'    => array(
		'name' => gTranslate('core', "Delete this (sub)album"),
		'requirements' => array('canDeleteAlbum', '!albumIsRoot'),
		'action' => 'popup',
		'value' => makeGalleryUrl('delete_photo.php',
			array(
				'set_albumName' => $gallery->album->fields["parentAlbumName"],
				'type' => 'popup',
				'id' => $gallery->album->fields["name"],
				'albumDelete' => true))
	),
	'rename_album'    => array(
		'name' => gTranslate('core', "Rename album"),
		'requirements' => array('isAdminOrAlbumOwner'),
		'action' => 'popup',
		'value' => makeGalleryUrl('rename_album.php',
			array(
				'set_albumName' => $gallery->session->albumName,
				'type' => 'popup',
				'useLoad' => 1))
	),
	'nested_album'    => array(
		'name' => gTranslate('core', "New nested album"),
		'requirements' => array('canCreateSubAlbum', 'notOffline'),
		'action' => 'url',
		'value' => doCommand('new-album',
			array('parentName' => $gallery->session->albumName), 'view_album.php')
	),
	'edit_captions'   => array(
		'name' => gTranslate('core', "Edit captions"),
		'requirements' => array('canChangeText','notOffline'),
		'action' => 'url',
		'value' => makeGalleryUrl('captionator.php',
			array(
				'set_albumName' => $gallery->session->albumName,
				'page' => $page,
				'perPage' => $perPage))
	),
	'sort_items'	=> array(
		'name' => gTranslate('core', "Sort items"),
		'requirements' => array('canWriteToAlbum', 'photosExist'),
		'action' => 'popup',
		'value' => makeGalleryUrl('sort_album.php',
			array('set_albumName' => $gallery->session->albumName, 'type' => 'popup'))
	),
	'resize_all'      => array(
		'name' => gTranslate('core', "Resize all"),
		'requirements' => array('canWriteToAlbum', 'photosExist'),
		'action' => 'popup',
		'value' => makeGalleryUrl('resize_photo.php',
			array(
				'set_albumName' => $gallery->session->albumName,
				'index' => 'all',
				'type' => 'popup'))
	),
	'rebuild_thumbs'  => array(
		'name' => gTranslate('core', "Rebuild thumbs"),
		'requirements' => array('canWriteToAlbum', 'photosExist'),
		'action' => 'popup',
		'value' => doCommand('remake-thumbnail',
			array(
				'set_albumName' => $gallery->session->albumName,
				'index' => 'all',
				'type' => 'popup'))
	),
	'properties'      => array(
		'name' => gTranslate('core', "Properties"),
		'requirements' => array('canWriteToAlbum'),
		'action' => 'popup',
		'value' => makeGalleryUrl('edit_appearance.php',
			array('set_albumName' => $gallery->session->albumName, 'type' => 'popup'))
	),
	'rearrange' => array(
		'name' => gTranslate('core', "Rearrange items"),
		'requirements' => array('canWriteToAlbum', 'photosExist'),
		'action' => 'popup',
		'value' => makeGalleryUrl('rearrange.php',
			array('set_albumName' => $gallery->session->albumName, 'type' => 'popup'))
	),
	'permissions'     => array(
		'name' => gTranslate('core', "Permissions"),
		'requirements' => array('isAdminOrAlbumOwner'),
		'action' => 'popup',
		'value' => makeGalleryUrl('album_permissions.php',
			array('set_albumName' => $gallery->session->albumName, 'type' => 'popup'))
	),
	'poll_results'    => array(
		'name' => gTranslate('core', "Poll results"),
		'requirements' => array('isAdminOrAlbumOwner'),
		'action' => 'url',
		'value' => makeGalleryUrl('poll_results.php',
			array('set_albumName' => $gallery->session->albumName))
	),
	'poll_reset'      => array(
		'name' => gTranslate('core', "Poll reset"),
		'requirements' => array('isAdminOrAlbumOwner'),
		'action' => 'popup',
		'value' => makeGalleryUrl('reset_votes.php',
			array('set_albumName' => $gallery->session->albumName, 'type' => 'popup'))
	),
	'view_comments'   => array(
		'name' => gTranslate('core', "View comments"),
		'requirements' => array('isAdminOrAlbumOwner', 'allowComments', 'comments_enabled', 'hasComments'),
		'action' => 'url',
		'value' => makeGalleryUrl('view_comments.php',
			array('set_albumName' => $gallery->session->albumName))
	),
	'watermark_album'   => array(
		'name' => gTranslate('core', "Watermark album"),
		'requirements' => array('isAdminOrAlbumOwner','photosExist','watermarkingEnabled'),
		'action' => 'popup',
		'value' => makeGalleryUrl('watermark_album.php',
			array('set_albumName' => $gallery->session->albumName, 'type' => 'popup'))
	)
);

/* sort the drop-down array by translated name */
array_sort_by_fields($adminOptions, 'name', 'asc', true, true);

$va_javascript = '';
$adminOptionHTML = '';
$adminJavaScript = '';
/* determine which options to include in admin drop-down menu */
if (!$gallery->session->offline) {
	foreach ($adminOptions as $key => $data) {
		$enabled = true;
		while ($enabled && $test = array_shift($data['requirements'])) {
			$success = testRequirement($test);
			if (!$success) {
				$enabled = false;
			}
		}
		if ($enabled) {
			$adminOptionHTML .= "\t\t<option value=\"$key\">${data['name']}</option>\n";
			$adminJavaScript .= "adminOptions.$key = new Object;\n";
			$adminJavaScript .= "adminOptions.$key.action = \"${data['action']}\";\n";
			/* We need to pass un-html-entityified URLs to the JavaScript
			* This line effectively reverses htmlentities() */
			$decodeHtml = unhtmlentities($data['value']);
			$adminJavaScript .= "adminOptions.$key.value = \"${decodeHtml}\";\n";
		}
	}
}

$adminCommands = '';
$adminJSFrame = '';
/* build up drop-down menu and related javascript */
if (!empty($adminOptionHTML)) {
	$adminJSFrame .= "<script language=\"javascript1.2\" type=\"text/JavaScript\">\n"
	. "adminOptions = new Object;\n"
	. $adminJavaScript
	. "\nfunction execAdminOption() {\n"
	. "\tkey = document.forms.admin_options_form.admin_select.value;\n"
	. "\tdocument.forms.admin_options_form.admin_select.selectedIndex = 0;\n"
	. "\tdocument.forms.admin_options_form.admin_select.blur();\n"
	. "\tswitch (adminOptions[key].action) {\n"
	. "\tcase 'popup':\n"
	. "\t\tnw = window.open(adminOptions[key].value, 'Edit', 'height=500,width=600,location=no,scrollbars=yes,menubars=no,toolbars=no,resizable=yes');\n"
	. "\t\tnw.opener=self;\n"
	. "\t\tbreak;\n"
	. "\tcase 'url':\n"
	. "\t\tdocument.location = adminOptions[key].value;\n"
	. "\t\tbreak;\n"
	. "\t}\n"
	. "}\n"
	. "</script>\n\n";

	$iconElements[] = "\n\t<select class=\"g-admin\" name=\"admin_select\" onChange=\"execAdminOption()\">\n"
	. "\t\t<option value=\"\">&laquo; " . gTranslate('core', 'Album Actions') . " &raquo;</option>\n"
	. $adminOptionHTML
	. "\t</select>\n";
}

if ($gallery->album->fields["slideshow_type"] != "off" &&
($numPhotos != 0 ||
($numVisibleItems != 0 && $gallery->album->fields['slideshow_recursive'] == "yes"))) {
	$iconElements[] = galleryLink(
		makeGalleryUrl("slideshow.php", array("set_albumName" => $albumName)),
		gTranslate('core', "_slideshow"),
		array(),
		'display.gif'
	);
}

/* User is allowed to view ALL comments */
if ( $numVisibleItems != 0 &&
($gallery->app->comments_enabled == 'yes' && $gallery->album->lastCommentDate("no") != -1) &&
((isset($gallery->app->comments_overview_for_all) && $gallery->app->comments_overview_for_all == "yes") ||
$gallery->user->canViewComments($gallery->album))) {
	$iconElements[] = galleryLink(
		makeGalleryUrl( "view_comments.php", array("set_albumName" => $gallery->session->albumName)),
		gTranslate('core', "view&nbsp;_comments"),
		array(),
		'presentation.png'
	);
}

$logoutReturn = doCommand("logout", array() ,'view_album.php',
	array('"page' => $page, 'set_albumName' => $albumName));
$iconElements[] = LoginLogoutButton($logoutReturn);

$adminbox["text"] = $adminText;
$adminbox["commands"] =	$adminCommands . makeIconMenu($iconElements, 'right');
$adminbox["bordercolor"] = $bordercolor;

if (!empty($adminOptionHTML)) {
	$va_javascript .= $adminJSFrame;
}

// -- if borders are off, just make them the bgcolor ----
$borderwidth = $gallery->album->fields["border"];

if (($gallery->album->getPollType() == "rank") && canVote()) {
	$my_choices = array();
	if ( $gallery->album->fields["votes"]) {
		foreach ($gallery->album->fields["votes"] as $id => $image_votes) {
			$index = $gallery->album->getIndexByVotingId($id);
			if ($index < 0) {
				// image has been deleted!
				unset($gallery->album->fields["votes"][$id]);
				continue;
			}
			if (isset($image_votes[getVotingID()])) {
				$my_choices[$image_votes[getVotingID()]] = $id;
			}
		}
	}
	if (sizeof($my_choices) == 0 && $gallery->album->getVoterClass() ==  "Logged in") {
		$va_poll_box1 = gTranslate('core', "You have currently no votes recorded for this poll.");
	}
	else if (sizeof($my_choices) > 0) {
		ksort($my_choices);
		$nv_pairs = $gallery->album->getVoteNVPairs();

		$va_poll_box1 = gTranslate('core', "Your votes in the current session are:");

		$pollInfoTable = new galleryTable();
		foreach ($my_choices as $key => $id) {
			$index = $gallery->album->getIndexByVotingId($id);

			$pollInfoTable->addElement(array('content' => $nv_pairs[$key]["name"]));
			$pollInfoTable->addElement(array('content' => ':'));
			if ($gallery->album->isAlbum($index)) {
				$albumName = $gallery->album->getAlbumName($index);
				$myAlbum = new Album();
				$myAlbum->load($albumName);

				$pollInfoTable->addElement(array('content' =>
                    		  galleryLink(
					makeAlbumUrl($albumName), 
					sprintf(gTranslate('core', "Album: %s"), $myAlbum->fields['title']))
                ));
			} else {
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

$results = 1;
if ($gallery->album->getPollShowResults()) {
    list($va_poll_result, $results) = showResultsGraph( $gallery->album->getPollNumResults());
    if(!empty($va_poll_result) && $results && testRequirement('isAdminOrAlbumOwner')) {
        $va_poll_result .= galleryLink(
          makeGalleryUrl("poll_results.php", array("set_albumName" => $gallery->session->albumName)),
          gTranslate('core', "See full poll results")
        );
    }
}

if (canVote()) {
	$nv_pairs = $gallery->album->getVoteNVPairs();
	if ($gallery->album->getPollScale() == 1) {
		$options = $nv_pairs[0]["name"];
	}
	else {
		/** note to translators:
         * This produces (in English) a list of the form: "a, b, c or d".  Correct translation
         * of ", " and " or  " should produce a version that makes sense in your language.
         */
		$options = '';
		for ($count=0; $count < $gallery->album->getPollScale()-2 ; $count++) {
			$options .= $nv_pairs[$count]["name"] .gTranslate('core', ", ");
		}
		$options .= $nv_pairs[$count++]["name"] .gTranslate('core', " or ");
		$options .= $nv_pairs[$count]["name"];
	}

	$va_poll_box3 = sprintf(gTranslate('core', "To vote for an image, click on %s."), $options);
	$va_poll_box3 .= ' ';
	$va_poll_box3 .= sprintf(gTranslate('core', "You MUST click on %s for your vote to be recorded."), "<b>".gTranslate('core', "Vote")."</b>");
	$va_poll_box3 .= ' ';
	if ($gallery->album->getPollType() == 'rank') {
		$voteCount = $gallery->album->getPollScale();
		$va_poll_box3 .= gTranslate('core',
			"You have a total of %d vote and can change it later if you wish.",
			"You have a total of %d votes and can change them later if you wish.", $voteCount, '', true);
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
if ($numPhotos) {
	$rowCount = 0;

	/* Find the correct starting point, accounting for hidden photos */
	$rowStart = 0;
	$cnt = 0;
	$form_pos=0; // counts number of images that have votes below, ie withou albums;
	$rowStart = $start;

	$albumItems = array();
	while ($rowCount < $rows) {
	    /* Do the inline_albumthumb header row */
	    $visibleItemIndex = $rowStart;
	    $i = $visibleItemIndex<=$numVisibleItems ? $visibleItems[$visibleItemIndex] : $numPhotos+1;
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
			//-- put some parameters for the wrap files in the global object ---
			$gallery->html_wrap['borderColor'] = $bordercolor;
			$borderwidth = $gallery->html_wrap['borderWidth'] = $borderwidth;

			if ($gallery->album->isAlbum($i)) {
				$scaleTo = 0; //$gallery->album->fields["thumb_size"];
				$myAlbum = $gallery->album->getNestedAlbum($i);
				list($iWidth, $iHeight) = $myAlbum->getHighlightDimensions($scaleTo);
			} else {
				unset($myAlbum);
				$scaleTo = 0;  // thumbs already the right
				//	size for this album
				list($iWidth, $iHeight) = $gallery->album->getThumbDimensions($i, $scaleTo);
			}
			if ($iWidth == 0) {
				$iWidth = $gallery->album->fields["thumb_size"];
			}
			if ($iHeight == 0) {
				$iHeight = 100;
			}

			$gallery->html_wrap['imageWidth'] = $iWidth;
			$gallery->html_wrap['imageHeight'] = $iHeight;

			$id = $gallery->album->getPhotoId($i);
			if ($gallery->album->isMovieByIndex($i)) {
				$gallery->html_wrap['imageTag'] = $gallery->album->getThumbnailTag($i);
				$gallery->html_wrap['imageHref'] = makeAlbumUrl($gallery->session->albumName, $id);
				$frame= $gallery->html_wrap['frame'] = $gallery->album->fields['thumb_frame'];
				$gallery->html_wrap['type'] = 'inline_moviethumb.frame';

				list($divCellWidth, $divCellHeight, $padding) = calcVAdivDimension($frame, $iHeight, $iWidth, $borderwidth);
				// If there is only one column, we don't need to try and match row heights
				if ($cols == 1) {
					$padding = 0;
				}
			}
			elseif (isset($myAlbum)) {
				// We already loaded this album - don't do it again, for performance reasons.

				$gallery->html_wrap['imageTag'] = $myAlbum->getHighlightTag(
					$scaleTo,
					array('alt' => sprintf(gTranslate('core', "Highlight for album: %s"),
						gallery_htmlentities(strip_tags($myAlbum->fields['title']))))
					);
				$gallery->html_wrap['imageHref'] = makeAlbumUrl($gallery->album->getAlbumName($i));
				$frame = $gallery->html_wrap['frame'] = $gallery->album->fields['album_frame'];
				$gallery->html_wrap['type'] = 'inline_albumthumb.frame';

				list($divCellWidth,$divCellHeight, $padding) = calcVAdivDimension($frame, $iHeight, $iWidth, $borderwidth);
			}
			else {
				$gallery->html_wrap['imageTag'] = $gallery->album->getThumbnailTag($i);
				$gallery->html_wrap['imageHref'] = makeAlbumUrl($gallery->session->albumName, $id);
				$frame = $gallery->html_wrap['frame'] = $gallery->album->fields['thumb_frame'];
				$gallery->html_wrap['type'] = 'inline_photothumb.frame';

				list($divCellWidth,$divCellHeight, $padding) = calcVAdivDimension($frame, $iHeight, $iWidth, $borderwidth);
			}
			++$nr;
            $gallery->html_wrap['style'] = "style=\"padding-top: {$padding}px; padding-bottom:{$padding}px; width: {$divCellWidth}px; height: {$divCellHeight}px;\"";
            $albumItems[$nr]['thumb'] = $gallery->html_wrap;

			if (canVote()){
				if ($gallery->album->fields["poll_type"] == 'rank' && $divCellWidth < 200) {
					$divCellWidth = 200;
				}
			}

			/* Do the clickable-dimensions line */
			if ($gallery->album->fields['showDimensions'] == 'yes') {
				$photo    = $gallery->album->getPhoto($i);
				$image    = $photo->image;
				if (!empty($image) && !$photo->isMovie()) {
				    $albumItems[$nr]['dimensions'] = '';
					$viewFull = $gallery->user->canViewFullImages($gallery->album);
					$fullOnly = (isset($gallery->session->fullOnly) &&
					  !strcmp($gallery->session->fullOnly, 'on') &&
					  !strcmp($gallery->album->fields['use_fullOnly'], 'yes'));
					list($wr, $hr) = $image->getDimensions();
					list($wf, $hf) = $image->getRawDimensions();
					/* display file sizes if dimensions are identical */
					if ($wr == $wf && $hr == $hf && $viewFull && $photo->isResized()) {
						$fsr = ' ' . sprintf(gTranslate('core', '%dkB'), (int) $photo->getFileSize(0) >> 10);
						$fsf = ' ' . sprintf(gTranslate('core', '%dkB'), (int) $photo->getFileSize(1) >> 10);
					} else {
						$fsr = '';
						$fsf = '';
					}

					if (($photo->isResized() && !$fullOnly) || !$viewFull) {
						$albumItems[$nr]['dimensions'] = galleryLink(
    						makeAlbumUrl($gallery->session->albumName, $image->name),
    						"[${wr}x{$hr}${fsr}] "
						);
					}

					if ($viewFull) {
					    $albumItems[$nr]['dimensions'] .= galleryLink(
    						makeAlbumUrl($gallery->session->albumName, $image->name, array('full' => 1)),
						    "[${wf}x${hf}${fsf}]"
						 );
					}
				}
			}

			/* Now do the caption line */
			$albumItems[$nr]['note'] = '';
			if ($gallery->album->isAlbum($i)) {
				$myAlbum = new Album;
				$myAlbum->load($gallery->album->getAlbumName($i));
			}
			else {
				$myAlbum = NULL;
			}

			if ($gallery->album->isAlbum($i)) {
				$iWidth = $gallery->album->fields['thumb_size'];
			} else {
				list($iWidth, $iHeight) = $gallery->album->getThumbDimensions($i);
			}

			$id = $gallery->album->getPhotoId($i);
			if ($gallery->album->isHidden($i) && !$gallery->session->offline) {
				$albumItems[$nr]['note'] .= "(" . gTranslate('core', "hidden") .")<br>";
			}
			$photo = $gallery->album->getPhoto($i);
			if ($gallery->user->canWriteToAlbum($gallery->album) &&
			  $photo->isHighlight() && !$gallery->session->offline) {
				$albumItems[$nr]['note'] .= "(" . gTranslate('core', "highlight") .")<br>";
			}

            $albumItems[$nr]['infos'] = array();
			/* Album */
			if (isset($myAlbum)) {
				// Caption itself
				$myDescription = $myAlbum->fields['description'];
				$buf = '';
				$link = '';

				if ($gallery->user->canDownloadAlbum($myAlbum) && $myAlbum->numPhotos(1)) {
					$iconText = gImage('icons/compressed.png', gTranslate('core', "Download entire album as archive"));
					$link = popup_link(
						$iconText,
						'download.php?set_albumName='. $gallery->album->getAlbumName($i),
						false, true, 500, 500, '', '', '', false, false
					);
				}

				$buf .="<b>";
				$buf .= sprintf(gTranslate('core', "Album: %s"),
					'<a href="'. makeAlbumUrl($gallery->album->getAlbumName($i)) .'">'. $myAlbum->fields['title'] .'</a>');
				$buf .= "</b> $link";
				if (!empty($myDescription) &&
				  $myDescription != gTranslate('core', "No description") &&
				  $myDescription != "No description") {
					$buf .= '<br>'. $myDescription;
				}

				$albumItems[$nr]['caption'] = $buf;

				$albumItems[$nr]['infos'][] =
				    sprintf (gTranslate('core', "Last change: %s"), $myAlbum->getLastModificationDate());

				$visItems = array_sum($myAlbum->numVisibleItems($gallery->user));
				$contains = gTranslate('core',"Contains: One Item","Contains %d items", $visItems, '', true) .'. ';
				// If comments indication for either albums or both
				switch ($gallery->app->comments_indication) {
					case "albums":
					case "both":
						$lastCommentDate = $myAlbum->lastCommentDate($gallery->app->comments_indication_verbose);
						if ($lastCommentDate > 0) {
							$contains .= lastCommentString($lastCommentDate, $displayCommentLegend);
						}
						break;
				}

				$albumItems[$nr]['infos'][] = $contains;

				if ($gallery->album->fields["display_clicks"] == 'yes' &&
				  !$gallery->session->offline &&
				  $myAlbum->getClicks() > 0) {
					$albumItems[$nr]['clickcounter'] = gTranslate('core', "Viewed: Once.", "Viewed: %d times.", $myAlbum->getClicks(), '', true);
				}
			}
			/* Photo or Movie */
			else {
                $albumItems[$nr]['caption'] = nl2br($gallery->album->getCaption($i));
                $albumItems[$nr]['caption'] .= $gallery->album->getCaptionName($i) . ' ';
				// indicate with * if we have a comment for a given photo
				if ($gallery->user->canViewComments($gallery->album) &&
				  $gallery->app->comments_enabled == 'yes') {
					// If comments indication for either photos or both
					switch ($gallery->app->comments_indication) {
						case "photos":
						case "both":
							$lastCommentDate = $gallery->album->itemLastCommentDate($i);
							$albumItems[$nr]['caption'] .=
                                lastCommentString($lastCommentDate, $displayCommentLegend);
							break;
					}

				}

				if ($gallery->album->fields["display_clicks"] == 'yes' &&
				  !$gallery->session->offline &&
				  $gallery->album->getItemClicks($i) > 0) {
					$albumItems[$nr]['clickcounter'] = gTranslate('core', "Viewed: 1 time.", "Viewed: %d times.", $gallery->album->getItemClicks($i), '', true);
				}
			}
			// End Caption

			if (canVote()) {
				$albumItems[$nr]['voting'] =
				    addPolling($gallery->album->getVotingIdByIndex($i), $form_pos, false);
		                $form_pos++;
			}

			list($albumItemOptions, $javascript) = getItemActions($i, false, true);
 			if(!empty($javascript)) {
				$va_javascript .= $javascript;
			}
			if (sizeof($albumItemOptions) > 2 ||
			  (sizeof($albumItemOptions) == 2 && !isset($albumItemOptions['showExif']))) {
				$albumItems[$nr]['options'] = drawSelect2("s$i", $albumItemOptions, array(
    				'onChange' => "imageEditChoice(this)",
    				'class' => 'g-admin'));
			}

			$j++;
			$visibleItemIndex++;
			$i = $visibleItemIndex<=$numVisibleItems ? $visibleItems[$visibleItemIndex] : $numPhotos+1;
		}

		/* Now do the inline_albumthumb footer row */
		$visibleItemIndex = $rowStart;
		$i = $visibleItemIndex <= $numVisibleItems ? $visibleItems[$visibleItemIndex] : $numPhotos+1;
		$j = 1;

		while ($j <= $cols && $i <= $numPhotos) {
			$j++;
			$visibleItemIndex++;
			$i = $visibleItemIndex<=$numVisibleItems ? $visibleItems[$visibleItemIndex] : $numPhotos+1;
		}

		$rowCount++;
		$rowStart = $visibleItemIndex;
	}
}
else {
    if ($gallery->user->canAddToAlbum($gallery->album) && !$gallery->session->offline) {
        $url = makeGalleryUrl(
            'add_photos_frame.php',
            array('set_albumName' => $gallery->session->albumName, 'type' => 'popup')
        );
        $va_notice = popup_link(gTranslate('core', "Hey! Add some photos."), $url, 1, true, 500, 600, 'admin');
    } else {
        $va_notice = gTranslate('core', "This album is empty.");

    }
}

define('READY_TO_INCLUDE', 'DISCO');
require(dirname(__FILE__) .'/templates/album.tpl.default');

?>
