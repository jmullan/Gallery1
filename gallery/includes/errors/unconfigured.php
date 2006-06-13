<?php
// $Id$
?>
<?php
    printPopupStart(gTranslate('core', "Gallery Configuration Error"), gTranslate('core', "Gallery has not been configured!"));
?>
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
	<td colspan="2" class="g-emphasis"><?php echo gTranslate('core', "Make sure that both files and the folder are read and writeable for your webserver !"); ?></td>
  </tr>
  </table>

  </div>
  <br>
<?php 
    echo sprintf(gTranslate('core', "Then start the %sConfiguration Wizard%s."), 
	'<a href="'. makeGalleryUrl('setup/index.php') .'">', '</a>'); 
    echo '<br>';
    include(dirname(__FILE__) . "/configure_help.php");
?>
  </div>

<br>
<?php 
    echo gallery_validation_link('index.php', true);
?>
</body>
</html>
