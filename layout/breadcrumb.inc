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

<div class="g-breadcrumb">
	<?php
	if ($gallery->user->isLoggedIn()) {
	    $name = $gallery->user->getFullName();
	    if (!$name) {
	        $name = $gallery->user->getUsername();
	    }
	    echo '<div class="left floatleft">'. sprintf(gTranslate('core', "Logged in as: %s"), $name) .'</div>';
	}
	?>

	<div class="right">
	<?php
	if (isset($breadcrumb['text'])) {
	    foreach($breadcrumb["text"] as $nr => $text) {
	        echo "\t\t$text\n";
	    }
	}
	?>
	</div>
</div>

<?php
}
?>
