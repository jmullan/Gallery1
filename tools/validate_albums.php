<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2006 Bharat Mediratta
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
<body>
<?php
    }

    includeTemplate("gallery.header", '', 'classic');

    $adminbox['text'] = ($action == 'unlinkInvalidAlbum') ? gTranslate('core', "Delete Album") : gTranslate('core', "Delete Photo");
    $adminbox["commands"] = galleryLink(makeGalleryUrl("admin-page.php"), gTranslate('core', "return to _admin page"), array(), '', true);
    $adminbox["commands"] .= galleryLink(makeAlbumUrl(), gTranslate('core', "return to _gallery"), array(), '', true);

    $adminbox["bordercolor"] = $gallery->app->default["bordercolor"];
    $breadcrumb['text'][] = languageSelector();

    includeLayout('adminbox.inc');
    includeLayout('breadcrumb.inc');
?>
<div class="g-content-popup" align="center">
<?php
    switch ($action) {
        case 'unlinkInvalidAlbum':
            list ($verified, $invalidAlbum) = getRequestVar(array('verified', 'invalidAlbum'));
            if ($verified) {
                removeInvalidAlbum($gallery->app->albumDir . '/' . $invalidAlbum);
                dismiss();
            }
            else {
                echo makeFormIntro('tools/validate_albums.php', array(), array('action' => $action, 'invalidAlbum' => $invalidAlbum));
                echo "\n<p>" . gSubmit('verified', sprintf(gTranslate('core', "Delete %s"), $invalidAlbum)) .'</p>';
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
                dismiss();
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
                echo "\n<p>" . gSubmit('verified', sprintf(gTranslate('core', "Delete %s"), "$album/$id")) .'</p>';
                echo "<p>" . gTranslate('core', "Please Note: Even if the thumbnail image is properly displayed above, the actual full-sized image has been verified to be missing.") . "</p>";
                echo "</form>";
            }
            break;

        default:
            echo infoBox(array(array(
                'type' => 'error',
                'text' => gTranslate('core', "Invalid Action !"))));
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
<body>
<?php
}
includeTemplate("gallery.header", '', 'classic');

$adminbox['text'] =gTranslate('core', "Validate Albums");
$adminCommands = galleryLink(makeGalleryUrl("admin-page.php"), gTranslate('core', "return to _admin page"), array(), '', true);
$adminCommands .= galleryLink(makeAlbumUrl(), gTranslate('core', "return to _gallery"), array(), '', true);

$adminbox["commands"] = $adminCommands;
$adminbox["bordercolor"] = $gallery->app->default["bordercolor"];
$breadcrumb['text'][] = languageSelector();

includeLayout('adminbox.inc');

includeLayout('breadcrumb.inc');

echo '<div class="g-content-popup" align="center">';
if (empty($action)) {
	if (!empty($results['file_missing'])) { ?>
		<p><?php echo sprintf(gTranslate('core', "Missing Files: %s"), sizeof($results['file_missing'])); ?></p>
		<p><?php echo gTranslate('core', "The following files are missing from the albums directory.  Information is still stored about the photo in the album data, but the file itself is no longer present for some reason.  These files will cause failures when attempting to migrate to Gallery 2.0. This can be fixed in one of two ways; the first is to simply delete the photo entry from the album.  The second is to manually re-add the file to the albums directory using the filename you see in the left side of the table.") ?></p>
		<center>
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
			echo "\n\t<td>" . popup_link(gTranslate('core', 'delete photo'),
				"tools/validate_albums.php?action=deleteMissingPhoto" .
					"&album={$contents[0]}&id={$contents[1]}",
				false, true, 500, 500) . '</td>';
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
?>
		<center>
		<table>
		<tr><th><?php echo gTranslate('core', "No Missing Files") ?></th></tr>
		<tr><td><?php echo gTranslate('core', "There are no missing files in this Gallery.") ?></td></tr>
		</table>
		</center>
<?php
	}

	if (!empty($results['invalid_album'])) {
?>

		<p><?php echo gTranslate('core', "Invalid Albums:") . " " . sizeof($results['invalid_album']) ?></p>
		<p><?php echo gTranslate('core', "Invalid Albums are directories which have been created in the albums directory that don't actually contain album data.  The presence of these directories can cause problems for Gallery as well as when trying to migrate to Gallery 2.0") ?></p>
		<center>
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
			echo "\n\t<td>" . popup_link(gTranslate('core', 'delete directory'),
				"tools/validate_albums.php?action=unlinkInvalidAlbum" .
					"&invalidAlbum={$invalidAlbum}",
				false, true, 500, 500) . '</td>';
			echo "\n\t\t</tr>";
		}
?>
		</table>
		<br>
		</center>
<?php
	}
	else {
		// No Orphans
?>
		<center>
		<table>
		<tr><th><?php echo gTranslate('core', "No Invalid Albums Found") ?></th></tr>
		<tr><td><?php echo gTranslate('core', "There are no invalid albums present in this Gallery.") ?></td></tr>
		</table>
		</center>
<?php
	}
}
?>
</div>
<?php
includeTemplate("overall.footer");

if (!$GALLERY_EMBEDDED_INSIDE) {
?>
</body>
</html>
<?php }
?>
