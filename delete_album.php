<?
if ($confirm) {
	$album->delete();
	dismissAndReload();
	return;
}

require('style.php');

if ($album) {
?>

<center>
Do you really want to delete this album?
<br>
<b><?= $album->fields["title"] ?></b>
<p>
<form action=delete_album.php>
<input type=submit name=confirm value="Delete">
<input type=submit value="Cancel" onclick='parent.close()'>
</form>
<p>
<?
	if ($album->numPhotos(1)) {
		echo $album->getThumbnailTag($album->getHighlight());
	}
} else {
	error("no album specified");
}
?>

