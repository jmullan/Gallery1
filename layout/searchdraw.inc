<?php
/* $Id$ */

global $searchdraw;

// If the border color is not passed in, we do a black/white combo
if ($searchdraw["bordercolor"]) {
	$borderIn = $searchdraw["bordercolor"];
}
else {
	$borderIn = "#FFFFFF";
}

?>

  <tr><td colspan="3"><hr width="100%"></td></tr>
  <tr>
    <td width="100">
	<a href="<?php echo $searchdraw["photoURL"] ?>"><?php echo $searchdraw["photolink"] ?></a>
    </td>
    <td width="10">&nbsp;</td>
    <td class="g-albumdesc-cell"><?php
	if (!empty($searchdraw['Text'])) {
	    foreach ($searchdraw['Text'] as $text) {
		echo $text;
	    }
	}
?>
    </td>
  </tr>
