<?php
// $Id$
global $breadcrumb, $navigator, $gallery;
// If the border color is not passed in, we do a black/white combo

if ($breadcrumb["bordercolor"]) {
	$borderIn = $breadcrumb["bordercolor"];
} else {
	$borderIn = "#FFFFFF";
}
$pixelImage = '<img src="' . getImagePath('pixel_trans.gif') .'" width="1" height="1" alt="transpixel">';

global $navigator, $adminbox, $adminText;

if (!isset($navigator)) {
	$navigator["fullWidth"] = 100;
	$navigator["widthUnits"] = "%";
}

if (!isset($navigator) && !isset($adminbox) && !isset($adminText)) {
	$style="border-color: $borderIn; border-width:1px; border-style: solid;";
} else {
	$style='';
}
?>
<table style="<?php echo $style; ?>" width="<?php echo $navigator["fullWidth"] . $navigator["widthUnits"] ?>" border="0" cellspacing="0" cellpadding="0" class="bread">
<tr> 
<?php
	if ($gallery->user->isLoggedIn()) {
		$name = $gallery->user->getFullName();
		if (!$name) {
			$name = $gallery->user->getUsername();
		}
		echo "\t". '<td style="padding-left:5px;" class="bread" height="18">'. _("Logged in as:") .' '. $name .'</td>';
	}
?>
	
	<td class="bread" height="18" align="right">
<?php
for ($i = 0; isset($breadcrumb["text"][$i]); $i++) {
	echo "\t\t&nbsp;".$breadcrumb["text"][$i]."&nbsp;\n";
}
?>
	</td> 
</tr>
</table>  
