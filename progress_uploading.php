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
<? require('init.php'); ?>
<html>
<head>
  <title>Uploading Photos</title>
  <link rel="stylesheet" type="text/css" href="<?= getGalleryStyleSheetName() ?>">
</head>

<body onload='start_animation()'>
<center>
<span class="title">File upload in progress!</span>
<p>
This page will go away automatically when the upload is complete.  Please be patient!
<p>
<table border=0 cellpadding=0 cellspacing=0>
 <tr>
  <td> <img src=images/computer.gif width=31 height=32> </td>
  <td> <img src=images/pixel_trans.gif width=8 height=11> </td>
  <td> <img src=images/pixel_trans.gif width=8 height=11> </td>
  <td> <img src=images/pixel_trans.gif width=8 height=11> </td>
  <td> <img src=images/pixel_trans.gif width=8 height=11> </td>
  <td> <img src=images/pixel_trans.gif width=8 height=11> </td>
  <td> <img src=images/pixel_trans.gif width=8 height=11> </td>
  <td> <img src=images/pixel_trans.gif width=8 height=11> </td>
  <td> <img src=images/pixel_trans.gif width=8 height=11> </td>
  <td> <img src=images/pixel_trans.gif width=8 height=11> </td>
  <td> <img src=images/pixel_trans.gif width=8 height=11> </td>
  <td> <img src=images/pixel_trans.gif width=8 height=11> </td>
  <td> <img src=images/pixel_trans.gif width=8 height=11> </td>
  <td> <img src=images/pixel_trans.gif width=8 height=11> </td>
  <td> <img src=images/pixel_trans.gif width=8 height=11> </td>
  <td> <img src=images/pixel_trans.gif width=8 height=11> </td>
  <td> <img src=images/pixel_trans.gif width=8 height=11> </td>
  <td> <img src=images/pixel_trans.gif width=8 height=11> </td>
  <td> <img src=images/pixel_trans.gif width=8 height=11> </td>
  <td> <img src=images/pixel_trans.gif width=8 height=11> </td>
  <td> <img src=images/pixel_trans.gif width=8 height=11> </td>
  <td> <img src=images/computer.gif width=31 height=32> </td>
 </tr>
</table>

</center>

<script language="javascript1.2">
// <!--
var start_pad = 2;
var end_pad = 2;
var sel = 0;
var mod = 3;
var timer;
function animate() {

	for (var i = start_pad; i < document.images.length - end_pad; i++) {
		if (i % mod == sel) {
			document.images[i].src = "images/nav_dot_left.gif";
		} else {
			document.images[i].src = "images/pixel_trans.gif";
		}
	}

	sel++;
	if (sel == mod) {
		sel = 0;
	}

	start_animation();
}

function start_animation() {
	timer=window.setTimeout("animate();",250);
}

function stop_animation() {
	window.clearTimeout(timer);
}

// -->
</script>
</body>