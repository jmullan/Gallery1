<?
if ($save) {
	$album->fields[$field] = $data;
	$album->save();
	dismissAndReload();
	return;
}

require('style.php');
?>

<center>
Edit the <?= $field ?> and click <b>Save</b> when you're done.

<form action=edit_field.php method=POST>
<input type=hidden name="save" value=1>
<input type=hidden name="field" value="<?= $field ?>">
<textarea name="data" rows=5 cols=40>
<?= $album->fields[$field] ?>
</textarea>
<p>
<input type=submit name="submit" value="Save">
<input type=submit name="submit" value="Cancel" onclick='parent.close()'>
</form>
