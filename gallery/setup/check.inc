<?php /* $Id$ */ ?>

<input type="hidden" name="this_page" value="check">
<input type="hidden" name="next_page" value="constants">

<div class="header"><?php echo sprintf (_("Gallery Configuration Wizard: Step %d"),1); ?></div>

<div class="sitedesc">
<?php 
	echo _("This is the Gallery system check page.") . "  ";
	echo _("This page will validate your installation to make sure that you have all the necessary components and permissions on this system and that everything is configured reasonably.") ;
?>
<p>
<?php 
	$diagnostics_page='<a href="diagnostics.php">' . _("Gallery Diagnostics Page").'</a>';
	$help_page='<a href="http://gallery.sourceforge.net/help.php">'. _("Gallery Help Page"). '</a>';
	$docs_page = galleryDocs();

	if ($docs_page) {
		echo sprintf(_("Having problems?  Try the %s, %s and %s."),
				$docs_page, $diagnostics_page, $help_page);
	} else {
		echo sprintf(_("Having problems?  Try the %s and %s."),
			$diagnostics_page, $help_page);
	}
?>
</p>
</div>

<p />

<table width="100%">
<?php
$warning =0;
foreach ($checklist as $check) {
	if( isset($check["enabled"]) && $check["enabled"] == "no") continue;
?> 
	<tr>
		<td>
		<table class="inner" width="100%">
		<tr>
			<td class="desc" width="50%" valign="top"><?php echo $check["desc"] ?></td>
			<td width="5%">&nbsp;</td>
			<td class="desc" valign="top">
<?php
	$func = $check["func"];
	list($success, $fail, $warn) = $func();
	foreach ($success as $key) {
		echo "\t\t\t\t". '<p class="success">'. _("Success") . '</p>';
		if (strcmp($key, "")) {
			$msg = "<i>$key</i>";
		}
		echo "\n\t\t\t\t<p>$msg</p>";
	}
	
	$openBasedir = ini_get('open_basedir');
	foreach ($fail as $key => $val) {
		if (isset($check['optional']) && $check['optional'] == 1) {
			if (isset($check["serious"]) && $check["serious"] == 1) {
				$serious_warning = true;
				$text = _("Serious Warning");
				$class="error";
			} else {
				$text = _("Warning");
				$class= "warning";
			}
			echo "\t\t\t<p class=\"$class\">$text!</p>";
			if (isset($check) && isset($check[$key])) {
				echo "\n\t\t\t<p>$check[$key]</p>";
			}
			$warning++;
		} else {
			echo "\t\t\t\t<p class=\"error\">" . _("Failed!") ."</p>";
			echo "\n\t\t\t<p>" . $check[$key] ."</p>";
			$error = 1;
		}

		if (isset($check["open-basedir-note"]) && !empty($openBasedir)) {
			print '<p>'. $check["open-basedir-note"] . '</p>';
		}

		if (is_array($val)) {
			foreach ($val as $msg) {
				if ($val) {
					echo "<p class=\"error\">$msg</p>";
				}
			}
		}
		else if (is_string($val)) {
			echo '<p>'. $val . '</p>';
		}
	}

	if (isset($warn)) {
		foreach ($warn as $key => $val) {
			if ($key === 'nocolor') continue;
			echo "\t\t\t\t". '<p class="warning">'. _("Warning") . '</p>';
                	echo "\n\t\t\t\t<p>";
			if (isset($check[$key])) {
				echo "\t\t\t\t<p>". $check[$key] . "</p>";
			}

			if (! isset($warn['nocolor'])) {
				$class="warningpct";
			} else {
				$class="";
			}
			echo "\n\t\t\t\t<p class=\"$class\">$val</p>";
	        }
	}
?>

			</td>
		</tr>
		</table>
		</td>
	</tr>

<?php
}
?>

</table>

<div align="center">
<?php 
	if (isset($error)) {
		echo '<p class="error" id="bottom">';
		echo _("There are errors in your configuration that will prevent Gallery from functioning properly.");
		echo '<br>';
		echo _("You must fix them before you can continue.");
	} else {
		if ($warning >0) {
			if (isset($serious_warning)) {
				echo '<p class="error" id="bottom">';
				echo _("Your installation has <b>serious warnings</b>!<br>Continue at your own risk...");
			} else {
				echo '<p class="warning" id="bottom">';
				echo _("Your installation has warnings, but this is usually OK.  Keep going!");
			}
		} else {
			echo '<p class="success" id="bottom">';
			echo _("Your installation passed with flying colors!  Go go go!");
		}
		echo '</p><p>';
		echo '<input type=submit name="go_next" value="'. _("Next Page") . ' ->">';
		echo '</p><div>';
}
?>
</p>
