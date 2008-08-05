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

require(dirname(dirname(__FILE__)) . '/init.php');
require(dirname(__FILE__) . '/lib/lib-validate_albums.php');

// Security check
if (!$gallery->user->isAdmin()) {
	header("Location: " . makeAlbumHeaderUrl());
		exit;
}

// Ensure that the results we get aren't due to caching
clearstatcache();

$results = array(
	'file_missing' => array(),
	'invalid_album' => array(),
);

$iconElements = array();
$action = getRequestVar('action');

if(!empty($action)) {
	if($action == 'unlinkInvalidAlbum') {
		$title = gTranslate('core', "Delete Album");
	}
	else {
		$title = gTranslate('core', "Delete Photo");
	}
}
else {
	$title = gTranslate('core', "Validate Albums");
}

$iconElements[] = galleryIconLink(
				makeGalleryUrl("admin-page.php"),
				'navigation/return_to.gif',
				gTranslate('core', "Return to admin page"));

$iconElements[] = galleryIconLink(
				makeAlbumUrl(),
				'navigation/return_to.gif',
				gTranslate('core', "Return to gallery"));

$iconElements[] = LoginLogoutButton(makeGalleryUrl());

$adminbox['text'] ='<span class="head">'. $title .'</span>';
$adminbox['commands'] = makeIconMenu($iconElements, 'right');
$adminbox['bordercolor'] = $gallery->app->default['bordercolor'];

$breadcrumb['text'][] = languageSelector();


if (empty($action)) {
	findInvalidAlbums();
}
else {
   if (!$GALLERY_EMBEDDED_INSIDE) {
		doctype();
?>
<html>
<head>
  <title><?php echo $title; ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>">
<?php
	}

	includeHtmlWrap("gallery.header");
	includeLayout('navtablebegin.inc');
	includeLayout('adminbox.inc');
	includeLayout('navtablemiddle.inc');
	includeLayout('breadcrumb.inc');
	includeLayout('navtableend.inc');
?>
<br>
<div class="popup left">
<?php
	switch ($action) {
		case 'unlinkInvalidAlbum':
			list ($verified, $invalidAlbum) = getRequestVar(array('verified', 'invalidAlbum'));

			if ($verified) {
				$ret = removeInvalidAlbum($gallery->app->albumDir . '/' . $invalidAlbum);

				if($ret) {
					printInfoBox(array(array(
						'type' => 'success',
						'text' => gTranslate('core', "Album deleted.")
					)));
				}
				else {
					echo gallery_error(gTranslate('core', "Album not deleted!"));
				}

				echo galleryLink(makeGalleryUrl("tools/validate_albums.php"), gTranslate('core', "Validate again"), array(), '', true);
				echo galleryLink(makeGalleryUrl("admin-page.php"), gTranslate('core', "Return to admin page"), array(), '', true);
				echo galleryLink(makeAlbumUrl(), gTranslate('core', "Return to gallery"), array(), '', true);
			}
			else {
				echo makeFormIntro('tools/validate_albums.php', array(), array('action' => $action, 'invalidAlbum' => $invalidAlbum));
				echo gTranslate('core', "Are you sure you want to delete the folder below and all of its content?");
				echo "<p class=\"g-emphasis\">$invalidAlbum</p>";
				echo gSubmit('verified', gTranslate('core', "Yes, Delete"));
				echo gButton('revalidate', gTranslate('core', "No, Cancel"), "parent.location='" .makeGalleryUrl("tools/validate_albums.php") ."'");
				echo "</form>";
			}
			break;

		case 'deleteMissingPhoto':
			list ($verified, $album, $id) = getRequestVar(array('verified', 'album', 'id'));
			if ($verified) {
				$targetAlbum = new Album();
				$targetAlbum->load($album);
				$photoIndex = $targetAlbum->getPhotoIndex($id);

				$ret = $targetAlbum->deletePhoto($photoIndex);

				if($ret) {
					$targetAlbum->save(array(i18n("Item '$id' deleted from '$album' because the target image file is missing")));

					printInfoBox(array(array(
						'type' => 'success',
						'text' => gTranslate('core', "Item deleted.")
					)));
				}
				else {
					echo gallery_error(gTranslate('core', "Item not deleted!"));
				}

				echo galleryLink(makeGalleryUrl("tools/validate_albums.php"), gTranslate('core', "Validate again"), array(), '', true);
				echo galleryLink(makeGalleryUrl("admin-page.php"), gTranslate('core', "Return to admin page"), array(), '', true);
				echo galleryLink(makeAlbumUrl(), gTranslate('core', "Return to gallery"), array(), '', true);
			}
			else {
				echo makeFormIntro(
					'tools/validate_albums.php',
					array(),
					array('action' => $action, 'album' => $album, 'id' => $id)
				);

				$targetAlbum = new Album();
				$targetAlbum->load($album);

				echo $targetAlbum->getThumbnailTagById($id);

				echo "\n<br><br>";
				echo gSubmit('verified', gTranslate('core', "Yes, delete"));
				echo gButton('revalidate', gTranslate('core', "No, cancel"), "parent.location='" .makeGalleryUrl("tools/validate_albums.php") ."'");

				echo "<p>" . gTranslate('core', "Please Note: Even if the thumbnail image is properly displayed above, the actual full-sized image has been verified to be missing.") . "</p>";
				echo "</form>";
			}
			break;

		default:
			echo gallery_error(gTranslate('core', "Invalid action!"));
			break;
	}
?>
</div>
<?php
	includeHtmlWrap("gallery.footer");

	if (!$GALLERY_EMBEDDED_INSIDE) {
?>
</body>
</html>
<?php
	}
	exit;
}

if (!$GALLERY_EMBEDDED_INSIDE) {
	doctype();
?>
<html>
<head>
<title><?php echo clearGalleryTitle(gTranslate('core', "Validate Albums")) ?></title>
<?php
	common_header();
?>
</head>
<body dir="<?php echo $gallery->direction ?>">
<?php
}
includeHtmlWrap("gallery.header");
$adminbox['text'] ='<span class="head">'.  gTranslate('core', "Validate albums") .'</span>';
$adminCommands = '[<a href="'. makeGalleryUrl("admin-page.php") .'">'. gTranslate('core', "Return to admin page") .'</a>] ';
$adminCommands .= '[<a href="'. makeAlbumUrl() .'">'. gTranslate('core', "Return to gallery") .'</a>] ';

$adminbox["commands"] = $adminCommands;
$adminbox["bordercolor"] = $gallery->app->default["bordercolor"];
$breadcrumb['text'][] = languageSelector();

includeLayout('navtablebegin.inc');
includeLayout('adminbox.inc');
includeLayout('navtablemiddle.inc');
includeLayout('breadcrumb.inc');
includeLayout('navtableend.inc');

echo '<br><div class="g-content-popup left">';
echo "<fieldset><legend>". gTranslate('core', "Missing files") ."</legend>";
if (empty($action)) {
	if (!empty($results['file_missing'])) { ?>
		<p>
		<?php echo gTranslate('core', "The following files are missing from the albums directory.  Information is still stored about the photo in the album data, but the file itself is no longer present for some reason.  These files will cause failures when attempting to migrate to Gallery 2.x."); ?>
		<br>
		<?php echo gTranslate('core', "This can be fixed in one of two ways:"); ?>
		<ul>
		<li><?php echo gTranslate('core', "The first is to simply delete the photo entry from the album."); ?></li>
		<li><?php echo gTranslate('core', "The second is to manually re-add the file to the albums directory using the filename you see in the left side of the table."); ?></li>
		</ul>

		<div class="g-error left g-message">
		<?php echo gImage('icons/notice/error.gif'); ?>
		<?php echo sprintf(gTranslate('core', "Missing files: %s"), sizeof($results['file_missing'])); ?>
		<br><br>
		<table>
		<tr>
			<th><?php echo gTranslate('core', "Missing file") ?></th>
			<th>&nbsp;</th>
			<th><?php echo gTranslate('core', "Action") ?></th>
		</tr>
<?php
		foreach ($results['file_missing'] as $fileName) {
			$contents = split('/', $fileName);
			$contents[1] = substr($contents[1], 0, strrpos($contents[1], '.'));
			echo "\t<tr>";
			echo "\n\t<td><a href='" . makeAlbumUrl($contents[0], $contents[1]) . "'>" . $fileName . "</a></td>";
			echo "\n\t<td>=&gt;</td>";
			echo "\n\t<td>" . galleryLink(makeGalleryUrl(
					'tools/validate_albums.php',
					array('action' => 'deleteMissingPhoto',
						  'album' => $contents[0],
					 	  'id' => $contents[1])),
					gTranslate('core', "Delete item"),
					array('class' => 'error')) .
			'</td>';
			echo "\n\t</tr>";
		}
?>
		</table>
		<br>
		</center>
<?php
	}
	else {
		// No Orphans
		printInfoBox(array(array(
			'type' => 'success',
			'text' => gTranslate('core', "There are no missing files in this Gallery.")
		)), '', false);
	}
	echo "\n</fieldset><br>";

	echo "<fieldset><legend>". gTranslate('core', "Invalid albums") ."</legend>";
	if (!empty($results['invalid_album'])) {
?>
		<p><?php echo gTranslate('core', "Invalid albums are directories which have been created in the albums directory that don't actually contain album data.  The presence of these directories can cause problems for Gallery as well as when trying to migrate to Gallery 2.x.") ?></p>

		<div class="g-error left g-message">
		<?php echo gImage('icons/notice/error.gif'); ?>
		<?php printf(gTranslate('core', "Invalid albums: %d"), sizeof($results['invalid_album'])) ?>
		<br><br>
			<table>
			<tr>
				<th><?php echo gTranslate('core', "Invalid album") ?></th>
				<th>&nbsp;</th>
				<th><?php echo gTranslate('core', "Action") ?></th>
			</tr>
<?php
		foreach ($results['invalid_album'] as $invalidAlbum) {
			echo "\n\t<tr>";
			echo "\n\t<td>$invalidAlbum</td>";
			echo "\n\t<td>=&gt;</td>";
			echo "\n\t<td>" . galleryLink(makeGalleryUrl(
					'tools/validate_albums.php',
					array('action' => 'unlinkInvalidAlbum', 'invalidAlbum' => $invalidAlbum)),
					gTranslate('core', "Delete directory"),
					array('class' => 'error')) .
			'</td>';
			echo "\n\t\t</tr>";
		}
?>
			</table>
		</div>
<?php
	}
	else {
		// No Orphans
		printInfoBox(array(array(
			'type' => 'success',
			'text' => gTranslate('core', "There are no invalid albums in this Gallery.")
		)), '', false);
	}
	echo "\n</fieldset><br>";
}
?>
</div>
<?php
    includeHtmlWrap("gallery.footer");
    if (!$GALLERY_EMBEDDED_INSIDE) {
?>
</body>
</html>
<?php } ?>
