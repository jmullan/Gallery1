<? require('style.php'); ?>

<?
if ($albumName && isset($index)) {
	$numPhotos = $album->numPhotos(1);
	if (isset($newIndex)) {
		$album->movePhoto($index, $newIndex);
		$album->save();
		dismissAndReload();
		return;
	} else {
?>

<center>
Select the new location of photo #<?=$index+1?>:
<form>
<input type=hidden name="index" value="<?=$index?>">
<select name="newIndex">
<?
for ($i = 1; $i <= $numPhotos; $i++) {
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
<?= $album->getThumbnailTag($index) ?>

<?
	}
} else {
	error("no album / index specified");
}
?>
