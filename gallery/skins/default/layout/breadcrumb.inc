<?php
// $Id$
global $breadcrumb, $navigator;
// If the border color is not passed in, we do a black/white combo
if ($breadcrumb["bordercolor"]) {
	$borderIn = $breadcrumb["bordercolor"];
} else {
	$borderIn = "#FFFFFF";
}
$pixelImage = "<img src=\"" . getImagePath('pixel_trans.gif') . "\" width=\"1\" height=\"1\">";

?>

<?php 
if (!isset($navigator)) {
	$navigator["fullWidth"] = 100;
	$navigator["widthUnits"] = "%";
}
?>
<table width="<?php echo $navigator["fullWidth"] . $navigator["widthUnits"] ?>" border="0" cellspacing="0" cellpadding="0" class="modnavboxmid">
<?php
if ($breadcrumb["top"]) {
}
?>
  <tr> 
    <td align="right" width="3000" height="18">
<?php
$i = 0;
while (isset($breadcrumb["text"][$i])) {
	if ($i > 0) {
		echo "<img src=\"" . getImagePath('nav_home.gif') . "\" width=13 height=11>";
	}
	echo "&nbsp;".$breadcrumb["text"][$i]."&nbsp;";
	$i++;
}
?>
      <img src="<?php echo getImagePath('nav_home.gif') ?>" width="13" height="11">&nbsp;
    </td> 
  </tr>
<?php
if (!$breadcrumb["top"] || !empty($breadcrumb["bottom"])) {
}
?>
</table>    
