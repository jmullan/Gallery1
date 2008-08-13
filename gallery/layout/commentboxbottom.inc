<?php
/* $Id$ */

global $embeddedAlbum, $id, $index ;

if(!$embeddedAlbum == 1) {
        $url = "add_comment.php?set_albumName={$gallery->album->fields['name']}&id=$id";
?>
                </td>
        </tr>
        </table>

<?php
	if ($gallery->user->canAddComments($gallery->album)) {
		echo gButton('add', gTranslate('core', "add comment"), popup($url));
	}

	if ($gallery->user->canWriteToAlbum($gallery->album)) {
		echo gSubmit('submit', gTranslate('core', "Delete marked comments"));
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
