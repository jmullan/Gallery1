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

$action = getRequestVar('action');
if (empty($action)) {
	findInvalidAlbums();
}
else {
   if (!$GALLERY_EMBEDDED_INSIDE) {
		doctype();
?>
<html>
<head>
  <title><?php echo ($action == 'unlinkInvalidAlbum') ? gTranslate('core', "Delete Album") : gTranslate('core', "Delete Photo") ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>">
<?php
	}

	includeHtmlWrap("gallery.header");
	$adminbox['text'] ='<span class="head">'. ($action == 'unlinkInvalidAlbum') ? _("Delete Album") : _("Delete Photo") .'</span>';
	$adminCommands = '[<a href="'. makeGalleryUrl("admin-page.php") .'">'. _("return to admin page") .'</a>] ';
	$adminCommands .= '[<a href="'. makeAlbumUrl() .'">'. _("return to gallery") .'</a>] ';

	$adminbox["commands"] = $adminCommands;
	$adminbox["bordercolor"] = $gallery->app->default["bordercolor"];

	$breadcrumb['text'][] = languageSelector();

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

				echo infoLine(gTranslate('core', "Album deleted."), 'success left');

				echo galleryLink(makeGalleryUrl("tools/validate_albums.php"), gTranslate('core', "Validate again"), array(), '', true);
				echo galleryLink(makeGalleryUrl("admin-page.php"), gTranslate('core', "Return to admin page"), array(), '', true);
				echo galleryLink(makeAlbumUrl(), gTranslate('core', "Return to Gallery"), array(), '', true);
			}
			else {
				echo makeFormIntro('tools/validate_albums.php', array(), array('action' => $action, 'invalidAlbum' => $invalidAlbum));
				echo gTranslate('core', "Are you sure you want to delete the folder below and all of its content ?");
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
				$targetAlbum->deletePhoto($photoIndex);
				$targetAlbum->save(array(i18n("Photo $id deleted from $album because the target image file is missing")));

				echo infoLine(gTranslate('core', "Photo deleted."), 'success left');

				echo galleryLink(makeGalleryUrl("tools/validate_albums.php"), gTranslate('core', "Validate again"), array(), '', true);
				echo galleryLink(makeGalleryUrl("admin-page.php"), gTranslate('core', "Return to admin page"), array(), '', true);
				echo galleryLink(makeAlbumUrl(), gTranslate('core', "Return to Gallery"), array(), '', true);
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
				echo gSubmit('verified', gTranslate('core', "Yes, Delete"));
				echo gButton('revalidate', gTranslate('core', "No, Cancel"), "parent.location='" .makeGalleryUrl("tools/validate_albums.php") ."'");

				echo "<p>" . gTranslate('core', "Please Note: Even if the thumbnail image is properly displayed above, the actual full-sized image has been verified to be missing.") . "</p>";
				echo "</form>";
			}
			break;

		default:
			echo infoLine(gTranslate('core', "Invalid Action !"), 'error left');

			break;
	}
?>
</div>
<?php
	includeTemplate("overall.footer");

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
$adminbox['text'] ='<span class="head">'.  _("Validate Albums") .'</span>';
$adminCommands = '[<a href="'. makeGalleryUrl("admin-page.php") .'">'. _("return to admin page") .'</a>] ';
$adminCommands .= '[<a href="'. makeAlbumUrl() .'">'. _("return to gallery") .'</a>] ';

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

		<div class="infoline_error left">
		<?php echo sprintf(gTranslate('core', "Missing Files: %s"), sizeof($results['file_missing'])); ?>
		<br><br>
		<table>
		<tr>
			<th><?php echo gTranslate('core', "Missing File") ?></th>
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
					gTranslate('core', "delete photo"),
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
		echo infoLine(gTranslate('core', "There are no missing files in this Gallery."), 'success left');
	}
	echo "\n</fieldset><br>";

	echo "<fieldset><legend>". gTranslate('core', "Invalid albums") ."</legend>";
	if (!empty($results['invalid_album'])) {
?>
		<p><?php echo gTranslate('core', "Invalid Albums are directories which have been created in the albums directory that don't actually contain album data.  The presence of these directories can cause problems for Gallery as well as when trying to migrate to Gallery 2.x") ?></p>

		<div class="infoline_error left">
		<?php printf(gTranslate('core', "Invalid Albums: %d"), sizeof($results['invalid_album'])) ?>
		<br><br>
			<table>
			<tr>
				<th><?php echo gTranslate('core', "Invalid Album") ?></th>
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
					gTranslate('core', "delete directory"),
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
		echo infoLine(gTranslate('core', "There are no invalid albums in this Gallery."), 'success left');
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
