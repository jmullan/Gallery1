<?php
// $Id$
?>
<?php
    doctype();
?>
<html>
<head>
  <title><?php echo gTranslate('core', "Gallery needs Reconfiguration") ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>">

<div align="center">
<p class="header"><?php echo gTranslate('core', "Gallery needs Reconfiguration") ?></p>

<p class="sitedesc">
    <?php echo gTranslate('core', "Your Gallery settings were configured with an older version of Gallery, and are out of date. Please re-run the Configuration Wizard!") ?>
</p>

<p>
<?php
    echo sprintf(gTranslate('core', "Launch the %sConfiguration Wizard%s."),
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
