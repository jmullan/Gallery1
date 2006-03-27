<?php /* $Id$ */ ?>
<?php global $embeddedAlbum, $id, $index ;
if(!$embeddedAlbum == 1) {
        $url = "add_comment.php?set_albumName={$gallery->album->fields['name']}&id=$id";
?>
                </td>
        </tr>
        </table>

<?php
	if ($gallery->user->canAddComments($gallery->album)) {
	    echo "\n". '<input type="button" onClick="javascript:'. popup($url) .'" value="'. _("add comment") .'">';
	}

	if ($gallery->user->canWriteToAlbum($gallery->album)) {
		echo "\n". '<input type="submit" value="'. _("Delete marked comments") .'">';
	}
	?>
	</form>
<br>
	</td>
	<td width="20">&nbsp;</td>
<?php } ?>
</tr>
</table>
<br>
