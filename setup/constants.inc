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

require(GALLERY_BASE . '/includes/setup/constants.inc');
require(GALLERY_BASE . '/lib/tabs.php');
require(GALLERY_BASE . '/js/multiInput.js.php');

$inst_type = '';

if(!empty($gallery->session->configForm->inst_type)) {
	$inst_type = $gallery->session->configForm->inst_type;
}

if($inst_type == 'easy') {
	$visibilityKeyword = 'inst_type';
	$visibilityValue = 'easy';
}
else {
	$visibilityKeyword = '';
	$visibilityValue = '';

	include_once(GALLERY_BASE .'/classes/horde/Browser.php');
	$browser = &Browser::singleton();

	if($browser->hasFeature('xmlhttpreq')) {
		echo autoCompleteJS();
	}
}

insertFormJS('config');
?>

<input type="hidden" name="this_page" value="constants">
<input type="hidden" name="next_page" value="defaults">
<input type="hidden" name="back_page" value="check">

<script type="text/javascript" language="Javascript">

function toggleGraphics(show) {
	if(show == 'ImageMagick') {
		document.getElementById('subgroup_ImageMagick').style.display = 'inline';
		document.getElementById('subgroup_Netpbm').style.display      = 'none';
	}
	else {
		document.getElementById('subgroup_ImageMagick').style.display = 'none';
		document.getElementById('subgroup_Netpbm').style.display      = 'inline';
	}
}

function toggleSMTP(show) {
	if(show == 'yes') {
		document.getElementById('subgroup_SMTP').style.display = 'inline';
	}
	else {
		document.getElementById('subgroup_SMTP').style.display = 'none';
	}
}
</script>

<div class="g-header">
    <div class="g-pagetitle"><?php echo gTranslate('config', "Gallery configuration wizard"); ?></div>
</div>

<div class="g-config">
<?php include(GALLERY_BASE . '/includes/setup/stepbar.inc'); ?>

    <div class="g-config-content">
        <div class="g-config-step floatleft"><?php echo gTranslate('config', "Settings"); ?></div>
        <div class="floatright">
        <span style="white-space: nowrap;">
            <?php echo gSubmit('go_back', gTranslate('config', "&lt;&lt; _Previous Step"), array('disabled' => 'disabled')); ?>
            <?php echo gSubmit('go_next', gTranslate('config', "_Next Step &gt;&gt;"), array('disabled' => 'disabled')); ?>
        </span>
            <?php if(!empty($inst_type)) :?>
            <div class="g-columnheader" style="-moz-border-radius: 5px;">
            <?php printf(gTranslate('config', "Installation type: %s"), gTranslate('config', $inst_type)); ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="g-album-desc clear">
        <?php
            echo gTranslate('config', "In this step you'll customize various settings for Gallery.  If this is the first time you've customized Gallery, you'll notice that we've tried to pick reasonable defaults for you.  Even so, you'll probably have to make at least some minor changes.  If you've already configured Gallery before, then you'll notice that this page already has your current settings loaded, so you probably won't need to do much.");
            echo '<br>';
            echo gTranslate('config',"Switch through the corresponding tab and fill the values you need. Some, but not all values are required. If you have finished this step use the button to go to the next Step.");
        ?>
        </div>
    </div>

    <div style="clear: right; height: 35px"></div>

    <div class="g-config-main">
        <div align="center" style="margin: 5px 0">
            <?php echo gButton('go_backTab', gTranslate('config', "&lt; Previous Tab"), 'section_tabs.prevTab()', array('disabled' => 'disabled', 'class' => 'g-buttonDisable')); ?>
            <?php echo gButton('go_nextTab', gTranslate('config', "Next Tab &gt;"), 'section_tabs.nextTab()', array('disabled' => 'disabled')); ?>
            <input type="submit" name="go_defaults" disabled="disabled" value="<?php echo gTranslate('config', "Use Defaults") ?>" class="g-button">
        </div>
<?php

$initialtab = makeSectionTabs($constants, '', false, $visibilityKeyword, $visibilityValue);
echo '<input name="initialtab" id="initialtab" type="hidden" value="'. $initialtab .'">';

$i = 0;
foreach ($constants as $key => $val) {
	if( isset($val["enabled"]) && $val["enabled"] == "no") {
		continue;
	}

	if (isset($val["type"]) && $val["type"] === 'message') {
		echo $val["message"];
		continue;
	}

	if (isset($val["type"]) && $val["type"] === 'block_start') {
		echo "\n<table class=\"g-setup-block\">";
		echo evenOdd_row(make_fields($key, $val), $i % 2);
		continue;
	}

	if (isset($val["type"]) && $val["type"] === 'block_end') {
		echo evenOdd_row(make_fields($key, $val), $i++ % 2);
		echo "\n</table>";
		continue;
	}

	if (isset($val["type"]) && $val["type"] === 'subgroup') {
		if($inst_type == 'easy' && !isset($val["inst_type"])) {
			$visibility = 'none';
		}
		else {
			$visibility =  $val['display'];
		}

		echo "\n<div id=\"$key\" style=\"display: $visibility\">";
		echo "\n\t<div class=\"g-subgroup center\">{$val['title']}</div>";
		if (isset($val['desc'])) {
			echo "\n\t<div>{$val['desc']}</div>";
		}
		continue;
	}

	if (isset($val["type"]) && $val["type"] === 'subgroup_end') {
		echo "\n</div>";
		continue;
	}

	if (isset($val["type"]) && $val["type"] === 'group_start') {
		if($inst_type == 'easy' && !isset($val["inst_type"])) {
			$visibility = 'none';
		}
		else {
			$visibility = $val["default"];
		}

		echo "\n<div id=\"$key\" style=\"display: {$visibility}\">";
		echo make_separator($key, $val);
		continue;
	}

	if (isset($val["type"]) && $val["type"] === 'group_end') {
		echo "\n</div>";
		continue;
	}

	$onThisPage[$key] = true;
	$preserve[$key] = true;

	if(!empty($visibilityKeyword) && isset($val[$visibilityKeyword]) &&
	    $val[$visibilityKeyword] != $visibilityValue)
	{
		$val["type"] = 'hidden';
		$val["persistent"] = true;
	}

	/* if the variable is hidden, lock it in as we don't want to use previous values*/
	if (isset($val["type"]) && $val["type"] === 'hidden') {
		if (!empty($val["persistent"]) && isset($gallery->app->$key)) {
			$val["value"] = $gallery->app->$key;
		}
		elseif (is_array($val["value"])) {
			$key = $key."[]";
			$val["value"] = $val["value"][0];
		}

		if (ereg("^(..*)\[.*\]$", $key, $matches)) {
			unset($preserve[$key]);
			$preserve[$matches[1]] = true;
			unset($onThisPage[$key]);
			$onThisPage[$matches[1]] = true;
		}

		list($f1, $f2) = make_fields($key, $val);
		echo $f2;
	}
	else {
		if (!isset($val["type"]) || $val["type"] !== 'block_element') {
			echo "\n<table class=\"g-setup-fields\">";
		}
		/* Support $app-> for backwards compatibility */
		if (isset($app->$key)) {
			$val["value"] = $app->$key;
		}

		/* $gallery->app-> is the desired container, however */
		if (isset($gallery->app->$key)) {
			$val["value"] = $gallery->app->$key;
		}

		if (!isset($go_defaults)) {
			if (ereg("^(..*)\[.*\]$", $key, $matches)) {
				if (isset ($gallery->session->configForm->$key)) {
					$val['value'] = $gallery->session->configForm->$key;
				}

				unset($preserve[$key]);
				$preserve[$matches[1]] = 1;
				unset($onThisPage[$key]);
				$onThisPage[$matches[1]] = 1;
			}
			else if(!empty($gallery->session->configForm->$key)) {
				$val["value"] = $gallery->session->configForm->$key;
			}

			if (get_magic_quotes_gpc() && !isset($val['noStripSlash'])) {
				if (is_array($val["value"])) {
					$val["value"] = array_map("stripslashes", $val["value"]);
				}
				else {
					$val["value"] = stripslashes($val["value"]);
				}
			}
		}

		// Protect quote characters to avoid screwing up HTML forms
		if (isset($val['value'])) {
			$val['value'] = str_replace('"', '&quot;', $val['value']);
		}
		else {
			$val['value'] = '';
		}

		echo evenOdd_row(make_fields($key, $val), $i++ % 2);
	}

	if (!isset($val['type']) || ($val['type'] !== 'hidden' && $val['type'] !== 'block_element')) {
		echo "\n</table>";
	}
}
?>
        <div align="center">
        	<?php echo gSubmit('go_back', gTranslate('config', "&lt;&lt; _Previous Step"), array('disabled' => 'disabled')); ?>
			<?php echo gButton('go_backTab', gTranslate('config', "&lt; Previous Tab"), 'section_tabs.prevTab()', array('disabled' => 'disabled', 'class' => 'g-buttonDisable')); ?>
            <?php echo gButton('go_nextTab', gTranslate('config', "Next Tab &gt;"), 'section_tabs.nextTab()', array('disabled' => 'disabled')); ?>
			<?php echo gSubmit('go_next', gTranslate('config', "_Next Step &gt;&gt;"), array('disabled' => 'disabled')); ?>
            <?php echo gSubmit('go_defaults', gTranslate('config', "Use Defaults"), array('disabled' => 'disabled')); ?>
        </div>

        <div class="clear"></div>
    </div>
</div>