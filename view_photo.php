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

if ($full) {
	$fullTag = "?full=1";
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
}

#-- if borders are off, just make them the bgcolor ----
if (!strcmp($album->fields["border"], "off")) {
        $bordercolor = $album->fields["bgcolor"];
        $borderwidth = 4;
} else {
        $bordercolor = $album->fields["bordercolor"];
        $borderwidth = $album->fields["border"];
}

if (!strcmp($album->fields["resize_size"], "off")) {
        $mainWidth = 0;
} else {
	$mainWidth = $album->fields["resize_size"] + ($borderwidth*2);


}

#-- breadcrumb text ---
if (strcmp($album->fields["returnto"], "no")) {
	$breadtext[0] = "Gallery: <a href=../albums.php>The Gallery</a>";
	$breadtext[1] = "Album: <a href=../view_album.php>".$album->fields["title"]."</a>";
} else {
	$breadtext[0] = "Album: <a href=../view_album.php>".$album->fields["title"]."</a>";
}
?>

<body <?=$bodyAttrs?>>



<center>
<!-- Top Nav Bar -->
<table border=0 width=<?=$mainWidth?> cellpadding=0 cellspacing=0>
<tr>
<td>
<?
$breadcrumb["text"] = $breadtext;
$breadcrumb["bordercolor"] = $bordercolor;
$breadcrumb["top"] = true;

include("layout/breadcrumb.inc");
?>
</td>
</tr>
<tr>
<td>
<?
$navigator["page"] = $index;
$navigator["pageVar"] = "index";
$navigator["maxPages"] = $numPhotos;
$navigator["fullWidth"] = "100";
$navigator["widthUnits"] = "%";
$navigator["url"] = ".";
#if ($full) {
#	$navigator["url"] .= "?full=0";
#} else {
#	$navigator["url"] .= "?full=1";
#}
$navigator["spread"] = 5;
$navigator["bordercolor"] = $bordercolor;
$navigator["noIndivPages"] = true; 

include("layout/navphoto.inc");
?>
<br>
</td>
</tr>


</table>
<table border=0 width=<?=$mainWidth?> cellpadding=0 cellspacing=0>
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
<br>
<br>
</td>
</tr>
</table>
<table border=0 width=<?=$mainWidth?> cellpadding=0 cellspacing=0>
<tr>
<td>
<?
include("layout/navphoto.inc");
$breadcrumb["top"] = false;
include("layout/breadcrumb.inc");
?>
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
</table>
