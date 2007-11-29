<?php
/*
* Gallery - a web based photo album viewer and editor
* Copyright (C) 2000-2007 Bharat Mediratta
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

$gallery->session->offlineAlbums[$gallery->album->fields["name"]]=true;

$page = intval($page);
if (empty($page) || $page < 0) {
    if (isset($gallery->session->albumPage[$gallery->album->fields['name']])) {
        $page = $gallery->session->albumPage[$gallery->album->fields["name"]];
	}
	else {
        $page = 1;
    }
}
else {
    $gallery->session->albumPage[$gallery->album->fields["name"]] = $page;
}

$albumName = $gallery->session->albumName;

$noCount = getRequestVar('noCount');
if ($noCount != 1 && !isset($gallery->session->viewedAlbum[$albumName]) &&
	!$gallery->session->offline)
{
    $gallery->session->viewedAlbum[$albumName] = 1;
    $gallery->album->incrementClicks();
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

if (!empty($Vote) && canVote()) {
    if ($gallery->album->getPollScale() == 1 && $gallery->album->getPollType() != "rank") {
		for ($index = $start; $index < $start+$perPage; $index ++) {
			$id = $gallery->album->getPhotoId($index);
            if (!$votes[$id]) {
                $votes[$id]=null;
            }
        }
    }
    saveResults($votes);
}

$bordercolor = $gallery->album->fields["bordercolor"];

$imageCellWidth = floor(100 / $cols) . "%";

$navigator['page'] = $page;
$navigator['pageVar'] = 'page';
$navigator['maxPages'] = $maxPages;
$navigator['fullWidth'] = '100';
$navigator['widthUnits'] = '%';
$navigator['url'] = makeAlbumUrl($gallery->session->albumName);
$navigator['spread'] = 5;
$navigator['bordercolor'] = $bordercolor;

$fullWidth = $navigator['fullWidth'] . $navigator['widthUnits'];

$upArrowURL = gImage('nav_home.gif', gTranslate('core', "navigate UP"));

if ($gallery->album->fields['returnto'] != 'no') {
    foreach ($gallery->album->getParentAlbums() as $navAlbum) {
        $breadcrumb["text"][] = $navAlbum['prefixText'] .': <a class="bread" href="'. $navAlbum['url'] . '">'.
        $navAlbum['title'] . "&nbsp;" . $upArrowURL . "</a>";
    }
}

$breadcrumb['bordercolor'] = $bordercolor;

global $GALLERY_EMBEDDED_INSIDE;
if (!$GALLERY_EMBEDDED_INSIDE) {
    $title = sprintf(
        htmlspecialchars($gallery->app->galleryTitle) .
		' :: ' .
		htmlspecialchars($gallery->album->fields['title'])
    );

    doctype();
?>
<html>
<head>
  <title><?php echo $title ?></title>
  <?php common_header();
  /* RSS */
  if ($gallery->app->rssEnabled == "yes" && !$gallery->session->offline) {
  	$rssTitle = sprintf(gTranslate('core', "%s RSS"), $title);
	$rssHref = $gallery->app->photoAlbumURL . "/rss.php?set_albumName=" . $gallery->album->fields["name"];

	echo "<link rel=\"alternate\" title=\"$rssTitle\" href=\"$rssHref\" type=\"application/rss+xml\">";
  }
  /* prefetching/navigation */
  $firstUrl  = makeAlbumUrl($gallery->session->albumName, '', array('page' => 1, 'noCount' => 1));
  $prevUrl   = makeAlbumUrl($gallery->session->albumName, '', array('page' => $previousPage, 'noCount' => 1));
  $nextUrl   = makeAlbumUrl($gallery->session->albumName, '', array('page' => $nextPage, 'noCount' => 1));
  $lastUrl   = makeAlbumUrl($gallery->session->albumName, '', array('page' => $maxPages, 'noCount' => 1));
  $upUrl	 = makeAlbumUrl($gallery->album->fields['parentAlbumName'], '', array('page' => $maxPages, 'noCount' => 1));

  if (!isset($first)) { ?>
  <link rel="first" href="<?php echo $firstUrl; ?>" >
  <link rel="prev" href="<?php echo $prevUrl; ?>" >
<?php }
  if (!isset($last)) { ?>
  <link rel="next" href="<?php echo $nextUrl; ?>" >
  <link rel="last" href="<?php echo $lastUrl; ?>" >
<?php } if ($gallery->album->isRoot() &&
(!$gallery->session->offline ||
	 isset($gallery->session->offlineAlbums["albums.php"]))) { ?>
  <link rel="up" href="<?php echo makeAlbumUrl(); ?>" >
<?php
	 } else if (!$gallery->session->offline ||
	 isset($gallery->session->offlineAlbums[$pAlbum->fields['parentAlbumName']])) { ?>
  <link rel="up" href="<?php echo $upUrl; ?>" >
<?php }
if (!$gallery->session->offline ||
	 isset($gallery->session->offlineAlbums["albums.php"])) { ?>
  <link rel="top" href="<?php echo makeGalleryUrl('albums.php', array('set_albumListPage' => 1)) ?>" >
<?php } ?>
  <style type="text/css">
<?php
// the link colors have to be done here to override the style sheet
if ($gallery->album->fields["linkcolor"]) {
    ?>
    A:link, A:visited, A:active
      { color: <?php echo $gallery->album->fields['linkcolor'] ?>; }
    A:hover
      { color: #ff6600; }
<?php
}
if ($gallery->album->fields["bgcolor"]) {
    echo "BODY { background-color:".$gallery->album->fields['bgcolor']."; }";
}
if (isset($gallery->album->fields['background']) && $gallery->album->fields['background']) {
    echo "BODY { background-image:url(".$gallery->album->fields['background']."); } ";
}
if ($gallery->album->fields["textcolor"]) {
    echo "BODY, TD {color:".$gallery->album->fields['textcolor']."; }";
    echo ".head {color:".$gallery->album->fields['textcolor']."; }";
    echo ".headbox {background-color:".$gallery->album->fields['bgcolor']."; }";
}
?>
  </style>
</head>

<body dir="<?php echo $gallery->direction ?>">
<?php
}

includeHtmlWrap("album.header");

if (!$gallery->session->offline) { ?>

  <script language="javascript1.2" type="text/JavaScript">
  <!-- //
  var statusWin;
  function showProgress() {
      statusWin = <?php echo popup_status("progress_uploading.php"); ?>
  }

  function hideProgress() {
      if (typeof(statusWin) != "undefined") {
          statusWin.close();
          statusWin = void(0);
      }
  }

  function hideProgressAndReload() {
      hideProgress();
      location.reload();
  }
  //-->
  </script>
<?php }

$adminText = '';
$albums_str = gTranslate('core', "1 sub-album", "%d sub-albums", $numAlbums, gTranslate('core', "No albums"));
$imags_str = gTranslate('core', "1 image", "%d images", $numPhotos, gTranslate('core', "no images"));
$pages_str = gTranslate('core', "1 page", "%d pages", $maxPages, gTranslate('core', "0 pages"));

if ($numAlbums && $maxPages > 1) {
    $adminText .= sprintf(gTranslate('core', "%s and %s in this album on %s"),
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

if ($gallery->user->canWriteToAlbum($gallery->album) &&
!$gallery->session->offline) {
    $hidden = $gallery->album->numHidden();
    $verb = gTranslate('core', "%s are hidden");
    if ($hidden == 1) {
        $verb = gTranslate('core', "%s is hidden");
    }
    if ($hidden) {
        $adminText .= "(".sprintf($verb, $hidden).")";
    }
}

/* admin items for drop-down menu */
$adminOptions = array(
    'add_photos'	=> array(
        'name' 	=> gTranslate('core', "Add photos"),
        'requirements' => array('canAddToAlbum'),
        'action' 	=> 'popup',
        'value' 	=> makeGalleryUrl('add_photos_frame.php',
            array('set_albumName' => $gallery->session->albumName, 'type' => 'popup'))),
	'delete_root_album'	=> array(
        'name' => gTranslate('core', "Delete this album"),
        'requirements' => array('canDeleteAlbum', 'albumIsRoot'),
        'action' => 'popup',
        'value' => makeGalleryUrl('delete_album.php', array('type' => 'popup'))),
	'delete_sub_album'	=> array(
        'name' => gTranslate('core', "Delete this (sub)album"),
        'requirements' => array('canDeleteAlbum', '!albumIsRoot'),
        'action' => 'popup',
        'value' => makeGalleryUrl('delete_photo.php',
			array('set_albumName' => $gallery->album->fields["parentAlbumName"],
				'type' => 'popup',
				'id' => $gallery->album->fields["name"],
				'albumDelete' => true))),
	'rename_album'	=> array(
        'name' => gTranslate('core', "Rename album"),
        'requirements' => array('isAdminOrAlbumOwner'),
        'action' => 'popup',
        'value' => makeGalleryUrl('rename_album.php', array(
            'set_albumName' => $gallery->session->albumName,
            'type' => 'popup', 'useLoad' => 1))),
	'nested_album'	=> array(
        'name' => gTranslate('core', "New nested album"),
        'requirements' => array('canCreateSubAlbum', 'notOffline'),
        'action' => 'url',
        'value' => doCommand('new-album',
            array('parentName' => $gallery->session->albumName), 'view_album.php')),
    'edit_captions'   => array(
        'name' => gTranslate('core', "Edit captions"),
        'requirements' => array('canChangeText','notOffline'),
        'action' => 'url',
        'value' => makeGalleryUrl('captionator.php',
            array('set_albumName' => $gallery->session->albumName, 'page' => $page, 'perPage' => $perPage))),
	'sort_items'	  => array(
        'name' => gTranslate('core', "Sort items"),
        'requirements' => array('canWriteToAlbum', 'photosExist'),
        'action' => 'popup',
        'value' => makeGalleryUrl('sort_album.php',
            array('set_albumName' => $gallery->session->albumName, 'type' => 'popup'))),
	'resize_all'	  => array(
        'name' => gTranslate('core', "Resize all"),
        'requirements' => array('canWriteToAlbum', 'photosExist'),
        'action' => 'popup',
        'value' => makeGalleryUrl('resize_photo.php',
            array('set_albumName' => $gallery->session->albumName, 'index' => 'all', 'type' => 'popup'))),
    'rebuild_thumbs'  => array(
        'name' => gTranslate('core', "Rebuild thumbs"),
        'requirements' => array('canWriteToAlbum', 'photosExist'),
        'action' => 'popup',
        'value' => doCommand('remake-thumbnail',
            array('set_albumName' => $gallery->session->albumName, 'index' => 'all', 'type' => 'popup'))),
    'rebuild_capture_dates' => array(
		'name'		  => gTranslate('core', "Rebuild capture dates"),
		'requirements'  => array('canWriteToAlbum', 'photosExist', 'exif'),
		'action'		=> 'popup',
		'value'		 => makeGalleryUrl('rebuild_capture_dates.php',
							array('set_albumName' => $gallery->session->albumName,'type' => 'popup'))),
	'properties'	  => array(
        'name' => gTranslate('core', "Properties"),
        'requirements' => array('canWriteToAlbum'),
        'action' => 'popup',
        'value' => makeGalleryUrl('edit_appearance.php',
            array('set_albumName' => $gallery->session->albumName, 'type' => 'popup'))),
    'rearrange' => array(
        'name' => gTranslate('core', "Rearrange items"),
        'requirements' => array('canWriteToAlbum', 'photosExist'),
        'action' => 'popup',
        'value' => makeGalleryUrl('rearrange.php',
            array('set_albumName' => $gallery->session->albumName, 'type' => 'popup'))),
	'permissions'	 => array(
        'name' => gTranslate('core', "Permissions"),
        'requirements' => array('isAdminOrAlbumOwner'),
        'action' => 'popup',
        'value' => makeGalleryUrl('album_permissions.php',
            array('set_albumName' => $gallery->session->albumName, 'type' => 'popup'))),
	'poll_results'	=> array(
        'name' => gTranslate('core', "Poll results"),
        'requirements' => array('isAdminOrAlbumOwner'),
        'action' => 'url',
        'value' => makeGalleryUrl('poll_results.php',
            array('set_albumName' => $gallery->session->albumName,))),
	'poll_reset'	  => array(
        'name' => gTranslate('core', "Poll reset"),
        'requirements' => array('isAdminOrAlbumOwner'),
        'action' => 'popup',
        'value' => makeGalleryUrl('reset_votes.php',
            array('set_albumName' => $gallery->session->albumName, 'type' => 'popup'))),
    'view_comments'   => array(
        'name' => gTranslate('core', "View comments"),
        'requirements' => array('isAdminOrAlbumOwner', 'allowComments', 'comments_enabled', 'hasComments'),
        'action' => 'url',
        'value' => makeGalleryUrl('view_comments.php',
            array('set_albumName' => $gallery->session->albumName))),
    'watermark_album'   => array(
        'name' => gTranslate('core', "Watermark album"),
        'requirements' => array('isAdminOrAlbumOwner','photosExist','watermarkingEnabled'),
        'action' => 'popup',
        'value' => makeGalleryUrl('watermark_album.php',
            array('set_albumName' => $gallery->session->albumName, 'type' => 'popup'))),
);

/* sort the drop-down array by translated name */
array_sort_by_fields($adminOptions, 'name', 'asc', true, true);

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

$iconElements = array();

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

    $iconElements[] = "\n\t<select class=\"adminform\" name=\"admin_select\" onChange=\"execAdminOption()\">\n"
    . "\t\t<option value=\"\">&laquo; " . gTranslate('core', 'Album Actions') . " &raquo;</option>\n"
    . $adminOptionHTML
    . "\t</select>\n";
}

if ($gallery->album->fields["slideshow_type"] != "off" &&
($numPhotos != 0 || ($numVisibleItems != 0 && $gallery->album->fields['slideshow_recursive'] == "yes"))) {
    $iconText = getIconText('display.gif', gTranslate('core', "Slideshow"));
    $iconElements[] = '<a href="'
    . makeGalleryUrl("slideshow.php",
    array("set_albumName" => $albumName)) .'">'. $iconText .'</a>';
}

/* User is allowed to view ALL comments */
if ($numVisibleItems != 0 &&
	($gallery->app->comments_enabled == 'yes' && $gallery->album->lastCommentDate("no") != -1) &&
((isset($gallery->app->comments_overview_for_all) && $gallery->app->comments_overview_for_all == "yes") ||
	$gallery->user->canViewComments($gallery->album)))
{
    $iconText = getIconText('showcomment.gif', gTranslate('core', "View&nbsp;comments"));
    $iconElements[] = '<a href="'.
    makeGalleryUrl("view_comments.php",
    array("set_albumName" => $gallery->session->albumName)) . '">'. $iconText .'</a>';
}

if (!$GALLERY_EMBEDDED_INSIDE && !$gallery->session->offline) {
    if ($gallery->user->isLoggedIn()) {
        $iconText = getIconText('exit.gif', gTranslate('core', "Logout"));
        $iconElements[] = '<a href="'.
        doCommand("logout", array(), "view_album.php",
        array("page" => $page, 'set_albumName' => $albumName)) .'">'. $iconText .'</a>';
	}
	else {
        $iconText = getIconText('identity.gif', gTranslate('core', "Login"));
        $iconElements[] = popup_link($iconText, "login.php", false, true, 500, 500);
    }
}

$adminbox["text"] = $adminText;
$adminbox["commands"] =	$adminCommands . makeIconMenu($iconElements, 'right');
$adminbox["bordercolor"] = $bordercolor;

if (!empty($adminOptionHTML)) {
    print $adminJSFrame;
}
includeLayout('navtablebegin.inc');
includeLayout('adminbox.inc');
?>

<!-- top nav -->
<?php
$breadcrumb["top"] = true;
$breadcrumb['bottom'] = false;
if (!empty($breadcrumb["text"]) || $gallery->user->isLoggedIn()) {
    includeLayout('navtablemiddle.inc');
    includeLayout('breadcrumb.inc');
}
if ($navigator["maxPages"] > 1) {
    includeLayout('navtablemiddle.inc');
    includeLayout('navigator.inc');
}
includeLayout('navtableend.inc');


#-- if borders are off, just make them the bgcolor ----
$borderwidth = $gallery->album->fields["border"];
if ($borderwidth == 0) {
    $bordercolor = $gallery->album->fields["bgcolor"];
    $borderwidth = 1;
}

if ($page == 1 && !empty($gallery->album->fields["summary"])) {
    echo '<div align="center"><p class="vasummary">'. $gallery->album->fields["summary"] . '</p></div>';
}

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

        echo "\n<div class=\"g-va-poll-box1\">\n";
        echo $va_poll_box1;
        echo "\n</div>\n";
    }
}

list($va_poll_result, $results) = showResultsGraph( $gallery->album->getPollNumResults());

if ($gallery->album->getPollShowResults()) {
    echo $va_poll_result;
}

if(!empty($results) && testRequirement('isAdminOrAlbumOwner')) {
    echo galleryLink(
        makeGalleryUrl("poll_results.php", array("set_albumName" => $gallery->session->albumName)),
        gTranslate('core', "[ See full poll results ]"),
        array('class' => 'admin'));
}

?>

   <script language="javascript1.2" type="text/JavaScript">
   function chooseOnlyOne(i, form_pos, scale) {
       for(var j=0;j<scale;j++) {
           if(j != i) {
               eval("document.vote_form['votes["+j+"]']["+form_pos+"].checked=false");
           }
       }
   }
   </script>
<?php

echo makeFormIntro('view_album.php',
    array('name' => 'vote_form', 'style' => 'margin-bottom: 0px;'));

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

	echo "\n<div class=\"g-va-poll-box3\">\n";
    echo $va_poll_box3;
    echo "\n</div>\n";
?>
	<div align="center">
 		<input type=submit name="Vote" value="<?php print gTranslate('core', "Vote") ?>">
	</div>

<?php
}
?>

<!-- image grid table -->
<table border="0" cellspacing="5" cellpadding="0" width="100%" class="vatable" align="center">
<?php
$numPhotos = $gallery->album->numPhotos(1);
$displayCommentLegend = 0;  // this determines if we display "* Item contains a comment" at end of page

if ($numPhotos) {
    $rowCount = 0;

    // Find the correct starting point, accounting for hidden photos
    $rowStart = 0;
    $cnt = 0;
    $form_pos=0; // counts number of images that have votes below, ie withou albums;
    $rowStart = $start;

    while ($rowCount < $rows) {
        /* Do the inline_albumthumb header row */
        $visibleItemIndex = $rowStart;
        $i = $visibleItemIndex<=$numVisibleItems ? $visibleItems[$visibleItemIndex] : $numPhotos+1;
        $j = 1;
        $printTableRow = false;
        if ($j <= $cols && $i <= $numPhotos) {
            $printTableRow = true;
        }
        while ($j <= $cols && $i <= $numPhotos) {
            $j++;
            $visibleItemIndex++;
            $i = $visibleItemIndex <= $numVisibleItems ? $visibleItems[$visibleItemIndex] : $numPhotos+1;
        }

        /* Do the picture row */
        $visibleItemIndex = $rowStart;
        $i = $visibleItemIndex <= $numVisibleItems ? $visibleItems[$visibleItemIndex] : $numPhotos+1;
        $j = 1;
        if ($printTableRow) {
            echo('<tr>');
        }
        while ($j <= $cols && $i <= $numPhotos) {
            echo("<td align=\"center\" valign=\"top\" class=\"vathumbs\">\n");

            //-- put some parameters for the wrap files in the global object ---
            $gallery->html_wrap['borderColor'] = $bordercolor;
            $borderwidth= $gallery->html_wrap['borderWidth'] = $borderwidth;
            $gallery->html_wrap['pixelImage'] = getImagePath('pixel_trans.gif');

            if ($gallery->album->isAlbum($i)) {
                $scaleTo = 0; //$gallery->album->fields["thumb_size"];
                $myAlbum = $gallery->album->getNestedAlbum($i);
                list($iWidth, $iHeight) = $myAlbum->getHighlightDimensions($scaleTo);
			}
			else {
                unset($myAlbum);
				$scaleTo = 0;  // thumbs already the right
                //	size for this album
                list($iWidth, $iHeight) = $gallery->album->getThumbDimensions($i, $scaleTo);
            }

            if ($iWidth == 0) {
				$iWidth = $gallery->album->fields['thumb_size'];
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
                /*begin backwards compatibility */
                $gallery->html_wrap['thumbTag']	= $gallery->html_wrap['imageTag'];
                $gallery->html_wrap['thumbHref'] = $gallery->html_wrap['imageHref'];
                /*end backwards compatibility*/
                list($divCellWidth, $divCellHeight, $padding) = calcVAdivDimension($frame, $iHeight, $iWidth, $borderwidth);
                // If there is only one column, we don't need to try and match row heights
                if ($cols == 1) {
                    $padding = 0;
                }
                echo "<div style=\"padding-top: {$padding}px; padding-bottom:{$padding}px; width: {$divCellWidth}px; height: {$divCellHeight}px;\" align=\"center\" class=\"vafloat2\">\n";

                includeHtmlWrap('inline_moviethumb.frame');
			}
			elseif (isset($myAlbum)) {
                // We already loaded this album - don't do it again, for performance reasons.

                $gallery->html_wrap['imageTag'] = $myAlbum->getHighlightTag($scaleTo,'',gTranslate('core', "Highlight for Album:"). " ". gallery_htmlentities(strip_tags($myAlbum->fields['title'])));
                $gallery->html_wrap['imageHref'] = makeAlbumUrl($gallery->album->getAlbumName($i));
                $frame= $gallery->html_wrap['frame'] = $gallery->album->fields['album_frame'];
                /*begin backwards compatibility */
                $gallery->html_wrap['thumbWidth'] =  $gallery->html_wrap['imageWidth'];
                $gallery->html_wrap['thumbHeight'] = $gallery->html_wrap['imageHeight'];
                $gallery->html_wrap['thumbTag'] = $gallery->html_wrap['imageTag'];
                $gallery->html_wrap['thumbHref'] = $gallery->html_wrap['imageHref'];
                /*end backwards compatibility*/

                list($divCellWidth,$divCellHeight, $padding) = calcVAdivDimension($frame, $iHeight, $iWidth, $borderwidth);
                echo "<div style=\"padding-top: {$padding}px; padding-bottom:{$padding}px; width: {$divCellWidth}px; height: {$divCellHeight}px;\" align=\"center\" class=\"vafloat2\">\n";
                includeHtmlWrap('inline_albumthumb.frame');
			}
			else {
                $gallery->html_wrap['imageTag'] = $gallery->album->getThumbnailTag($i);
                $gallery->html_wrap['imageHref'] = makeAlbumUrl($gallery->session->albumName, $id);
                $frame= $gallery->html_wrap['frame'] = $gallery->album->fields['thumb_frame'];
                /*begin backwards compatibility */
                $gallery->html_wrap['thumbTag'] = $gallery->html_wrap['imageTag'];
                $gallery->html_wrap['thumbHref'] = $gallery->html_wrap['imageHref'];
                /*end backwards compatibility*/

                list($divCellWidth,$divCellHeight, $padding) = calcVAdivDimension($frame, $iHeight, $iWidth, $borderwidth);
                echo "<div style=\"padding-top: {$padding}px; padding-bottom:{$padding}px; width: {$divCellWidth}px; height: {$divCellHeight}px;\" align=\"center\" class=\"vafloat2\">\n";
                includeHtmlWrap('inline_photothumb.frame');
            }

            echo "\n";
            echo "</div>\n";

            if (canVote()){
                if ($gallery->album->fields["poll_type"] == 'rank' && $divCellWidth < 200) {
                    $divCellWidth=200;
                }
            }

            echo "<div style=\"width: {$divCellWidth}px;\"  align=\"center\" class=\"vafloat\">\n";
            /* Do the clickable-dimensions row */
            if (!strcmp($gallery->album->fields['showDimensions'], 'yes')) {
                echo '<span class="dim">';
				$photo	= $gallery->album->getPhoto($i);
				$image	= $photo->image;
                if (!empty($image) && !$photo->isMovie()) {
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
                        echo '<a href="'.
                        makeAlbumUrl($gallery->session->albumName, $image->name) .
                        "\">[${wr}x{$hr}${fsr}]</a>&nbsp;";
                    }
                    if ($viewFull) {
                        echo '<a href="'.
                        makeAlbumUrl($gallery->session->albumName,
                        $image->name, array('full' => 1)) .
                        "\">[${wf}x${hf}${fsf}]</a>";
                    }
                } else {
                    echo "&nbsp;";
                }
                echo '</span>';

            }

            /* Now do the caption row */
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

            // Caption itself
            echo "\n<div align=\"center\" class=\"modcaption\">\n";
            $id = $gallery->album->getPhotoId($i);
            if ($gallery->album->isHidden($i) && !$gallery->session->offline) {
                echo "(" . gTranslate('core', "hidden") .")<br>";
            }

            $photo = $gallery->album->getPhoto($i);
            if ($gallery->user->canWriteToAlbum($gallery->album) &&
              $photo->isHighlight() && !$gallery->session->offline) {
                echo "(" . gTranslate('core', "highlight") .")<br>";
            }
            if (isset($myAlbum)) {
            	$myDescription = $myAlbum->fields['description'];
            	$buf = '';
            	$link = '';
            	if ($gallery->user->canDownloadAlbum($myAlbum) && $myAlbum->numPhotos(1)) {
            		$iconText = getIconText('compressed.png', gTranslate('core', "Download entire album as archive"), 'yes');
            		$link = popup_link($iconText, 'download.php?set_albumName='. $gallery->album->getAlbumName($i),false,false,500,500);
            	}
            	$buf .="<center><b>";
            	$buf .= sprintf(gTranslate('core', "Album: %s"),
            	'<a class="modcaption" href="'. makeAlbumUrl($gallery->album->getAlbumName($i)) .'">'. $myAlbum->fields['title'] .'</a>');
            	$buf .= "</b> $link</center>";

            	if ($myDescription != gTranslate('core', "No description") &&
            	  $myDescription != "No description" &&
            	  $myDescription != "") {
            		$buf = $buf."<br>".$myDescription."";
            	}
            	echo $buf;

            	echo '<div class="fineprint" style="margin-top:3px">';
            	printf (gTranslate('core', "Last change: %s"), $myAlbum->getLastModificationDate());
            	echo "\n<br>";

            	$visItems = array_sum($myAlbum->numVisibleItems($gallery->user));
            	printf(gTranslate('core', "Contains: %s."), gTranslate('core', "1 item", "%d items", $visItems));

            	// If comments indication for either albums or both
            	switch ($gallery->app->comments_indication) {
            		case "albums":
            		case "both":
	            		$lastCommentDate = $myAlbum->lastCommentDate(
	            		$gallery->app->comments_indication_verbose);
	            		if ($lastCommentDate > 0) {
	            			print lastCommentString($lastCommentDate, $displayCommentLegend);
	            		}
            		break;
            	}
            	echo '</div>';

            	if (!(strcmp($gallery->album->fields["display_clicks"] , "yes")) &&  !$gallery->session->offline && ($myAlbum->getClicks() > 0)) {
            		echo '<div class="viewcounter" style="margin-top:3px">';
            		printf (gTranslate('core', "Viewed: %s"), gTranslate('core', "1 time", "%d times", $myAlbum->getClicks()));
            		echo ".</div>";
            	}
            }
			/* Photo or Movie */
            else {
            	echo "<div align=\"center\">\n";
            	echo nl2br($gallery->album->getCaption($i));
            	echo $gallery->album->getCaptionName($i) . ' ';
            	// indicate with * if we have a comment for a given photo
            	if ($gallery->user->canViewComments($gallery->album)
            	&& $gallery->app->comments_enabled == 'yes') {
            		// If comments indication for either photos or both
            		switch ($gallery->app->comments_indication) {
            			case "photos":
            			case "both":
	            			$lastCommentDate = $gallery->album->itemLastCommentDate($i);
	            			print lastCommentString($lastCommentDate, $displayCommentLegend);
	            		break;
            		}

            	}
            	echo "</div>\n";

            	if (!(strcmp($gallery->album->fields["display_clicks"] , "yes")) && !$gallery->session->offline && ($gallery->album->getItemClicks($i) > 0)) {
            		echo '<div class="viewcounter" style="margin-top:3px">';
            		echo gTranslate('core', "Viewed: 1 time.", "Viewed: %d times.", $gallery->album->getItemClicks($i));
            		echo "</div>\n";
            	}
            }
            echo "<br>\n";
            // End Caption

            if (canVote()) {
                echo("<div align=\"center\">\n");
                addPolling($gallery->album->getVotingIdByIndex($i), $form_pos, false);
                $form_pos++;
            }

            $albumItemOptions = getItemActions($i, false);
            if (sizeof($albumItemOptions) > 2 ||
				(sizeof($albumItemOptions) == 2 && !isset($albumItemOptions['showExif']))) 
			{
                echo drawSelect2("s$i", $albumItemOptions, array(
                    'onChange' => "imageEditChoice(document.vote_form.s$i)",
                    'class' => 'adminform'));
            }

            if (canVote()) {
                print '</div>';
            }
			echo "</div></div>\n";
			echo "</td>\n";

            $j++;
            $visibleItemIndex++;
            $i = $visibleItemIndex<=$numVisibleItems ? $visibleItems[$visibleItemIndex] : $numPhotos+1;
        }
        if ($printTableRow) {
			echo "</tr>\n";
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
} else {
?>
  <tr>
	<td colspan="<?php echo $rows ?>" align="center" class="headbox">
<?php if ($gallery->user->canAddToAlbum($gallery->album) && !$gallery->session->offline) {
    $url = makeGalleryUrl('add_photos_frame.php', array('set_albumName' => $gallery->session->albumName, 'type' => 'popup'));
    echo popup_link('['. gTranslate('core', "Hey! Add some photos.") .']', $url, 1, true, 500, 600, 'admin');
} else {
    echo gTranslate('core', "This album is empty.");
}
?>
	</td>
	</tr>
<?php
}
?>

</table>

<?php //display legend for comments
if ($displayCommentLegend) {  ?>
<span class="commentIndication">*</span>
<span class="fineprint"> <?php echo gTranslate('core', "Comments available for this item.") ?></span>
<br>
<?php }

if (canVote()) { ?>
<p align="center">
	<input type=submit name="Vote" value="<?php print gTranslate('core', "Vote") ?>">
</p>
<?php
}

echo "\n</form>";

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
?>
	<fieldset class="admin" style="width: 400px; margin-bottom: 2px">
	<legend>
    <?php echo gTranslate('core', "Email me when one of the following actions are done to this album:")."  "; ?>
    </legend>
<?php
    echo makeFormIntro("view_album.php",
        array("name" => "email_me", "style" => "margin-bottom: 0px;"));

    $checked_com = ($gallery->album->getEmailMe('comments', $gallery->user)) ? "checked" : "" ;
    $checked_other = ($gallery->album->getEmailMe('other', $gallery->user)) ? "checked" : "";
?>
	<input type="checkbox" name="comments" <?php echo $checked_com; ?> onclick="document.email_me.submit()">
	<?php echo gTranslate('core', "Comments are added"); ?>
	<br>
	<input type="checkbox" name="other" <?php echo $checked_other; ?> onclick="document.email_me.submit()">
	<?php print gTranslate('core', "Other changes are made") ?>
	<hr>
	<input type="checkbox" name="recursive" onclick="document.email_me.submit()">
	<?php echo gTranslate('core', "Apply settings (both) recursive for subalbums."); ?>

	<input type="hidden" name="submitEmailMe" value="true">
	</form>
	</fieldset>

<?php } ?>
<!-- bottom nav -->
<?php

if($numVisibleItems != 0) {
    includeLayout('navtablebegin.inc');
    if ($navigator["maxPages"] > 1) {
        includeLayout('navigator.inc');
        includeLayout('navtablemiddle.inc');
    }
    includeLayout('breadcrumb.inc');
    includeLayout('navtableend.inc');
}
echo languageSelector();
includeHtmlWrap("album.footer");

if (!$GALLERY_EMBEDDED_INSIDE) { ?>
</body>
</html>
<?php } ?>
