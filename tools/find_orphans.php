<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2004 Bharat Mediratta
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

if (!isset($gallery->version)) {
        require(dirname(dirname(__FILE__)) . '/init.php');
}

$action = getRequestVar('action');

// Security check
if (!$gallery->user->isAdmin()) {
	header("Location: " . makeAlbumHeaderUrl());
	exit;
}

$albumDB = new AlbumDB();

function attachOrphanedAlbums($orphans) {
	global $albumDB;
	foreach ($orphans as $childname => $parentname) {
		if ($parentname == 0) {
			// Parent was deleted - attach it to root
			$child = $albumDB->getAlbumByName($childname);
			$child->fields['parentAlbumName'] = 0;
			while ($child->save() != true);
			continue;
		}
		
		$parent = $albumDB->getAlbumByName($parentname);
		$parent->addNestedAlbum($childname);
	
		// Set a default highlight if appropriate, for the parent
		if ($parent->numPhotos(1) == 1) {
			$parent->setHighlight(1);
		}
	
		// If the machine is fast, it can find a new album before it
		// has time to finish physically saving the last one.
		// Keep trying to save until it works.
		while ($parent->save() != true);
	}
}

function findOrphanedAlbums() {
	global $albumDB;
	$orphaned = Array();
	foreach ($albumDB->albumList as $album) {
		
		// Root albums can't be orphans
		if ($album->isRoot()) {
			continue;
		}
	
		$parent = $album->getParentAlbum();
	
		if (!isset($parent) || !isset($parent->fields['name'])) {
			// Orphaned, but the parent album is missing - link it to root
			$orphaned[$album->fields['name']] = 0;
			continue;
		}
	
		// Search for a filename match in the parent album
		if (!empty($parent->photos)) {
			foreach ($parent->photos as $photo) {
				if ($photo->isAlbum() && ($photo->getAlbumName() == $album->fields['name'])) {
					// Found a matching name - this is not an orphaned album
					// continue from outer loop
					continue 2;
				}
			}
		}
	
		// "Orphaned Album => Parent Album"
		$orphaned[$album->fields['name']] = $parent->fields['name'];
	}
	
	// Sort the array by value (parent) so it can be displayed to the user
	asort($orphaned);
	return $orphaned;
}

function findOrphanedImages() {
	global $gallery, $albumDB;

	$orphans = Array();

	// "." covers the "." and ".." dir entries
	$ignoreFiles = array('.', 'dat', 'bak', 'lock');

	foreach ($albumDB->albumList as $album) {

		// Get the album name, build the album path, open the directory
		$albumName = $album->fields['name'];
		$albumDir = $gallery->app->albumDir . "/" . $albumName;
		$dirhandle = opendir($albumDir);

		// Storage array
		$albumFiles = array();

		// Retrieve each file until the directory ends
		// Skip the files which have arrays in the ignoreFiles array
		while (false !== ($file = readdir($dirhandle))) { 
			$file = pathinfo($file);

			if (empty($file['extension']) || in_array($file['extension'], $ignoreFiles))
				continue;

			$albumFiles[$file['basename']] = 1;
		} 

		// Don't bother doing anything if there are no files
		if (sizeof($albumFiles)) {
			foreach ($album->photos as $photo) {
				foreach ($photo as $image) {

					// Since we're iterating through the entire AlbumItem class looking for files
					// we know we can skip any objects that aren't of the class "Image"
					if (strcasecmp(get_class($image), "Image")) {
						continue;

					// If we encounter a file that's in the AlbumItem, and in the file array
					// purge it, because it's valid
					} elseif (isset($albumFiles[$image->name . "." . $image->type])) {
						unset($albumFiles[$image->name . "." . $image->type]);
					}

					// Resized files have to be handled separately
					if (!empty($image->resizedName) && isset($albumFiles[$image->resizedName . "." . $image->type])) {
						unset($albumFiles[$image->resizedName . "." . $image->type]);
					}

				}
			}

			// Check the size again so that we don't assign a null array
			if (sizeof($albumFiles)) {
				$orphans[$albumName] = $albumFiles;
			}
		}
	}

	asort($orphans);
	return $orphans;
}

function deleteOrphanedImages($orphans) {
	global $gallery;

	foreach ($orphans as $albumName => $imageVal) {
		foreach (array_keys($imageVal) as $fileName) {
			fs_unlink($gallery->app->albumDir . "/" . $albumName . "/" . $fileName);
		}
	}

}

global $GALLERY_EMBEDDED_INSIDE;
if (!$GALLERY_EMBEDDED_INSIDE) {
	doctype();
?>
<html>
<head>
<title><?php echo $gallery->app->galleryTitle ?></title>
<?php 
	common_header();
?>
</head>
<body dir="<?php echo $gallery->direction ?>">
<?php  
}
        includeHtmlWrap("gallery.header");
?>

<?php
$orphanAlbums = findOrphanedAlbums();
$orphanImages = findOrphanedImages();

if (empty($action)) { 
	if (!empty($orphanAlbums)) { ?>
		<p align="center" class="popuphead"><?php echo _("Orphaned Albums") . " " . sizeof($orphanAlbums) ?></p>
		<p><?php echo _("Orphaned Albums will be re-attached to their parent albums, if at all possible.  If the parent album is missing, the orphan will be attached to the Gallery Root, and it can be moved to a new location from there.") ?></p>
		<table>
		<tr><th><?php echo _("Orphaned Album") ?></th><th>&nbsp;</th><th><?php echo _("Parent Album") ?></th></tr>
<?php
		foreach ($orphanAlbums as $childname => $parentname) {
			echo "\t<tr><td>" . "<a href='" . makeAlbumUrl($childname) . "'>" . $childname . "</a>" . "</td><td>=&gt;</td><td>" . 
			     ($parentname ? "<a href='" . makeAlbumUrl($albumName) . "'>" . $albumName . "</a>" : _("Gallery Root")) . "</td></tr>\n";
		}
?>
		</table>
		<?php echo makeFormIntro("tools/find_orphans.php", array("method" => "GET")); ?>
		<input type="hidden" name="action" value="albums">
		<input type="submit" value="<?php echo _("Re-Attach Orphaned Albums!") ?>">
		</form>	
<?php
	} elseif (!empty($orphanImages)) {
?>

		<p align="center" class="popuphead"><?php echo _("Orphaned Images") . " " . sizeof($orphanImages) ?></p>

		<p><?php echo _("Orphaned Images will be deleted from the disk.  Orphaned images should never exist - if they do, they are the result of a failed upload attempt, or other more serious issue such as the photos database being overwritten with bad information.") ?></p>
		<table>
		<tr><th><?php echo _("Orphaned Image") ?></th><th>&nbsp;</th><th><?php echo _("In album directory") ?></th></tr>
<?php
		foreach ($orphanImages as $albumName => $imageVal) {
			foreach (array_keys($imageVal) as $fileName) {
?>
			<tr><td><?php echo "<a href='" . $gallery->app->albumDirURL . "/" . $albumName . "/" . $fileName . "'>" . $fileName . "</a>"; ?></td><td>=&gt;</td><td><?php echo "<a href='" . makeAlbumUrl($albumName) . "'>" . $albumName . "</a>"; ?></td></tr>
<?php
			}       
		}       
?>
		</table>
		<?php echo makeFormIntro("tools/find_orphans.php", array("method" => "GET")); ?>
		<input type="hidden" name="action" value="images">
		<input type="submit" value="<?php echo _("Delete Orphaned Images!") ?>">
		</form>	
<?php 
	} else {
		// No Orphans
		echo "\n<p align=\"center\" class=\"popuphead\">" .  _("No Orphans Found") . "</p>";
		echo "\n<p align=\"center\">". _("There are no orphaned albums in this Gallery.") . "</p>";
	}
} // !isset(update) 
else { 
	echo "\n<p align=\"center\" class=\"popuphead\">" .  sprintf(_("Orphan %s Repaired"), ($action == "albums") ? _("Albums") :
_("Images")) . "</p>";
	if ($action == "albums") attachOrphanedAlbums($orphanAlbums);
	if ($action == "images") deleteOrphanedImages($orphanImages);
}

	echo "\n<p><a href=\"" . makeAlbumUrl() . "\">"._("Return to Gallery")."</a></p>";
?>
<hr>
<?php 
	includeHtmlWrap("gallery.footer"); 
if (!$GALLERY_EMBEDDED_INSIDE) {
?>
</body>
</html>
<?php } ?>
