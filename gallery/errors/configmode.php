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
  <title><?php echo _("Gallery in Configuration Mode") ?></title>
  <?php echo getStyleSheetLink() ?>
</head>
<body dir="<?php echo $gallery->direction ?>">

<center>
<div class="header"><?php echo _("Gallery: Configuration Mode") ?></div>

<p class="sitedesc">
<?php echo sprintf(_("To configure gallery, run the %sConfiguration Wizard%s."),
		'<font size=+1> <a href="' . $GALLERY_BASEDIR . 'setup/index.php">', 
		'</a></font>') ?>
</p>

<p>

<?php echo _("If you've finished your configuration but you're still seeing this page, that's because for safety's sake we don't let you run Gallery in an insecure mode.") ;
	echo ' ' . _("You need to switch to secure mode before you can use it.  Here's how:")
?>
</p>

<p>
<?php echo configure("secure"); ?>
<p>
<?php echo _("Then just reload this page and all should be well.") ?>

<?php include($GALLERY_BASEDIR . "errors/configure_help.php"); ?>

</p>
</center>
</body>
</html>
