<?php /* $Id$ */ ?>
<?php
// Calculate the beginning and ending of the navigator range
$begin = 0;
$navpage = sizeof($navigator["allIds"]) - 1;
while ($navpage > 0) {
	if (!strcmp($navigator["allIds"][$navpage], $navigator["id"])) {
		break;
	}
	$navpage--;
}
$navcount = sizeof($navigator["allIds"]);

// If the border color is not passed in, we do a white one
if ($navigator["bordercolor"]) {
	$borderIn = $navigator["bordercolor"];
} else {
	$borderIn = "#FFFFFF";
}


$imageDir = $gallery->app->photoAlbumURL."/images";
$pixelImage = "<img src=\"$imageDir/pixel_trans.gif\" width=\"1\" height=\"1\">";
?>

<table width="<?php echo $navigator["fullWidth"] . $navigator["widthUnits"] ?>" border="0" cellspacing="0" cellpadding="0">
  <tr> 
    <td colspan="11" bgcolor="<?php echo $borderIn ?>"><?php echo $pixelImage ?></td>
  </tr>
  <tr> 
    <td bgcolor="<?php echo $borderIn ?>" width="1" height="1"><?php echo $pixelImage ?></td>
<?php      
#-- 'first' button cell ---
if ($navpage == 0) {
?>
    <td align="center" width="27" height="18">&nbsp;</td> 
    <td width="1" height="1"><?php echo $pixelImage ?></td>
<?php
} else {
	$firstPage = $navigator["allIds"][0];
	$firstAlbumName = "";
	if ($gallery->album->isAlbumName(1)) { // first image is an album
		$firstAlbumName = $gallery->album->isAlbumName(1);
	}
?>
    <td align="center" width="27" height="18"> 
<?php
	if ($firstAlbumName) {
?>
      <a href="<?php echo makeAlbumUrl($firstAlbumName) ?>"><img src="<?php echo $imageDir ?>/<?php echo ($gallery->direction == "ltr") ? "nav_first.gif" : "nav_last.gif" ?>" border="0" width="27" height="11" alt="<?php echo _("First Photo") ?>" title="<?php echo _("First Photo") ?>"></a>
<?php	} else {
?>
      <a href="<?php echo makeAlbumUrl($gallery->session->albumName, $firstPage) ?>"><img src="<?php echo $imageDir ?>/<?php echo ($gallery->direction == "ltr") ? "nav_first.gif" : "nav_last.gif" ?>" border="0" width="27" height="11" alt="<?php echo _("First Photo") ?>" title="<?php echo _("First Photo") ?>"></a>
<?php
	}
?>
      </td>
    <td bgcolor="<?php echo $borderIn ?>" width="1" height="1"><?php echo $pixelImage ?></td>
<?php
} 
?>
<?php
#-- 'previous' button cell ---
$prevPage = $navpage - 1;
if ($prevPage >= 0) {
	$prevPage = $navigator["allIds"][$prevPage];
	$prevAlbumName = "";
	if ($gallery->album->isAlbumName($navpage)) { // previous page is album
		$prevAlbumName = $gallery->album->isAlbumName($navpage);
	}
?>
    <td align="center" width="72" height="18">
<?php
        if ($prevAlbumName) {
?>
      <a href="<?php echo makeAlbumUrl($prevAlbumName) ?>"><img src="<?php echo $imageDir ?>/<?php echo ($gallery->direction == "ltr") ? "nav_prev.gif" : "nav_next.gif" ?>" border="0" width="72" height="11" alt="<?php echo _("Previous Photo") ?>" title="<?php echo _("Previous Photo") ?>">
</a>
<?php
        } else {
?>
      <a href="<?php echo makeAlbumUrl($gallery->session->albumName, $prevPage) ?>"><img src="<?php echo $imageDir ?>/<?php echo ($gallery->direction == "ltr") ? "nav_prev.gif" : "nav_next.gif" ?>" border="0" width="72" height="11" alt="<?php echo _("Previous Photo") ?>" title="<?php echo _("Previous Photo") ?>"></a>
<?php
}
?>
      </td>
    <td bgcolor="<?php echo $borderIn ?>" width="1" height="1"><?php echo $pixelImage ?></td>
<?php
} else {
?>
    <td align="center" width="72" height="18">&nbsp;</td>
    <td width="1" height="1"><?php echo $pixelImage ?></td>
<?php
}
?>
    <td align="center" width="3000" height="18">   
<?php
#-- 'page numbers' cell ---                  
echo "<span class=\"nav\">";
echo sprintf(_("%d of %d"), $navpage+1, $navcount);
echo "</span>";
?>
    </td>
<?php
#-- 'next' button cell ---
if ($navpage+1 != $navcount) {
	$nextPage = $navigator["allIds"][$navpage+1];
	$nextAlbumName = "";
	if ($gallery->album->isAlbumName($navpage+2)) { //our next page is an album
		$nextAlbumName = $gallery->album->isAlbumName($navpage+2);
	} 
?>
    <td bgcolor="<?php echo $borderIn ?>" width="1" height="1"><?php echo $pixelImage ?></td>
    <td align="center" width="72" height="18">
<?php 
	if ($nextAlbumName) {
?>
      <a href="<?php echo makeAlbumUrl($nextAlbumName) ?>"><img src="<?php echo $imageDir ?>/<?php echo ($gallery->direction == "ltr") ? "nav_next.gif" : "nav_prev.gif" ?>" border="0" width="72" height="11" alt="<?php echo _("Next Photo") ?>" title="<?php echo _("Next Photo") ?>"></a>
<?php
	} else {
?>
      <a href="<?php echo makeAlbumUrl($gallery->session->albumName, $nextPage) ?>"><img src="<?php echo $imageDir ?>/<?php echo ($gallery->direction == "ltr") ? "nav_next.gif" : "nav_prev.gif" ?>" border="0" width="72" height="11" alt="<?php echo _("Next Photo") ?>" title="<?php echo _("Next Photo") ?>">
</a>
<?php
}
?>
      </td>
<?php
} else {   
?>
    <td align="center" width="72" height="18">&nbsp;</td>
    <td width="1" height="1"><?php echo $pixelImage ?></td>
<?php
} 
?>
<?php
#-- 'last' button ---
if ($navpage+1 == $navcount) {
?>
    <td width="1" height="1"><?php echo $pixelImage ?></td>
    <td align="center" width="27" height="18">&nbsp;</td>
<?php
} else {        
	$lastPage = $navigator["allIds"][$navcount-1];
	$lastAlbumName = "";
	if ($gallery->album->isAlbumName($navcount)) { // out last page is an album
		$lastAlbumName = $gallery->album->isAlbumName($navcount);
	}
?>
    <td bgcolor="<?php echo $borderIn ?>" width="1" height="1"><?php echo $pixelImage ?></td>
    <td align="center" width="27" height="18">
<?php
	if ($lastAlbumName) {
?>
      <a href="<?php echo makeAlbumUrl($lastAlbumName) ?>"><img src="<?php echo $imageDir ?>/<?php echo ($gallery->direction == "ltr") ? "nav_last.gif" : "nav_first.gif" ?>" border="0" width="27" height="11" alt="<?php echo _("Last Photo") ?>" title="<?php echo _("Last Photo") ?>"></a>
<?php 	} else {
?>
      <a href="<?php echo makeAlbumUrl($gallery->session->albumName, $lastPage) ?>"><img src="<?php echo $imageDir ?>/<?php echo ($gallery->direction == "ltr") ? "nav_last.gif" : "nav_first.gif" ?>" border="0" width="27" height="11" alt="<?php echo _("Last Photo") ?>" title="<?php echo _("Last Photo") ?>"></a>
<?php
}
?>
    </td>
<?php
} 
?>
    <td bgcolor="<?php echo $borderIn ?>" width="1" height="1"><?php echo $pixelImage ?></td>
  </tr>
  <tr> 
    <td colspan="11" bgcolor="<?php echo $borderIn ?>"><?php echo $pixelImage ?></td>
  </tr>
</table>
    
