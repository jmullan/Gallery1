<?
function configure($cmd="configure") {
?>
<center>
 <table>
  <tr>
   <td>
    <code>
     <br> <b>Unix</b>
     <br> % cd /path/to/your/gallery
     <br> % sh ./<?=$cmd?>.sh
     <br>
     <br> <b>Windows</b>
     <br> C:\> cd \path\to\your\gallery
     <br> C:\> <?=$cmd?>.bat
     <br>
   </td>
  </tr>
 </table>
</center>
<?
}
?>