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

/**
 * @package	Mail
 */

/**
 * Checks whether an emailstring has a valid format or not
 *
 * @param string    $email
 * @return boolean
 */
function check_email($email) {
	return preg_match('/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9_.-]+\.[a-zA-Z]{2,6}$/', $email) >0;
}

function emailDisclaimer() {
	global $gallery;

	$msg = unhtmlentities(
			sprintf(
			  gTranslate('common', "Note: This is an automatically generated email message sent from the %s website.  If you have received this in error, please ignore this message."),
			  $gallery->app->photoAlbumURL) .
		   "  \r\n".
		   sprintf(gTranslate('common', "Report abuse to %s"),$gallery->app->adminEmail)
	);

	$msg2 = sprintf("Note: This is an automatically generated email message sent from the %s website.  If you have received this in error, please ignore this message.  \r\nReport abuse to %s",
				$gallery->app->photoAlbumURL, $gallery->app->adminEmail);

	if ($msg != $msg2) {
		return "\r\n\r\n$msg\r\n\r\n$msg2";
	}
	else {
		return "\r\n\r\n$msg";
	}
}

/**
 * This function is a wrapper around the Mail classes
 * It has currently the same structure as gallery_mail_old
 * Return is true when succesfully send, otherise false
 * Errormessages are printed immediately
 *
 * @param string $to
 * @param string $subject
 * @param string $msg
 * @param string $logmsg
 * @param boolean $hide_recipients
 * @param string $from
 * @param boolean $isNotifyMail
 * @param boolean $isHTML
 * @return boolean
 */
function gallery_mail($to, $subject, $msg, $logmsg, $hide_recipients = false, $from = NULL, $isNotifyMail = false, $isHTML = false) {
	global $gallery;

	$bcc = array();

	if(!is_array($to)) {
		$to = array($to);
	}

	/* Begin catch errors from call */
	if ($gallery->app->emailOn == "no") {
		echo gallery_error(gTranslate('common', "Email not sent as it is disabled for this gallery."));
		return false;
	}

	foreach($to as $rcpnr => $mail) {
		if (! check_email($mail)) {
			echo "\n<br>". gallery_error(sprintf(gTranslate('common', "Email not sent to %s as it is not a valid address."),
			'<i>' . $mail . "</i>"));
			unset ($to[$rcpnr]);
		}
	}

	if (empty($to)) {
		echo "\n<br>". gallery_error(gTranslate('common', "Email not sent as no recipient address provided."));
		return false;
	}

	if ($hide_recipients) {
		$bcc = $to;
		$to = array();
	}

	if (! check_email($from)) {
		if (isDebugging() && $from) {
			echo "\n<br>". 
				gallery_error(sprintf(gTranslate('common', "Sender address %s is invalid, using %s."),
							  $from, $gallery->app->senderEmail));
		}
		$from = $gallery->app->senderEmail;
		$reply_to = $gallery->app->adminEmail;
	}
	else {
		$reply_to = $from;
	}

	/* End catch errors from call */

	if(!empty($gallery->app->emailSubjPrefix)) {
		$subject = $gallery->app->emailSubjPrefix .' '. $subject;
	}

	if (isset($gallery->app->email_notification) &&
	  in_array("bcc", $gallery->app->email_notification)) {
		$bcc[] = $gallery->app->adminEmail;
	}

	if (get_magic_quotes_gpc()) {
		$msg = stripslashes($msg);
	}

	$gallery_mail = new htmlMimeMail();

	$gallery_mail->setSubject($subject);
	$gallery_mail->setHeadCharset($gallery->charset);

	if($isHTML) {
		$gallery_mail->setHtmlCharset($gallery->charset);
        $gallery_mail->setHtml($msg, gTranslate('common', "This is a HTML mail, please have a look at the Attachment."));
	}
	else {
		$gallery_mail->setText($msg);
		$gallery_mail->setTextCharset($gallery->charset);
	}
	$gallery_mail->setFrom($from);
	$gallery_mail->setReturnPath($reply_to);

	/* As bccs are set as headers, they nead to be a string. Converting former array. */
	if (!empty($bcc)) {
		$gallery_mail->setBcc(implode(", ", $bcc));
	}

	if ($gallery->app->useOtherSMTP == "yes") {
		if (!checkSMTPParams()) {
			return false;
		}

		$gallery_mail->setSMTPParams(
			$gallery->app->smtpHost,
			$gallery->app->smtpPort,
			$gallery->app->smtpUserName,
			FALSE,
			$gallery->app->smtpUserName,
			$gallery->app->smtpPassword
		);
	}

	$result = $gallery_mail->send($to, ($gallery->app->useOtherSMTP != "yes") ? 'mail' : 'smtp');

	if(! $isNotifyMail) {
		emailLogMessage($logmsg, $result, $isNotifyMail);
	}

	return $result;
}

function checkSMTPParams() {
	global $gallery;

	$checklist = array('smtpHost', 'smtpPort', 'smtpUserName', 'smtpPassword');

	foreach ($checklist as $paramName) {
		if(empty($gallery->app->$paramName)) {
			return false;
		}
	}

	return true;
}

function welcome_email($show_default=false) {
	global $gallery;

	$default= gTranslate('common', "Hi !!FULLNAME!!,

Congratulations.  You have just been subscribed to %s at %s.  Your account name is !!USERNAME!!.  Please visit the gallery soon, and create a password by clicking this link:

!!NEWPASSWORDLINK!!

Your %s Administrator.");
	if ($show_default) {
		return sprintf($default,
		'<b><span style="white-space: nowrap;">&lt;' . gTranslate('core', "gallery title") . "&gt;</span></b>",
		'<b><span style="white-space: nowrap;">&lt;' . gTranslate('core', "gallery URL") . "&gt;</span></b>",
		'<b><span style="white-space: nowrap;">&lt;' . gTranslate('core', "gallery title") . "&gt;</span></b>");
	}
	elseif (empty($gallery->app->emailGreeting)) {
		return sprintf($default,
		$gallery->app->galleryTitle,
		$gallery->app->photoAlbumURL,
		$gallery->app->galleryTitle);
	}
	else {
		return $gallery->app->emailGreeting;
	}

}

function welcomeMsgPlaceholderList() {
	$placeholders = array(
		'galleryurl'	=> gTranslate('common', "The URL to your Gallery."),
		'gallerytitle'	=> gTranslate('common', "Title of your Gallery."),
		'adminemail'	=> gTranslate('common', "Admin email(s)"),
		'password'		=> gTranslate('common', "Password for the newly created user."),
		'username'		=> gTranslate('common', "Username"),
		'fullname'		=> gTranslate('common', "Fullname"),
		'newpasswordlink' =>  gTranslate('common', "Will be replaced by a link the new user can click on to create a new password.")
	);

	return $placeholders;
}

/**
 * This function substitutes placeholder like !!USERNAME!!
 * with the corresponding value in the welcome message for new users.
 */
function resolveWelcomeMsg($placeholders = array()) {
	global $gallery;

	$welcomeMsg =  welcome_email();

	$placeholders['galleryurl']		= $gallery->app->photoAlbumURL;
	$placeholders['gallerytitle']	= $gallery->app->galleryTitle;
	$placeholders['adminemail']		= $gallery->app->adminEmail;

	foreach (welcomeMsgPlaceholderList() as $key => $trash) {
		$welcomeMsg = str_replace('!!'. strtoupper($key) .'!!',
			isset($placeholders[$key]) ? $placeholders[$key] : '', $welcomeMsg);
	}

	return $welcomeMsg;
}

/**
 * This functions sends a notification to all people that request an email when a comment was added
 * to an item.
 * @param	string	$photoid
 * @param	string	$comment_text
 * @param	string	$commenter_name
 */
function emailComments($id, $comment_text, $commenter_name) {
	global $gallery;

	$to = $gallery->album->getEmailMeList('comments', $id);
	$subject = sprintf(gTranslate('common', "New comment for %s."), $id);
	$text = '';

	if (!empty($to)) {
		$text .= '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">';
		$text .= "\n\n<html>";
		$text .= "\n  <head>";
		$text .= "\n  <title>$subject</title>";
		$text .= "\n  </head>\n<body>\n<p>";
	    $text .= sprintf(gTranslate('common', "A new comment has been added to Gallery: %s"), $gallery->app->galleryTitle);
		$text .= "\n</p>";
	    $text .= sprintf(gTranslate('common', "The comment was added by %s to this %s in this %s."),
		$commenter_name,
			'<a href="'. makeAlbumHeaderUrl($gallery->session->albumName, $id) .'">'. gTranslate('common', "Item") .'</a>',
			'<a href="'. makeAlbumHeaderUrl($gallery->session->albumName) .'">'. gTranslate('common', "Album") .'</a>');
	    $text .= "\n<br>". gTranslate('common', "*** Begin comment ***") ."<br>\n";
		$text .= nl2br($comment_text);
	    $text .= "<br>\n". gTranslate('common', "*** End comment ***") . "\n<p>\n";
	    $text .= gTranslate('common', "If you no longer wish to receive emails about this item, follow the links above and ensure that 'Email me when comments are added' is unchecked in both the item and album page (you'll need to login first).");
		$text .= "\n</p>\n</body>\n</html>";

        $logmsg = sprintf(gTranslate('common', "New comment for %s."), makeAlbumHeaderUrl($gallery->session->albumName, $id));

		gallery_mail($to, $subject, $text, $logmsg, true, NULL, false, true);
	}
}

function emailLogMessage($logmsg, $result, $isNotifyMail) {
	global $gallery;

	if (!$result) {
		$logmsg = gTranslate('common', "Sending email failed. Additional info:") . "\n<br>" .
				  $logmsg;
	}

	if (isset($gallery->app->email_notification) &&
		in_array("logfile", $gallery->app->email_notification))
	{
		$logfile = $gallery->app->userDir."/email.log";
		logMessage($logmsg, $logfile);
	}

	if (isset($gallery->app->email_notification) &&
		in_array("email", $gallery->app->email_notification))
	{
		$subject = gTranslate('common', "Email activity");
		if ($subject != "Email activity") {
			$subject .= "/Email activity";
		}

		$subject .= ": ".  $gallery->app->galleryTitle;
		$subject = unhtmlentities($subject);

		gallery_mail(
			$gallery->app->adminEmail,
			$subject,
			$logmsg . emailDisclaimer(),
			'',
			false,
			$gallery->app->senderEmail,
			true
		);
	}
}
?>