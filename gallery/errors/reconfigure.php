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

	require($GALLERY_BASEDIR . "errors/configure_instructions.php") ;
?>
<html>
<head>
  <title><?php echo _("Gallery needs Reconfiguration") ?></title>
  <?php echo getStyleSheetLink() ?>
</head>
<body dir="<?php echo $gallery->direction ?>">

<center>
<p class="header"><?php echo _("Gallery needs Reconfiguration") ?></p>

<p class="sitedesc">
	<?php echo _("Your Gallery configuration was created using the config wizard from an older version of Gallery.  It is out of date.  Please re-run the configuration wizard!") ?>
	<?php echo _("In a shell do this") ?>:
</p>

<p>
<?php configure("configure"); ?>
</p>

<p>
<?php echo sprintf(_("Then launch the %sconfiguration wizard%s"),
		'<a href="'. $GALLERY_BASEDIR . 'setup/index.php">', '</a>') ?>
  <?php include($GALLERY_BASEDIR . "errors/configure_help.php"); ?>
</p>
</center>
</body>
</html>
