<title> Photo Albums </title>
<body bgcolor=#EEEEFF>

<font face=arial>

<center>

<table width=75%><tr><td align=center>
<font size=+3>
The Photo Gallery
</font>
<p>
<font size=+1>
Click on an album below to view it.  
<br>
You may require a <a href=http://www.apple.com/quicktime/download target=new>special
viewer</a> to view some of the movies.
</font>
</td></tr></table>

<p>

<table width=800 border=0>

<?
/* Read the album list */
$albumDB = new AlbumDB();
$albumName = "";
$page = 0;

/* If there are albums in our list, display them in the table */
$numAlbums = $albumDB->numAlbums();
$col = 0;
for ($i = 0; $i < $numAlbums; $i++) {
	$album = $albumDB->getAlbum($i);
	$highlight = $album->getHighlight();
	$tmpAlbumName = $album->fields["name"];
	$albumURL = $app->photoAlbumURL . "/" . $tmpAlbumName;

	if ($col % 3 == 0) {
		echo "<tr>";
	}
?>

<!-- Begin Album Column Block -->
 <td width=33% align=center valign=center>
  <a href=<?=$albumURL?> 
	target=_top 
	onMouseOver='parent.description.location="albums_description.php?name=<?=$tmpAlbumName?>"'>
  <?
	if ($album->numPhotos()) { 
		echo $album->getThumbnailTag($highlight); 
	} else {
		echo "<font size=+3> Empty! </font>";
	}
  ?>
  </a>
  <br>
  <font size=+1>
  <a href=view_album.php?set_albumName=<?= $tmpAlbumName?> target=_top>
  <?= editField($album, "title", $edit) ?>
  </a>
  </font>
  <font size=1>
  <? if (isCorrectPassword($edit)) { ?>
  <center>
  <a href=<?= popup("delete_album.php?set_albumName={$tmpAlbumName}")?>>[delete]</a>
  :
  <a href=<?= popup("move_album.php?set_albumName={$tmpAlbumName}&index=$i")?>>[move]</a>
  :
  <a href=<?= popup("rename_album.php?set_albumName={$tmpAlbumName}&index=$i")?>>[rename]</a>
  <? } ?>
  <br>
  <a href=<?=$albumURL?> target=_top><?=$albumURL?></a>
  <br>
  </font>
 </td>
<!-- End Album Column Block -->

<? // editField($album, "description", $edit) ?>

<?
	if (++$col % 3 == 0) {
		echo "</tr>";
	}
}
?>

</table>

<p>

<? if (isCorrectPassword($edit)) { ?>
<font size=+2 face=arial>
<a href=do_command?cmd=new-album&return=view_album.php target=_top>Create a New Album</a>
<br>
<a href=do_command?cmd=leave-edit&return=albums.php target=_top>Leave edit mode</a>
</font>
<? }  else { ?>
<font size=+2 face=arial>
<a href=<?= popup("edit_mode.php")?>>Enter edit mode</a>
<? } ?>

