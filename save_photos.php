<?
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000 Bharat Mediratta
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
<? require($GALLERY_BASEDIR . "init.php"); ?>
<?
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

function msg($buf) {
	global $msgcount;

	if ($msgcount) {
		print "<br>";
	}
	print $buf;
	my_flush();
	$msgcount++;
}

?>
<html>
<head>
  <title>Processing and Saving Photos</title>
  <?= getStyleSheetLink() ?>

</head>
<body onLoad='opener.hideProgressAndReload();'>

<?
if ($urls) {
?>
<span class=title>Fetching Urls...</span>
<br>
<?
	/* Process all urls first */
	$temp_files = array();
	
	foreach ($urls as $url) {

		/*
		 * Check to see if the URL is a local directory (inspired by
		 * code from Jared (hogalot)
		 */
		if (fs_is_dir($url)) {
			msg("Processing <i>$url</i> as a local directory.");
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

		/* Parse URL for name and file type */
		$url_stuff = parse_url($url);
		$name = basename($url_stuff["path"]);
		$tag = ereg_replace(".*\.([^\.]*)$", "\\1", $url);
		$tag = strtolower($tag);
	
		/* Dont output warning messages if we cant open url */
	
		/*
		 * Try to open the url in lots of creative ways.
		 */
 		$id = @fs_fopen($url, "r");
		if (!ereg("http", $url)) {
			if (!$id) $id = @fs_fopen("http://$url");
			if (!$id) $id = @fs_fopen("http://$url/");
		}
		if (!$id) $id = @fs_fopen("$url/");

		if ($id) {
			msg(urldecode($url));
		} else {
			msg("Could not open url: '$url'");
			continue;
		} 
	
		/* copy file locally */
		$file = $gallery->app->tmpDir . "/photo.$name";
		$od = fs_fopen($file, "w");
		if ($id && $od) {
			while (!feof($id)) {
				fwrite($od, fread($id, 65536));
				set_time_limit(30);
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
			msg("Parsing $url for images...");
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
			foreach (split("[[:space:]\=\"\'\<\>\?]", $contents) as $value) {
				if (eregi("\." . acceptableFormatRegexp(), $value, $regs)) {
					$things[$value]++;
				}
			}

			/* Add each unique link to an array we scan later */
			foreach (array_keys($things) as $thing) {
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
			msg("Found " . count($image_tags) . " Images.");
		}
	}
} /* if ($urls) */
?>


<br>
<span class=title>Processing status...</span>
<br>

<?
while (sizeof($userfile)) {
	$name = array_shift($userfile_name);
	$file = array_shift($userfile);

	$tag = ereg_replace(".*\.([^\.]*)$", "\\1", $name);
	$tag = strtolower($tag);

	if ($name) {
		process($file, $tag, $name, $setCaption);
	}
}


function process($file, $tag, $name, $setCaption="") {
	global $gallery;

	if (!strcmp($tag, "zip")) {
		if (!$gallery->app->feature["zip"]) {
			msg("Skipping $name (ZIP support not enabled)");
			continue;
		}
		/* Figure out what files we can handle */
		list($files, $status) = exec_internal(
			fs_import_filename($gallery->app->zipinfo) . 
			" -1 " .
			fs_import_filename($file));
		sort($files);
		foreach ($files as $pic_path) {
			$pic = basename($pic_path);
			$tag = ereg_replace(".*\.([^\.]*)$", "\\1", $pic);
			$tag = strtolower($tag);

			if (acceptableFormat($tag) || !strcmp($tag, "zip")) {
				$cmd_pic_path = str_replace("[", "\[", $pic_path); 
				$cmd_pic_path = str_replace("]", "\]", $cmd_pic_path); 
				exec_wrapper(fs_import_filename($gallery->app->unzip) . 
					     " -j -o " .
					     fs_import_filename($file) .
					     " '" .
					     fs_import_filename($cmd_pic_path) .
					     "' -d " .
					     fs_import_filename($gallery->app->tmpDir));
				process($gallery->app->tmpDir . "/$pic", $tag, $pic, $setCaption);
				fs_unlink($gallery->app->tmpDir . "/$pic");
			}
		}
	} else {
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
	
		set_time_limit(30);
		if (acceptableFormat($tag)) {
			msg("- Adding $name");
			if ($setCaption) {
				$caption = $originalFilename;
			} else {
				$caption = "";
			}	
	
			$err = $gallery->album->addPhoto($file, $tag, $mangledFilename, $caption);
			if (!$err) {
				/* resize the photo if needed */
				if ($gallery->album->fields["resize_size"] > 0 && isImage($tag)) {
					$index = $gallery->album->numPhotos(1);
					$photo = $gallery->album->getPhoto($index);
					list($w, $h) = $photo->getDimensions();
					if ($w > $gallery->album->fields["resize_size"] ||
					    $h > $gallery->album->fields["resize_size"]) {
						msg("- Resizing $name"); 
						$gallery->album->resizePhoto($index, $gallery->album->fields["resize_size"]);
					}
				}
			} else {
				msg("<font color=red>Error: $err!</font>");
			}
		} else {
			msg("Skipping $name (can't handle '$tag' format)");
		}
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

<?
if (!$msgcount) {
	print "No images uploaded!";
}
?>
<center>
<form>
<input type=submit value="Dismiss" onclick='parent.close()'>
</form>
<?
/* Prompt for additional files if we found links in the HTML slurpage */
if (count($image_tags)) {
?>
<?= makeFormIntro("save_photos.php", 
		array("name" => "uploadurl_form", 
			"enctype" => "multipart/form-data", 
			"method" => "POST")); ?>
<table border=0>
<?
	/* Allow user to select which files to grab - only show url right now ( no image previews ) */
	sort($image_tags);
	foreach ( $image_tags as $image_src) {
		print "<tr><td><input type=checkbox name=\"urls[]\" value=\"$image_src\" checked>$image_src</td></tr>\n";
	}
?>
</table>
<input type=hidden name="setCaption" value="<?=$setCaption?>">
<input type=button value="Add Files" onClick="opener.showProgress(); document.uploadurl_form.submit()">
</form>
<? } /* End if links slurped */ ?>
</body>
</html>
