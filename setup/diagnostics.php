<?php /* $Id$ */ ?>
<?php 

$GALLERY_BASEDIR="../";
@include($GALLERY_BASEDIR . "config.php"); 
require($GALLERY_BASEDIR . "util.php");
initLanguage();
?>
<html>
    <head>
      <title><?php echo _("Gallery Diagnostics Page") ?></title>
    </head>
    <body dir=<?php echo $gallery->direction ?>>
      <center>
	<H1><?php echo _("Gallery Diagnostics") ?></H1>
      </center>

	<?php echo _("This page is designed to provide some diagnostics about your server to help you find issues that may prevent Gallery from functioning properly.") ?>
	<?php echo _("The config wizard tries all kinds of diagnostics to try to find and work around any issues that it finds on your system, but there may be other problems that we have not thought of.") ?>
	<?php echo _("You can use these tools to find out more about your setup") ?>:

      <p></p>

      <center>
	<table width=90% border=1>
	  <tr>
	    <th bgcolor=#99AACC> <?php echo _("Tool") ?> </th>
	    <th bgcolor=#99AACC> <?php echo _("Description") ?> </th>
	  </tr>
	  <tr>
	    <td width=140 align=center valign=top>
	      <a href="phpinfo.php"><?php echo _("PHP Info") ?></a>
	    </td>
	    <td>
		<?php echo _("This page provides information about your PHP installation.") ?>
		<?php echo _("It's a good place to look to examine all the various PHP configuration settings, and to find out on what kind of system you're running (sometimes it's difficult to tell when you're on an ISP's machine)") ?>
	    </td>
	  </tr>

	  <tr>
	    <td width=140 align=center valign=top>
	      <a href="check_netpbm.php"><?php echo _("Check") ?> NetPBM</a>
	    </td>
	    <td>
	      <?php echo _("This page provides information about your NetPBM binaries.") ?> 
		<?php echo _("You can only use this page after you have successfully completed the configuration wizard (as it expects that you've already located and configured Gallery with the right path to NetPBM).") ?>
	    </td>
	  </tr>

	  <tr>
	    <td width=140 align=center valign=top>
	      <a href="check_imagemagick.php"><?php echo _("Check") ?> ImageMagick</a>
	    </td>
	    <td>
		<?php echo _("This page provides information about your ImageMagick binaries.") ?> 
		<?php echo _("You can only use this page after you have successfully complete the configuration wizard (as it expects that you've already located and configured Gallery with the right path to ImageMagick).") ?>
	    </td>
	  </tr>

	  <tr>
	    <td width=140 align=center valign=top>
	      <a href="session_test.php"><?php echo _("Check Sessions") ?></a>
	    </td>
	    <td>
		<?php echo _("This page runs a very simple test on your PHP session configuration.") ?>
		<?php echo _("Gallery requires that your PHP installation is configured with proper session support.") ?>
	    </td>
	  </tr>

	</table>

	<p> </p>

	<center>
	  <?php echo sprintf(_("Return to the %sconfig wizard%s."),
			  '<a href="index.php">', '</a>') ?>
	</center>

    </body>
  </html>
 
