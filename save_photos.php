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
    $GALLERY_BASEDIR = '';
}
require($GALLERY_BASEDIR . 'init.php'); ?>
<?php
// Hack check
if (!$gallery->user->canAddToAlbum($gallery->album)) {
	exit;
}

if ($userfile_name) {
	$file_count = 0;
	foreach ($userfile_name as $file) {
		if ($file) {
			$file_count++;
		}
	}
}

?>
<html>
<head>
  <title><?php echo _("Processing and Saving Photos") ?></title>
  <?php echo getStyleSheetLink() ?>

</head>
<body onLoad='opener.hideProgressAndReload();'>

<?php
if ($urls) {
?>
<span class=title><?php echo _("Fetching Urls...") ?></span>
<br>
<?php
	/* Process all urls first */
	$temp_files = array();
	
	foreach ($urls as $url) {

	        /* Get rid of any extra white space */
	        $url = trim($url);
		
		/*
		 * Check to see if the URL is a local directory (inspired by
		 * code from Jared (hogalot))
		 */
		if (fs_is_dir($url)) {
			processingMsg(sprintf(_("Processing %s as a local directory."),
						"<i>$url</i>"));
			$handle = fs_opendir($url);
			while (($file = readdir($handle)) != false) {
				if ($file != "." && $file != "..") {
					$tag = ereg_replace(".*\.([^\.]*)$", "\\1", $file);
					$tag = strtolower($tag);
					if (acceptableFormat($tag)) {
						/* Tack it onto userfile */
						if (substr($url,-1) == "/") {
							$image_tags[] = fs_export_filename($url . $file);
						} else {
							$image_tags[] = fs_export_filename($url . "/" . $file);
						}
					}
				}
			}
			closedir($handle);
			continue;
		}

		/* Get rid of any preceding whitespace (fix for odd browsers like konqueror) */
		$url = eregi_replace("^[[:space:]]+", "", $url);

		/* If the URI doesn't start with a scheme, prepend 'http://' */
		if (!fs_is_file($url)) {
			if (!ereg("^(http|ftp)", $url)) {
				$url = "http://$url";
			}
		}

		/* Parse URL for name and file type */
		$url_stuff = parse_url($url);
		$name = basename($url_stuff["path"]);
		$tag = ereg_replace(".*\.([^\.]*)$", "\\1", $url);
		$tag = strtolower($tag);
	
		/* Dont output warning messages if we cant open url */
	
		/*
		 * Try to open the url in lots of creative ways.
		 * Do NOT use fs_fopen here because that will pre-process
		 * the URL in win32 style (ie, convert / to \, etc).
		 */
 		$id = @fopen($url, "rb");
		if (!ereg("http", $url)) {
			if (!$id) $id = @fopen("http://$url", "rb");
			if (!$id) $id = @fopen("http://$url/", "rb");
		}
		if (!$id) $id = @fopen("$url/", "rb");

		if ($id) {
			processingMsg(urldecode($url));
		} else {
			processingMsg(sprintf(_("Could not open url: %s"), 
							$url));
			continue;
		} 
	
		/* copy file locally */
		$file = $gallery->app->tmpDir . "/photo.$name";
		$od = fs_fopen($file, "wb");
		if ($id && $od) {
			while (!feof($id)) {
				fwrite($od, fread($id, 65536));
				set_time_limit($gallery->app->timeLimit);
			}
			fclose($id);
			fclose($od);
		}

		/* Make sure we delete this file when we're through... */
		$temp_files[$file]++;
	
		/* If this is an image or movie - add it to the processor array */
		if (acceptableFormat($tag) || !strcmp($tag, "zip")) {
			/* Tack it onto userfile */
			$userfile_name[] = $name;
			$userfile[] = $file;
		} else {
			/* Slurp the file */
			processingMsg(sprintf(_("Parsing %s for images..."),
						$url));
			$fd = fs_fopen ($file, "r");
			$contents = fread ($fd, fs_filesize ($file));
			fclose ($fd);
	
			/* We'll need to add some stuff to relative links */
			$base_url = $url_stuff["scheme"] . '://' . $url_stuff["host"];
			$base_dir = '';
			if ($url_stuff["port"]) {
			  $base_url .= ':' . $url_stuff["port"];
			}
	
			/* Hack to account for broken dirname */
			if (ereg("/$", $url_stuff["path"])) {
				$base_dir = $url_stuff["path"];
			} else {
				$base_dir = dirname($url_stuff["path"]);
			}
	
			/* Make sure base_dir ends in a / ( accounts for empty base_dir ) */
			if (!ereg("/$", $base_dir)) {
				$base_dir .= '/';
			}

			$things = array();
			while ($cnt = eregi('(src|href)="?([^" >]+\.' . acceptableFormatRegexp() . ')[" >]',
					    $contents, 
					    $results)) {
				set_time_limit($gallery->app->timeLimit);
				$things[$results[2]]++;
				$contents = str_replace($results[2], "", $contents);
			}

			/* Add each unique link to an array we scan later */
			foreach (array_keys($things) as $thing) {

				/* 
				 * Some sites (slashdot) have images that start with // and this
				 * confuses Gallery.  Prepend 'http:'
				 */
				if (!strcmp(substr($thing, 0, 2), "//")) {
					$thing = "http:$thing";
				}

				/* Absolute Link ( http://www.foo.com/bar ) */
				if (substr($thing, 0, 4) == 'http') {
					$image_tags[] = $thing;

				/* Relative link to the host ( /foo.bar )*/
				} elseif (substr($thing, 0, 1) == '/') {
					$image_tags[] = $base_url . $thing;

				/* Relative link to the dir ( foo.bar ) */
				} else {
					$image_tags[] = $base_url . $base_dir . $thing;
				}
			}
	
			/* Tell user how many links we found, but delay processing */
			processingMsg(sprintf(_("Found %d images"), count($image_tags)));
		}
	}
} /* if ($urls) */
?>


<br>
<span class=title><?php echo _("Processing status...") ?></span>
<br>

<?php
while (sizeof($userfile)) {
	$name = array_shift($userfile_name);
	$file = array_shift($userfile);
	if (!empty($usercaption) && is_array($usercaption)) {
	    $caption = removeTags(array_shift($usercaption));
	}
	if (get_magic_quotes_gpc()) {
		$caption=stripslashes($caption);    
	}

	$tag = ereg_replace(".*\.([^\.]*)$", "\\1", $name);
	$tag = strtolower($tag);

	if ($name) {
		processNewImage($file, $tag, $name, $caption, $setCaption);
	}
}

$gallery->album->save();

if ($temp_files) {
	/* Clean up the temporary url file */
	foreach ($temp_files as $tf => $junk) {
		fs_unlink($tf);
	}
}
?>

<?php
if (!$msgcount) {
	print _("No images uploaded!");
}
?>
<center>
<form>
<input type=submit value="<?php echo _("Dismiss") ?>" onclick='parent.close()'>
</form>
<?php
/* Prompt for additional files if we found links in the HTML slurpage */
if (count($image_tags)) {
?>

<?php $uploadUrlFormName = "uploadurl_form"; ?>

<?php /* Note: the w3c-suggested "text/javascript" doesn't work with Navigator 4 */ ?>
<script language="javascript">
// <!-- 
function setCheck(val) {
	ufne=document.<?php echo $uploadUrlFormName; ?>;
	len = ufne.elements.length;
	for(i = 0 ; i < len ; i++) {
		if (ufne.elements[i].name=='urls[]') {
			ufne.elements[i].checked=val;
		}
	}
}
function invertCheck() {
	ufne=document.<?php echo $uploadUrlFormName; ?>;
	len = ufne.elements.length;
	for(i = 0 ; i < len ; i++) {
		if (ufne.elements[i].name=='urls[]') {
			ufne.elements[i].checked = !(ufne.elements[i].checked);
		}
	}
}
// -->
</script>

<p><span class="fineprint">
<a href="javascript:setCheck(1)"><?php echo _("Check All") ?></a>
-
<a href="javascript:setCheck(0)"><?php echo _("Clear All") ?></a>
-
<a href="javascript:invertCheck()"><?php echo _("Invert Selection") ?></a>
</span></p>

<table><tr><td>
<?php echo makeFormIntro("save_photos.php", 
		array("name" => $uploadUrlFormName, 
			"method" => "POST")); ?>
<?php
	/* Allow user to select which files to grab - only show url right now ( no image previews ) */
	sort($image_tags);
	foreach ( $image_tags as $image_src) {
		print "<input type=checkbox name=\"urls[]\" value=\"$image_src\" checked>$image_src<br />\n";
	}
?>
</td></tr></table>

<?php /* REVISIT - it'd be nice to have these functions get shoved
  into util.php at some time - maybe added functionality to the makeFormIntro? */ ?>

<p><span class="fineprint">
<a href="javascript:setCheck(1)"><?php echo _("Check All") ?></a>
-
<a href="javascript:setCheck(0)"><?php echo _("Clear All") ?></a>
-
<a href="javascript:invertCheck()"><?php echo _("Invert Selection") ?></a>
</span></p>

<p>
<input type=hidden name="setCaption" value="<?php echo $setCaption ?>">
<input type=button value=<?php echo '"' . _("Add Files") . '"' ?> onClick="opener.showProgress(); document.uploadurl_form.submit()">
</p>

</form>
</center>
<?php } /* End if links slurped */ ?>
</body>
</html>
