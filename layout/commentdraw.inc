<?php /* $Id$ */ ?>
<?php 

	global $commentdraw, $i, $index;

// If the border color is not passed in, we do a black/white combo
if ($commentdraw["bordercolor"]) {
        $borderIn = $commentdraw["bordercolor"];
} else {
        $borderIn = "#FFFFFF";
}

$pixelImage = "<img src=\"" . getImagePath('pixel_trans.gif') . "\" width=\"1\" height=\"1\" alt=\"\">";

?>
<!-- Begin Comment -->
<table width="50%" align="center" border="0" class="commentbox" cellpadding="0" cellspacing="0">
<tr>
	<td class="commentboxhead"><?php echo _("From:") ?></td> 
	<td class="commentboxhead"><b><?php echo $commentdraw["name"] ?></b>
<?php
if ($gallery->user->isAdmin()) {
	echo "@ &nbsp;";
	echo $commentdraw["IPNumber"];
}
?>	</td>
	<td class="commentboxhead">(<?php echo $commentdraw["datePosted"] ?>)</td>
</tr>
<tr>
	<td colspan="3"><?php echo $commentdraw["comment"] ?></td>
</tr>

<?php
if ($gallery->user->canWriteToAlbum($gallery->album)) {
	echo "\n<tr>";
	echo "\n\t". '<td colspan="3" align="center" class="commentboxfooter">';
	$url = "do_command.php?cmd=delete-comment&index=$index&comment_index=$i";
	echo '<a href="#" onClick="javascript:' . popup($url) . '">['. _("delete comment") . ']</a>';
	echo "\n\t</td>";
	echo "\n</tr>";
}
?>
</table>
<br>
<!-- End Comment -->
