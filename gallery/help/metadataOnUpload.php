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

printPopupStart(gTranslate('core', "Metadata on Upload Help"),'', langLeft());
?>
	<ul>
	  <li>
		  <span class="g-attention"><?php echo gTranslate('core', "What is meant with metadata in this context?"); ?></span>
	
		  <p>
			<?php echo gTranslate('core', "Photos in Gallery have descriptive fields, like caption, title and other data fields. You can also define your own custom fields."); ?>
			<br><?php echo gTranslate('core', "This information is called Metadata."); ?>
		  </p>
	
		  <p>
			<?php echo gTranslate('core', "Normally this info is added manually inside the Gallery for each photo."); ?>
			<br><?php echo gTranslate('core', "You can also do this automatically during your uploads."); ?>
		  </p>
	  </li>
   
	  <li>
		  <span class="g-attention"><?php echo gTranslate('core', "How can I add the metadata?"); ?></span>
	
		  <p>
			<?php printf(gTranslate('core', "Create a %s'csv-file (Comma Separated Values)'%s which contains the data you want associated with the files you are uploading."), '<a href="http://en.wikipedia.org/wiki/Comma-separated_values">', '</a>'); ?>
			<br><?php echo gTranslate('core', "Upload this file at the same time as you upload your files, you cannot upload it later and expect Gallery to import the metadata from it."); ?>
		  </p>
	  </li>

	
	  <li>
		  <span class="g-attention"><?php echo gTranslate('core', "In which format does the data have to be?"); ?></span>
	
		  <p>
		<?php echo gTranslate('core', "The first row must be the fieldnames, there is one mandatory field, some predefined fields and you can use your own custom fields."); ?>
		<?php echo gTranslate('core', "Order does not matter, but you have to use a <b>;</b> (Semicolon) as separator as your texts might have commas."); ?>
			<ul><li><?php printf(gTranslate('core', "Mandatory: %s"), "'Filename'"); ?>
				<li><?php printf(gTranslate('core', "Predefined: %s, %s, %s"), "'Caption'", "'Title'", "'Description'"); ?>
			</ul>
	
			<br><?php echo gTranslate('core', "Then follow the lines containing the info itself."); ?>
	  </li>
	</ul>

	<b><?php echo gTranslate('core', "Example:"); ?></b>
		<div style="padding: 2px; border: 1px solid black">Filename;Caption;Title;Note
	  <br>madonna.jpg;Madonna in Concert;Madonna Picture;Live in Concert in NYC
		  <br>myExGirlfriend.jpg;Joan;Joan;I miss her so
		  <br>car.jpg;Mercedes 200D/8;Mercedes Benz Diesel built in 1976;Some day i will own it
		</div>
	  
	<br>
	<div align="center">
	  <?php echo gButton('back', gTranslate('core', "Back to upload"), 'history.back()'); ?>
	</div>
  
</div>
  
</body>
</html>
