<div class="g-sitedesc left">
<?php
echo sprintf(_("This script is designed to examine your %s installation to see if it is ok to be used by Gallery."), $app_name);
echo sprintf(_("You should run this script <b>after</b> you have run the config wizard, if you have had problems with your %s installation that the wizard did not detect."), $app_name)
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
	  <?php echo returnToConfig(); ?>
</div>

</body>
</html>
<?php
    exit;
} else {
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
  <td class="g-desc-cell"><?php echo _("Let us see if we can figure out what operating system you are using.") ?></td>
</tr>
<tr>
    <td class="g-desc-cell">
    <?php echo _("This is what your system reports") ?>:
      <p><b><?php passthru("uname -a"); ?></b></p>

      <?php echo _("This is the type of system on which PHP was compiled") ?>:
      <br><b><?php echo php_uname() ?></b>

      <p><?php echo _("Make sure that the values above make sense to you.") ?></p>

<?php
echo "\t\t\t". sprintf(_("Look for keywords like %s, %s, %s etc. in the output above."),
  '&quot;Linux&quot;', '&quot;Windows&quot;', '&quot;FreeBSD&quot;'
);
echo _("If both the attempts above failed, you should ask your ISP what operating system you are using.");
printf(_("You can check via %s, they can often tell you."),
  '<a href="http://www.netcraft.com/whats?host=' . $_SERVER['HTTP_HOST'] . '">Netcraft</a>'
) ;
?>
    </td>
</tr>
</table>


<table class="g-double-bottom-border-spacer" width="80%" cellspacing="0">
<tr>
    <td class="g-desc-cell">
      <?php printf(_("You told the config wizard that your %s binaries live here:"), $app_name) . "\n" ?>
      <b><?php echo $gallery->app->ImPath ?></b>
      <br><br>
      <?php printf(_("If that is not right (or if it is blank), re-run the configuration wizard and enter a location for %s."), $app_name); ?>
    </td>
</tr>
<?php

if (! inOpenBasedir($tpl->appPath)) {
	$note = sprintf(_("<b>Note:</b> Your %s directory (%s) is not in your open_basedir list %s"),
			$app_name,
			$tpl->appPath,
			'<ul>'.  ini_get('open_basedir') . '</ul>');

	$note .= _("The open_basedir list is specified in php.ini.") . "<br>";
	$note .= _("The result is, that we can't perform all of our basic checks on the files to make sure that they exist and they're executable.") ."\n";
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
    <td class="g-desc-cell" colspan="2"><?php echo sprintf(_("We are going to test each %s binary individually."), $app_name) ?></td>
</tr>
<?php
foreach ($results as $nr => $result) {
    echo "\n<tr>";
    echo "\n  ". '<td width="100%">' . _("Checking:"). ' <b>' . $result[0] . '</b></td>';
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
			echo sprintf(_("If you see an error above complaining about reading or writing to %s then this is likely a permission/configuration issue on your system.  If it mentions %s then it's because your system is configured with %s enabled."),
			  "<b>$debugfile</b>",
			  '<i>open_basedir</i>',
			  '<a href="http://www.php.net/manual/en/configuration.php#ini.open-basedir"> open_basedir</a>') ;
			echo "   ". sprintf(_("You should talk to your system administrator about this, or see the %sGallery Help Page%s."),
			  '<a href="http://gallery.sourceforge.net/help.php">',
			  '</a>');

?>
      <p><?php echo sprintf(_("For other errors, please refer to the list of possible responses in %s to get more information."), '<a href="http://gallery.sourceforge.net/faq.php">FAQ</a> C.2'); ?>
      </p>
    </td>
</tr>
</table>

</div>
	
<div class="center">
	  <?php echo returnToConfig(); ?>
</div>

<br>

</body>
</html>