<center>
<font size=+2> Gallery Setup: Step 2 </font>
<table width=80%><tr><td>

In this step you'll customize various settings for Gallery.
If this is the first time you've customized Gallery, you'll
notice that we've tried to pick reasonable defaults for you.
Even so, you'll probably have to make at least some minor changes.
If you've already configured Gallery before, then you'll notice
that this page already has your current config information in it
so you probably won't need to do much.

</td></tr></table>
<p>

<table border=0 cellspacing=0 cellpadding=0>
<input type=hidden name=this_page value="constants">
<input type=hidden name=next_page value="defaults">
<input type=hidden name=back_page value="check">

<?php
$i = 0;
foreach ($constants as $key => $val) {
	/* Support $app-> for backwards compatibility */
	if ($app->$key) {
		$val["value"] = $app->$key;
	}

	/* $gallery->app-> is the desired container, however */
	if ($gallery->app->$key) {
		$val["value"] = $gallery->app->$key;
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
<input type=submit name="go_back" value="<- System Check Page">
<input type=submit name="go_next" value="Album Defaults ->">
<input type=submit name="go_defaults" value="Use Defaults">
