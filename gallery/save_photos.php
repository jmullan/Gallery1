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
<? require_once('init.php'); ?>
<?
// Hack check
if (!$user->canAddToAlbum($album)) {
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
	$msgcount++;
}

?>
<html>
<head>
  <title>Processing and Saving Photos</title>
  <link rel="stylesheet" type="text/css" href="<?= getGalleryStyleSheetName() ?>">
</head>
<body onLoad='opener.location.reload(); '>

<?
if ($urls) {
?>
<span class=title>Fetching Urls...</span>
<br>
<?
	/* Process all urls first */
	$temp_files = array();
	
	foreach ($urls as $url) {
	
		/* Get rid of any preceding whitespace (fix for odd browsers like konqueror) */
		$url = eregi_replace("^[[:space:]]+", "", $url);
	
		/* Parse URL for name and file type */
		$url_stuff = parse_url($url);
		$name = basename($url_stuff["path"]);
		$tag = ereg_replace(".*\.([^\.]*)$", "\\1", $url);
		$tag = strtolower($tag);
	
		/* Dont output warning messages if we cant open url */
		$id = @fopen($url, "r");
	
		/* Manual check - was the url accessible? */
		if (!$id) {
			msg("Could not open url: '$url'");
			continue;
		} else {
			msg("$url");
		}
	
		/* copy file locally */
		$file = "$app->tmpDir/photo.$name";
		$od = fopen($file, "w");
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
		if (acceptableFormat($tag)) {
			/* Tack it onto userfile */
			$userfile_name[] = $name;
			$userfile[] = $file;
		} else {
			/* Slurp the file */
			msg("Parsing $url for images...");
			$fd = fopen ($file, "r");
			$contents = fread ($fd, filesize ($file));
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
	
			/* Perl Regex: Find all src= and href= links to valid file types */
			if(preg_match_all ('/(src|href)="?([^" >]+\.' .
					acceptableFormatRegexp() .
					')[" >]/is', $contents, $things, PREG_PATTERN_ORDER)) {
	
				/* Add each unique link to an array we scan later */
				foreach (array_unique($things[2]) as $thing) {
	
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

	if (!strcmp($tag, "zip")) {
		if (!$app->feature["zip"]) {
			msg("Skipping $name (ZIP support not enabled)");
			continue;
		}
		/* Figure out what files we can handle */
		list($files, $status) = exec_internal("$app->zipinfo -1 $file");
		sort($files, SORT_NUMERIC);
		foreach ($files as $pic_path) {
			$pic = basename($pic_path);
			$tag = ereg_replace(".*\.([^\.]*)$", "\\1", $pic);
			$tag = strtolower($tag);

			if (acceptableFormat($tag)) {
				$cmd_pic_path = str_replace("[", "\[", $pic_path); 
				$cmd_pic_path = str_replace("]", "\]", $cmd_pic_path); 
				exec_wrapper("$app->unzip -j -o $file '$cmd_pic_path' -d $app->tmpDir");
				process("$app->tmpDir/$pic", $tag, $pic);
				unlink("$app->tmpDir/$pic");
			}
		}
	} else {
		if ($name) {
			process($file, $tag, $name);
		}
	}
}


function process($file, $tag, $name) {
	global $album;

	set_time_limit(30);
	if (acceptableFormat($tag)) {
		msg("- Adding $name");
		my_flush();

		$err = $album->addPhoto($file, $tag);
		if (!$err) {
			/* resize the photo if needed */
			if ($album->fields["resize_size"] > 0 && isImage($tag)) {
				$index = $album->numPhotos(1);
				$photo = $album->getPhoto($index);
				list($w, $h) = $photo->getDimensions();
				if ($w > $album->fields["resize_size"] ||
				    $h > $album->fields["resize_size"]) {
					msg("- Resizing $name"); 
					my_flush();
					$album->resizePhoto($index, $album->fields["resize_size"]);
				}
			}
		} else {
			print "<font color=red>Error: $err!</font>";
		}
	} else {
		msg("Skipping $name (can't handle '$tag' format)");
		my_flush();
	}
}

$album->save();

if ($temp_files) {
	/* Clean up the temporary url file */
	foreach ($temp_files as $tf => $junk) {
		unlink($tf);
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
<form enctype="multipart/form-data" action="save_photos.php" method=post name="uploadurl_form">
<table border=0>
<?
	/* Allow user to select which files to grab - only show url right now ( no image previews ) */
	foreach ( $image_tags as $image_src) {
		print "<tr><td><input type=checkbox name=\"urls[]\" value=\"$image_src\" checked>$image_src</td></tr>\n";
	}
?>
</table>
<input type=submit value="Add Files" onClick="opener.showProgress(); document.uploadurl_form.submit()">
</form>
<? } /* End if links slurped */ ?>
</body>
</html>
