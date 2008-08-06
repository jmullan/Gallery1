<?php
/**
 * Stats is a Gallery mod that allows photo statistics to be displayed.
 * Use the url variable "type" to select the statistic to display.
 *
 *   - number of views (clicks) - use stats.php?type=views
 *   - upload date - use stats.php?type=date
 *   - number of votes (stats votes) - use stats.php?type=votes
 *   - ratings (Gallery votes) - use stats.php?type=ratings
 *   - capture date - use stats.php?type=cdate
 *   - latest comments - use stats.php?type=comments
 *   - random images - use stats.php?type=random
 *
 * The photos displayed can be restricted by their upload date.
 * Use stats.php?type=date&period=<x> where x is the number of months
 * that you want to display. For example:
 *   stats.php?type=date&period=0 displays all photos uploaded during
 *	   the current month.
 *   stats.php?type=views?period=1 displays the number of views (clicks)
 *	   for all photos uploaded during the last month and the current month
 *	   stats.php?type=date&period=6 covers the last 6 months.
 *
 * The images listed can be restricted to one album by using the album
 * parameter. For example:
 *   stats.php?type=date&album=new displays only images from the new album.
 *
 * More details are available in the readme.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * @package Stats
 * @author Jeremy Gilliat
 * @author Haplo
 * @author Jens Tkotz
 */

/**
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (C) 2004 Jeremy Gilliat with rating contribution by Haplo
 * based on Bharat Mediratta's standard Gallery code.
 *
 * $Id$
 */

require_once(dirname(__FILE__) . '/init.php');
require_once(dirname(__FILE__) . '/includes/stats/stats.inc.php');
require_once(dirname(__FILE__) . '/lib/stats.php');

//$album="album01";

list ($type, $sca, $sal, $sde, $sco, $scd, $sud, $svi, $sac, $svo, $sav, $sao, $stm, $reverse, $tsz ,$ppp, $total, $showGrid, $rows, $cols, $addLinksPos) =
	getRequestVar(array('type', 'sca', 'sal', 'sde', 'sco', 'scd', 'sud', 'svi', 'sac', 'svo', 'sav', 'sao', 'stm', 'reverse', 'tsz' ,'ppp', 'total', 'showGrid', 'rows', 'cols', 'addLinksPos'));

list ($ty, $tm, $td) = getRequestVar(array('ty', 'tm', 'td'));

list ($page, $set_albumListPage) = getRequestVar(array('page', 'set_albumListPage'));

if (empty($type)) {
	/* We assume was called direct. So we call show defaults */
	header("Location: ". unhtmlentities(defaultStatsUrl('views')));
}

$reverse = (bool)$reverse;

/* Start of HTML Output to show page in valid HTML when debug is ON */
switch ($type) {
	case 'votes':
		if ($reverse) {
			$stats_title =  gTranslate('core', " - Images with the least votes");
		}
		else {
			$stats_title =  gTranslate('core', " - Images with the most votes");
		}
		break;

	case 'ratings':
		if ($reverse ) {
			$stats_title =  gTranslate('core', " - Bottom rated images");
		}
		else {
			$stats_title =  gTranslate('core', " - Top rated images");
		}
		break;

	case 'date':
		if ($reverse ) {
			$stats_title =  gTranslate('core', " - Oldest images first");
		}
		else {

			$stats_title =  gTranslate('core', " - Latest added images");
		}
		break;

	case 'cdate':
		if ($reverse ) {
			$stats_title =  gTranslate('core', " - Oldest Capture Date");
		}
		else {
			$stats_title =  gTranslate('core', " - Latest Capture Date");
		}
		break;

	case 'comments':
		if ($reverse ) {
			$stats_title =  gTranslate('core', " - Oldest Comments");
		}
		else {
			$stats_title =  gTranslate('core', " - Latest Comments");
		}
		break;

	case 'random':
		$stats_title =  gTranslate('core', " - Random Images");
		break;

	default:
		// 'views'
		$type = 'views';
		if ($reverse ) {
			$stats_title =  gTranslate('core', " - Images with the least views");
		}
		else {
			$stats_title =  gTranslate('core', " - Images with the most views");
		}
		break;
}

if (!$GALLERY_EMBEDDED_INSIDE) {
	doctype();
?>
<html>
<head>
  <title><?php echo clearGalleryTitle($stats_title); ?></title>
<?php
common_header() ;
?>
</head>
   <body dir="<?php echo $gallery->direction ?>">
<?php
}
/* End of HTML begin, lets do some calculations etc. */

$set_albumListPage = intval($set_albumListPage);
if (empty($set_albumListPage) || $set_albumListPage < 0) {
	$set_albumListPage = 1;
}

//$page = intval($page);
//if (empty($page) || $page < 0) {
	$page = $set_albumListPage;
//}

$rating = '';
$ratingCount = '';
$randomNum = '';
$pixelImage = '<img src="' . getImagePath('pixel_trans.gif') . '" width="1" height="1" alt="pixel_trans">';

$time_start = getmicrotime();

class cacheCtrl {
	var $enabled;
	var $expireSecs;

	function cacheCtrl($enabled = 0, $expireSecs = -1) {
		$this->enabled	= $enabled;
		$this->expireSecs = $expireSecs;
	}
}

define("CACHE_INDEX_FIELD_WIDTH", 16);

// Always show comments if only displaying images that have comments.
if ($type == "comments" ) {
	$showComments = 1;
	$cache = new cacheCtrl( $gallery->app->stats_commentsCacheOn, $gallery->app->stats_commentsCacheExpireSecs );
}
/*
** Commented out, because the rating code is broken.
else if ($type == "ratings" ) {
$showRatings = 1;
$cache = new cacheCtrl( $gallery->app->stats_ratingsCacheOn, $gallery->app->stats_ratingsCacheExpireSecs );
}
*/
else if ($type == "views" ) {
	$showViews = 1;
	$cache = new cacheCtrl($gallery->app->stats_viewsCacheOn, $gallery->app->stats_viewsCacheExpireSecs);
}
else if ($type == "date" ) {
	$showUploadDate = 1;
	$cache = new cacheCtrl( $gallery->app->stats_dateCacheOn, $gallery->app->stats_dateCacheExpireSecs );
}
else if ($type == "cdate" ) {
	$showCaptureDate = 1;
	$cache = new cacheCtrl( $gallery->app->stats_cDateCacheOn, $gallery->app->stats_cDateCacheExpireSecs );
}
else if ($type == "votes" ) {
	$showVotes = 1;
	$cache = new cacheCtrl( $gallery->app->stats_votesCacheOn, $gallery->app->stats_votesCacheExpireSecs );
}
else {
	$cache = new cacheCtrl;
}

/* Check for any control variables passed in the url. */
if (!empty( $sca )) {
	$showCaption = $sca;
}

if (!empty( $sal )) {
	$showAlbumLink = $sal;
}

if (!empty( $sde )) {
	$showDescription = $sde;
}

if (!empty( $scd )) {
	$showCaptureDate = $scd;
}

if (!empty( $sud )) {
	$showUploadDate = $sud;
}

if (!empty( $svi )) {
	$showViews = $svi;
}

if (!empty( $svo )) {
	$showVotes = $svo;
}
/*
if (!empty( $sra )) {
$showRatings = $sra;
}
*/
if (!empty( $sco )) {
	$showComments = $sco;
}

if (!empty( $sac )) {
	$showAddComment = $sac;
}

if (!empty( $sav )) {
	$showAddVote = $sav;
}

if (!empty( $sao )) {
	$showAlbumOwner = $sao;
}

if (!empty( $ppp )) {
	$photosPerPage = $ppp;
}

if (!empty( $rows )) {
	$numRows = $rows;
}

if (!empty( $total )) {
	$totalPhotosToDisplay = $total;
}
if (!empty( $ty )) {
	$timeYear = $ty;
}

if (!empty( $tm )) {
	$timeMonth = $tm;
}

if (!empty( $td )) {
	$timeDay = $td;
}

if (!empty($showGrid)) {
	// In grid mode photos per page is controlled by the number of rows and columns.
	$photosPerPage = $cols * $numRows;
}

$albumDB = new AlbumDB(FALSE);

// Retrieve all albums and store in list array.
// This can be done using new AlbumDB() but this doesn't work
// on all hosts.
$numTopAlbums = $albumDB->numAlbums($gallery->user);
for ($i = 1; $i <= $numTopAlbums; $i++) {
	$topAlbum = $albumDB->getAlbum($gallery->user, $i);
	$list[] = $topAlbum;
	recurseAlbums( $topAlbum );
}

$numAlbums = count($list);

debugMessage(sprintf (gTranslate('core', "Number Albums = %s") . "<br>", $numAlbums),__FILE__ , __LINE__) ;

$skip = array();
$arrPhotos = array();

$useCache = false;
$cacheReloadRequired = false;

// Attempt to load from cache if cache is selected
// and a period or album have not been specified.
$cacheFilename = $gallery->app->albumDir . "/stats.$type.$reverse.cache";
debugMessage(sprintf (gTranslate('core', "Cache filename = %s ; enabled = %s ; expires = %s"), $cacheFilename, $cache->enabled, $cache->expireSecs), __FILE__, __LINE__);

if (!isset($refreshcache) &&
	!isset($period) &&
	!isset($album)  &&
	$cache->enabled )
{
	if (fs_file_exists($cacheFilename)) {
		$cacheState = fs_stat($cacheFilename);
		$cacheTime = $cacheState[9];
		if ($cache->expireSecs == -1 ||
		time() - $cacheTime < $cache->expireSecs) {
			debugMessage(sprintf (gTranslate('core', "Time now = %s ; Cache time = %s"), time(), $cacheTime), __FILE__, __LINE__);
			$numPhotos = readCacheNumPhotos($cacheFilename);
			if ($numPhotos != -1 ) {
				$arrPhotos = array_fill(0, $numPhotos, 0);
				$useCache = true;
			}
		}
	}

	if (!$useCache ) {
		$refreshcache = true;
	}

	// Logged in users don't use the cache
	if ($gallery->user->isLoggedIn()) {
		debugMessage(gTranslate('core', "Logged In - Disabling Cache"), __FILE__, __LINE__);
		$refreshcache = false;
		$useCache = false;
	}
}

debugMessage((!empty($useCache)) ? gTranslate('core', "Using cache") : gTranslate('core', "Not using cache") ,__FILE__, __LINE__);
debugMessage((!empty($refreshcache)) ? gTranslate('core', "Cache to be rebuilt") : gTranslate('core', "Cache will not rebuild.") , __FILE__, __LINE__);

// Check if photo data will be loaded from the caches.
// If it isn't, then load the photos data and sort.
if (empty($useCache)) {
	if (!empty($period )) {
		$cutoffDate = strftime("%Y") * 12 + strftime("%m") - $period;
	}
	else {
		$cutoffDate = 0;
	}

	if ($type == "random" ) {
		// Seed the random number generator.
		srand((double)microtime()*1000000);
	}

	for ($i = 0; $i<$numAlbums; $i++) {
		if(isDebugging()) {
		echo "\n<hr>";
	}
		$statsAlbum = $list[$i];
		if ($statsAlbum->versionOutOfDate()) {
			debugMessage(gTranslate('core', "Version out of date."), __FILE__, __LINE__, 2);
			$skip[] = $statsAlbum;
			continue;
		}

		/* broken when showNestedAlbums = 1 */
		if (isset($album)) {
			if (isset($showNestedAlbums)) {
				// Need to show nested images inside the requested album.
				if (in_array($album, getParentAlbums($statsAlbum))) {
					// Check if this album is the parent
					if ($album == $statsAlbum->fields['name']) {
						$albumobj = $statsAlbum;
					}
				}
				else {
					// Ignore albums that are not within the specified $album
					continue;
				}
			}
			else {
				if ($album == $statsAlbum->fields['name']) {
					$albumobj = $statsAlbum;
				}
				else {
					continue;
				}
			}
		}

		$uid = $gallery->user->getUid();
		if ($statsAlbum->canRead($uid) || $gallery->user->isAdmin() || $statsAlbum->isOwner($uid))  {
			debugMessage(sprintf (gTranslate('core', "Checking album: %s"), $statsAlbum->fields['name']), __FILE__, __LINE__);

			// Haplo code to make sense of the Gallery rankings.
			/*
			if (isset($showRatings) || $type == "ratings" ) {
			$ratingAverageList = getRatingAverage();
			}
			*/
			$numPhotos = $statsAlbum->numPhotos(1);
			for ($j = 1; $j <= $numPhotos; $j++) {
				debugMessage(sprintf (gTranslate('core', "Reading info for photo index = %d , id = %d"), $j, $statsAlbum->getPhotoId($j)), __FILE__, __LINE__, 2);

				if (! $statsAlbum->isAlbum($j) && (!$statsAlbum->isHidden($j) || $gallery->user->isAdmin())) {
					$uploaddate = $statsAlbum->getUploadDate($j);

					if (strftime("%Y",$uploaddate ) * 12 + strftime("%m",$uploaddate) >= $cutoffDate ) {
						// If displaying latest comments,
						// then only list photos with comments;
						// otherwise display all.
						if ($type != "comments" || ( $statsAlbum->numComments($j) > 0 && $statsAlbum->canViewComments($uid) )) {
							if (isset($showVotes) || $type == "votes" ) {
								debugMessage(gTranslate('core', "Getting SVotes"), __FILE__, __LINE__, 2);
								$votes = $statsAlbum->getItemSVotes($j);
							} else {
								$votes = '';
							}

							debugMessage(gTranslate('core', "Getting Item Clicks"), __FILE__, __LINE__, 2);
							$views = $statsAlbum->getItemClicks($j);

							debugMessage(gTranslate('core', "Getting Item Capture Date"), __FILE__, __LINE__, 2);
							$captureDate = $statsAlbum->getItemCaptureDate($j);

							// If the user wants stats for a capture date of a
							// specific year, month or day then filter out any images
							// that do not match.
							if (!empty($timeYear) && $timeYear != strftime("%Y",$captureDate)) {
								continue;
							}

							if (!empty($timeMonth) && $timeMonth != strftime("%m", $captureDate)) {
								continue;
							}

							if (!empty($timeDay) && $timeDay != strftime("%d", $captureDate)) {
								continue;
							}


							debugMessage(gTranslate('core', "Getting Number of Comments"), __FILE__, __LINE__, 2);
							if ($statsAlbum->numComments($j) > 0 )  {
								debugMessage(gTranslate('core', "Getting Comments"), __FILE__, __LINE__, 2);
								$comment = $statsAlbum->getComment( $j, $statsAlbum->numComments($j) );
								$commentDate = $comment->datePosted;
							}
							else {
								$commentDate = 0;
							}
							/*
							if (isset($showRatings) || $type == "ratings" ) {
							debugMessage(gTranslate('core', "Getting Ratings"), __FILE__, __LINE__, 2);

							if (!empty($ratingAverageList[$j])) {
							$ratingCount = $ratingAverageList[$j]['count'];
							if ($ratingAverageList[$j]['average'] != 0 ) {
							// Only show rating when sufficient votes have been cast.
							if ($ratingAverageList[$j]['count'] >= $votesNeededToShowRating ) {
							$rating = $ratingAverageList[$j]['average'];
							}
							else {
							$rating = -1;
							}
							}
							else {
							$rating = -2;
							}
							}
							}
							*/

							if ($type == "random" ) {
								$randomNum = rand();
							}

							$arrPhotos[] = array("albumName" => $statsAlbum->fields['name'],
								"photoId" => $statsAlbum->getPhotoId($j),
								"votes" => $votes,
								"views" => $views,
								"uploaddate" => $uploaddate,
								"capturedate" => $captureDate,
								"commentdate" => $commentDate,
								"rating" => $rating,
								"ratingcount" => $ratingCount,
								"random" => $randomNum );
							debugMessage(sprintf (gTranslate('core', "Album: %s ; Index: %d ; Votes: %d ; Views: %d; Date: %s; Capture: %s; Comment Date: %s; Rating: %s; Rating count: %d; Random: %d"). "<br>",
								$statsAlbum->fields['name'], $j, $votes, $views, $uploaddate, $captureDate, $commentDate, $rating, $ratingCount, $randomNum)
				, __FILE__, __LINE__, 1);
						}
					}
				}
			}
		}
	}

	$order = ($reverse) ? 'asc' : 'desc';

	if (is_array($arrPhotos) ) {
		// Do the search using the criteria specified by $type.
	switch ($type) {
		case 'votes':
			array_sort_by_fields($arrPhotos, 'votes', $order);
		break;

		case 'views':
			array_sort_by_fields($arrPhotos, 'views', $order);
		break;

		case 'date':
			array_sort_by_fields($arrPhotos, 'uploaddate', $order);
		break;

		case 'cdate':
			array_sort_by_fields($arrPhotos, 'capturedate', $order);
		break;

		case 'comments':
			array_sort_by_fields($arrPhotos, 'commentdate', $order);
		break;

		case 'ratings':
			array_sort_by_fields($arrPhotos, 'rating', $order);
		break;

		case 'random':
			array_sort_by_fields($arrPhotos, 'random', $order);
		break;
		}
	}
}

if (!empty($refreshcache) &&
	!isset($period) &&
	!isset($album) &&
	is_array($arrPhotos))
{
	writeGalleryStatsCache($cacheFilename);
}

$uid = $gallery->user->getUid();

if (!isset($totalPhotosToDisplay) || $totalPhotosToDisplay <= 0 || $totalPhotosToDisplay > sizeof($arrPhotos)) {
	$totalPhotosToDisplay = sizeof($arrPhotos);
}

if (!isset($photosPerPage)) {
	$photosPerPage = $totalPhotosToDisplay;
}

$totalPhotosReq = $totalPhotosToDisplay;
$startPhoto = ($page - 1) * $photosPerPage;

// Use fuz factor to avoid rounding up when result is 0.5
$lastpage = round( ($totalPhotosToDisplay / $photosPerPage) - 0.500001) + 1;
debugMessage(sprintf(gTranslate('core', "Total: %s ; Start: %s ; Last Page: %s"), $totalPhotosToDisplay, $startPhoto, $lastpage), __FILE__, __LINE__);

$borderColor = $gallery->app->default["bordercolor"];
if (isset($tsz) ) {
	$thumbSize = $tsz;
}
else {
	$thumbSize = $gallery->app->default["thumb_size"];
}

// <!-- stats.header begin -->
includeHtmlWrap("stats.header");

$navigator["fullWidth"] = 100;
$navigator["widthUnits"] = "%";
$adminText = '';
if (isset($album)) {
	if (isset($albumobj)) {
		if ($type == "comments" ) {
            $adminText .= sprintf(gTranslate('core', "%d items with comments in album: %s."), count($arrPhotos), $albumLink);
		}
		else {
			$adminText .= sprintf(gTranslate('core', "%d items in album: %s"), count($arrPhotos), $albumLink);
		}
	}
	else {
		$adminText .= sprintf(gTranslate('core', "Given albumname: '%s' is invalid !"), $album);
	}
}
else {
	if ($type == "comments" ) {
        $adminText .= sprintf(gTranslate('core', "%d items with comments in this Gallery."), count($arrPhotos));
	}
	else {
        $adminText .= sprintf(gTranslate('core', "%d items this Gallery."), count($arrPhotos));
	}
}

if ($gallery->user->isAdmin()) {
	$iconElements[] = galleryIconLink(
				makeGalleryUrl("admin-page.php"),
				'navigation/return_to.gif',
				gTranslate('core', "Return to admin page"));
}

$iconElements[] = galleryIconLink(
				makeAlbumUrl(),
				'navigation/return_to.gif',
				gTranslate('core', "Return to gallery"));

$iconElements[] = LoginLogoutButton(makeGalleryUrl());

$adminbox['commands'] = makeIconMenu($iconElements, 'right');

if (!empty($gallery->app->stats_foruser)) {
	$adminText .= "\n<br>&nbsp;". generateStatsLinks();
}

$adminbox['text'] = $adminText;
$adminbox["bordercolor"] = $borderColor;

$navigator["page"] = $page;
$navigator["pageVar"] = "set_albumListPage";
$navigator["url"] = makeStatsUrl( $page );
$navigator["maxPages"] = $lastpage;
$navigator["spread"] = 6;
$navigator["fullWidth"] = 100;
$navigator["widthUnits"] = "%";
$navigator["bordercolor"] = $borderColor;

includeLayout('navtablebegin.inc');
includeLayout('adminbox.inc');
includeLayout('navtablemiddle.inc');

echo "<!-- Begin top nav -->";
if ($navigator["maxPages"] > 1) {
	echo '<div class="g-navbar-top">';
	includeLayout('navigator.inc');
	echo '</div>';
}
includeLayout('navtableend.inc');
echo languageSelector();
echo "<!-- End top nav -->";

if ($useCache ) {
	readGalleryStatsCache($cacheFilename, $startPhoto, $photosPerPage );
}

if (isset($stm)) {
	$time = getmicrotime() - $time_start;
    	printf(gTranslate('core', "Data load time %d seconds."), $time);
}

/* Start of album layout style. */

if (empty($showGrid)) {
	$cols = 1;
	$style = 'style="margin-right:3px; float:left"';
}
else {
	$style = '';
}

echo '<br clear="all">';

$statsTable = new galleryTable();
$statsTable->setColumnCount(2 * $cols);
$statsTable->setAttrs(array(
	'id' => 'statsTable',
	'class' => 'g-vatable',
	'width' => $navigator["fullWidth"] . $navigator["widthUnits"],
	'border' => 0,
	'cellspacing' => 7));

for ($j = $startPhoto; $j < $totalPhotosToDisplay && $j < $startPhoto + $photosPerPage; $j+=1) {
	$photoInfo = $arrPhotos[$j];
	for ( $i = 0; $i < $numAlbums; ++$i ) {
		if (!strcmp($photoInfo['albumName'], $list[$i]->fields['name']) ) {
			$statsAlbum = $list[$i];
			break;
		}
	}

	if (!isset($statsAlbum)) {
		// Album deleted.
		$cacheReloadRequired = true;
	}
	else {
		$photoId = $photoInfo['photoId'];
		$photoIndex = $statsAlbum->getPhotoIndex($photoId);
		if ($photoIndex == -1 ) {
			$cacheReloadRequired = true;
		}
		else {
			if ($statsAlbum->canRead($uid) || $gallery->user->isAdmin()) {
				if (!$statsAlbum->isHidden($photoIndex) || $statsAlbum->isOwner($uid) || $gallery->user->isAdmin()) {
					$statsCaption = $statsAlbum->getCaption($photoIndex);
					$statsCaption .= $statsAlbum->getCaptionName($photoIndex);
					$statsUrl = makeAlbumUrl($statsAlbum->fields['name'], $photoId);

					// Image Cell
					$statsTable->addElement(array(
						'content' => '<div class="g-vathumb">'.
									 "<a href=\"$statsUrl\">". $statsAlbum->getThumbnailTag($photoIndex, $thumbSize) . "</a>" .
									 '</div>',
						'cellArgs' => array('class' => 'g-vathumb-cell', 'style' => 'vertical-align: top')));

					//  Text Cell -->
					$statsTable->addElement(array(
						'content' => displayTextCell($statsAlbum, $photoIndex, $photoId, $photoInfo['rating'], $photoInfo['ratingcount']),
						'cellArgs' => array('class' => 'g-va-thumb-texts', 'style' => 'vertical-align: top')));
				}
			}
		}
	}
}

echo $statsTable->render();


if (isset($stm)) {
	$time = getmicrotime() - $time_start;
	echo infoBox(array(array(
		'type' => 'success',
		'text' => sprintf (gTranslate('core', "Finished in %d seconds"), $time)
	)));
}

if ($cacheReloadRequired) {
	$url = makeStatsUrl( $page );
	$url .= "&refreshcache=1";
	$urlhref = '<a href="'. $url .'">['. gTranslate('core', "Update") .']</a>';
	echo gallery_error(gTranslate('core', "Cache update required. ").$urlhref);
}

if (sizeof($skip) > 0) {
	echo gallery_error(sprintf(gTranslate('core', "Some albums not searched as they require upgrading to the latest version of %s first"),Gallery()));
	if ($gallery->user->isAdmin()) {
		print ":<br>";
		echo popup_link(gTranslate('core', "upgrade all albums"), "upgrade_album.php");
		print "<br>(";
		$join_text='';
		foreach($skip as $stalbum) {
			$link = makeGalleryUrl("view_album.php",
			array("set_albumName" => $stalbum->fields["name"]));
			echo $join_text .'<a href="'. $link .'">'. $stalbum->fields["name"] .'</a>';
			$join_text=", ";
		}
		print ")";
	}
	else {
		print ".";
	}
	echo "<p>";
}
echo "<br>";

// <!-- bottom nav -->

includeLayout('navtablebegin.inc');
if ($navigator["maxPages"] > 1) {
		echo '<div class="g-navbar-bottom">';
		includeLayout('navigator.inc');
		echo '</div>';
	}
includeLayout('navtableend.inc');

echo languageSelector();
includeHtmlWrap("stats.footer");

if (!$GALLERY_EMBEDDED_INSIDE) { ?>
</body>
</html>
<?php
}

?>
