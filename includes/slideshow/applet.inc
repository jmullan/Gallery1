<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2004 Bharat Mediratta
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
 *
 * $Id$
 */
?>

<?php
function slideshow_initialize() {
	global $albumName, $gallery, $title;
	
	if (empty($albumName)) {
        $title = sprintf(_("Slide Show for Gallery :: %s"),
            $gallery->app->galleryTitle);
	} else {
        $title = sprintf(_("Slide Show for album :: %s"), $gallery->album->fields["title"]);
	}
}

function slideshow_body() {}
?>

<?php
function slideshow_controlPanel() {
	global $pixelImage;
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="modnavboxmid">
  <tr>
    <td colspan="3"><?php echo $pixelImage ?></td>
  </tr>
  <tr>
    <td height="25" width="1"><?php echo $pixelImage ?></td>
    <td align="left" valign="middle">
    </td>
    <td width="1"><?php echo $pixelImage ?></td>
  </tr>
  <tr>
    <td colspan="3"><?php echo $pixelImage ?></td>
  </tr>
</table>
<?php
}
?>

<?php
function slideshow_image() {
	global $photo_count, $gallery, $album;
?>
<div align="center">

<?php echo _("<p>If you don't have the Java Plugin 1.3 or later, or you don't want to wait for the applet to
download, you can use one of the non-fullscreen versions.") ?></p>

<?php
	drawApplet(
		300,
		430,
		"com.gallery.GalleryRemote.GRAppletSlideshow",
		"java/GalleryRemoteAppletMini.jar,java/GalleryRemoteHTTPClient.jar,java/applet_img.jar",
		$gallery->album->fields["name"],
		array(
			"slideshowLowRez" => "true",
			"slideshowRecursive" => ($album->fields["slideshow_recursive"] == "yes")?"true":"false",
			"slideshowRandom" => ($album->fields["slideshow_type"] == "random")?"true":"false",
			"slideshowMaxPictures" => (int)$album->fields["slideshow_length"]
		),
		array("toSysOut" => "true"),
		_("Your browser doesn't support applets; you should use one of the other slideshow methods.")
	);
?>

</div>
<?php
}
?>