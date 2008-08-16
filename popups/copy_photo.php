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

require_once(dirname(dirname(__FILE__)) . '/init.php');

// Hack check
if (!$gallery->user->canWriteToAlbum($gallery->album)) {
	echo gTranslate('core', "You are not allowed to perform this action!");
	exit;
}

$albumDB = new AlbumDB(FALSE); // read album database

list($index, $startPhoto, $endPhoto, $newAlbum) =
	getRequestVar(array('index', 'startPhoto', 'endPhoto', 'newAlbum'));

printPopupStart(gTranslate('core', "Copy Photo"));

if ($gallery->session->albumName && isset($index)) {
	$numPhotos = $gallery->album->numPhotos(1);
	// we are copying from one album to another
	if (!empty($newAlbum)) {
		$postAlbum = $albumDB->getAlbumByName($newAlbum);

		if (!$postAlbum) {
			echo gallery_error(sprintf(gTranslate('core', "Invalid album selected: %s"),
			$newAlbum));
		}
		else {
			if ($gallery->album->isAlbum($index)) {
				echo gallery_error(sprintf(gTranslate('core', "Can't copy album #%d"), $index));
			}
			// copying "picture" to another album
			else {
				for ($index = $startPhoto; $index <= $endPhoto; $index++) {
					if (!$gallery->album->isAlbum($index)) {
						set_time_limit($gallery->app->timeLimit);
						processingMsg (sprintf(gTranslate('core', "Copying photo #%d"),$index));
						$mydir = $gallery->album->getAlbumDir();
						$myphoto = $gallery->album->getPhoto($index);
						$myname = $myphoto->image->name;
						$myresized = $myphoto->image->resizedName;
						$mytype = $myphoto->image->type;
						$myfile = "$mydir/$myname.$mytype";
						$myhidden = $myphoto->isHidden();
						if ($postAlbum->fields["thumb_size"] == $gallery->album->fields["thumb_size"] &&
							!$myphoto->isMovie())
						{
							$pathToThumb = "$mydir/$myname.thumb.$mytype";
						}
						else {
							$pathToThumb = '';
							echo "- ". gTranslate('core', "Creating Thumbnail") ."<br>";
							my_flush();
						}

						$photo = $gallery->album->getPhoto($index);

						$id = $gallery->album->getPhotoId($index);

						list($status, $statusMsg) = $postAlbum->addPhoto($myfile, $mytype, $myname,
						  $gallery->album->getCaption($index),
						  $pathToThumb, $photo->extraFields,
						  $gallery->album->getItemOwner($index),
						  NULL,
						  '', 0, 0, 0, 0,
						  false
						);

						if ($status) {
							if ($postAlbum->getAddToBeginning()) {
								$newPhotoIndex = 1;
							}
							else {
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

							printInfoBox(array(array(
								'type' => 'success',
								'text' => gTranslate('core', "The copy process was done with success.")))
							);
							reload();
						}
						else {
							echo $statusMsg;
						}
					}
					else {
						echo sprintf(gTranslate('core', "Skipping Album #%d"), $index)."<br>";
						// we hit an album... don't copy it... just increment the index
						$index++;
					}
					//end for
				}
				//end else
			}
			echo "\n<br>";
			echo gButton('dismiss', gTranslate('core', "_Close Window"), 'parent.close()');
			echo "</div></body></html>";

			return;
			//end if ($gallery->album != $postAlbum)
		}
		//end if (isset($newAlbum))
	}
	elseif (isset($newAlbum) && $newAlbum == 0) {
		echo gallery_error(gTranslate('core', "Please select the album where you want to copy the photo(s) to."));
	}

	if ($gallery->album->isAlbum($index)) {
		echo gallery_error(sprintf(gTranslate('core', "Can't copy album #%d"), $index));
		return;
	}
	else {
		echo $gallery->album->getThumbnailTag($index)
?>
<p>
<?php echo gTranslate('core', "Copy a range of photos to a new album:") ?><br>
<i>(<?php echo gTranslate('core', "To copy just one photo, make First and Last the same.") ?>)</i><br>
<i>(<?php echo gTranslate('core', "Nested albums in this range will be ignored.") ?>)</i>

<?php echo makeFormIntro('copy_photo.php',
	array('name' => 'copy_to_album_form'),
	array('type' => 'popup', 'index' => $index));

// Display album list for a photo and display num photos to copy
?>
<table>
<tr>
	<td align="center"><b><?php echo gTranslate('core', "First") ?></b></td>
	<td align="center"><b><?php echo gTranslate('core', "Last") ?></b></td>
	<td align="center"><b><?php echo gTranslate('core', "New Album") ?></b></td>
</tr>
<tr>
	<td align="center">
	<select name="startPhoto">
<?php
	for ($i = 1; $i <= $numPhotos; $i++) {
			$sel = '';
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
			$sel = '';
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
<?php echo gSubmit('copy', gTranslate('core', "Cop_y to Album")); ?>
<?php echo gButton('close', gTranslate('core', "_Cancel"), 'parent.close()'); ?>
</form>
<?php
	if (!$uptodate) {
		echo "<br>". infoBox(array(array(
			'type' => 'warning',
			'text' => sprintf(gTranslate('core', "WARNING: Some of the albums need to be upgraded to the current version of %s."), Gallery()) ." ".
						galleryLink(makeGalleryUrl("upgrade_album.php"), gTranslate('core', "Upgrade now"))
		)));
	}
}
else {
	echo gallery_error(gTranslate('core', "no album / index specified"));
}
?>
</div>

</body>
</html>
