<center>
<font size=+2> Gallery Setup: Step 4 </font>
<br>
<table width=80%><tr><td>
Confirm that the settings you entered came through correctly.
If you find this page confusing, you can probably safely ignore it
(unless you see stuff in red which indicates an error).
</td></tr></table>

<center>
<table width=90% border=0>
<pre>

<?php
require("../Version.php");

$error = 0;

$data .= "<" . "?php\n";

$data .= "/* \n"
        ." * Protect against very old versions of 4.0 (like 4.0RC1) which  \n"
        ." * don't implicitly create a new stdClass() when you use a variable \n"
        ." * like a class. \n"
        ." */ \n"
        ."if (!\$gallery) { \n"
        ."        \$gallery = new stdClass(); \n"
        ."}\n"
        ."if (!\$gallery->app) { \n"
        ."        \$gallery->app = new stdClass(); \n"
        ."}\n\n";

$data .= "/* Version  */\n";
$view .= row_wrap("/* Version  */");

$data .= "\$gallery->app->config_version = $gallery->config_version;\n";
$view .= row_wrap("\$gallery->app->config_version = $gallery->config_version;");

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

$graphtool = "";
$graphpath = "";
$graphtest = array();

foreach ($constants as $key => $val) {
	echo "<input type=hidden name=$key value=\"{$$key}\">";

	if ($constants[$key]["eval"]) {
		$tmp = $constants[$key]["eval"];
		eval("\$$key = $tmp;");
	}

	if (!strcmp($key, "graphics")) {
		$graphtool = $$key;
	}
	
	if (!strcmp($$key, "")) {
		if (!strcmp($key, "pnmDir")) {
		        $graphtest['pnmDir'] = 1;
		}
		else if (!strcmp($key, "ImPath")) {
		        $graphtest['ImPath'] = 1;
		}
		if ($constants[$key]["optional"]) {
			$data .= "// optional <i>$key</i> missing\n";
			$view .= row_wrap("// optional <i>$key</i> missing");
		} else {
			$view .= row_wrap(error_missing($constants[$key]["prompt"]));
			$error = 1;
		}
	} else {
		if ($constants[$key]["filename"]) {
			$$key = fs_export_filename($$key);
		}

		if ($constants[$key]["no-trailing-slash"]) {
			$$key = ereg_replace("\/$", "", $$key);
			/* Catch windows paths too. */
			$$key = ereg_replace("\\$", "", $$key);
		}

		if ($constants[$key]["must-be-url"]) {
			if (!ereg("^http", $$key)) {
				$view .= row_wrap(error_format("$key: <b>" . $$key . 
					"</b> must be an absolute URL!"));
				$error = 1;
				continue;
			}
		}

		if ($constants[$key]["must-be-file"]) {
			$dir = dirname($$key);
			if (!inOpenBasedir($dir)) {
				$warn_about_open_basedir = 1;
			} else {
				if (!@is_file($$key)) {
					$view .= row_wrap(error_format("$key: <b>" . $$key . 
						"</b> must be a valid file (not a directory)!"));
					$error = 1;
					continue;
				}
			}
		}

		if ($constants[$key]["must-be-number"]) {
			if (ereg("[^0-9]",$$key)) {
				$view .= row_wrap(error_format("$key: <b>\"" . $$key .
					"\"</b> must be a number"));
				$error = 1;
				continue;
			}
		}

		if ($constants[$key]["must-not-be-zero"]) {
			if ($$key == 0) {
				$view .= row_wrap(error_format("$key: <b>" . $$key .
					"</b> must not be zero"));
				$error = 1;
				continue;
			}
		}

		if ($constants[$key]["must-be-executable"]) {
			if (!inOpenBasedir($dir)) {
				$warn_about_open_basedir = 1;
			} else if (!fs_is_executable($$key)) {
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

			if (!is_writeable($dir) ||
			    !test_write_to_dir($dir)) {
				$view .= row_wrap(error_format("$key: $dir exists, but is not " .
						"writeable by the webserver user.  Try:" .
						"<br><b><code>" .
						"chown -R " . $webserver_user . " $dir" .
						"</code></b><br><br>" .
						"or if that doesn't work:" .
						"<br><b><code>" .
						"chmod -R 777 $dir</code>" .
						"</code></b><br><br>" .
					        "Also make sure that if you have an " .
					        "<a href=phpinfo.php>open_basedir restriction</a> " .
						", then this tmp directory " .
					        "should be under the open_basedir path"));
				$error = 1;
				continue;
			}
		}

		if (!$constants[$key]["dont-write"]) {
			$data .= one_constant($key, $$key);
			$view .= row_wrap(one_constant($key, $$key));
		}

		if ($constants[$key]["verify-func"]) {
			$func = $constants[$key]["verify-func"];
			if ($constants[$key]["verify-func-takes-graphics-type"]) {
			    list($success, $fail) = $func($$key, $graphtool);
			} else {
			    list($success, $fail) = $func($$key);
			}
			
			foreach ($fail as $fkey => $fval) {
				$view .= row_wrap(error_format($constants[$key][$fkey]));
				if (is_array($fval)) {
					foreach ($fval as $msg) {
						if ($val) {
							$view .= row_wrap(error_format($msg));
						}
					}
				}
				$error = 1;
			}
			/* Needed for the pnmtojpeg<->ppmtojpeg silliness */
			if (!strcmp($key, "pnmDir")) {
				$graphpath  = $$key;
			}
		}
	}
}

/*
 * In NetPBM 9.19, they renamed "ppmtojpeg" to "pnmtojpeg".  !@#$%
 * Add a special case constant to tell us which binary we located on
 * the machine (so we know which one to use!).  It sucks that we
 * have to do this!
 *
 * Don't bother if we are configuring for ImageMagick.
 */
if (empty($graphtest['pnmDir'])) {
	if (!inOpenBasedir($graphpath)) {
		/* Can't tell -- assume they're using pnmtojpeg and hope for the best */
		$data .= one_constant("pnmtojpeg", "pnmtojpeg");
		$view .= row_wrap(one_constant("pnmtojpeg", "pnmtojpeg"));
		$warn_about_open_basedir = 1;
	} else {
		if (fs_file_exists(fs_executable($graphpath . "/pnmtojpeg"))) {
			$data .= one_constant("pnmtojpeg", "pnmtojpeg");
			$view .= row_wrap(one_constant("pnmtojpeg", "pnmtojpeg"));
		} else if (fs_file_exists(fs_executable($graphpath . "/ppmtojpeg"))) {
			$data .= one_constant("pnmtojpeg", "ppmtojpeg");
			$view .= row_wrap(one_constant("pnmtojpeg", "ppmtojpeg"));
		} else {
			$error = 1;
			$view .= row_wrap(error_format(
				"Could not find <i>pnmtojpeg</i> or <i>ppmtojpeg</i>. ".
				"Please check your configuration (or use ImageMagick)."));
		}
	}
}

/* 
 * Make sure they picked a graphics package to use, and that the package they
 * chose is configured properly.
 */
if (!empty($graphtest['pnmDir']) && !empty($graphtest['ImPath'])) {
    $view .= row_wrap(error_format("You must specify one of <i>pnmDir</i> or ".
				   "<i>ImPath</i> for Gallery to function!!"));
    $error = 1;
} else if (!strcmp($graphtool, "ImageMagick") && !empty($graphtest['ImPath'])) {
    $view .= row_wrap(error_format("ImageMagick is not properly configured for use ".
				   "as a graphics package.  Make sure you entered ".
				   "a path to the ImageMagick binaries in step 2."));
    $error = 1;
} else if (!strcmp($graphtool, "NetPBM") && !empty($graphtest['pnmDir'])) {
    $view .= row_wrap(error_format("NetPBM is not properly configured for use ".
				   "as a graphics package.  Make sure you entered ".
				   "a path to the NetPBM binaries in step 2."));
    $error = 1;
}

if ($warn_about_open_basedir) {
	$view .= row_wrap("<hr><b>Note:</b> Your server has the PHP open_basedir restriction " .
			  "set, and certain of your paths are not in the list of restricted paths " .
			  "(" .ini_get('open_basedir') . ")  This doesn't mean that they won't ".
			  "work, however it does mean that we can't verify that the paths are " .
			  "correct.  If you are unable to upload photos correctly, please refer " .
			  "to the FAQ and to the <a href=diagnostics.php>diagnostics</a> to " .
			  "resolve your problems." .
			  "<hr>");
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

<input type=hidden name=data value=<?php echo urlencode($data)?>>
</pre>
</table>

<input type=hidden name=back_page value="defaults">
<input type=hidden name=next_page value="write">
<p>
<input type=submit name="go_back" value="<- Edit Config">
<?php if (!$error) { ?>
 <input type=submit name="go_next" value="Save Config ->">
<?php } else { ?>
<br>
<font color=red>There are errors in your configuration.
<br>
Please go back and fix them!</font>
<?php } ?>

