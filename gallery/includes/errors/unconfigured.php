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
<p class="header"><?php echo _("Gallery has not been configured!") ?></p>

<p class="sitedesc">
<?php
    echo _("Gallery must be configured before you can use it.");
?>
</p>
<table class="sitedesc" style="text-align:left">
<tr>
	<td><?php echo _("1."); ?></td>
	<td><?php echo _("Create an empty file .htaccess and an empty file config.php in your Gallery folder."); ?></td>
</tr>
<tr>
	<td><?php echo _("2."); ?></td>
	<td><?php echo _("Create an albums folder for your pictures and movies. This folder can be anywhere in your webspace."); ?></td>
</tr>
<tr>
	<td colspan="2" class="emphasis"><?php echo _("Make sure that both files and the folder are read and writeable for your webserver !"); ?></td>
</tr>
</table>

<p>
<?php
    echo sprintf(_("Then start the %sConfiguration Wizard%s."),
	'<a href="'. makeGalleryUrl('setup/index.php') .'">', '</a>');
    echo '<br>';
    include(dirname(__FILE__) . "/configure_help.php");
?>
</p>
</div>
<?php
    echo sprintf(_("%sNote:%s When you get an 'error 500' when accessing the config wizard, try removing the .htaccess file the setup folder."), '<b>', '</b>');
    echo "\n<br>";
    echo gallery_validation_link('index.php', true);
?>
</body>
</html>
