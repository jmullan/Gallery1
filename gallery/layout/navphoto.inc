<?
// Calculate the beginning and ending of the navigator range
$begin = 1;
$end = $navigator["maxPages"];

// If the border color is not passed in, we do a white one
if ($navigator["bordercolor"]) {
	$borderIn = $navigator["bordercolor"];
} else {
	$borderIn = "#FFFFFF";
}


$imageDir = $app->photoAlbumURL."/images";
$pixelImage = "<img src=\"$imageDir/pixel_trans.gif\" width=\"1\" height=\"1\">";
?>

<table width="<?=$navigator["fullWidth"] . $navigator["widthUnits"]?>" border="0" cellspacing="0" cellpadding="0">
  <tr> 
    <td colspan="11" bgcolor="<?=$borderIn?>"><?=$pixelImage?></td>
  </tr>
  <tr> 
    <td bgcolor="<?=$borderIn?>" width="1" height="1"><?=$pixelImage?></td>
<?      
#-- 'first' button cell ---
if ($navigator["page"] == 1) {
?>
    <td align="center" width="27" height="18">&nbsp;</td> 
    <td width="1" height="1"><?=$pixelImage?></td>
<?
} else {
	$firstPage = $album->getPhotoId(1);
?>
    <td align="center" width="27" height="18"> 
      <a href="<?=makeGalleryUrl($albumName, $firstPage)?>"><img src="<?=$imageDir?>/nav_first.gif" border="0" width="27" height="11" alt="First Photo"></a>
      </td>
    <td bgcolor="<?=$borderIn?>" width="1" height="1"><?=$pixelImage?></td>
<?
} 
?>
<?
#-- 'previous' button cell ---
$prevPage = $navigator["page"] - 1;
if ($prevPage >= 1) {
	$prevPage = $album->getPhotoId($prevPage);
?>
    <td align="center" width="72" height="18">
      <a href="<?=makeGalleryUrl($albumName, $prevPage)?>"><img src="<?=$imageDir?>/nav_prev.gif" border="0" width="72" height="11" alt="Previous Photo"></a>
      </td>
    <td bgcolor="<?=$borderIn?>" width="1" height="1"><?=$pixelImage?></td>
<?
} else {
?>
    <td align="center" width="72" height="18">&nbsp;</td>
    <td width="1" height="1"><?=$pixelImage?></td>
<?
}
?>
    <td align="center" width="3000" height="18">   
<?
#-- 'page numbers' cell ---                  
echo "<span class=\"nav\">";
$current = $navigator["page"];
echo "$current (of $end)";
echo "</span>";
?>
    </td>
<?
#-- 'next' button cell ---
$nextPage = $navigator["page"] + 1;
if ($nextPage <= $navigator["maxPages"]) {
	$nextPage = $album->getPhotoId($nextPage);
?>
    <td bgcolor="<?=$borderIn?>" width="1" height="1"><?=$pixelImage?></td>
    <td align="center" width="72" height="18">
      <a href="<?=makeGalleryUrl($albumName, $nextPage)?>"><img src="<?=$imageDir?>/nav_next.gif" border="0" width="72" height="11" alt="Next Photo"></a>
      </td>
<?
} else {   
?>
    <td align="center" width="72" height="18">&nbsp;</td>
    <td width="1" height="1"><?=$pixelImage?></td>
<?
} 
?>
<?
#-- 'last' button ---
if ($navigator["page"] == $navigator["maxPages"]) {
?>
    <td width="1" height="1"><?=$pixelImage?></td>
    <td align="center" width="27" height="18">&nbsp;</td>
<?
} else {        
	$lastPage = $album->getPhotoId($navigator["maxPages"]);
?>
    <td bgcolor="<?=$borderIn?>" width="1" height="1"><?=$pixelImage?></td>
    <td align="center" width="27" height="18">
      <a href="<?=makeGalleryUrl($albumName, $lastPage)?>"><img src="<?=$imageDir?>/nav_last.gif" border="0" width="27" height="11" alt="Last Photo"></a>
    </td>
<?
} 
?>
    <td bgcolor="<?=$borderIn?>" width="1" height="1"><?=$pixelImage?></td>
  </tr>
  <tr> 
    <td colspan="11" bgcolor="<?=$borderIn?>"><?=$pixelImage?></td>
  </tr>
</table>
    
