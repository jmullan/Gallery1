<? require('style.php'); ?>

<?
if ($albumName) {
	if ($confirm) {
		$album->shufflePhotos();
		$album->save();
		dismissAndReload();
		return;
	} else {
?>

<center>
Do you really want to shuffle all the photos in this album?  This can't be undone.  You'll also need to reset the highlight photo (shown below).
<br>
<form>
<input type=submit name=confirm value="Yes">
<input type=submit value="Cancel" onclick='parent.close()'>
</form>
<br>

<p>
<?= $album->getThumbnailTag($album->getHighlight()) ?>
<br>
<?= $album->fields["caption"] ?>

<?
	}
} else {
	error("no album specified");
}
?>
