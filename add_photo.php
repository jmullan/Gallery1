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


function msg($buf) {
	global $msgcount;

	if ($msgcount) {
		print "<br>";
	}
	print $buf;
	my_flush();
	$msgcount++;
}
function process($file, $tag, $name, $caption, $setCaption="", $extra_fields=array()) {
	global $gallery;
	global $temp_files;

	/* Figure out what files we can handle */
	// remove %20 and the like from name
	$name = urldecode($name);
	// parse out original filename without extension
	$originalFilename = eregi_replace(".$tag$", "", $name);
	// replace multiple non-word characters with a single "_"
	$mangledFilename = ereg_replace("[^[:alnum:]]", "_", $originalFilename);

	/* Get rid of extra underscores */
	$mangledFilename = ereg_replace("_+", "_", $mangledFilename);
	$mangledFilename = ereg_replace("(^_|_$)", "", $mangledFilename);

	/* 
	need to prevent users from using original filenames that are purely numeric.
	Purely numeric filenames mess up the rewriterules that we use for mod_rewrite
	specifically:
	RewriteRule ^([^\.\?/]+)/([0-9]+)$	/~jpk/gallery/view_photo.php?set_albumName=$1&index=$2	[QSA]
	*/

	if (ereg("^([0-9]+)$", $mangledFilename)) {
		$mangledFilename .= "_G";
	}

	set_time_limit($gallery->app->timeLimit);
	if (acceptableFormat($tag)) {

	        /*
		 * Move the uploaded image to our temporary directory
		 * using move_uploaded_file so that we work around
		 * issues with the open_basedir restriction.
		 */
		if (function_exists('move_uploaded_file')) {
		        $newFile = tempnam($gallery->app->tmpDir, "gallery");
			if (move_uploaded_file($file, $newFile)) {
			    $file = $newFile;
			}
			
			/* Make sure we remove this file when we're done */
			$temp_files[$newFile]++;
		}
	    
		msg("- Adding $name");
		if ($setCaption and $caption == "") {
			$caption = $originalFilename;
		}

		if (!$extra_fields)
		{
			$extra_fields=array();
		}
		$err = $gallery->album->addPhoto($file, $tag, $mangledFilename, $caption, "",  $extra_fields);
		if (!$err) {
			/* resize the photo if needed */
			if ($gallery->album->fields["resize_size"] > 0 && isImage($tag)) {
				$index = $gallery->album->numPhotos(1);
				$photo = $gallery->album->getPhoto($index);
				list($w, $h) = $photo->image->getRawDimensions();
				if ($w > $gallery->album->fields["resize_size"] ||
				    $h > $gallery->album->fields["resize_size"]) {
					msg("- Resizing $name"); 
					$gallery->album->resizePhoto($index, $gallery->album->fields["resize_size"]);
				}
			}
		} else {
			msg("<font color=red>Error: $err!</font>");
			msg("<b>Need help?  Look in the " .
			    "<a href=http://gallery.sourceforge.net/faq.php target=_new>Gallery FAQ</a></b>");
		}
	} else {
		msg("Skipping $name (can't handle '$tag' format)");
	}
}
?>
<html>
<head>
  <title>Add Photo</title>
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
<?php
if ($file_name) {
        $tag = ereg_replace(".*\.([^\.]*)$", "\\1", $file_name);
        $tag = strtolower($tag); 
	process($file, $tag, $file_name, $caption, $setCaption, $extra_fields);
	$gallery->album->save();

	if ($temp_files) {
		/* Clean up the temporary url file */
		foreach ($temp_files as $tf => $junk) {
				fs_unlink($tf);
		}
	}
	reload();
	?>
	<p><center><form>
	<input type=submit value="Dismiss" onclick='parent.close()'>
	</form></center>
<script language="Javascript">
<!--
opener.hideProgressAndReload();
-->
</script>

<?php
}

else
{
?>


<span class="popuphead">Add Photo</span>
<br>
<span class="popup">
Click the <b>Browse</b> button to locate a photo to upload.
<span class="admin">
<br>
&nbsp;&nbsp;(Supported file types: <?php echo join(", ", acceptableFormatList()) ?>)
</span>

<br><br>

<?php echo makeFormIntro("add_photo.php",
			array("name" => "upload_form",
				"enctype" => "multipart/form-data",
				"method" => "POST")); ?>
<input type="hidden" name="max_file_size" value="10000000">
<table>
<tr><td>
File</td>
<td><input name="file" type="file" size=40></td></tr>
<td>Caption</td><td> <textarea name="caption" rows=2 cols=40></textarea></td></tr>
<?php
foreach ($gallery->album->getExtraFields() as $field)
{
        if ($field == "Capture Date" || $field == "Upload Date")
        {
                continue;
        }
        print "<tr><td valign=top>$field</td><td>";
        if ($field == "Title")
        {
                print "<input type=text name=\"extra_fields[$field]\" value=\"$value\" size=\"40\">";
        }
	else
	{
        	print "<textarea name=\"extra_fields[$field]\" rows=2 cols=40>";
        	print "$value</textarea>";
	}
        print "</td></tr>";
}
?>

</table>
<input type=checkbox name=setCaption checked value="1">Use filename as caption if no caption is specified.
<br>
<center>
<input type="button" value="Upload Now" onClick='opener.showProgress(); document.upload_form.submit()'>
<input type=submit value="Cancel" onclick='parent.close()'>
</center>
</form>
<?php } ?>

</body>
</html>
