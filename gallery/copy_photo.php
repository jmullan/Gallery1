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

require_once(dirname(__FILE__) . '/init.php');

// Hack check
if (!$gallery->user->canWriteToAlbum($gallery->album)) {
	echo _("You are not allowed to perform this action!");
	exit;
}

$albumDB = new AlbumDB(FALSE); // read album database

list($index, $startPhoto, $endPhoto, $newAlbum) = getRequestVar(array('index', 'startPhoto', 'endPhoto', 'newAlbum'));

printPopupStart(_("Copy Photo"));

if ($gallery->session->albumName && isset($index)) {
	$numPhotos = $gallery->album->numPhotos(1);
	// we are copying from one album to another
	if (!empty($newAlbum)) {
		$postAlbum = $albumDB->getAlbumByName($newAlbum);
		if (!$postAlbum) {
			echo gallery_error(sprintf(_("Invalid album selected: %s"),
			$newAlbum));
		} else {
			if ($gallery->album->isAlbum($index)) {
				echo gallery_error(sprintf(_("Can't copy album #%d"),
				$index));
			}
			// copying "picture" to another album
			else {
				for ($index = $startPhoto; $index <= $endPhoto; $index++) {
					if (!$gallery->album->isAlbum($index)) {
						set_time_limit($gallery->app->timeLimit);
						processingMsg (sprintf(_("Copying photo #%d"),$index));
						$mydir = $gallery->album->getAlbumDir();
						$myphoto = $gallery->album->getPhoto($index);
						$myname = $myphoto->image->name;
						$myresized = $myphoto->image->resizedName;
						$mytype = $myphoto->image->type;
						$myfile = "$mydir/$myname.$mytype";
						$myhidden = $myphoto->isHidden();
						if (($postAlbum->fields["thumb_size"] == $gallery->album->fields["thumb_size"]) &&
						(!$myphoto->isMovie())) {
							$pathToThumb = "$mydir/$myname.thumb.$mytype";
						} else {
							$pathToThumb = '';
							echo "- ". _("Creating Thumbnail") ."<br>";
							my_flush();
						}
						$photo = $gallery->album->getPhoto($index);

						$id = $gallery->album->getPhotoId($index);

						$err = $postAlbum->addPhoto($myfile, $mytype, $myname,
						  $gallery->album->getCaption($index),
						  $pathToThumb, $photo->extraFields,
						  $gallery->album->getItemOwner($index),
						  NULL,
						  '', 0, 0, 0, 0,
						  false
						);

						if (!$err) {
							if ($postAlbum->getAddToBeginning()) {
								$newPhotoIndex = 1;
							} else {
								$newPhotoIndex = $postAlbum->numPhotos(1);
							}

							// Save additional item settings... currently:
							//  $clicks $keywords $comments $uploadDate $itemCaptureDate;
							$newphoto = $postAlbum->getPhoto($newPhotoIndex);
							$oldphoto = $gallery->album->getPhoto($index);

							$newphoto->clicks = $oldphoto->clicks;
							$newphoto->keywords = $oldphoto->keywords;
							$newphoto->comments = $oldphoto->comments;
							$newphoto->uploadDate = $oldphoto->uploadDate;
							$newphoto->itemCaptureDate = $oldphoto->itemCaptureDate;
							if ($myhidden) {
								$newphoto->hide();
							}
							$postAlbum->setPhoto($newphoto,$newPhotoIndex);
							$postAlbum->save(array(i18n("An image %s has been copied into this album"), $id));
						} else {
							echo gallery_error($err);
							return;
						}
					} else {
						echo sprintf(_("Skipping Album #%d"), $index)."<br>";
						// we hit an album... don't copy it... just increment the index
						$index++;
					}
					//end for
				}
				//end else
			}
		       	?>
			<form>
			<input type="button" value="<?php echo _("Dismiss") ?>" onclick="parent.close()" class="g-button">
			</form>
		       	<?php
		       	return;
		       	//end if ($gallery->album != $postAlbum)
		}
		//end if (isset($newAlbum))
	}
	elseif (isset($newAlbum) && $newAlbum == 0) {
		echo gallery_error(_("Please select the album where you want to copy the photo(s) to."));
	}
	if ($gallery->album->isAlbum($index)) {
		echo gallery_error(sprintf(_("Can't copy album #%d"), $index));
		return;
	} else {
		echo $gallery->album->getThumbnailTag($index)
?>
<p>
<?php echo _("Copy a range of photos to a new album:") ?><br>
<i>(<?php echo _("To copy just one photo, make First and Last the same.") ?>)</i><br>
<i>(<?php echo _("Nested albums in this range will be ignored.") ?>)</i>
<?php echo makeFormIntro("copy_photo.php",
	array("name" => "copy_to_album_form"),
	array("type" => "popup"));
?>
<input type="hidden" name="index" value="<?php echo $index ?>">

<?php
// Display album list for a photo and display num photos to copy
?>
<table>
<tr>
	<td align="center"><b><?php echo _("First") ?></b></td>
	<td align="center"><b><?php echo _("Last") ?></b></td>
	<td align="center"><b><?php echo _("New Album") ?></b></td>
</tr>
<tr>
	<td align="center">
	<select name="startPhoto">
<?php
for ($i = 1; $i <= $numPhotos; $i++) {
        $sel = "";
        if ($i == $index) {
                $sel = "selected";
        }
        echo "\n\t<option value=\"$i\" $sel> $i</option>";
}
?>
	</select>
	</td>
	<td align="center">
	<select name="endPhoto">
<?php
for ($i = 1; $i <= $numPhotos; $i++) {
        $sel = "";
        if ($i == $index) {
                $sel = "selected";
        }
        echo "\n\t<option value=\"$i\" $sel> $i</option>";
}
?>
	</select>
	</td>
	<td>
	<select name="newAlbum">
<?php
	$uptodate= printAlbumOptionList(0,0,1);
?>
	</select>
	</td>
</tr>
</table>
<?php
} // end else
?>
<br>
<input type="submit" value="<?php echo gTranslate('core', "Copy to Album!") ?>" class="g-button">
<input type="button" name="close" value="<?php echo _("Cancel") ?>" onclick="parent.close()" class="g-button">
</form>
<?php

    if (!$uptodate) {
	echo "<br>". infoBox(array(array(
	    'type' => 'warning',
	    'text' => sprintf(_("WARNING: Some of the albums need to be upgraded to the current version of %s."), Gallery()) ." ".
		      galleryLink(makeGalleryUrl("upgrade_album.php"), _("Upgrade now"))
	)));
    }
}
else {
	echo gallery_error(_("no album / index specified"));
}
?>
</div>
<?php print gallery_validation_link("copy_photo.php", true, array('index' => $index)); ?>
</body>
</html>
