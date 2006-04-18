<?php
// $Id$
?>
<?php 
    doctype();
?>
<html>
<head>
  <title><?php echo _("Gallery Configuration Error") ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>">

<div align="center">
<p class="g-header"><span class="g-pagetitle"><?php echo _("Gallery has not been configured!") ?></span></p>

  <div class="g-sitedesc">
<?php 
    echo _("Gallery must be configured before you can use it.");
?>

  <table class="g-sitedesc">
  <tr>
	<td><?php echo _("1."); ?></td>
	<td><?php echo _("Create an empty file .htaccess and an empty file config.php"); ?></td>
  </tr>
  <tr>
	<td><?php echo _("2."); ?></td>
	<td><?php echo _("Create an albums folder for your pictures and movies."); ?></td>
  </tr>
  <tr>
	<td colspan="2" class="emphasis"><?php echo _("Make sure that both files and the folder are read and writeable for your webserver !"); ?></td>
  </tr>
  </table>

<?php 
    echo sprintf(_("Then start the %sConfiguration Wizard%s."), 
	'<a href="'. makeGalleryUrl('setup/index.php') .'">', '</a>'); 
    echo '<br>';
    include(dirname(__FILE__) . "/configure_help.php");
?>
  </div>
</div>
<br>
<?php 
    echo gallery_validation_link('index.php', true);
?>
</body>
</html>
