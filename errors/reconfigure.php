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
<span class="title"> <?php echo _("Gallery needs Reconfiguration") ?> </span>
<p>
<table width=80%><tr><td>
<?php echo _("Your Gallery configuration was created using the config wizard from an older version of Gallery.  It is out of date.  Please re-run the configuration wizard!") ?>
<?php echo _("In a shell do this") ?>:
<p>
<?php configure("configure"); ?>
<p>
<?php echo sprintf(_("Then launch the %sconfiguration wizard%s"),
		'<a href="'. $GALLERY_BASEDIR . 'setup/index.php">', '</a>') ?>
  <?php include($GALLERY_BASEDIR . "errors/configure_help.php"); ?>

</table>
</center>
</body>
</html>
