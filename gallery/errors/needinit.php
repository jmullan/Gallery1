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
<?php 
	if (! (@include($GALLERY_BASEDIR . "ML_files/ML_config.php")) || ! $gallery->ML) {
                include ($GALLERY_BASEDIR ."setup/ML_wizard.php");
                exit;
        }
	require($GALLERY_BASEDIR . "errors/configure_instructions.php") ?>
<html>
<head>
  <title><?php echo _("Gallery is misconfigured") ?></title>
  <?php echo getStyleSheetLink() ?>
</head>
<body dir=<?php echo $gallery->direction ?>>
<?php include ($gallery->path ."ML_files/ML_info_addon.inc"); ?>
<center>
<span class="error"> <?php echo _("Uh oh!") ?> </span>
<p>
<center>
<table width=80%><tr><td>
<?php echo _("Gallery is not configured correctly.") ?>
<?php echo _("There could be a variety of reasons for this.") ?>
<?php echo _("The easiest way to fix this problem is to re-run the configuration wizard.") ?>
<?php echo _("First, put Gallery in configuration mode") ?>:
<p>
<?php echo configure("configure"); ?>
<p>
<?php echo _("Then launch the") ?> <a href="<?php echo $GALLERY_BASEDIR ?>setup/index.php"><?php echo _("configuration wizard") ?></a>.

<?php include($GALLERY_BASEDIR . "errors/configure_help.php"); ?>

</table>
</body>
</html>
