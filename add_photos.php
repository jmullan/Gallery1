<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2002 Bharat Mediratta
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
<?php
// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
	print "Security violation\n";
	exit;
}
?>
<?php require($GALLERY_BASEDIR . "init.php"); ?>
<?php
// Hack check
if (!$gallery->user->canAddToAlbum($gallery->album)) {
	exit;
}
	
if (!$boxes) {
	$boxes = 5;
}

?>

<html>
<head>
  <title>Add Photos</title>
  <?php echo getStyleSheetLink() ?>

<script language="Javascript">
<!--
	function reloadPage() {
		document.count_form.submit();
		return false;
	}
// -->
</script>
</head>
<body>

<span class="popuphead">Add Photos</span>
<br>
<span class="popup">
Click the <b>Browse</b> button to locate a photo to upload.
<span class="admin">
<?php if ($gallery->app->feature["zip"]) { ?>
<br>
&nbsp;&nbsp;Tip:  Upload a ZIP file full of photos and movies!
<?php } ?>
<br>
&nbsp;&nbsp;(Supported file types: <?php echo join(", ", acceptableFormatList()) ?>)
</span>

<br><br>
<?php echo makeFormIntro("add_photos.php",
			array("name" => "count_form",
				"method" => "POST")); ?>
1. Select the number of files you want to upload:
<select name="boxes" onChange='reloadPage()'>
<?php for ($i = 1; $i <= 10;  $i++) {
	echo "<option ";
        if ($i == $boxes) {
		echo "selected ";
	}
	echo "value=\"$i\">$i\n";

} ?>
</select>
<br>
</form>

<?php echo makeFormIntro("save_photos.php",
			array("name" => "upload_form",
				"enctype" => "multipart/form-data",
				"method" => "POST")); ?>
2. Use the Browse button to find the photos on your computer
<input type="hidden" name="max_file_size" value="10000000">
<?php for ($i = 0; $i < $boxes;  $i++) { ?>
<br> <input name="userfile[]" type="file" size=40>
<?php } ?>
<br>
<input type=checkbox name=setCaption checked value="1">Set photo captions with original filenames.
<br>
<center>
<input type="button" value="Upload Now" onClick='opener.showProgress(); document.upload_form.submit()'>
<input type=submit value="Cancel" onclick='parent.close()'>
</center>
</form>

<?php echo makeFormIntro("save_photos.php",
			array("name" => "uploadurl_form",
				"method" => "POST")); ?>
Or, upload any images found at this location.  The location
can either be a URL or a directory on the server.
<br>
<span class="admin">
&nbsp;&nbsp;Tip: FTP images to a directory on your server then provide that path here!
</span>
<br>

<input type="text" name="urls[]" size=40>
<br>
<input type=checkbox name=setCaption checked value="1">Set photo captions with original filenames.
<br>
<center>
<input type="button" value="Submit URL or directory" onClick='opener.showProgress(); document.uploadurl_form.submit()'>
<input type=submit value="Cancel" onclick='parent.close()'>
</center>
</form>
Alternatively, you can use one of these desktop agents to drag and drop photos from your desktop:
<br>
&nbsp;&nbsp;&nbsp;<b><a href="#" onClick="opener.location = 'http://gallery.sourceforge.net/gallery_remote.php?protocol_version=<?php echo $gallery->remote_protocol_version ?>'; parent.close();">Gallery Remote</a></b>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; A Java application that runs on Mac, Windows and Unix
<br>
&nbsp;&nbsp;&nbsp;<b><a href="<?php echo makeGalleryUrl("publish_xp.php") ?>">Windows XP Publishing Agent</a></b>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i>Note:</i> this feature is still experimental!
</span>

</body>
</html>
