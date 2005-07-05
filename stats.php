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

//$album="album01";

list ($type, $sca, $sal, $sde, $sco, $scd, $sud, $svi, $sac, $svo, $sav, $sao, $stm, $reverse, $tsz ,$ppp, $total, $showGrid, $rows, $cols, $addLinksPos) =
    getRequestVar(array('type', 'sca', 'sal', 'sde', 'sco', 'scd', 'sud', 'svi', 'sac', 'svo', 'sav', 'sao', 'stm', 'reverse', 'tsz' ,'ppp', 'total', 'showGrid', 'rows', 'cols', 'addLinksPos'));

list ($ty, $tm, $td) = getRequestVar(array('ty', 'tm', 'td'));

list ($page, $set_albumListPage) =
    getRequestVar(array('page', 'set_albumListPage'));
if (empty($type)) {
    /* We assume was called direct. So we call show defaults */
    header("Location: ". unhtmlentities(defaultStatsUrl('views')));
}

/*
** Start of HTML Output to show page in valida HTML when debug is ON
*/

switch ($type) {
    case 'votes':
        if ( $reverse) {
            $stats_title =  _(" - Images with the least votes");
        }
        else {
            $stats_title =  _(" - Images with the most votes");
        }
    break;

    case 'ratings':
        if ( $reverse ) {
            $stats_title =  _(" - Bottom rated images");
        }
    else {
            $stats_title =  _(" - Top rated images");
    }
    break;

    case 'date':
        if ( $reverse ) {
            $stats_title =  _(" - Oldest images first");
        }
        else {

            $stats_title =  _(" - Latest added images");
        }
    break;

    case 'cdate':
        if ( $reverse ) {
            $stats_title =  _(" - Latest Capture Date");
        }
        else {
            $stats_title =  _(" - Oldest Capture Date");
        }
    break;

    case 'comments':
        if ( $reverse ) {
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
    if ( $reverse ) {
        $stats_title =  _(" - Images with the least views");
    }
    else {
        $stats_title =  _(" - Images with the most views");
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
/* End of HTML begin, lets do some calculations etc. */

if ($set_albumListPage < 0) {
    $set_albumListPage = 1;
}

$rating = '';
$ratingCount = '';
$randomNum = '';
$pixelImage = '<img src="' . getImagePath('pixel_trans.gif') . '" width="1" height="1" alt="pixel_trans">';

$time_start = getmicrotime();

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

debugMessage(sprintf (_("Number Albums = %s") . "<br>", $numAlbums),__FILE__ , __LINE__) ;

$skip = array();
$arrPhotos = array();

$useCache = false;
$cacheReloadRequired = false;

// Attempt to load from cache if cache is selected
// and a period or album have not been specified.
$cacheFilename = $gallery->app->albumDir . "/stats.$type.$reverse.cache";
debugMessage(sprintf (_("Cache filename = %s ; enabled = %s ; expires = %s"), $cacheFilename, $cache->enabled, $cache->expireSecs), __FILE__, __LINE__);

if (!isset($refreshcache) &&
    !isset($period) &&
    !isset($album)  &&
    $cache->enabled ) {
    if (fs_file_exists($cacheFilename)) {
        $cacheState = fs_stat($cacheFilename);
        $cacheTime = $cacheState[9];
        if ( $cache->expireSecs == -1 ||
        time() - $cacheTime < $cache->expireSecs) {
            debugMessage(sprintf (_("Time now = %s ; Cache time = %s"), time(), $cacheTime), __FILE__, __LINE__);
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
        debugMessage(_("Logged In - Disabling Cache"), __FILE__, __LINE__);
        $refreshcache = false;
        $useCache = false;
    }
}

debugMessage((!empty($useCache)) ? _("Using cache") : _("Not using cache") ,__FILE__, __LINE__);
debugMessage((!empty($refreshcache)) ? _("Cache to be rebuilt") : _("Cache will not rebuild.") , __FILE__, __LINE__);

// Check if photo data will be loaded from the caches.
// If it isn't, then load the photos data and sort.
if (empty($useCache)) {
    if (!empty($period )) {
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
        debugMessage("<hr>", __FILE__, __LINE__);
        $statsAlbum = $list[$i];
        //		print_r($statsAlbum);
        if ($statsAlbum->versionOutOfDate()) {
            debugMessage(_("Version out of date."), __FILE__, __LINE__, 2);
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
            debugMessage(sprintf (_("Checking album: %s"), $statsAlbum->fields['name']), __FILE__, __LINE__);

            // Haplo code to make sense of the Gallery rankings.
            /*
            if ( isset($showRatings) || $type == "ratings" ) {
            $ratingAverageList = getRatingAverage();
            }
            */
            $numPhotos = $statsAlbum->numPhotos(1);
            for ($j = 1; $j <= $numPhotos; $j++) {
                debugMessage(sprintf (_("Reading info for photo index = %d , id = %d"), $j, $statsAlbum->getPhotoId($j)), __FILE__, __LINE__, 2);

                if (! $statsAlbum->isAlbum($j) && (!$statsAlbum->isHidden($j) || $gallery->user->isAdmin())) {
                    $uploaddate = $statsAlbum->getUploadDate($j);

                    if ( strftime("%Y",$uploaddate ) * 12 + strftime("%m",$uploaddate) >= $cutoffDate ) {
                        // If displaying latest comments,
                        // then only list photos with comments;
                        // otherwise display all.
                        if ( $type != "comments" || ( $statsAlbum->numComments($j) > 0 && $statsAlbum->canViewComments($uid) )) {
                            if ( isset($showVotes) || $type == "votes" ) {
                                debugMessage(_("Getting SVotes"), __FILE__, __LINE__, 2);
                                $votes = $statsAlbum->getItemSVotes($j);
                            } else {
                                $votes = '';
                            }

                            debugMessage(_("Getting Item Clicks"), __FILE__, __LINE__, 2);
                            $views = $statsAlbum->getItemClicks($j);

                            debugMessage(_("Getting Item Capture Date"), __FILE__, __LINE__, 2);
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


                            debugMessage(_("Getting Number of Comments"), __FILE__, __LINE__, 2);
                            if ( $statsAlbum->numComments($j) > 0 )  {
                                debugMessage(_("Getting Comments"), __FILE__, __LINE__, 2);
                                $comment = $statsAlbum->getComment( $j, $statsAlbum->numComments($j) );
                                $commentDate = $comment->datePosted;
                            }
                            else {
                                $commentDate = 0;
                            }
                            /*
                            if ( isset($showRatings) || $type == "ratings" ) {
                            debugMessage(_("Getting Ratings"), __FILE__, __LINE__, 2);

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
                            debugMessage(sprintf (_("Album: %s ; Index: %d ; Votes: %d ; Views: %d; Date: %s; Capture: %s; Comment Date: %s; Rating: %s; Rating count: %d; Random: %d"). "<br>",
                                $statsAlbum->fields['name'], $j, $votes, $views, $uploaddate, $captureDate, $commentDate, $rating, $ratingCount, $randomNum) 
				, __FILE__, __LINE__, 1);
                        }
                    }
                }
            }
        }
    }

    $order = ($reverse) ? 'asc' : 'desc';
    if ( is_array($arrPhotos) ) {
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
debugMessage(sprintf(_("Total: %s ; Start: %s ; Last Page: %s"), $totalPhotosToDisplay, $startPhoto, $lastpage), __FILE__, __LINE__);

$borderColor = $gallery->app->default["bordercolor"];
if ( isset($tsz) ) {
    $thumbSize = $tsz;
}
else {
    $thumbSize = $gallery->app->default["thumb_size"];
}

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
echo languageSelector();
echo "<!-- End top nav -->";

if ( $useCache ) {
    readCache($cacheFilename, $startPhoto, $photosPerPage );
}
if (isset($stm)) {
    $time = getmicrotime() - $time_start;
    echo sprintf(_("Data load time %d seconds"), $time);
}

/* Start of album layout style. */

if (empty($showGrid)) {
    $cols = 1;
    $style = 'style="margin-right:3px; float:left"';
} else {
    $style = '';
}

echo '<br clear="all">';

$statsTable = new galleryTable();
$statsTable->setColumnCount(2 * $cols);
$statsTable->setAttrs(array(
    'id' => 'statsTable',
    'width' => $navigator["fullWidth"] . $navigator["widthUnits"],
    'border' => 0,
    'cellspacing' => 7));

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
                if (!$statsAlbum->isHidden($photoIndex) || $statsAlbum->isOwner($uid) || $gallery->user->isAdmin()) {
                    $statsCaption = $statsAlbum->getCaption($photoIndex);
                    $statsCaption .= $statsAlbum->getCaptionName($photoIndex);
                    $statsUrl = makeAlbumUrl($statsAlbum->fields['name'], $photoId);

                    // Image Cell
                    $statsTable->addElement(array(
                        'content' => "<a href=\"$statsUrl\">". $statsAlbum->getThumbnailTag($photoIndex, $thumbSize) . "</a>",
                        'cellArgs' => array('align' => 'center', 'valign' => 'top')));

                    //  Text Cell -->
                    $statsTable->addElement(array(
                    'content' => displayTextCell($statsAlbum, $photoIndex, $photoId, $photoInfo['rating'], $photoInfo['ratingcount']),
                    'cellArgs' => array('align' => 'left', 'valign' => 'top', 'class' => 'albumdesc')));
                }
            }
        }
    }
}

echo $statsTable->render();

$time = getmicrotime() - $time_start;
echo infoLine(sprintf (_("Finished in %d seconds"), $time), 'success1');

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

// <!-- bottom nav -->

includeLayout('navtablebegin.inc');
includeLayout('navigator.inc');
includeLayout('navtableend.inc');

echo languageSelector();
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
    global $list, $gallery;
    if ($parentAlbum) {
        debugMessage(sprintf(_("Recursing album: %s"),  $parentAlbum->fields['name']),__FILE__, __LINE__, 2);

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
            debugMessage(_("Read cache num photos lock failed."), __FILE__, __LINE__, 2);
        }
        fclose($fd);
    }

    return $numPhotos;
}

function readCache( $cacheFilename, $start, $numPhotos ) {
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
                if ( $data ) {
                    debugMessage(sprintf(_("Album name : %s ; index: %s"), $data[0], $data[1]), __FILE__, __LINE__, 1);

                    $arrPhotos[$start+$i] = array("albumName" => $data[0],
			'photoId' => $data[1],
			'rating' => $data[2],
                        'ratingcount' => $data[3] );
                }
            }
            myFlock($fd, LOCK_UN);
        }
        else {
            debugMessage(_("Read cache lock failed."), __FILE__, __LINE__, 2);
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
            debugMessage(_("Read cache lock failed."), __FILE__, __LINE__, 2);
        }
        fclose($fd);
    }
}

function makeStatsUrl( $urlpage ) {
    global $type, $period, $album, $thumbSize;
    global $showCaption, $showAlbumLink, $showDescription;
    global $showUploadDate, $showViews, $showVotes;
    //	global $showRatings;
    global $showComments, $showCaptureDate;
    global $showAddComment, $showAddVote, $showAlbumOwner, $showGrid, $numRows, $cols;
    global $photosPerPage, $totalPhotosReq, $reverse;
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
    "showGrid" => $showGrid,
    "reverse" => $reverse,
    "tsz" => $thumbSize,
    "ppp" => $photosPerPage,
    "rows" => $numRows,
    "cols" => $cols,
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
    return '<span class="fineprint">' . popup_link('[' . _("add comment") . ']', $url, 0) . "</span>";
}

// Show the add vote link
function showAddVoteLink( $photoId, $page ) {
    global $statsAlbum;

    $urlargs['set_albumName'] = $statsAlbum->fields['name'];
    $urlargs['id'] = $photoId;
    $urlargs['url'] = urlencode(makeStatsUrl( $page ));

    $addVoteLink = '<span class="fineprint">';
    $addVoteLink .= '<a href="'. makeGalleryUrl("vote.php", $urlargs) . '">';
    $addVoteLink .= "[". _("add vote") ."]";
    $addVoteLink .= "</a></span>";

    return $addVoteLink;
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
        $text = '&nbsp;<span class="fineprint">';
        if ($showAddComment) {
            $text .= showAddCommentLink($photoId);
        }
       if ($showAddVote) {
            $text .= "&nbsp;";
            $text .= showAddVoteLink($photoId, $page);
        }
        $text .= '</span>';
    }
    return $text;
}

function displayTextCell($statsAlbum, $photoIndex, $photoId, $rating, $ratingcount ) {
    global $addLinksPos, $showAddComment, $showAddVote, $page, $showAlbumOwner, $showCaptureDate, $showUploadDate;
    global $showViews, $gallery, $showVotes;
    //	global $showRatings;
    global $showComments, $newestCommentsFirst;
    global $showCaption, $showAlbumLink, $showDescription, $showGrid;

    $html = '';

    if ($showCaption) {
        $captionTable = new galleryTable();
        $captionTable->setAttrs(array(
            'width' => '100%',
            'border' => 0,
            'cellspacing' => 0,
            'cellpadding' => 0,
            'class' => 'mod_title'));

        $statsCaption = $statsAlbum->getCaption($photoIndex);
        $statsCaption .= $statsAlbum->getCaptionName($photoIndex);
        $statsUrl = makeAlbumUrl($statsAlbum->fields['name'], $photoId);

        $captionText = '<a href="'. $statsUrl .'">'. $statsCaption .'</a>&nbsp;&nbsp;';

        if ( $addLinksPos == 'oncaptionline' ) {
            $captionText .= showAddVoteAddCommentLinks($photoId, $page);
        }

        $captionTextTable = new galleryTable();
        $captionTextTable->setAttrs(array(
            'border' => 0,
            'cellspacing' => 0,
            'cellpadding' => 0,
            'class' => 'mod_title_bg'));

        $captionTextTable->addElement(array(
            'content' => '',
            'cellArgs' => array('class' => 'mod_title_left', 'align' => 'right')));
        $captionTextTable->addElement(array(
            'content' => $captionText,
            'cellArgs' => array('class' => 'title', 'align' => 'left')));
        $captionTextTable->addElement(array(
            'content' => '',
            'cellArgs' => array('class' => 'mod_title_right', 'align' => 'left')));

        $captionTable->addElement(array(
            'content' => $captionTextTable->render(2),
            'cellArgs' => ''));

        $html = $captionTable->render(1);
    }

    // End Caption

    if ( $showAlbumLink ) {
        $albumLink = sprintf(_("From album: %s"),
        '<a href="'. makeAlbumUrl($statsAlbum->fields['name']) .'">'. $statsAlbum->fields['title'] . '</a>');

        $owner_var = '';
        if ( $showAlbumOwner == 1 ) {
            $owner_var = '<br>' . sprintf(_("Owned by: %s"), showOwner($statsAlbum->getOwner()));
        }

        $html .= "\n    " . '<div class="fineprint">'. $albumLink . $owner_var . '</div>';
    }

    if ($showDescription) {
        $description = $statsAlbum->getExtraField($photoIndex, "Description");
        if ($description != "") {
            $html .= "\n    ". '<div class="fineprint" style="margin-top:10px;">'. $description .'</div>';
        }
    }

    if ( $addLinksPos == 'abovestats' ) {
        $html .= showAddVoteAddCommentLinks($photoId, $page);
    }

    /* Begin Inner Stats */

    $innerStatsTable = new galleryTable();
    $innerStatsTable->setAttrs(array(
        'border' => 0,
        'cellspacing' => 0,
        'cellpadding' => 0,
        'class' => 'fineprint'));

    $innerStatsTable->setColumnCount(2);

    if ( $showCaptureDate ) {
        $captureDate = strftime($gallery->app->dateTimeString, $statsAlbum->getItemCaptureDate($photoIndex));

        $innerStatsTable->addElement(array(
            'content' => _("Capture Date:"),
            'cellArgs' => array('width' => 100)));

        $innerStatsTable->addElement(array(
            'content' => $captureDate,
            'cellArgs' => array('class' => 'fineprint')));
    }

    if ( $showUploadDate ) {
        $time = $statsAlbum->getUploadDate($photoIndex);
        // Older albums may not have this field.
        if ($time) {
            $time = strftime($gallery->app->dateString,$time);
            $innerStatsTable->addElement(array(
                'content' => _("Upload Date:"),
                'cellArgs' => array('width' => 100)));

            $innerStatsTable->addElement(array(
                'content' => $time,
                'cellArgs' => array('class' => 'fineprint')));
        }
    }

    if ( $showViews &&
    !($statsAlbum->fields["display_clicks"] == "no") &&
    !$gallery->session->offline) {

        $innerStatsTable->addElement(array(
        'content' => _("Views:"),
        'cellArgs' => array('width' => 100)));

        $innerStatsTable->addElement(array(
        'content' => pluralize_n2($statsAlbum->getItemClicks($photoIndex), "1 time", "times" , "0 times"),
        'cellArgs' => array('class' => 'fineprint')));
    }

    if ( !empty($showVotes )) {
        $innerStatsTable->addElement(array(
            'content' => _("Votes:"),
            'cellArgs' => array('width' => 100)));

        $innerStatsTable->addElement(array(
            'content' => $statsAlbum->getItemSVotes($photoIndex),
            'cellArgs' => array('class' => 'fineprint')));
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

        $innerStatsTable->addElement(array(
            'content' => _("Rating:"),
            'cellArgs' => array('width' => 100)));

        $innerStatsTable->addElement(array(
            'content' => $photoRate .' | '. $photoRateCounts,
            'cellArgs' => array('class' => 'fineprint')));
    }

    $html .= $innerStatsTable->render(1);
    // End Innerstats

    if ( $addLinksPos == 'abovecomments' ) {
        $html .= showAddVoteAddCommentLinks($photoId, $page);
    }

    if ( $showComments &&
    $statsAlbum->numComments($photoIndex) > 0 &&
    $statsAlbum->canViewComments($gallery->user->getUid()) ) {

        $gallery->album = $statsAlbum;
        $html .= '<br clear="all">'. showComments($photoIndex, $statsAlbum->fields['name']);
    }

    if ( $addLinksPos == 'belowcomments' ) {
        $html .= showAddVoteAddCommentLinks($photoId, $page);
    }

    return $html;
}
?>
