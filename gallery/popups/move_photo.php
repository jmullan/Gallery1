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

require_once(dirname(dirname(__FILE__)) . '/init.php');

list ($reorder, $index, $newAlbum, $newIndex, $startPhoto, $endPhoto) =
  getRequestVar(array('reorder', 'index', 'newAlbum', 'newIndex', 'startPhoto', 'endPhoto'));

// Hack check
if (!$gallery->user->canWriteToAlbum($gallery->album)) {
	echo gTranslate('core', "You are not allowed to perform this action!");
	exit;
}

$albumDB = new AlbumDB(FALSE); // read album database

if ($gallery->album->isAlbum($index)) {
	$title = !empty($reorder) ? gTranslate('core',"Reorder Album") : gTranslate('core',"Move Album");
}
else {
	$title = !empty($reorder) ? gTranslate('core',"Reorder Photo") : gTranslate('core',"Move Photo");
}

printPopupStart($title);

if ($gallery->session->albumName && isset($index)) {
	$numPhotos = $gallery->album->numPhotos(1);

	// Here we are "moving" a photo from one album to another by "adding" it to the new album
	// and then deleting it from the old one.  This could be optimized because our thumnails
	// and resized images already exist in the original directory, but the current method is an easy
	// way to make sure all thumbnails and resized images are the correct size.

	// we are moving from one album to another
	if (isset($newAlbum)) {
		if ($gallery->session->albumName == $newAlbum) {
			echo gallery_error(gTranslate('core',"You can't move photos into the album they already exist in."));
			exit;
		}
		$postAlbum = $albumDB->getAlbumByName($newAlbum);
		if (!$gallery->user->canWriteToAlbum($postAlbum)) {
			printf(gTranslate('core',"You do not have the required permissions to write to %s!"), $newAlbum);
			exit;
		}
		if ((isset($postAlbum->fields['name']) || $newAlbum == ".root") &&
			($gallery->album->fields['name'] != $postAlbum->fields['name']))
		{
			$votes_transferable = $gallery->album->pollsCompatible($postAlbum);
			$vote_id = $gallery->album->getVotingIdByIndex($index);

			if (isset($gallery->album->fields["votes"][$vote_id]) && $votes_transferable) {
				$votes = $gallery->album->fields["votes"][$vote_id];
			}
			else {
				$votes = NULL;
			}
			// moving "album" to another location
			if ($gallery->album->isAlbum($index)) {
				$myAlbum = $gallery->album->getNestedAlbum($index);
				$hIndex = $myAlbum->getHighlight();
				$oldHSize = $gallery->album->fields["thumb_size"];
				// moving "album" to .root location
				if ($newAlbum == ".root") {
					$myAlbum->fields['parentAlbumName'] = 0;
					$gallery->album->deletePhoto($index, 0, 0);
					if ($oldHSize != $gallery->app->highlight_size && isset($hIndex)) {
						$hPhoto =& $myAlbum->getPhoto($hIndex);
						$hPhoto->setHighlight($myAlbum->getAlbumDir(), true, $myAlbum);
					}
					$myAlbum->save(array(i18n("Moved to ROOT")));
					$gallery->album->save(array(i18n("Subalbum %s moved to ROOT"), $myAlbum->fields['name']));
				}
				// moving "album" to another album
				else {
					// we don't ever want to point an album back at itself!!!
					if ($postAlbum != $myAlbum) {
						// copy "album" to new album
						$postAlbum->addNestedAlbum($gallery->album->getAlbumName($index));
						if ($votes) {
							$postAlbum->fields["votes"]["album.".$myAlbum->fields["name"]]=$votes;
						}
						$myAlbum->fields['parentAlbumName'] = $postAlbum->fields['name'];

						// delete "album" from original album
						$gallery->album->deletePhoto($index, 0, 0);
						if ($oldHSize != $postAlbum->fields["thumb_size"] && isset($hIndex)) {
							$hPhoto =& $myAlbum->getPhoto($hIndex);
							$hPhoto->setHighlight($myAlbum->getAlbumDir(), true, $myAlbum);
						}
						$gallery->album->save(array(i18n("Moved subalbum %s to %s"),
						$myAlbum->fields['name'],
						$postAlbum->fields['name']));
						$myAlbum->save(array(i18n("Moved from %s to %s"),
						$gallery->album->fields['name'],
						$postAlbum->fields['name']));
						if ($postAlbum->numPhotos(1) == 1) {
							$postAlbum->setHighlight(1);
						}
						$postAlbum->save(array(i18n("New subalbum %s from %s"),
						$myAlbum->fields['name'],
						$gallery->album->fields['name']));
					}
				}
				// moving "picture" to another album
			}
			else {
				$index = $startPhoto; // set the index to the first photo that we are moving.

				while ($startPhoto <= $endPhoto) {
					if (!$gallery->album->isAlbum($index)) {
						set_time_limit($gallery->app->timeLimit);
						echo gTranslate('core',"Moving photo #").$startPhoto."<br>";
						my_flush();
						$mydir = $gallery->album->getAlbumDir();
						$myphoto = $gallery->album->getPhoto($index);
						$myname = $myphoto->image->name;
						$myresized = $myphoto->image->resizedName;
						$mytype = $myphoto->image->type;
						$myfile = "$mydir/$myname.$mytype";
						$myhidden = $myphoto->isHidden();

						if (($postAlbum->fields["thumb_size"] == $gallery->album->fields["thumb_size"]) &&
						  (!$myphoto->isMovie())) {
							$pathToThumb="$mydir/$myname.thumb.$mytype";
						}
						else {
							$pathToThumb = '';
							echo "- ". gTranslate('core',"Creating Thumbnail") ."<br>";
							my_flush();
						}
						$photo = $gallery->album->getPhoto($index);

						$id = $gallery->album->getPhotoId($index);
						list($status, $statusMsg) = $postAlbum->addPhoto($myfile, $mytype, $myname,
						  $gallery->album->getCaption($index),
						  $pathToThumb,
						  $photo->extraFields,
						  $gallery->album->getItemOwner($index),
						  $votes,
						  '', 0, 0, 0, 0,
						  false
						);

						if ($status) {
							$newPhotoIndex = $postAlbum->getAddToBeginning() ? 1 : $postAlbum->numPhotos(1);

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

							$postAlbum->save(array(i18n("New image %s from album %s"),
							  makeAlbumURL($postAlbum->fields["name"], $gallery->album->getPhotoId($index)),

							$gallery->album->fields['name']));
							if ($startPhoto == $endPhoto) {
								if (!$gallery->album->hasHighlight()) {
									$resetHighlight = 1;
									$gallery->album->deletePhoto($index,$resetHighlight);
									echo gTranslate('core',"- Creating New Album Highlight") ."<br>";
								} else {
									$gallery->album->deletePhoto($index);
								}
							} else {
								$resetHighlight = -1;
								$gallery->album->deletePhoto($index,$resetHighlight);
							}
							$gallery->album->save(array(
							  i18n("%s moved to %s"),
							  $id,
							  $postAlbum->fields['name']
							  )
							);
						} else {
							echo $statusMsg;
							return;
						}
					} else {
						echo sprintf(gTranslate('core',"Skipping Album #%d"), $startPhoto)."<br>";
						 // we hit an album... don't move it... just increment the index
						$index++;
					}
					$startPhoto++;
					//end while
				}
				//end else
			}
			if ($votes) {
				unset($gallery->album->fields["votes"][$vote_id]);
				$gallery->album->save();
			}
		} //end if ($gallery->album != $postAlbum)
		dismissAndReload();
		return;
	} //end if (isset($newAlbum))

	if (isset($newIndex)) {
		$gallery->album->movePhoto($index, $newIndex);
		$gallery->album->save(array(i18n("Images rearranged")));
		dismissAndReload();
		return;
	}
	else {
		echo '<br>'. $gallery->album->getThumbnailTag($index) .'<br><br>';
		if ($reorder ) { // Reorder, intra-album move
			if ($gallery->album->isAlbum($index)) {
				echo gTranslate('core',"Reorder this album within the album:") ."<br>";
			}
			else {
				echo gTranslate('core',"Reorder this photo within the album:") ."<br>";
			}
?>
<i>(<?php echo sprintf(gTranslate('core',"Current Location is %s"), $index) ?>)</i>

<?php

echo makeFormIntro('move_photo.php', array(), array('type' => 'popup', 'index' => $index));
echo "\n<br><br>";
echo gTranslate('core',"Select the new location:"); ?>

<select name="newIndex">
<?php
for ($i = 1; $i <= $numPhotos; $i++) {
		$sel = '';
		if ($i == $index) {
				$sel = "selected";
		}
		$j = $i - 1;
		echo "<option value=\"$j\" $sel>$i</option>";
}
?>
</select>
<br><br>
<?php echo gSubmit ('submit', $reorder ? gTranslate('core','Re_order it!') : gTranslate('core','_Move it!')); ?>
<?php echo gButton('close', gTranslate('core', "_Cancel"), 'parent.close()'); ?>
</form>

<?php
// Don't reorder, trans-album move
		}
		else if (!$reorder) {
		if ($gallery->album->isAlbum($index)) {
			echo gTranslate('core',"Move the album to different position in your gallery:");
			echo makeFormIntro('move_photo.php',
			  array('name' => 'move_to_album_form'),
			  array('type' => 'popup', 'index' => $index));
?>
<select name="newAlbum">
<?php
			$uptodate = printAlbumOptionList(1,0,0);
?>
</select>
<?php
		}
		else {
			echo gTranslate('core',"Move a range of photos to a new album:");
?><br>
<i>(<?php echo gTranslate('core',"To move just one photo, make First and Last the same.") ?>)</i><br>
<i>(<?php echo gTranslate('core',"Nested albums in this range will be ignored.") ?>)</i>

<?php echo makeFormIntro('move_photo.php',
	array('name' => 'move_to_album_form'),
	array('type' => 'popup', 'index' => $index));

// Display album list for a photo and display num photos to move
?>
<table>
<tr>
<td align="center"><b><?php echo gTranslate('core',"First") ?></b></td>
<td align="center"><b><?php echo gTranslate('core',"Last") ?></b></td>
<td align="center"><b><?php echo gTranslate('core',"New Album") ?></b></td>
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
				echo "<option value=\"$i\" $sel> $i</option>";
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
				echo "<option value=\"$i\" $sel> $i</option>";
		}
?>
</select>
</td>
<td>
<select name="newAlbum">
<?php
		$uptodate = printAlbumOptionList(0,0,1);
?>
</select>
</td>
</tr>
</table>
<?php
		} // end else
		if (sizeof($gallery->album->fields["votes"])> 0) {
			print "<br>";
			   	if ($gallery->album->fields["poll_type"] == "rank") {
				   	echo '<span class="g-attention">' . gTranslate('core',"Note: items that have votes will lose these votes when moved to another album") . "</span>"; // can't move rank votes, doesn't  make sense.
			  	} else {
				   	echo '<span class="g-attention">' . sprintf(gTranslate('core',"Note: items that have votes will lose these votes if moved to an album without compatible polling.  Compatible albums are marked with an &quot;%s&quot;."), "*") . "</span>";
			   	}
			echo "\n<br>";
		}

		if (!$uptodate) {
			echo '<span class="g-attention">' . sprintf(gTranslate('core',"WARNING: Some of the albums need to be upgraded to the current version of %s."), Gallery()) . '</span>';
			echo "\n<br>";
			echo galleryLink(makeGalleryUrl("upgrade_album.php"), gTranslate('core', "Upgrade now"));
		}
?>
<br>
<?php echo gSubmit('submit', $title); ?>
<?php echo gButton('close', gTranslate('core', "_Cancel"), 'parent.close()'); ?>
</form>
<?php
		} // end reorder
	}
} else {
	echo gallery_error(gTranslate('core',"no album / index specified"));
}
?>

<script language="javascript1.2" type="text/JavaScript">
<!--
// position cursor in top form field
<?php
if ($reorder) {
	echo 'document.g1_form.newIndex.focus()';
} else {
	echo 'document.move_to_album_form.newAlbum.focus()';
} ?>
//-->
</script>

</div>

</body>
</html>
