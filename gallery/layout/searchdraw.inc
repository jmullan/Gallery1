<?php
/* $Id$ */

global $searchdraw;

// If the border color is not passed in, we do a black/white combo
if ($searchdraw["bordercolor"]) {
	$borderIn = $searchdraw["bordercolor"];
}
else {
	$borderIn = "#FFFFFF";
}

$pixelImage = "<img src=\"" . getImagePath('pixel_trans.gif') . "\" width=\"0\" height=\"0\" alt=\"\">";

?>

	<tr><td colspan="3"><hr width="100%"></td></tr>
	<tr>
		<td width="100">
		<a href="<?php echo $searchdraw["photoURL"] ?>"><?php echo $searchdraw["photolink"] ?></a>
		</td>
		<td width="10"><?php echo $pixelImage ?></td>
		<?php
		if (!empty($searchdraw['Text'])) {
			echo "\n<td valign=\"top\" class=\"albumdesc\">";
			foreach ($searchdraw['Text'] as $text) {
				echo $text;
			}
			echo "\n</td>";
		}
		else {
			echo "\n<td>&nspp;</td>";
		}
		?>
	</tr>
	<tr><td colspan="3">&nbsp;</td></tr>
