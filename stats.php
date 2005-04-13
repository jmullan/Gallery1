<?php
/*
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
*       the current month.
*   stats.php?type=views?period=1 displays the number of views (clicks)
*       for all photos uploaded during the last month and the current month
*       stats.php?type=date&period=6 covers the last 6 months.
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
* This program is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
* General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*
* Copyright (C) 2004 Jeremy Gilliat with rating contribution by Haplo
* based on Bharat Mediratta's standard Gallery code.
*
* $Id$
*/


require_once(dirname(__FILE__) . '/init.php');
require_once(dirname(__FILE__) . '/includes/stats/stats.inc.php');

$debug = 0;
//$album="album01";

list ($type, $sca, $sal, $sde, $sco, $scd, $sud, $svi, $sac, $svo, $sav, $sao, $stm, $rev, $tsz ,$ppp, $total, $sgr, $rows, $cols, $addLinksPos) =
	getRequestVar(array('type', 'sca', 'sal', 'sde', 'sco', 'scd', 'sud', 'svi', 'sac', 'svo', 'sav', 'sao', 'stm', 'rev', 'tsz' ,'ppp', 'total', 'sgr', 'rows', 'cols', 'addLinksPos'));

list ($ty, $tm, $td) = getRequestVar(array('ty', 'tm', 'td'));

list ($page, $set_albumListPage) =
	getRequestVar(array('page', 'set_albumListPage'));

if (empty($type)) {
	/* We assume was called direct. So we call show defaults */
	header("Location: ". unhtmlentities(defaultStatsUrl('views')));
}

$rating = "";
$ratingCount = "";
$randomNum = "";
$pixelImage = '<img src="' . getImagePath('pixel_trans.gif') . '" width="1" height="1" alt="pixel_trans">';

if ( !empty($stm) ) {
	$time_start = getmicrotime();
}

class cacheCtrl {
	var $enabled;
	var $expireSecs;

	function cacheCtrl($enabled = 0, $expireSecs = -1) {
		$this->enabled    = $enabled;
		$this->expireSecs = $expireSecs;
	}
}

define("CACHE_INDEX_FIELD_WIDTH", 16);

// Always show comments if only displaying images that have comments.
if ( $type == "comments" ) {
	$showComments = 1;
	$cache = new cacheCtrl( $gallery->app->stats_commentsCacheOn, $gallery->app->stats_commentsCacheExpireSecs );
}
/*
** Commented out, because the rating code is broken.
else if ( $type == "ratings" ) {
	$showRatings = 1;
	$cache = new cacheCtrl( $gallery->app->stats_ratingsCacheOn, $gallery->app->stats_ratingsCacheExpireSecs );
}
*/
else if ( $type == "views" ) {
	$showViews = 1;
	$cache = new cacheCtrl($gallery->app->stats_viewsCacheOn, $gallery->app->stats_viewsCacheExpireSecs);
}
else if ( $type == "date" ) {
	$showUploadDate = 1;
	$cache = new cacheCtrl( $gallery->app->stats_dateCacheOn, $gallery->app->stats_dateCacheExpireSecs );
}
else if ( $type == "cdate" ) {
	$showCaptureDate = 1;
	$cache = new cacheCtrl( $gallery->app->stats_cDateCacheOn, $gallery->app->stats_cDateCacheExpireSecs );
}
else if ( $type == "votes" ) {
	$showVotes = 1;
	$cache = new cacheCtrl( $gallery->app->stats_votesCacheOn, $gallery->app->stats_votesCacheExpireSecs );
}
else {
	$cache = new cacheCtrl;
}

// Check for any control variables passed in the url.
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

if (!empty( $sgr )) {
	$showGrid = $sgr;
}

if (!empty( $rev )) {
	$reverseOrder = $rev;
} else {
	$reverseOrder = 0;
}

if (!empty( $ppp )) {
	$photosPerPage = $ppp;
}

if (!empty( $rows )) {
	$numRows = $rows;
}

if (!empty( $cols )) {
	$numCols = $cols;
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
if ($debug) {
	echo sprintf (_("Number Albums = %s") . "<br>", $numAlbums) ;
}
$skip = array();
$arrPhotos = array();

$useCache = false;
$cacheReloadRequired = false;

// Attempt to load from cache if cache is selected
// and a period or album have not been specified.
$cacheFilename = $gallery->app->albumDir . "/stats.$type.$reverseOrder.cache";
if ( $debug > 1 ) {
	echo sprintf (_("Cache filename = %s ; enabled = %s ; expires = %s"), $cacheFilename, $cache->enabled, $cache->expireSecs) . "<br>";
}

if ( !isset($refreshcache) &&
	!isset($period) &&
	!isset($album)  &&
	$cache->enabled ) {
	if (fs_file_exists($cacheFilename)) {
		$cacheState = fs_stat($cacheFilename);
		$cacheTime = $cacheState[9];
		if ( $cache->expireSecs == -1 ||
		time() - $cacheTime < $cache->expireSecs) {
			if ( $debug > 1 ) {
				echo sprintf (_("Time now = %s ; Cache time = %s"), time(), $cacheTime). "<br>";
			}
			$numPhotos = readCacheNumPhotos($cacheFilename);
			if ( $numPhotos != -1 ) {
				$arrPhotos = array_fill(0, $numPhotos, 0);
				$useCache = true;
			}
		}
	}

	if ( !$useCache ) {
		$refreshcache = true;
	}

	// Logged in users don't use the cache
	if ($gallery->user->isLoggedIn()) {
		if ($debug > 1) {
			echo _("Logged In - Disabling Cache") . "<br>";
		}
		$refreshcache = false;
		$useCache = false;
	}
}

if ( $debug > 1 ) {
	if ( $useCache ) {
		echo _("Using cache") . "<br>";
	}
	else {
		echo _("Not using cache"). "<br>";
	}
	if ( !empty($refreshcache) ) {
		echo _("Cache to be rebuilt"). "<br>";
	}
}

// Check if photo data will be loaded from the caches.
// If it isn't, then load the photos data and sort.
if ( !$useCache ) {
	if ( isset($period ) ) {
		$cutoffDate = strftime("%Y") * 12 + strftime("%m") - $period;
	}
	else {
		$cutoffDate = 0;
	}

	if ( $type == "random" ) {
		// Seed the random number generator.
		srand((double)microtime()*1000000);
	}

	for ($i = 0; $i<$numAlbums; $i++) {
		$statsAlbum = $list[$i];
		if ($statsAlbum->versionOutOfDate()) {
			if ( $debug >= 2 ) {
				echo _("Version out of date.") ."<br>";
			}
			$skip[] = $statsAlbum;
			continue;
		}

		/* broken when showNestedAlbums = 1 */
		if (isset($album)) {
			if (isset($showNestedAlbums)) {
				// Need to show nested images inside the requested album.
				if (in_array($album, getParentAlbums($statsAlbum))) {
					// Check if this album is the parent
					if ( $album == $statsAlbum->fields['name']) {
						$albumobj = $statsAlbum;
					}
				}
				else {
					// Ignore albums that are not within the specified $album
					continue;
				}
			}
			else {
				if ( $album == $statsAlbum->fields['name']) {
					$albumobj = $statsAlbum;
				}
				else {
					continue;
				}
			}
		}

		$uid = $gallery->user->getUid();
		if ($statsAlbum->canRead($uid) || $gallery->user->isAdmin() || $statsAlbum->isOwner($uid))  {
			if ( $debug ) {
				echo sprintf (_("Checking album: %s"), $statsAlbum->fields['name']) ."<br>";
			}

			// Haplo code to make sense of the Gallery rankings.
			/*
			if ( isset($showRatings) || $type == "ratings" ) {
				$ratingAverageList = getRatingAverage();
			}
			*/
			$numPhotos = $statsAlbum->numPhotos(1);
			for ($j = 1; $j <= $numPhotos; $j++) {
				if ( $debug > 1 ) {
					echo sprintf (_("Reading info for photo index = %d , id = %d"), $j, $statsAlbum->getPhotoId($j)). "<br>";
				}

				if (! $statsAlbum->isAlbum($j) &&
				(!$statsAlbum->isHidden($j) || $gallery->user->isAdmin())) {
					$uploaddate = $statsAlbum->getUploadDate($j);

					if ( strftime("%Y",$uploaddate ) * 12 + strftime("%m",$uploaddate) >= $cutoffDate ) {
						// If displaying latest comments,
						// then only list photos with comments;
						// otherwise display all.
						if ( $type != "comments" ||
						( $statsAlbum->numComments($j) > 0 &&
						$statsAlbum->canViewComments($uid) )) {
							if ( isset($showVotes) || $type == "votes" ) {
								if ( $debug > 2 ) {
									echo _("Getting SVotes") ."<br>";
								}
								$votes = $statsAlbum->getItemSVotes($j);
							} else {
								$votes ="";
							}

							if ( $debug > 2 ) {
								echo _("Getting Item Clicks") ."<br>";
							}
							$views = $statsAlbum->getItemClicks($j);

							if ( $debug > 2 ) {
								echo _("Getting Item Capture Date") ."<br>";
							}
							$captureDate = $statsAlbum->getItemCaptureDate($j);

							// If the user wants stats for a capture date of a
							// specific year, month or day then filter out any images
							// that do not match.
							if ( !empty($timeYear) && $timeYear != strftime("%Y",$captureDate)) {
								continue;
							}
							
							if ( !empty($timeMonth) && $timeMonth != strftime("%m", $captureDate)) {
								continue;
							}
							
							if ( !empty($timeDay) && $timeDay != strftime("%d", $captureDate)) {
								continue;
							}

							if ( $debug > 2 ) {
								echo _("Getting Number of Comments"). "<br>";
							}
							if ( $statsAlbum->numComments($j) > 0 )  {
								if ( $debug > 2 ) {
									echo _("Getting Comments") ."<br>";
								}
								$comment = $statsAlbum->getComment( $j, $statsAlbum->numComments($j) );
								$commentDate = $comment->datePosted;
							}
							else {
								$commentDate = 0;
							}
							/*
							if ( isset($showRatings) || $type == "ratings" ) {
								if ( $debug > 2 ) {
									echo _("Getting Ratings"). "<br>";
								}

								if (!empty($ratingAverageList[$j])) {
									$ratingCount = $ratingAverageList[$j]['count'];
									if ( $ratingAverageList[$j]['average'] != 0 ) {
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

							if ( $type == "random" ) {
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
							if ( $debug > 1 ) {
								echo sprintf (_("Album: %s ; Index: %d ; Votes: %d ; Views: %d; Date: %s; Capture: %s; Comment Date: %s; Rating: %s; Rating count: %d; Random: %d"). "<br>",
								$statsAlbum->fields['name'], $j, $votes, $views, $uploaddate, $captureDate, $commentDate, $rating, $ratingCount, $randomNum);
							}
						}
					}
				}
			}
		}
	}

	if ( is_array($arrPhotos) ) {
		// Set what is returned from the sort comparisons depending
		// upon whether the user wants to reverse the sort order or not.
		if ( $reverseOrder ) {
			$retSortGreater = 1;
			$retSortLesser  = -1;
		}
		else {
			$retSortGreater = -1;
			$retSortLesser  = 1;
		}

		// Now do the search using the criteria specified by $type.
		if ( $type == "votes" ) {
			usort($arrPhotos, "votesort");
		}
		else if ( $type == "views" ) {
			usort($arrPhotos, "viewsort");
		}
		else if ( $type == "date" ) {
			usort($arrPhotos, "datesort");
		}
		else if ( $type == "cdate" ) {
			usort($arrPhotos, "capturedatesort");
		}
		else if ( $type == "comments" ) {
			usort($arrPhotos, "commentdatesort");
		}
		else if ( $type == "ratings" ) {
			usort($arrPhotos, "ratingsort");
		}
		else if ( $type == "random" ) {
			usort($arrPhotos, "randomsort");
		}
	}
}

if ( !empty($refreshcache) &&
    !isset($period) &&
    !isset($album) &&
    is_array($arrPhotos)) {
	writeCache($cacheFilename);
}

$uid = $gallery->user->getUid();
if (empty($page) && empty($set_albumListPage)) {
	$page = 1;
} elseif (!empty($set_albumListPage)) {
	$page = $set_albumListPage;
}

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
if ($debug) {
	echo sprintf(_("Total: %s ; Start: %s ; Last Page: %s"), $totalPhotosToDisplay, $startPhoto, $lastpage) . "<br>";
}

$borderColor = $gallery->app->default["bordercolor"];
if ( isset($tsz) ) {
	$thumbSize = $tsz;
}
else {
	$thumbSize = $gallery->app->default["thumb_size"];
}

switch ($type) {
	case 'votes':
	if ( !$reverseOrder ) {
		$stats_title =  _(" - Images with the most votes");
	}
	else {
		$stats_title =  _(" - Images with the least votes");
	}
	break;

	case 'ratings':
	if ( !$reverseOrder ) {
		$stats_title =  _(" - Top rated images");
	}
	else {
		$stats_title =  _(" - Bottom rated images");
	}
	break;

	case 'date':
	if ( !$reverseOrder ) {
		$stats_title =  _(" - Latest added images");
	}
	else {
		$stats_title =  _(" - Oldest images first");
	}
	break;

	case 'cdate':
	if ( !$reverseOrder ) {
		$stats_title =  _(" - Latest Capture Date");
	}
	else {
		$stats_title =  _(" - Oldest Capture Date");
	}
	break;

	case 'comments':
	if ( !$reverseOrder ) {
		$stats_title =  _(" - Latest Comments");
	}
	else {
		$stats_title =  _(" - Oldest Comments");
	}
	break;

	case 'random':
		$stats_title =  _(" - Random Images");
	break;

	default:
	// 'views'
	if ( !$reverseOrder ) {
		$stats_title =  _(" - Images with the most views");
	}
	else {
		$stats_title =  _(" - Images with the least views");
	}
	break;
}

if (!$GALLERY_EMBEDDED_INSIDE) {
	doctype();
?>
<html>
<head>
  <title><?php echo $gallery->app->galleryTitle . $stats_title; ?></title>
<?php
	common_header() ;
?>
</head>
   <body dir="<?php echo $gallery->direction ?>">
<?php
}
?>
<?php 

// <!-- stats.header begin -->
includeHtmlWrap("stats.header");

$navigator["fullWidth"] = 100;
$navigator["widthUnits"] = "%";

$adminText = "";

if (isset($album)) {
	if (isset($albumobj)) {
		if ( $type == "comments" ) {
			$adminText .= sprintf(_("%d images with comments in album: %s"), count($arrPhotos), $albumLink);
		} else {
			$adminText .= sprintf(_("%d images in album: %s"), count($arrPhotos), $albumLink);
		}
	} else {
		$adminText .= sprintf(_("Given albumname: '%s' is invalid !"), $album);
	}
}
else {
	if ( $type == "comments" ) {
		$adminText .= sprintf(_("%d images with comments in this Gallery"), count($arrPhotos));
	}
	else {
		$adminText .= sprintf(_("%d images this Gallery"), count($arrPhotos));
	}
}

$adminbox["commands"] = '';
if ($gallery->user->isAdmin()) {
	$adminbox["commands"] = '[<a href="'. makeGalleryURL('stats-wizard.php') .'">'. _("Back to stats-wizard") .'</a>] ';
}
$adminbox["commands"] .= '[<a href="'. makeAlbumUrl() .'">'. _("return to gallery") .'</a>]';


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
includeLayout('navigator.inc');
includeLayout('navtableend.inc');
includeLayout('ml_pulldown.inc');
echo "<!-- End top nav -->";

if ( $useCache ) {
	readCache($cacheFilename, $startPhoto, $photosPerPage );
}
if (isset($stm)) {
	$time = getmicrotime() - $time_start;
	echo sprintf(_("Data load time %d seconds"), $time);
}

	/* Start of album layout style. */
	echo '<table width="'. $navigator["fullWidth"] . $navigator["widthUnits"] .'" border="0" cellspacing="7">';

	for ($j = $startPhoto; $j < $totalPhotosToDisplay && $j < $startPhoto + $photosPerPage; $j+=1) {
		$photoInfo = $arrPhotos[$j];
		for ( $i = 0; $i < $numAlbums; ++$i ) {
			if ( !strcmp($photoInfo['albumName'], $list[$i]->fields['name']) ) {
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
			if ( $photoIndex == -1 ) {
				$cacheReloadRequired = true;
			}
			else {
				if ($statsAlbum->canRead($uid) || $gallery->user->isAdmin()) {
					if (!$statsAlbum->isHidden($photoIndex) ||
					$statsAlbum->isOwner($uid) ||
					$gallery->user->isAdmin()) {
						$statsCaption = $statsAlbum->getCaption($photoIndex);
						$statsCaption .= $statsAlbum->getCaptionName($photoIndex);
						$statsUrl = makeAlbumUrl($statsAlbum->fields['name'], $photoId);
?>
                     <!-- Begin Album Column Block -->
                     <tr>
                        <td height="10"><?php echo $pixelImage ?></td>
                        <td height="10"><?php echo $pixelImage ?></td>
                     </tr>
                     <tr>
                        <!-- Begin Image Cell -->
                        <td align="center" valign="top">

<?php
$gallery->html_wrap['borderColor'] = $borderColor;
$gallery->html_wrap['borderWidth'] = 1;
$gallery->html_wrap['pixelImage'] = getImagePath('pixel_trans.gif');
$scaleTo = $gallery->app->highlight_size;
$iWidth = $gallery->app->highlight_size;
$iHeight = 100;
/*begin backwards compatibility */
$gallery->html_wrap['thumbWidth'] = $iWidth;
$gallery->html_wrap['thumbHeight'] = $iHeight;
$gallery->html_wrap['thumbTag'] = $statsAlbum->getThumbnailTag($photoIndex, $thumbSize);
$gallery->html_wrap['thumbHref'] = $statsUrl;
/*end backwards compatibility*/
$gallery->html_wrap['imageTag'] =  $statsAlbum->getThumbnailTag($photoIndex, $thumbSize);

// Added for Gallery 1.44
$imgTag = $gallery->html_wrap['imageTag'];
$gallery->html_wrap['imageWidth']  = getWidthFromTag($imgTag);
$gallery->html_wrap['imageHeight'] = getHeightFromTag($imgTag);

$gallery->html_wrap['imageHref'] = $statsUrl;
$gallery->html_wrap['frame'] = $statsAlbum->fields['thumb_frame'];
includeHtmlWrap('inline_gallerythumb.frame');
?>
                        </td>
                        <td>&nbsp;</td>
                        <!-- End Image Cell -->

<?php
displayTextCell($statsAlbum, $photoIndex, $photoId, $photoInfo['rating'], $photoInfo['ratingcount'] );
?>

                     </tr>
<?php
					}
				}
			}
		}
	}

	echo "</table>";

if (isset($stm)) {
	$time = getmicrotime() - $time_start;
	echo sprintf (_("Finished in %d seconds"), $time). "\n";
}

if ($cacheReloadRequired) {
	$url = makeStatsUrl( $page );
	$url .= "&refreshcache=1";
	$urlhref = '<a href="'. $url .'">['. _("Update") .']</a>';
	echo gallery_error(_("Cache update required. ").$urlhref);
}

if (sizeof($skip) > 0) {
	echo gallery_error(sprintf(_("Some albums not searched as they require upgrading to the latest version of %s first"),Gallery()));
	if ($gallery->user->isAdmin()) {
		print ":<br>";
		echo popup_link(_("upgrade all albums"), "upgrade_album.php");
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
?>

<!-- bottom nav -->
<?php
includeLayout('navtablebegin.inc');
includeLayout('navigator.inc');
includeLayout('navtableend.inc');

includeLayout('ml_pulldown.inc');
includeHtmlWrap("stats.footer");

if (!$GALLERY_EMBEDDED_INSIDE) { ?>
</body>
</html>
<?php }

/*
*  Functions
*/

function getmicrotime() {
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

function recurseAlbums( $parentAlbum) {
	global $debug, $list, $gallery;
	if ($parentAlbum) {
		if ( $debug >= 2 ) {
			echo sprintf(_("Recursing album: %s"),  $parentAlbum->fields['name']) ."<br>";
		}

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
	global $debug;

	$numPhotos = -1;
	if ($fd = fs_fopen($cacheFilename, "rb")) {
		if (myFlock($fd, LOCK_SH)) {
			$numPhotos = fgets($fd);
			myFlock($fd, LOCK_UN);
		}
		else {
			if ( $debug > 1 ) {
				echo _("Read cache num photos lock failed.") ."<br>";
			}
		}
		fclose($fd);
	}

	return $numPhotos;
}

function readCache( $cacheFilename, $start, $numPhotos ) {
	global $arrPhotos, $debug;

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
				if ( $data ) {
					if ( $debug > 1 ) {
						echo sprintf(_("Album name : %s ; index: %s"), $data[0], $data[1]) ."<br>";
					}
					$arrPhotos[$start+$i] = array("albumName" => $data[0],
					"photoId" => $data[1],
					"rating" => $data[2],
					"ratingcount" => $data[3] );
				}
			}
			myFlock($fd, LOCK_UN);
		}
		else {
			if ( $debug > 1 ) {
				echo _("Read cache lock failed."). "<br>";
			}
		}
		fclose($fd);
	}
}

function writeCache( $cacheFilename ) {
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
			if ( $debug > 1 ) {
				echo _("Read cache lock failed.") ."<br>";
			}
		}
		fclose($fd);
	}
}

function votesort($a, $b) {
	global $retSortGreater, $retSortLesser;
	if ($a['votes'] == $b['votes']) {
		return 0;
	}
	return ($a['votes'] > $b['votes']) ? $retSortGreater : $retSortLesser;
}

function ratingsort($a, $b) {
	global $retSortGreater, $retSortLesser;
	if ($a['rating'] == $b['rating']) {
		return 0;
	}
	return ($a['rating'] > $b['rating']) ? $retSortGreater : $retSortLesser;
}

function viewsort($a, $b) {
	global $retSortGreater, $retSortLesser;
	if ($a['views'] == $b['views']) {
		return 0;
	}
	return ($a['views'] > $b['views']) ? $retSortGreater : $retSortLesser;
}

function datesort($a, $b) {
	global $retSortGreater, $retSortLesser;
	if ($a['uploaddate'] == $b['uploaddate']) {
		return 0;
	}
	return ($a['uploaddate'] > $b['uploaddate']) ? $retSortGreater : $retSortLesser;
}

function commentdatesort($a, $b) {
	global $retSortGreater, $retSortLesser;
	if ($a['commentdate'] == $b['commentdate']) {
		return 0;
	}
	return ($a['commentdate'] > $b['commentdate']) ? $retSortGreater : $retSortLesser;
}

function capturedatesort($a, $b) {
	global $retSortGreater, $retSortLesser;

	if($a['capturedate'] == $b['capturedate']) {
		return 0;
	}

	return ($a['capturedate'] > $b['capturedate']) ? $retSortGreater : $retSortLesser;
}

function randomsort($a, $b) {
	if ($a['random'] == $b['random']) {
		return 0;
	}
	return ($a['random'] > $b['random']) ? -1 : 1;
}

function makeStatsUrl( $urlpage ) {
	global $type, $period, $album, $thumbSize;
	global $showCaption, $showAlbumLink, $showDescription;
	global $showUploadDate, $showViews, $showVotes;
//	global $showRatings;
	global $showComments, $showCaptureDate;
	global $showAddComment, $showAddVote, $showAlbumOwner, $showGrid, $numRows, $numCols;
	global $photosPerPage, $totalPhotosReq, $reverseOrder;
	global $timeMonth, $timeYear, $timeDay;
	$urlParams = array( "type" => $type,
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
		"sgr" => $showGrid,
		"rev" => $reverseOrder,
		"tsz" => $thumbSize,
		"ppp" => $photosPerPage,
		"rows" => $numRows,
		"cols" => $numCols,
		"total" => $totalPhotosReq);
	if ( isset($period) ) {
		$urlParams["period"] = $period;
	}
	if ( isset($album) ) {
		$urlParams["album"] = $album;
	}
	if ( isset($timeYear) ) {
		$urlParams["ty"] = $timeYear;
	}
	if ( isset($timeMonth) ) {
		$urlParams["tm"] = $timeMonth;
	}
	if ( isset($timeDay) ) {
		$urlParams["td"] = $timeDay;
	}

	$msStatsUrl = makeGalleryUrl( "stats.php", $urlParams);

	return $msStatsUrl;
}

// Get rating average value for all pics in statsalbum
function getRatingAverage() {
	global $statsAlbum;
	$results=array();
	$ratings=array();
	$results_count=array();
	$nv_pairs=$statsAlbum->getVoteNVPairs();
	$voters=array();
	foreach ($statsAlbum->fields["votes"] as $element => $image_votes) {
		$accum_votes=0;
		$count=0;
		foreach ($image_votes as $voter => $vote_value ) {
			$voters[$voter]=true;
			if ($vote_value> $statsAlbum->getPollScale()) { // scale has changed
				$vote_value=$statsAlbum->getPollScale();
			}
			$accum_votes+=$nv_pairs[$vote_value]["value"];
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
function showAddCommentLink( $photoId ) {
	global $statsAlbum;

	$url = "add_comment.php?set_albumName={$statsAlbum->fields['name']}&id=$photoId";
	echo  '<span class="fineprint">' .
	popup_link('[' . _("add comment") . ']', $url, 0) .
	"</span>";
}

// Show the add vote link
function showAddVoteLink( $photoId, $page ) {
	global $statsAlbum;

	$urlargs['set_albumName'] = $statsAlbum->fields['name'];
	$urlargs['id'] = $photoId;
	$urlargs['url'] = urlencode(makeStatsUrl( $page ));
	echo '<span class="fineprint">';
	echo '<a href="'. makeGalleryUrl("vote.php", $urlargs) . '">';
	echo "[". _("add vote") ."]";
	echo "</a></span>";
}

function getHeightFromTag($str) {
	$start = 'height="';
	$end = '"  border';
	$lenStr= strpos($str,$end) -strpos($str, $start);
	return substr(substr($str, strpos($str,$start), $lenStr), 8);
}

function getWidthFromTag($str) {
	$start = 'width="';
	$end = '" height=';
	$lenStr= strpos($str,$end) -strpos($str, $start);
	return substr(substr($str, strpos($str,$start), $lenStr), 7);
}

function displayTextCell($statsAlbum, $photoIndex, $photoId, $rating, $ratingcount ) {
	global $addLinksPos, $showAddComment, $showAddVote, $page, $showAlbumOwner, $showCaptureDate, $showUploadDate;
	global $showViews, $gallery, $showVotes;
//	global $showRatings;
	global $showComments, $newestCommentsFirst;
	global $showCaption, $showAlbumLink, $showDescription, $showGrid,  $imageCellWidth;

	if ( $showGrid ) {
		$statsAlign = "center";
		$statsWidth = $statsAlbum->fields['thumb_size'];
	}
	else {
		$statsAlign = "left";
		$statsWidth = "100%";
	}
?>
   <!-- Begin Text Cell -->
   <td align="<?php echo $statsAlign ?>" valign="top" class="albumdesc">
<?php

if ( $showCaption && !$showGrid ) {
?>
         <table cellpadding="0" cellspacing="0" width="100%" border="0" align="center" class="mod_title">
            <tr valign="middle">
               <td class="leftspacer">
                  <td>
                     <table cellspacing="0" cellpadding="0" border="0" class="mod_title_bg">
                        <tr valign="middle">
                           <td class="mod_title_left" align="right"></td>
                           <td wrap class="title" align="left">
<?php
	$statsCaption = $statsAlbum->getCaption($photoIndex);
	$statsCaption .= $statsAlbum->getCaptionName($photoIndex);
	$statsUrl = makeAlbumUrl($statsAlbum->fields['name'], $photoId);
	echo '<a href="'. $statsUrl .'">'. $statsCaption .'</a>&nbsp;&nbsp;';
	if ( $addLinksPos == 'oncaptionline' ) {
		echo '<span class="fineprint">&nbsp;&nbsp;';
                	if ( $showAddComment ) {
                        	showAddCommentLink( $photoId );
	                }
		if ( $showAddVote ) {
			echo "&nbsp;";
			showAddVoteLink( $photoId, $page );
		}
		echo '</span>';
}

?>
                           </td><td class="mod_title_right" align="left"></td>

                        </tr>
                     </table>
                  </td>
               </td>  <!-- Added during formatting -->
            </tr>
         </table>
         <table width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
               <td class="mod_titleunder_hl"></td>
            </tr>
         </table>
<?php
}

if ( $showCaption && $showGrid ) {
	echo '<table width="' . $statsAlbum->fields['thumb_size'] . '" border="0" cellpadding="0" cellspacing="4">';
	echo '<tr><td class="pcaption">';

	$statsCaption = $statsAlbum->getCaption($photoIndex);
	$statsCaption .= $statsAlbum->getCaptionName($photoIndex);
	$statsUrl = makeAlbumUrl($statsAlbum->fields['name'], $photoId);
	echo "$statsCaption";
	if ( $addLinksPos == 'oncaptionline' ) {
		echo '<span class="fineprint">&nbsp;&nbsp;';
		if ( $showAddComment ) {
			showAddCommentLink( $photoId );
		}
		if ( $showAddVote ) {
			echo '&nbsp;';
			showAddVoteLink( $photoId, $page );
		}
		echo '</span>';
	}
	echo '</td></table>';
}

if ( $showAlbumLink ) {
?>
         <span class="fineprint"><br clear="all"><?php echo _("From album") . ' '; /* Needs a trailing space */ ?>
<?php
$owner_var = '';
if ( $showAlbumOwner == 1 ) {
	$owner_var = '<br>' . _("Owned by:") . ' ' . showOwner($statsAlbum->getOwner());
}

echo '<a href="'. makeAlbumUrl($statsAlbum->fields['name']) .'">'. $statsAlbum->fields['title'] .'</a>'. $owner_var;
?>
         </span>
         <br clear all>
<?php
}

if ( $showDescription ) {
?>
         <span class="fineprint">
<?php
$description =$statsAlbum->getExtraField($photoIndex, "Description");
if ($description != "") {
	echo "<br clear=all>$description<br clear=all>";
}
?>
         </span>

<?php
}

if ( $addLinksPos == 'abovestats' ) {
	if ( $showAddComment ) {
		echo "<br clear=all>";
		showAddCommentLink( $photoId );
		if ( !$showAddVote ) {
			echo "<br clear=all>";
		}
	}

	if ( $showAddVote ) {
		if ( !$showAddComment ) {
			echo "<br clear=all>";
		}
		else {
			echo "&nbsp;&nbsp";
		}
		showAddVoteLink( $photoId, $page );
		echo "<br clear=all>";
	}
}
?>
      <br clear="all">
      <table cellpadding="0" cellspacing="0" width="<?php echo $statsWidth ?>" border="0" align="<?php echo $statsAlign ?>" class="fineprint">

<?php
if ( $showCaptureDate ) {
	$captureDate = strftime($gallery->app->dateTimeString, $statsAlbum->getItemCaptureDate($photoIndex));

	echo '<tr>';
	echo '<td width="105" class="fineprint">'. _("Capture Date:") .'</td>';
	echo '<td class="fineprint">'. $captureDate .'</td>';
	echo '</tr>';
}

if ( $showUploadDate ) {
	$time = $statsAlbum->getUploadDate($photoIndex);
	// Older albums may not have this field.
	if ($time) {
		$time = strftime($gallery->app->dateString,$time);
		echo '<tr>';
		echo '<td width="105" class="fineprint">'. _("Upload Date:") .'</td>';
		echo '<td class="fineprint">'. $time. '</td>';
		echo '</tr>';
	}
}

if ( $showViews &&
!($statsAlbum->fields["display_clicks"] == "no") &&
!$gallery->session->offline) {

	echo "\n<tr>";
	echo "\n\t". '<td width="105" class="fineprint">'. _("Views:") .'</td>';
	echo "\n\t". '<td class="fineprint">';
	echo pluralize_n2($statsAlbum->getItemClicks($photoIndex), "1 time", "times" , "0 times");
	echo "</td>";
	echo "\n</tr>";
}

if ( !empty($showVotes )) {
	echo "\n<tr>";
	echo "\n\t". '<td width="105" class="fineprint">' . _("Votes:") .'</td>';
	echo "\n\t". '<td class="fineprint">'. $statsAlbum->getItemSVotes($photoIndex) .'</td>';
	echo "\n</tr>";
}

if ( !empty($showRatings)) {
	switch ($rating) {
		case -2:
		$photoRateCounts = '';
		$photoRate = _("not rated");
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

	if ( $showGrid ) {
		echo "<tr>";
		echo '<td valign="top" width="105" class="fineprint">'. _("Rating:") .'</td>';
		echo '<td class="fineprint">'. $photoRate .'</td>';
		echo "</tr>";

		echo "<tr>";
		echo '<td width="105" class="fineprint"></td>';
		echo '<td class="fineprint">'. $photoRateCounts .'</td>';
		echo "</tr>";
	}
	else {
		echo "<tr>";
		echo '<td valign="top" width="105" class="fineprint">'. _("Rating:") .'</td>';
		echo '<td class="fineprint">' .$photoRate.$photoRateCounts;
		echo "</td>";
		echo "</tr>";
	}
}

echo "</table>";

if ( $addLinksPos == 'abovecomments' ) {
	if (  $showAddComment ) {
		echo "<br clear=all>";
		if ( !$showGrid ) {
			echo "<br clear=all>";
		}
		showAddCommentLink( $photoId );
	}

	if ( $showAddVote ) {
		if ( !$showAddComment ) {
			echo "<br clear=all>";
			if ( !$showGrid ) {
				echo "<br clear=all>";
			}
		}
		else
		echo "&nbsp;&nbsp";
		showAddVoteLink( $photoId, $page );
	}
}

if ( $showComments &&
$statsAlbum->numComments($photoIndex) > 0 &&
$statsAlbum->canViewComments($gallery->user->getUid()) ) {
	// Force the comment table below the previous table using clear all.
	echo "<br clear=all>";
	if ( !$showGrid ) {
		echo "<br clear=all>";
	}
	$gallery->album = $statsAlbum;
	viewComments($photoIndex, $gallery->user->canAddComments($statsAlbum), "DISCO1", $newestCommentsFirst, 'popup', $statsAlbum->fields['name']);
}

if ( $addLinksPos == 'belowcomments' ) {
	if ( $showAddComment ) {
		echo "<br clear=all>";
		if ( !$showGrid ) {
			echo "<br clear=all>";
		}
		showAddCommentLink( $photoId );
	}
	if ( $showAddVote ) {
		if ( !$showAddComment ) {
			echo "<br clear=all>";
			if ( !$showGrid ) {
				echo "<br clear=all>";
			}
		}
		else {
			echo "&nbsp;&nbsp";
		}
		showAddVoteLink( $photoId, $page );
	}
}
?>
   </td>
   <!-- End Text Cell -->
<?php
}
?>
