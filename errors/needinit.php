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
	require($GALLERY_BASEDIR . "errors/configure_instructions.php") ?>
<html>
<head>
  <title><?php echo _("Gallery is misconfigured") ?></title>
  <?php echo getStyleSheetLink() ?>
</head>
<body dir=<?php echo $gallery->direction ?>>
<center>
<span class="error"> <?php echo _("Uh oh!") ?> </span>
<p>
<center>
<table width=80%><tr><td>
<?php echo _("Gallery is not configured correctly.  There could be a variety of reasons for this.  The easiest way to fix this problem is to re-run the configuration wizard.  First, put Gallery in configuration mode:") ?>
<p>
<?php echo configure("configure"); ?>
<p>
<?php echo sprintf(_("Then launch the %sconfiguration wizard%s"),
		'<a href="'.$GALLERY_BASEDIR . 'setup/index.php">', '</a>'); ?>
<?php include($GALLERY_BASEDIR . "errors/configure_help.php"); ?>

</table>
</body>
</html>
