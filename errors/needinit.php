<?php
// $Id$
// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
                    !empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
                    !empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
            print _("Security violation") ."\n";
	            exit;
		    }
?>
<?php 
	require($GALLERY_BASEDIR . "errors/configure_instructions.php");
?>
<html>
<head>
  <title><?php echo _("Gallery is misconfigured") ?></title>
  <?php echo getStyleSheetLink() ?>
</head>
<body dir="<?php echo $gallery->direction ?>">

<div class="header" align="center"><?php echo _("Gallery is misconfigured") ?></div>

<p class="sitedesc">

<?php 	echo _("Gallery is not configured correctly.  There could be a variety of reasons for this.  The easiest way to fix this problem is to re-run the configuration wizard.") ."  ";
	echo _("First, put Gallery in configuration mode:") ?>
</p>
<p>
<?php echo configure("configure"); ?>
<p>
<?php echo sprintf(_("Then launch the %sconfiguration wizard%s"),
		'<a href="'.$GALLERY_BASEDIR . 'setup/index.php">', '</a>'); ?>
<?php include($GALLERY_BASEDIR . "errors/configure_help.php"); ?>


</center>
</body>
</html>
