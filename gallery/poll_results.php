<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2004 Bharat Mediratta
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
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 * $Id$
 */
?>
<?php

require_once(dirname(__FILE__) . '/init.php');

// Hack check
if (!$gallery->album->isLoaded()) {
	header("Location: " . makeAlbumHeaderUrl());
	return;
}

// Is user allowed to see this page ?
if (!testRequirement('isAdminOrAlbumOwner')) {
	echo _("You are not allowed to perform this action!");
	echo '<p><a href="'. makeAlbumUrl() .'">'. _("Back to Gallery") .'</a></p>';
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
  <title><?php echo $gallery->app->galleryTitle ?> :: <?php echo $gallery->album->fields["title"] . "::" . _("Poll Results") ?></title>
  <?php common_header(); ?>
  <style type="text/css">
<?php
// the link colors have to be done here to override the style sheet 
if ($gallery->album->fields["linkcolor"]) {
?>
    A:link, A:visited, A:active
      { color: <?php echo $gallery->album->fields[linkcolor] ?>; }
    A:hover
      { color: #ff6600; }
<?php }
if ($gallery->album->fields["bgcolor"]) {
	echo "BODY { background-color:".$gallery->album->fields[bgcolor]."; }";
}
if ($gallery->album->fields["background"]) {
	echo "BODY { background-image:url(".$gallery->album->fields[background]."); } ";
}
if ($gallery->album->fields["textcolor"]) {
	echo "BODY, TD {color:".$gallery->album->fields[textcolor]."; }";
	echo ".head {color:".$gallery->album->fields[textcolor]."; }";
	echo ".headbox {background-color:".$gallery->album->fields[bgcolor]."; }";
}
?>
  </style>
</head>
<body dir="<?php echo $gallery->direction ?>">
<?php } 

includeHtmlWrap("album.header");
$breadcrumb["top"] = true;
$breadcrumb["bordercolor"] = $bordercolor;
$breadcrumb["text"][] = sprintf(_("Return to  %s"), 
		"<a href=\"" .  makeAlbumUrl($gallery->session->albumName) .
      		"\">" . $pAlbum->fields['title'] . "</a>");

includeLayout('breadcrumb.inc');

$navigator["page"] = 1;
$navigator["pageVar"] = "page";
$navigator["maxPages"] = 1;
$navigator["fullWidth"] = $fullWidth;
$navigator["url"] = makeAlbumUrl($gallery->session->albumName);
$navigator["spread"] = 5;
$navigator["bordercolor"] = $bordercolor;
includeLayout('navigator.inc');

?>


<!-- image grid table -->
<br>
<?php
	$num_rows=$gallery->album->numPhotos($gallery->user->canWriteToAlbum($gallery->album));
	list($buf, $results)=showResultsGraph($num_rows);
	$ranks=array_keys($results);
	print $buf;
		?>
			<p><span class=pollresults>
			<?php echo _("Results Breakdown") ?>
			</span>
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
					$result=true;
					if ($gallery->album->isAlbum($index)) {
						$albumName = $gallery->album->getAlbumName($index);
						$album = $gallery->album->getSubAlbum($index);
						print sprintf(_("Album: %s"),$album->fields['title'])."<Br>";
					} else {
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
			if (!$result) {
				print _("No votes so far.");
			}
		?>
		
		</table>
		
	<?php
	$validation_file = basename(__FILE__);
	includeHtmlWrap("general.footer");
?>

<?php if (!$GALLERY_EMBEDDED_INSIDE) { ?>
</body>
</html>
<?php } ?>
