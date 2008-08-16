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
 * $Id: add_local.inc 13490 2006-04-18 12:36:36Z jenst $
 */

/**
 * Writes a HTML form to add items from local server
 *
 * @package Add_Items
 */

/**
 * Horde Browser class to detect the browser and its abilities.
 */
include_once(GALLERY_BASE .'/classes/horde/Browser.php');

if (class_exists('Browser')) {
	$browser = &Browser::singleton();
}

if($browser->hasFeature('xmlhttpreq') && isset($gallery->app->uploadPaths)) {
	echo autoCompleteJS();
}

echo "\n<br><br>";
echo gTranslate('core', "Upload any images found at this location on your server.");

echo '<br><b>'. gTranslate('core', "Hint:") . '</b> ';
echo gTranslate('core', "FTP images to a directory on your server then provide that path here!");

echo makeFormIntro("save_photos.php",
array('name' => 'uploadurl_form'),
array('type' => 'popup'));

if(isset($gallery->app->uploadPaths)) {
	echo gTranslate('core', "Choose one of these legal paths:");

	$options = array(
	array('text' => gTranslate('core', "<< Choose path >>"),
	'value' => 0,
	'selected' => true));
	foreach ($gallery->app->uploadPaths as $value) {
		$options[] = array('text' => $value, 'value' => $value);
	}

	echo drawSelect2('prefix', $options, array('onChange' => 'setUploadPath()'));
	echo "<br>";
}

echo initAutocompleteJS(
	gTranslate('core', "Path to your image(s):"),
	'urls[]',
	'YUIsearchinput',
	$browser->hasFeature('xmlhttpreq') && isset($gallery->app->uploadPaths),
	isset($gallery->app->uploadPaths)
);

echo "\n<div class=\"clear\" style=\"padding-top: 5px\">";
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

	$watermarkForm["askRecursive"] = 0;
	$watermarkForm["askPreview"] = 0;
	$watermarkForm["allowNone"] = 1;
	includeLayout('watermarkform.inc');
}
?>
<div align="center">
<input type="submit" value="<?php echo gTranslate('core', "Submit directory") ?>" onClick='parent.opener.showProgress(); document.uploadurl_form.submit()' class="g-button">
<?php echo gButton('cancel', gTranslate('core', '_Cancel'), 'parent.close()'); ?>
</div>
</form>

<?php
if(isset($gallery->app->uploadPaths) && $browser->hasFeature('xmlhttpreq')) {?>
<script type="text/javascript" language="javascript">
    var prefix = document.getElementsByName('prefix');
    var YUIsearchinput = document.getElementById('YUIsearchinput');

    function setUploadPath() {
        var selectedIndex = document.uploadurl_form.prefix.selectedIndex;
        if (selectedIndex != 0) {
            YUIsearchinput.value = document.uploadurl_form.prefix.options[selectedIndex].value;
            YUIsearchinput.disabled = false;
            YUIsearchinput.focus();
        }
        else {
            YUIsearchinput.value = ''
            YUIsearchinput.disabled = true;
        }
    }

</script>
<?php
}
elseif ($browser->hasFeature('xmlhttpreq')) {
    if ($gallery->user->isAdmin()) {
        echo "<br>\n" . infoBox(array(array(
            'type' => 'information',
            'text' => gTranslate('core', "No legal upload pathes defined. Autocomplete is switched off.")
        )), '', false);
    }
}
?>