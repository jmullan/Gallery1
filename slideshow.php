<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2003 Bharat Mediratta
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
// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
                !empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
                !empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
	print _("Security violation") ."\n";
        exit;
}
?>
<?php if (!isset($GALLERY_BASEDIR)) {
    $GALLERY_BASEDIR = './';
}
require($GALLERY_BASEDIR . 'init.php'); ?>
<?php
// Hack check

if ($gallery->session->albumName == "" &&
	       $gallery->app->gallery_slideshow_type == "off") {
        header("Location: albums.php");
        return;
}
$albumName=$gallery->session->albumName;
?>

<?php if (!$GALLERY_EMBEDDED_INSIDE) { ?>
<html>
<head>
  <title><?php echo _("Slideshow") ?></title>
  <?php echo getStyleSheetLink() ?>
  <style type="text/css">
<?php
// the link colors have to be done here to override the style sheet
if ($albumName) {
       	if ($gallery->album->fields["linkcolor"]) {
?>
    A:link, A:visited, A:active
      { color: <?php echo $gallery->album->fields[linkcolor] ?>; }
    A:hover
      { color: #ff6600; }
<?php
       	}
       	if ($gallery->album->fields["bgcolor"]) {
	       	echo "BODY { background-color:".$gallery->album->fields[bgcolor]."; }";
       	}
       	if (isset($gallery->album->fields["background"]) && $gallery->album->fields["background"]) {
	       	echo "BODY { background-image:url(".$gallery->album->fields['background']."); } ";
       	}
       	if ($gallery->album->fields["textcolor"]) {
	       	echo "BODY, TD {color:".$gallery->album->fields[textcolor]."; }";
	       	echo ".head {color:".$gallery->album->fields[textcolor]."; }";
	       	echo ".headbox {background-color:".$gallery->album->fields[bgcolor]."; }";
       	}
}
?>
  </style>

</head>

<body dir="<?php echo $gallery->direction ?>">
<?php } ?>
<?php includeHtmlWrap("slideshow.header"); ?>

<?php
$imageDir = $gallery->app->photoAlbumURL."/images";
$pixelImage = "<img src=\"" . getImagePath('pixel_trans.gif') . "\" width=\"1\" height=\"1\" alt=\"\">";

?>
<form name="TopForm">
<?php

#-- breadcrumb text ---
$breadCount = 0;
$breadtext=array();
if ($albumName) {
if (!$gallery->session->offline
	|| isset($gallery->session->offlineAlbums[$gallery->session->albumName])) {
	$breadtext[$breadCount] = _("Album") .": <a class=\"bread\" href=\"" . makeAlbumUrl($gallery->session->albumName) .
      	"\">" . $gallery->album->fields['title'] . "</a>";
	$breadCount++;
}
$pAlbum = $gallery->album;
do {
  if (!strcmp($pAlbum->fields["returnto"], "no")) {
    break;
  }
  $pAlbumName = $pAlbum->fields['parentAlbumName'];
  if ($pAlbumName && (!$gallery->session->offline
  	|| isset($gallery->session->offlineAlbums[$pAlbumName]))) {
    $pAlbum = new Album();
    $pAlbum->load($pAlbumName);
    $breadtext[$breadCount] = _("Album") .": <a class=\"bread\" href=\"" . makeAlbumUrl($pAlbumName) .
      "\">" . $pAlbum->fields['title'] . "</a>";
  } elseif (!$gallery->session->offline || isset($gallery->session->offlineAlbums["albums.php"])) {
    //-- we're at the top! ---
    $breadtext[$breadCount] = _("Gallery") .": <a class=\"bread\" href=\"" . makeGalleryUrl("albums.php") .
      "\">" . $gallery->app->galleryTitle . "</a>";
  } else {
	  break;
  }
  $breadCount++;
} while ($pAlbumName);

//-- we built the array backwards, so reverse it now ---
for ($i = count($breadtext) - 1; $i >= 0; $i--) {
    $breadcrumb["text"][] = $breadtext[$i];
}
}
else {
       	if (!$gallery->session->offline || isset($gallery->session->offlineAlbums["albums.php"])) {
	       	//-- we're at the top! ---
	       	$breadcrumb["text"][$breadCount] = _("Gallery") .": <a class=\"bread\" href=\"" . makeGalleryUrl("albums.php") .
		      	"\">" . $gallery->app->galleryTitle . "</a>";
		$breadCount++;
       	}
}

$breadcrumb["bordercolor"] = $borderColor;
$breadcrumb["top"] = true;

$adminbox["commands"] = "<span class=\"admin\">";

// Low-tech version is just for online. It does not work offline (because the
// URLs are generated dynamically by JavaScript and were therfore not
// downloaded by Wget).
if ( !$gallery->session->offline && isset($gallery->session->albumName)) {
    $adminbox["commands"] .= "&nbsp;<a class=\"admin\" href=\"" . makeGalleryUrl("slideshow_low.php",
        array("set_albumName" => $gallery->session->albumName)) .
	"\">[" ._("not working for you? try the low-tech") ."]</a>";
}
$adminbox["commands"] .= "</span>";
$adminbox["text"] = _("Slide Show");
$adminbox["bordercolor"] = $borderColor;
$adminbox["top"] = true;
includeLayout('navtablebegin.inc');
includeLayout('adminbox.inc');
includeLayout('navtablemiddle.inc');
includeLayout('breadcrumb.inc');
includeLayout('navtablemiddle.inc');

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
    includeLayout('navtableend.inc');
?>

<br>
<div align="center">

<?php // hack
$photo_count = 1; ?>

<?php echo _("<p>If you don't have the Java Plugin 1.3 or later, or you don't want to wait for the applet to
download, you can use the") . " <a class=\"admin\" href=\"" . makeGalleryUrl("slideshow_high.php",
        array("set_albumName" => $gallery->session->albumName)) .
	"\">[" ._("non-fullscreen version") ."]</a>" ?>.</p>
<?php if ($photo_count > 0) { ?>
<?php $cookieInfo = session_get_cookie_params(); ?>

<object
		classid="clsid:8AD9C840-044E-11D1-B3E9-00805F499D93"
		codebase="http://java.sun.com/products/plugin/autodl/jinstall-1_4-windows-i586.cab#Version=1,4,0,0"
		width="300" height="400">
	<param name="code" value="com.gallery.GalleryRemote.GRAppletSlideshow">
	<param name="archive" value="java/GalleryRemoteAppletMini.jar,java/GalleryRemoteHTTPClient.jar,java/applet_img.jar">
	<param name="type" value="application/x-java-applet;version=1.4">
	<param name="scriptable" value="false">
	<param name="progressbar" value="true">
	<param name="boxmessage" value="Downloading the Gallery Remote Applet">
	<param name="gr_url" value="<?php echo $gallery->app->photoAlbumURL ?>">
	<param name="gr_cookie_name" value="<?php echo session_name() ?>">
	<param name="gr_cookie_value" value="<?php echo session_id() ?>">
	<param name="gr_cookie_domain" value="<?php echo $cookieInfo['domain'] ?>">
	<param name="gr_cookie_path" value="<?php echo $cookieInfo['path'] ?>">
	<param name="gr_album" value="<?php echo $gallery->album->fields["name"] ?>">
	<param name="GRDefault_slideshowDelay" value="10">
	<param name="GROverride_slideshowLowRez" value="true">
	<param name="GROverride_toSysOut" value="true">

	<comment>
		<embed
				type="application/x-java-applet;version=1.4"
				code="com.gallery.GalleryRemote.GRAppletSlideshow"
				archive="java/GalleryRemoteAppletMini.jar,java/GalleryRemoteHTTPClient.jar,java/applet_img.jar"
				width="300"
				height="400"
				scriptable="false"
				progressbar="true"
				boxmessage="Downloading the Gallery Remote Applet"
				pluginspage="http://java.sun.com/j2se/1.4.1/download.html"
				gr_url="<?php echo $gallery->app->photoAlbumURL ?>"
				gr_cookie_name="<?php echo session_name() ?>"
				gr_cookie_value="<?php echo session_id() ?>"
				gr_cookie_domain"<?php echo $cookieInfo['domain'] ?>"
				gr_cookie_path="<?php echo $cookieInfo['path'] ?>"
				gr_album="<?php echo $gallery->album->fields["name"] ?>"
				GRDefault_slideshowDelay="10"
				GROverride_slideshowLowRez="true"
				GROverride_toSysOut="true">
			<noembed
					alt="Your browser understands the &lt;APPLET&gt; tag but isn't running the applet, for some reason.">
				Your browser doesn't support applets; you should use one of the other upload methods.
			</noembed>

		</embed>
	</comment>
</object>
<br>

<?php
} else {
?>

<br><b><?php echo _("This album has no photos to show in a slide show.") ?></b>
<br><br>
<span class="admin">
<a href="<?php echo makeGalleryUrl("view_album.php",
array("set_albumName" => $gallery->session->albumName)) ?>">[<?php echo _("back to album") ?>]</a>
</span>

<?php
}
?>

</div>

<?php
includeLayout('ml_pulldown.inc');
includeHtmlWrap("slideshow.footer"); ?>

<?php if (!$GALLERY_EMBEDDED_INSIDE) { ?>
</body>
</html>
<?php } ?>
