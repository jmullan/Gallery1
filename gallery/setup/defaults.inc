<?php /* $Id$ */ ?>
<center>
<font size=+2><?php echo _("Gallery Setup: Step 3") ?></font>
<table width=80%><tr><td>

<?php echo _("Here you can customize the default values for new albums that you create.") ?>
<br>
<?php echo _("Note that these are not <b>master switches</b> (ie, if you turn a setting on here, you can still turn it off in a given album).") ?>
<br>
<?php echo _("If this is the first time you've customized Gallery, you'll notice that we've tried to pick reasonable defaults for you.") ?>
<br>
<?php echo _("If you've already configured Gallery before, then you'll notice that this page already has your current config information in it so you probably won't need to do much.") ?>

</td></tr></table>
<p>

<table>
<input type=hidden name=this_page value="defaults">
<input type=hidden name=next_page value="confirm">
<input type=hidden name=back_page value="constants">

<?php
$i = 0;
foreach ($defaults as $key => $val) {
	/* Support $app-> for backwards compatibility */
	if ($app->default[$key]) {
		$val["value"] = $app->default[$key];
	}

	/* $gallery->app-> is the desired container, however */
	if ($gallery->app->default[$key]) {
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

	if (!$go_defaults && isset($$key)) {
		$val["value"] = $$key;
		
		if (get_magic_quotes_gpc()) {
		    $val["value"] = stripslashes($val["value"]);
		}
	}

	// Protect quote characters to avoid screwing up HTML forms
	$val["value"] = str_replace('"', "&quot;", $val["value"]);
	
	if (!strcmp($val["type"], "hidden")) {
		list($f1, $f2) = make_fields($key, $val);
		echo $f2;
	} else {
		echo evenOdd_row(make_fields($key, $val),
				$i++ % 2);
		echo "<tr><td colspan=2> &nbsp; </td></tr>";
	}

	$onThisPage[$key] = 1;
	$preserve[$key] = 1;
}
?>
</table>
<p>
<input type=submit name="go_back" value=<?php echo '"<- ' . _("Gallery Settings") .'"' ?>>
<input type=submit name="go_next" value=<?php echo '"' . _("Save Settings") .' ->"' ?>>
<input type=submit name="go_defaults" value=<?php echo '"' . _("Use Defaults") . '"' ?>>
