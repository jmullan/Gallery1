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
  <title><?php echo _("Gallery Configuration Error") ?></title>
  <?php echo getStyleSheetLink() ?>
</head>
<body dir="<?php echo $gallery->direction ?>">

<center>
<p class="header"><?php echo _("Gallery has not been configured!") ?></p>

<p class="sitedesc">
<?php 
	echo _("Gallery must be configured before you can use it.  First, you must put it into configuration mode.  Here's how.");
	echo configure("configure"); 
?>
</p>

<p>
<?php echo sprintf(_("And then start the %sConfiguration Wizard%s."), 
		'<a href="'. $GALLERY_BASEDIR . 'setup/index.php">', '</a>'); 
	print "  ";
	include($GALLERY_BASEDIR . "errors/configure_help.php"); ?>
</p>
</center>
</body>
</html>
