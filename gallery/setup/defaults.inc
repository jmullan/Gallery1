<?php /* $Id$ */ ?>

<input type="hidden" name="this_page" value="defaults">
<input type="hidden" name="next_page" value="confirm">
<input type="hidden" name="back_page" value="constants">

<div class="header"><?php echo sprintf (_("Gallery Setup: Step %d"),3); ?></div>

<div class="sitedesc">
<?php
	echo _("Here you can customize the default values for new albums that you create.  Note that these are not <b>master switches</b> (ie, if you turn a setting on here, you can still turn it off in a given album).  If this is the first time you've customized Gallery, you'll notice that we've tried to pick reasonable defaults for you.  If you've already configured Gallery before, then you'll notice that this page already has your current config information in it so you probably won't need to do much.");
?>
</div>

<p align="center">
<input type=submit name="go_back" value="<- <?php echo _("Previous Page") ?>">
<input type=submit name="go_next" value="<?php echo _("Next Page") ?> ->">
<input type=submit name="go_defaults" value="<?php echo _("Use Defaults") ?>">
</p>

<table width="100%">


<?php
$i = 0;
foreach ($defaults as $key => $val) {
	if (isset($val["type"]) && ($val["type"] === "block_start" )) {
		echo evenOdd_row(make_fields($key, $val), $i % 2);
		continue;
	}
	if (isset($val["type"]) && ($val["type"] === "block_end" )) {
		echo evenOdd_row(make_fields($key, $val), $i++ % 2);
		echo "\n<tr><td colspan=\"2\"> &nbsp; </td></tr>";
		continue;
	}

	if( isset($val["type"]) && ($val["type"] === "separator")) {
		echo make_separator($key, $val);
		continue;
        }

	/* Support $app-> for backwards compatibility */
	if (isset($app->default[$key])) {
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

	if (!isset($go_defaults) && isset($$key)) {
		$val["value"] = $$key;
		
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
		if (!isset($val["type"]) || ($val["type"] !== "block_element" )) {
			echo "<tr><td colspan=2> &nbsp; </td></tr>";
		}
	}

	$onThisPage[$key] = 1;
	$preserve[$key] = 1;
}
?>
</table>
<p align="center">
	<input type=submit name="go_back" value="<- <?php echo _("Previous Page") ?>">
	<input type=submit name="go_next" value="<?php echo _("Next Page") ?> ->">
	<input type=submit name="go_defaults" value="<?php echo _("Use Defaults") ?>">
</p>
