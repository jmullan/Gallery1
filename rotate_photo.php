<? require('style.php'); ?>

<?
if ($albumName && isset($index)) {
	if ($rotate) {
		$album->rotatePhoto($index, $rotate);
		$album->save();
		dismissAndReload();
		return;
	} else {
?>

<center>
How do you want to rotate this photo?
<br>
<a href=rotate_photo.php?rotate=90&albumName=<?= $album->fields["name"] ?>&index=<?= $index ?>>Counter-Clockwise</a>
/
<a href=rotate_photo.php?rotate=-90&albumName=<?= $album->fields["name"] ?>&index=<?= $index ?>>Clockwise</a>
/
<a href="javascript:void(parent.close())">Cancel</a>
<br>

<p>
<?= $album->getThumbnailTag($index) ?>

<?
	}
} else {
	error("no album / index specified");
}
?>
