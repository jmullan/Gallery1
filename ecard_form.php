<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2005 Bharat Mediratta
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
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
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

  list($photoIndex, $ecard, $submit_action) = getRequestVar(array('photoIndex', 'ecard', 'submit_action'));
  $photo = $gallery->album->getPhoto($photoIndex);

  /* Get the dimensions of the sized Photo */
  list($width, $height) = $photo->getDimensions(0, false);  

  $max_length = 300;   // Maximum length of the e-Card text
  $msgTextError1 = _("Error processing e-card. Please try later.");
  $msgTextError2 = _("Some input fields are not correctly filled out. Please fill out.");
  $ecard_PLAIN_data = _("You have an e-card as attachment. Click to see.");

  $error_msg = "";

  $ecard_send = false;

  if (! empty($submit_action)) {
    if ( check_email($ecard["email_recepient"]) && check_email($ecard["email_sender"]) && ($ecard["email_recepient"] != "") && ($ecard["name_sender"] != "") )  {
      if (strlen($ecard["message"]) > $max_length) {
	$ecard["message"] = substr($ecard["message"],0,$max_length-1);
      }
      list($error,$ecard_data_to_parse) = get_ecard_template($ecard["template_name"]);
      if ($error) {
        $error_msg = $msgTextError1;
       } else {
           $ecard_HTML_data = parse_ecard_template($ecard,$ecard_data_to_parse);
           $result = send_ecard($ecard,$ecard_HTML_data,$ecard_PLAIN_data);
           if ($result) {
             $ecard_send = true;
           } else {
               $error_msg = $msgTextError1;
             }
         }
    } else {
       $error_msg = $msgTextError2;
      }
  } else {
	if (!isset($ecard["image_name"])) {
	    $ecard["image_name"] = $gallery->album->getPhotoPath($photoIndex, false);
	}
    }
doctype();
?>
<html>
  <?php common_header(); ?>
  <title><?php echo _("Send this photo as eCard") ?></title>
 
<script type="text/javascript">
<!--
function popup_win(theURL,winName,winOptions) {
   win = window.open(theURL,winName,winOptions);
   win.focus();
 }

 function make_preview() {
   document.ecard_form.action = "ecard_preview.php";
   popup_win('_templates/leer.htm','ecard_preview','resizable=yes,scrollbars=yes,width=800,height=600');
   document.ecard_form.target = "ecard_preview";
   document.ecard_form.submit();
 }
 function take_stamp() {
   document.ecard_form.action = "stamp_preview.php";
   popup_win('stamp_preview.php','stamp_preview','resizable=yes,scrollbars=yes,width=300,height=600');
   document.ecard_form.target = "stamp_preview";
   document.ecard_form.submit();
 }
 function send_ecard() {
   document.ecard_form.action = "<?php echo $HTTP_SERVER_VARS["PHP_SELF"] ?>";
   document.ecard_form.target = "_self";
   document.ecard_form["submit_action"].value = "send";
   if (check()) { document.ecard_form.submit(); }
 }
 
 function check() {
   var error = false;
   var error_message = "<?php echo _("Error: to send an eCard you need to fill out all fields.";) ?>";
   error_message +="\n <?php echo_("Please fill this fields:"); ?>\n\n";

   if (document.ecard_form["ecard[name_sender]"].value == "") {
     error = true;
     error_message += "<?php echo _("- Your Name"); ?>\n";
   } 
 
   if ((document.ecard_form["ecard[email_sender]"].value == "") && 
      (document.ecard_form["ecard[email_sender]"].value.indexOf("@") == -1)) {
        error = true;
        error_message += "<?php echo _("- Your Email"); ?>\n";
   }
  
   if (document.ecard_form["ecard[name_recepient]"].value == "") {
     error = true;
     error_message += "<?php echo _("- Recipient's Name"); ?>\n";
   } 
 
   if ((document.ecard_form["ecard[email_recepient]"].value == "") && 
      (document.ecard_form["ecard[email_recepient]"].value.indexOf("@") == -1)) {
        error = true;
        error_message += "<?php echo _("- Recipient's Email"); ?>\n";
   }
  
   if (document.ecard_form["ecard[message]"].value == "") {
     error = true;
     error_message += "<?php echo _("- Your Message"); ?>\n";
   }

   if (error) {
     error_message += "\n\n<?php echo _('Please fill all fields next click >Send<.'); ?>";
     alert(error_message);
     return false;  // Formular wird nicht abgeschickt.
   } else {
       return true;  // Formular wird abgeschickt.
     }

  } // Ende function check()
  
  function CountMax() {
    max = <?php echo $max_length ?>;
    wert = max - document.ecard_form["ecard[message]"].value.length;
    if (wert < 0) {
      alert("<?php echo sprintf(_("You have entered more than %d characters"), $max_length); ?>");
      document.ecard_form["ecard[message]"].value = document.ecard_form["ecard[message]"].value.substring(0,max);
      wert = 0;
      document.ecard_form.counter.value = wert;
    } else {
        document.ecard_form.counter.value = max - document.ecard_form["ecard[message]"].value.length;
      }
  } // Ende function CountMax()

function MM_openBrWindow(theURL,winName,features) { //v2.0
  window.open(theURL,winName,features);
}
//-->
</script>

</HEAD>

<body class="popupbody" dir="<?php echo $gallery->direction ?>" onLoad="document.ecard_form['ecard[name_sender]'].focus()">
<div class="popuphead"><?php echo _("Send this photo as eCard") ?></div>
<div align="center" class="popup">

<?php 
    if (! $ecard_send) {
	echo $gallery->album->getThumbnailTag($photoIndex);
	if (!empty($error_msg)) {
	    echo '<p>'. gallery_error($error_msg) .'</p>';
	}

    echo makeFormIntro("ecard_form.php",
                array("name" => "ecard_form", "method" => "POST"),
                array("type" => "popup"));
?>
  <input name="ecard[image_name]" type="hidden" value="<?php echo $ecard["image_name"] ?>">
  <input name="ecard[template_name]" type="hidden" value="ecard_1.tpl">
  <input name="photoIndex" type="hidden" value="<?php echo $photoIndex; ?>">
  <input name="submit_action" type="hidden" value="">

  <br>
  <table border="0" cellpadding="0" cellspacing="4" align="center">
  <tr>
    <td class="columnheader" colspan="2"><?php echo _("Your info"); ?></td>
    <td width="10">&nbsp;</td>
    <td class="columnheader" colspan="2"><?php echo _("Recipient's info"); ?></td>
  </tr>
  <tr>
    <td><?php echo _("Name") ?></td>
    <?php
        $defaultSenderName = '';
	$defaultSenderEmail = '';
	if (! empty($gallery->user) && $gallery->user->isLoggedIn()) {
	    $defaultSenderName = $gallery->user->displayName();
	    $defaultSenderEmail = $gallery->user->getEmail();
	}
    ?>
    <td><input tabindex="1" maxlength="40" name="ecard[name_sender]" size="18" type="Text" value="<?php echo $defaultSenderName; ?>"></td>
    <td></td>
    <td><?php echo _("Name") ?></td>
    <td><input tabindex="3" maxlength="40" name="ecard[name_recepient]" size="18" type="Text" value=""></td>
  </tr>
  <tr>
    <td><?php echo _("E-Mail"); ?></td>
    <td><input tabindex="2" maxlength="40" name="ecard[email_sender]" size="18" type="Text" value="<?php echo $defaultSenderEmail; ?>"></td>
    <td></td>
    <td><?php echo _("E-Mail"); ?></td>
    <td><input tabindex="4" maxlength="40" name="ecard[email_recepient]" size="18" type="Text" value=""></td>
  </tr>
  <tr>
    <td colspan="5" align="center">
  	  <select id="ecardstamp" name="ecard[stamp]">
            <option selected value="<?php echo $gallery->app->photoAlbumURL .'/images/ecard_images/08.gif' ?>"><?php echo _("Choose a Stamp"); ?></option>
<?php
for($i = 1; $i <= 27; $i++) {
    $nr = sprintf("%02d", $i-1);
    echo "\n\t" . '<option value="'. $gallery->app->photoAlbumURL .'/images/ecard_images/'. $nr .'.gif">';
    echo sprintf(_("Stamp #%d"), $i);
    echo "</option>";
}
?>
        </select>
        <img alt="helpIcon" height="15" hspace="5" onclick="MM_openBrWindow('stamp_preview.php','Francobolli','scrollbars=yes,width=130,height=300')" src="images/ecard_images/icon_help.gif" width="15">
    </td>
  </tr>
  <tr>
    <td><?php echo _("Subject:"); ?></td>
    <?php $defaultSubject = (!empty($defaultSenderName)) ? sprintf(_("%s sent you an E-C@rd"), $defaultSenderName) : ''; ?>
    <td colspan="4"><input type="Text" size="65" maxlength="75" name="ecard[subject]" value="<?php echo $defaultSubject; ?>"></td>
  </tr>
  <tr>
    <td colspan="5"><?php echo _("Your Message:"); ?></td>
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
        <td><input type="button" onClick="javascript:make_preview();" value="<?php echo _("Preview"); ?>"></td>
        <td><input type="reset" value="<?php echo _("Reset"); ?>"></td>
	<td width="100%">&nbsp;</td>
        <td align="left"><input type="button" onClick="javascript:window.close()" value="<?php echo _("Cancel"); ?>"></td>
	<td><input type="button" onClick="javascript:send_ecard();" value="<?php echo _("Send eCard"); ?>"></td>
      </tr>
     </table>
     </td>
  </tr>
  </table>
  </form>
<?php } else {
    echo sprintf(_("Your E-C@rd with the picture below has been sent to %s &lt;%s&gt;."), $ecard["name_recepient"], $ecard["email_recepient"]);
?>
  <p align="center"><?php echo $gallery->album->getThumbnailTag($photoIndex); ?></p>
<br>
<a href="javascript:window.close()"><?php echo _("Close this window") ?></a>
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
