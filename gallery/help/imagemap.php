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

printPopupStart(_("ImageMap Help"),'', langLeft());
?>
  <style>
        li { font-weight: bold; color: red;}
  </style>

    <div align="center" style="font-weight: bold">
      <?php  echo _("This is a help for working with gallerys so called 'imagemaps'"); ?>
    </div>   

    <p align="left">
	<?php echo _("An ImageMap is a predefined area of a picture, or a hotspot if you will."); ?>
    <br><?php echo _("A sample usage scenario is a family photo, where you can apply an ImageMap for each of the family members featured in the photo."); ?>
    <br><?php echo _("The defined area can contain information you want displayed when a user places his mouse pointer on top of that area."); ?>
    <br><br><?php echo _("You can add basic text, or even define an URL that gets activated when a user clicks the area you have defined as an ImageMap."); ?>
    </p>

    <ul>
      <li><?php echo _("How to create an ImageMap in Gallery?"); ?></li>

      <p>
        <?php echo _("It's as easy as it is fun. First, you need to create your ImageMap, this is done by clicking on the image, in the position where you want the first corner of the ImageMap to be."); ?>
        <br><?php echo _("Click again on a new spot, and you'll see that Gallery creates a polygon shape based on the two spots you have created."); ?>
        <br><?php echo _("Click again to create another spot. Add spots until your polygon shape surrounds the entire area you want to use as an ImageMap."); ?>
      </p>

      <p>
        <?php echo _("When you are satisifed with your selection (e.g. a person or object), enter the describing text for your for your ImageMap in the 'Description' form on the left hand side."); ?>
        <br><?php echo _("The text entered in the Description field, will be displayed when your users point their mousecursor over the ImageMap"); ?>
      </p>

      <p>
        <?php echo _("Optionally you can enter a Link-Url for your ImageMap, which is activated when a user clicks your ImageMap selection."); ?>
        <br><?php echo _("When you satisfied, click the 'Save ImageMap' button at the left side."); ?>
      </p>
    </ul>

    <ul>
      <li><?php echo _("How to update an ImageMap in Gallery?"); ?></li>

      <p>
        <?php echo _("Currently you can only modify the text and the url for an ImageMap. The polygon for the ImageMap is currently not changeable."); ?>
        <br><?php echo _("If you do need to change the actual shape of the selection, you need to delete it and create a new selection in the shape you want."); ?>
      </p>
      <p>
        <?php echo _("Select the ImageMap you want to edit in the box on the left hand side."); ?>
        <br><?php echo _("The Image preview on the right hand side, will show which area is covered by the ImageMap currently selected."); ?>
        <br><?php echo _("You can then modify your 'Description', or edit the 'Link-URL'."); ?>
        <br><?php echo _("When finished, click the 'Update ImageMap' button. "); ?>
      </p>
    </ul>

    <ul>
      <li><?php echo _("How to delete an ImageMap in Gallery?"); ?></li>

      <p>
	<?php echo _("Just select the ImageMap(s) you want to delete in the box. Then click the 'Delete selectes ImageMap(s)' button."); ?>
      </p>

    </ul>

    <div align="center">
      <input type="button" value="<?php echo _("Close Window"); ?>" onclick="parent.close()">
    </div>
  </div>
</body>
</html>
