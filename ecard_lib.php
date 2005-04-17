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
###################################################################
# IBPS E-C@ard for Gallery           Version 1                    #
# Copyright 2002 IBPS Friedrichs     info@ibps-friedrichs.de      #
# Ported for Gallery By freenik      webmaster@cittadipolicoro.com#
# Filename: ecard_lib.php                                         #
###################################################################
*/
/*
** Modidied (stripped and changed) by Jens Tkotz <jens@jems.de>
*/

include('htmlMimeMail.php');

  function get_ecard_template($template_name) {
    $error = false;
    $file_data = "";
    $fpread = @fopen("_templates/".$template_name, 'r');
    if (!$fpread) {
      $error = true;
    } else {
        while(! feof($fpread) ) {
          $file_data .= fgets($fpread, 4096);
        }
        fclose($fpread);
      }
    return array($error,$file_data);
  }

   function parse_ecard_template($ecard,$ecard_data) {
    $ecard_data = preg_replace ("/<%ecard_sender_email%>/", $ecard["email_sender"], $ecard_data);
    $ecard_data = preg_replace ("/<%ecard_sender_name%>/", $ecard["name_sender"], $ecard_data);
    $ecard_data = preg_replace ("/<%ecard_image_name%>/", $ecard["image_name"], $ecard_data);
    $ecard_data = preg_replace ("/<%ecard_message%>/", preg_replace ("/\r?\n/", "<BR>\n", htmlspecialchars($ecard["message"])), $ecard_data);
    $ecard_data = preg_replace ("/<%ecard_reciepient_email%>/", $ecard["email_recepient"], $ecard_data);
    $ecard_data = preg_replace ("/<%ecard_reciepient_name%>/", $ecard["name_recepient"], $ecard_data);
    $ecard_data = preg_replace ("/<%ecard_stamp%>/", $ecard["stamp"], $ecard_data);
	
    return $ecard_data;
  }

  function send_ecard($ecard,$ecard_HTML_data,$ecard_PLAIN_data) {
    $ecard_mail = new htmlMimeMail();
    $ecard_image = $ecard_mail->getFile($ecard["image_name"]);
    if (preg_match_all("/(<IMG.*SRC=\")(.*)(\".*>)/Uim", $ecard_HTML_data, $matchArray)) {
      for ($i=0; $i<count($matchArray[0]); ++$i) {
        $ecard_image = $ecard_mail->getFile($matchArray[2][$i]);
      }
    }
    $ecard_mail->setHtml($ecard_HTML_data, $ecard_PLAIN_data,'./');
    $ecard_mail->setFrom($ecard["name_sender"].'<'.$ecard["email_sender"].'>');
    $ecard_mail->setSubject('You have an E-C@rd from '.$ecard["name_sender"]);
    $ecard_mail->setReturnPath($ecard["email_sender"]);
	
    $result = $ecard_mail->send(array($ecard["email_recepient"]));
    
    return $result;
  }
  
  function check_email($email) {
    if (preg_match ("/(@.*@)|(\.\.)|(@\.)|(\.@)|(^\.)/", $email) || !preg_match ("/^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/", $email)) {
      $mail_ok = false;
    } else {
        $mail_ok = true;
      }
    return $mail_ok;
  }  # End of - sub check_email -
?>