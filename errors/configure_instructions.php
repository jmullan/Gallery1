<?php
// $Id$
function configure($cmd="configure") {
?>
<center>
 <table>
  <tr>
   <td>
    <code>
     <br> <?php echo sprintf(_("%s with shell access"), '<b>Unix</b>') ?>
     <?php $pathstring = _("/path/to/your/gallery") ?>
     <br> % cd <?php echo $pathstring ?>
     <br> % sh ./<?php echo $cmd ?>.sh
     <br>
     <br> <?php echo sprintf(_("%s with FTP access"), '<b>Unix</b>') ?>
     <br> ftp> chmod <?php echo configure_filemode($cmd) ?> .htaccess
     <br> ftp> chmod <?php echo configure_filemode($cmd) ?> config.php
     <br> ftp> chmod <?php echo configure_dirmode($cmd) ?> setup
     <br>
     <br> <b>Windows</b>
     <br> C:\> cd <?php echo strtr($pathstring, '/', '\\') ?>
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
		return 666;
	} else {
		return 644;
	}
}

function configure_dirmode($cmd = "configure") {
	if (!strcmp($cmd, "configure")) {
		return 755;
	} else {
		return 700;
	}
}
?>
