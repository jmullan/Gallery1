<?php
// $Id$
?>
<?php 
	if (! defined("GALLERY_URL")) define ("GALLERY_URL","");
	doctype();
?>
<html>
<head>
  <title><?php echo _("Gallery Configuration Error") ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>">

<center>
<p class="header"><?php echo _("Gallery has not been configured!") ?></p>

<p class="sitedesc">
<?php 
	echo _("Gallery must be configured before you can use it.");
?>
</p>
<table>
<tr>
	<td><?php echo _("Create an empty file .htaccess and an empty file config.php");?></td>
</tr>
<tr>
	<td><?php echo _("Make sure that both files are read and writeable for your webserver !"); ?></td>
</tr>
</table>

<p>
<?php echo sprintf(_("Then start the %sConfiguration Wizard%s."), 
		'<a href="'. GALLERY_URL . 'setup/index.php">', '</a>'); 
	print "<br>";
	include(dirname(__FILE__) . "/configure_help.php"); ?>
</p>
</center>
</body>
</html>
