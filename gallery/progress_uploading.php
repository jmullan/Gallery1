<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2006 Bharat Mediratta
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
?>
<?php

require_once(dirname(__FILE__) . '/init.php');

function image($name) {
	return getImagePath("$name");
}

doctype();
?>
<html>
<head>
  <title><?php echo _("Uploading Photos") ?></title>
  <?php common_header(); ?>
</head>

<body dir="<?php echo $gallery->direction ?>" class="popupbody">
<div class="popuphead"><?php echo _("File upload in progress!") ?></div>
<div class="popup" align="center">
<?php echo _("This page will go away automatically when the upload is complete.  Please be patient!") ?>
<p>
<table border=0 cellpadding=0 cellspacing=0>
 <tr>
  <td> <img src="<?php echo image("computer.gif") ?>" width="31" height="32"> </td>
  <td> <img src="<?php echo image("uploading.gif") ?>" width="160" height="11"> </td>
  <td> <img src="<?php echo image("computer.gif") ?>" width="31" height="32"> </td>
 </tr>
</table>

</div>
</body>
</html>
