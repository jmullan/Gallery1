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

<!-- gallery.header begin -->
<?
/* load the gallery header layout */ 
$header = "layout/gallery.header";
if (file_exists($header)) {
	include($header);
} else {
	echo("<body><p>no gallery.header file found.<p>");
}
?>
<!-- gallery.header end -->

<!-- album table begin -->
<table width=90% border=0 cellspacing=7>

<?
/* Read the album list */
$albumDB = new AlbumDB();
$albumName = "";
$page = 0;

/* If there are albums in our list, display them in the table */
$numAlbums = $albumDB->numAlbums();
for ($i = 0; $i < $numAlbums; $i++) {
        $album = $albumDB->getAlbum($i);
        $tmpAlbumName = $album->fields["name"];
        $albumURL = $app->photoAlbumURL . "/" . $tmpAlbumName;

?>     

  <!-- Begin Album Column Block -->
  <tr>
  <!-- Begin Image Cell -->
  <td width=<?=$app->highlight_size?> align=center valign=middle>
  <a href=<?=$albumURL?>>
  <?   
        if ($album->numPhotos()) {
                echo $album->getHighlightTag();
        } else {
                echo "<font size=+3> Empty! </font>";
        }
  ?>   
  </a>
  </td>
  <!-- End Image Cell -->
  <!-- Begin Text Cell -->
  <td align=left valign=top>
  <hr size=1>
  <font size=+1 face=arial>
  <a href=<?=$albumURL?>>
  <?= editField($album, "title", $edit) ?></a>
  </font>
  <br>
  <font size=+0 face=arial>
  <?= editField($album, "description", $edit) ?>
  </font>
  <br>
  <font size=1 face=arial>
  <? if (isCorrectPassword($edit)) { ?>
  <a href=<?= popup("delete_album.php?set_albumName={$tmpAlbumName}")?>>[delete album]</a>
  :
  <a href=<?= popup("move_album.php?set_albumName={$tmpAlbumName}&index=$i")?>>[move album]</a>
  :
  <a href=<?= popup("rename_album.php?set_albumName={$tmpAlbumName}&index=$i")?>>[rename album]</a>
  <br>
  url: <a href=<?=$albumURL?>><?=$albumURL?></a>
   <? if (preg_match("/album\d$/", $albumURL)) { ?>
 	<br>
         <font size=+1 face=arial color=red>
          Hey!
          <a href=<?= popup("rename_album.php?set_albumName={$tmpAlbumName}&index=$i")?>>Rename</a> 
          this album so that the URL is not so generic!
         </font>
   <? } ?>
  <? } ?>
  <br>
  </font>
  </td>
  </tr>
  <!-- End Text Cell -->
  <!-- End Album Column Block -->

<?
}      
?>
</table>
<!-- album table end -->
<p>
<!-- admin section begin -->
<hr size=1>
Admin:
<? if (isCorrectPassword($edit)) { ?>
<font size=+0 face=arial>
<a href=do_command.php?cmd=new-album&return=view_album.php>[Create a New Album]</a>
&nbsp;
<a href=do_command.php?cmd=leave-edit&return=albums.php>[Leave edit mode]</a>
</font>
<? }  else { ?>
<font size=+0>
<a href=<?= popup("edit_mode.php")?>>[Enter edit mode]</a>
</font>
<? } ?>

<?
} 

else {
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

</font>
<!-- admin section end -->

<!-- gallery.footer begin -->
<?
/* load the gallery footer layout */    
$footer = "layout/gallery.footer";
if (file_exists($footer)) {
	include($footer);
} else {
	echo("<p>no gallery.footer file found<br></body>");
}
?>
<!-- gallery.footer end -->
