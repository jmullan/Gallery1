<?php /* $Id$ */ ?>
<center>
<font size=+2><?php echo _("Gallery Setup: Step 4") ?></font>
<br>
<table width=80%><tr><td>
<?php 
echo _("Confirm that the settings you entered came through correctly.  If you find this page confusing, you can probably safely ignore it  (unless you see stuff in red which indicates an error)")
?>
</td></tr></table>

<center>
<table width=90% border=0>

<?php
require("../Version.php");

$error = 0;

$data .= "<" . "?php\n";

$data .= "/* \n"
        ." * Protect against very old versions of 4.0 (like 4.0RC1) which  \n"
        ." * don't implicitly create a new stdClass() when you use a variable \n"
        ." * like a class. \n"
        ." */ \n"
        ."if (!isset(\$gallery)) { \n"
        ."        \$gallery = new stdClass(); \n"
        ."}\n"
        ."if (!isset(\$gallery->app)) { \n"
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
			$cause .= _("missing") ." <i>$dep</i> -- " . _("it's optional");
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
	$real = $$key;
	if (ereg("^(..*)\[.*\]$", $key, $matches)) {
		$line='$real = $'.$key . ';';
		eval($line);
	}

        if (get_magic_quotes_gpc()) {
	    $real = stripslashes($real);
	}

	// Protect quote characters to avoid screwing up HTML forms
	$real = str_replace('"', '&quot;', $real);

	if (is_array($real)) {
		foreach ($real as $value) {
			echo "<input type=hidden name=${key}[] value=\"{$value}\">";
		}
	} else {
		echo "<input type=hidden name=$key value=\"{$real}\">";
	}

	if ($constants[$key]["eval"]) {
		$tmp = $constants[$key]["eval"];
		eval("\$$key = $tmp;");
	}

	if (!strcmp($key, "graphics")) {
		$graphtool = $real;
	}
	
	if (!strcmp($real, "")) {
		if (!strcmp($key, "pnmDir")) {
		        $graphtest['pnmDir'] = 1;
		}
		else if (!strcmp($key, "ImPath")) {
		        $graphtest['ImPath'] = 1;
		}
		if ($constants[$key]["optional"]) {
			$data .= "// ". _("optional") . " <i>$key</i> ". _("missing") ."\n";
			$view .= row_wrap("// ". _("optional") ." <i>$key</i> ". _("missing"));
		} else {
			$view .= row_wrap(error_missing($constants[$key]["prompt"]));
			$error = 1;
		}
	} else {
		if ($constants[$key]["filename"]) {
			$real = fs_export_filename($real);
		}

		if ($constants[$key]["no-trailing-slash"]) {
			$real = ereg_replace("\/$", "", $real);
			/* Catch windows paths too. */
			$real = ereg_replace("\\$", "", $real);
		}

		if ($constants[$key]["must-be-url"]) {
			if (!ereg("^http", $real)) {
				$view .= row_wrap(error_format("$key: <b>" . $real . 
					"</b> ". _("must be an absolute URL!")));
				$error = 1;
				continue;
			}
		}

		if ($constants[$key]["must-be-file"]) {
			$dir = dirname($real);
			if (!inOpenBasedir($dir)) {
				$warn_about_open_basedir = 1;
			} else {
				if (!@is_file($real)) {
					$view .= row_wrap(error_format("$key: <b>" . $real . 
						"</b> " . _("must be a valid file (not a directory)!")));
					$error = 1;
					continue;
				}
			}
		}

		if ($constants[$key]["must-be-number"]) {
			if (ereg("[^0-9]",$real)) {
				$view .= row_wrap(error_format("$key: <b>\"" . $real .
					"\"</b> ". _("must be a number")));
				$error = 1;
				continue;
			}
		}

		if ($constants[$key]["must-not-be-zero"]) {
			if ($real == 0) {
				$view .= row_wrap(error_format("$key: <b>" . $real .
					"</b> ". _("must not be zero")));
				$error = 1;
				continue;
			}
		}

		if ($constants[$key]["must-be-executable"]) {
			if (!inOpenBasedir($dir)) {
				$warn_about_open_basedir = 1;
			} else if (!fs_is_executable($real)) {
				$view .= row_wrap(error_format("$key: <b>" . $real . 
					"</b> " . _("must be an executable file!")));
				$error = 1;
				continue;
			}
		}

		if ($constants[$key]["require-write-dir"]) {
			$dir = $real;
			if (!file_exists($dir)) {
				$view .= row_wrap(error_format(_("Directory") ." <b>$dir</b> " . _("does not exist.  Please create it.")));
				$error = 1;
				continue;
			}

			if (!is_dir($dir)) {
				$view .= row_wrap(error_format("<b>$dir</b> " . _("exists but is not a directory.  Please fix this.")));
				$error = 1;
				continue;
			}

			if (!is_writeable($dir) ||
			    !test_write_to_dir($dir)) {
				$view .= row_wrap(error_format("$key: $dir " . _("exists, but is not writeable by the webserver user.  Try:") .
						"<br><b><code>" .
						"chown -R " . $webserver_user . " $dir" .
						"</code></b><br><br>" .
						_("or if that doesn't work:") .
						"<br><b><code>" .
						"chmod -R 777 $dir</code>" .
						"</code></b><br><br>" .
					        _("Also make sure that if you have an") .
					        " <a href=phpinfo.php>open_basedir ". _("restriction") ."</a> " .
						", " . _("then this tmp directory should be under the open_basedir path")));
				$error = 1;
				continue;
			}
		}

		if (!$constants[$key]["dont-write"]) {
			if (is_array($real)) {
				$line = array_constant($key, $real);
			} else {
				$line = one_constant($key, $real);
			}
			$data .= $line;
			$view .= row_wrap($line);
		}

		if ($constants[$key]["verify-func"]) {
			$func = $constants[$key]["verify-func"];
			if ($constants[$key]["verify-func-takes-graphics-type"]) {
			    list($success, $fail) = $func($real, $graphtool);
			} else {
			    list($success, $fail) = $func($real);
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
				$graphpath  = $real;
			}
		}
	}
}

/*
 * In NetPBM 9.19, they renamed "ppmtojpeg" to "pnmtojpeg".  !@#$%
 *
 * Don't bother if we are configuring for ImageMagick.
 */
if (!strcmp($graphtool, "NetPBM") && empty($graphtest['pnmDir'])) {
	/* 
	 * If the graph path is not in the open_basedir list, then we can't tell.
         * Assume whatever they entered is correct and hope for the best
	 */
	if (inOpenBasedir($graphpath)) {
		if (!fs_file_exists(fs_executable($graphpath . "/" . $pnmtojpeg))) {
			$error = 1;
			$view .= row_wrap(error_format(
				sprintf(_("Could not find %s.  Check your NetPBM directory to see which PNG->JPEG converter you have (or just try using %s instead).  Please check your configuration (or use ImageMagick)."),
					"<i>$graphpath/$pnmtojpeg</i>",
					($pnmtojpeg == "pnmtojpeg" ? "ppmtojpeg" : "pnmtojpeg"))));
		}
	}
}

/* 
 * Make sure they picked a graphics package to use, and that the package they
 * chose is configured properly.
 */
if (!empty($graphtest['pnmDir']) && !empty($graphtest['ImPath'])) {
    $view .= row_wrap(error_format(_("You must specify one of <i>pnmDir</i> or <i>ImPath</i> for Gallery to function!!")));
    $error = 1;
} else if (!strcmp($graphtool, "ImageMagick") && !empty($graphtest['ImPath'])) {
    $view .= row_wrap(error_format(_("ImageMagick is not properly configured for use as a graphics package.  Make sure you entered a path to the ImageMagick binaries in step 2.")));
    $error = 1;
} else if (!strcmp($graphtool, "NetPBM") && !empty($graphtest['pnmDir'])) {
    $view .= row_wrap(error_format(_("NetPBM is not properly configured for use as a graphics package.  Make sure you entered a path to the NetPBM binaries in step 2.")));
    $error = 1;
}

if ($warn_about_open_basedir) {
	$view .= row_wrap("<hr><b>". _("Note") .":</b> ". 
			_("Your server has the PHP open_basedir restriction set, and certain of your paths are not in the list of restricted paths") .
			" (" . ini_get('open_basedir') . ")  " .
			sprintf(_("This doesn't mean that they won't work, however it does mean that we can't verify that the paths are correct.  If you are unable to upload photos correctly, please refer to the FAQ and to the %sdiagnostics%s to resolve your problems."),
 			'<a href="diagnostics.php">', '</a>').
			  "<hr>");
}

$data .= "\n";
$view .= row_wrap("&nbsp;");
$data .= "/* Defaults */\n";
$view .= row_wrap("/* Defaults */\n");
foreach ($defaults as $key => $val) {
	if (!$$key && !$defaults[$key]["optional"]) {
		$view .= error_missing($defaults[$key]["desc"]);
		$error = 1;
	} else {
	        if (get_magic_quotes_gpc()) {
		    $$key = stripslashes($$key);
		}
		// Protect quote characters to avoid screwing up HTML forms
		$$key = str_replace('"', '&quot;', $$key);
		
		$data .= defaults($key, $$key);
		$view .= row_wrap(defaults($key, $$key));
		if ($defaults[$key]["verify-func"]) {
			$func = $defaults[$key]["verify-func"];
			list($success, $fail) = $func($$key);
			foreach ($fail as $fkey => $fval) {
				if (is_array($fval)) {
					foreach ($fval as $msg) {
						if ($val) {
							$view .= row_wrap(error_format($msg));
						}
					}
				}
				$error = 1;
			}
		}
	}
	echo "<input type=hidden name=$key value=\"{$$key}\">";

}
$data .= "?" . ">\n";
?>

<input type=submit name="go_back" value="<- <?php echo _("Edit Config") ?>">
<?php if (!$error) { ?>
 <input type=submit name="go_next" value="<?php echo _("Save Config") ?> ->">
<?php } ?>

<pre>
<?php echo $view; ?>

<input type=hidden name=data value=<?php echo urlencode($data) ?>>
</pre>
</table>

<input type=hidden name=back_page value="defaults">
<input type=hidden name=next_page value="write">
<p>
<input type=submit name="go_back" value="<- <?php echo _("Edit Config") ?>">
<?php if (!$error) { ?>
 <input type=submit name="go_next" value="<?php echo _("Save Config") ?> ->">
<?php } else { ?>
<br>
<font color=red><?php echo _("There are errors in your configuration.") ?>
<br>
<?php echo _("Please go back and fix them!") ?></font>
<?php } ?>

