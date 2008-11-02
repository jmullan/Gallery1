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
require_once(dirname(__FILE__) . '/includes/stats/stats.inc.php');

if (empty($gallery->session->username)) {
	/* Get the cached version if possible */
	$cache_file = "cache.html";
	if (!getRequestVar('gallery_nocache')) {
		$cache_now = time();
		$cacheFileBaseNames = array(sprintf("cache-%s.html", $_SERVER['HTTP_HOST']), "cache.html");
		foreach ($cacheFileBaseNames as $cache_file_basename) {
			$cache_file = dirname(__FILE__) . '/' . $cache_file_basename;
			if (fs_file_exists($cache_file)) {
				$cache_stat = @stat($cache_file);
				if ($cache_now - $cache_stat[9] < (20 * 60)) {
					if ($fp = fopen($cache_file, "rb")) {
						while (!feof($fp)) {
							print fread($fp, 4096);
						}
						fclose($fp);
						printf("<!-- From %s, created at %s -->",
						$cache_file_basename, strftime("%D %T", $cache_stat[9]));
						return;
					}
				}
			}
		}
	}
}

$gallery->session->offlineAlbums["albums.php"] = true;

$g_theme = $gallery->app->theme;

/* Read the album list */
$albumDB = new AlbumDB(FALSE);

if(! $albumDB->isInitialized()) {
	exit;
}

$galleryTitle = clearGalleryTitle();

$gallery->session->albumName = '';
$page = 1;

/* If there are albums in our list, display them in the table */
list ($numPhotos, $numAccess, $numAlbums) = $albumDB->numAccessibleItems($gallery->user);

if (empty($gallery->session->albumListPage) || $gallery->session->albumListPage < 1) {
	$gallery->session->albumListPage = 1;
}

$perPage = $gallery->app->albumsPerPage;
$maxPages = max(ceil($numAlbums / $perPage), 1);

if ($gallery->session->albumListPage > $maxPages) {
	$gallery->session->albumListPage = $maxPages;
}

$borderColor = $gallery->app->default['bordercolor'];

$navigator['page']		= $gallery->session->albumListPage;
$navigator['pageVar']		= 'set_albumListPage';
$navigator['url']		= makeGalleryUrl('albums.php');
$navigator['maxPages']		= $maxPages;
$navigator['spread']		= 6;
$navigator['fullWidth']		= 100;
$navigator['widthUnits']	= '%';
$navigator['bordercolor']	= $borderColor;

$currentUrl = makeGalleryUrl("albums.php", array("page" => $gallery->session->albumListPage));

// this determines if we display "* Item contains a comment" at end of page
$displayCommentLegend = 0;

/* Admin Text (left side) */
$adminText = '';
if ($numAccess == $numAlbums) {
	$toplevel_str = gTranslate(
		'core',
		"1 album","%d albums",
		$numAlbums,
		gTranslate('core', "No albums"), true
	);
}
else {
	$toplevel_str = gTranslate(
		'core',
		"1 top-level album",
		"%d top-level albums",
		$numAlbums,
		gTranslate('core', "No top-level albums"), true
	);
}

$total_str	= sprintf(gTranslate('core', "%d total"), $numAccess);
$image_str	= gTranslate('core', "1 image", "%d images", $numPhotos, gTranslate('core', "no images"), true);
$page_str	= gTranslate('core', "1 page", "%d pages", $maxPages, gTranslate('core', "no pages"), true);

if ($numAccess != $numAlbums && $maxPages > 1) {
	$adminText .= sprintf(gTranslate('core',"%s (%s), %s on %s"),
		$toplevel_str,
		$total_str,
		$image_str, $page_str
	);
}
else if ($numAccess != $numAlbums) {
	$adminText .= sprintf(gTranslate('core', "%s (%s), %s"), $toplevel_str, $total_str, $image_str);
}
else if ($maxPages > 1) {
	$adminText .= sprintf(gTranslate('core', "%s, %s on %s"), $toplevel_str, $image_str, $page_str);
}
else {
	$adminText .= sprintf(gTranslate('core', "%s, %s"), $toplevel_str, $image_str);
}

if (!empty($gallery->app->stats_foruser) && $numPhotos != 0) {
	$adminText .= "\n<br>". generateStatsLinks();
}

/* Admin texts  */

$iconElements = array();

if($gallery->app->theme == 'classic_sidebar' && $gallery->app->useIcons == 'both') {
	$specialIconMode = 'lined';
}

if ($gallery->user->isLoggedIn() && !$gallery->session->offline) {
	$displayName = $gallery->user->displayName();
	$gallery_welcome = sprintf(gTranslate('core', "Welcome, %s"), $displayName) . "&nbsp;&nbsp;<br>";
}
else {
	$gallery_welcome = '';
}

if ($gallery->app->gallery_slideshow_type != "off" && $numPhotos != 0) {
	$iconElements[] = galleryLink(
		makeGalleryUrl("slideshow.php", array("set_albumName" => null)),
		gTranslate('core', "Slidesho_w"), array(), 'monitor.png', true
	);
}

if ($gallery->user->canCreateAlbums() && !$gallery->session->offline) {
	$iconElements[] = galleryLink(
		doCommand("new-album", array(), "view_album.php"),
		gTranslate('core', "New _album"), array(), 'folder_new.png', true
	);
}

$loggedIn = ($gallery->user->isLoggedIn() && !$gallery->session->offline);

if ($loggedIn) {
	if ($gallery->user->isAdmin()) {
		$linkurl = makeGalleryUrl('administer_startpage.php', array('type' => 'popup'));
		$iconElements[] = popup_link(
			gTranslate('core', "Administer fron_tpage"),
			$linkurl, true, true, 550, 600, '', '', 'text_list_numbers.png'
		);

		$iconElements[] = galleryLink(
			makeGalleryUrl('admin-page.php'),
			gTranslate('core', "Ad_min page"), array(), 'cog.png', true
		);

	}

	if ($gallery->userDB->canModifyUser()) {
		$iconElements[] = popup_link(
			gTranslate('core', "Pr_eferences"),
			'user_preferences.php', false, true, 500, 500, '','','preferences.gif'
		);
	}
}

$iconElements[] = languageSelector();
$iconElements[] = LoginLogoutButton($currentUrl, $numPhotos, $currentUrl);

if (!$loggedIn && !$GALLERY_EMBEDDED_INSIDE && $gallery->app->selfReg == 'yes') {
	$iconElements[] = popup_link(
		gTranslate('core', "_Register"),
		'register.php', false, true, 500, 500, '','','register.gif'
	);
}

$specialIconMode = '';

/**
 * Searchfield and when inside phpBB2 a link back to home
 */
$searchBar = '';
if (!$gallery->session->offline &&
	(($gallery->app->showSearchEngine == 'yes' && $numPhotos != 0) ||
	 $GALLERY_EMBEDDED_INSIDE == 'phpBB2'))
{

	$searchBar = "\n". '<table class="g-searchbar">';
	$searchBar.= "\n<tr>";

	if ($GALLERY_EMBEDDED_INSIDE == 'phpBB2') {
		$searchBar .= "\n  ". '<td class="left">'.
		'<a href="index.php">'. sprintf($lang['Forum_Index'], $board_config['sitename']) . '</a></td>';
	}

	if ($numPhotos != 0) {
		$searchBar .= "\n  ". '<td class="right">'. addSearchForm() .'  </td>';
	}

	$searchBar .= "\n</tr>";
	$searchBar .= "\n</table>";
}

$notice_caption		= '';
$notice_messages	= array();

/* Generate warnings about broken albums */
if ($gallery->user->isAdmin() &&
   (sizeof($albumDB->brokenAlbums) || sizeof($albumDB->outOfDateAlbums))) {
	$notice_caption = gTranslate('core', "Attention Gallery Administrator!");

	if (sizeof($albumDB->brokenAlbums)) {
		$message = sprintf(
			gTranslate('core',
				"%s has detected one invalid folders in your albums directory<br>(%s):",
				"%s has detected the following invalid folders in your albums directory<br>(%s):",
				sizeof($albumDB->brokenAlbums)),
			Gallery(), $gallery->app->albumDir
		);

		$message .= "\n<ul>";
		foreach ($albumDB->brokenAlbums as $tmpAlbumName) {
			$message .= "<li>$tmpAlbumName\n";
		}

		$message .= "\n</ul>";
		$message .= gTranslate('core',
			"Please move it out of the albums directory.",
			"Please move them out of the albums directory.",
			sizeof($albumDB->brokenAlbums)
		);

		$notice_messages[] = array(
			'type' => 'information',
			'text' => $message
		);
	}

	if(sizeof($albumDB->outOfDateAlbums)) {
		$message = gTranslate('core',
			"Gallery has detected that one of your albums is out of date.",
			"Gallery has detected that %d of your albums are out of date.",
			sizeof($albumDB->outOfDateAlbums), '', true
		);

		$message .= "\n<br>";
		$message .= sprintf(gTranslate('core', "Please %s."),
			popup_link(
				gTranslate('core', "perform an upgrade"),
				"upgrade_album.php", 0, 0, 550, 600, 'g-error', '', '', false)
		);

		$notice_messages[] = array(
			'type' => 'warning',
			'text' => $message
		);
	}
}

if (getRequestVar('gRedir') == 1 && ! $gallery->session->gRedirDone) {
	$message = sprintf(gTranslate('core', "The album or photo that you were attempting to view either does not exist, or requires user privileges that you do not possess. %s"),
		($gallery->user->isLoggedIn() && !$GALLERY_EMBEDDED_INSIDE ? '' : sprintf(gTranslate('core', "Login at the %s and try again."),
		galleryLink('login.php', gTranslate('core', "Login page"))))
	);

	$notice_messages[] = array(
		'type' => 'error',
		'text' => $message
	);

	$gallery->session->gRedirDone = true;
}

$rootAlbum = array();

$start = ($gallery->session->albumListPage - 1) * $perPage + 1;
$end = min($start + $perPage - 1, $numAlbums);

for ($i = $start; $i <= $end; $i++) {
	if(!$gallery->album = $albumDB->getAlbum($gallery->user, $i)) {
		$notice_messages[] = array(
			'type' => 'error',
			'text' => sprintf(gTranslate('core', "The requested album with index %s is not valid."), $i)
		);
		continue;
	}

	$isRoot = $gallery->album->isRoot(); // Only display album if it is a root album
	if($isRoot) {
		$tmpAlbumName = $gallery->album->fields["name"];
		$albumURL = makeAlbumUrl($tmpAlbumName);
		$scaleTo = $gallery->app->highlight_size;

		$rootAlbum[$tmpAlbumName]['url'] = $albumURL;

		$imageTag = $gallery->album->getHighlightTag($scaleTo);

		list($iWidth, $iHeight) = $gallery->album->getHighlightDimensions($scaleTo);

		if (empty($iWidth)) {
			$iWidth = $gallery->app->highlight_size;
			$iHeight = 100;
		}

		// <!-- Begin Album Column Block -->
		// <!-- Begin Image Cell -->
		$gallery->html_wrap['borderColor']	= $borderColor;
		$gallery->html_wrap['borderWidth']	= 1;

		$gallery->html_wrap['imageWidth']	= $iWidth;
		$gallery->html_wrap['imageHeight']	= $iHeight;
		$gallery->html_wrap['imageTag']		= $imageTag;
		$gallery->html_wrap['imageHref']	= $albumURL;
		$gallery->html_wrap['frame']		= $gallery->app->gallery_thumb_frame_style;

		$rootAlbum[$tmpAlbumName]['imageCell'] = $gallery->html_wrap;
		// <!-- End Image Cell -->

		// <!-- Begin Text Cell -->
		$rootAlbum[$tmpAlbumName]['albumdesc']['title'] = editField($gallery->album, "title", $albumURL);

		if ($gallery->user->canDownloadAlbum($gallery->album) && $gallery->album->numPhotos(1)) {
			$rootAlbum[$tmpAlbumName]['albumdesc']['title'] .= ' '. popup_link(
				gImage('icons/compressed.gif', gTranslate('core', "Download entire album as archive")),
				"download.php?set_albumName=$tmpAlbumName",
				false, false, 550, 600, 'g-small', '', '',
				false, false
			);
		}

		/* Admin album Commands */
		$selectBoxCaption = ($g_theme == 'matrix') ? true : false;
		$specialIconMode = 'no';
		$rootAlbum[$tmpAlbumName]['albumdesc']['adminRootAlbumCommands'] = getAlbumCommands($gallery->album, $selectBoxCaption);
		$specialIconMode = '';

		/* Description */
		$rootAlbum[$tmpAlbumName]['albumdesc']['description'] = editField($gallery->album, "description") ;

		/* Owner */
		if ($gallery->app->showOwners == 'yes') {
			$owner = $gallery->album->getOwner();
			$rootAlbum[$tmpAlbumName]['albumdesc']['owner'] =
			sprintf(gTranslate('core', "Owner: %s"),showOwner($owner));
		}

		/* URL (only for admins and owner) */
		if ($gallery->user->isAdmin() || $gallery->user->isOwnerOfAlbum($gallery->album)) {

			$rootAlbum[$tmpAlbumName]['albumdesc']['url'] =
				gTranslate('core', "URL:") . '<a href="'. $albumURL . '">';

				if (!$gallery->session->offline) {
				$rootAlbum[$tmpAlbumName]['albumdesc']['url'] .=
				breakString(urldecode($albumURL), 60, '&', 5);
			}
			else {
				$rootAlbum[$tmpAlbumName]['albumdesc']['url'] .= $tmpAlbumName;
			}

			$rootAlbum[$tmpAlbumName]['albumdesc']['url'] .= '</a>';

			if (ereg("album[[:digit:]]+$", $tmpAlbumName)) {
				if (!$gallery->session->offline) {
					$rootAlbum[$tmpAlbumName]['albumdesc']['url'] .= infoBox(array(array(
						'text' => gTranslate('core', "Hey!") .
							sprintf(gTranslate('core', "%s so that the URL is not so generic and easy guessable!"),
							popup_link(
								gTranslate('core', "Change the foldername of this album"),
								"rename_album.php?set_albumName={$tmpAlbumName}&index=$i",
								0,0,500,500,'', '','' ,false)
							),
						'type' => 'warning'))
					);
				}
			}
		}

		/* Created / Last Changed */
		$creationDate = $gallery->album->getCreationDate();
		$lastModifiedDate = $gallery->album->getLastModificationDate();

		if($creationDate) {
			$rootAlbum[$tmpAlbumName]['albumdesc']['changeDate'] =
				sprintf(gTranslate('core', "Created on %s, last changed on %s."),
					$creationDate,
					$lastModifiedDate
				);
		}
		else {
			$rootAlbum[$tmpAlbumName]['albumdesc']['changeDate'] =
			sprintf(gTranslate('core', "Last changed on %s."), $lastModifiedDate);
		}

		/* Amount of items */
		list($visibleItems) = $gallery->album->numItems($gallery->user, true);

		$rootAlbum[$tmpAlbumName]['albumdesc']['numItems'] =
			gTranslate('core',
				"This album contains 1 item.",
				"This album contains %d items.",
				$visibleItems,
				gTranslate('core', "This album is empty."),
				true
			);

		/* Click counter + reset for it */
		if (!($gallery->album->fields["display_clicks"] == 'no') && !$gallery->session->offline) {
			$clickCount = $gallery->album->getClicks();
			$resetDate = $gallery->album->getClicksDate();

			$rootAlbum[$tmpAlbumName]['albumdesc']['clickCounter'] =
				sprintf(gTranslate('core',
					"This album has been viewed %d time since %s.",
					"This album has been viewed %d times since %s.",
					$clickCount,
					sprintf(gTranslate('core', "This album has never been viewed since %s."), $resetDate)
					),
					$clickCount, $resetDate
				);
		}

		if ($gallery->user->canWriteToAlbum($gallery->album) &&
		(!($gallery->album->fields["display_clicks"] == "no"))) {
			$rootAlbum[$tmpAlbumName]['albumdesc']['clickCounter'] .= ' '. popup_link(
				gTranslate('core', "reset counter"),
				doCommand("reset-album-clicks", array("set_albumName" => $tmpAlbumName), "albums.php"), 1);
		}

		/* Comment Indicator */
		if($gallery->app->comments_enabled == 'yes') {
			// if comments_indication are "albums" or "both"
			switch ($gallery->app->comments_indication) {
				case "albums":
				case "both":
					$lastCommentDate = $gallery->album->lastCommentDate($gallery->app->comments_indication_verbose);
					$rootAlbum[$tmpAlbumName]['albumdesc']['commentIndication'] =
						lastCommentString($lastCommentDate, $displayCommentLegend);
					break;
			}
		}

		// End Album Infos

		// Start tree
		if (isset($gallery->app->albumTreeDepth) &&
			$gallery->app->albumTreeDepth > 0 &&
			$g_theme != 'matrix')
		{
			$subalbumTree = createTreeArray($tmpAlbumName,$depth = 0);
			if(!empty($subalbumTree)) {
				if (isset($gallery->app->microTree) && $gallery->app->microTree == 'yes') {
					$rootAlbum[$tmpAlbumName]['albumdesc']['microthumbs'] = printMicroChildren2($subalbumTree);
					$rootAlbum[$tmpAlbumName]['albumdesc']['subalbumTree'] = '&nbsp;';
				}
				else {
					$dynsubalbumTree = true;
					$rootAlbum[$tmpAlbumName]['subalbumTree'] = true;
					$rootAlbum[$tmpAlbumName]['albumdesc']['subalbumTree'] = getYUIHtmlTree($subalbumTree);
				}
			}
		}
	}
}

if(!fs_file_exists(GALLERY_BASE . "/templates/$g_theme/gallery.tpl.default")) {
	$g_theme = 'classic';
}

define('READY_TO_INCLUDE', 'DISCO');
$templateFile = getDefaultFilename(GALLERY_BASE ."/templates/$g_theme/gallery.tpl");

require($templateFile);

?>