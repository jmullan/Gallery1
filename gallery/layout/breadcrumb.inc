<?php
// $Id$
global $breadcrumb, $navigator;
// If the border color is not passed in, we do a black/white combo
if ($breadcrumb["bordercolor"]) {
	$borderIn = $breadcrumb["bordercolor"];
} else {
	$borderIn = "#FFFFFF";
}
$pixelImage = "<img src=\"" . getImagePath('pixel_trans.gif') . "\" width=\"1\" height=\"1\" alt=\"\">";

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
    <td align="right" width="3000" height="18" class="bread">
<?php

for ($i = 0; isset($breadcrumb["text"][$i]); $i++) {
	echo "&nbsp;".$breadcrumb["text"][$i]."&nbsp;";
}
?>
    </td> 
  </tr>
<?php
if (!$breadcrumb["top"] || !empty($breadcrumb["bottom"])) {
}
?>
</table>    
