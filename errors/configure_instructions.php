<?
function configure($cmd="configure.sh") {
	global $HTTP_SERVER_VARS, $HTTP_ENV_VARS;

	$tmp = $HTTP_SERVER_VARS["PATH_TRANSLATED"];
	if (!$tmp) {
		$tmp = $HTTP_ENV_VARS["PATH_TRANSLATED"];
	}
	if (!$tmp) {
		$tmp = getenv("SCRIPT_FILENAME");
	}
	$path = dirname(dirname($tmp));

?>
<center>
 <table>
  <tr>
   <td>
    <code>
     <br> % cd <?= $path ?>
     <br> % sh ./<?=$cmd?>
   </td>
  </tr>
 </table>
</center>
<?
}
?>