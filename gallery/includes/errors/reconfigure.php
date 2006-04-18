<?php
// $Id$
?>
<?php 
    doctype();
?>
<html>
<head>
  <title><?php echo _("Gallery needs Reconfiguration") ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>">

<div class="g-header" align="center">
    <span class="g-pagetitle"><?php echo _("Gallery needs Reconfiguration") ?></span>
</div>

<div class="g-sitedesc" align="center">
    <?php echo _("Your Gallery settings were configured with an older version of Gallery, and are out of date. Please re-run the Configuration Wizard!") ?>

<p>
<?php 
    echo sprintf(_("Launch the %sConfiguration Wizard%s."),
	'<a href="'. makeGalleryUrl('setup/index.php') .'">', '</a>');
    echo '<br>';	
    include(dirname(__FILE__) . "/configure_help.php"); ?>
</p>
</div>
<?php
    echo gallery_validation_link('index.php', true);
?>
</body>
</html>
