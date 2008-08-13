<?php
// $Id$

global $breadcrumb, $navigator, $gallery;
global $navigator, $adminbox, $adminText;

if(!includeTemplate('breadcrumb.tpl')
    && (!empty($breadcrumb['text']) || $gallery->user->isLoggedIn()))
 {

	/* If the border color is not passed in, we do a black/white combo */
	if (isset($breadcrumb["bordercolor"])) {
		$borderIn = $breadcrumb["bordercolor"];
	}
	else {
		$borderIn = "#FFFFFF";
	}

	if (!isset($navigator) && !isset($adminbox) && !isset($adminText)) {
		$style = "border-color: $borderIn; border-width:1px; border-style: solid;";
	}
	else {
		$style = '';
	}

	if (!isset($navigator)) {
		$navigator["fullWidth"] = 100;
		$navigator["widthUnits"] = "%";
	}
?>

<table style="<?php echo $style; ?>" width="<?php echo $navigator["fullWidth"] . $navigator["widthUnits"] ?>" border="0" cellspacing="0" cellpadding="0" class="modnavboxmid">
<tr>
	<?php
	if ($gallery->user->isLoggedIn()) {
		$name = $gallery->user->getFullName();
		if (!$name) {
			$name = $gallery->user->getUsername();
		}
		echo "\t". '<td class="bread" height="18" style="text-align:'. langLeft() .'; padding-left: 5px; padding-right: 5px;">'. gTranslate('core', "Logged in as:") .' '. $name .'</td>';
	}
	?>

	<td class="bread" style="text-align:<?php echo langRight(); ?>; padding-left: 5px; padding-right: 5px;">
	<?php
	if (isset($breadcrumb['text'])) {
		foreach($breadcrumb["text"] as $nr => $text) {
			echo "\t\t$text\n";
		}
	}
	?>
	</td>
</tr>
</table>

<?php
}
?>
