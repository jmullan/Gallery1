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
<html>
<head>
  <title>Album Upgrade in Progress</title>
  <?= getStyleSheetLink() ?>
</head>
<body>
<center>
<span class="title">
Album Upgrade in Progress
</span>
</center>
<p>

The album you're viewing was created with an older version of Gallery
and is out of date.  This is not a problem!  Please be patient while
we upgrade it to work with the current version of Gallery.  This may
take some time if the album is very large, but we'll try to keep you 
informed.  None of your photos will be harmed in any way by this 
process.
<p>
Rest assured, that if this process takes a long time now, it's going
to make your Gallery run more efficiently in the future.
<br>
<br>
<b>Progress:</b>
<ul>
<?
if ($gallery->album->integrityCheck()) {
	$gallery->album->save(0);
}
?>
</ul>
Upgrade complete.  To view the gallery, click the <b>Done</b> button below
or simply reload the page.
</span>

<form>
<input type=submit value="Done" onClick='document.location.reload()'>
</form>
</body>
</html>
