<?php /* $Id$ */ ?>
<?php
global $navigator;

// Calculate the beginning and ending of the navigator range
$begin = 0;
$navpage = sizeof($navigator["allIds"]) - 1;

$navpage=array_search($navigator["id"], $navigator["allIds"]);

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

$fpAltText= _("First Photo");
$ppAltText= _("Previous Photo");
$npAltText= _("Next Photo");
$lpAltText= _("Last Photo");

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


$firstPage = $navigator["allIds"][0];
$lastPage = $navigator["allIds"][$navcount-1];

#-- 'first' and 'previous button cell ---
if ($navpage > 0) {
	$fpContent = '<a href="'. makeAlbumUrl($gallery->session->albumName, $firstPage) .'">';
	$fpContent .='<img src="'. $fpImgUrl . '" border="0" width="27" height="11" alt="'. $fpAltText .'" title="'. $fpAltText .'">';
	$fpContent .='</a>';

	$prevPage = $navigator["allIds"][$navpage-1];
	$ppContent = '<a href="'. makeAlbumUrl($gallery->session->albumName, $prevPage).'">';
	$ppContent .='<img src="'. $ppImgUrl . '" border="0" width="72" height="11" alt="'. $ppAltText .'" title="'. $ppAltText .'">';
	$ppContent .='</a>';

	$pClass='borderright';
} else {
	$fpContent='&nbsp;';
	$ppContent='&nbsp;';

	$pClass='';
}

#-- 'page numbers' cell ---                  
	$mpContent=sprintf(_("%d of %d"), $navpage+1, $navcount);

#-- 'next' and 'last' button cell ---
if ($navpage < $navcount-1) {
	
	$nextPage = $navigator["allIds"][$navpage+1];
	$npContent ='<a href="'. makeAlbumUrl($gallery->session->albumName, $nextPage) .'">';
	$npContent .='<img src="'. $npImgUrl . '" border="0" width="72" height="11" alt="'. $npAltText .'" title="'. $npAltText .'">';
	$npContent .='</a>';

	$lpContent ='<a href="'. makeAlbumUrl($gallery->session->albumName, $lastPage) .'">';
	$lpContent .='<img src="'. $lpImgUrl . '" border="0" width="27" height="11" alt="'. $lpAltText .'" title="'. $lpAltText .'">';
	$lpContent .='</a>';

	$nClass='borderleft';
} else {   
	$npContent='&nbsp;';
	$lpContent='&nbsp;';

        $nClass='';
} 
?>

<!-- Photo Navigator -->
<table<?php echo $width ?> border="0" cellspacing="0" cellpadding="0" class="modnavbox">
<tr>
	<td class="<?php echo $pClass ?>" align="center" width="30" height="18"><span class="nav"><?php echo $fpContent ?></span></td>
	<td class="<?php echo $pClass ?>" align="center" width="100" height="18"><span class="nav"><?php echo $ppContent ?></span></td>
	<td class="nav" align="center" height="18"><?php echo $mpContent; ?></td>
	<td class="<?php echo $nClass ?>" align="center" width="100" height="18"><span class="nav"><?php echo $npContent ?></span></td>
	<td class="<?php echo $nClass ?>" align="center" width="30" height="18"><span class="nav"><?php echo $lpContent ?></span></td>
</tr>
</table>
