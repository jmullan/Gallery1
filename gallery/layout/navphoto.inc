<?php /* $Id$ */ ?>
<?php
global $navigator;
// Calculate the beginning and ending of the navigator range
$begin = 0;
$navpage = sizeof($navigator["allIds"]) - 1;
while ($navpage > 0) {
	if (!strcmp($navigator["allIds"][$navpage], $navigator["id"])) {
		break;
	}
	$navpage--;
}
$navcount = sizeof($navigator["allIds"]);

// If the border color is not passed in, we do a white one
if ($navigator["bordercolor"]) {
	$borderIn = $navigator["bordercolor"];
} else {
	$borderIn = "#FFFFFF";
}

$pixelImage = "<img src=\"" . getImagePath('pixel_trans.gif') . "\" width=\"0\" height=\"0\" alt=\"\">";
?>

<table width="<?php echo $navigator["fullWidth"] . $navigator["widthUnits"] ?>" border="0" cellspacing="0" cellpadding="0" class="modnavbox">
  <tr> 

<?php      
#-- 'first' button cell ---
if ($navpage == 0) {
?>
    <td align="center" width="27" height="18">&nbsp;</td> 
    <td width="0" height="18"><?php echo $pixelImage ?></td>
<?php
} else {
	$firstPage = $navigator["allIds"][0];
	$firstAlbumName = "";
?>
    <td align="center" width="27" height="18" class="borderright"> 
      <a href="<?php echo makeAlbumUrl($gallery->session->albumName, $firstPage) ?>"><img src="<?php echo ($gallery->direction == "ltr") ? getImagePath('nav_first.gif') : getImagePath('nav_last.gif') ?>" border="0" alt="<?php echo _("First Photo") ?>" title="<?php echo _("First Photo") ?>"></a>
      </td>
    <td width="0" height="18"><?php echo $pixelImage ?></td>
<?php
} 
?>
<?php
#-- 'previous' button cell ---
$prevPage = $navpage - 1;
if ($prevPage >= 0) {
	$prevPage = $navigator["allIds"][$prevPage];
	$prevAlbumName = "";
?>
    <td align="center" width="72" height="18" class="borderright">
      <a href="<?php echo makeAlbumUrl($gallery->session->albumName, $prevPage) ?>"><img src="<?php echo ($gallery->direction == "ltr") ? getImagePath('nav_prev.gif') : getImagePath('nav_next.gif') ?>" border="0" alt="<?php echo _("Previous Photo") ?>" title="<?php echo _("Previous Photo") ?>"></a>
      </td>
    <td width="0" height="18"><?php echo $pixelImage ?></td>
<?php
} else {
?>
    <td align="center" width="72" height="18">&nbsp;</td>
    <td width="0" height="18"><?php echo $pixelImage ?></td>
<?php
}
?>
    <td align="center" width="3000" height="18">   
<?php
#-- 'page numbers' cell ---                  
echo "<span class=\"nav\">";
echo sprintf(_("%d of %d"), $navpage+1, $navcount);
echo "</span>";
?>
    </td>
<?php
#-- 'next' button cell ---
if ($navpage+1 != $navcount) {
	$nextPage = $navigator["allIds"][$navpage+1];
	$nextAlbumName = "";
?>
    <td width="0" height="18"><?php echo $pixelImage ?></td>
    <td align="center" width="72" height="18" class="borderleft">
	<a href="<?php echo makeAlbumUrl($gallery->session->albumName, $nextPage) ?>"><img src="<?php echo ($gallery->direction == "ltr") ? getImagePath('nav_next.gif') : getImagePath('nav_prev.gif') ?>" border="0" alt="<?php echo _("Next Photo") ?>" title="<?php echo _("Next Photo") ?>"></a>
      </td>
<?php
} else {   
?>
    <td align="center" width="72" height="18">&nbsp;</td>
    <td width="0" height="18"><?php echo $pixelImage ?></td>
<?php
} 
?>
<?php
#-- 'last' button ---
if ($navpage+1 == $navcount) {
?>
    <td width="0" height="18"><?php echo $pixelImage ?></td>
    <td align="center" width="27" height="18">&nbsp;</td>
<?php
} else {        
	$lastPage = $navigator["allIds"][$navcount-1];
	$lastAlbumName = "";
?>
    <td width="0" height="18"><?php echo $pixelImage ?></td>
    <td align="center" width="27" height="18" class="borderleft">
      <a href="<?php echo makeAlbumUrl($gallery->session->albumName, $lastPage) ?>"><img src="<?php echo ($gallery->direction == "ltr") ? getImagePath('nav_last.gif') : getImagePath('nav_first.gif') ?>" border="0" alt="<?php echo _("Last Photo") ?>" title="<?php echo _("Last Photo") ?>"></a>
    </td>
<?php
} 
?>
  </tr>
</table>
