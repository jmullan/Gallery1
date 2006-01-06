<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2006 Bharat Mediratta
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
<input type="hidden" name="this_page" value="defaults">
<input type="hidden" name="next_page" value="confirm">
<input type="hidden" name="back_page" value="constants">

<div class="header"><?php echo sprintf (_("Gallery Configuration Wizard: Step %d"),3); ?></div>

<div class="sitedesc">
<?php
	echo _("Here you can customize the default values for <b>new albums</b> that you create. These settings have no effect on existing albums!");
	echo "\n<br>";
	echo _("Note that these are <b>not master switches</b>. This means you can change those settings for each separately.");
	echo _("If this is the first time you've customized Gallery, you'll notice that we've tried to pick reasonable defaults for you.  If you've already configured Gallery before, then you'll notice that this page already has your current settings loaded, so you probably won't need to do much.");
	echo "\n<br>";
	echo _("Switch through the corresponding tab and fill the values you need. Some, but not all values are required. If you have finished this step use the button to go to the next Step.");
	echo "\n<br><br>". '<div class="attention">' . _('Note to users upgrading from a previous version: settings new since your old version will be applied to all existing albums. If you wish to change these later, they must be changed one by one in each album. Please choose these default values carefully.') .'</div>';

?>
</div>

<p align="center">
	<input type="submit" name="go_back" disabled="disabled" value="&lt;-- <?php echo _("Previous Step") ?>">
	<input type="button" name="go_backTab" disabled="disabled" value="&lt;- <?php echo _("Previous Tab") ?>" onClick="section_tabs.prevTab()">
	<input type="button" name="go_nextTab" disabled="disabled" value="<?php echo _("Next Tab") ?> -&gt;" onClick="section_tabs.nextTab()">
	<input type="submit" name="go_next" disabled="disabled" value="<?php echo _("Next Step") ?> --&gt;">
	<input type="submit" name="go_defaults" disabled="disabled" value="<?php echo _("Use Defaults") ?>">
</p>

<?php
	makeSectionTabs($defaults);

$i = 0;
foreach ($defaults as $key => $val) {

	if (isset($val["type"]) && ($val["type"] === 'group_start' )) {
                echo "\n<div id=\"". $val["name"] ."\" style=\"display: ". $val["default"] ."\">";
                echo make_separator($key, $val);
                continue;

        }

        if (isset($val["type"]) && ($val["type"] === 'group_end' )) {
                echo "\n</div>";
                continue;
        }

	echo "\n<table width=\"100%\" class=\"inner\">";

	if (isset($val["type"]) && ($val["type"] === "block_start" )) {
		echo evenOdd_row(make_fields($key, $val), $i % 2);
		echo "\n</table>";
		continue;
	}
	if (isset($val["type"]) && ($val["type"] === "block_end" )) {
		echo evenOdd_row(make_fields($key, $val), $i++ % 2);
		echo "\n<tr><td colspan=\"2\"> &nbsp; </td></tr>";
		echo "\n</table>";
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

	if (isset($val["type"]) && !strcmp($val["type"], "hidden")) {
		list($f1, $f2) = make_fields($key, $val);
		echo $f2;
	} else {
		echo evenOdd_row(make_fields($key, $val),
				$i++ % 2);
	}

	$onThisPage[$key] = 1;
	$preserve[$key] = 1;
	
	echo "\n</table>";
}
?>

<p align="center">
	<input type=submit name="go_back" disabled="disabled" value="&lt;-- <?php echo _("Previous Step") ?>">
	<input type="button" name="go_backTab" disabled="disabled" value="&lt;- <?php echo _("Previous Tab") ?>" onClick="section_tabs.prevTab()">
	<input type="button" name="go_nextTab" disabled="disabled" value="<?php echo _("Next Tab") ?> -&gt;" onClick="section_tabs.nextTab()">
	<input type=submit name="go_next" disabled="disabled" value="<?php echo _("Next Step") ?> --&gt;">
	<input type=submit name="go_defaults" disabled="disabled" value="<?php echo _("Use Defaults") ?>">
</p>
