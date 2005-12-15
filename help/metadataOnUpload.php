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
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * $Id$
 */
?><?php

require_once(dirname(dirname(__FILE__)) . '/init.php');

printPopupStart(_("Metadata on Upload Help"),'', langLeft());
?> 
  <style>
        li.top { font-weight: bold; color: red;}
  </style>
    <ul>
      <li class="top"><?php echo _("What is meant with metadata in this context ?"); ?></li>

      <p>
        <?php echo _("Photos in Gallery have descriptive fields, like caption, title and so on. You can also have your own custom fields."); ?>
        <br><?php echo _("All this information are called metadata."); ?>
      </p>
      
      <p>
        <?php echo _("Normally this info is added manually inside the Gallery for each photo."); ?>
        <br><?php echo _("But you are also able to do this automatically during the upload."); ?>
      </p>

    </ul>

    <ul>
      <li class="top"><?php echo _("How can i add the metadata?"); ?></li>

      <p>
        <?php echo _("You have to create file that contain the info you want to give during the upload.."); ?>
        <br><?php echo _("Upload this file together with your pictures."); ?>
      </p>
    </ul>

    <ul>
      <li class="top"><?php echo _("In which format has the data to be?"); ?></li>

      <p>
	    <?php echo _("The first row must be the fieldnames, there is one mandatory field, some predefined fields and you can use your own custom fields. "); ?>
	    <?php echo _("Order does not matter, but you have to you a <b>;</b> (Semicolon) as separator."); ?>
        <ul><li><?php printf(_("Mandatory: %s"), "'filename'"); ?>
            <li><?php printf(_("Predefined: %s, %s, %s"), "'caption'", "'title'", "'description'"); ?>
        </ul>
        <br><?php echo _("Then follow the lines containing the info it self"); ?>
        <br><br><b><?php echo _("Example:"); ?></b>
        <div style="border: 1px solid black">filename;Caption;Note
        <br>myExGirlfriend.jpg;Joan;I miss her so
        <br>car.jpg;Mercededes 200D/8;Some day i will own it
        </div>

      </p>

    </ul>

    <div align="center">
      <input type="button" value="<?php echo _("Back to upload"); ?>" onclick="history.back()">
    </div>
  </div>
</body>
</html>
