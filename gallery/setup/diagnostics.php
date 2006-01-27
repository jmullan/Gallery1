<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2006 Bharat Mediratta
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
?>
<html>
<head>
	<title><?php echo _("Gallery Diagnostics Page") ?></title>
	<?php echo getStyleSheetLink() ?>
</head>

<body dir="<?php echo $gallery->direction ?>">
<?php configLogin(basename(__FILE__)); ?>

<div class="header"><?php echo _("Gallery Diagnostics") ?></div>
<p></p>
<div class="sitedesc">
<?php echo _("This page is designed to provide some diagnostics about your server to help you find issues that may prevent Gallery from functioning properly.") ?>
<?php echo ' ' . _("The config wizard tries all kinds of diagnostics to try to find and work around any issues that it finds on your system, but there may be other problems that we have not thought of.") ?>
<?php echo ' ' . _("You can use these tools to find out more about your setup") ?>:
</div>
<p></p>
	<table class="inner" width="100%">
	  <tr>
	    <th class="separator"> <?php echo _("Tool") ?> </th>
	    <th class="separator"> <?php echo _("Description") ?> </th>
	  </tr>
	  <tr>
	    <td class="shortdesc" style="padding: 10px;" width="140" align=center valign="top">
	      <a href="phpinfo.php"><?php echo _("PHP Info") ?></a>
	    </td>
	    <td class="desc" valign="top">
		<?php echo _("This page provides information about your PHP installation.") ?>
		<?php echo _("It's a good place to look to examine all the various PHP configuration settings, and to find out on what kind of system you're running (sometimes it's difficult to tell when you're on an ISP's machine)") ?>
	    </td>
	  </tr>

	  <tr>
	    <td class="shortdesc" style="padding: 10px;" width="140" align=center valign="top">
	      <a href="check_netpbm.php"><?php echo _("Check") ?> NetPBM</a>
	    </td>
	    <td class="desc" valign="top">
	      <?php echo _("This page provides information about your NetPBM binaries.") ?> 
		<?php echo _("You can only use this page after you have successfully completed the configuration wizard (as it expects that you've already located and configured Gallery with the right path to NetPBM).") ?>
	    </td>
	  </tr>

	  <tr>
	    <td class="shortdesc" style="padding: 10px;" width="140" align=center valign="top">
	      <a href="check_imagemagick.php"><?php echo _("Check") ?> ImageMagick</a>
	    </td>
	    <td class="desc" valign="top">
		<?php echo _("This page provides information about your ImageMagick binaries.") ?> 
		<?php echo _("You can only use this page after you have successfully complete the configuration wizard (as it expects that you've already located and configured Gallery with the right path to ImageMagick).") ?>
	    </td>
	  </tr>

	  <tr>
	    <td class="shortdesc" style="padding: 10px;" width="140" align=center valign="top">
	      <a href="session_test.php"><?php echo _("Check Sessions") ?></a>
	    </td>
	    <td class="desc" valign="top">
		<?php echo _("This page runs a very simple test on your PHP session configuration.") ?>
		<?php echo _("Gallery requires that your PHP installation is configured with proper session support.") ?>
	    </td>
	  </tr>

	  <tr>
	    <td class="shortdesc" style="padding: 10px;" width="140" align=center valign="top">
	      <a href="check_mail.php"><?php echo _("Check Email") ?></a>
	    </td>
	    <td class="desc" valign="top">
		<?php echo _("This page will simply send a test email.") ?>
		<?php echo sprintf(_("This allows you to see if you can use the email functions in %s."), Gallery()) ?>
	    </td>
	  </tr>

	  <tr>
	    <td class="shortdesc" style="padding: 10px;" width="140" align=center valign="top">
	      <a href="check_versions.php"><?php echo _("Check versions") ?></a>
	    </td>
	    <td class="desc" valign="top">
		<?php echo sprintf(_("This page is for %s upgrades that have problems."), Gallery()); ?>
		<?php echo sprintf(_("This allows you to check you have the correct version of all your %s files."), Gallery()) ?>
	    </td>
	  </tr>
	</table>

	<p> </p>

	<center>
	  <?php echo returnToConfig(); ?>
	</center>

    </body>
  </html>
 
