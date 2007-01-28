<?php /* $Id$ */ ?>
<?php
global $navigator;
global $full;

if(!includeTemplate('navphoto.tpl')) {
    // Calculate the beginning and ending of the navigator range
    $begin = 0;
    $navpage = sizeof($navigator["allIds"]) - 1;
    $navpage = array_search($navigator["id"], $navigator["allIds"]);
    $navcount = sizeof($navigator["allIds"]);

    // If the border color is not passed in, we do a white one
    if ($navigator["bordercolor"]) {
        $borderIn = $navigator["bordercolor"];
    } else {
        $borderIn = "#FFFFFF";
    }

    if (isset($navigator["fullWidth"]) && isset($navigator["widthUnits"])) {
        $width=' width="'. $navigator["fullWidth"] . $navigator["widthUnits"] .'"';
    }

    $fpAltText = gTranslate('core', "_First Photo");
    $ppAltText = gTranslate('core', "_Previous Photo");
    $npAltText = gTranslate('core', "_Next Photo");
    $lpAltText = gTranslate('core', "_Last Photo");

    include(dirname(dirname(__FILE__)) .'/includes/definitions/navIcons.php');

    $firstPage = $navigator["allIds"][0];
    $lastPage = $navigator["allIds"][$navcount-1];

    #-- 'first' and 'previous button cell ---
    if ($navpage > 0) {
    	$fpUrl =  makeAlbumUrl($gallery->session->albumName, $firstPage, ($full) ? array('full' => 1) : NULL) ;
	$fpContent = galleryIconLink($fpUrl, $fpImg, $fpAltText, 'yes');

        $prevPage = $navigator["allIds"][$navpage-1];
        $ppUrl = makeAlbumUrl($gallery->session->albumName, $prevPage, ($full) ? array('full' => 1) : NULL);
        $ppContent = galleryIconLink($ppUrl, $ppImg, $ppAltText, 'yes');

        $pClass= 'g-borderright';
    } else {
        $fpContent = '&nbsp;';
        $ppContent = '&nbsp;';

        $pClass = '';
    }

    #-- 'page numbers' cell ---
    $mpContent = sprintf(gTranslate('core', "%d of %d"), $navpage+1, $navcount);

    #-- 'next' and 'last' button cell ---
    if ($navpage < $navcount-1) {
        $nextPage = $navigator["allIds"][$navpage+1];
        $npUrl = makeAlbumUrl($gallery->session->albumName, $nextPage, ($full) ? array('full' => 1) : NULL);
        $npContent = galleryIconLink($npUrl, $npImg, $npAltText, 'yes');

        $lpUrl = makeAlbumUrl($gallery->session->albumName, $lastPage, ($full) ? array('full' => 1) : NULL);
        $lpContent = galleryIconLink($lpUrl, $lpImg, $lpAltText, 'yes');

        $nClass = 'g-borderleft';
    } else {
        $npContent = '&nbsp;';
        $lpContent = '&nbsp;';

        $nClass = '';
    }
?>
<!-- 'Classic' Photo Navigator -->
<table cellspacing="0" cellpadding="0" width="100%">
<tr>
	<td class="<?php echo $pClass ?>" width="25" height="18"><?php echo $fpContent ?></td>
	<td class="<?php echo $pClass ?>" width="25" height="18"><?php echo $ppContent ?></td>
	<td height="18"><?php echo $mpContent; ?></td>
	<td class="<?php echo $nClass ?>" width="25" height="18"><?php echo $npContent ?></td>
	<td class="<?php echo $nClass ?>" width="25" height="18"><?php echo $lpContent ?></td>
</tr>
</table>
	<?php
}
?>