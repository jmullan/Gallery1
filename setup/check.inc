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
<input type=hidden name=next_page value="form">

<?
foreach ($checklist as $check) {
?> 
	<tr>
	<td width=60% valign=top><?=$check["desc"]?></td>
	<td width=5%> &nbsp; </td>
	<td valign=top>
<?
	$func = $check["func"];
	if ($func()) {
		echo "<font color=green><b>Success</font>";
	} else {
		if ($check["optional"]) {
			echo "<font color=yellow><b>Warning!</b></font>";
			echo "<br>$check[why]";
			$warning++;
		} else {
			echo "<font color=red><b>Failed!</b></font>";
			echo "<br>$check[why]";
			$error = 1;
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
Your installation has warnings, but life is short so let's GO!
<? 	} else { ?>
Your installation passed with flying colors!  Go go go!
<? 	} ?>
 <p>
 <input type=submit name="go_next" value="Configure Gallery ->">
<? } ?>
