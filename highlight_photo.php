<? require('style.php'); ?>

<?
if ($albumName && isset($index)) {
	if ($confirm) {
		$album->setHighlight($index);
		$album->save();
		dismissAndReload();
		return;
	} else {
?>

<center>
Do you want this photo to be the one that
shows up on the album list?
<br>
<form action=highlight_photo.php>
<input type=hidden name=index value=<?= $index?>>
<input type=submit name=confirm value="Yes">
<input type=submit value="No" onclick='parent.close()'>
</form>

<p>
<?= $album->getThumbnailTag($index) ?>
<br>
<?= $album->getCaption($index) ?>

<?
	}
} else {
	error("no album / index specified");
}
?>
