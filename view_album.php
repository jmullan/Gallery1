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

$start = ($page - 1) * $perPage + 1;
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

if ($album->fields["textcolor"]) {
	$bodyAttrs .= " text={$album->fields[textcolor]}";
}
if ($album->fields["linkcolor"]) {
	$bodyAttrs .= " link={$album->fields[linkcolor]}";
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
		$i = $start + $rowCount * $cols;
		$j = 1;
		while ($j <= $cols && $i <= $numPhotos) {
			if (!editMode() && $album->isHidden($i)) {
				$i++;
				if ($i >= $numPhotos) {
					break;
				}
			}

			echo("<td width=$width align=center valign=middle>");
			if ($album->isMovie($i)) {
				echo("<a href=" . $album->getPhotoPath($i) . " target=other>" . 
					$album->getThumbnailTag($i) .
					"</a>");
			} else {
				echo("<a href=$albumName/$i>" . 
					$album->getThumbnailTag($i) .
					"</a>");
			}
			echo("</td>");
			$j++; $i++;
		}
		echo("</tr>");
	
		/* Now do the caption row */
		echo("<tr>");
		$i = $start + $rowCount * $cols;
		$j = 1;
		while ($j <= $cols && $i <= $numPhotos) {
			if (!editMode() && $album->isHidden($i)) {
				$i++;
				if ($i >= $numPhotos) {
					break;
				}
			}

			echo("<td width=$width valign=top align=center>");
			echo "<center><font face={$album->fields[font]}>";
			echo(editCaption($album, $i, $edit));
			if (isCorrectPassword($edit)) {
				echo("<font size=2>");
				echo("<a href=");
				echo(popup("delete_photo.php?index=$i"));
				echo("><br>[delete]</a>");
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
			echo("<hr size=1>");
			echo("<hr size=1>");
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
<? } ?>

</table>

<p>
<font face=<?= $album->fields["font"]?> size=+2>
<? if (strcmp($album->fields["returnto"], "no")) { ?>
<a href=albums.php> Return to The Gallery </a>
<? } ?>
</center>
<br>
<hr size=1>
<font size=+0 face=arial>
Admin:
<? if (isCorrectPassword($edit)) { ?>
<a href=<?= popup("add_photos.php?albumName=$albumName") ?>>[Add Photos] </a>
<a href=<?= popup("shuffle_album.php?albumName=$albumName") ?>>[Shuffle Photos] </a>
<a href=<?= popup("resize_photo.php?albumName=$albumName&index=all") ?>>[Resize All] </a>
<a href=<?= popup("edit_appearance.php?albumName=$albumName") ?>>[Edit Appearance] </a>
<a href=do_command?cmd=leave-edit&return=view_album.php>[Leave edit mode]</a>
</font>
<? }  else { ?>
<a href=<?= popup("edit_mode.php")?>>[Enter edit mode]</a>
<? } ?>
</font>

