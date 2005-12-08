<?php /* $Id$ */ ?>
<?php
global $navigator;
if(!includeTemplate('navigator.tpl')) {
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

	if($begin > $navigator["spread"]) {
	   $beginNextBlock = max(1, $navigator["page"] - 2 * $navigator["spread"]);
	}

	if($end < $navigator["maxPages"]) {
	   $endNextBlock = min($navigator["page"] + 2 * $navigator["spread"], $navigator["maxPages"]);
	}

	// If the border color is not passed in, we do a white one
	if ($navigator["bordercolor"]) {
		$borderIn = $navigator["bordercolor"];
	} else {
		$borderIn = '';
	}

	$url = $navigator["url"];
	if (!strstr($url, "?")) {
		$url .= "?";
	}
	else {
		$url .= "&amp;";
	}

	$fpAltText= _("First Page");
	$ppAltText= _("Previous Page");
	$npAltText= _("Next Page");
	$lpAltText= _("Last Page");

	include(dirname(dirname(__FILE__)) .'/includes/definitions/navIcons.php');

	$current = $navigator["page"];
	$prevPage = $current -1;
	$nextPage = $current +1;
	$maxPages = $navigator["maxPages"];

	#-- 'first' and 'previous button cell ---
	if ($navigator["page"] != 1) {
		$fpContent ='<a href="'. $url . $navigator['pageVar'] .'=1">';
		$fpContent .='<img src="'. $fpImgUrl . '"  alt="'. $fpAltText .'" title="'. $fpAltText .'">';
		$fpContent .='</a>';
	
		$ppContent ='<a href="'. $url . $navigator['pageVar'] .'='. $prevPage .'">';
		$ppContent .='<img src="'. $ppImgUrl . '"  alt="'. $ppAltText .'" title="'. $ppAltText .'">';
	        $ppContent .='</a>';

		$pClass='borderright';
	} else {
		$fpContent='&nbsp;';
		$ppContent='&nbsp;';

		$pClass='';
	} 

	#-- 'page numbers' cell ---                  
	if ($begin != $end) {
		$ndlUrl = getImagePath('nav_dot_left.gif');
		$ndrUrl = getImagePath('nav_dot_right.gif');
		$ndUrl = getImagePath('nav_dot.gif');
	
		$mpContent = "\n\t\t". '<table align="center" border="0" cellpadding="0" cellspacing="0"><tr>';
		if (isset($beginNextBlock)) {
		     $title = sprintf(_("Jump to page %d"), $beginNextBlock);
		     $mpContent .= "\n\t". '<td><a title="'. $title .'" href="'. $url . $navigator['pageVar'] .'='. $beginNextBlock .'">...</a></td>';
		}
		for ($i = $begin; $i <= $end; $i++) {
			if ($i == $current) {
				$number = '<span class="nav"><b>'. $i .'</b></span>';
				$leftdot = '<img src="'. $ndlUrl .'" alt="leftdot">';
			} else {
				$number = '<a class="nav" href="'. $url. $navigator['pageVar'] .'='. $i .'">'. $i .'</a>';
				if ( $i-1 == $current) {
					$leftdot = '<img src="'. $ndrUrl .'" alt="leftdot">';
				} else {
					$leftdot = '<img src="'. $ndUrl .'"  alt="leftdot">';
				}
			}
			$mpContent .= "\n\t<td>&nbsp;$leftdot&nbsp;$number</td>";
		}

		if ($end == $current) {
			$rightdot = '<img src="'. $ndrUrl .'" alt="rightdot">';
		} else {
			$rightdot = '<img src="'. $ndUrl .'" alt="rightdot">';
		}

		$mpContent .="\n\t<td>&nbsp;$rightdot";
		if (isset($endNextBlock)) {
		    $title = sprintf(_("Jump to page %d"), $endNextBlock);
                    $mpContent .= '<a title="'. $title .'" href="'. $url . $navigator['pageVar'] .'='. $endNextBlock .'">...</a>';
                }		
		$mpContent .= '</td></tr></table>';
	} else {
		$mpContent ='';
	}

	#-- 'next' and 'last' button cell ---
	if ($current < $maxPages) {
		$npContent ='<a href="'. $url . $navigator['pageVar'] .'='. $nextPage .'">';
		$npContent .='<img src="'. $npImgUrl . '"  alt="'. $npAltText .'" title="'. $npAltText .'">';
	        $npContent .='</a>';

		$lpContent ='<a href="'. $url . $navigator['pageVar'] .'='. $navigator['maxPages'] .'">';
		$lpContent .='<img src="'. $lpImgUrl . '"  alt="'. $lpAltText .'" title="'. $lpAltText .'">';
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
	<?php
}
?>
