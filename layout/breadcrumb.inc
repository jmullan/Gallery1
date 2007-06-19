<?php

/**
 * Prints a table where at the leftside is shown who si logged in an on the right the returnto path.
 *
 * @package Gallery
 * @todo 	Move to templates/
 *
 * @version	$Id$
 */

global $breadcrumb, $gallery;

if(!includeTemplate('breadcrumb') &&
  (!empty($breadcrumb['text']) || $gallery->user->isLoggedIn()))
{
?>

<table class="g-breadcrumb">
   <tr>
	<?php
	if ($gallery->user->isLoggedIn()) {
	    $name = $gallery->user->getFullName();
	    if (!$name) {
	        $name = $gallery->user->getUsername();
	    }
	    echo '<td class="left floatleft">'. sprintf(gTranslate('core', "Logged in as: %s"), $name) .'</td>';
	}
	?>

	<td class="right">
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
