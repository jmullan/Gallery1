<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2005 Bharat Mediratta
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
  global $watermarkForm;
  global $wmAlign;
  global $wmAlignX;
  global $wmAlignY;
  global $previewFull;
  global $wmName;

  $err = "";	
  if (!isset($wmAlignX) || ($wmAlignX <= 0))
  {
    $wmAlignX = 0;
  }
  if (!isset($wmAlignY) || ($wmAlignY <= 0))
  {
    $wmAlignY = 0;
  }
  if (!isset($wmName))
  {
    $wmName = "";
  }
  if (!isset($wmAlign)) {
    $wmAlign=9;
  }
  if (!isset($previewFull)) {
    $previewFull=0;
  }
  if (!isset($wmSelect)) {
    $wmSelect = $gallery->app->watermarkSizes;
  }
?>
<p>
<?php
   
if (!isset($wmAlign)) {
	$wmAlign = 0;
}

// get a list of watermarks
if ($wmDir = opendir($gallery->app->watermarkDir)) { 
	$found = 0;
	$extensions = array ("png", "gif", "tif");
	$extensions_string = "png, gif, tif";
	while ($wmFile = readdir($wmDir)) {
		$matches_ext = 0;
		foreach ($extensions as $ext) {
			if (eregi("\\.$ext$", $wmFile, $regs)) {
				$matches_ext = 1;
			}
		}
		if ($matches_ext) {
			$found++;
			if ($found == 1) {
				// Start select form element on first matching file
				print "<b>" . _("Watermark") . ":</b>&nbsp;";
				print "<select name=\"wmName\">\n";
				if ($watermarkForm["allowNone"]) {
					print '<option label="' . _("None") . '" value=""></option>' . "\n";
				}
			}
			print "\t<option ";
			if ($wmName == $wmFile) {
				print "selected ";
			}
			print "label=\"".$wmFile."\" value=\"".$wmFile."\">".$wmFile."</option>\n";
		} // end if ($matches_ext)
	} // end while ($wmFile
	closedir($wmDir);

	if ($found == 0) {
		$err=sprintf(_("No files matching %s found in %s"),
			"<b>" . $extensions_string . "</b>",
			"<b>" . $gallery->app->watermarkDir . "</b>");
		echo gallery_error($err);
	} else {
		print "</select>\n";
	}
} else {
	$err=sprintf (_("Could not open directory %s"), $gallery->app->watermarkDir);
	echo gallery_error($err);
}
?>
</p>

<p><b><?php echo _("Alignment") ?>:</b></p>
<table border="1" width="100%">
<tr>
 <td><input <?php if ($wmAlign==1) echo " checked"; ?> type="radio" name="wmAlign" value="1">
     <?php echo _("Upper&nbsp;Left") ?></td>
 <td><input <?php if ($wmAlign==2) echo " checked"; ?> type="radio" name="wmAlign" value="2">
     <?php echo _("Top") ?></td>
 <td><input <?php if ($wmAlign==3) echo " checked"; ?> type="radio" name="wmAlign" value="3">
     <?php echo _("Upper&nbsp;Right") ?></td>
</tr>
<tr>
 <td><input <?php if ($wmAlign==4) echo " checked"; ?> type="radio" name="wmAlign" value="4">
     <?php echo _("Left") ?></td>
 <td><input <?php if ($wmAlign==5) echo " checked"; ?> type="radio" name="wmAlign" value="5">
     <?php echo _("Center") ?></td>
 <td><input <?php if ($wmAlign==6) echo " checked"; ?> type="radio" name="wmAlign" value="6">
     <?php echo _("Right") ?></td>
</tr>
<tr>
 <td><input <?php if ($wmAlign==7) echo " checked"; ?> type="radio" name="wmAlign" value="7">
     <?php echo _("Lower&nbsp;Left") ?></td>
 <td><input <?php if ($wmAlign==8) echo " checked"; ?> type="radio" name="wmAlign" value="8">
     <?php echo _("Bottom") ?></td>
 <td><input <?php if ($wmAlign==9) echo " checked"; ?> type="radio" name="wmAlign" value="9">
     <?php echo _("Lower&nbsp;Right") ?></td>
</tr>
<tr>
  <td colspan="3">
    <input <?php if ($wmAlign==10) echo " checked"; ?> type="radio" name="wmAlign" value="10">
    <?php echo _("Other") ?>
    X=<input type="text" name="wmAlignX" value="<?php echo $wmAlignX ?>">
    Y=<input type="text" name="wmAlignY" value="<?php echo $wmAlignY ?>">
  </td>
</tr>
</table>
<br>
<?php if ($watermarkForm["askPreview"] && $gallery->user->canViewFullImages($gallery->album)) { ?>
<b><?php echo _("Preview Size") ?>:</b><br>
<table border="0">
<tr><td><input type="radio" name="previewFull" value="0"<?php if ($previewFull==0) echo " checked"; ?>>
<?php echo _("Resized"); ?> </input></td></tr>
<tr><td><input type="radio" name="previewFull" value="1"<?php if ($previewFull==1) echo " checked"; ?>>
<?php echo _("Full"); ?> </input></td></tr>
</table>
<?php } // end if canViewFullImages ?>
<br>
<?php if ($watermarkForm["askRecursive"]) {
  print '<input type="checkbox" name="recursive"';
  global $recursive;
  if (isset($recursive)) echo " checked";
  print ">" . _("Watermark subalbums") . "</input><br><br>";
} ?>
<?php echo _("Apply watermark to") ?> <select name="wmSelect">
  <option value="0"<?php if ($wmSelect <  1) echo " selected"; ?>><?php echo _("All photos");?></option>
  <option value="1"<?php if ($wmSelect == 1) echo " selected"; ?>><?php echo _("Only sized photos");?></option>
  <option value="2"<?php if ($wmSelect >  1) echo " selected"; ?>><?php echo _("Only full photos");?></option>
</select>
<br>
