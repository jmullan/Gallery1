<?
if ($save) {
	$album->setCaption($index, $data);
	$album->save();
	dismissAndReload();
	return;
}
?>

<? require('style.php'); ?>

<center>
Enter a caption for this picture in the text
box below.

<form action=edit_caption.php method=POST>
<input type=hidden name="save" value=1>
<input type=hidden name="index" value="<?= $index ?>">
<textarea name="data" rows=5 cols=40>
<?= $album->getCaption($index) ?>
</textarea>

<br>

<input type=submit name="submit" value="Save">
<input type=submit name="submit" value="Cancel" onclick='parent.close()'>

<p>
<?= $album->getThumbnailTag($index) ?>

</form>
