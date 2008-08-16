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

$messages = array();

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
			$messages[] = array(
				'type' => 'success',
				'text' => sprintf(gTranslate('config', "Test email sent to <b>%s</b>, and should arrive in a few minutes."), $email_address) .
					"\n<br>" .
					gTranslate('config', "If you don't receive it please confirm the email address used was correct.") .
					"\n<br>" .
					sprintf(gTranslate('config', "If you cannot receive the email, then it must be disabled for this server, and %s email functions cannot be used until that is rectified."), Gallery())
			);
		}
	}
	else {
		$messages[] = array(
			'type' => 'error',
			'text' => gTranslate('config', "You must use a valid email address") .
				'<br>' .
				gTranslate('config', "Try again?")
		);
	}
}
else {
	echo '<div class="g-sitedesc left">';
	print sprintf(gTranslate('config', "This enables you to confirm that email is working correctly on your system.  Submit your email address below, and an email will be sent to you. If you receive it, then you know that mail is working on your system"));
	echo '</div>';
	if (getOS() != OS_WINDOWS) {
		if (! ini_get("sendmail_path")) {
			$messages[] = array(
				'type' => 'warning',
				'text' => sprintf(gTranslate('config', "%s not set in php.ini."), "sendmail_path")
			);
		}
	}
	else {
		if (! ini_get("SMTP")) {
			$messages[] = array(
				'type' => 'warning',
				'text' => sprintf(gTranslate('config', "%s not set in php.ini."), "SMTP")
			);
		}
	}
}

echo infoBox($messages);

echo makeFormIntro('setup/check_mail.php');

echo gInput('text', 'email_address', gTranslate('config', "Your _email address:"), false, false,
			array('size' => 50));

echo gSubmit('submit', gTranslate('config', "_Send Email"));
?>

</form>

</div>

<div class="center">
	<?php echo returnToDiag(); ?><?php echo returnToConfig(); ?>
</div>

<script type="text/javascript">
	document.g1_form.email_address.focus();
</script>

</body>
</html>
