<?php
// $Id$
?>
<?php
global $breadcrumb, $gallery;

if(!includeTemplate('breadcrumb.tpl')
  && (!empty($breadcrumb['text']) || $gallery->user->isLoggedIn())) {
?>

<table class="g-breadcrumb" cellspacing="0" cellpadding="0">
<tr>
	<?php
	if ($gallery->user->isLoggedIn()) {
	    $name = $gallery->user->getFullName();
	    if (!$name) {
	        $name = $gallery->user->getUsername();
	    }
	    echo "\t". '<td class="left">'. sprintf(_("Logged in as: %s"), $name) .'</td>';
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
