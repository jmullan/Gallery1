<? require('style.php'); ?>

<?
$all = !strcmp($index, "all");
if ($albumName && isset($index)) {
	if ($resize) {
		if (!strcmp($index, "all")) {
			$album->resizeAllPhotos($resize);
		} else {
			$album->resizePhoto($index, $resize);
		}
		$album->save();
		dismissAndReload();
		return;
	} else {
?>

<center>
<font size=+1>Resizing photos</a>
<br>
This will resize your photos so that the longest side of the 
photo is equal to the target size below.  

<p>

What is the target size for <?= $all ? "all the photos in this album" : "this photo" ?>?
<br>
<form>
<input type=hidden name=index value=<?=$index?>>
<input type=submit name=resize value="Original Size">
<input type=submit name=resize value="800">
<input type=submit name=resize value="700">
<input type=submit name=resize value="600">
<input type=submit value="Cancel" onclick='parent.close()'>
</form>

<p>
<?
if (!$all) {
	echo $album->getThumbnailTag($index);
} 
?>

<?
	}
} else {
	error("no album / index specified");
}
?>







