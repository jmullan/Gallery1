<title> Gallery Setup: Step 1 </title>
<center>
<font size=+2>This is the Gallery system check page. </font>
<br> This page will validate your installation to make sure
<br> that you have all the various libraries that you need
<br> installed on the system and that everything is configured
<br> reasonably.

<p> 

<center>
<table width=75%>
<input type=hidden name=this_page value="check">
<input type=hidden name=next_page value="form">

<?
foreach ($checklist as $check) {
?> 
	<tr>
	<td width=45% valign=top><?=$check["desc"]?></td>
	<td width=10%> &nbsp; </td>
	<td width=45% valign=top>
<?
	$func = $check["func"];
	if ($func()) {
		echo "<font color=green><b>Success</font>";
	} else {
		echo "<font color=red><b>Failed!</b></font>";
		echo "<br>$check[why]";
		$error = 1;
	}
?>
	</td>
	</tr>

	<tr><td> &nbsp; </td></tr>
<?
}
?>

</table>
<p>
<? if (!$error) { ?>
 Your installation is configured properly!
 <br>
 <input type=submit name="go_next" value="Configure Gallery ->">
<? } else { ?>
 There are errors in your configuration that will prevent
<br> Gallery from functioning properly.  You must fix them 
<br> before you can continue.  
<? } ?>
