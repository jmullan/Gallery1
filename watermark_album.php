<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2003 Bharat Mediratta
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
 *
 * $Id$
 */
?>
<?php

require(dirname(__FILE__) . '/init.php');

// Hack check
if (!$gallery->user->canChangeTextOfAlbum($gallery->album)) {
	echo _("You are no allowed to perform this action !");
	exit;
}

if (empty($index)) {
	$index='';
}

if (!isset($recursive)) {
	$recursive = 1;
}

$err = "";	
if (isset($save)) {
	if (isset($wmAlign) && ($wmAlign > 0) && ($wmAlign < 12)) {
		if (isset($wmName) && !empty($wmName)) {
			print "<html><body>\n";
	                echo "<center> ". _("Watermarking album.")."<br>(". _("this may take a while"). ")</center>\n";

        	        my_flush();
                	set_time_limit($gallery->app->timeLimit);
	                $gallery->album->watermarkAlbum($wmName, "",
				$wmAlign, $wmAlignX, $wmAlignY, $recursive);
        	        $gallery->album->save();
                	dismissAndReload();
	                return;
		} else {
			$err = _("Please select a watermark.");
		}
	} else {
		$err = _("Please select an alignment.");
	}
}
doctype();
?>
<html>
<head>
  <title><?php echo _("Watermark Album") ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>">

<div align="center">
<p align="center" class="popuphead"><?php echo _("Watermark Album") ?></p>

<?php
if (!empty($err)) {
	echo "\n<p>". gallery_error($err) . "</p>";
}
   echo makeFormIntro("watermark_album.php",
                      array("name" => "theform",
                            "method" => "POST"));
   $askRecursive = 1;
   include (dirname(__FILE__). '/layout/watermarkform.inc') ;
?>

<p>
	<input type="hidden" name="index" value="<?php echo $index ?>">
	<input type="submit" name="save" value="<?php echo _("Save") ?>">
	<input type="button" name="cancel" value="<?php echo _("Cancel") ?>" onclick='parent.close()'>
</p>
</form>
</div>

<script language="javascript1.2" type="text/JavaScript">
<!--   
// position cursor in top form field
document.theform.data.focus();
//-->
</script>

<?php print gallery_validation_link("watermark_album.php"); ?>
</body>
</html>
