<?php /* $Id$ */ ?>
<center>
<font size=+2> Gallery Setup: Step 1 </font>
<br>
<table width=80%><tr><td>

This is the Gallery system check page. This page will validate your
installation to make sure that you have all the necessary components
and permissions on this system and that everything is configured
reasonably.

<p>

<center>
Having problems?  Try the <a href=diagnostics.php>Gallery Diagnostics Page</a> and the <a href="http://gallery.sourceforge.net/help.php">Gallery Help Page</a>
</center>

</td></tr></table>

<p> 

<center>
<table width=800>
<input type=hidden name=this_page value="check">
<input type=hidden name=next_page value="constants">

<?php
foreach ($checklist as $check) {
?> 
	<tr>
	<td width=50% valign=top><?php echo $check["desc"]?></td>
	<td width=5%> &nbsp; </td>
	<td valign=top>
<?php
	$func = $check["func"];
	list($success, $fail) = $func();
	foreach ($success as $key) {
		if (strcmp($key, "")) {
			$msg = "Success<br><i>$key</i>";
		} else {
			$msg = "Success";
		}
		echo "<font color=green><b>$msg</b></font><br>";
	}

	$openBasedir = ini_get('open_basedir');
	foreach ($fail as $key => $val) {
		if ($check["optional"]) {
			if ($check["serious"]) {
				$serious_warning++;
				$text = "Serious Warning";
				$color = "red";
			} else {
				$text = "Warning";
				$color = "yellow";
			}
			echo "<font color=$color><b>$text!</b></font>";
			echo "<br>$check[$key]<p>";
			$warning++;
		} else {
			echo "<font color=red><b>Failed!</b></font>";
			echo "<br>$check[$key]<p>";
			$error = 1;
		}

		if ($check["open-basedir-note"] && !empty($openBasedir)) {
			print $check["open-basedir-note"];
		}

		if (is_array($val)) {
			foreach ($val as $msg) {
				if ($val) {
					echo "<font color=red>$msg</font><br>";
				}
			}
		}
	}
?>
	</td>
	</tr>

	<tr><td colspan=3> <hr width=50%> </td></tr>
<?php
}
?>

</table>
<p>
<?php if ($error) { ?>
 There are errors in your configuration that will prevent
<br> Gallery from functioning properly.  You must fix them 
<br> before you can continue.  
<?php } else { ?>
<?php	if ($warning) { ?>
<?php		if ($serious_warning) { ?>
Your installation has <b>serious warnings</b>!  Continue at your own risk...
<?php 		} else { ?>
Your installation has warnings, but life is short so let's GO!
<?php 		} ?>
<?php 	} else { ?>
Your installation passed with flying colors!  Go go go!
<?php 	} ?>
 <p>
 <input type=submit name="go_next" value="Configure Gallery ->">
<?php } ?>
