<?php /* $Id$ */ ?>

<?php
$GALLERY_BASEDIR="../";
require($GALLERY_BASEDIR . "util.php");
if (getOS() == OS_WINDOWS) {
       include($GALLERY_BASEDIR . "platform/fs_win32.php");
       if (fs_file_exists("SECURE")) {
?>
You cannot access this file while gallery is in secure mode.
<?php
               exit;
       }
}
?>
<?php phpinfo() ?>
