<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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
require_once(dirname(dirname(__FILE__)) . '/init.php');

list($urls, $meta, $usercaption, $setCaption) =
	getRequestVar(array('urls', 'meta', 'usercaption','setCaption'));

list($wmName, $wmAlign, $wmAlignX, $wmAlignY, $wmSelect) =
	getRequestVar(array('wmName', 'wmAlign', 'wmAlignX', 'wmAlignY', 'wmSelect'));

// Hack check
if (!$gallery->user->canAddToAlbum($gallery->album)) {
	printPopupStart(clearGalleryTitle(gTranslate('core', "Add items")));
	showInvalidReqMesg(gTranslate('core', "You are not allowed to perform this action!"));
	exit;
}

//cleanup $_FILES as from FORM upload one additional trash file is sent.
if(!empty($_FILES)) {
	$uploadTry = true;
	foreach ($_FILES as $name => $attribs) {
		foreach($attribs['size'] as $nr => $value) {
			if($value == 0) {
				foreach($attribs as $attrib => $trash)
				unset($_FILES[$name][$attrib][$nr]);
			}
		}
		if(empty($_FILES[$name]['name'])) {
			unset($_FILES[$name]);
		}
	}
}

$image_tags	= array();
$info_tags	= array();

doctype();
?>
<html>
<head>
  <title><?php echo gTranslate('core', "Processing and Saving Photos") ?></title>
  <?php common_header(); ?>
</head>
<body class="g-popup" onLoad="parent.opener.hideProgressAndReload();">
<div class="g-header-popup">
  <div class="g-pagetitle-popup"><?php echo gTranslate('core', "Fetching Urls...") ?></div>
</div>

<?php
// Were an Url given ?
if (!empty($urls)) {
	echo '<div class="g-content-popup center">';

	/* Process all urls first.
	* $urls contains all URLs given by the "URL Upload".
	* $urls should be empty when using the "Form Upload".
	*/
	$messages = array();

	foreach ($urls as $url) {
		/* Get rid of any extra white space */
		$url = trim($url);

		/*
		 * Check to see if the URL is a local directory (inspired by code from Jared (hogalot))
		 */
		if (fs_is_dir($url)) {
			echo infobox(array(array(
				'type' => 'information',
				'text' => sprintf(gTranslate('core', "Processing '%s' as a local directory."),
							'<i>' . htmlspecialchars(strip_tags(urldecode($url))) . '</i>')
			)));

			$handle = fs_opendir($url);
			if($handle) {
				while (($file = readdir($handle)) != false) {
					if ($file != "." && $file != "..") {
						$tag = pathinfo($file);
						$tag = strtolower(isset($tag['extension']) ? $tag['extension'] : '');
						if (acceptableFormat($tag) || canDecompressArchive($tag)) {
							/* File seems to be valid, so add to userfile */
							if (substr($url,-1) == "/") {
								$image_tags[] = fs_export_filename($url . $file);
							}
							else {
								$image_tags[] = fs_export_filename($url . "/" . $file);
							}
						}
						if ($tag == 'csv') {
							if (substr($url,-1) == "/") {
								$info_tags[] = fs_export_filename($url . $file);
							}
							else {
								$info_tags[] = fs_export_filename($url . "/" . $file);
							}
						}
					}
				}
				closedir($handle);
			}
			continue;
		}
		// $url is not a dir
		else {
			echo infoBox(array(array(
				'type' => 'information',
				'text' => sprintf(gTranslate('core', "Processing '%s' as file or URL."),
							'<i>' . htmlspecialchars(strip_tags(urldecode($url))) . '</i>')
			)));
		}

		$urlParts		= parse_url($url);
		$urlPathInfo	= isset($urlParts['path']) ? pathinfo($urlParts['path']) : '';
		$urlExt			= isset($urlPathInfo['extension']) ? strtolower($urlPathInfo['extension']) : '';

		/* If the URI doesn't start with a scheme, prepend 'http://' */
		if (!empty($url) && !fs_is_file($url)) {
			if (!ereg("^(http|ftp)", $url)) {
				echo infoBox(array(array(
					'type' => 'warning',
					'text' => sprintf(gTranslate('core', 'Unable to find %s locally - trying %s.'),
							  htmlspecialchars(strip_tags(urldecode($url))), 'http')
				)));
				$url = "http://$url";
			}

			/* Parse URL for name and file type */
			$url_stuff = @parse_url($url);
			if (!isset($url_stuff["path"])) {
				$url_stuff["path"] = "";
			}

			$name = basename($url_stuff["path"]);
		}
		else {
			debugMessage(gTranslate('core', 'local file'), __FILE__, __LINE__, 2);
			$name = basename($url);
		}

		/*
		 * Try to open the url in lots of creative ways.
		 * Do NOT use fs_fopen here because that will pre-process
		 * the URL in win32 style (ie, convert / to \, etc).
		 */
		$urlArray = array($url, "$url/");
		if (!ereg("http", $url)) {
			$urlArray[] = "http://$url";
			$urlArray[] = "http://$url/";
		}

		// Dont output warning messages if we cant open url
		do {
			$tryUrl = array_shift($urlArray);
			$id = @fopen($tryUrl, "rb");
		}
		while (!$id && !empty($urlArray));

		if (!$id) {
			echo infoBox(array(array(
				'type' => 'error',
				'text' => gTranslate('core', "Could not open as URL, file or directory.")
			)));
			continue;
		}
		else {
			debugMessage(sprintf(gTranslate('core', "Opened '%s' successfully"), $url), __FILE__, __LINE__, 2);
		}

		/*
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
			$temp_files[$file] = 1;

			/* Add it to userfile */
			$_FILES['userfile']['name'][]		= $name;
			$_FILES['userfile']['tmp_name'][]	= $file;
			debugMessage(gTranslate('core', "Copy file locally"), __FILE__, __LINE__, 2);
		}
		else {
			/* Slurp the file */
			processingMsg(sprintf(gTranslate('core', "Parsing %s for images..."), $url));
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
			}
			else {
				$base_dir = dirname($url_stuff["path"]);
			}

			/* Make sure base_dir ends in a / ( accounts for empty base_dir ) */
			if (!ereg("/$", $base_dir)) {
				$base_dir .= '/';
			}

			$things	 = array();
			$results = array();

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
				if (substr($thing, 0, 2) == '//') {
					$thing = "http:$thing";
				}

				/* Absolute Link ( http://www.foo.com/bar ) */
				if (substr($thing, 0, 4) == 'http') {
					$image_tags[] = $thing;
				}
				/* Relative link to the host ( /foo.bar )*/
				elseif (substr($thing, 0, 1) == '/') {
					$image_tags[] = $base_url . $thing;
				}
				/* Relative link to the dir ( foo.bar ) */
				else {
					$image_tags[] = $base_url . $base_dir . $thing;
				}
			}

			/* Tell user how many links we found, but delay processing */
			processingMsg(sprintf(gTranslate('core', "Found %d images"), count($image_tags)));
		}
	}
} /* if ($urls) */

// Begin Metadata fetching and preprocessing
$image_info = array();
// Get meta data
if (isset($meta)) {
	echo infoBox(array(array(
		'type' => 'information',
		'text' => gTranslate('core',"Metainfo found.")
	)));

	foreach ($meta as $data) {
		$image_info = array_merge($image_info, parse_csv(fs_export_filename($data),";"));
	}
}

if(!empty($_FILES['metafile'])) {
	if (!isset($meta) || isDebugging()) {
		echo infoBox(array(array(
			'type' => 'information',
			'text' => gTranslate('core',"Metainfo found")
		)));
	}

	$image_info = array();

	for($i = 0; $i < sizeof($_FILES['metafile']['name']); $i++) {
		$name = $_FILES['metafile']['name'][$i];
		echo debugMessage("name $name", __FILE__, __LINE__);

		$file = $_FILES['metafile']['tmp_name'][$i];
		echo debugMessage("file $file", __FILE__, __LINE__);

		// image_info is the array that contains the parsed from the csv file(s)
		$image_info = array_merge($image_info, parse_csv(fs_export_filename($file),";"));
	}

	$exampleMetaData = $image_info[0];

	// Find the key of the file name field
	foreach (array_keys($exampleMetaData) as $currKey) {
		if (eregi("^\"?file\ ?name\"?$", $currKey)) {
			$filenameKey = $currKey;
		}
	}

	if(!isset($filenameKey)) {
		echo gallery_error(sprintf(gTRanslate('core', "Filename-column not found! CSV data not valid and is not used.")));
	}

	/* $captionMetaFields is an array that containes possible fields for the caption.
	 * Ordered in priority from high to low.
	 */
	$captionMetaFields = array("Caption", "Title", "Description");
}
// End Metadata preprocessing

$upload_started = false;

echo "\n</div>";

echo debugMessage("Now we start processing the given Files. (If they were given)", __FILE__, __LINE__,1);

$photoCount = isset($_FILES['userfile']) ? sizeof($_FILES['userfile']['name']) : 0;

if(isset($uploadTry) || $photoCount > 0) {
	echo '<div class="g-content-popup left">';

		echo infoBox(array(array(
			'type' => ($photoCount > 0) ? 'information' : 'error',
			'text' => gTranslate('core',
				"Processing %d element.",
				"Processing %d elements.",
				$photoCount,
				'Error, no photo uploaded.', true)
		)));

	if($photoCount > 0) {
		for($i = 0; $i < $photoCount; $i++) {
			$upload_started = true;

			$name = $_FILES['userfile']['name'][$i];
			$file = $_FILES['userfile']['tmp_name'][$i];

			if (!empty($usercaption) && is_array($usercaption)) {
				$caption = array_shift($usercaption);
			}

			if (!isset($caption)) {
				$caption = '';
			}

			$extra_fields = array();
			if (!isset($setCaption)) {
				$setCaption = '';
			}

			if(isset($filenameKey)) {
				// Find in meta data array
				foreach ($image_info as $line) {
					if ($line[$filenameKey] == $name) {
						// Loop through fields
						foreach ($captionMetaFields as $field) {
							// If caption isn't populated and current field is
							if (empty($caption) && !empty($line[$field])) {
								$caption = $line[$field];
								unset($line[$field]);
							}
						}
						$extra_fields = $line;
						if(isDebugging()) {
							echo gTranslate('common', "Extra fields:");
							print_r($extra_fields);
						}
					}
				}
			}

			$path_parts = pathinfo($name);
			$ext = strtolower($path_parts["extension"]);

			// Add new image
			processNewImage($file, $ext, $name, $caption, $setCaption, $extra_fields, $wmName, $wmAlign, $wmAlignX, $wmAlignY, $wmSelect);
		}

		$gallery->album->save(array(i18n("%d files uploaded"), $photoCount));
	}

	if (!empty($temp_files)) {
		/* Clean up the temporary url file */
		foreach ($temp_files as $tf => $junk) {
			fs_unlink($tf);
		}
	}

	echo "\n</div>";
}

echo '<div class="g-content-popup center">';

if (empty($photoCount) && $upload_started) {
	print gTranslate('core', "No images uploaded!");
}

echo "\n<br>";
echo gButton('close', gTranslate('core', "_Dismiss"), 'parent.close()');

/* Prompt for additional files if we found links in the HTML slurpage */
if (count($image_tags)) {
	/*
	 * include JavaScript (de)selection and invert
	 */
	insertFormJS('uploadurl_form');

	echo "\n<p>". insertFormJSLinks('urls[]') ."</p>\n";

	echo '<div class="left">';
	echo gTranslate('core', "Select the items you want to upload. To select multiple hold 'ctrl' (PC) or 'Command' (Mac).");
	echo "\n</div>";

	echo makeFormIntro("save_photos.php",
	array('name' => 'uploadurl_form'),
	array('type' => 'popup'));

	/* Allow user to select which files to grab - only show url right now ( no image previews ) */
	sort($image_tags);
	$selectSize = (sizeof($image_tags) > 20) ? 20 : sizeof($image_tags);

	echo '<select name="urls[]" multiple="multiple" size="'. $selectSize ."\">\n";
	foreach ( $image_tags as $image_src) {
		echo "\t<option value=\"$image_src\" selected>$image_src</option>\n";
	}

	echo "</select>\n";

	/* REVISIT - it'd be nice to have these functions get shoved
	 * into util.php at some time - maybe added functionality to the makeFormIntro?
	 */
	echo "\n<p>". insertFormJSLinks('urls[]') ."</p>";

	if (count($info_tags)) { ?>
<div class="g-content-popup left">
<?php
printf(gTranslate('core', "%d meta file(s) found.  These files contain information about the images, such as titles and descriptions."), count($info_tags));
?>
</div>
<?php
		echo insertFormJSLinks('meta[]');
		echo "\n<br><br>";

		foreach ($info_tags as $info_tag) {
			echo "\t<input type=\"checkbox\" name=\"meta[]\" value=\"$info_tag\" checked>$info_tag<br>\n";
		}

		echo "\n<br>";

		echo insertFormJSLinks('meta[]');
	}
	/* end if (count($info_tags)) */
?>

<p>
<input type="hidden" name="setCaption" value="<?php echo isset($setCaption) ? $setCaption : '' ?>">
<input type="hidden" name="wmName" value="<?php echo $wmName ?>">
<input type="hidden" name="wmAlign" value="<?php echo $wmAlign ?>">
<input type="hidden" name="wmAlignX" value="<?php echo $wmAlignX ?>">
<input type="hidden" name="wmAlignY" value="<?php echo $wmAlignY ?>">
<input type="hidden" name="wmSelect" value="<?php echo $wmSelect ?>">
<?php echo gButton('addFiles', gTranslate('core', "_Add Files"), 'parent.opener.showProgress(); document.uploadurl_form.submit()'); ?>
</p>

</form>

<?php
} /* End if links slurped */

?>
</div>
</body>
</html>
