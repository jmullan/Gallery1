<center>
<font size=+2> Gallery Setup: Step 2 </font>
<table width=80%><tr><td>
This is the setup step.  Customize the default values for 
your Gallery here.  We've tried to pick reasonable defaults
for you but you'll have to make at least some minor changes.
If you've already configuired Gallery once, then you'll notice
that this has your current config information already in it.
</td></tr></table>
<p>

<table>
<input type=hidden name=this_page value="form">
<input type=hidden name=next_page value="confirm">
<input type=hidden name=prev_page value="check">
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
	if ($app->default[$key]) {
		$val["value"] = $app->default[$key];
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
<input type=submit name="go_back" value="<- Back to Check Page">
<input type=submit name="go_next" value="Save Settings ->">
<input type=submit name="go_defaults" value="Use Defaults">
