<?
$numPhotos = $album->numPhotos(editMode());
$next = $index+1;
if ($next >= $numPhotos) $next = 1;
$prev = $index-1;
if ($prev <= 0) $prev = $numPhotos - 1;

/*
 * We might be prev/next navigating using this page
 *  so recalculate the 'page' variable
 */
$perPage = $rows * $cols;
$page = ceil(($index + 1) / ($rows * $cols));
?>

<center>
<table border=0 width=1%>
<tr>
<td width=33% align=left>
<font size=+2 face=<?=$album->fields["font"]?>>
<a href=view_photo?index=<?=$prev?>>Previous Photo</a>
</td>
<td width=33% align=center>
<font size=+1 face=<?=$album->fields["font"]?>>
<?=$index+1?> of <?=$numPhotos?>
</td>
<td width=33% align=right>
<font size=+2 face=<?=$album->fields["font"]?>>
<a href=view_photo?index=<?=$next?>>Next Photo</a>
</td>
</tr>

<tr>
<td colspan=3 align=center>
<font face=<?=$album->fields["font"]?>>
<?=$album->getPhotoTag($index, $full)?>
</td>
</tr>
<tr>
<td colspan=3 align=center>
<?= editCaption($album, $index, $edit) ?>
</td>
</tr>

<tr>
<td align=left>
<font size=+2 face=<?=$album->fields["font"]?>>
<a href=view_photo?index=<?=$prev?>>Previous Photo</a>
</td>
<td align=center>
<font size=+2 face=<?=$album->fields["font"]?>>
<?
if (!$album->isMovie($index)) {
	if ($album->isResized($index)) { 
		if ($full) { 
?>
<a href=view_photo?index=<?=$index?>&full=0>Scaled Version</a>
<?	 	} else { ?>
<a href=view_photo?index=<?=$index?>&full=1>Full Version</a>
<?
	}
}
?>
&nbsp;
<? } ?>
</td>
<td align=right>
<font size=+2 face=<?=$album->fields["font"]?>>
<a href=view_photo?index=<?=$next?>>Next Photo</a>
</td>
</tr>

<tr>
<td colspan=3 align=center>
<? if (!$album->isMovie($index) && isCorrectPassword($edit)) { ?>
<a href=<?= popup("resize_photo.php?index=$index") ?>>[resize photo]</a>
<br>
<? } ?>
<font size=+3 face=<?=$album->fields["font"]?>>
<a href=view_album.php> Return to <b><?= $album->fields["title"] ?></b> </a>
</td>
</tr>

</table>
