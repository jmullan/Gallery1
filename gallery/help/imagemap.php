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

require_once(dirname(dirname(__FILE__)) . '/init.php');

printPopupStart(gTranslate('core', "ImageMap help"),'', langLeft());
?>
	<div align="center" class="g-emphasis">
	  <?php  echo gTranslate('core', "This is a help for working with Gallerys so called 'ImageMaps'"); ?>
	</div>

	<p align="left">
	<?php echo gTranslate('core', "An ImageMap is a predefined area of a picture, or a hotspot if you will."); ?>
	<br><?php echo gTranslate('core', "A sample usage scenario is a family photo, where you can apply an ImageMap for each of the family members featured in the photo."); ?>
	<br><?php echo gTranslate('core', "The defined area can contain information you want displayed when a user places his mouse pointer on top of that area."); ?>
	<br><br><?php echo gTranslate('core', "You can add basic text, or even define an URL that gets activated when a user clicks the area you have defined as an ImageMap."); ?>
	</p>

	<ul>
		<li>
		  <span class="g-attention"><?php echo gTranslate('core', "How to create an ImageMap in Gallery?"); ?></span>
	
		  <p>
			<?php echo gTranslate('core', "It's as easy as it is fun. First, you need to create your ImageMap, this is done by clicking on the image, in the position where you want the first corner of the ImageMap to be."); ?>
			<br><?php echo gTranslate('core', "Click again on a new spot, and you'll see that Gallery creates a polygon shape based on the two spots you have created."); ?>
			<br><?php echo gTranslate('core', "Click again to create another spot. Add spots until your polygon shape surrounds the entire area you want to use as an ImageMap."); ?>
		  </p>
	
		  <p>
			<?php echo gTranslate('core', "When you are satisifed with your selection (e.g. a person or object), enter the describing text for your ImageMap in the 'Description' field on the left hand side."); ?>
			<br><?php echo gTranslate('core', "The text entered in the description field, will be displayed when your users point their mouse cursor over the ImageMap"); ?>
		  </p>
	
		  <p>
			<?php echo gTranslate('core', "Optionally you can enter a Link-URL for your ImageMap, which is activated when a user clicks your ImageMap selection."); ?>
			<br><?php echo gTranslate('core', "When you are satisfied, click the 'Save ImageMap' button at the left side."); ?>
		  </p>
		  </li>
	</ul>

	<ul>
		<li>
		  <span class="g-attention"><?php echo gTranslate('core', "How to update an ImageMap in Gallery?"); ?></span>
	
		  <p>
			<?php echo gTranslate('core', "Currently you can only modify the text and the URL for an ImageMap. The polygon for the ImageMap is currently not changeable."); ?>
			<br><?php echo gTranslate('core', "If you do need to change the actual shape of the selection, you need to delete it and create a new selection in the shape you want."); ?>
		  </p>
		  <p>
			<?php echo gTranslate('core', "Select the ImageMap you want to edit in the box on the left hand side."); ?>
			<br><?php echo gTranslate('core', "The image preview on the right hand side, will show which area is covered by the ImageMap currently selected."); ?>
			<br><?php echo gTranslate('core', "You can then modify your 'Description', or edit the 'Link-URL'."); ?>
			<br><?php echo gTranslate('core', "When finished, click the 'Update ImageMap' button."); ?>
		  </p>
		</li>
	</ul>

	<ul>
		<li>
		  <span class="g-attention"><?php echo gTranslate('core', "How to delete an ImageMap in Gallery?"); ?></span>
	
		  <p>
		<?php echo gTranslate('core', "Just select the ImageMap(s) you want to delete in the box. Then click the 'Delete selected ImageMap(s)' button."); ?>
		  </p>
		</li>
	</ul>

	<div align="center">
	  <?php echo gButton('close', gTranslate('core', "Close Window"), 'parent.close()'); ?>
	</div>
	
</div>
  
</body>
</html>
