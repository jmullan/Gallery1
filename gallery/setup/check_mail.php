<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * $Id$
 */

require_once(dirname(__FILE__) . '/init.php');
    
printPopupStart(gTranslate('config', "Check Mail"));

configLogin(basename(__FILE__));

list($submit, $email_address) = getRequestVar(array('submit', 'email_address'));

if (isset($submit)) {
	if(check_email($email_address)) {
		$to = $email_address;
		$subject = sprintf(gTranslate('config', "Test email from %s"), Gallery());
		$msg = gTranslate('config', "This email was automatically generated."). "\n\n" .
			gTranslate('config', "If you recevied this in error, then please disregard, as you should not receive any similar emails.") . "\n\n" .
			sprintf(gTranslate('config', "If you were expecting email from the %s installation at %s, then Congratulations!  Email is working and you can enable the %s email functions."),
			Gallery(),
			"http://" . getenv("SERVER_NAME") . $GALLERY_URL,
			Gallery()) . "\n\n";

		$logmsg = gTranslate('config', "Attempt to send Testmail from config wizard.");

		$ret = gallery_mail($to, $subject, $msg, $logmsg);
		if ($ret) {
			echo '<table class="inner" width="100%"><tr>';
			echo '<td class="successpct">'. gTranslate('config', "SUCCESS!"). '</td></tr>';
			echo '<tr><td class="desc">' . sprintf(gTranslate('config', "Test email sent to <b>%s</b>, and should arrive in a few minutes.  If you don't receive it please confirm the email address used was correct.  If you cannot receive the email, then it must be disabled for this server, and %s email functions cannot be used until that is rectified"), $email_address, Gallery()) .'</td>';
		}
	} else {
		echo '<table class="inner" width="100%"><tr>';
		echo '<td class="errorlong">'. gTranslate('config', "You must use a valid email address") . '</td>';
		echo '</tr><tr><td class="desc">' . gTranslate('config', "Try again?") .'</td>';
	}
	
	echo '</tr></table>';
} else {
	echo '<div class="sitedesc left">';
	print sprintf(gTranslate('config', "This enables you to confirm that email is working correctly on your system.  Submit your email address below, and an email will be sent to you. If you receive it, then you know that mail is working on your system"));
	echo '</div>';
	if (getOS() != OS_WINDOWS) {
	       	if (! ini_get("sendmail_path")) {
		       	$warning[] = sprintf(gTranslate('config', "%s not set."), 
					"sendmail_path");
	       	}
	} else { 
		if (!ini_get("SMTP")) {
		       	$warning[] = sprintf(gTranslate('config', "%s not set."), "SMTP");
		}
	}
	if (isset($warning)) {
		echo '<table class="inner" width="100%">';
		foreach ($warning as $value) {
			echo '<tr><td class="warningpct">' . gTranslate('config', "Warning") . 
				": " .  $value .'</td></tr>';
		}
//		echo '<td class="desc">' . gTranslate('config', "Please fix this before you continue!") .'</td>';
		echo '</table>';
	}
}
?>

<form action="check_mail.php" method="POST">
<table width="100%">
<tr><td>
<table class="inner" width="100%">
	<tr>
		<td style="white-space:nowrap;"><?php echo gTranslate('config', "Your email address:") ?></td>
		<td><input name="email_address" size="50"></td>
		<td><input type="submit" name="submit" value="<?php echo gTranslate('config', "Send Email") ?>"></td>
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

</div>
</body>
</html>
