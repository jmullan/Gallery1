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
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * $Id$
 */
?>
<?php

include_once(dirname(dirname(dirname(__FILE__))) . '/js/multifile.js.php');

echo "\n\t<p>";
echo _("Click the <b>Browse</b> button to locate a photo to upload.");
echo '<br>';
printf(_("Supported file types: %s"),join(", ", acceptableFormatList()));
echo '</p>';

if ($gallery->app->feature["zip"] || $gallery->app->rar) {
    echo '<p><b>'. _("Tip:") . '</b> ';
    echo _("Upload a compressed file (zip, rar, ace ...) full of photos and movies!");
    echo "</p>\n\t";
}

echo makeFormIntro("save_photos.php",
	array("name" => "upload_form","enctype" => "multipart/form-data"),
	array('type' => 'popup'));

echo "\n\t<input id=\"trash_userfile\" type=\"file\">";
echo "\n\t<br>". _("Use the Browse button to find the photos on your computer");
echo "\n<br><br>". _("Files:");
?>
    <div style="border: 1px solid black;" id="files_list"></div>
<script type="text/javascript">
    var userfile_selector = new MultiSelector(document.getElementById('files_list'), -1, 'userfile', true);
    userfile_selector.addElement(document.getElementById('trash_userfile'));
</script>

<p><?php 
    echo _("Do the same for optional metafiles.");
    echo popup_link(gImage('icons/help.gif', _("Help")), 'help/metadataOnUpload.php');
?>
</p>
<input id="trash_metafile" type="file">
	<br>
MetaFiles:

<div style="border: 1px solid black;" id="metafiles_list"></div>
<script type="text/javascript">
    var userfile_selector = new MultiSelector(document.getElementById('metafiles_list'), -1, 'metafile', false);
    userfile_selector.addElement(document.getElementById('trash_metafile'));
</script>
<br>
<?php 
	echo _("If no caption is set, what would you like the captions to default to?");
	echo "\n<p>";
	include(dirname(__FILE__) .'/captionOptions.inc.php');

if (isset($gallery->app->watermarkDir)) {
    global $watermarkForm;
    $watermarkForm["askRecursive"] = 0;
    $watermarkForm["askPreview"] = 0;
    $watermarkForm["allowNone"] = 1;
    includeLayout('watermarkform.inc');
}
?>
	<p align="center">
	<input type="button" value="<?php echo _("Upload Now") ?>" onClick='parent.opener.showProgress(); document.upload_form.submit()'>
	<input type="submit" value="<?php echo _("Cancel") ?>" onclick='parent.close()'>
	</p>
	</form>