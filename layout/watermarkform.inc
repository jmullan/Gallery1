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
	$wmAlign=0;
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
				print "<b>" . _("Watermark") . ":</b>";
				print "<select name=\"wmName\">\n";
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
		$err=printf(_("Error: No files matching %s found in %s"),
			"<b>" . $extensions_string . "</b>",
			"<b>" . $gallery->app->watermarkDir . "</b>");
		echo gallery_error($err);
	} else {
		print "</select>\n";
	}
} else {
	$err=printf (_("Could not open directory %s"), $gallery->app->watermarkDir);
	echo gallery_error($err);
}
?>
</p>

<b><?php echo _("Alignment") ?>:</b><br>
<table name="watermark_alignment" border="1" width="100%">
<tr>
 <td><input <?php if ($wmAlign==1) echo checked ?> type="radio" name="wmAlign" value="1">
     <?php echo _("Upper&nbsp;Left") ?></input></td>
 <td><input <?php if ($wmAlign==2) echo checked ?> type="radio" name="wmAlign" value="2">
     <?php echo _("Top") ?></input></td>
 <td><input <?php if ($wmAlign==3) echo checked ?> type="radio" name="wmAlign" value="3">
     <?php echo _("Upper&nbsp;Right") ?></input></td>
</tr>
<tr>
 <td><input <?php if ($wmAlign==4) echo checked ?> type="radio" name="wmAlign" value="4">
     <?php echo _("Left") ?></input></td>
 <td><input <?php if ($wmAlign==5) echo checked ?> type="radio" name="wmAlign" value="5">
     <?php echo _("Center") ?></input></td>
 <td><input <?php if ($wmAlign==6) echo checked ?> type="radio" name="wmAlign" value="6">
     <?php echo _("Right") ?></input></td>
</tr>
<tr>
 <td><input <?php if ($wmAlign==7) echo checked ?> type="radio" name="wmAlign" value="7">
     <?php echo _("Lower&nbsp;Left") ?></input></td>
 <td><input <?php if ($wmAlign==8) echo checked ?> type="radio" name="wmAlign" value="8">
     <?php echo _("Bottom") ?></input></td>
 <td><input <?php if ($wmAlign==9) echo checked ?> type="radio" name="wmAlign" value="9">
     <?php echo _("Lower&nbsp;Right") ?></input></td>
</tr>
<tr>
  <td colspan="3">
    <input <?php if ($wmAlign==10) echo checked ?> type="radio" name="wmAlign" value="10">
    <?php echo _("Other") ?></input>
    X=<input type="text" name="wmAlignX" value="<?php echo $wmAlignX ?>">
    Y=<input type="text" name="wmAlignY" value="<?php echo $wmAlignY ?>">
  </td>
</tr>
</table>
<br>
