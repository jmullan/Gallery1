<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2006 Bharat Mediratta
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
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
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

function slideshow_controlPanel() {
	global $pixelImage;
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="modnavboxmid">
  <tr>
    <td colspan="3"><?php echo $pixelImage ?></td>
  </tr>
  <tr>
    <td height="25" width="1"><?php echo $pixelImage ?></td>
    <td align="center" valign="middle"><span class="admin">
<?php echo _("If you don't have the Java Plugin 1.4 or later, or you don't want to wait for the applet to download, you can use one of the non-fullscreen versions.") ?>
    </span></td>
    <td width="1"><?php echo $pixelImage ?></td>
  </tr>
  <tr>
    <td colspan="3"><?php echo $pixelImage ?></td>
  </tr>
</table>
<?php
}

function slideshow_image() {
	global $photo_count, $gallery, $album, $albumName, $recursive, $random, $number;
?>
<div align="center">

<?php
	drawApplet(
		300,
		430,
		"com.gallery.GalleryRemote.GRAppletSlideshow",
                $gallery->app->photoAlbumURL . "/java/GalleryRemoteAppletMini.jar," . 
                        $gallery->app->photoAlbumURL . "/java/GalleryRemoteHTTPClient.jar," . 
                        $gallery->app->photoAlbumURL . "/java/applet_img.jar",
		isset($gallery->album) ? $gallery->album->fields["name"] : "",
		array(
			"slideshowLowRez" => "true",
			"slideshowRecursive" => $recursive?"true":"false",
			"slideshowRandom" => $random?"true":"false",
			"slideshowMaxPictures" => $number
		),
		array("toSysOut" => "true"),
		dirname(__FILE__) . "/applet.cfg",
		_("Your browser doesn't support applets; you should use one of the other slideshow methods.")
	);
?>

</div>
<?php
}
?>