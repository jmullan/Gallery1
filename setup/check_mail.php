<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2005 Bharat Mediratta
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
 *
 * $Id$
 */
?>
<?php 

	require_once(dirname(__FILE__) . '/init.php');
	require(dirname(__FILE__) . '/functions.inc');
?>

<html>
<head>
	<title> <?php echo _("Check Mail") ?> </title>
	<?php echo getStyleSheetLink() ?>
</head>

<body dir="<?php echo $gallery->direction ?>">
<?php configLogin(basename(__FILE__)); ?>
<h1 class="header"><?php echo _("Check Mail") ?></h1>

<?php 

if (isset($submit)) {
	echo '<table class="inner" width="100%"><tr>';
	if(gallery_validate_email($email_address)) {
		mail($email_address, sprintf(_("Test email from %s"), Gallery()), 
			_("This email was automatically generated."). "\n\n" .
			_("If you recevied this in error, then please disregard, as you should not receive any similar emails.") . "\n\n" .
			sprintf(_("If you were expecting email from the %s installation at %s, then Congratulations!  Email is working and you can enable the %s email functions."),
			Gallery(),
			"http://" . getenv("SERVER_NAME") . $GALLERY_URL,
			Gallery()) . "\n\n");
		echo '<td class="successpct">'. _("SUCCESS!"). '</td></tr>';
		echo '<tr><td class="desc">' . sprintf(_("Test email sent to <b>%s</b>, and should arrive in a few minutes.  If you don't receive it please confirm the email address used was correct.  If you cannot receive the email, then it must be disabled for this server, and %s email functions cannot be used until that is rectified"), $email_address, Gallery()) .'</td>';
	} else {
		echo '<td class="errorlong">'. _("You must use a valid email address") . '</td>';
		echo '</tr><tr><td class="desc">' . _("Try again?") .'</td>';
	}
	
	echo '</tr></table>';
} else {
	echo '<div class="sitedesc">';
	print sprintf(_("This enables you to confirm that email is working correctly on your system.  Submit your email address below, and an email will be sent to you. If you receive it, then you know that mail is working on your system"));
	echo '</div>';
	if (getOS() != OS_WINDOWS) {
	       	if (! ini_get("sendmail_path")) {
		       	$warning[] = sprintf(_("%s not set."), 
					"sendmail_path");
	       	}
	} else { 
		if (!ini_get("SMTP")) {
		       	$warning[] = sprintf(_("%s not set."), "SMTP");
		}
	}
	if (isset($warning)) {
		echo '<table class="inner" width="100%">';
		foreach ($warning as $value) {
			echo '<tr><td class="warningpct">' . _("Warning") . 
				": " .  $value .'</td></tr>';
		}
//		echo '<td class="desc">' . _("Please fix this before you continue!") .'</td>';
		echo '</table>';
	}
}
?>

<form action="check_mail.php" method="POST">
<table width="100%">
<tr><td>
<table class="inner" width="100%">
	<tr>
		<td style="white-space:nowrap;"><?php echo _("Your email address:") ?></td>
		<td><input name="email_address" width="50"></td>
		<td><input type="submit" name="submit" value="<?php echo _("Send Email") ?>"></td>
		<td width="100%">&nbsp;</td>
	</tr>
</table>
</td>
</tr>
</table>
</form>

<table class="inner" width="100%">
<tr>
	<td class="desc" align="center"><?php echo returnToConfig(); ?></td>
</tr>
</table>    

</body>
</html>
