<?php
// $Id$
?>
<?php 
	require(dirname(__FILE__) . '/configure_instructions.php');
	if (! defined("GALLERY_URL")) define ("GALLERY_URL","");
	doctype();
?>
<html>
<head>
  <title><?php echo _("Gallery in Configuration Mode") ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>">

<center>
<div class="header"><?php echo _("Gallery: Configuration Mode") ?></div>

<p class="sitedesc">
<?php 
	echo sprintf(_("To configure gallery, run the %sConfiguration Wizard%s."),
		'<font size="+1"><a href="'. GALLERY_URL . 'setup/index.php">', 
		'</a></font>'); 
?>
</p>
<p>
<?php 
	echo _("If you've finished your configuration but you're still seeing this page, that's because for safety's sake we don't let you run Gallery in an insecure mode.") ;
	echo ' ' . _("You need to switch to secure mode before you can use it.  Here's how:")
?>
</p>

<p>
	<?php echo configure("secure"); ?>
<p>
<?php 
	echo _("Then just reload this page and all should be well.");
	include(dirname(__FILE__) . '/configure_help.php');
?>
</p>
</center>
</body>
</html>
