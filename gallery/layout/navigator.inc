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

$fpAltText= _("First Page");
$ppAltText= _("Previous Page");
$npAltText= _("Next Page");
$lpAltText= _("Last Page");

if ($gallery->direction == "ltr") {
	$fpImgUrl= getImagePath('nav_first.gif');
	$ppImgUrl= getImagePath('nav_prev.gif');
	$npImgUrl= getImagePath('nav_next.gif');
	$lpImgUrl= getImagePath('nav_last.gif');
} else {
	$fpImgUrl= getImagePath('nav_last.gif');
	$ppImgUrl= getImagePath('nav_next.gif');
	$npImgUrl= getImagePath('nav_prev.gif');
	$lpImgUrl= getImagePath('nav_first.gif');
}

$current = $navigator["page"];
$prevPage = $current -1;
$nextPage = $current +1;
$maxPages = $navigator["maxPages"];

#-- 'first' and 'previous button cell ---
if ($navigator["page"] != 1) {
	$fpContent ='<a href="'. $url . $navigator['pageVar'] .'=1">';
	$fpContent .='<img src="'. $fpImgUrl . '" border="0" width="27" height="11" alt="'. $fpAltText .'" title="'. $fpAltText .'">';
	$fpContent .='</a>';

	$ppContent ='<a href="'. $url . $navigator['pageVar'] .'='. $prevPage .'">';
	$ppContent .='<img src="'. $ppImgUrl . '" border="0" width="72" height="11" alt="'. $ppAltText .'" title="'. $ppAltText .'">';
        $ppContent .='</a>';

	$pClass='borderright';

} else {
	$fpContent='&nbsp;';
	$ppContent='&nbsp;';

	$pClass='';
} 

#-- 'page numbers' cell ---                  
if ($begin != $end) {
$ndlUrl=getImagePath('nav_dot_left.gif');
$ndrUrl=getImagePath('nav_dot_right.gif');
$ndUrl=getImagePath('nav_dot.gif');

	$mpContent = "\n\t\t";
	for ($i = $begin; $i <= $end; $i++) {
		if ($i == $current) {
			$number = "<b>$i</b>";
			$leftdot = '<img valign="absmiddle" src="'. $ndlUrl .'" alt="leftdot">';
		} else {
			$number = '<a class="nav" href="'. $url. $navigator['pageVar'] .'='. $i .'">'. $i .'</a>';
			if ( $i-1 == $current) {
				$leftdot = '<img valign="absmiddle" src="'. $ndrUrl .'" alt="leftdot">';
			} else {
				$leftdot = '<img valign="absmiddle" src="'. $ndUrl .'"  alt="leftdot">';
			}
		}
		$mpContent .= "&nbsp;$leftdot&nbsp$number\n\t\t";
	}

	if ($end == $current) {
		$rightdot = '<img valign="absmiddle" src="'. $ndrUrl .'"  alt="rightdot">';
	} else {
		$rightdot = '<img valign="absmiddle" src="'. $ndUrl .'"  alt="rightdot">';
	}

	$mpContent .="&nbsp;$rightdot&nbsp;";
} else {
	$mpContent ='';
}

#-- 'next' and 'last' button cell ---
if ($current < $maxPages) {
	$npContent ='<a href="'. $url . $navigator['pageVar'] .'='. $nextPage .'">';
	$npContent .='<img src="'. $npImgUrl . '" border="0" width="72" height="11" alt="'. $npAltText .'" title="'. $npAltText .'">';
        $npContent .='</a>';

	$lpContent ='<a href="'. $url . $navigator['pageVar'] .'='. $navigator['maxPages'] .'">';
	$lpContent .='<img src="'. $lpImgUrl . '" border="0" width="27" height="11" alt="'. $lpAltText .'" title="'. $lpAltText .'">';
	$lpContent .='</a>';

	$nClass='borderleft';
} else {
	$npContent='&nbsp;';
	$lpContent='&nbsp;';

        $nClass ='';
}
?>

<!-- Navigator -->
<table width="<?php echo $navigator["fullWidth"] . $navigator["widthUnits"] ?>" border="0" cellspacing="0" cellpadding="0" class="modnavbox">
<tr>
	<td class="<?php echo $pClass ?>" align="center" width="30" height="18"><span class="nav"><?php echo $fpContent ?></span></td>
	<td class="<?php echo $pClass ?>" align="center" width="100" height="18"><span class="nav"><?php echo $ppContent ?></span></td>
	<td class="nav" align="center" height="18"><?php echo $mpContent; ?></td>
	<td class="<?php echo $nClass ?>" align="center" width="100" height="18"><span class="nav"><?php echo $npContent ?></span></td>
	<td class="<?php echo $nClass ?>" align="center" width="30" height="18"><span class="nav"><?php echo $lpContent ?></span></td>
</tr>
</table>
