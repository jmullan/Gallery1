<?php /* $Id$ */ ?>
<?php
global $navigator;
// Calculate the beginning and ending of the navigator range
$begin = max($navigator["page"] - $navigator["spread"], 1);
$end = min($navigator["page"] + $navigator["spread"], $navigator["maxPages"]);

// If we're pinned at the beginning or the end, expand as far as we can
// in the opposite direction
if ($begin == 1 && $end <= $navigator["maxPages"]) {
	$end = min(2 * $navigator["spread"], $navigator["maxPages"]);
}
if ($end == $navigator["maxPages"]) {
	$begin = max(1, $end - 2 * $navigator["spread"]);
}

// If the border color is not passed in, we do a white one
if ($navigator["bordercolor"]) {
	$borderIn = $navigator["bordercolor"];
} else {
	$borderIn = "";
}

$url = $navigator["url"];
if (!strstr($url, "?")) {
	$url .= "?";
}
else {
	$url .= "&";
}

$pixelImage = "<img src=\"" . getImagePath('pixel_trans.gif') . "\" width=\"0\" height=\"0\" alt=\"pixel_trans\">";
?>

<table width="<?php echo $navigator["fullWidth"] . $navigator["widthUnits"] ?>" border="0" cellspacing="0" cellpadding="0" class="modnavbox">
  <tr> 
<?php      
#-- 'first' button cell ---
if ($navigator["page"] == 1) {
?>
    <td align="center" width="27" height="14">&nbsp;</td> 
    <td width="0" height="14"><?php echo $pixelImage ?></td>
<?php
} else {
?>
    <td align="center" width="27" height="14" class="borderright"><span class="nav"> 
      <a href="<?php echo $url ?><?php echo $navigator['pageVar'] ?>=1"><img src="<?php echo ($gallery->direction == "ltr") ? getImagePath('nav_first.gif') : getImagePath('nav_last.gif') ?>" border="0" width="27" height="11" alt="<?php echo _("First Page") ?>" title="<?php echo _("First Page") ?>"></a>
      </span></td>
    <td width="1" height="1"><?php echo $pixelImage ?></td>
<?php
} 
?>
<?php
#-- 'previous' button cell ---
$prevPage = $navigator["page"] - 1;
if ($prevPage >= 1) {
?>
    <td align="center" width="72" height="14" class="borderright"><span class="nav">
      <a href="<?php echo $url ?><?php echo $navigator['pageVar'] ?>=<?php echo $prevPage ?>"><img valign="absmiddle" src="<?php echo ($gallery->direction == "ltr") ? getImagePath('nav_prev.gif') : getImagePath('nav_next.gif') ?>" border="0" width="72" height="11" alt="<?php echo _("Previous Page") ?>" title="<?php echo _("Previous Page") ?>"></a>
      </span></td>
    <td width="0" height="14"><?php echo $pixelImage ?></td>
<?php
} else {

}
?>
    <td align="center" width="3000" height="14">   
<?php
#-- 'page numbers' cell ---                  
if ($begin != $end) {
	$current = $navigator["page"];
	echo "<span class=\"nav\">";
	for ($i = $begin; $i <= $end; $i++) {
		if ($i == $current) {
			$number = "<b>$i</b>";
			$leftdot = "<img valign=\"absmiddle\" src=\"" . getImagePath('nav_dot_left.gif') . "\">";
		} else {
			$number = "<a href=\"".$url.$navigator['pageVar']."=$i\">$i</a>";
			if (($i-1) == $navigator["page"]) {
				$leftdot = "<img valign=\"absmiddle\" src=\"" . getImagePath('nav_dot_right.gif') . "\">";
			} else {
				$leftdot = "<img valign=\"absmiddle\" src=\"" . getImagePath('nav_dot.gif') . "\">";
			}
		}
	
		echo "&nbsp;$leftdot&nbsp;";
		echo "$number";
	}
	echo "</span>";
	if ($end == $current) {
		$rightdot = "<img valign=\"absmiddle\" src=\"" . getImagePath('nav_dot_right.gif') . "\">";
	} else {
		$rightdot = "<img valign=\"absmiddle\" src=\"" . getImagePath('nav_dot.gif') . "\">";
	}
	echo "&nbsp;$rightdot&nbsp;";
}
?>
    </td>
<?php
#-- 'next' button cell ---
$nextPage = $navigator["page"] + 1;
if ($nextPage <= $navigator["maxPages"]) {
?>
    <td width="0" height="14"><?php echo $pixelImage ?></td>
    <td align="center" width="72" height="14" class="borderleft"><span class="nav">
      <a href="<?php echo $url ?><?php echo $navigator['pageVar'] ?>=<?php echo $nextPage ?>"><img valign="absmiddle" src="<?php echo ($gallery->direction == "ltr") ? getImagePath('nav_next.gif') : getImagePath('nav_prev.gif') ?>" border="0" alt="<?php echo _("Next Page") ?>" title="<?php echo _("Next Page") ?>"></a>
      </span></td>
<?php
} else {   

} 
?>
<?php
#-- 'last' button ---
if ($navigator["page"] == $navigator["maxPages"]) {

} else {        
?>
    <td width="0" height="14"><img valign="absmiddle" src="<?php echo getImagePath('pixel_trans.gif')?>" width="0" height="0"></td>
    <td align="center" width="27" height="14" class="borderleft"><span class="nav">
      <a href="<?php echo $url ?><?php echo $navigator['pageVar'] ?>=<?php echo $navigator['maxPages'] ?>"><img valign="absmiddle" src="<?php echo ($gallery->direction == "ltr") ? getImagePath('nav_last.gif') : getImagePath('nav_first.gif') ?>" border="0" alt="<?php echo _("Last Page") ?>" title="<?php echo _("Last Page") ?>"></a>
      </span></td>
<?php
} 
?>
  </tr>
</table>
