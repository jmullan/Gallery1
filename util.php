<?

function editField($album, $field, $edit) {
	$buf = $album->fields[$field];
	if (!strcmp($buf, "")) {
		$buf = "<i>&lt;Empty&gt;</i>";
	}
	if (isCorrectPassword($edit)) {
		$url = "edit_field.php?set_albumName={$album->fields[name]}&field=$field";
		$buf .= "<font size=1>";
		$buf .= "<a href=" . popup($url) . ">[edit]</a>";
		$buf .= "</font>";
	}
	return $buf;
}

function editCaption($album, $index, $edit) {
	$buf = $album->getCaption($index);
	if (isCorrectPassword($edit)) {
		if (!strcmp($buf, "")) {
			$buf = "<i>&lt;No Caption&gt;</i>";
		}
		$url = "edit_caption.php?set_albumName={$album->fields[name]}&index=$index";
		$buf .= "<font size=1>";
		$buf .= "<a href=" . popup($url) . ">[edit]</a>";
		$buf .= "</font>";
	}
	return $buf;
}

function error($message) {
	echo "<H1>Error: $message</H1>";
}

function popup($url) {
	$attrs = "height=350,width=500,location=no,scrollbars=yes,menubars=no,toolbars=no,resizable=yes";
	return "javascript:void(open('$url','Edit','$attrs'))";
}

function loadJpeg ($imgname) {
	$im = ImageCreateFromJPEG ($imgname); /* Attempt to open */
	if ($im == "") { /* See if it failed */
		$im = ImageCreate (150, 30); /* Create a blank image */
		$bgc = ImageColorAllocate ($im, 255, 255, 255);
		$tc  = ImageColorAllocate ($im, 0, 0, 0);
		ImageFilledRectangle ($im, 0, 0, 150, 30, $bgc);
		/* Output an errmsg */
		ImageString ($im, 1, 5, 5, "Error loading $imgname", $tc); 
	}
	return $im;
}

function loadImage($dir, $name, $tag) {
	if (!strcmp($tag, "jpg")) {
		$img = loadJpeg("$dir/$name.$tag");
	} elseif (!strcmp($tag, "png")) {
		$img = ImageCreateFromPng("$dir/$name.$tag");
	}
	return $img;
} 

function selectOptions($album, $field, $opts) {
	foreach ($opts as $opt) {
		$sel = "";
		if (!strcmp($opt, $album->fields[$field])) {
			$sel = "selected";
		}
		echo "<option $sel>$opt";
	}
}

function acceptableFormat($tag) {
	return (isImage($tag) || isMovie($tag));
}

function isImage($tag) {
	return (!strcmp($tag, "jpg") ||
		!strcmp($tag, "gif") ||
		!strcmp($tag, "png"));
}

function isMovie($tag) {
	return (!strcmp($tag, "avi") ||
		!strcmp($tag, "mpg"));
}

function isCorrectPassword($pass) {
	global $app;

	return (!strcmp($app->editPassword, $pass));
}

function editMode() {
	global $edit;
	return (isCorrectPassword($edit));
}

function getFile($fname) {
	$tmp = "";

	if (!file_exists($fname)) {
		return $tmp;
	}

	if ($fd = fopen($fname, "r")) {
		while (!feof($fd)) {
			$tmp .= fread($fd, 65536);
		}
		fclose($fd);
	}
	return $tmp;
}

function dismissAndReload() {
	echo "<BODY onLoad='opener.location.reload(); parent.close()'>";
}

function reload($url) {
	echo "<BODY onLoad='opener.location.reload()'>";
}

function dismissAndLoad($url) {
	echo("<BODY onLoad='opener.location = \"$url\"; parent.close()'>");
}
?>