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
 * Writes a HTML formular to upload items via file-input fields.
 *
 * @package Add_Items
 */

/**
 * Javascript that adds the ability to have dynamically multiple input fields.
 */
include_once(dirname(dirname(dirname(__FILE__))) . '/js/multifile.js.php');

echo "\n<p>". gTranslate('core', "Click the <b>Browse</b> button to locate a photo to upload.");
echo '<br>'. gTranslate('core', "After you added a file, you can add more files on the sane way, just use the browse button as often as you want.");
echo '<br>'. gTranslate('core', "It's possible to enter a manual caption for each item you upload, or you can let gallery set the captions.");
echo '<br>'. sprintf(gTranslate('core', "Supported file types: %s"),join(', ', acceptableFormatList()));

if (isset($gallery->app->feature['zip']) || isset($gallery->app->rar)) {
    $ftype = '';

    if(isset($gallery->app->feature['zip'])) {
        $ftype .= 'zip';
    }

    if(isset($gallery->app->rar)) {
        $ftype .= ($ftype) ? gTranslate('core', " or " ) : '';
        $ftype .= ('rar');
    }

    echo "\n<br><br>". sprintf(gTranslate('core', "You can also upload compressed archive files (%s) full of photos and movies!"), $ftype);
}

echo "</p>\n";

echo makeFormIntro('save_photos.php',
	array('name' => 'upload_form', 'enctype' => 'multipart/form-data'),
	array('type' => 'popup')
);

echo "\n<input id=\"trash_userfile\" type=\"file\">";
echo "\n<br>". gTranslate('core', "Use the Browse button to find the photos on your computer");
echo "\n<br><br>". gTranslate('core', "Files:");
?>

<div style="padding: 2px; border: 1px solid black;" id="files_list"></div>
<script type="text/javascript">
    var userfile_selector = new MultiSelector(document.getElementById('files_list'), -1, 'userfile', true);
    userfile_selector.addElement(document.getElementById('trash_userfile'));
</script>

<p><?php
    echo gTranslate('core', "Do the same for optional metafiles.");
    echo popup_link(gImage('icons/help.gif', gTranslate('common', "Help")), 'help/metadataOnUpload.php',
	 false, false, 550, 600, '', '', '', false, false);
?>
</p>
<input id="trash_metafile" type="file">

<br><?php echo gTranslate('core', "MetaFiles:"); ?>
<div style="padding: 2px; border: 1px solid black;" id="metafiles_list"></div>
<script type="text/javascript">
    var userfile_selector = new MultiSelector(document.getElementById('metafiles_list'), -1, 'metafile', false);
    userfile_selector.addElement(document.getElementById('trash_metafile'));
</script>
<br>
<?php

echo gTranslate('core', "If no caption is set, what would you like the captions to default to?");
echo "\n<p>";
/**
 * Central caption options
 */
include(dirname(__FILE__) .'/captionOptions.inc.php');
echo "\n</p>";

if (isset($gallery->app->watermarkDir)) {
	global $watermarkForm;

	$watermarkForm['askRecursive']	= 0;
	$watermarkForm['askPreview']	= 0;
	$watermarkForm['allowNone']		= 1;

	includeLayout('watermarkform.inc');
}
?>
<p align="center">
	<?php echo gButton('upload', gTranslate('core', "_Upload Now"), 'parent.opener.showProgress(); document.upload_form.submit()'); ?>
	<?php echo gButton('cancel', gTranslate('core', "_Cancel"), 'parent.close()'); ?>
</p>

</form>