<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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
 * $Id: im-netpbm-test.tpl 13820 2006-06-14 12:03:36Z jenst $
 */
?>
<div class="g-sitedesc left">
<?php
printf(gTranslate('config', "This script is designed to examine your %s installation to see if it is ok to be used by Gallery."), $app_name);
printf(gTranslate('config', "You should run this script <b>after</b> you have run the config wizard, if you have had problems with your %s installation that the wizard did not detect."), $app_name)
?>
</div>

<br>
<table class="g-double-bottom-border-spacer" width="80%" cellspacing="0">
<tr>
  <td class="g-desc-cell">
    <?php echo gTranslate('config', "Loading configuration files.  If you see an error here, it is probably because you have not successfully run the config wizard.") ?>
  </td>
<?php
if (gallerySanityCheck() != NULL) {
?>
</tr>
<tr>
  <td><?php echo infoBox(array(array(
  	'type' => 'error',
  	'text' => gTranslate('config', "It seems that you did not configure your GALLERY. Please run and finish the configuration wizard.")))); ?></td>
</tr>
</table>

</div>

<div class="center">
    <?php echo returnToDiag(); ?><?php echo returnToConfig(); ?>
</div>

</body>
</html>
<?php
    exit;
}
else {
    require(GALLERY_BASE . '/config.php');
?>
  <td><?php echo infoBox(array(array(
  	'type' => 'success',
  	'text' => gTranslate('config', "OK"))),'', false); ?></td>
</tr>
</table>
<?php } ?>

<table class="g-double-bottom-border-spacer" width="80%" cellspacing="0">
<tr>
  <td class="g-desc-cell"><?php echo gTranslate('config', "Let us see if we can figure out what operating system you are using.") ?></td>
</tr>
<tr>
    <td class="g-desc-cell">
    <?php echo gTranslate('config', "This is what your system reports") ?>:
      <p><b><?php passthru("uname -a"); ?></b></p>

      <?php echo gTranslate('config', "This is the type of system on which PHP was compiled") ?>:
      <br><b><?php echo php_uname() ?></b>

      <p><?php echo gTranslate('config', "Make sure that the values above make sense to you.") ?></p>

<?php
echo "\t\t\t". sprintf(gTranslate('config', "Look for keywords like %s, %s, %s etc. in the output above."),
  '&quot;Linux&quot;', '&quot;Windows&quot;', '&quot;FreeBSD&quot;'
);

echo gTranslate('config', "If both the attempts above failed, you should ask your ISP what operating system you are using.");

if(isXSSclean($_SERVER['HTTP_HOST'])) {
	$link = '<a href="http://www.netcraft.com/whats?host=' . $_SERVER['HTTP_HOST'] . '" target="_blank">Netcraft</a>';
}
else {
	$link = '<a href="http://www.netcraft.com" target="_blank">Netcraft</a>';
}

printf(gTranslate('config', "You can check via %s, they can often tell you."), $link);
?>
    </td>
</tr>
</table>


<table class="g-double-bottom-border-spacer" width="80%" cellspacing="0">
<tr>
    <td class="g-desc-cell">
      <?php printf(gTranslate('config', "You told the config wizard that your %s binaries live here:"), $app_name) . "\n" ?>
      <b><?php echo $gallery->app->ImPath ?></b>
      <br><br>
      <?php printf(gTranslate('config', "If that is not right (or if it is blank), re-run the configuration wizard and enter a location for %s."), $app_name); ?>
    </td>
</tr>
<?php

if (! inOpenBasedir($tpl->appPath)) {
	$note = sprintf(gTranslate('config', "<b>Note:</b> Your %s directory (%s) is not in your open_basedir list %s"),
			$app_name,
			$tpl->appPath,
			'<ul>'.  ini_get('open_basedir') . '</ul>');

	$note .= gTranslate('config', "The open_basedir list is specified in php.ini.") . "<br>";
	$note .= gTranslate('config', "The result is, that we can't perform all of our basic checks on the files to make sure that they exist and they're executable.") ."\n";
?>
<tr>
    <td width="100%"><?php
    echo infoBox(array(array(
  			'type' => 'information',
  			'text' => $note))
    );
    ?></td>
</tr>
<?php	} ?>
</table>

<table class="g-double-bottom-border-spacer" width="80%" cellspacing="0">
<tr>
    <td class="g-desc-cell" colspan="2"><?php echo sprintf(gTranslate('config', "We are going to test each %s binary individually."), $app_name) ?></td>
</tr>
<?php
foreach ($results as $nr => $result) {
    echo "\n<tr>";
    echo "\n  ". '<td width="100%">' . gTranslate('config', "Checking:"). ' <b>' . $result[0] . '</b></td>';
	echo "\n  ". '<td style="white-space:nowrap;">';
    if (isset($result['error'])) {
    	$priority = isset($priority) ? $priority : 'mandatory';
        $type = ($priority == 'mandatory') ? 'error' : 'warning';
        echo infoBox(array(array(
  			'type' => $type,
  			'text' => $result['error'])),'', false);
    }
    elseif (isset($result['warning'])) {
        echo infoBox(array(array(
  			'type' => 'warning',
  			'text' => $result['error'])),'', false);
    }
    else {
    	echo infoBox(array(array(
  			'type' => 'success',
  			'text' => $result['ok'])),'', false);
    }
	echo '</td>';
    echo "\n</tr>";
}

?>
</table>

<table class="g-double-bottom-border-spacer" width="80%" cellspacing="0">
<tr>
    <td class="g-desc-cell"><?php
			echo sprintf(gTranslate('config', "If you see an error above complaining about reading or writing to %s then this is likely a permission/configuration issue on your system.  If it mentions %s then it's because your system is configured with %s enabled."),
			  "<b>$debugfile</b>",
			  '<i>open_basedir</i>',
			  '<a href="http://www.php.net/manual/en/configuration.php#ini.open-basedir" target="_blank"> open_basedir</a>') ;
			echo "   ". sprintf(gTranslate('config', "You should talk to your system administrator about this, or see the %sGallery Help Page%s."),
			  '<a href="http://gallery.sourceforge.net/help.php" target="_blank">',
			  '</a>');

?>
      <p><?php echo sprintf(gTranslate('config', "For other errors, please refer to the list of possible responses in %s to get more information."), '<a href="http://gallery.sourceforge.net/faq.php">FAQ</a> C.2'); ?>
      </p>
    </td>
</tr>
</table>

</div>

<div class="center">
    <?php echo returnToDiag(); ?><?php echo returnToConfig(); ?>
</div>

<br>

</body>
</html>