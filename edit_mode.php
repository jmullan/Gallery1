<?
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000 Bharat Mediratta
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
 */
?>

<html>
<head>
  <title>Administrator Login</title>
  <link rel="stylesheet" type="text/css" href="<?= getGalleryStyleSheetName() ?>">
</head>
<body>

<center>
<span class="popuphead">Login as Administrator</span>
<br>
<br>
<?
if ($password) {
	if (isCorrectPassword($password)) {
		$edit = $password;
		dismissAndReload();
		return;
	} else {
		echo("<font size=+1 color=red>Wrong password!</font><p>");
	}
}

?>

Edit mode lets you create and edit photo albums!
<br>
What is the password?
<br>
<form name="login_form">
<input type=password name="password">
<p>
<input type=submit value="Login">
<input type=submit name="submit" value="Cancel" onclick='parent.close()'>
</form>

<script language="javascript1.2">
<!--
// position cursor in top form field
document.login_form.password.focus();
//--> 
</script>

</body>
</html>
