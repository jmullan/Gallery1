<?php
// $Id$
?>
<?php 

	require(dirname(__FILE__) . "/configure_instructions.php") ;
	if (! defined("GALLERY_URL")) define ("GALLERY_URL","");
	doctype();
?>
<html>
<head>
  <title><?php echo _("Gallery needs Reconfiguration") ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>">

<center>
<p class="header"><?php echo _("Gallery needs Reconfiguration") ?></p>

<p class="sitedesc">
	<?php echo _("Your Gallery settings were configured with an older version of Gallery, and are out of date. Please re-run the Configuration Wizard! Here's how:") ?>
</p>

<p>
	<?php configure("configure"); ?>
</p>

<p>
<?php 
	echo sprintf(_("Then launch the %sConfiguration Wizard%s."),
		'<a href="'. $GALLERY_BASEDIR . 'setup/index.php">', '</a>') . ' ';
	
	include(dirname(__FILE__) . "/configure_help.php"); ?>
</p>
</center>
</body>
</html>
