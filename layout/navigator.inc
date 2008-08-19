<?php

/**
 * Generats and prints a table with page navigation, forward backward links/icons
 *
 * @package Gallery
 * @todo 	Split into code and layout.
 *
 * @version $Id$
 */

global $navigator;
global $navc;
$navc++;

if(!includeTemplate('navigator.tpl')) {
	if ($navigator["maxPages"] > 1) {
		/** Direction depending navigation icons */
		include(dirname(dirname(__FILE__)) .'/includes/definitions/navIcons.php');

		// Calculate the beginning and ending of the navigator range
		$begin = max($navigator["page"] - $navigator["spread"], 1);
		$end = min($navigator["page"] + $navigator["spread"], $navigator["maxPages"]);

		// If we're pinned at the beginning or the end, expand as far as we can
		// in the opposite direction
		if ($begin == 1 && $end <= $navigator["maxPages"]) {
			$end = min(2 * $navigator["spread"], $navigator["maxPages"]);
		}

		if ($end == $navigator["maxPages"]) {
			$begin = max(1, $end - 2 * $navigator["spread"] + 1);
		}

		if($begin > $navigator["spread"]) {
			$beginNextBlock = max(1, $begin - $navigator["spread"]);
		}

		if($end < ($navigator["maxPages"] - $navigator["spread"])) {
			$endNextBlock = min($end + $navigator["spread"], $navigator["maxPages"]);
		}

		// If the border color is not passed in, we do a white one
		if ($navigator["bordercolor"]) {
			$borderIn = $navigator["bordercolor"];
		}
		else {
			$borderIn = '';
		}

		$url = $navigator["url"];
		if (!strstr($url, "?")) {
			$url .= "?";
		}
		else {
			$url .= "&amp;";
		}

		$fpAltText= gTranslate('core', "_First Page ");
		$ppAltText= gTranslate('core', "_Previous Page");
		$npAltText= gTranslate('core', "_Next Page");
		$lpAltText= gTranslate('core', "_Last Page");

		$current = $navigator["page"];
		$prevPage = $current -1;
		$nextPage = $current +1;
		$maxPages = $navigator["maxPages"];

		#-- 'first' and 'previous button cell ---
		if ($navigator["page"] != 1) {
			$fpContent = galleryIconLink(
					$url . $navigator['pageVar'] .'=1',
					$fpImg,
					$fpAltText,
					'yes'
			);

			$ppContent = galleryIconLink(
				$url . $navigator['pageVar'] ."=$prevPage",
				$ppImg,
				$ppAltText,
				'yes',
				array('id' => "g-nav-backward_$navc")
			);

			$pClass = 'g-borderright';
		}
		else {
			$fpContent = '&nbsp;';
			$ppContent = '&nbsp;';

			$pClass = '';
		}

		#-- 'page numbers' cell ---
		if ($begin != $end) {
			$ndl = 'icons/navigation/nav_dot_left.gif';
			$ndr = 'icons/navigation/nav_dot_right.gif';
			$nd =  'icons/navigation/nav_dot.gif';

			$mpContent = "\n\t\t". '<table align="center" cellpadding="0" cellspacing="0" class="g-nav-pages"><tr>';
			if (isset($beginNextBlock)) {
				$title = sprintf(gTranslate('core', "Jump to page %d (Accesskey '_r')"), $beginNextBlock);
				$accesskey = getAndRemoveAccessKey($title);
				$link = galleryLink("{$url}${navigator['pageVar']}=$beginNextBlock",
				    '...', array('title' => $title, 'accesskey' => $accesskey));
				$mpContent .= "\n\t<td>$link</td>";
			}
			for ($i = $begin; $i <= $end; $i++) {
				if ($i == $current) {
					$number = "<b>$i</b>";
					$leftdot = gImage($ndl, "leftdot");
				}
				else {
					$number = galleryLink("${url}${navigator['pageVar']}=$i", $i);
					if ($i-1 == $current) {
						$leftdot = gImage($ndr, "leftdot");
					}
					else {
						$leftdot = gImage($nd, "leftdot");
					}
				}
				$mpContent .= "\n\t<td>&nbsp;$leftdot&nbsp;$number</td>";
			}

			if ($end == $current) {
				$rightdot = gImage($ndr, "rightdot");
			}
			else {
				$rightdot = gImage($nd, "rightdot");
			}

			$mpContent .="\n\t<td>&nbsp;$rightdot";

			if (isset($endNextBlock)) {
				$title = sprintf(gTranslate('core', "Jump to page %d (Accesskey '_j')"), $endNextBlock);
				$accesskey = getAndRemoveAccessKey($title);
				$link = galleryLink("{$url}${navigator['pageVar']}=$endNextBlock",
				    '...', array('title' => $title, 'accesskey' => $accesskey));
				$mpContent .= "\n\t</td><td>$link";
			}
			$mpContent .= '</td></tr></table>';
		}
		else {
			$mpContent = '';
		}

		#-- 'next' and 'last' button cell ---
		if ($current < $maxPages) {
			$npContent = galleryIconLink(
				$url . $navigator['pageVar'] ."=$nextPage",
				$npImg,
				$npAltText,
				'yes',
				array('id' => "g-nav-forward_$navc")
			);

			$lpContent = galleryIconLink(
				$url . $navigator['pageVar'] .'='. $navigator['maxPages'],
				$lpImg,
				$lpAltText,
				'yes'
			);

			$nClass='g-borderleft';
		}
		else {
			$npContent = '&nbsp;';
			$lpContent = '&nbsp;';

			$nClass = '';
		}

		if($navc < 2) {
			$id = 'g-navtable';
		}
		else {
			$id = 'g-navtable-bottom';
		}
?>

<!-- Navigator -->
<table cellspacing="0" cellpadding="0" id="<?php echo $id; ?>" dir="<?php echo $gallery->direction; ?>">
<tr>
	<td class="<?php echo $pClass ?>" width="25" height="18"><?php echo $fpContent ?></td>
	<td class="<?php echo $pClass ?>" width="25" height="18"><?php echo $ppContent ?></td>
	<td height="18"><?php echo $mpContent; ?></td>
	<td class="<?php echo $nClass ?>" width="25" height="18"><?php echo $npContent ?></td>
	<td class="<?php echo $nClass ?>" width="25" height="18"><?php echo $lpContent ?></td>
</tr>
</table>
<?php
		if($navc < 2) {
			echo jsHtml('arrowkey_nav.js');
	    }
	}
}
?>
