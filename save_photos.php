<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2005 Bharat Mediratta
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

/**
 * @package Item
 */

/**
 *
 */
require_once(dirname(__FILE__) . '/init.php');

list($urls, $meta, $usercaption, $setCaption) = getRequestVar(array('urls', 'meta', 'usercaption','setCaption'));
list($wmName, $wmAlign, $wmAlignX, $wmAlignY) = getRequestVar(array('wmName', 'wmAlign', 'wmAlignX', 'wmAlignY'));
list($wmSelect) = getRequestVar(array('wmSelect'));

// Hack check
if (!$gallery->user->canAddToAlbum($gallery->album)) {
	echo _("You are not allowed to perform this action!");
	exit;
}

if (!empty($_FILES['userfile']['name'])) {
	$file_count = 0;
	foreach ($_FILES['userfile']['name'] as $file) {
		if ($file) {
			$file_count++;
		}
	}
}

doctype();
?>
<html>
<head>
  <title><?php echo _("Processing and Saving Photos") ?></title>
  <?php common_header(); ?>

</head>
<body dir="<?php echo $gallery->direction ?>" onLoad='parent.opener.hideProgressAndReload();' class="popupbody">
<?php

$image_tags = array();
$info_tags = array();
if (!empty($urls)) {
?>
<div class="popuphead"><?php echo _("Fetching Urls...") ?></div>
<div class="popup" align="center">
<?php
	/* Process all urls first.
	** $urls contains all URLs given by the "URL Upload".
	** $urls should be empty when using the "Form Upload".
	*/
	foreach ($urls as $url) {

	/* Get rid of any extra white space */
	$url = trim($url);
		
	/*
	** Check to see if the URL is a local directory (inspired by
	** code from Jared (hogalot))
	*/
	if (fs_is_dir($url)) {
		processingMsg(sprintf(_("Processing %s as a local directory."), 
			'<i>' . htmlspecialchars(strip_tags(urldecode($url))) . '</i>'));
		$handle = fs_opendir($url);
		while (($file = readdir($handle)) != false) {
			if ($file != "." && $file != "..") {
				$tag = pathinfo($file);
				$tag = strtolower(isset($tag['extension']) ? $tag['extension'] : '');
				if (acceptableFormat($tag)) {
					/* Add to userfile */
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
		$url = ltrim($url);
		
		$urlParts = parse_url($url);
		$urlPathInfo = isset($urlParts['path']) ? pathinfo($urlParts['path']) : '';
		$urlExt = isset($urlPathInfo['extension']) ? strtolower($urlPathInfo['extension']) : '';
		
		/* If the URI doesn't start with a scheme, prepend 'http://' */
		if (!empty($url) && !fs_is_file($url)) {
			if (!ereg("^(http|ftp)", $url)) {
				processingMsg(sprintf(_('Unable to find %s locally - trying %s.'), 
					htmlspecialchars(strip_tags(urldecode($url))), 'http'));
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
			if (!$id) {
			    $url = "http://$url";
			    $id = @fopen($url, "rb");
			}
			if (!$id) {
			    $url = "http://$url/";
                $id = @fopen($url, "rb");
			}
		}
		if (!$id) {
		  $url = "$url/";
		  $id = @fopen($url, "rb");
		}
		
		if (!$id) {
			processingMsg(sprintf(_("Could not open url: %s"), $url));
			continue;
		} 
	
		/**
		 * If this is an image or movie - 
		 * copy it locally and add it to the processor array
		 */
		if (acceptableFormat($urlExt) || acceptableArchive($urlExt)) {

		    /* copy file locally
		    * use fopen instead of fs_fopen to prevent directory and filename disclosure
		    */
		    $file = $gallery->app->tmpDir . "/upload." . genGUID();
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
		    
		    /* Add it to userfile */
		    $_FILES['userfile']['name'][] = $name;
		    $_FILES['userfile']['tmp_name'][] = $file;

		} else {
		    /* Slurp the file */
		    processingMsg(sprintf(_("Parsing %s for images..."), $url));
		    $contents = fs_file_get_contents($url);

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
			$results =array();

            if (preg_match_all('{(?:src|href)\s*=\s*(["\'])([^\'">]+\.'. acceptableFormatRegexp() .')(?:\1)}i', $contents, $matches)) {
                foreach ($matches[2] as $url) {
                    $things[$url] = 1;
                }
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
	echo "</div>\n";
} /* if ($urls) */
?>

<div class="popuphead"><?php echo _("Processing status...") ?></div>
<div class="popup" align="center">

<?php
$image_count = 0;
$image_info = array();
// Get meta data
if (isset($meta)) {
	processingMsg("Metainfo found");
	foreach ($meta as $data) {
		$image_info = array_merge($image_info, parse_csv(fs_export_filename($data),";"));
	}
}
while (isset($_FILES['metafile']['tmp_name']) && sizeof($_FILES['metafile']['tmp_name'])) {
	$name = array_shift($_FILES['metafile']['name']);
	$file = array_shift($_FILES['metafile']['tmp_name']);
	$image_info = array_merge($image_info, parse_csv(fs_export_filename($file),";"));
}
if (isDebugging()) {
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

$upload_started = false;
/* Now we start processing the given Files */
while (isset($_FILES['userfile']['tmp_name']) && sizeof($_FILES['userfile']['tmp_name'])) {
    $upload_started = true;
	$name = array_shift($_FILES['userfile']['name']);
	$file = array_shift($_FILES['userfile']['tmp_name']);
	if (!empty($usercaption) && is_array($usercaption)) {
	    $caption = strip_tags(array_shift($usercaption));
	} else {
	    $caption = '';
	}
	if (!isset($caption)) {
	       	$caption="";
       	}

	if ($name) {
		$extra_fields = array();
		if (!isset($setCaption)) {
			$setCaption = '';
		}
		// Find in meta data array
		$firstRow = 1;
		$fileNameKey = "File Name";
		foreach ($image_info as $info) {
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

		$path_parts = pathinfo($name);
		$ext = strtolower($path_parts["extension"]);

		// Add new image
		processNewImage($file, $ext, $name, $caption, $setCaption, $extra_fields, $wmName, $wmAlign, $wmAlignX, $wmAlignY, $wmSelect);
		$image_count++;
	}
}

if ($image_count) {
	$gallery->album->save(array(i18n("%d files uploaded"), $image_count));
}

if (!empty($temp_files)) {
    /* Clean up the temporary url file */
    foreach ($temp_files as $tf => $junk) {
        fs_unlink($tf);
    }
}
?>

<div align="center">
<?php
if (empty($image_count) && $upload_started) {
	print _("No images uploaded!");
}
?>
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
</div>
<p class="popuptd">
<?php 
	echo insertFormJSLinks('urls[]'); 
?>
</p>

<?php 
    echo _("Select the items you want to upload. To select multiple hold 'ctrl' (PC) or 'Command' (Mac)");
    echo makeFormIntro("save_photos.php", 
		array("name" => 'uploadurl_form'),
		array('type' => 'popup')); 

    /* Allow user to select which files to grab - only show url right now ( no image previews ) */
    sort($image_tags);
    $selectSize = (sizeof($image_tags) > 20) ? 20 : sizeof($image_tags);
	
    echo '<select name="urls[]" multiple="multiple" size="'. $selectSize ."\">\n";
    foreach ( $image_tags as $image_src) {
	   echo "\t<option value=\"$image_src\" selected>$image_src</option><br>\n";
    }

echo "</select>\n";

/* REVISIT - it'd be nice to have these functions get shoved
  into util.php at some time - maybe added functionality to the makeFormIntro? */ 
?>

<p>
<?php 
	echo insertFormJSLinks('urls[]'); 
?>
</p>
<?php if (count($info_tags)) { ?>
<span>
<?php
	processingMsg(sprintf(_("%d meta file(s) found.  These files contain information about the images, such as titles and descriptions."), count($info_tags)));
?>
</span>
<p>
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
<p>
<?php
	echo insertFormJSLinks('meta[]');
?>
<?php } /* end if (count($info_tags)) */ ?>
<p>
<input type="hidden" name="setCaption" value="<?php echo isset($setCaption) ? $setCaption : '' ?>">
<input type="hidden" name="wmName" value="<?php echo $wmName ?>">
<input type="hidden" name="wmAlign" value="<?php echo $wmAlign ?>">
<input type="hidden" name="wmAlignX" value="<?php echo $wmAlignX ?>">
<input type="hidden" name="wmAlignY" value="<?php echo $wmAlignY ?>">
<input type="hidden" name="wmSelect" value="<?php echo $wmSelect ?>">
<input type="button" value="<?php echo _("Add Files") ?>" onClick="parent.opener.showProgress(); document.uploadurl_form.submit()">
</p>

</form>
</div>
</div>
<?php } /* End if links slurped */ ?>
</div>
</body>
</html>
