<?php /* $Id$ */ ?>
<?php 
$GALLERY_BASEDIR="../";
require($GALLERY_BASEDIR . "util.php");
require("./init.php");
initLanguage();
extract($HTTP_POST_VARS);
require("functions.inc");
?>

<html> <head><title> <?php echo _("Check Mail") ?> </title></head>
<body dir="<?php echo $gallery->direction ?>">
<?php 

if (isset($submit)) {
    if(validate_email($email_address)) {
	mail($email_address, sprintf(_("Test email from %s"), Gallery()), 
			_("This email was automatically generated."). "\n\n" .
			_("If you recevied this in error, then please disregard, as you should not receive any similar emails.") . "\n\n" .
			sprintf(_("If you were expecting email from the %s installation at %s, then Congratulations!  Email is working and you can enable the %s email functions."),
			Gallery(),
			"http://" . getenv("SERVER_NAME") . $GALLERY_URL,
			Gallery()) . "\n\n");
	print sprintf(_("Test email sent to %s, and should arrive in a few minutes.  If you don't receive it please confirm the email address used was correct.  If you cannot receive the email, then it must be disabled for this server, and %s email functions cannot be used until that is rectified"), $email_address, Gallery());
    } else {
	print error_format(_("You must use a valid email address"));
   }
   print "<br><br>Try again?<br>";
} else {
print sprintf(_("This enables you to confirm that email is working correctly on your system.  Submit your email address below, and an email will be sent to you. If you receive it, then you know that mail is working on your system"));
if (!ini_get("sendmail_path")) {
	$error = sprintf(_("%s not set."), "sendmail_path");
	print "<font color=red>". sprintf(_("Error! %s"), $error).'</font>';
}
if (!ini_get("SMTP")) {
	$error = sprintf(_("%s not set."), "SMTP");
	print "<font color=red>". sprintf(_("Error! %s"), $error).'</font>';
}

}
?>

<p>
<form action="check_mail.php" method="POST">
<?php echo _("Your email address:") ?>   <input name="email_address" width="50">
<br><input type="submit" name="submit" value="<?php echo _("Send Email") ?>">
</form>
<p>
<?php echo returnToConfig() ?>


    
</body>
</html>
