<?
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2002 Bharat Mediratta
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
// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
	print "Security violation\n";
	exit;
}
?>
<? require($GALLERY_BASEDIR . "init.php"); ?>
<?
function image($name) {
	global $GALLERY_BASEDIR;
	return $GALLERY_BASEDIR . "images/$name";
}
?>
<html>
<head>
  <title>Uploading Photos</title>
  <?= getStyleSheetLink() ?>
</head>

<body>
<center>
<span class="title">File upload in progress!</span>
<p>
This page will go away automatically when the upload is complete.  Please be patient!
<p>
<table border=0 cellpadding=0 cellspacing=0>
 <tr>
  <td> <img src=<?=image("computer.gif")?> width=31 height=32> </td>
  <td> <img src=<?=image("uploading.gif")?> width=160 height=11> </td>
  <td> <img src=<?=image("computer.gif")?> width=31 height=32> </td>
 </tr>
</table>

</center>

</script>
</body>
