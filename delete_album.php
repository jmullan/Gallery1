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
if ($confirm) {
	$album->delete();
	dismissAndReload();
	exit;
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

