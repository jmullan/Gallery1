<?php /* $Id$ */ ?>

<input type="hidden" name="this_page" value="constants">
<input type="hidden" name="next_page" value="defaults">
<input type="hidden" name="back_page" value="check">

<div class="header"><?php echo sprintf (_("Gallery Configuration Wizard: Step %d"),2); ?></div>

<div class="sitedesc">
<?php 
	echo _("In this step you'll customize various settings for Gallery.  If this is the first time you've customized Gallery, you'll notice that we've tried to pick reasonable defaults for you.  Even so, you'll probably have to make at least some minor changes.  If you've already configured Gallery before, then you'll notice that this page already has your current settings loaded, so you probably won't need to do much.");
?>
</div>

<p align="center">
	<input type="submit" name="go_back" value="<- <?php echo _("Previous Page") ?>">
	<input type="submit" name="go_next" value="<?php echo _("Next Page") ?> ->">
	<input type="submit" name="go_defaults" value="<?php echo _("Use Defaults") ?>">

</p>



<?php
$i = 0;

foreach ($constants as $key => $val) {
	if( isset($val["enabled"]) && $val["enabled"] == "no") continue;
	
	if( isset($val["type"]) && ($val["type"] === "separator")) {
		echo "\n<table width=\"100%\">";
		echo make_separator($key, $val);
		continue;
	} else {
		if (isset($val["type"]) && ($val["type"] === "block_start" )) {
			echo "\n<table width=\"100%\" class=\"inner\">";
			echo evenOdd_row(make_fields($key, $val), $i % 2);
			continue;
		}
		if (isset($val["type"]) && ($val["type"] === "block_end" )) {
			echo evenOdd_row(make_fields($key, $val), $i++ % 2);
			echo "\n<tr><td colspan=\"2\"> &nbsp; </td></tr>";
			echo "\n</table>";
			continue;
		}


		$onThisPage[$key] = 1;
		$preserve[$key] = 1;

		/* if the variable is hidden, lock it in as we don't want to use previous values*/
		if (isset($val["type"]) && ($val["type"] === "hidden")) {
		       	if (!empty($val["persistent"]) && 
					isset($gallery->app->$key)) {
				$val["value"] = $gallery->app->$key;
			}
			else if (is_array($val["value"])) {
				$key=$key."[]";
				$val["value"]=$val["value"][0];
			}
			if (ereg("^(..*)\[.*\]$", $key, $matches)) {
				unset($preserve[$key]);
				$preserve[$matches[1]] = 1;
				unset($onThisPage[$key]);
				$onThisPage[$matches[1]] = 1;
			}
			list($f1, $f2) = make_fields($key, $val);
			echo $f2;
		} else {
			echo "\n<table width=\"100%\" class=\"inner\">";
			/* Support $app-> for backwards compatibility */
			if (isset($app->$key)) {
				$val["value"] = $app->$key;
			}
	
			/* $gallery->app-> is the desired container, however */
			if (isset($gallery->app->$key)) {
				$val["value"] = $gallery->app->$key;
			}
			else {
				@eval ("\$defn=\$gallery->app->$key;");
				if ($defn) {
					eval ('$val["value"] = $gallery->app->'.$key.';');
				}
			}
			if (!isset($go_defaults)) {
				if (ereg("^(..*)\[.*\]$", $key, $matches)) {
					$line='if (isset($'.$key.')) $val["value"] = $'.$key . ';';
					eval($line);
					unset($preserve[$key]);
					$preserve[$matches[1]] = 1;
					unset($onThisPage[$key]);
					$onThisPage[$matches[1]] = 1;
				}
				else if( isset($$key)) {
					$val["value"] = $$key;
	
				}
				if (get_magic_quotes_gpc()) {
					if (is_array($val["value"])) {
						if (function_exists('array_map')) { 
							$val["value"] = array_map("stripslashes", $val["value"]);
						}
					} else {
						$val["value"] = stripslashes($val["value"]);
					}
				}
			}

			// Protect quote characters to avoid screwing up HTML forms
			$val["value"] = str_replace('"', "&quot;", $val["value"]);
	
			echo evenOdd_row(make_fields($key, $val), $i++ % 2);
		}
	}
	if (isset($val["type"]) && ($val["type"] === "hidden")) {
		// aasd
	} else {
		echo "\n</table>";
	}
}
?>
<p align="center">
	<input type=submit name="go_back" value="<- <?php echo _("Previous Page") ?>">
	<input type=submit name="go_next" value="<?php echo _("Next Page") ?> ->">
	<input type=submit name="go_defaults" value="<?php echo _("Use Defaults") ?>">
</p>
