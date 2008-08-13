<?php
/* $Id$ */

global $navigator, $full, $gallery, $navc;
$navc++;

if(!includeTemplate('navphoto.tpl')) {
	// Calculate the beginning and ending of the navigator range
	$begin = 0;
	$navpage = sizeof($navigator["allIds"]) - 1;
	$navpage = array_search($navigator["id"], $navigator["allIds"]);
	$navcount = sizeof($navigator["allIds"]);

	// If the border color is not passed in, we do a white one
	if ($navigator["bordercolor"]) {
		$borderIn = $navigator["bordercolor"];
	}
	else {
		$borderIn = "#FFF";
	}

	if (isset($navigator["fullWidth"]) && isset($navigator["widthUnits"])) {
		$width=' width="'. $navigator["fullWidth"] . $navigator["widthUnits"] .'"';
	}

	$fpAltText = gTranslate('core', "First Photo");
	$ppAltText = gTranslate('core', "Previous Photo");
	$npAltText = gTranslate('core', "Next Photo");
	$lpAltText = gTranslate('core', "Last Photo");

	include(dirname(dirname(__FILE__)) .'/includes/definitions/navIcons.php');

	$firstPage = $navigator["allIds"][0];
	$lastPage = $navigator["allIds"][$navcount-1];

	#-- 'first' and 'previous button cell ---
	if ($navpage > 0) {
		$fpUrl =  makeAlbumUrl($gallery->session->albumName, $firstPage, ($full) ? array('full' => 1) : NULL) ;
		$fpContent = galleryIconLink($fpUrl, $fpImg, $fpAltText, 'yes');

		$prevPage = $navigator["allIds"][$navpage-1];
		$ppUrl = makeAlbumUrl($gallery->session->albumName, $prevPage, ($full) ? array('full' => 1) : NULL);

		$ppContent = galleryIconLink(
			$ppUrl,
			$ppImg,
			$ppAltText,
			'yes',
			array('id' => "g-nav-backward_$navc")
		);

		$pClass= 'g-borderright';
	}
	else {
		$fpContent = '&nbsp;';
		$ppContent = '&nbsp;';

		$pClass = '';
	}

	#-- 'page numbers' cell ---
	$mpContent = sprintf(gTranslate('core', "%d of %d"), $navpage+1, $navcount);

	#-- 'next' and 'last' button cell ---
	if ($navpage < $navcount-1) {
		$nextPage	= $navigator["allIds"][$navpage+1];
		$npUrl		= makeAlbumUrl($gallery->session->albumName, $nextPage, ($full) ? array('full' => 1) : NULL);

		$npContent = galleryIconLink(
			$npUrl,
			$npImg,
			$npAltText,
			'yes',
			array('id' => "g-nav-forward_$navc")
		);

		$lpUrl		= makeAlbumUrl($gallery->session->albumName, $lastPage, ($full) ? array('full' => 1) : NULL);
		$lpContent	= galleryIconLink($lpUrl, $lpImg, $lpAltText, 'yes');

		$nClass		= 'g-borderleft';
	}
	else {
		$npContent	= '&nbsp;';
		$lpContent	= '&nbsp;';

		$nClass		= '';
	}

	if ($navcount > 1) {
		$goToOptions = array();
		foreach ($navigator['allIds'] as $key => $value) {
			$goToOptions[] = array(
				'value' => makeAlbumUrl($gallery->session->albumName, $value, ($full) ? array('full' => 1) : NULL),
				'text' =>  $key +1,
				'selected' => ($navpage == $key)
			);
		}

		$goToSelect = drawSelect2('goto', $goToOptions, array('onChange' => 'document.location.href=this.value'));

		$goto = '<div class="nowrap">' . gTranslate('core', "Jump to:") . $goToSelect . '</div>';
	}
	else {
		$goto = '';
	}

	if($navc < 2) {
		$id = 'g-navtable';
	}
	else {
		$id = 'g-navtable-bottom';
	}
?>
<!-- 'Classic' Photo Navigator -->
<table <?php echo $width ?> cellspacing="0" cellpadding="0" id="<?php echo $id; ?>" dir="<?php echo $gallery->direction; ?>">
<tr>
	<td class="arrows <?php echo $pClass ?>" width="25" height="18"><?php echo $fpContent ?></td>
	<td class="arrows <?php echo $pClass ?>" width="25" height="18"><?php echo $ppContent ?></td>
	<td height="18"><?php echo $mpContent; ?></td>
	<td class="arrows <?php echo $nClass ?>" width="25" height="18"><?php echo $npContent ?></td>
	<td class="arrows <?php echo $nClass ?>" width="25" height="18"><?php echo $lpContent ?></td>
	<td style="text-align: right" class="<?php echo $nClass ?>" width="100" height="18"><?php echo $goto ?></td>
</tr>
</table>
<?php
	if($navc < 2 && $gallery->app->comments_addType != "inside") {
		echo jsHtml('arrowkey_nav.js');
	}
}
?>