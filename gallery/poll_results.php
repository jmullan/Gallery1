<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
 *
 * Additional voting code Copyright (C) 2003-2004 Joan McGalliard
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

// Hack check
if (empty($gallery->album) || !$gallery->album->isLoaded()) {
	header("Location: " . makeAlbumHeaderUrl());
	return;
}

// Is user allowed to see this page ?
if (!testRequirement('isAdminOrAlbumOwner')) {
	printPopupStart(gTranslate('core', "Poll Results"));
	showInvalidReqMesg(gTranslate('core', "You are not allowed to perform this action!"));
	exit;
}

$albumName = $gallery->session->albumName;

if (!$gallery->session->viewedAlbum[$albumName]) {
	$gallery->session->viewedAlbum[$albumName] = 1;
	$gallery->album->incrementClicks();
}

$bordercolor = $gallery->album->fields["bordercolor"];

$cols = $gallery->album->fields["cols"];
$imageCellWidth = floor(100 / $cols) . "%";
$fullWidth="100%";

$adminbox['bordercolor'] = $gallery->app->default['bordercolor'];
$adminbox['text'] = gTranslate('core', "Poll Results");

$iconElements[] = galleryIconLink(
				makeAlbumUrl($gallery->session->albumName),
				'navigation/return_to.gif',
				gTranslate('core', "Return to album")
);

$iconElements[] = LoginLogoutButton(makeGalleryUrl());

$adminbox['commands'] = makeIconMenu($iconElements, 'right');

$navigator["page"] = 1;
$navigator["pageVar"] = "page";
$navigator["maxPages"] = 1;
$navigator["url"] = makeAlbumUrl($gallery->session->albumName);
$navigator["spread"] = 5;
$navigator["bordercolor"] = $bordercolor;
$navigator["fullWidth"] = $fullWidth;

$albumTitle = clearGalleryTitle(strip_tags($gallery->album->fields['title']) . ' :: '. gTranslate('core', "Poll Results"));

if (!$GALLERY_EMBEDDED_INSIDE) {
	doctype();
?>

<html>
<head>
  <title><?php echo $albumTitle ?></title>
  <?php common_header();

  if(! empty($gallery->album->fields["linkcolor"]) ||
  	 !empty($gallery->album->fields["bgcolor"]) ||
  	 !empty($gallery->album->fields["textcolor"]))
   {

  	echo "\n<style type=\"text/css\">";
  	// the link colors have to be done here to override the style sheet
if ($gallery->album->fields["linkcolor"]) {
  		echo "\n  a:link, a:visited, a:active {";
  		echo "\n		color: ".$gallery->album->fields['linkcolor'] ."; }";
  		echo "\n  a:hover { color: #ff6600; }";

  	}
if ($gallery->album->fields["bgcolor"]) {
  		echo "body { background-color:".$gallery->album->fields['bgcolor']."; }";
}
  	if (isset($gallery->album->fields['background']) && $gallery->album->fields['background']) {
  		echo "body { background-image:url(".$gallery->album->fields['background']."); } ";
}
if ($gallery->album->fields["textcolor"]) {
  		echo "body, tf {color:".$gallery->album->fields['textcolor']."; }";
  		echo ".head {color:".$gallery->album->fields['textcolor']."; }";
  		echo ".headbox {background-color:".$gallery->album->fields['bgcolor']."; }";
  	}

  	echo "\n  </style>";
}
?>

</head>
<body dir="<?php echo $gallery->direction ?>">
<?php }

includeHtmlWrap("album.header");

includeLayout('navtablebegin.inc');
includeLayout('adminbox.inc');
includeLayout('navtablemiddle.inc');
includeLayout('breadcrumb.inc');
includeLayout('navtableend.inc');

?>

<!-- image grid table -->
<br>
<?php
$num_rows = $gallery->album->numPhotos($gallery->user->canWriteToAlbum($gallery->album));

if($num_rows > 0) {
	list($buf, $results) = showResultsGraph($num_rows);
	$ranks = array_keys($results);
	print $buf;
?>
	<p>
	<span class="pollresults"><?php echo gTranslate('core', "Results Breakdown") ?></span>
	<table width="<?php print $fullWidth?>" border="0" cellspacing="0" cellpadding="7">
<?php
	$rowStart = 0;
	$i = 0;
	$numPhotos = sizeof($ranks);
	$result = false;

	while ($i < $numPhotos) {
		/* Do the inline_albumthumb header row */
		echo("<tr>");
		$i = $rowStart;
		$j = 1;
		while ($j <= $cols && $i < $numPhotos) {
			echo("<td>");
			$index = $gallery->album->getIndexByVotingId($ranks[$i]);
			if ($index < 0) {
				$i++;
				continue;
			}

			$result = true;
			if ($gallery->album->isAlbum($index)) {
				$albumName = $gallery->album->getAlbumName($index);
				$album = $gallery->album->getSubAlbum($index);
				printf(gTranslate('core', "Album: %s"),$album->fields['title'])."<Br>";
			}
			else {
				print $gallery->album->getCaption($index)."<br>";
			}

			print showResults($ranks[$i]);
			echo("</td>");
			$j++;
			$i++;
		}
		echo("</tr>");
		$rowStart = $i;
	}
	echo "\n</table>";
}
else {
	echo gallery_info(gTranslate('core', "No votes so far."));
}

echo "<br>";

$validation_file = basename(__FILE__);
includeLayout('navtablebegin.inc');
includeLayout('breadcrumb.inc');
includeLayout('navtableend.inc');
echo languageSelector();
includeHtmlWrap("album.footer");

if (!$GALLERY_EMBEDDED_INSIDE) { ?>
</body>
</html>
<?php } ?>
