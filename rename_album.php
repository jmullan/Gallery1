<? require('style.php'); ?>

<center>

<?
/* Read the album list */
$albumDB = new AlbumDB();

if ($newName) {
	$newName = str_replace("'", "", $newName);
	$newName = strtr($newName, "\\/*?\"<>|& ", "----------");
	$newName = ereg_replace("\-+", "-", $newName);
	$newName = ereg_replace("\-+$", "", $newName);
	$albumDB->renameAlbum($oldName, $newName);
	$albumDB->save();
	dismissAndReload();
	return;
} else {
}

?>

What do you want to name this album?  The name cannot contain any of
the following characters:  <br><center><b>\ / * ? " ' &amp; &lt; &gt; | </b>or<b> spaces</b><br></center>
Those characters will be ignored in your new album name.

<br>
<form>
<input type=text name="newName" value=<?=$albumName?>>
<input type=hidden name="oldName" value=<?=$albumName?>>
<p>
<input type=submit value="Rename">
<input type=submit name="submit" value="Cancel" onclick='parent.close()'>
</form>
