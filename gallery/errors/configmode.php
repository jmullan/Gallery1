<?php
// $Id$
// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
                    !empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
                    !empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
            print "Security violation\n";
	            exit;
		    }
?>
<?php if (! (@include($GALLERY_BASEDIR . "ML_files/ML_config.php")) || ! $gallery->ML) {
		include ($GALLERY_BASEDIR ."setup/ML_wizard.php");
		exit;	
	}
	
require($GALLERY_BASEDIR . "errors/configure_instructions.php");
?>
<html>
<head>
  <title><?php echo _("Gallery in Configuration Mode") ?></title>
  <?php echo getStyleSheetLink() ?>
</head>
<body dir=<?php echo $gallery->direction ?>>
<?php @include ($gallery->path ."ML_files/ML_info_addon.inc"); ?>
<center>
<span class="title"> <?php echo _("Gallery: Configuration Mode") ?> </span>
<p>
<table width=80%><tr><td>
<br>
<center>
<?php echo _("If you want to reconfigure language settings first, start the"); ?> 
<a href="<?php echo $GALLERY_BASEDIR ?>setup/ML_wizard.php"><?php echo _("ML Configuration Wizard") ?></a>
</p>
<?php echo _("To configure gallery,") ?> 
<font size=+1>
<a href="<?php echo $GALLERY_BASEDIR ?>setup/index.php"><?php echo _("Start the configuration wizard") ?></a>
</font>
</center>
<br>

<?php echo _("If you've finished your configuration but you're still seeing this page,") ?>
<?php echo _("that's because for safety's sake") ?>
<?php echo _("we don't let you run Gallery in an insecure mode.") ?>
<?php echo _("You need to switch to secure mode before you can use it.") ?>
<?php echo _("Here's how:") ?>

<p><center>
<?php echo configure("secure"); ?>
<p>
<?php echo _("Then just reload this page and all should be well.") ?>

<?php include($GALLERY_BASEDIR . "errors/configure_help.php"); ?>

</table>
</body>
</html>
