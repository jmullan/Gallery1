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

<?php echo _("<p>Use the Gallery Remote Mini Applet to upload pictures. It will take a short while to download.</p>") ?>
<?php echo _("<p>If you don't have the Java Plugin 1.3 or later, you can use one of the other methods.</p>") ?>

<?php $cookieInfo = session_get_cookie_params(); ?>
<p>

<object
		classid="clsid:8AD9C840-044E-11D1-B3E9-00805F499D93"
		codebase="http://java.sun.com/products/plugin/autodl/jinstall-1_4-windows-i586.cab#Version=1,4,0,0"
		width="400" height="300">
	<param name="code" value="com.gallery.GalleryRemote.GRAppletMini">
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
	<param name="GROverride_toSysOut" value="true">

	<comment>
		<embed
				type="application/x-java-applet;version=1.4"
				code="com.gallery.GalleryRemote.GRAppletMini"
				archive="java/GalleryRemoteAppletMini.jar,java/GalleryRemoteHTTPClient.jar,java/applet_img.jar"
				width="400"
				height="300"
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
				GROverride_toSysOut="true">
			<noembed
					alt="Your browser understands the &lt;APPLET&gt; tag but isn't running the applet, for some reason.">
				Your browser doesn't support applets; you should use one of the other upload methods.
			</noembed>

		</embed>
	</comment>
</object>

</p>