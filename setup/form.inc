<title> Gallery Setup: Step 1 </title>
<center>
<font size=+2>This is the Gallery setup page. </font>
<br>
You can use this page to customize the default values for
<br> your installation of the Gallery web application.

<table>
<input type=hidden name=this_page value="form">
<input type=hidden name=next_page value="confirm">
<?
foreach ($constants as $key => $val) {
	if ($app->$key) {
		$val["value"] = $app->$key;
	}

	if (!$go_defaults && isset($$key)) {
		$val["value"] = $$key;
	}

	if ($val["choices"]) {
		echo row_choice($key, $val);
	} else {
		echo row_text($key, $val);
	}
}

foreach ($defaults as $key => $val) {
	if ($app->defaults[$key]) {
		$val["value"] = $app->defaults[$key];
	}

	if (!$go_defaults && isset($$key)) {
		$val["value"] = $$key;
	}

	if ($val["choices"]) {
		echo row_choice($key, $val);
	} else {
		echo row_text($key, $val);
	}
}
?>
</table>
<p>
<input type=submit name="go_next" value="Next ->">
<input type=submit name="go_defaults" value="Use Defaults">
