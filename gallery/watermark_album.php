<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2004 Bharat Mediratta
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
$highlightIndex = $gallery->album->getHighlight();

$err = "";	
if (isset($save) || isset($preview)) {
	if (isset($wmAlign) && ($wmAlign > 0) && ($wmAlign < 12)) {
		if (isset($wmName) && !empty($wmName)) {
			if (isset($save)) {
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
				// create a preview of the highlight image
				$gallery->album->watermarkPhoto($highlightIndex, $wmName, "", $wmAlign,
                                                       isset($wmAlignX) ? $wmAlignX : 0,
                                                       isset($wmAlignY) ? $wmAlignY : 0,
                                                       1, // set as preview
                                                       isset($previewFull) ? $previewFull : 0);

			}
		} else {
			$err = _("Please select a watermark.");
		}
	} else {
		$err = _("Please select an alignment.");
	}
} else {
	if (!isset($recursive)) {
	        $recursive = 1;
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
if (!$gallery->album->numPhotos(1)) {
	echo "\n<p>". gallery_error(_("No items to watermark.")) . "</p>";
} else {
   $highlightIndex = $gallery->album->getHighlight();
   if (isset($highlightIndex)) {
      if (isset($preview)) {
         echo $gallery->album->getPreviewTag($highlightIndex);
      } else {
         echo $gallery->album->getThumbnailTag($highlightIndex);
      }
   }

   if (!empty($err)) {
      echo "\n<p>". gallery_error($err) . "</p>";
   }
   echo makeFormIntro("watermark_album.php",
                      array("name" => "theform",
                            "method" => "POST"));
   global $watermarkForm;
   $watermarkForm["askRecursive"] = 1;
   $watermarkForm["askPreview"] = 1;
   $watermarkForm["allowNone"] = 0;
   includeLayout ('watermarkform.inc');
?>

<p>
	<input type="hidden" name="index" value="<?php echo $index ?>">
	<input type="submit" name="save" value="<?php echo _("Save") ?>">
<?php // only allow preview if there is a highlight
 if (isset($highlightIndex)) { ?>
	<input type="submit" name="preview" value="<?php echo _("Preview") ?>">
<?php } ?>
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
<?php } // end if numPhotos() ?>
<?php print gallery_validation_link("watermark_album.php"); ?>
</body>
</html>
