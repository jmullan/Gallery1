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
<? require('style.php') ?>

<center>
<?
if ($url) {
	$file = basename($url);
	$tag = ereg_replace(".*\.([^\.]*)$", "\\1", $url);
	$tag = strtolower($tag);

	/* copy file locally */
	$urlFile = "$app->tmpDir/photo.$file";
	$id = fopen($url, "r");
	$od = fopen($urlFile, "w");
	echo ("Downloading<br>$url<br>"); flush(); 
	$msgcount++;
	if ($id && $od) {
		while (!feof($id)) {
			fwrite($od, fread($id, 65536));
			set_time_limit(30);
		}
		fclose($id);
		fclose($od);
	}

	/* Tack it onto userfile */
	$userfile_name[] = $file;
	$userfile[] = $urlFile;
}

?>
<br>
Processing files...
</center>
<ul>
<?

while (sizeof($userfile)) {
	$name = array_shift($userfile_name);
	$file = array_shift($userfile);

	$tag = ereg_replace(".*\.([^\.]*)$", "\\1", $name);
	$tag = strtolower($tag);

	if (!strcmp($tag, "zip")) {
		if (!$app->feature["zip_support"]) {
			echo "<li> Skipping $name (ZIP support not enabled)";
			$msgcount++;
			continue;
		}
		/* Figure out what files we can handle */
		exec("$app->zipinfo -1 $file", $files);
		foreach ($files as $pic_path) {
			$pic = basename($pic_path);
			$tag = ereg_replace(".*\.([^\.]*)$", "\\1", $pic);
			$tag = strtolower($tag);

			if (acceptableFormat($tag)) {
				exec("$app->unzip -j -o $file '$pic_path' -d $app->tmpDir");
				process("$app->tmpDir/$pic", $tag, $pic);
				unlink("$app->tmpDir/$pic");
			}
		}
	} else {
		if ($name) {
			process($file, $tag, $name);
			$msgcount++;
		}
	}
}

/* Clean up the temporary url file */
if ($urlFile) {
	unlink($urlFile);
}

function process($file, $tag, $name) {
	global $album;

	set_time_limit(30);
	if (acceptableFormat($tag)) {
		echo "<li> Adding $name";
		$album->addPhoto($file, $tag);
	} else {
		echo "<li> Skipping $name (can't handle '$tag' format)";
	}
	flush();
}

$album->save();
if (!$msgcount) {
	dismissAndReload();
} else {
	reload();
?>
	<center>
	<form>
	<input type=submit value="Dismiss" onclick='parent.close()'>
	</form>
<?
}
?>
