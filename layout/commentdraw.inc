<?php /* $Id$ */ ?>
<?php 

	global $commentdraw, $i, $index, $id;

// If the border color is not passed in, we do a black/white combo
if ($commentdraw["bordercolor"]) {
        $borderIn = $commentdraw["bordercolor"];
} else {
        $borderIn = "#FFFFFF";
}

$pixelImage = "<img src=\"" . getImagePath('pixel_trans.gif') . "\" width=\"1\" height=\"1\" alt=\"\">";

?>
<!-- Begin Comment -->
<table width="75%" align="center" border="0" class="commentbox" cellpadding="0" cellspacing="0">
<tr>
	<td class="commentboxhead"><?php echo _("From:") ?></td> 
	<td class="commentboxhead"><b><?php echo wordwrap($commentdraw["name"], 50, " ", 1) ?></b>
<?php
if ($gallery->user->isAdmin()) {
	echo "@ &nbsp;";
	echo $commentdraw["IPNumber"];
}
?>	</td>
	<td class="commentboxhead">(<?php echo $commentdraw["datePosted"] ?>)</td>
</tr>
<tr>
	<td colspan="3"><?php echo wordwrap($commentdraw["comment"], 100, " ", 1) ?></td>
</tr>

<?php
if ($gallery->user->canWriteToAlbum($gallery->album)) {
	echo "\n<tr>";
	echo "\n\t". '<td colspan="3" align="center" class="commentboxfooter">';
        $url = doCommand('delete-comment', 
				array('index'=> $commentdraw['index'], 
					'comment_index' => $i,
					'albumName' => $commentdraw['albumName'])
			);
	echo '<a href="#" onclick="javascript:' . popup($url,1) . '">['. _("delete comment") . ']</a>';
	echo "\n\t</td>";
	echo "\n</tr>";
}
?>
</table>
<br>
<!-- End Comment -->
