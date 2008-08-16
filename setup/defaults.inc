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

require(GALLERY_BASE . '/includes/setup/defaults.inc');
require(GALLERY_BASE . '/lib/tabs.php');

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
}
?>

<input type="hidden" name="this_page" value="defaults">
<input type="hidden" name="next_page" value="confirm">
<input type="hidden" name="back_page" value="constants">

<div class="g-header">
    <div class="g-pagetitle"><?php echo gTranslate('config', "Gallery configuration wizard"); ?></div>
</div>

<div class="g-config">
    <?php include(GALLERY_BASE . '/includes/setup/stepbar.inc'); ?>

    <div class="g-config-content">
        <div class="g-config-step floatleft"><?php echo gTranslate('config', "Defaults"); ?></div>
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
            echo gTranslate('config', "Here you can customize the default values for <b>new albums</b> that you create. These settings have no effect on existing albums!");
        	echo "\n<br>";
        	echo gTranslate('config', "Note that these are <b>not master switches</b>. This means you can change those settings for each separately.");
        	echo gTranslate('config', "If this is the first time you've customized Gallery, you'll notice that we've tried to pick reasonable defaults for you.  If you've already configured Gallery before, then you'll notice that this page already has your current settings loaded, so you probably won't need to do much.");
        	echo "\n<br>";
        	echo gTranslate('config', "Switch through the corresponding tab and fill the values you need. Some, but not all values are required. If you have finished this step use the button to go to the next Step.");
        	echo "\n<br><br>".
        	     '<div class="g-attention">' .
        	     gTranslate('config', 'Note to users upgrading from a previous version: settings new since your old version will be applied to all existing albums. If you wish to change these later, they must be changed one by one in each album. Please choose these default values carefully.') .
        	     '</div>';
        ?>
        </div>
    </div>

    <div style="clear: right; height: 2px"></div>

    <div class="g-config-main">
        <div align="center" style="margin: 5px 0">
			<?php echo gButton('go_backTab', gTranslate('config', "&lt; Previous Tab"), 'section_tabs.prevTab()', array('disabled' => 'disabled', 'class' => 'g-buttonDisable')); ?>
			<?php echo gButton('go_nextTab', gTranslate('config', "Next Tab &gt;"), 'section_tabs.nextTab()', array('disabled' => 'disabled')); ?>
			<?php echo gSubmit('go_defaults', gTranslate('config', "Use Defaults"), array('disabled' => 'disabled')); ?>
        </div>
<?php

$initialtab = makeSectionTabs($defaults, '', false, $visibilityKeyword, $visibilityValue);
echo '<input name="initialtab" id="initialtab" type="hidden" value="'. $initialtab .'">';

$i = 0;
foreach ($defaults as $key => $val) {
    if (isset($val["type"]) && $val["type"] === 'group_start') {
	    if(isset($gallery->session->configForm->inst_type) &&
		$gallery->session->configForm->inst_type == 'easy' && !isset($val["inst_type"])) {
	        $visibility = 'none';
	    }
	    else {
	        $visibility = $val["default"];
	    }

		echo "\n<div id=\"$key\" style=\"display: {$visibility}\">";
		echo make_separator($key, $val);
		continue;
	}

    if (isset($val["type"]) && ($val["type"] === 'group_end' )) {
        echo "\n</div>";
        continue;
    }

	/* Support $app-> for backwards compatibility */
	if (isset($app->default) && isset($app->default[$key])) {
		$val["value"] = $app->default[$key];
	}

	/* $gallery->app-> is the desired container, however */
	if (isset($gallery->app->default[$key])) {
		$val["value"] = $gallery->app->default[$key];
	}

	/* Convert custom fields for compatibility */
	if ($key == "extra_fields" && strrpos($val["value"], "&quot;")) {
	    /* Convert:
         *    "&quot;Description&quot;, &quot;Foo Bar&quot;"
	     * to
	     *    "Description, Foo Bar"
	     */
	    $val["value"] = str_replace("&quot;", "", $val["value"]);
	    $val["value"] = trim($val["value"], " ,");
	}

	if (!isset($go_defaults) && isset($gallery->session->configForm->$key)) {
		$val["value"] = $gallery->session->configForm->$key;

		if (get_magic_quotes_gpc()) {
			$val["value"] = array_stripslashes($val["value"]);
		}
	}
	// Protect quote characters to avoid screwing up HTML forms
	$val["value"] = array_str_replace('"', "&quot;", $val["value"]);

	if(!empty($visibilityKeyword) && isset($val[$visibilityKeyword]) &&
	  $val[$visibilityKeyword] != $visibilityValue) {
        $val["type"] = 'hidden';
	}

	if (isset($val["type"]) && $val["type"] == 'hidden') {
	    list($f1, $f2) = make_fields($key, $val);
	    echo $f2;
	}
	else {
        echo "\n<table class=\"g-setup-fields\">";
	    echo evenOdd_row(make_fields($key, $val), $i++ % 2);
        echo "\n</table>";
	}

	$onThisPage[$key] = true;
	$preserve[$key] = true;
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