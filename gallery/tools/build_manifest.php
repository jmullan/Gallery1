<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2003 Bharat Mediratta
 * This file by Joan McGalliard.
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


/*
 * This script must be run from command line, in directory gallery/
 * eg php tools/build_manifest.php
 */
?>
<?php
$GALLERY_BASEDIR="./";
if (php_sapi_name() != "cli") {
	print _("This page is for development use only.");
	print "<br>";
	exit;
}

include 'util.php';
include($GALLERY_BASEDIR . "platform/fs_unix.php");
if (!fs_is_readable("setup")) {
       	print "Cannot build manifest unless in config mode";
	print "\n";
	exit (2);
}
$files=array(	"AUTHORS",
	       	"LICENSE.txt",
	       	"README",
	       	"Version.php",
	       	"add_comment.php",
	       	"add_photo.php",
	       	"add_photos.php",
	       	"adv_search.php",
	       	"album_permissions.php",
	       	"albums.php",
	       	"captionator.php",
	       	"configure.bat",
	       	"configure.sh",
	       	"copy_photo.php",
	       	"create_user.php",
	       	"delete_album.php",
	       	"delete_photo.php",
	       	"delete_user.php",
	       	"do_command.php",
	       	"edit_appearance.php",
	       	"edit_caption.php",
	       	"edit_field.php",
	       	"edit_thumb.php",
	       	"extra_fields.php",
	       	"gallery_remote.php",
	       	"gallery_remote2.php",
	       	"index.php",
	       	"init.php",
	       	"login.php",
	       	"manage_users.php",
	       	"modify_user.php",
	       	"move_album.php",
	       	"move_photo.php",
	       	"multi_create_user.php",
	       	"new_password.php",
	       	"nls.php",
	       	"photo_owner.php",
	       	"poll_properties.php",
	       	"poll_results.php",
	       	"progress_uploading.php",
	       	"publish_xp.php",
	       	"publish_xp_docs.php",
	       	"register.php",
	       	"rename_album.php",
	       	"reset_votes.php",
	       	"resize_photo.php",
	       	"rotate_photo.php",
	       	"save_photos.php",
	       	"search.php",
	       	"secure.bat",
	       	"secure.sh",
	       	"session.php",
	       	"slideshow.php",
	       	"slideshow_low.php",
	       	"sort_album.php",
	       	"upgrade_album.php",
	       	"upgrade_users.php",
	       	"user_preferences.php",
	       	"util.php",
	       	"view_album.php",
	       	"view_comments.php",
	       	"view_photo.php",
	       	"view_photo_properties.php",
	       	"classes/Album.php",
	       	"classes/AlbumDB.php",
	       	"classes/AlbumItem.php",
	       	"classes/Comment.php",
	       	"classes/Database.php",
	       	"classes/EverybodyUser.php",
	       	"classes/Image.php",
	       	"classes/LoggedInUser.php",
	       	"classes/NobodyUser.php",
	       	"classes/User.php",
	       	"classes/UserDB.php",
	       	"classes/database/mysql/Database.php",
	       	"classes/gallery/User.php",
	       	"classes/gallery/UserDB.php",
	       	"classes/nuke5/AdminUser.php",
	       	"classes/nuke5/User.php",
	       	"classes/nuke5/UserDB.php",
	       	"classes/postnuke/User.php",
	       	"classes/postnuke/UserDB.php",
	       	"classes/postnuke0.7.1/User.php",
	       	"classes/postnuke0.7.1/UserDB.php",
	       	"classes/remote/GalleryRemoteProperties.php",
	       	"css/config.css.default",
	       	"css/embedded_style.css.default",
	       	"css/standalone_style.css.default",
	       	"errors/configmode.php",
	       	"errors/configure_help.php",
	       	"errors/configure_instructions.php",
	       	"errors/reconfigure.php",
	       	"errors/unconfigured.php",
	       	"html/errorRow.inc",
	       	"html/userData.inc",
	       	"html_wrap/album.footer.default",
	       	"html_wrap/album.header.default",
	       	"html_wrap/gallery.footer.default",
	       	"html_wrap/gallery.header.default",
	       	"html_wrap/inline_albumthumb.footer.default",
	       	"html_wrap/inline_albumthumb.frame.default",
	       	"html_wrap/inline_albumthumb.header.default",
	       	"html_wrap/inline_gallerythumb.frame.default",
	       	"html_wrap/inline_imagewrap.inc",
	       	"html_wrap/inline_moviethumb.frame.default",
	       	"html_wrap/inline_photo.footer.default",
	       	"html_wrap/inline_photo.frame.default",
	       	"html_wrap/inline_photo.header.default",
	       	"html_wrap/inline_photothumb.frame.default",
	       	"html_wrap/photo.footer.default",
	       	"html_wrap/photo.header.default",
	       	"html_wrap/search.footer.default",
	       	"html_wrap/search.header.default",
	       	"html_wrap/slideshow.footer.default",
	       	"html_wrap/slideshow.header.default",
	       	"html_wrap/wrapper.footer.default",
	       	"html_wrap/wrapper.header.default",
	       	"html_wrap/frames/README.php",
	       	"html_wrap/frames/polaroid/frame.def",
	       	"html_wrap/frames/polaroids/frame.def",
	       	"html_wrap/frames/shadows/frame.def",
	       	"html_wrap/frames/simple_book/frame.def",
	       	"js/client_sniff.js",
	       	"layout/adminbox.inc",
	       	"layout/breadcrumb.inc",
	       	"layout/commentbox.inc",
	       	"layout/commentboxbottom.inc",
	       	"layout/commentboxtop.inc",
	       	"layout/commentdraw.inc",
	       	"layout/ml_pulldown.inc",
	       	"layout/navigator.inc",
	       	"layout/navphoto.inc",
		"layout/navtablebegin.inc",
		"layout/navtableend.inc",
		"layout/navtablemiddle.inc",
	       	"layout/searchdraw.inc",
	       	"platform/fs_unix.php",
	       	"platform/fs_win32.php",
	       	"setup/backup_albums.php",
	       	"setup/check.inc",
	       	"setup/check_imagemagick.php",
	       	"setup/check_mail.php",
	       	"setup/check_netpbm.php",
	       	"setup/check_versions.php",
	       	"setup/config_data.inc",
	       	"setup/confirm.inc",
	       	"setup/constants.inc",
	       	"setup/defaults.inc",
	       	"setup/diagnostics.php",
	       	"setup/functions.inc",
	       	"setup/gpl.txt",
	       	"setup/index.php",
	       	"setup/init.php",
	       	"setup/mod_rewrite.template",
	       	"setup/php_value.template",
	       	"setup/php_value_ok.php",
	       	"setup/phpinfo.php",
	       	"setup/session_test.php",
	       	"setup/write.inc");

$outfile="manifest.inc";
copy("setup/gpl.txt", $outfile);
$fd=fopen($outfile, "a");

fwrite($fd, "<?php\n\n");
fwrite($fd, "/*\n * DO NOT EDIT!!!  This file is created by build_manifest.php.\n * Edit that file and re-run via command line to modify this.\n */\n\n");
fwrite($fd, "\$versions=array();\n");

$error=false;
foreach ($files as $file) {
       	$version=getCVSVersion($file);
       	if ($version === NULL) {
	       	print "ERROR: $file missing\n";
		$error=true;
       	} else if ($version === "") {
	       	print "ERROR: \$id missing from $file\n";
		$error=true;
       	} else {
	       	fwrite($fd, "\$versions['$file']='$version';\n");
       	}
}	

fwrite($fd, "?>\n");
fclose($fd);
if (!$error) {
	print "Done\n";
	exit(0);
} else {
	print "Please fix errors and re-run\n";
	exit(1);
}



?>
