<center>
<font size=+2> Gallery Setup: Step 1 </font>
<br>
<table width=80%><tr><td>

This is the Gallery system check page. This page will validate your
installation to make sure that you have all the necessary components
and permissions on this system and that everything is configured
reasonably.

</td></tr></table>

<p> 

<center>
<table width=800>
<input type=hidden name=this_page value="check">
<input type=hidden name=next_page value="constants">

<?
foreach ($checklist as $check) {
?> 
	<tr>
	<td width=50% valign=top><?=$check["desc"]?></td>
	<td width=5%> &nbsp; </td>
	<td valign=top>
<?
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
<?
}
?>

</table>
<p>
<? if ($error) { ?>
 There are errors in your configuration that will prevent
<br> Gallery from functioning properly.  You must fix them 
<br> before you can continue.  
<? } else { ?>
<?	if ($warning) { ?>
<?		if ($serious_warning) { ?>
Your installation has <b>serious warnings</b>!  Continue at your own risk...
<? 		} else { ?>
Your installation has warnings, but life is short so let's GO!
<? 		} ?>
<? 	} else { ?>
Your installation passed with flying colors!  Go go go!
<? 	} ?>
 <p>
 <input type=submit name="go_next" value="Configure Gallery ->">
<? } ?>
