<?
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000 Bharat Mediratta
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
 */
?>
<? require_once('init.php'); ?>
<?
// Hack check
if (!$gallery->user->canWriteToAlbum($gallery->album)) {
	exit;
}

$albumDB = new AlbumDB(); // read album database

?>
<html>
<head>
  <title>Move Photo</title>
  <link rel="stylesheet" type="text/css" href="<?= getGalleryStyleSheetName() ?>">
</head>
<body>

<?
if ($gallery->session->albumName && isset($index)) {
	$numPhotos = $gallery->album->numPhotos(1);

	// Here we are "moving" a photo from one album to another by "adding" it to the new album
	// and then deleting it from the old one.  This could be optimized because our thumnails 
	// and resized images already exist in the original directory, but the current method is an easy
	// way to make sure all thumbnails and resized images are the correct size.

        if (isset($newAlbum)) {	// we are moving from one album to another
            	$postAlbum = $albumDB->getAlbumbyName($newAlbum);
	    	if ($gallery->album != $postAlbum) {
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
					$gallery->album->deletePhoto($index); 
					$myAlbum->save();
					$gallery->album->save();
				} else { // moving "album" to another album
					$myAlbum = $gallery->album->getNestedAlbum($index);
					if ($postAlbum != $myAlbum) { // we don't ever want to point an album back at itself!!!
						$postAlbum->addNestedAlbum($gallery->album->isAlbumName($index)); // copy "album" to new album
						$myAlbum->fields[parentAlbumName] = $postAlbum->fields[name];
						$gallery->album->deletePhoto($index); // delete "album" from original album
						$postAlbum->save();
						$gallery->album->save();
						$myAlbum->save();
					}
				}
			} else { // moving "picture" to another album
				$index = $startPhoto; // set the index to the first photo that we are moving.	
				while ($startPhoto <= $endPhoto) {
					if (!$gallery->album->isAlbumName($index)) {
						print "<br>Moving photo #".$startPhoto."<br>";
						my_flush();
						$mydir = $gallery->album->getAlbumDir();
						$myphoto = $gallery->album->getPhoto($index);
						$myname = $myphoto->image->name;
						$mytype=$myphoto->image->type;
						$myfile="$mydir/$myname.$mytype";
						//print "mydir=".$mydir."<br>";
						//print "myphoto=".$myphoto."<br>";
						//print "myname=".$myname."<br>";
						//print "mytype=".$mytype."<br>";
						//print "myfile=".$myfile."<br>";
						$err = $postAlbum->addPhoto($myfile, $mytype);
						if (!$err) {
							$newPhotoIndex = $postAlbum->numPhotos(1);
							// Set the caption of the new photo
							$postAlbum->setCaption($newPhotoIndex, $gallery->album->getCaption($index));
							$postAlbum->save();
							/* resize the photo if needed */
							if ($postAlbum->fields["resize_size"] > 0 ) {
								$photo = $postAlbum->getPhoto($newPhotoIndex);
								list($w, $h) = $photo->getDimensions();
								if ($w > $postAlbum->fields["resize_size"] ||
								    $h > $postAlbum->fields["resize_size"]) {
									print "- Resizing #".$startPhoto;
									$postAlbum->resizePhoto($newPhotoIndex, $postAlbum->fields["resize_size"]);
								}
							}
							$postAlbum->save();
							$gallery->album->deletePhoto($index);
							$gallery->album->save();
						} else {
							print "<font color=red>Error: $err!</font>";
							return;
                				}
			     		} else {
						print "<br>Skipping Album #".$startPhoto."<br>";
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
<?
if ($gallery->album->isAlbumName($index)) {
?>
Move this album within the album:<br>
<? } else { ?>
Move this photo within the album:<br>
<? } ?>
<i>(Current Location is <?=$index?>)</i>
<p>
<?= $gallery->album->getThumbnailTag($index) ?>
<p>
<form name="theform">
Select the new location:
<input type=hidden name="index" value="<?=$index?>">
<select name="newIndex">
<?
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
<input type=submit value="Move it!">
<input type=submit name="submit" value="Cancel" onclick='parent.close()'>
</form>
<p>
<hr size=1>
<b>OR</b>
<hr size=1>




<?
if ($gallery->album->isAlbumName($index)) {
?>
Move the album to a new album:<br>
<form name=move_to_album_form>
<input type=hidden name="index" value="<?=$index?>">
<select name="newAlbum">
<?
	printAlbumOptionList(1,0,0);
?>
</select>
<?
} else {  
?>
Move a range of photos to a new album:<br>
<i>(To move just one photo, make First and Last the same)</i><br>
<i>(Nested albums in this range will be ignored)</i><p>
<form name=move_to_album_form>
<input type=hidden name="index" value="<?=$index?>">

<?
// Display album list for a photo and display num photos to move
?>
<table>
<tr>
<td align=center><b>First</b></td>
<td align=center><b>Last</b></td>
<td align=center><b>New Album</b></td>
</tr>
<tr>
<td align=center>
<select name="startPhoto">
<option value=1 selected> 1</option>
<?
for ($i = 2; $i <= $numPhotos; $i++) {
        $sel = "";
        if ($i == $index) {
                $sel = "selected";
        }
        echo "<option value=$i $sel> $i</option>";
}
?>
</select>
</td>
<td align=center>
<select name="endPhoto">
<option value=1 selected> 1</option>
<?
for ($i = 2; $i <= $numPhotos; $i++) {
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
<?
printAlbumOptionList(0,0,1); 
?>
</select>
</td>
</tr>
</table>
<?
} // end else
?>
<br>
<input type=submit value="Move to Album!">
<input type=submit name="submit" value="Cancel" onclick='parent.close()'>
</form>
<?
}
} else {
	error("no album / index specified");
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

