<?
function configure($cmd="configure") {
?>
<center>
 <table>
  <tr>
   <td>
    <code>
     <br> <b>Unix</b> with shell access
     <br> % cd /path/to/your/gallery
     <br> % sh ./<?=$cmd?>.sh
     <br>
     <br> <b>Unix</b> with FTP access
     <br> ftp> chmod <?= configure_filemode($cmd) ?> .htaccess
     <br> ftp> chmod <?= configure_filemode($cmd) ?> config.php
     <br> ftp> chmod <?= configure_dirmode($cmd) ?> setup
     <br>
     <br> <b>Windows</b>
     <br> C:\> cd \path\to\your\gallery
     <br> C:\> <?=$cmd?>.bat
     <br>
     <br>
   </td>
  </tr>
 </table>
</center>
<?
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