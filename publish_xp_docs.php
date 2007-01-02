<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2007 Bharat Mediratta
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

require_once(dirname(__FILE__) . '/init.php');

printPopupStart(gTranslate('core', "Publishing with Windows XP"), '', 'left');

echo '<p class="g-sitedesc">';
echo sprintf(gTranslate('core', "Windows XP comes with a nice feature that allows you to publish content from your desktop directly to a web service.  %s <b>has experimental</b> support for this feature."), Gallery());
echo gTranslate('core', "It's relatively easy to configure.");

?>

</p>

  <div class="center g-emphasis"><?php echo gTranslate('core', "Step 1") ?></div>

  <div class="g-albumdesc-cell">
<?php
	echo sprintf(gTranslate('core', "Download the %sXP Configuration File%s"),
		'<a href="'.makeGalleryUrl('publish_xp.php').'">', '</a>');
	echo sprintf(gTranslate('core', "Save this file on your PC and rename it %s."), '"install_registry.reg"');
	echo gTranslate('core', "If it asks you for confirmation about changing the file type, answer &quot;yes&quot;.");
	echo gTranslate('core', "Right click on this file and you should see a menu appear.");
	echo gTranslate('core', "Select the <b>Merge</b> option (this should be at the top of the menu).");
	echo gTranslate('core', "It will ask you if you want to import these values into your registry.");
	echo gTranslate('core', "Click &quot;ok&quot;.");
	echo gTranslate('core', "It will tell you that the files were imported successfully.");
	echo gTranslate('core', "Click &quot;ok&quot; again.");
?>
  </div>

  <br>
  <div class="center g-emphasis"><?php echo gTranslate('core', "Step 2") ?></div>

  <div class="g-albumdesc-cell">
<?php 	echo gTranslate('core', "Open your Windows explorer and browse to a folder containing supported images.") ."  ";
	echo gTranslate('core', "Select the image(s) or a folder and there should be a link on the left that says &quot;Publish this file to the web...&quot;");
	echo gTranslate('core', "Click this link and then follow the instructions to log into your Gallery, select an album and publish the image.") ?>
  </div>

</div>

<div class="center">
  <?php echo galleryLink(makeGalleryUrl('add_photos.php', array('type' => 'popup')),gTranslate('core', "Return to Add Photos")); ?>
</div>

</body>
</html>
<?php
?>
