<?
function configure($cmd="configure.sh") {
	global $HTTP_SERVER_VARS;
?>
<center>
 <table>
  <tr>
   <td>
    <code>
     <br> % cd <?=dirname($HTTP_SERVER_VARS["PATH_TRANSLATED"])?>
     <br> % sh ./<?=$cmd?>
   </td>
  </tr>
 </table>
</center>
<?
}
?>