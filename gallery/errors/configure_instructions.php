<?php
// $Id$
function configure($cmd="configure") {
?>
<center>
 <table>
  <tr>
   <td>
    <code>
     <br> <b>Unix</b> <?php echo _("with shell access") ; ?>
     <br> % cd /<?php echo _("path") .'/'. _("to") .'/'. _("your") .'/'. _("gallery") ; ?>
     <br> % sh ./<?php echo $cmd ?>.sh
     <br>
     <br> <b>Unix</b> <?php echo _("with FTP access") ; ?>
     <br> ftp> chmod <?php echo configure_filemode($cmd) ?> .htaccess
     <br> ftp> chmod <?php echo configure_filemode($cmd) ?> config.php
     <br> ftp> chmod <?php echo configure_dirmode($cmd) ?> setup
     <br>
     <br> <b>Windows</b>
     <br> C:\> cd \<?php echo _("path") .'\\'. _("to") .'\\'. _("your") .'\\'. _("gallery") ; ?>
     <br> C:\> <?php echo $cmd ?>.bat
     <br>
     <br>
   </td>
  </tr>
 </table>
</center>
<?php
}

function configure_filemode($cmd = "configure") {
	if (!strcmp($cmd, "configure")) {
		return 777;
	} else {
		return 644;
	}
}

function configure_dirmode($cmd = "configure") {
	if (!strcmp($cmd, "configure")) {
		return 755;
	} else {
		return 0;
	}
}
?>
