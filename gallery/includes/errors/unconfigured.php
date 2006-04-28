<?php
// $Id$
?>
<?php 
    doctype();
?>
<html>
<head>
  <title><?php echo gTranslate('core', "Gallery Configuration Error") ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>">

<div align="center">
<p class="g-header"><span class="g-pagetitle"><?php echo gTranslate('core', "Gallery has not been configured!") ?></span></p>

  <div class="g-sitedesc">
<?php 
    echo gTranslate('core', "Gallery must be configured before you can use it.");
?>

  <table class="g-sitedesc">
  <tr>
	<td><?php echo gTranslate('core', "1."); ?></td>
	<td><?php echo gTranslate('core', "Create an empty file .htaccess and an empty file config.php"); ?></td>
  </tr>
  <tr>
	<td><?php echo gTranslate('core', "2."); ?></td>
	<td><?php echo gTranslate('core', "Create an albums folder for your pictures and movies."); ?></td>
  </tr>
  <tr>
	<td colspan="2" class="emphasis"><?php echo gTranslate('core', "Make sure that both files and the folder are read and writeable for your webserver !"); ?></td>
  </tr>
  </table>

<?php 
    echo sprintf(gTranslate('core', "Then start the %sConfiguration Wizard%s."), 
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
