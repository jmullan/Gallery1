<?
if ($confirm && isset($index)) {
	$album->deletePhoto($index);
	$album->save();
	dismissAndReload();
	return;
}

require('style.php');
if ($album && isset($index)) {
?>

<center>
Do you really want to delete this photo?
<br>
<form action=delete_photo.php>
<input type=hidden name=index value=<?= $index?>>
<input type=submit name=confirm value="Delete">
<input type=submit value="Cancel" onclick='parent.close()'>
</form>
<br>

<p>
<?= $album->getThumbnailTag($index) ?>
<br>
<?= $album->getCaption($index) ?>

<?
} else {
	error("no album / index specified");
}
?>
