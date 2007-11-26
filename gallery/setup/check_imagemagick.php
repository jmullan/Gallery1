<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2007 Bharat Mediratta
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * $Id$
 */
?>
<?php 
require_once(dirname(__FILE__) . '/init.php');

doctype();
?>
<html>
<head>
	<title><?php echo gTranslate('config', "Gallery ImageMagick Check") ?></title>
	<?php echo getStyleSheetLink() ?>
</head>
<body dir="<?php echo $gallery->direction ?>">
<?php configLogin(basename(__FILE__)); ?>
<?php $app_name='ImageMagick' ?>

<div class="header"><?php printf(gTranslate('config', "Check %s"), $app_name) ?></div>

<div class="sitedesc">
<?php 
	printf(gTranslate('config', "This script is designed to examine your %s installation to see if it is ok to be used by Gallery."), $app_name);
	printf(gTranslate('config', "You should run this script <b>after</b> you have run the config wizard, if you have had problems with your %s installation that the wizard did not detect."), $app_name)
?>
</div>

<br>
<table class="inner" width="100%">
<tr>
  <td class="desc">
    <?php echo gTranslate('config', "Loading configuration files.  If you see an error here, it is probably because you have not successfully run the config wizard.") ?>
  </td>
<?php
if (gallerySanityCheck() != NULL) {
?>
</tr>
<tr>
  <td class="errorlong"><?php echo gTranslate('config', "It seems that you did not configure your GALLERY. Please run and finish the configuration wizard.") ?></td>
</tr>
</table>

<p><?php echo returnToConfig(); ?></p>

</body>
</html>
<?php
    exit;
} else {
    require(GALLERY_BASE . '/config.php');
?>
			<td class="Success"><?php echo gTranslate('config', "OK") ?></td>
		</tr>
		</table>
<?php } ?>

<table class="inner" width="100%">	
<tr>
  <td class="desc"><?php echo gTranslate('config', "Let us see if we can figure out what operating system you are using.") ?></td>
</tr>
<tr>
    <td class="desc">
    <?php echo gTranslate('config', "This is what your system reports") ?>:
      <p><b><?php passthru("uname -a"); ?></b></p>

      <p><?php echo gTranslate('config', "This is the type of system on which PHP was compiled") ?>:</p>
      <p><b><?php echo php_uname() ?></b></p>
      <p><?php echo gTranslate('config', "Make sure that the values above make sense to you.") ?></p>
			
      <p>
<?php 
echo "\t\t\t". sprintf(gTranslate('config', "Look for keywords like %s, %s, %s etc. in the output above."), 
  '&quot;Linux&quot;', '&quot;Windows&quot;', '&quot;FreeBSD&quot;'
);
echo gTranslate('config', "If both the attempts above failed, you should ask your ISP what operating system you are using.");
printf(gTranslate('config', "You can check via %s, they can often tell you."),
  '<a href="http://www.netcraft.com/whats?host=' . $_SERVER['HTTP_HOST'] . '">Netcraft</a>'
) ;
?>
		</p>
    </td>
</tr>
</table>

<table class="inner" width="100%">
<tr>
    <td class="desc">
      <?php printf(gTranslate('config', "You told the config wizard that your %s binaries live here:"), $app_name) . "\n" ?>
      <div style="padding: 20px;" class="emphasis"><?php echo $gallery->app->ImPath ?></div>
      <?php printf(gTranslate('config', "If that is not right (or if it is blank), re-run the configuration wizard and enter a location for %s."), $app_name) . "\n"; ?>
    </td>
</tr>
<?php
$debugfile = tempnam($gallery->app->tmpDir, "gallerydbg");

if (! inOpenBasedir($gallery->app->ImPath)) {
?>
<tr>
    <td class="warningpct" width="100%"><?php printf(gTranslate('config', "<b>Note:</b> Your %s directory (%s) is not in your open_basedir list %s"), 
			$app_name,
			$gallery->app->ImPath,
			'<ul>'.  ini_get('open_basedir') . '</ul>');
			echo gTranslate('config', "The open_basedir list is specified in php.ini.") . "<br>";
						echo gTranslate('config', "The result is, that we can't perform all of our basic checks on the files to make sure that they exist and they're executable.") ."\n"; ?>
    </td>
</tr>
<?php	} ?>
</table>

<table class="inner" width="100%">
<tr>
    <td class="desc" colspan="2"><?php printf(gTranslate('config', "We are going to test each %s binary individually."), $app_name) ?></td>
</tr>
<?php

$binaries = array('identify' => 'mandatory', 'convert' => 'mandatory', 'composite' => 'optional');

foreach ($binaries as $bin => $priority) {
    $result = checkImageMagick($bin);
    echo "\n<tr>";
    echo "\n  ". '<td class="desc" width="100%">' . gTranslate('config', "Checking:"). ' <b>' . $result[0] . '</b></td>';
    if (isset($result['error'])) {
        $class = ($priority == 'mandatory') ? 'errorpct' : 'warningpct';
        echo "\n  ". '<td style="white-space:nowrap;" class="'. $class .'">'. $result['error'] . '</td>';
    } else {
        echo "\n  ". '<td style="white-space:nowrap;" class="successpct">'. $result['ok'] . '</td>';
    }
    echo "\n</tr>";
}

if (fs_file_exists($debugfile)) {
    fs_unlink($debugfile);
}
?>
</table>

<table class="inner" width="100%">
<tr>
    <td class="desc"><?php 
			printf(gTranslate('config', "If you see an error above complaining about reading or writing to %s then this is likely a permission/configuration issue on your system.  If it mentions %s then it's because your system is configured with %s enabled."),
			  "<b>$debugfile</b>",
			  '<i>open_basedir</i>',
			  '<a href="http://www.php.net/manual/en/configuration.php#ini.open-basedir"> open_basedir</a>') ;
			echo "   ". sprintf(gTranslate('config', "You should talk to your system administrator about this, or see the %sGallery Help Page%s."),
			  '<a href="http://gallery.sourceforge.net/help.php">',
			  '</a>');

?>
      <p><?php printf(gTranslate('config', "For other errors, please refer to the list of possible responses in %s to get more information."), '<a href="http://gallery.sourceforge.net/faq.php">FAQ</a> C.2'); ?>
      </p>
    </td>
</tr>
</table>

<p class="inner" align="center"><?php echo returnToConfig(); ?></p>

</body>
</html>