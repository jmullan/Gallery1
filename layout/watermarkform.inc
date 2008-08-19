<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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

/**
 * @package Layout
 */

global $watermarkForm;
global $wmAlign;
global $wmAlignX;
global $wmAlignY;
global $previewFull;
global $wmName;
global $wmSelect;

global $errors;
$errors = array();

if (!isset($wmAlignX) || ($wmAlignX <= 0)) {
	$wmAlignX = 0;
}

if (!isset($wmAlignY) || ($wmAlignY <= 0)) {
	$wmAlignY = 0;
}

if (!isset($wmName)) {
	$wmName = '';
}

if (!isset($wmAlign)) {
	$wmAlign = 9;
}

if (!isset($previewFull)) {
	$previewFull = 0;
}

if (!isset($wmSelect)) {
	$wmSelect = $gallery->app->watermarkSizes;
}

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
			if(!empty($wmName) && $wmName == $wmFile) {
				$options[] = array('value' => $wmFile, 'text' => $wmFile, 'selected' => true);
			}
			else {
				$options[] = array('value' => $wmFile, 'text' => $wmFile);
			}
		}
	}
	closedir($wmDir);

	if(isset($options) && sizeof($options) > 0) {
		echo "<b>". gTranslate('core', "Watermark:") ."</b>&nbsp;";

		array_sort_by_fields($options, 'text', 'desc', false);
		if ($watermarkForm["allowNone"]) {
			$options[] = array('value' => '', 'text' => gTranslate('core', "* No Watermark *"), 'selected' => true);
		}
		$options = array_reverse($options);
		echo drawSelect2('wmName', $options);
	}
	else {
		$errors[] = array(
			'type' => 'error',
			'text' => sprintf(gTranslate('core', "No files matching %s found in %s"),
						"<b>" . $extensions_string . "</b>",
						"<b>" . $gallery->app->watermarkDir . "</b>")
		);
	}
}
else {
	$errors[] = array(
		'type' => 'error',
		'text' => sprintf(gTranslate('core', "Could not open directory %s"), $gallery->app->watermarkDir)
	);
}
?>

<div class="left" style="margin-top: 5px;">
<?php
if(!empty($errors)) {
	echo infoBox($errors);
}
else {
	echo jsHTML('toggle.js.php');
?>

<fieldset style="margin-bottom: 5px;">
	<legend class="g-emphasis"><?php echo gTranslate('core', "Alignment:") ?></legend>
	<table border="0" width="100%">
	<tr>
	 <td><input <?php if ($wmAlign==1) echo " checked"; ?> type="radio" name="wmAlign" value="1">
	     <?php echo gTranslate('core', "Upper&nbsp;Left") ?></td>
	 <td><input <?php if ($wmAlign==2) echo " checked"; ?> type="radio" name="wmAlign" value="2">
	     <?php echo gTranslate('core', "Top") ?></td>
	 <td><input <?php if ($wmAlign==3) echo " checked"; ?> type="radio" name="wmAlign" value="3">
	     <?php echo gTranslate('core', "Upper&nbsp;Right") ?></td>
	</tr>
	<tr>
	 <td><input <?php if ($wmAlign==4) echo " checked"; ?> type="radio" name="wmAlign" value="4">
	 	<?php echo gTranslate('core', "Left") ?></td>
	 <td><input <?php if ($wmAlign==5) echo " checked"; ?> type="radio" name="wmAlign" value="5">
	 	<?php echo gTranslate('core', "Center") ?></td>
	 <td><input <?php if ($wmAlign==6) echo " checked"; ?> type="radio" name="wmAlign" value="6">
	     <?php echo gTranslate('core', "Right") ?></td>
	</tr>
	<tr>
	 <td><input <?php if ($wmAlign==7) echo " checked"; ?> type="radio" name="wmAlign" value="7">
	     <?php echo gTranslate('core', "Lower&nbsp;Left") ?></td>
	 <td><input <?php if ($wmAlign==8) echo " checked"; ?> type="radio" name="wmAlign" value="8">
	     <?php echo gTranslate('core', "Bottom") ?></td>
	 <td><input <?php if ($wmAlign==9) echo " checked"; ?> type="radio" name="wmAlign" value="9">
	     <?php echo gTranslate('core', "Lower&nbsp;Right") ?></td>
	</tr>
	<tr>
	  <td colspan="3">
	    <input <?php if ($wmAlign==10) echo " checked"; ?> type="radio" name="wmAlign" value="10">
	    <?php echo gTranslate('core', "Other") ?>
	    X=<input type="text" name="wmAlignX" value="<?php echo $wmAlignX ?>">
	    Y=<input type="text" name="wmAlignY" value="<?php echo $wmAlignY ?>">
	  </td>
	</tr>
	</table>
</fieldset>

<?php

echo gTranslate('core', "Apply watermark to") ?> <select name="wmSelect">
  <option value="0"<?php if ($wmSelect <  1) echo " selected"; ?>><?php echo gTranslate('core', "All photos");?></option>
  <option value="1"<?php if ($wmSelect == 1) echo " selected"; ?>><?php echo gTranslate('core', "Only sized photos");?></option>
  <option value="2"<?php if ($wmSelect >  1) echo " selected"; ?>><?php echo gTranslate('core', "Only full photos");?></option>
</select>

<?php

if ($watermarkForm["askRecursive"]) {
	global $recursive;

	echo gInput('checkbox', 'recursive', gTranslate('core', "Watermark subalbums?"), false, null, array('checked' => isset($recursive) ? null: false));
}

if ($watermarkForm["askPreview"] && $gallery->user->canViewFullImages($gallery->album)) {
	echo '<p style="margin: 3px;">' .toggleButton('blub') . '<b>' . gTranslate('core', "Preview ?") . '</b>';

	echo "\n" . '<span id="toggleFrame_blub" style="display:none;">';
	echo gInput('radio','previewFull', gTranslate('core', "Resized"), '', 0, array('id' => 'previewResized', 'checked' => $previewFull==0 ? NULL : false));
	echo gInput('radio','previewFull', gTranslate('core', "Full"), '', 0, array('checked' => $previewFull==0 ? NULL : false));
	echo "&nbsp;&nbsp;&nbsp;&nbsp;";
	echo gSubmit('preview', gTranslate('core', "Show _Preview"));
	echo "\n</span></p>";
}

?>
</div>
<?php
}
?>
