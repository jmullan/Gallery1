<?
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000 Bharat Mediratta
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */
?>
<?
$numPhotos = $album->numPhotos(editMode());
$next = $index+1;
if ($next > $numPhotos) {
	//$next = 1;
        $last = 1;
}
$prev = $index-1;
if ($prev <= 0) {
	//$prev = $numPhotos;
        $first = 1;
}

/*
 * We might be prev/next navigating using this page
 *  so recalculate the 'page' variable
 */
$perPage = $rows * $cols;
$page = ceil($index / ($rows * $cols));

if ($album->fields["background"]) {
        $bodyAttrs .= "background={$album->fields[background]}";
} elseif ($album->fields["bgcolor"]) {
        $bodyAttrs .= "bgcolor={$album->fields[bgcolor]}";
}

if ($album->fields["textcolor"]) {
        $bodyAttrs .= " text={$album->fields[textcolor]}";
}
if ($album->fields["linkcolor"]) {
        $bodyAttrs .= " link={$album->fields[linkcolor]}";
}

?>

<body <?=$bodyAttrs?>>



<center>
<table border=0 width=1%>
<!-- Top Nav Bar -->
<tr>
<td align=left>
<font size=+1 face=<?=$album->fields["font"]?>>
<?
if ($first) {
	//echo "<a href=../view_album.php></a>";
} else {
	echo "< <a href=".$prev.">Previous</a> | ";
}
if ($last) {
	echo "<a href=../view_album.php>Done</a>";
} else {
	echo "<a href=".$next.">Next</a> >";
}
?>
</td>
<td></td>
<td align=right>
<font size=+1 face=<?=$album->fields["font"]?>>
<?=$index?> of <?=$numPhotos?>
</td>
</tr>

<!-- image row --><tr>
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

<!-- bottom nav bar -->
<tr>
<td align=left>
<font size=+1 face=<?=$album->fields["font"]?>>
<?
if ($first) {
	//echo "<a href=../view_album.php></a>";
} else {
	echo "< <a href=".$prev.">Previous</a> | ";
}
if ($last) {
	echo "<a href=../view_album.php>Done</a>";
} else {
	echo "<a href=".$next.">Next</a> >";
}
?>
</td>
<td></td>
<td align=right>
<font size=+0 face=<?=$album->fields["font"]?>>
<?
if (!$album->isMovie($index)) {
	if ($album->isResized($index)) { 
		if ($full) { 
?>
<a href=<?=$index?>?full=0>Show Scaled</a>
<?	 	} else { ?>
<a href=<?=$index?>?full=1>Show Full Size</a>
<?
	}
}
?>
<? } ?>
</td>
</tr>

<tr>
<td colspan=3 align=left>
<hr size=1>
<? if (!$album->isMovie($index) && isCorrectPassword($edit)) { ?>
Admin: <a href=<?= popup("../resize_photo.php?index=$index") ?>>[resize photo]</a>
<br>
<? } ?>
<font size=+0 face=<?=$album->fields["font"]?>>
^ <a href=../view_album.php> Return to <b><?= $album->fields["title"] ?></b> </a>
</td>
</tr>

</table>
