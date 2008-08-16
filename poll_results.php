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
if (!$gallery->album->isLoaded()) {
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

$pAlbum = $gallery->album;

if (!$GALLERY_EMBEDDED_INSIDE) {
	doctype();
?>

<html>
<head>
  <title><?php echo $gallery->app->galleryTitle ?> :: <?php echo $gallery->album->fields["title"] . "::" . gTranslate('core', "Poll Results") ?></title>
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
<body>
<?php }

includeTemplate('album.header');

$breadcrumb["bordercolor"] = $bordercolor;
$breadcrumb["text"][] = sprintf(
	makeAccessKeyString(gTranslate('core', "_Return to  %s")),
	galleryLink(makeAlbumUrl($gallery->session->albumName), $pAlbum->fields['title'])
);

includeLayout('breadcrumb.inc');

$navigator["page"] = 1;
$navigator["pageVar"] = "page";
$navigator["maxPages"] = 1;
$navigator["url"] = makeAlbumUrl($gallery->session->albumName);
$navigator["spread"] = 5;
$navigator["bordercolor"] = $bordercolor;
includeLayout('navigator.inc');

$num_rows = $gallery->album->numPhotos($gallery->user->canWriteToAlbum($gallery->album));
list($buf, $results) = showResultsGraph($num_rows);
$ranks = array_keys($results);

echo "<br>";
echo $buf;

$i = 0;
$numPhotos = sizeof($ranks);

$resultTable = new galleryTable();
$resultTable->setAttrs(array('class' => 'g-vatable'));
$resultTable->setColumnCount($cols);

while ($i < $numPhotos) {
	$content = '';

	$index = $gallery->album->getIndexByVotingId($ranks[$i]);
	if ($index < 0) {
		$i++;
		continue;
	}

	if ($gallery->album->isAlbum($index)) {
		$albumName = $gallery->album->getAlbumName($index);
		$album = $gallery->album->getSubAlbum($index);
		$content = sprintf(gTranslate('core', "Album: %s"),$album->fields['title']) . "<br>";
	}
	else {
		$content = $gallery->album->getCaption($index) . "<br>";
	}

	$content .= showResults($ranks[$i]);

	$resultTable ->addElement(array(
		'content' => $content,
		'cellArgs' => array('class' => 'g-vathumb-cell')
	));

	$i++;
}

if (!empty($resultTable->elements)) {
?>
<br>
<p class="g-vote-box">
<?php echo gTranslate('core', "Results Breakdown") ?>
</p>
<?php
	echo $resultTable->render();
}

includeTemplate('info_donation-block');

includeTemplate('overall.footer');

if (!$GALLERY_EMBEDDED_INSIDE) { ?>
</body>
</html>
<?php }
?>
