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
<body dir=<?php echo $gallery->direction ?>>
<center>
<span class="title"> <?php echo _("Gallery: Configuration Mode") ?> </span>
<p>
<table width=80%><tr><td>
<br>
<center>
<?php echo sprintf(_("To configure gallery, %sStart the configuration wizard%s"),
		'<font size=+1> <a href="' . $GALLERY_BASEDIR . 'setup/index.php">', 
		'</a></font>') ?>
</center>
<br>

<?php echo _("If you've finished your configuration but you're still seeing this page, that's because for safety's sake we don't let you run Gallery in an insecure mode.") ;
	echo _("You need to switch to secure mode before you can use it.  Here's how:")
?>

<p><center>
<?php echo configure("secure"); ?>
<p>
<?php echo _("Then just reload this page and all should be well.") ?>

<?php include($GALLERY_BASEDIR . "errors/configure_help.php"); ?>

</table>
</body>
</html>
