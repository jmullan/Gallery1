<? if(!$embeddedAlbum == 1)
{
?>
</td>
</tr>
</table>
<?
        $url = "add_comment.php?set_albumName={$gallery->album->fields[name]}&index=$index";
        $buf = "<span class=editlink>";
        $buf .= '<a href="#" onClick="' . popup($url) . '">[add comment]</a>';
        $buf .= "</span>";
        echo "<br>$buf<br>";
?>
<br>
</td>
<td width="20">&nbsp;</td>
<?}?>
</tr>
</table>
<br>
