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

require(dirname(__FILE__) . '/init.php');

// Hack check
if (!$gallery->user->canAddToAlbum($gallery->album)) {
	echo _("You are no allowed to perform this action !");
	exit;
}

if (isset($userfile_name)) {
	$file_count = 0;
	foreach ($userfile_name as $file) {
		if ($file) {
			$file_count++;
		}
	}
}

if (!isset($wmName)) $wmName = "";
if (!isset($wmAlign)) $wmAlign = "";
if (!isset($wmAlignX)) $wmAlignX = "";
if (!isset($wmAlignY)) $wmAlignY = "";

doctype();
?>
<html>
<head>
  <title><?php echo _("Processing and Saving Photos") ?></title>
  <?php common_header(); ?>

</head>
<body dir="<?php echo $gallery->direction ?>" onLoad='parent.opener.hideProgressAndReload();'>

<?php
$image_tags = array();
$info_tags = array();
if (!empty($urls)) {
?>
<span class="popuphead"><?php echo _("Fetching Urls...") ?></span>
<span class="popup">
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
					if ($tag == "csv") {
						if (substr($url,-1) == "/") {
							$info_tags[] = fs_export_filename($url . $file);
						} else {
							$info_tags[] = fs_export_filename($url . "/" . $file);
						}
					}
				}
			}
			closedir($handle);
			continue;
		}

		/* Get rid of any preceding whitespace (fix for odd browsers like konqueror) */
		$url = eregi_replace("^[[:space:]]+", "", $url);

		$tag = ereg_replace(".*\.([^\.]*)$", "\\1", $url);
		$tag = strtolower($tag);

		/* If the URI doesn't start with a scheme, prepend 'http://' */
		if (!empty($url) && !fs_is_file($url)) {
			if (!ereg("^(http|ftp)", $url)) {
				$url = "http://$url";
			}

			/* Parse URL for name and file type */
			$url_stuff = @parse_url($url);
			if (!isset($url_stuff["path"])) { 
				$url_stuff["path"]="";
			}
			$name = basename($url_stuff["path"]);

		} else {
			$name = basename($url);

		}
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
                // Ensure that the file we've retrieved is in an acceptable image/movie format
                if (!acceptableFormat($tag)) {
                        gallery_error(sprintf(_("Invalid file type; %s !"), $tag));
                        continue;
                }

                // Prevent overly long temp names from an external source
                if(strlen($name) > 20) {
                        $name = substr($name, strlen($name) - 20);
                }       
                
                /* copy file locally 
                   use fopen instead of fs_fopen to prevent directory
                   disclosure */
                $file = $gallery->app->tmpDir . "/photo.$name";
                $od = @fopen($file, "wb");
		if ($id && $od) {
			while (!feof($id)) {
				fwrite($od, fread($id, 65536));
				set_time_limit($gallery->app->timeLimit);
			}
			fclose($id);
			fclose($od);
		}

		/* Make sure we delete this file when we're through... */
		$temp_files[$file]=1;
	
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
			if (isset($url_stuff["port"])) {
			  $base_url .= ':' . $url_stuff["port"];
			}
	
			/* Hack to account for broken dirname 
			 * This has to make the ugly assumption that the URL is either a
			 * directory (with or without trailing /), or a filename containing a "."
			 * This prevents a directory without a trailing / from being inadvertantly
			 * dropped from resulting URLs.
			 */
			if (ereg("/$", $url_stuff["path"]) || !ereg("\.", $name)) {
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
				$things[$results[2]]=1;
				$contents = str_replace($results[0], "", $contents);
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

</span>
<br>
<span class="popuphead"><?php echo _("Processing status...") ?></span>
<br>

<?php
$image_count=0;
$image_info = array();
// Get meta data
if (isset($meta)) {
	foreach ($meta as $data) {
		$image_info = array_merge($image_info, parse_csv(fs_export_filename($data),";"));
	}
}
while (isset($metafile) && sizeof($metafile)) {
	$name = array_shift($metafile_name);
	$file = array_shift($metafile);
	$image_info = array_merge($image_info, parse_csv(fs_export_filename($file),";"));
}
if ($gallery->app->debug == "yes") {
	// Print meta data
	print "<table border=\"1\">\n";
	$row = 0;
	foreach ($image_info as $info) {
		print "<tr>";
		if ($row == 0) {
			$keys = array_keys($info);
			foreach ($keys as $key) {
				print "<th>$key</th>";
			}
			print "</tr>\n<tr>";
		}
		foreach ($keys as $key) {
			print "<td>".$info[$key]."</td>";
		}
		$row++;
		print "</tr>\n";
	}
	print "</table>\n";
}
// $captionMetaFields will store the names (in order of priority to set caption to)
$captionMetaFields = array("Caption", "Title", "Description", "Persons");

while (isset($userfile) && sizeof($userfile)) {
	$name = array_shift($userfile_name);
	$file = array_shift($userfile);
	if (!empty($usercaption) && is_array($usercaption)) {
	    $caption = removeTags(array_shift($usercaption));
	} else {
	    $caption = '';
	}
	if (!isset($caption)) {
	       	$caption="";
       	}
	if (get_magic_quotes_gpc()) {
		$caption=stripslashes($caption);    
	}

	$tag = ereg_replace(".*\.([^\.]*)$", "\\1", $name);
	$tag = strtolower($tag);

	if ($name) {
		$extra_fields = array();
		if (!isset($setCaption)) {
			$setCaption = '';
		}
		// Find in meta data array
		$firstRow = 1;
		$fileNameKey = "File Name";
                foreach ( $image_info as $info ) {
			if ($firstRow) {
				// Find the name of the file name field
				foreach (array_keys($info) as $currKey) {
					if (eregi("^\"?file\ ?name\"?$", $currKey)) {
						$fileNameKey = $currKey;
					}
				}
				$firstRow = 0;
			}
                        if ($info[$fileNameKey] == $name) {
				// Loop through fields
				foreach ($captionMetaFields as $field) {
					// If caption isn't populated and current field is
					if (!strlen($caption) && strlen($info[$field])) {
						$caption = $info[$field];
					}
				}
				$extra_fields = $info;
                        }
                }
		// Add new image
		processNewImage($file, $tag, $name, $caption, $setCaption, $extra_fields, $wmName, $wmAlign, $wmAlignX, $wmAlignY);
		$image_count++;
	}
}

$gallery->album->save(array(i18n("%d files uploaded"), $image_count));

if (!empty($temp_files)) {
	/* Clean up the temporary url file */
	foreach ($temp_files as $tf => $junk) {
		fs_unlink($tf);
	}
}
?>

<span class="popup">
<?php
if (empty($msgcount)) {
	print _("No images uploaded!");
}
?>
<center>
<form>
<input type="button" value="<?php echo _("Dismiss") ?>" onclick='parent.close()'>
</form>
<?php
/* Prompt for additional files if we found links in the HTML slurpage */
if (count($image_tags)) {

	/*
	** include JavaScript (de)selection and invert
	*/
	insertFormJS('uploadurl_form');
?>
</span>
<p class="popup">
<?php 
	echo insertFormJSLinks('urls[]'); 
?>
</p>

<table>
<tr>
	<td class="popup">
<?php echo makeFormIntro("save_photos.php", 
		array("name" => 'uploadurl_form',
			"method" => "POST")); 

	/* Allow user to select which files to grab - only show url right now ( no image previews ) */
	sort($image_tags);
	foreach ( $image_tags as $image_src) {
		print "\t<input type=checkbox name=\"urls[]\" value=\"$image_src\" checked>$image_src</input><br>\n";
	}
?>
	</td>
</tr>
</table>

<?php /* REVISIT - it'd be nice to have these functions get shoved
  into util.php at some time - maybe added functionality to the makeFormIntro? */ ?>

<p class="popup">
<?php 
	echo insertFormJSLinks('urls[]'); 
?>
</p>
<?php if (count($info_tags)) { ?>
<span class="popup">
<?php
	processingMsg(sprintf(_("%d meta file(s) found.  These files contain information about the images, such as titles and descriptions."), count($info_tags)));
?>
</span>
<p class="popup">
<?php
        echo insertFormJSLinks('meta[]');
?>
</p>
<table>
<tr>
	<td>
<?php
	foreach ($info_tags as $info_tag) {
		print "\t<input type=\"checkbox\" name=\"meta[]\" value=\"$info_tag\" checked/>$info_tag</input><br>\n";
	}

?>
	</td>
</tr>
</table>
<p class="popup">
<?php
	echo insertFormJSLinks('meta[]');
?>
<?php } /* end if (count($info_tags)) */ ?>
<p>
<input type="hidden" name="setCaption" value="<?php echo $setCaption ?>">
<input type="hidden" name="wmName" value="<?php echo $wmName ?>">
<input type="hidden" name="wmAlign" value="<?php echo $wmAlign ?>">
<input type="hidden" name="wmAlignX" value="<?php echo $wmAlignX ?>">
<input type="hidden" name="wmAlignY" value="<?php echo $wmAlignY ?>">
<input type="button" value="<?php echo _("Add Files") ?>" onClick="parent.opener.showProgress(); document.uploadurl_form.submit()">
</p>

</form>
</center>
<?php } /* End if links slurped */ ?>
</body>
</html>
