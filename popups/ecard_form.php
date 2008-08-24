<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
 *
 * This file Copyright (C) 2003-2004 Joan McGalliard
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

####################################################################################
# IBPS E-C@ard for Gallery           Version 1                                     #
# Copyright 2002 IBPS Friedrichs     info@ibps-friedrichs.de                       #
# Ported (the first time) for Gallery By freenik      webmaster@cittadipolicoro.com#
####################################################################################
*/

require_once(dirname(dirname(__FILE__)) . '/init.php');

list($photoIndex, $ecard, $submit_action) =
	getRequestVar(array('photoIndex', 'ecard', 'submit_action'));

printPopupStart(gTranslate('core', "Send this photo as eCard"));

$ecard['photoIndex'] = empty($ecard['photoIndex']) ? $photoIndex : $ecard['photoIndex'];

if(! isValidGalleryInteger($ecard['photoIndex'])) {
	echo gallery_error(gTranslate('core', "Invalid element chosen."));
	echo "\n<br><br>";
	echo gButton('closeButton', gTranslate('core', "_Close Window."),'parent.close()');
	echo "</div></body></html>";
	exit;
}

if(!$photo = $gallery->album->getPhoto($ecard['photoIndex'])) {
	echo infoBox($global_notice_messages);
	echo "\n<br><br>";
	echo gButton('closeButton', gTranslate('core', "_Close Window."),'parent.close()');
	echo "</div></body></html>";
	exit;
}

/* Get the dimensions of the sized Photo */
list($width, $height) = $photo->getDimensions(0, false);

$max_length		= 300;   // Maximum length of the e-Card text
$ecard_PLAIN_data	= gTranslate('core', "You have an e-card as attachment. Click to see.");
$error_msg		= '';
$ecard_send		= false;
$sendButtonTest		= gTranslate('core',"_Send eCard");

$checks = array(
	'photoIndex'		=> array( 0, gTranslate('core', "Element index")),
	'name_sender'		=> array( 1, gTranslate('core', "Name of sender")),
	'email_sender'		=> array( 1, gTranslate('core', "Email of sender")),
	'name_recipient'	=> array( 1, gTranslate('core', "Name of recipient")),
	'email_recipient'	=> array( 1, gTranslate('core', "Email of recipient")),
	'message'		=> array( 0, gTranslate('core', "Message"))
);

if (! empty($submit_action)) {
	foreach ($checks as $fieldname => $specs) {
		if(empty($ecard[$fieldname]) || ! isXSSclean($ecard[$fieldname], $specs[0])) {
			$error_msg .= sprintf(gTranslate('core', "%s is not valid."), $specs[1]);
			$error_msg .= '<br>';
		}
	}

	if (!check_email($ecard["email_recipient"]) || !check_email($ecard["email_sender"])) {
		$error_msg .= gTranslate('core', "The sender or recipient email adress is not valid.");
		$error_msg .= '<br>';
	}

	if (strlen($ecard["message"]) > $max_length) {
		$ecard["message"] = substr($ecard["message"],0,$max_length-1);
	}

	list($error,$ecard_data_to_parse) = get_ecard_template($ecard["template_name"]);

	if ($error) {
		$error_msg .= gTranslate('core', "Couldn't load the ecard template. Please contact the Gallery admin!");
		$error_msg .= '<br>';
	}

	if (empty($error_msg)) {
		$ecard_HTML_data = parse_ecard_template($ecard, $ecard_data_to_parse, false);
		$result = send_ecard($ecard, $ecard_HTML_data, $ecard_PLAIN_data);

		if ($result) {
			$ecard_send = true;
		}
		else {
			$error_msg .= gTranslate('core', "Problem with sending the eCard. Please contact the Gallery admin!");
			$error_msg .= '<br>';
		}
	}
}
else {
	if (!isset($ecard["image_name"])) {
		$ecard["image_name"] = $photo->getPhotoPath($gallery->album->fields['name'], false);
	}
}
?>
<script type="text/javascript">
<!--
function popup_win(theURL,winName,winOptions) {
	win = window.open(theURL,winName,winOptions);
	win.focus();
}

function make_preview() {
	document.ecard_form.action = "<?php echo $gallery->app->photoAlbumURL ; ?>/popups/ecard_preview.php";
	popup_win('','ecard_preview','resizable=yes,scrollbars=yes,width=800,height=600');
	document.ecard_form.target = "ecard_preview";
	document.ecard_form.submit();
}

function send_ecard() {
	document.ecard_form.action = "<?php echo $_SERVER["PHP_SELF"] ?>";
	document.ecard_form.target = "_self";
	document.ecard_form["submit_action"].value = "send";
	if (check()) { document.ecard_form.submit(); }
}

function check() {
	var error = false;
	var error_message = "<?php echo gTranslate('core', "Error: to send an eCard you need to fill out all fields."); ?>";
	error_message +="\n <?php echo gTranslate('core', "Please fill these fields:"); ?>\n\n";

	if (document.ecard_form["ecard[name_sender]"].value == "") {
		error = true;
		error_message += "<?php echo gTranslate('core', "- Your Name"); ?>\n";
	}

	if ((document.ecard_form["ecard[email_sender]"].value == "") &&
		(document.ecard_form["ecard[email_sender]"].value.indexOf("@") == -1))
	{
		error = true;
		error_message += "<?php echo gTranslate('core', "- Your Email"); ?>\n";
	}

    if (document.ecard_form["ecard[name_recipient]"].value == "") {
		error = true;
		error_message += "<?php echo gTranslate('core', "- Recipient's Name"); ?>\n";
	}

    if ((document.ecard_form["ecard[email_recipient]"].value == "") &&
        (document.ecard_form["ecard[email_recipient]"].value.indexOf("@") == -1)) {
		error = true;
		error_message += "<?php echo gTranslate('core', "- Recipient's Email"); ?>\n";
	}

	if (document.ecard_form["ecard[message]"].value == "") {
		error = true;
		error_message += "<?php echo gTranslate('core', "- Your Message"); ?>\n";
	}

	if (error) {
		error_message += "\n\n<?php printf(gTranslate('core', "Please fill all fields. Then click '%s' again."), removeAccessKey($sendButtonTest)); ?>";
		alert(error_message);
		return false;  // Form not sent
	}
	else {
		return true;  // Form sent
	}

} // Ende function check()

function CountMax() {
	max = <?php echo $max_length ?>;
	wert = max - document.ecard_form["ecard[message]"].value.length;
	if (wert < 0) {
		alert("<?php echo sprintf(gTranslate('core', "You have entered more than %d characters!"), $max_length); ?>");
		document.ecard_form["ecard[message]"].value = document.ecard_form["ecard[message]"].value.substring(0,max);
		wert = 0;
		document.ecard_form.counter.value = wert;
	}
	else {
		document.ecard_form.counter.value = max - document.ecard_form["ecard[message]"].value.length;
	}
} // Ende function CountMax()


//-->
</script>

<?php
if (! $ecard_send) {
	echo $gallery->album->getThumbnailTag($ecard['photoIndex']);
	if (!empty($error_msg)) {
		echo "\n<br><br>";
		echo gallery_error($error_msg);
	}

	echo makeFormIntro("ecard_form.php",
	array("name" => "ecard_form"),
	array("type" => "popup"));
?>
	<input name="ecard[image_name]" type="hidden" value="<?php echo $ecard['image_name']; ?>">
	<input name="ecard[template_name]" type="hidden" value="ecard_1.tpl">
	<input name="ecard[photoIndex]" type="hidden" value="<?php echo $ecard['photoIndex']; ?>">
	<input name="submit_action" type="hidden" value="">

<?php
	$defaultSenderName = '';
	$defaultSenderEmail = '';

	if (! empty($gallery->user) && $gallery->user->isLoggedIn()) {
		$defaultSenderName = $gallery->user->displayName();
		$defaultSenderEmail = $gallery->user->getEmail();
	}

	$name_sender	= empty($ecard['name_sender'])		? $defaultSenderName : $ecard['name_sender'];
	$email_sender	= empty($ecard['email_sender'])		? $defaultSenderEmail : $ecard['email_sender'];
	$name_recipient	= !empty($ecard['name_recipient'])	? $ecard['name_recipient'] : '';
	$email_recipient= !empty($ecard['email_recipient'])	? $ecard['email_recipient'] : '';
	$defaultSubject	= !empty($defaultSenderName)		? sprintf(gTranslate('core', "%s sent you an E-C@rd"), $defaultSenderName) : '';
?>
	<table cellpadding="0" cellspacing="4" align="center" border="0">
	<tr>
		<td class="g-columnheader" colspan="2"><?php echo gTranslate('core', "Your info"); ?></td>
		<td width="10">&nbsp;</td>
		<td class="g-columnheader" colspan="2"><?php echo gTranslate('core', "Recipient's info"); ?></td>
	</tr>
	<tr>
	<?php echo gInput('text', 'ecard[name_sender]', gTranslate('core', "Name"), 'cell', $name_sender, array('tabindex' => 1, 'size' => 18, 'id' => 'name_sender')); ?>
	<td>&nbsp;</td>
	<?php echo gInput('text', 'ecard[name_recipient]', gTranslate('core', "Name"), 'cell', $name_recipient, array('tabindex' => 3, 'size' => 18, 'id' => 'name_recipient')); ?>
	</tr>

	<tr>
	<?php echo gInput('text', 'ecard[email_sender]', gTranslate('core', "E-Mail"), 'cell', $email_sender, array('tabindex' => 2, 'size' => 18, 'id' => 'email_sender')); ?>
	<td>&nbsp;</td>
	<?php echo gInput('text', 'ecard[email_recipient]', gTranslate('core', "E-Mail"), 'cell', $email_recipient, array('tabindex' => 4, 'size' => 18, 'id' => 'email_recipient')); ?>
	</tr>

	<tr>
		<td colspan="5" align="center">
		<select id="ecardstamp" name="ecard[stamp]">
			<option selected value="08"><?php echo gTranslate('core', "Choose a Stamp"); ?></option>
<?php
for($i = 1; $i <= 27; $i++) {
	$nr = sprintf("%02d", $i-1);
	echo "\n\t\t" . '<option value="'. $nr .'">';
	printf(gTranslate('core', "Stamp #%d"), $i);
	echo "</option>";
}
?>
		</select>
		<?php $stamp_previewURL = build_popup_url("stamp_preview.php"); ?>
		<img alt="helpIcon" height="15" hspace="5" onclick="popup_win('<?php echo $stamp_previewURL; ?>', 'Stamp_Preview','scrollbars=yes, width=150, height=300')" src="<?php echo getImagePath('ecard_images/icon_help.gif') ?>" width="15">
		</td>
	</tr>
	<tr>
		<td><label for="subject"><?php echo gTranslate('core', "Subject:"); ?></label></td>
		<td colspan="4"><input type="text" size="65" maxlength="75" name="ecard[subject]" id="subject" value="<?php echo $defaultSubject; ?>"></td>
	</tr>
	<tr>
		<td colspan="5" class="left g-small"><label for="message"><?php echo gTranslate('core', "Your Message:"); ?></label></td>
	</tr>
	<tr>
		<td align="center" colspan="5">
			<textarea cols="55" rows="7" name="ecard[message]" id="message" onKeyPress="CountMax();" onfocus="CountMax();"><?php if (! empty($ecard["message"])) echo $ecard["message"]; ?></textarea>
		</td>
	</tr>
	<tr>
		<td align="center" colspan="5">
			<input maxlength="<?php echo $max_length ?>" name="counter" size="3" type="text">
		</td>
	</tr>
	<tr>
		<td colspan="5" align="center">
		<table>
		<tr>
			<td><?php echo gButton('preview', gTranslate('core', "_Preview"), 'make_preview();'); ?></td>
			<td><?php echo gReset('reset', gTranslate('core', "_Reset")); ?></td>
			<td width="100%">&nbsp;</td>
			<td><?php echo gButton('cancel', gTranslate('core', "_Cancel"), 'window.close()'); ?></td>
			<td><?php echo gButton('send', $sendButtonTest, 'send_ecard();'); ?></td>
		</tr>
		</table>
		</td>
	</tr>
	</table>
</form>
<?php }
else {
	printf(gTranslate('core', "Your E-C@rd with the picture below has been sent to %s &lt;%s&gt;."), $ecard["name_recipient"], $ecard["email_recipient"]);
?>
  <p align="center"><?php echo $gallery->album->getThumbnailTag($ecard['photoIndex']); ?></p>
  <br>
  <a href="javascript:window.close()"><?php echo gTranslate('core', "Close this window") ?></a>
<?php }
?>
</div>

</body>
</html>
