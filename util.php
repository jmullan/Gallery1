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
<?

function editField($album, $field, $edit) {
	global $app;

	$buf = $album->fields[$field];
	if (!strcmp($buf, "")) {
		$buf = "<i>&lt;Empty&gt;</i>";
	}
	if (isCorrectPassword($edit)) {
		$url = "$app->photoAlbumURL/edit_field.php?set_albumName={$album->fields[name]}&field=$field";
		$buf .= "<font size=1>";
		$buf .= "<a href=" . popup($url) . ">[edit $field]</a>";
		$buf .= "</font>";
	}
	return $buf;
}

function editCaption($album, $index, $edit) {
	global $app;

	$buf = $album->getCaption($index);
	if (isCorrectPassword($edit)) {
		if (!strcmp($buf, "")) {
			$buf = "<i>&lt;No Caption&gt;</i>";
		}
		$url = "$app->photoAlbumURL/edit_caption.php?set_albumName={$album->fields[name]}&index=$index";
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
	$attrs = "height=450,width=500,location=no,scrollbars=yes,menubars=no,toolbars=no,resizable=yes";
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
	global $app; 

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

function reload() {
	echo "<BODY onLoad='opener.location.reload()'>";
}

function dismissAndLoad($url) {
	echo("<BODY onLoad='opener.location = \"$url\"; parent.close()'>");
}

function resize_image($src, $dest, $target) {
	global $app;				

	exec("$app->pnmDir/anytopnm $src | " .
	     "$app->pnmDir/pnmscale -xysize $target $target | ".
	     "$app->pnmDir/ppmtojpeg > $dest");

	if (file_exists("$dest") && filesize("$dest") > 0) {
		return 1;
	} else {
		return 0;
	}
}
