<?
if ($save) {
	$album->fields["bgcolor"] = $bgcolor;
	$album->fields["font"] = $font;
	$album->fields["border"] = $border;
	$album->fields["bordercolor"] = $bordercolor;
	$album->fields["background"] = $background;
	$album->fields["returnto"] = $returnto;
	$album->save();
	reload();
}

require('style.php');
?>

<center>

You can modify the appearance of your photo album here.

<form action=edit_appearance.php method=POST>
<input type=hidden name="save" value=1>
<table>
<tr>
<td>Background Color</td>
<td><input type=text name="bgcolor" value=<?=$album->fields["bgcolor"]?>></td>
</tr>
<tr>
<td>Background Image (URL)</td>
<td><input type=text name="background" value=<?=$album->fields["background"]?>></td>
</tr>
<tr>
<td>Font</td>
<td><input type=text name="font" value="<?=$album->fields["font"]?>"></td>
</tr>
<tr>
<td>Borders</td>
<td><select name="border"><?= selectOptions($album, "border", array("off", 1, 2, 3, 4)) ?></select></td>
</tr>
<tr>
<td>Border color</td>
<td><input type=text name="bordercolor" value=<?=$album->fields["bordercolor"]?>></td>
</tr>
<tr>
<td>Show <i>Return to</i> link at the bottom</td>
<td><select name="returnto"><?= selectOptions($album, "returnto", array("yes", "no")) ?></select></td>
</tr>
<tr>
</table>

<p>

<input type=submit name="submit" value="Apply">
<input type=reset value="Undo">
<input type=submit name="submit" value="Close" onclick='parent.close()'>

</form>


