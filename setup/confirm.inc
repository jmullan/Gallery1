<center>
<font size=+2> Gallery Setup: Step 3 </font>
<br>
<table width=80%><tr><td>
Confirm that the settings you entered came through correctly.
If you find this page confusing, you can probably safely ignore it
(unless you see stuff in red which indicates an error).
</td></tr></table>

<center>
<table width=90% border=0>
<pre>

<?
require("../version.php");

$error = 0;

$data .= "<" . "?\n";

$data .= "/* Version  */\n";
$view .= row_wrap("/* Version  */");

$data .= "\$app->config_version = $gallery->config_version;\n";
$view .= row_wrap("\$app->config_version = $gallery->config_version;");

$data .= "\n";
$view .= row_wrap("&nbsp;");

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
			$cause .= "missing <i>$dep</i> -- it's optional";
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
	echo "<input type=hidden name=$key value=\"{$$key}\">";

	if ($constants[$key]["eval"]) {
		$tmp = $constants[$key]["eval"];
		eval("\$$key = $tmp;");
	}

	if (!$$key) {
		if ($constants[$key]["optional"]) {
			$data .= "// optional <i>$key</i> missing\n";
			$view .= row_wrap("// optional <i>$key</i> missing");
		} else {
			$view .= row_wrap(error_missing($constants[$key]["desc"]));
			$error = 1;
		}
	} else {
		if ($constants[$key]["no-trailing-slash"]) {
			$$key = preg_replace("/\/$/", "", $$key);
		}

		if ($constants[$key]["must-be-file"]) {
			if (!is_file($$key)) {
				$view .= row_wrap(error_format("$key: <b>" . $$key . 
					"</b> must be a valid file (not a directory)!"));
				$error = 1;
				continue;
			}
		}

		if ($constants[$key]["must-be-executable"]) {
			if (!is_executable($$key)) {
				$view .= row_wrap(error_format("$key: <b>" . $$key . 
					"</b> must be an executable file!"));
				$error = 1;
				continue;
			}
		}

		if ($constants[$key]["require-write-dir"]) {
			$dir = $$key;
			if (!file_exists($dir)) {
				$view .= row_wrap(error_format("Directory <b>$dir</b> does not exist.  " .
						"Please create it."));
				$error = 1;
				continue;
			}

			if (!is_dir($dir)) {
				$view .= row_wrap(error_format("<b>$dir</b> exists but is not a directory.  " .
						"Please fix this."));
				$error = 1;
				continue;
			}

			if (!is_writeable($dir)) {
				$view .= row_wrap(error_format("$dir exists, but is not writeable by the " .
						"webserver user.  Try:<br><center><b><code>" .
						"chown -R " . $webserver_user . " $dir</code>"));
				$error = 1;
				continue;
			}
		}

		if (!$constants[$key]["dont-write"]) {
			$data .= one_constant($key, $$key);
			$view .= row_wrap(one_constant($key, $$key));
		}
	}
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

<input type=hidden name=back_page value="form">
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

