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

list($index, $startPhoto, $endPhoto, $newAlbum) =
	getRequestVar(array('index', 'startPhoto', 'endPhoto', 'newAlbum'));

printPopupStart(gTranslate('core', "Copy Photo"));

// Hack checks
$current = $gallery->album->getPhoto($index);
if(isset($startPhoto))	$myStart = $gallery->album->getPhoto($startPhoto);
if(isset($endPhoto))	$myEnd	 = $gallery->album->getPhoto($endPhoto);

if (! isset($gallery->album) || ! isset($gallery->session->albumName) ||
	! $current ||
	(isset ($startPhoto) && !$myStart) ||
	(isset ($endPhoto) && !$myEnd) ||
	! isValidText($newAlbum) ||
	(isset ($startPhoto) && isset($endPhoto) && intval($startPhoto) > intval($endPhoto)))
{
	showInvalidReqMesg();
	exit;
}

if (!$gallery->user->canWriteToAlbum($gallery->album)) {
	showInvalidReqMesg(gTranslate('core', "You are not allowed to perform this action!"));
	exit;
}

$albumDB = new AlbumDB(FALSE);

if (!empty($newAlbum)) {
	$postAlbum = $albumDB->getAlbumByName($newAlbum);

	if (!$postAlbum) {
		showInvalidReqMesg(gTranslate('core', "Invalid album selected."));
		exit;
	}
}

if ($gallery->album->isAlbum($index)) {
	showInvalidReqMesg(gTranslate('core', "Copying of subalbums not supported, sorry."));
	exit;
}

if (isset($newAlbum) && $newAlbum == "0") {
	echo gallery_error(gTranslate('core', "Please select the album where you want to copy the photo(s) to."));
}

if (isset($postAlbum)) {
	for ($i = $startPhoto; $i <= $endPhoto; $i++) {
		if (!$gallery->album->isAlbum($i)) {
			$myphoto	= $gallery->album->getPhoto($i);
			$mydir		= $gallery->album->getAlbumDir();
			$myname		= $myphoto->image->name;
			$myresized	= $myphoto->image->resizedName;
			$mytype		= $myphoto->image->type;
			$myfile		= "$mydir/$myname.$mytype";
			$myhidden	= $myphoto->isHidden();
			$myid		= $gallery->album->getPhotoId($i);

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

			list($status, $statusMsg) = $postAlbum->addPhoto(
							$myfile, $mytype, $myname,
							$gallery->album->getCaption($i),
							$pathToThumb, $myphoto->extraFields,
							$gallery->album->getItemOwner($i),
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
				$oldphoto = $gallery->album->getPhoto($i);

				$newphoto->clicks		= $oldphoto->clicks;
				$newphoto->keywords		= $oldphoto->keywords;
				$newphoto->comments		= $oldphoto->comments;
				$newphoto->uploadDate		= $oldphoto->uploadDate;
				$newphoto->itemCaptureDate	= $oldphoto->itemCaptureDate;

				if ($myhidden) {
					$newphoto->hide();
				}

				$postAlbum->setPhoto($newphoto, $newPhotoIndex);
				$postAlbum->save(array(i18n("An image %s has been copied into this album"), $myid));

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
			// we hit an album... don't copy it... just increment the index
			printInfoBox(array(
				'type' => 'information',
				'text' => gTranslate('core', "Skipping subalbum. Copying currently not supported."))
			);
			$i++;
		}
		//end for
	}
	echo '<p class="g-emphasis g-desc-cell">' . gTranslate('core', "Copy Again?"). '</p>';
}

$numPhotos = $gallery->album->numPhotos(1);

echo $gallery->album->getThumbnailTag($index)
?>
<p>
<?php echo gTranslate('core', "Copy a range of photos to a new album:") ?><br>
<i>(<?php echo gTranslate('core', "To copy just one photo, make First and Last the same.") ?>)</i><br>
<i>(<?php echo gTranslate('core', "Nested albums in this range will be ignored.") ?>)</i>

<?php echo makeFormIntro('copy_photo.php',
	array('name' => 'copy_to_album_form'),
	array('type' => 'popup', 'index' => $index));

	for ($i = 1; $i <= $numPhotos; $i++) {
		$indexArray[$i] = $i;
	}

	list($uptodate, $albumOptionList) = albumOptionList(0,0,0);
// Display album list for a photo and display num photos to copy
?>
<table>
<tr>
	<td align="center"><b><?php echo gTranslate('core', "First") ?></b></td>
	<td align="center"><b><?php echo gTranslate('core', "Last") ?></b></td>
	<td align="center"><b><?php echo gTranslate('core', "New Album") ?></b></td>
</tr>
<tr>
	<td align="center"><?php echo drawSelect('startPhoto', $indexArray, $index); ?></td>
	<td align="center"><?php echo drawSelect('endPhoto', $indexArray, $index); ?></td>
	<td><?php echo drawSelect2('newAlbum', $albumOptionList); ?></td>
</tr>
</table>
<br>
<?php
	echo gSubmit('copy', gTranslate('core', "Cop_y to Album"));
	echo gButton('close', gTranslate('core', "_Close"), 'parent.close()');
?>
</form>
<?php
if (!$uptodate) {
	echo "<br>". infoBox(array(array(
		'type' => 'warning',
		'text' => sprintf(gTranslate('core', "WARNING: Some of the albums need to be upgraded to the current version of %s."), Gallery()) ." ".
					galleryLink(makeGalleryUrl("upgrade_album.php"), gTranslate('core', "Upgrade now"))
	)));
}

includeTemplate('overall.footer');
?>
</body>
</html>
