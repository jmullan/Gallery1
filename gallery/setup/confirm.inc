<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2004 Bharat Mediratta
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
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 * $Id$
 */
?>

<input type="hidden" name="back_page" value="defaults">
<input type="hidden" name="next_page" value="write">

<div class="header"><?php echo sprintf (_("Gallery Configuration Wizard: Step %d"),4); ?></div>

<?php
/*
** We assume setup/init.php was loaded !
*/

require(GALLERY_BASE. '/Version.php');
$docs=galleryDocs() ? galleryDocs() : _("documentation");
$error = 0;
$view = "";
$outfile_list=array("config.php", ".htaccess");
foreach ($outfile_list as $outfile) {
	if (!fs_file_exists(GALLERY_CONFDIR . "/$outfile")) {
		$error++;
		$view .= "<p>" . gallery_error(sprintf(_("You must create an empty file named %s and chmod it 777 before proceeding.  See %s for more details."), "<i><b>$outfile</b></i>", $docs)) . "</p>\n";
	}
	elseif ($fd = @fs_fopen(GALLERY_CONFDIR ."/$outfile", "a")) {
		fclose($fd);
	} else {
		$error++;
		$view .= gallery_error(sprintf(_("File %s is not writable by the webserver - chmod it 777 before proceeding.  See %s for more details."), "<i><b>$outfile</b></i>", $docs)) . "</p>\n";
	}
}

$data = "<" . "?php\n";
$view .= "<pre>";

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

$data .= "\$gallery->app->config_version = '$gallery->config_version';\n";
$view .= row_wrap("\$gallery->app->config_version = '$gallery->config_version';");

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
	
	if (isset($val["enabled"]) && $val["enabled"] == "no") continue;

	if (isset($val["type"]) && 
			($val["type"] == "block_start" || 
			 $val["type"] == "block_end" ||
			 $val["type"] == "separator" ||
			 $val["type"] == "group_start" ||
			 $val["type"] == "group_end")) { 
		continue;
	}

	$bracketStart = strpos($key, '[');
	$bracketEnd = strpos($key, ']');

/* 
** This is a somewhat strange workaround i made for a strange SunOS with
** register global On an Magic Quotes On.
** The problem is that we have send/get hidden fields which are named like
** foo[bar]. So the result is, the $key=foo[bar] and $$key seems to fail.
** The former solution were to do create a string which is like the value 
** of $key and put it in $real via eval().
** This also seemed not to work.
** 02/2004, Jens Tkotz
** 06/2004, Jens Tkotz 
*/
	if ($bracketStart > 0 && $bracketEnd > 0) {
		$subKey=substr($key,0, $bracketStart);
		$subValue=substr($key, $bracketStart+1, $bracketEnd-($bracketStart+1));
		$newKey=$$subKey;
		key_strip_slashes ($newKey);
		$real = $newKey[$subValue];
	} else {
		$real = $$key;
	}

	if (get_magic_quotes_gpc()) {
		$real = array_stripslashes($real);
	}

	$real = array_str_replace('"', '&quot;', $real);

	if (isset($constants[$key]["eval"])) {
		$tmp = $constants[$key]["eval"];
		eval("\$real = $tmp;");
	}

	if (!strcmp($key, "graphics")) {
		$graphtool = $real;
	}
	
	if ((is_array($real) && join('', $real) === '') || $real == '') {
		if (isset($constants[$key]["remove_empty"])) {
			continue;
		}
		if (isset($constants[$key]["allow_empty"])) {
			$line = one_constant($key, $real);
			$data .= $line;
			$view .= row_wrap($line);
			continue;
		}
		if (!strcmp($key, "pnmDir")) {
		        $graphtest['pnmDir'] = 1;
		}
		else if (!strcmp($key, "ImPath")) {
		        $graphtest['ImPath'] = 1;
		}
		if (isset($constants[$key]["optional"])) {
			$data .= "// ". _("optional") . " <i>$key</i> ". _("missing") ."\n";
			$view .= row_wrap("// ". _("optional") ." <i>$key</i> ". _("missing"));
		} else {
			$view .= error_missing($constants[$key]["prompt"], $key)."<br>";
			$error++;
		}
	} else {
		if (isset($constants[$key]["filename"])) {
			$real = fs_export_filename($real);
		}

		if (isset($constants[$key]["no-trailing-slash"])) {
			$real = trim($real);
			$lastChar = $real[strlen($real)-1];
			if ($lastChar == '/' || $lastChar == '\\') {
				$real = substr($real, 0, -1);
			}
		}

		if (isset($constants[$key]["must-be-url"])) {
			if (!ereg("^http", $real)) {
				$view .= error_row_wrap(gallery_error(sprintf(_("%s: <b>%s</b> must be an absolute URL!"), $key, $real)));
				$error++;
				continue;
			}
		}

		if (isset($constants[$key]["must-be-file"])) {
			$dir = dirname($real);
			if (!inOpenBasedir($dir)) {
				$warn_about_open_basedir = 1;
			} else {
				if (!@is_file($real)) {
					$view .= error_row_wrap(gallery_error(sprintf(_("%s: <b>%s</b> must be a valid file (not a directory)!"), $key, $real)));
					$error++;
					continue;
				}
			}
		}

		if (isset($constants[$key]["must-be-number"])) {
			if (ereg("[^0-9]",$real)) {
				$view .= error_row_wrap(gallery_error(sprintf(_("%s: <b>&quot;%s&quot;</b> must be a positive number"), $key, $real)));
				$error++;
				continue;
			}
		}

		if (isset($constants[$key]["must-not-be-zero"])) {
			if ($real == 0) {
				$view .= error_row_wrap(gallery_error(sprintf(_("%s: <b>%s</b> must not be zero"), $key, $real)));
				$error++;
				continue;
			}
		}

		if (isset($constants[$key]["must-be-executable"])) {
			if (!inOpenBasedir($dir)) {
				$warn_about_open_basedir = 1;
			} else if (!fs_is_executable($real)) {
				$view .= error_row_wrap(gallery_error(sprintf(_("%s: <b>%s</b> must be an executable file!"), $key, $real)));
				$error++;
				continue;
			}
		}

		if (isset($constants[$key]["require-write-dir"])) {
			$dir = $real;
			if (!file_exists($dir)) {
				$view .= error_row_wrap(gallery_error(sprintf(_("Directory %s does not exist.  Please create it."), "<b>$dir</b>")));
				$error++;
				continue;
			}

			if (!is_dir($dir)) {
				$view .= error_row_wrap(gallery_error(sprintf(_("%s exists but is not a directory.  Please fix this."), "<b>$dir</b>")));
				$error++;
				continue;
			}

			if (!is_writeable($dir) ||
			    !test_write_to_dir($dir)) {
				$view .= error_row_wrap(gallery_error(sprintf(_("%s: %s exists, but is not writeable by the webserver user.  Try: %s or if that doesn't work: %s.  Also make sure that if you have an %s restriction then this tmp directory should be under the %s path"),
						$key, $dir,
						"<br><b><code>" .
						"chown -R " . $webserver_user . " $dir" .
						"</code></b><br><br>",
						"<br><b><code>" .
						"chmod -R 777 $dir</code>" .
						"</code></b><br><br>", 
						'<a href="phpinfo.php">open_basedir</a>', 
						'open_basedir')));
				$error++;
				continue;
			}
		}

		if (!isset($constants[$key]["dont-write"])) {
			if (is_array($real)) {
				$line = array_constant($key, $real);
			} else {
				$line = one_constant($key, $real);
			}
			$data .= $line;
			$view .= row_wrap($line);
		}

		if (isset($constants[$key]["verify-func"])) {
			$func = $constants[$key]["verify-func"];
			if (isset($constants[$key]["verify-func-takes-graphics-type"])) {
			    list($success, $fail, $warn) = $func($real, $graphtool);
			} else {
			    list($success, $fail) = $func($real);
			}
			
			foreach ($fail as $fkey => $fval) {
				if (isset($fkey) && isset($constants[$key][$fkey])) {
					$view .= error_row_wrap(gallery_error($constants[$key][$fkey]));
				}
				if (is_string($fval)) {
					$view .= error_row_wrap(gallery_error($fval));
				} else if (is_array($fval)) {
					foreach ($fval as $msg) {
						if ($val) {
							$view .= error_row_wrap(gallery_error($msg));
						}
					}
				}
				$error++;
			}

			/* Needed for the pnmtojpeg<->ppmtojpeg silliness */
			if (!strcmp($key, "pnmDir")) {
				$graphpath  = $real;
			}
		}

		/*
		** This checks for dependencies between to constants values.
		**
		** Note: This breaks if the key of the constant is an array.
		*/
		if (isset($constants[$key]['requires-features']) && $real == 'yes') {
			$reqFeatures = $constants[$key]['requires-features'];
			foreach ($reqFeatures as $reqFeatKey => $desc) {
				if ($$reqFeatKey  == 'no') {
					$view .= error_row_wrap(gallery_error(sprintf(_("%s: <b>%s</b> must be an enabled for this feature to function!"), $key, $desc)));
                                	$error++;
				}
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
			$error++;
			$view .= error_row_wrap(gallery_error(
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
    $view .= error_row_wrap(gallery_error(sprintf(_("You must specify a path for %s or %s for Gallery to function!!"), "<i>netpbm</i>",  "<i>ImageMagick</i>")));  
    $error++;
} else if (!strcmp($graphtool, "ImageMagick") && !empty($graphtest['ImPath'])) {
    $view .= error_row_wrap(gallery_error(sprintf(_("%s is not properly configured for use as a graphics package.  Make sure you entered a path to the %s binaries in step 2."),"ImageMagick","ImageMagick")));
    $error++;
} else if (!strcmp($graphtool, "NetPBM") && !empty($graphtest['pnmDir'])) {
    $view .= error_row_wrap(gallery_error(sprintf(_("%s is not properly configured for use as a graphics package.  Make sure you entered a path to the %s binaries in step 2."),"NetPBM","NetPBM")));
    $error++;
}

if (isset($warn_about_open_basedir)) {
	$view .= row_wrap("<hr><b>". _("Note:") ."</b> ". 
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
	if (isset($val["type"]) && 
			($val["type"] == "block_start" || 
			 $val["type"] == "block_end" ||
			 $val["type"] == "separator" ||
			 $val["type"] == "group_start" ||
			 $val["type"] == "group_end")) { 
		continue;
	}
	if (isset($val["type"]) && $val["type"] == "separator") continue;

	if (!isset($$key) && !isset($defaults[$key]["optional"])) {
		$view .= error_missing($defaults[$key]["prompt"], $key);
		$error++;
	} else {
	        if (get_magic_quotes_gpc()) {
			$$key = array_stripslashes($$key);
		}
		// Protect quote characters to avoid screwing up HTML forms
		$$key = array_str_replace('"', '&quot;', $$key);
		
		if (is_array($$key)) {
			foreach ($$key as $sub_key => $sub_value) {
				if (is_array ($sub_value)) {
					foreach ($sub_value as $sub_sub_key
							=> $sub_sub_value) {
						/* print_photos needs a quoted $sub_key */
						if ($key === "print_photos") {
							$string="\$gallery->app->default[\"$key\"][\"$sub_key\"][\"$sub_sub_key\"] = \"$sub_sub_value\";\n";
						} else {
							$string="\$gallery->app->default[\"$key\"][$sub_key][\"$sub_sub_key\"] = \"$sub_sub_value\";\n";
						}
						$data .= $string;
						$view .= row_wrap($string);
					}
				}
			}
		} else {
			$data .= defaults($key, $$key);
			$view .= row_wrap(defaults($key, $$key));
		}
		if (isset($defaults[$key]["verify-func"])) {
			$func = $defaults[$key]["verify-func"];
			list($success, $fail) = $func($$key);
			foreach ($fail as $fkey => $fval) {
				if (is_string($fval)) {
					$view .= error_row_wrap(gallery_error($fval));
				} else if (is_array($fval)) {
					foreach ($fval as $msg) {
						if ($val) {
							$view .= error_row_wrap(gallery_error($msg));
						}
					}
				}
				$error++;
			}
		}
	}

}
$data .= "?" . ">\n";
?>
<div class="sitedesc">
<?php 
$view .= "</pre>";
if (!$error) { 
	echo _("Confirm that the settings you entered came through correctly.  If you find this page confusing, you can safely ignore it.");
} else {
	echo _("Confirm that the settings you entered came through correctly.  There are errors highlighted below in red which need to be fixed before you can save your settings.");
}
?>
</div>
<p align="center">
	<input type=submit name="go_back" disabled="disabled" value="&lt;- <?php echo _("Edit Config") ?>">
<?php if (!$error) {
		echo '<input type="submit" name="go_next" disabled="disabled" value="' . _("Save Config") .' -&gt;">';
	} else {
		echo '<input type="button" name="refresh" disabled="disabled" value="' . _("Refresh") . '" onclick="location.reload()">';
		echo '<br><div class="error" id="top">';
		echo pluralize_n2(ngettext("One error.", "%d errors.", $error), $error, _("No errors."));
		echo _("See Details below.");
		echo '</div>';
	}
?>
</p>

<?php echo $view; ?>
<input type="hidden" name="data" value="<?php echo urlencode($data) ?>">

<p align="center">
<input type="submit" name="go_back" disabled="disabled" value="&lt;- <?php echo _("Edit Config") ?>">
<?php if (!$error) {
	echo '<input type="submit" name="go_next" disabled="disabled" value="'. _("Save Config") . '-&gt;">';
} else {
	echo '<p class="error" id="bottom">';
	echo _("There are errors in your configuration.");
	echo '<br>' . _("Please go back and fix them!");
    echo '</p>';
} ?>
</p>
