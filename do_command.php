<?
if (!strcmp($cmd, "remake-thumbnail")) {
	if ($albumName && isset($index)) {
		$album->makeThumbnail($index);
		$album->save();
		dismissAndReload();
	}
} else if (!strcmp($cmd, "leave-edit")) {
	$edit = "";
	header("Location: $return");	
} else if (!strcmp($cmd, "hide")) {
	$album->hidePhoto($index);
	$album->save();
	header("Location: $return");	
} else if (!strcmp($cmd, "show")) {
	$album->unhidePhoto($index);
	$album->save();
	header("Location: $return");	
} else if (!strcmp($cmd, "new-album")) {
	$albumDB = new AlbumDB();
	$albumName = $albumDB->newAlbumName();
	$album = new Album();
	$album->fields["name"] = $albumName;
	$album->save();

	header("Location: $return?set_albumName=$albumName");
}
?>
