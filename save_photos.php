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
	if ($id && $od) {
		while (!feof($id)) {
			fwrite($od, fread($id, 65536));
			set_time_limit(30);
		}
		fclose($id);
		fclose($od);
	}

	/* Tack it onto userfile */
	$userfile[] = $file;
	$userfile[] = "";
	$userfile[] = $urlFile;
}

?>
<br>
Processing files...
<ul>
<?

while (sizeof($userfile)) {
	$name = array_shift($userfile_name);
	$file = array_shift($userfile);

	$tag = ereg_replace(".*\.([^\.]*)$", "\\1", $name);
	$tag = strtolower($tag);

	if (!strcmp($tag, "zip")) {
		/* Figure out what files we can handle */
		exec("$app->zipinfo -1 $file", $files);
		foreach ($files as $pic_path) {
			$pic = basename($pic_path);
			$tag = ereg_replace(".*\.([^\.]*)$", "\\1", $pic);
			$tag = strtolower($tag);

			if (acceptableFormat($tag)) {
				exec("$app->unzip -j -o $file '$pic_path' -d $app->tmpDir");
				process("$app->tmpDir/$pic", $tag);
				unlink("$app->tmpDir/$pic");
			}
		}
	} else {
		if ($name) {
			process($file, $tag);
		}
	}
}

/* Clean up the temporary url file */
if ($urlFile) {
	unlink($urlFile);
}

function process($file, $tag) {
	global $album;

	set_time_limit(30);
	if (acceptableFormat($tag)) {
		$name = basename($file);
		echo "<li> Adding $name";
		$album->addPhoto($file, $tag);
	} else {
		echo "<li> Skipping $name (can't handle '$tag' format)";
	}
	flush();
}

$album->save();
dismissAndReload();
?>
