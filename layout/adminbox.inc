<?php

// $Id$
// Note that this box is assumed to be attached to a navigator and uses the navigator's
// width parameters .

global $adminbox, $navigator;

// If the border color is not passed in, we do a black/white combo
if (isset ($adminbox["bordercolor"])) {
	$borderIn = $adminbox["bordercolor"];
} else {
	$borderIn = "#FFFFFF";
}

$checklist = array('commands', 'text');
foreach ($checklist as $check) {
	if (!isset($adminbox[$check])) {
		$adminbox[$check] = '';
	}
}

$pixelImage = "<img src=\"" . getImagePath('pixel_trans.gif') . "\" width=\"1\" height=\"1\" alt=\"pixel_trans\">";

$width='';
if (isset($navigator["fullWidth"]) && isset($navigator["widthUnits"])) {
        $width=' width="'. $navigator["fullWidth"] . $navigator["widthUnits"] .'" ';
}

?>
<table<?php echo $width; ?> border="0" cellspacing="0" cellpadding="2" class="modnavboxtop">
  <tr> 
    <td class="admin" align="left" valign="bottom" height="18">&nbsp;<?php echo $adminbox["text"] ?></td>
    <td class="admin" align="right" valign="bottom" height="18">
        <form name="admin_options_form" action="view_album.php" style="margin-bottom: 0px;">
        <?php echo $adminbox["commands"] ?>
        </form>
    </td>
  </tr>
</table>    
