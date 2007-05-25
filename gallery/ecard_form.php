<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2007 Bharat Mediratta
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
*/
?>
<?php
/*
####################################################################################
# IBPS E-C@ard for Gallery           Version 1                                     #
# Copyright 2002 IBPS Friedrichs     info@ibps-friedrichs.de                       #
# Ported (the first time) for Gallery By freenik      webmaster@cittadipolicoro.com#
####################################################################################
*/

require_once(dirname(__FILE__) . '/init.php');

list($photoIndex, $ecard, $submit_action) =
    getRequestVar(array('photoIndex', 'ecard', 'submit_action'));

doctype();

printPopupStart(gTranslate('core', "Send this photo as eCard"));
$ecard['photoIndex'] = empty($ecard['photoIndex']) ? $photoIndex : $ecard['photoIndex'];

if(!$photo = $gallery->album->getPhoto($ecard['photoIndex'])) {
    echo gallery_error($errortext);
    echo "\n<br><br>";
    echo '<input type="button" value="'. gTranslate('core', "Close Window.") .'" onClick="parent.close()">';
    echo "</div></body></html>";
    exit;
}

/* Get the dimensions of the sized Photo */
list($width, $height) = $photo->getDimensions(0, false);

$max_length = 300;   // Maximum length of the e-Card text
$ecard_PLAIN_data = gTranslate('core', "You have an e-card as attachment. Click to see.");
$error_msg = '';
$mandatory = array('name_sender', 'email_sender', 'name_recepient', 'email_recepient', 'message');
$ecard_send = false;

if (! empty($submit_action)) {
    foreach ($mandatory as $mandatoryField) {
        if(empty($ecard[$mandatoryField])) {
            $error_msg .= gTranslate('core', "Some input fields are not correctly filled out. Please fill out.") . '<br>';
            $error_msg .= '<br>';
            break;
        }
    }

    if (!check_email($ecard["email_recepient"]) || !check_email($ecard["email_sender"])) {
        $error_msg .= gTranslate('core', "The sender or recepient email adress is not valid.");
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
        $ecard_HTML_data = parse_ecard_template($ecard,$ecard_data_to_parse, false);
        $result = send_ecard($ecard,$ecard_HTML_data,$ecard_PLAIN_data);
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
    document.ecard_form.action = "<?php echo $gallery->app->photoAlbumURL ; ?>/ecard_preview.php";
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
    error_message +="\n <?php echo gTranslate('core', "Please fill this fields:"); ?>\n\n";

    if (document.ecard_form["ecard[name_sender]"].value == "") {
        error = true;
        error_message += "<?php echo gTranslate('core', "- Your Name"); ?>\n";
    }

    if ((document.ecard_form["ecard[email_sender]"].value == "") &&
    (document.ecard_form["ecard[email_sender]"].value.indexOf("@") == -1)) {
        error = true;
        error_message += "<?php echo gTranslate('core', "- Your Email"); ?>\n";
    }

    if (document.ecard_form["ecard[name_recepient]"].value == "") {
        error = true;
        error_message += "<?php echo gTranslate('core', "- Recipient's Name"); ?>\n";
    }

    if ((document.ecard_form["ecard[email_recepient]"].value == "") &&
    (document.ecard_form["ecard[email_recepient]"].value.indexOf("@") == -1)) {
        error = true;
        error_message += "<?php echo gTranslate('core', "- Recipient's Email"); ?>\n";
    }

    if (document.ecard_form["ecard[message]"].value == "") {
        error = true;
        error_message += "<?php echo gTranslate('core', "- Your Message"); ?>\n";
    }

    if (error) {
        error_message += "\n\n<?php echo gTranslate('core', 'Please fill all fields next click >Send<.'); ?>";
        alert(error_message);
        return false;  // Form not sent
    } else {
        return true;  // Form sent
    }

} // Ende function check()

function CountMax() {
    max = <?php echo $max_length ?>;
    wert = max - document.ecard_form["ecard[message]"].value.length;
    if (wert < 0) {
        alert("<?php echo sprintf(gTranslate('core', "You have entered more than %d characters"), $max_length); ?>");
        document.ecard_form["ecard[message]"].value = document.ecard_form["ecard[message]"].value.substring(0,max);
        wert = 0;
        document.ecard_form.counter.value = wert;
    } else {
        document.ecard_form.counter.value = max - document.ecard_form["ecard[message]"].value.length;
    }
} // Ende function CountMax()


//-->
</script>

<?php
if (! $ecard_send) {
    echo $gallery->album->getThumbnailTag($ecard['photoIndex']);
    if (!empty($error_msg)) {
        echo '<p>'. gallery_error($error_msg) .'</p>';
    }

    echo makeFormIntro("ecard_form.php",
    array("name" => "ecard_form"),
    array("type" => "popup"));
?>
  <input name="ecard[image_name]" type="hidden" value="<?php echo $ecard["image_name"]; ?>">
  <input name="ecard[template_name]" type="hidden" value="ecard_1.tpl">
  <input name="ecard[photoIndex]" type="hidden" value="<?php echo $ecard['photoIndex']; ?>">
  <input name="submit_action" type="hidden" value="">

  <br>
  <table border="0" cellpadding="0" cellspacing="4" align="center">
  <tr>
    <td class="columnheader" colspan="2"><?php echo gTranslate('core', "Your info"); ?></td>
    <td width="10">&nbsp;</td>
    <td class="columnheader" colspan="2"><?php echo gTranslate('core', "Recipient's info"); ?></td>
  </tr>
  <tr>
    <td><?php echo gTranslate('core', "Name") ?></td>
    <?php
    $defaultSenderName = '';
    $defaultSenderEmail = '';
    if (! empty($gallery->user) && $gallery->user->isLoggedIn()) {
        $defaultSenderName = $gallery->user->displayName();
        $defaultSenderEmail = $gallery->user->getEmail();
    }

    $name_sender = empty($ecard['name_sender']) ? $defaultSenderName : $ecard['name_sender'];
    $email_sender = empty($ecard['email_sender']) ? $defaultSenderEmail : $ecard['email_sender'];
    ?>
    <td><input tabindex="1" maxlength="40" name="ecard[name_sender]" size="18" type="Text" value="<?php echo $name_sender; ?>"></td>
    <td></td>
    <td><?php echo gTranslate('core', "Name") ?></td>
    <td><input tabindex="3" maxlength="40" name="ecard[name_recepient]" size="18" type="Text" value="<?php echo $ecard['name_recepient']; ?>"></td>
  </tr>
  <tr>
    <td><?php echo gTranslate('core', "E-Mail"); ?></td>
    <td><input tabindex="2" maxlength="40" name="ecard[email_sender]" size="18" type="Text" value="<?php echo $email_sender; ?>"></td>
    <td></td>
    <td><?php echo gTranslate('core', "E-Mail"); ?></td>
    <td><input tabindex="4" maxlength="40" name="ecard[email_recepient]" size="18" type="Text" value="<?php echo $ecard['email_recepient']; ?>"></td>
  </tr>
  <tr>
    <td colspan="5" align="center">
  	  <select id="ecardstamp" name="ecard[stamp]">
            <option selected value="08"><?php echo gTranslate('core', "Choose a Stamp"); ?></option>
<?php
for($i = 1; $i <= 27; $i++) {
    $nr = sprintf("%02d", $i-1);
    echo "\n\t" . '<option value="'. $nr .'">';
    echo sprintf(gTranslate('core', "Stamp #%d"), $i);
    echo "</option>";
}
?>
        </select>
        <?php $stamp_previewURL = build_popup_url("stamp_preview.php"); ?>
        <img alt="helpIcon" height="15" hspace="5" onclick="popup_win('<?php echo $stamp_previewURL; ?>', 'Stamp_Preview','scrollbars=yes, width=150, height=300')" src="<?php echo getImagePath('ecard_images/icon_help.gif') ?>" width="15">
    </td>
  </tr>
  <tr>
    <td><?php echo gTranslate('core', "Subject:"); ?></td>
    <?php $defaultSubject = (!empty($defaultSenderName)) ? sprintf(gTranslate('core', "%s sent you an E-C@rd"), $defaultSenderName) : ''; ?>
    <td colspan="4"><input type="Text" size="65" maxlength="75" name="ecard[subject]" value="<?php echo $defaultSubject; ?>"></td>
  </tr>
  <tr>
    <td colspan="5"><?php echo gTranslate('core', "Your Message:"); ?></td>
  </tr>
  <tr>
    <td align="center" colspan="5">
      <textarea cols="55" rows="7" name="ecard[message]" onKeyPress="CountMax();" onfocus="CountMax();"><?php if (! empty($ecard["message"])) echo $ecard["message"]; ?></textarea>
    </td>
  </tr>
  <tr>
    <td colspan="5">&nbsp;</td>
  </tr>
  <tr>
    <td align="center" colspan="5">
	<input maxlength="<?php echo $max_length ?>" name="counter" size="3" type="Text">
    </td>
  </tr>
  <tr>
     <td colspan="5" align="center">
     <table>
      <tr>
        <td><input type="button" onClick="javascript:make_preview();" value="<?php echo gTranslate('core', "Preview"); ?>"></td>
        <td><input type="reset" value="<?php echo gTranslate('core', "Reset"); ?>"></td>
	<td width="100%">&nbsp;</td>
        <td align="left"><input type="button" onClick="javascript:window.close()" value="<?php echo gTranslate('core', "Cancel"); ?>"></td>
	<td><input type="button" onClick="javascript:send_ecard();" value="<?php echo gTranslate('core', "Send eCard"); ?>"></td>
      </tr>
     </table>
     </td>
  </tr>
  </table>
  </form>
<?php }
else {
    printf(gTranslate('core', "Your E-C@rd with the picture below has been sent to %s &lt;%s&gt;."), $ecard["name_recepient"], $ecard["email_recepient"]);
?>
  <p align="center"><?php echo $gallery->album->getThumbnailTag($ecard['photoIndex']); ?></p>
  <br>
  <a href="javascript:window.close()"><?php echo gTranslate('core', "Close this window") ?></a>
<?php }
?>
</div>
<?php
global $GALLERY_EMBEDDED_INSIDE;
$validation_args = array('photoIndex' => $photoIndex);
$validation_file = basename(__FILE__);
if (! isset($GALLERY_EMBEDDED_INSIDE)) {
    print gallery_validation_link($validation_file, true, $validation_args);
}
?>
</body>
</html>
