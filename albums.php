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
<? if (file_exists("config.php")) { ?>
<title> The Photo Gallery </title>
<frameset rows="*,10" border=0 frameborder="no">
 <frame src="albums_content.php" name=content frameborder=1>
 <frame src="albums_description.php" name=description frameborder=1>
</frameset>
<?
} else {
	if (file_exists("setup") && is_readable("setup")) {
		header("Location: setup");
		return;
	}

	require("style.php");
?>
<center>
<font size=+2>Gallery has not been configured!</font>
<p>
Your installation of Gallery has not yet been configured.
To configure it, type:
	<table><tr><td>
		<code>
		% cd <?=dirname(getenv("SCRIPT_FILENAME"))?>
		<br>
		% sh ./configure.sh
	</td></tr></table>
<p>
And then go <a href=setup>here</a>
<?
} 
?>
