<? require('style.php'); ?>

<center>

<?
if ($password) {
	if (isCorrectPassword($password)) {
		$edit = $password;
		dismissAndReload();
		return;
	} else {
		echo("<font size=+2 color=red>Wrong password!</font><p>");
	}
}

?>

Edit mode lets you create and edit photo albums!
<br>
What is the password?
<br>
<form>
<input type=password name="password">
<p>
<input type=submit value="Login">
<input type=submit name="submit" value="Cancel" onclick='parent.close()'>
</form>
