<? require('style.php'); ?>

<?
/* Read the album list */
$albumDB = new AlbumDB();

if ($albumName && isset($index)) {
	if (isset($newIndex)) {
		$albumDB->moveAlbum($index, $newIndex);
		$albumDB->save();
		dismissAndReload();
		return;
	} else {
		$numAlbums = $albumDB->numAlbums();
?>

<center>
Select the new location of album <?=$album->fields["title"]?>:
<form>
<input type=hidden name="index" value="<?=$index?>">
<select name="newIndex">
<?
for ($i = 1; $i <= $numAlbums; $i++) {
	$sel = "";
	if ($i == $index+1) {
		$sel = "selected";
	} 
	$j = $i - 1;
	echo "<option value=$j $sel> $i";
}
?>
<input type=submit value="Move it!">
<input type=submit name="submit" value="Cancel" onclick='parent.close()'>
</form>

<p>
<?
if ($album->numPhotos(1)) {
	echo $album->getThumbnailTag($album->getHighlight());
}
?>

<?
	}
} else {
	error("no album / index specified");
}
?>
