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
if (file_exists("setup") && is_readable("setup")) {
	require("style.php");
?>
	<center>
	<font size=+2 color=red> Uh oh! </font>
	<p>
	<table width=80%><tr><td>
	<font size=+1>
	Gallery is still in configuration mode which means it's
	anybody out there can mess with it.  
	For safety's sake we don't let you run the app in this mode.
	You need to put it in secure mode before you can use it.  Put
	it in secure mode by doing this:

	<p><center>
	<table><tr><td>
		<code>
		% cd <?=dirname(getenv("SCRIPT_FILENAME"))?>
		<br>
		% sh ./secure.sh
	</td></tr></table>
	<p>
	When you've done this, just reload this page and all should
	be well.
	</table>
<?
	exit;
}

/* Load defaults */
require('version.php');
require('config.php');
require('class_Album.php');
require('class_Image.php');
require('class_AlbumItem.php');
require('class_AlbumDB.php');
require('util.php');
require('session.php');

if ($app->config_version != $gallery->config_version) {
	require("style.php");
?>
	<center>
	<font size=+2 color=red> Uh oh! </font>
	<p>
	<center>
	<table width=80%><tr><td>
	<font size=+1>
	Your Gallery configuration was created using the config wizard
	from an older version of Gallery.  It is out of date.  Please
	re-run the configuration wizard!  In a shell do this:
	<p><center>
	<table><tr><td>
		<code>
		% cd <?=dirname(getenv("SCRIPT_FILENAME"))?>
		<br>
		% sh ./configure.sh
	</td></tr></table>
	<p>
	Then launch the <a href=<?=$app->photoAlbumURL?>/setup/>configuration wizard</a>.
	</table>
<?
	exit;
}

/* Load the correct album object */
$album = new Album;
if ($albumName) {
	$album->load($albumName);
	if ($album->integrityCheck()) {
		$album->save();
	}
}
?>
