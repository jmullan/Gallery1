<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2003 Bharat Mediratta
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
// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
	print _("Security violation") ."\n";
	exit;
}
?>
<?php if (!isset($GALLERY_BASEDIR)) {
    $GALLERY_BASEDIR = './';
}
require($GALLERY_BASEDIR . 'init.php'); ?>
<?php
// Hack check
if (!$gallery->user->canWriteToAlbum($gallery->album)) {
	exit;
}

$albumDB = new AlbumDB(FALSE); // read album database

?>
<html>
<head>
  <title><?php echo _("Move Photo") ?></title>
  <?php echo getStyleSheetLink() ?>
</head>
<body dir="<?php echo $gallery->direction ?>">

<?php
if ($gallery->session->albumName && isset($index)) {
	$numPhotos = $gallery->album->numPhotos(1);

	// Here we are "moving" a photo from one album to another by "adding" it to the new album
	// and then deleting it from the old one.  This could be optimized because our thumnails 
	// and resized images already exist in the original directory, but the current method is an easy
	// way to make sure all thumbnails and resized images are the correct size.

        if (isset($newAlbum)) {	// we are moving from one album to another
            	$postAlbum = $albumDB->getAlbumbyName($newAlbum);
	    	if ($gallery->album->fields['name'] != $postAlbum->fields['name']) {
			//$startPhoto=$index;
			//$endPhoto=$startPhoto+max($numPhotosToMove,1);

			//if ($startPhoto > $endPhoto) { // the end photo value needs to be greater than the start value
			//	dismissAndReload();
			//	return;
			//}
			
			if ($gallery->album->isAlbumName($index)) { // moving "album" to another location
				if ($newAlbum == "ROOT") { // moving "album" to ROOT location
					$myAlbum = $gallery->album->getNestedAlbum($index);
					$myAlbum->fields[parentAlbumName] = 0;
					$gallery->album->deletePhoto($index, 0, 0); 
					$myAlbum->save();
					$gallery->album->save();
				} else { // moving "album" to another album
					$myAlbum = $gallery->album->getNestedAlbum($index);
					if ($postAlbum != $myAlbum) { // we don't ever want to point an album back at itself!!!
						$postAlbum->addNestedAlbum($gallery->album->isAlbumName($index)); // copy "album" to new album
						$myAlbum->fields[parentAlbumName] = $postAlbum->fields[name];

						// delete "album" from original album
						$gallery->album->deletePhoto($index, 0, 0);
						$postAlbum->save();
						$gallery->album->save();
						$myAlbum->save();
					}
				}
			} else { // moving "picture" to another album
				$index = $startPhoto; // set the index to the first photo that we are moving.	
				while ($startPhoto <= $endPhoto) {
					if (!$gallery->album->isAlbumName($index)) {
					        set_time_limit($gallery->app->timeLimit);
						echo _("Moving photo #").$startPhoto."<br>";
						my_flush();
						$mydir = $gallery->album->getAlbumDir();
						$myphoto = $gallery->album->getPhoto($index);
						$myname = $myphoto->image->name;
						$myresized = $myphoto->image->resizedName;
						$mytype=$myphoto->image->type;
						$myfile="$mydir/$myname.$mytype";
						$myhidden=$myphoto->isHidden();
						//print "mydir=".$mydir."<br>";
						//print "myphoto=".$myphoto."<br>";
						//print "myname=".$myname."<br>";
						//print "mytype=".$mytype."<br>";
						//print "myfile=".$myfile."<br>";
						if (($postAlbum->fields["thumb_size"] == $gallery->album->fields["thumb_size"]) &&
						    (!$myphoto->isMovie())) {
							$pathToThumb="$mydir/$myname.thumb.$mytype";
						} else {
							$pathToThumb="";
							echo "- ". _("Creating Thumbnail") ."<br>";
							my_flush();
						}
						$photo=$gallery->album->getPhoto($index);

						$err = $postAlbum->addPhoto($myfile, $mytype, $myname, $gallery->album->getCaption($index), $pathToThumb, $photo->extraFields, $gallery->album->getItemOwner($index));
						if (!$err) {
							$newPhotoIndex = $postAlbum->numPhotos(1);

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

							/* resize the photo if needed */
							if ($postAlbum->fields["resize_size"] > 0  || $postAlbum->fields["resize_file_size"] > 0) {
								$photo = $postAlbum->getPhoto($newPhotoIndex);
								list($w, $h) = $photo->image->getRawDimensions();
								$size = $photo->image->getRawFilesize();
								if ($w > $postAlbum->fields["resize_size"] ||
								    $h > $postAlbum->fields["resize_size"] ||
								    $size > $postAlbum->fields["resize_file_size"]) {
									if (($postAlbum->fields["resize_size"] == $gallery->album->fields["resize_size"]) &&
									    ($postAlbum->fields["resize_file_size"] == $gallery->album->fields["resize_file_size"]) &&
									   ($myresized)) {
										$pathToResized="$mydir/$myresized.$mytype";
									} else {
										$pathToResized="";
										echo "- " . _("Resizing photo") ."<br>";
										my_flush();
									}
									$postAlbum->resizePhoto($newPhotoIndex, $postAlbum->fields["resize_size"], $postAlbum->fields["resize_file_size"], $pathToResized);
								}
							}
							
							$postAlbum->save();
							if ($startPhoto == $endPhoto) {
								if (!$gallery->album->hasHighlight()) {
									$resetHighlight = 1;
									$gallery->album->deletePhoto($index,$resetHighlight);
									echo _("- Creating New Album Highlight") ."<br>";
								} else {
									$gallery->album->deletePhoto($index);
								}
							} else {
								$resetHighlight = -1;
								$gallery->album->deletePhoto($index,$resetHighlight);
							}
							$gallery->album->save();
						} else {
							echo "<font color=red>". _("Error") . ": "."$err!</font>";
							return;
                				}
			     		} else {
						echo sprintf(_("Skipping Album #%d"), $startPhoto)."<br>";
						$index++; // we hit an album... don't move it... just increment the index
					}
					$startPhoto++;
	    			} //end while
			} //end else
		} //end if ($gallery->album != $postAlbum)
		dismissAndReload();
		return;
	} //end if (isset($newAlbum))

        if (isset($newIndex)) {
		$gallery->album->movePhoto($index, $newIndex);
		$gallery->album->save();
		dismissAndReload();
		return;
	} else {
?>

<center>
<?php
if ($gallery->album->isAlbumName($index)) {
?>
<?php echo _("Move this album within the album:") ?><br>
<?php } else { ?>
<?php echo _("Move this photo within the album:") ?><br>
<?php } ?>
<i>(<?php echo sprintf(_("Current Location is %s"), $index) ?>)</i>
<p>
<?php echo $gallery->album->getThumbnailTag($index) ?>
<p>
<?php echo makeFormIntro("move_photo.php", array("name" => "theform")); ?>
<?php echo _("Select the new location:") ?> 
<input type=hidden name="index" value="<?php echo $index ?>">
<select name="newIndex">
<?php
for ($i = 1; $i <= $numPhotos; $i++) {
        $sel = "";
        if ($i == $index) {
                $sel = "selected";
        }
		$j = $i - 1;
        echo "<option value=$j $sel> $i</option>";
}
?>
</select>
<p>
<input type="submit" value=<?php echo '"' . _("Move it!") . '"' ?>>
<input type="button" name="close" value="<?php echo _("Cancel") ?>" onclick='parent.close()'>
</form>
<p>
<hr size="1">
<b><?php echo _("OR") ?></b>
<hr size="1">




<?php
if ($gallery->album->isAlbumName($index)) {
?>
<?php echo _("Move the album to a new album:") ?><br>
<?php echo makeFormIntro("move_photo.php", array("name" => "move_to_album_form")); ?>
<input type=hidden name="index" value="<?php echo $index ?>">
<select name="newAlbum">
<?php
	$uptodate=printAlbumOptionList(1,0,0);
?>
</select>
<?php
} else {  
?>
<?php echo _("Move a range of photos to a new album:") ?><br>
<i>(<?php echo _("To move just one photo, make First and Last the same") ?>)</i><br>
<i>(<?php echo _("Nested albums in this range will be ignored") ?>)</i><p>
<?php echo makeFormIntro("move_photo.php", array("name" => "move_to_album_form")); ?>
<input type=hidden name="index" value="<?php echo $index ?>">

<?php
// Display album list for a photo and display num photos to move
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
        echo "<option value=$i $sel> $i</option>";
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
        echo "<option value=$i $sel> $i</option>";
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
if (!$uptodate) {
	print '<span class="error"> <br>' . _("WARNING: Some of the albums need to be upgraded to the current version of gallery.") . '</span>  ' .
	'<a href="'. makeGalleryUrl("upgrade_album.php").'"><br>'. _("Upgrade now") . '</a>.<p>';
}
?>
<br>
<input type="submit" value="<?php echo _("Move to Album!") ?>">
<input type="button" name="close" value="<?php echo _("Cancel") ?>" onclick='parent.close()'>
</form>
<?php
}
} else {
	gallery_error(_("no album / index specified"));
}
?>
</font>

<script language="javascript1.2">
<!--   
// position cursor in top form field
document.theform.newIndex.focus();
//-->
</script>
</body>
</html>

