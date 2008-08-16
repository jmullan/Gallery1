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
 * Writes a HTML formular to upload items from an URL.
 *
 * @package Add_Items
 */

echo "\n<br><br>";

echo makeFormIntro('save_photos.php',
	array('name' => 'uploadurl_form'),
	array('type' => 'popup')
);

echo gInput(
		'text',
		'urls[]',
		gTranslate('core', "Upload any images found at this location:"),
		false, false, array('size' => 50));

echo "\n<div style=\"padding-top: 5px\">";
echo gTranslate('core', "What would you like the captions to default to?");
echo "\n</div>";

echo "\n<div style=\"padding-top: 5px\">";
/**
 * Central caption options
 */
include(dirname(__FILE__) .'/captionOptions.inc.php');
echo "\n</div>";

if (isset($gallery->app->watermarkDir)) {
	global $watermarkForm;

	$watermarkForm['askRecursive']	= 0;
	$watermarkForm['askPreview']	= 0;
	$watermarkForm['allowNone']	= 1;

	includeLayout('watermarkform.inc');
}
?>
<div align="center">
	<?php echo gButton('upload', gTranslate('core', "_Submit URL"), 'parent.opener.showProgress(); document.uploadurl_form.submit()'); ?>
	<?php echo gButton('cancel', gTranslate('core', "_Cancel"), 'parent.close()'); ?>
</div>

</form>
