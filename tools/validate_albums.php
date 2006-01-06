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

function findInvalidAlbums() {
    global $gallery;
    global $results;

    $albumsDir = opendir($gallery->app->albumDir);

    while (($file = readdir($albumsDir)) !== false) {
	$albumPath = $gallery->app->albumDir . '/' . $file;
	if (fs_is_dir($albumPath)) {
	    if ($file[0] == '.' ||
		    $file == 'CVS' ||
		    $file == '_vti_cnf') {
		continue;
	    } else {

		// Load the album - if it fails, it's invalid
		$album = new Album();
		if (!$album->load($file)) {
		    $results['invalid_album'][] = $file;
		    continue;
		}

		// Determine if the album is missing any essential files
		findMissingFiles($album, $albumPath);
	    }
	}
    }
    closedir($albumsDir);

    sort($results['file_missing']);
    sort($results['invalid_album']);
}

function findMissingFiles($album, $albumPath) {
    global $gallery;
    global $results;

    // Try to ensure we'll have enough time to process this album
    @set_time_limit($gallery->app->time_limit);

    /* 
     * Try and load each photo and examine its physical file
     * if the file doesn't exist, we flag it.
     */
    for ($i = 1; $i <= sizeof($album->photos); $i++) {
	$photo = $album->getPhoto($i);

	// Albums will be tested on their own
	if ($photo->isAlbum()) {
	    continue;
	}

	// Get the file path and verify
	$photoPath = $photo->getPhotoPath($albumPath, true);
	if (!fs_file_exists($photoPath)) {
	    // album/filename.ext
	    $results['file_missing'][] = substr($photoPath, strlen($gallery->app->albumDir) + 1);
	}
    }
}

function removeInvalidAlbum($path) {
    $removePath = opendir($path);
    while (($file = readdir($removePath)) !== false) {
	if ($file == '.' || $file == '..') {
	    continue;
	}
	
	if (fs_is_dir($path . '/' . $file)) {
	    removeInvalidAlbum($path . '/' . $file);
	} else {
	    unlink($path . '/' . $file);
	}
    }
    closedir($removePath);
    rmdir($path);
}

$action = getRequestVar('action');
if (empty($action)) {
    findInvalidAlbums();
} else {
   if (!$GALLERY_EMBEDDED_INSIDE) {
        doctype();
?>
<html>
<head>
  <title><?php echo ($action == 'unlinkInvalidAlbum') ? _("Delete Album") : _("Delete Photo") ?></title>
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
<div class="popup" align="center">
<?php
    switch ($action) {
	case 'unlinkInvalidAlbum':
	    list ($verified, $invalidAlbum) = getRequestVar(array('verified', 'invalidAlbum'));
	    if ($verified) {
		removeInvalidAlbum($gallery->app->albumDir . '/' . $invalidAlbum);
		dismiss();
	    } else {
		echo makeFormIntro('tools/validate_albums.php', array(), array('action' => $action, 'invalidAlbum' => $invalidAlbum));
		echo "<p><input type='submit' name='verified' value='Delete $invalidAlbum'></p>";
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
	    } else {
		echo makeFormIntro('tools/validate_albums.php', array(), array('action' => $action, 'album' => $album, 'id' => $id));
		$targetAlbum = new Album();
		$targetAlbum->load($album);
		echo $targetAlbum->getThumbnailTagById($id);
		echo "<p><input type='submit' name='verified' value='Delete $album/$id'></p>";
		echo "<p>" . _("Please Note: Even if the thumbnail image is properly displayed above, the actual full-sized image has been verified to be missing.") . "</p>";
		echo "</form>";
	    }
	    break;

	default:
	    echo '<p>Invalid Action</p>';
	    break;
	}
	?>
</div>
</body>
</html>
<?php
    exit;
}

if (!$GALLERY_EMBEDDED_INSIDE) {
	doctype();
?>
<html>
<head>
<title><?php echo $gallery->app->galleryTitle ?>::<?php echo _("Validate Albums") ?></title>
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


echo '<div class="popup">';
if (empty($action)) {
	if (!empty($results['file_missing'])) { ?>
		<p><?php echo _("Missing Files:") . " " . sizeof($results['file_missing']) ?></p>
		<p><?php echo _("The following files are missing from the albums directory.  Information is still stored about the photo in the album data, but the file itself is no longer present for some reason.  These files will cause failures when attempting to migrate to Gallery 2.0. This can be fixed in one of two ways; the first is to simply delete the photo entry from the album.  The second is to manually re-add the file to the albums directory using the filename you see in the left side of the table.") ?></p>
		<center>
		<table>
		<tr>
			<th><?php echo _("Missing File") ?></th>
			<th>&nbsp;</th>
			<th><?php echo _("Action") ?></th>
		</tr>
<?php
		foreach ($results['file_missing'] as $fileName) {
			$contents = split('/', $fileName);
			$contents[1] = substr($contents[1], 0, strrpos($contents[1], '.'));
			echo "\t<tr>";
			echo "\n\t<td><a href='" . makeAlbumUrl($contents[0], $contents[1]) . "'>" . $fileName . "</a></td>";
			echo "\n\t<td>=&gt;</td>";			
			echo "\n\t<td>" . popup_link(_('delete photo'), 
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
	} else {
		// No Orphans
?>
		<center>
		<table>
		<tr><th><?php echo _("No Missing Files") ?></th></tr>
		<tr><td><?php echo _("There are no missing files in this Gallery.") ?></td></tr>
		</table>
		</center>
<?php
	}

	if (!empty($results['invalid_album'])) {
?>

		<p><?php echo _("Invalid Albums:") . " " . sizeof($results['invalid_album']) ?></p>
		<p><?php echo _("Invalid Albums are directories which have been created in the albums directory that don't actually contain album data.  The presence of these directories can cause problems for Gallery as well as when trying to migrate to Gallery 2.0") ?></p>
		<center>
		<table>
		<tr>
			<th><?php echo _("Invalid Album") ?></th>
			<th>&nbsp;</th>
			<th><?php echo _("Action") ?></th>
		</tr>
<?php
		foreach ($results['invalid_album'] as $invalidAlbum) {
			echo "\n\t<tr>";
			echo "\n\t<td>$invalidAlbum</td>";
			echo "\n\t<td>=&gt;</td>";
			echo "\n\t<td>" . popup_link(_('delete directory'), 
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
	} else {
		// No Orphans
?>
		<center>
		<table>
		<tr><th><?php echo _("No Invalid Albums Found") ?></th></tr>
		<tr><td><?php echo _("There are no invalid albums present in this Gallery.") ?></td></tr>
		</table>
		</center>
<?php
	}
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
