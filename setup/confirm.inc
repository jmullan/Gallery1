<title> Gallery Setup: Step 2 </title>

This is what your configuration file will look like:
<center>
<table width=90% border=0>
<pre>

<?
$error = 0;

$data .= "<" . "?\n";
$data .= "/* Features */\n";
$view .= row_wrap("/* Features */");
foreach ($features as $feature => $dependents) {
	$use = 1;
	$cause = "";
	foreach ($dependents as $dep) {
		if (!$$dep) {
			$use = 0;
			if ($cause) {
				$cause .= ", ";
			}
			$cause .= "missing $dep";
		}
	}

	if ($use) {
		$data .= use_feature($feature);
		$view .= row_wrap(use_feature($feature));
	} else {
		$data .= no_feature($feature, $cause);
		$view .= row_wrap(no_feature($feature, $cause));
	}
}

$data .= "\n";
$view .= row_wrap("&nbsp;");
$data .= "/* Constants */\n";
$view .= row_wrap("/* Constants */\n");
foreach ($constants as $key => $val) {
	if (!$$key) {
		$view .= row_wrap(error_missing($constants[$key]["desc"]));
		$error = 1;
	} else {
		if ($constants[$key]["require-write-dir"]) {
			$dir = $$key;
			if (!file_exists($dir)) {
				$view .= error("Directory <b>$dir</b> does not exist.  Please create it.");
				continue;
			}

			if (!is_dir($dir)) {
				$view .= error("<b>$dir</b> exists but is not a directory.  " .
						"Please fix this.");
				continue;
			}

			if (!is_writeable($dir)) {
				$view .= error("$dir exists, but is not writeable by the " .
						"webserver user.  Try:<br><center><b><code>" .
						"chown -R " . $webserver_user["name"] . " $dir</code>");
				continue;
			}
		}

		$data .= constant($key, $$key);
		$view .= row_wrap(constant($key, $$key));
	}
	echo "<input type=hidden name=$key value=\"{$$key}\">";
}

$data .= "\n";
$view .= row_wrap("&nbsp;");
$data .= "/* Defaults */\n";
$view .= row_wrap("/* Defaults */\n");
foreach ($defaults as $key => $val) {
	if (!$$key) {
		$view .= error_missing($defaults[$key]["desc"]);
		$error = 1;
	} else {
		$data .= defaults($key, $$key);
		$view .= row_wrap(defaults($key, $$key));
	}
	echo "<input type=hidden name=$key value=\"{$$key}\">";
}
$data .= "?" . ">\n";

echo $view;
?>

<input type=hidden name=data value=<?=urlencode($data)?>>
</pre>
</table>

<input type=hidden name=next_page value="write">
<p>
<input type=submit name="go_back" value="<- Edit Config">
<? if (!$error) { ?>
 <input type=submit name="go_next" value="Save Config ->">
<? } else { ?>
<br>
<font color=red>There are errors in your configuration.
<br>
Please go back and fix them!</font>
<? } ?>

