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
if ($id) {
	$index = $album->getPhotoIndex($id);
	if ($index == -1) {
		$index = 1;
	}
} else {
	$id = $album->getPhotoId($index);
}

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

if ($index > $numPhotos) {
	$index = $numPhotos;
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

if (!strcmp($album->fields["resize_size"], "off")) {
        $mainWidth = 0;
} else {
	$mainWidth = $album->fields["resize_size"];
}
}
#-- if borders are off, just make them the bgcolor ----
if (!strcmp($album->fields["border"], "off")) {
        $bordercolor = $album->fields["bgcolor"];
        $borderwidth = 4;
} else {
        $bordercolor = $album->fields["bordercolor"];
        $borderwidth = $album->fields["border"];
}

?>

<body <?=$bodyAttrs?>>



<center>
<!-- Top Nav Bar -->
<table border=0 width=<?=$mainWidth?>>
<tr>
<td width=<?=$mainWidth/3?> align=left>
<font size=+0 face=<?=$album->fields["font"]?>>
<?
if ($first) {
        echo "<< <a href=../view_album.php>back to index</a>";
} else {
        echo "<a href=".$album->getPhotoId($prev)."><< previous</a>";
}      
?>
</td>
<td width=<?=$mainWidth/3?> align=center>
<font size=+0 face=<?=$album->fields["font"]?>>
<?=$index?> of <?=$numPhotos?>


<?
if (!$album->isMovie($index)) {
	if ($album->isResized($index)) {
		if ($full) {
			echo("(<a href=$id?full=0>show scaled</a>)");
		} else {
			echo("(<a href=$id?full=1>show full size</a>)");
		}
	}      
} 
?>

</td>
<td width=<?=$mainWidth/3?> align=right>
<font size=+0 face=<?=$album->fields["font"]?>>
<?
if ($last) {
        echo "<a href=../view_album.php>back to index</a> >>";
} else {
	echo "<a href=".$album->getPhotoId($next).">next >></a>";
}      
?>
</td>

</tr>
</table>
<table border=0 width=<?=$mainWidth?>>
<!-- image row -->
<tr>
<td colspan=3 align=center>
<font face=<?=$album->fields["font"]?>>
<?


                        echo("<table width=1% border=0 cellspacing=0 cellpadding=0>");
                        echo("<tr bgcolor=$bordercolor>");
                        echo("<td height=$borderwidth width=$borderwidth><img src=../images/pixel_trans.gif></td>");
                        echo("<td height=$borderwidth><img src=../images/pixel_trans.gif></td>");
                        echo("<td height=$borderwidth width=$borderwidth><img src=../images/pixel_trans.gif></td>");
                        echo("</tr>");
                        echo("<tr>");
			echo("<td bgcolor=$bordercolor width=$borderwidth>");
			for ($k=0; $k<$borderwidth; $k++) {
				echo("<img src=../images/pixel_trans.gif>");
			}
                        echo("</td>");
                        echo("<td>");

if (!$album->isMovie($index)) {
	if ($album->isResized($index)) { 
		if ($full) { 
			echo "<a href=$id?full=0>";
	 	} else {
			echo "<a href=$id?full=1>";
		}
		$openAnchor = 1;
	}
}
?>
<?=$album->getPhotoTag($index, $full)?>
<?
if ($openAnchor) {
	echo "</a>";
 	$openAnchor = 0;
}

			echo("</td>");
			echo("<td bgcolor=$bordercolor width=$borderwidth>");
			for ($k=0; $k<$borderwidth; $k++) {
				echo("<img src=../images/pixel_trans.gif>");
			}
                        echo("</td>");
			echo("</tr>");
                        echo("<tr bgcolor=$bordercolor>");
                        echo("<td height=$borderwidth width=$borderwidth><img src=../images/pixel_trans.gif></td>");
                        echo("<td height=$borderwidth><img src=../images/pixel_trans.gif></td>");
                        echo("<td height=$borderwidth width=$borderwidth><img src=../images/pixel_trans.gif></td>");
                        echo("</tr>");
                        echo("</table>");

?>

</td>
</tr>
<tr>
<td colspan=3 align=center>
<?= editCaption($album, $index, $edit) ?>
</td>
</tr>
</table>

<!-- bottom nav bar -->
<table border=0 width=<?=$mainWidth?>>
<? 
if (!$album->isMovie($index) && isCorrectPassword($edit)) {
?>
<tr>
<td colspan=3 align=left>
Admin: <a href=<?= popup("../resize_photo.php?index=$index") ?>>[resize photo]</a>
<a href=<?= popup("../delete_photo.php?index=$index") ?>>[delete photo]</a>
<br>
</td>
</tr>
<? 
} 
?>

<tr>
<td colspan=3 align=left>
<font size=+0 face=<?=$album->fields["font"]?>>
<< <a href=../view_album.php>back to <b><?= $album->fields["title"] ?></b> index</a>
</td>
</tr>

<tr>
<td colspan=3 align=left>
<font size=+0 face=<?=$album->fields["font"]?>>
<< <a href=../albums.php>back to <b>The Gallery</b></a>
</td>
</tr>

</table>
