<? 
if (!$album->isLoaded()) {
	header("Location: albums.php");
	exit;
}

$numPhotos = $album->numPhotos(editMode());
$perPage = $rows * $cols;
$maxPages = ceil($numPhotos / $perPage);

if ($page > $maxPages) {
	$page = $maxPages;
}

$start = ($page - 1) * $perPage;
$end = $start + $perPage;

$nextPage = $page + 1;
if ($nextPage > $maxPages) {
	$nextPage = 1;
}

$previousPage = $page - 1;
if ($previousPage == 0) {
	$previousPage = $maxPages;
}

if ($album->fields["background"]) {
	$bodyAttrs .= "background={$album->fields[background]}";
} elseif ($album->fields["bgcolor"]) {
	$bodyAttrs .= "bgcolor={$album->fields[bgcolor]}";
}

if (!strcmp($album->fields["border"], "off")) {
	$border = 0;
} else {
	$border = $album->fields["border"];
}

$width = floor(100 / $cols) . "%";
?>

<body <?=$bodyAttrs?>>
<font face=<?=$album->fields["font"]?>>

<center>
<font size=+3> <?= editField($album, "title", $edit)?> </font>
<br>
There are <font size=-1> <?= $numPhotos ?> photos in this album
<?
if (editMode()) {
	$hidden = $album->numHidden();
	$verb = "are";
	if ($hidden == 1) {
		$verb = "is";
	}
		
	if ($hidden) {
		echo "($hidden $verb hidden)";
	}
} 
?>

<br>
<font size=1>Click on a photo to enlarge it</font>

<table width=80% border=<?=$border?> bordercolor=<?=$album->fields["bordercolor"]?> cellspacing=2 cellpadding=1>

<? if ($maxPages > 1) { ?> 
<tr><td colspan=<?=$cols?>>
<table width=100%>
<tr>
<td align=left width=33%><a href=view_album.php?set_page=<?=$previousPage?>><font size=+2 face=<?=$album->fields["font"]?>>Previous Page</a></td>
<td align=center width=33%><font=<?=$album->fields["font"]?>><font face=<?=$album->fields["font"]?>>Page <?=$page?> of <?=$maxPages?></td>
<td align=right width=33%><a href=view_album.php?set_page=<?=$nextPage?>><font size=+2 face=<?=$album->fields["font"]?>>Next Page</a></td>
</tr>
</table>
</td></tr>
<? } ?> 

<?
$numPhotos = $album->numPhotos(1);
if ($numPhotos) {

	$rowCount = 0;
	while ($rowCount < $rows) {
		/* Do the picture row */
		echo("<tr>");
		$i = $start + $rowCount * $rows;
		$j = 0;
		while ($j < $cols && $i < $numPhotos) {
			if (!editMode() && $album->isHidden($i)) {
				$i++;
				if ($i >= $numPhotos) {
					break;
				}
			}

			echo("<!-- $i / $j / $numPhotos -->");
			echo("<td width=$width valign=top align=center>");
			if ($album->isMovie($i)) {
				echo("<a href=" . $album->getPhotoPath($i) . " target=other>" . 
					$album->getThumbnailTag($i) .
					"</a>");
			} else {
				echo("<a href=view_photo.php?index=$i>" . 
					$album->getThumbnailTag($i) .
					"</a>");
			}
			echo("</td>");
			$j++; $i++;
		}
		echo("</tr>");
	
		/* Now do the caption row */
		echo("<tr>");
		$i = $start + $rowCount * $rows;
		$j = 0;
		while ($j < $cols && $i < $numPhotos) {
			if (!editMode() && $album->isHidden($i)) {
				$i++;
				if ($i >= $numPhotos) {
					break;
				}
			}

			echo("<td width=$width valign=top align=center>");
			echo "<center><font face={$album->fields[font]}>";
			echo("<a href=view_photo.php?index=$i>" . 
				editCaption($album, $i, $edit) .
			      "</a>");
			if (isCorrectPassword($edit)) {
				echo("<hr><font size=2>");
				echo("<a href=");
				echo(popup("delete_photo.php?index=$i"));
				echo(">[delete]</a>");
				if (!$album->isMovie($i)) {
					echo(" <a href=");
					echo(popup("rotate_photo.php?index=$i"));
					echo(">[rotate]</a>");
					/*
					 * This will remake the thumbnail, which is rarely needed (mostly
					 * during development) so I've turned it off for now.
					 *
					echo(" <a href=");
					echo(popup("do_command.php?cmd=remake-thumbnail&index=$i"));
					echo(">[thumbnail]</a>");
					 */
				}
				echo(" <a href=");
				echo(popup("move_photo.php?index=$i"));
				echo(">[move]</a>");
				echo(" <a href=");
				echo(popup("highlight_photo.php?index=$i"));
				echo(">[highlight]</a>");
				if ($album->isHidden($i)) {
					echo("<a href=do_command?cmd=show&index=$i&return=view_album.php>[show]</a>");
				} else {
					echo("<a href=do_command?cmd=hide&index=$i&return=view_album.php>[hide]</a>");
				}
			}
			echo("</td>");
			$j++; $i++;
		}
		echo "</tr>";
		$rowCount++;
	}
} else {
?>
	<tr>
	<td colspan=$rows align=center>
	<font size=+2> There are no photos in this album. </font> 
	</td>
	</tr>
<?
}
?>

<? if ($maxPages > 1) { ?> 
<tr><td colspan=<?=$cols?>>
<table width=100%>
<tr>
<td align=left width=33%><a href=view_album.php?set_page=<?=$previousPage?>><font size=+2 face=<?=$album->fields["font"]?>>Previous Page</a></td>
<td align=center width=33%><font=<?=$album->fields["font"]?>><font face=<?=$album->fields["font"]?>>Page <?=$page?> of <?=$maxPages?></td>
<td align=right width=33%><a href=view_album.php?set_page=<?=$nextPage?>><font size=+2 face=<?=$album->fields["font"]?>>Next Page</a></td>
</tr>
</table>
</td></tr>
<? } ?> 

<? if (isCorrectPassword($edit)) { ?> 
<tr><td colspan=<?=$cols?>>
<table width=100% bordercolor=black cellpadding=0 cellspacing=0 border=1><tr><td>
<table width=100% bgcolor=#9999CC>
<tr>
<td width=25% align=center> <a href=<?= popup("add_photos.php?albumName=$albumName") ?>> Add Photos </a> </td>
<td width=25% align=center> <a href=<?= popup("shuffle_album.php?albumName=$albumName") ?>> Shuffle Photos </a> </td>
<td width=25% align=center> <a href=<?= popup("resize_photo.php?albumName=$albumName&index=all") ?>> Resize All </a> </td>
<td width=25% align=center> <a href=<?= popup("edit_appearance.php?albumName=$albumName") ?>> Edit Appearance </a> </td>
</tr>
</table>
</td></tr></table>
</td></tr>
<? } ?>

</table>

<p>
<font face=<?= $album->fields["font"]?> size=+2>
<? if (strcmp($album->fields["returnto"], "no")) { ?>
<a href=albums.php> Return to the Album List </a>
<? } ?>
<br>
<? if (isCorrectPassword($edit)) { ?>
<a href=do_command?cmd=leave-edit&return=view_album.php>Leave edit mode</a>
</font>
<? }  else { ?>
<font size=+2 face=arial>
<a href=<?= popup("edit_mode.php")?>>Enter edit mode</a>
<? } ?>

</center>
