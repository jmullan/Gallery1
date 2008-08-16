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

require_once(dirname(__FILE__) . '/init.php');

printPopupStart(gTranslate('config', "Gallery Diagnostics Page"), '', 'left');

configLogin(basename(__FILE__));
?>

<div class="g-sitedesc">
<?php
	echo gTranslate('config', "This page is designed to provide some diagnostics about your server to help you find issues that may prevent Gallery from functioning properly.");
	echo ' ' . gTranslate('config', "The config wizard tries all kinds of diagnostics to try to find and work around any issues that it finds on your system, but there may be other problems that we have not thought of.");
	echo ' ' . gTranslate('config', "You can use these tools to find out more about your setup");
?>
</div>

<br>
	<table width="100%">
	  <tr>
		<th class="g-columnheader"> <?php echo gTranslate('config', "Tool") ?> </th>
		<th class="g-columnheader"> <?php echo gTranslate('config', "Description") ?> </th>
	  </tr>
	  <tr>
		<td class="g-desc-cell" style="padding: 10px;" width="140">
		  <?php echo galleryLink('phpinfo.php', gTranslate('config', "PHP _Info")); ?>
		</td>
		<td class="g-longdesc">
		  <?php echo gTranslate('config', "This page provides information about your PHP installation.") ?>
		  <?php echo gTranslate('config', "It's a good place to look to examine all the various PHP configuration settings, and to find out on what kind of system you're running (sometimes it's difficult to tell when you're on an ISP's machine)") ?>
		</td>
	  </tr>

	  <tr>
		<td class="g-desc-cell" style="padding: 10px;" width="140">
		  <?php echo galleryLink('check_netpbm.php', gTranslate('config', "Check _Netpbm")); ?>
		</td>
		<td class="g-longdesc">
		  <?php echo gTranslate('config', "This page provides information about your Netpbm binaries.") ?>
		  <?php echo gTranslate('config', "You can only use this page after you have successfully completed the configuration wizard (as it expects that you've already located and configured Gallery with the right path to Netpbm).") ?>
		</td>
	  </tr>

	  <tr>
		<td class="g-desc-cell" style="padding: 10px;" width="140">
		  <?php echo galleryLink('check_imagemagick.php', gTranslate('config', "Check Image_Magick")); ?>
		</td>
		<td class="g-longdesc">
		  <?php echo gTranslate('config', "This page provides information about your ImageMagick binaries.") ?>
		  <?php echo gTranslate('config', "You can only use this page after you have successfully complete the configuration wizard (as it expects that you've already located and configured Gallery with the right path to ImageMagick).") ?>
		</td>
	  </tr>

	  <tr>
		<td class="g-desc-cell" style="padding: 10px;" width="140">
		  <?php echo galleryLink('session_test.php', gTranslate('config', "Check _Sessions")); ?>
		</td>
		<td class="g-longdesc">
		<?php echo gTranslate('config', "This page runs a very simple test on your PHP session configuration.") ?>
		<?php echo gTranslate('config', "Gallery requires that your PHP installation is configured with proper session support.") ?>
		</td>
	  </tr>

	  <tr>
		<td class="g-desc-cell" style="padding: 10px;" width="140">
		  <?php echo galleryLink('check_mail.php', gTranslate('config', "Check _Email")); ?>
		</td>
		<td class="g-longdesc">
		  <?php echo gTranslate('config', "This page will simply send a test email.") ?>
		  <?php echo sprintf(gTranslate('config', "This allows you to see if you can use the email functions in %s."), Gallery()) ?>
		</td>
	  </tr>
	  <tr>
		<td class="g-desc-cell" style="padding: 10px;" width="140">
		  <?php echo galleryLink('check_versions.php', gTranslate('config', "Check _Versions")); ?>
		</td>
		<td class="g-longdesc">
		  <?php echo sprintf(gTranslate('config', "This page is for %s upgrades that have problems."), Gallery()); ?>
		  <?php echo sprintf(gTranslate('config', "This allows you to check you have the correct version of all your %s files."), Gallery()) ?>
		</td>
	  </tr>
	</table>

	</div>

	<div class="center">
	  <?php echo returnToConfig(); ?>
	</div>

	</body>
</html>
