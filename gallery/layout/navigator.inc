<?
// Calculate the beginning and ending of the navigator range
$begin = max($navigator["page"] - $navigator["spread"], 1);
$end = min($navigator["page"] + $navigator["spread"], $navigator["maxPages"]);

// If we're pinned at the beginning or the end, expand as far as we can
// in the opposite direction
if ($begin == 1 && $end <= $navigator["maxPages"]) {
	$end = min(2 * $navigator["spread"], $navigator["maxPages"]);
}
if ($end == $navigator["maxPages"]) {
	$begin = max(1, $end - 2 * $navigator["spread"]);
}

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
    <td colspan="11" bgcolor="<?=$borderIn?>" height="1"><?=$pixelImage?></td>
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
?>
    <td align="center" width="27" height="18"><span class="nav"> 
      <a href=<?=$navigator[url]?>?<?=$navigator[pageVar]?>=1><img src="<?=$imageDir?>/nav_first.gif" border="0" width="27" height="11" alt="First Page"></a>
      </span></td>
    <td bgcolor="<?=$borderIn?>" width="1" height="1"><?=$pixelImage?></td>
<?
} 
?>
<?
#-- 'previous' button cell ---
$prevPage = $navigator["page"] - 1;
if ($prevPage >= 1) {
?>
    <td align="center" width="72" height="18"><span class="nav">
      <a href=<?=$navigator[url]?>?<?=$navigator[pageVar]?>=<?=$prevPage?>><img src="<?=$imageDir?>/nav_prev.gif" border="0" width="72" height="11" alt="Previous Page"></a>
      </span></td>
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
$current = $navigator["page"];
echo "<span class=\"nav\">";
for ($i = $begin; $i <= $end; $i++) {
	if ($i == $current) {
		$number = "<b>$i</b>";
		$leftdot = "<img src=\"$imageDir/nav_dot_left.gif\" width=\"8\" height=\"11\">";
	} else {
		$number = "<a href=".$navigator[url]."?".$navigator[pageVar]."=$i>$i</a>";
		if (($i-1) == $navigator["page"]) {
			$leftdot = "<img src=\"$imageDir/nav_dot_right.gif\" width=\"8\" height=\"11\">";
		} else {
			$leftdot = "<img src=\"$imageDir/nav_dot.gif\" width=\"8\" height=\"11\">";
		}
	}

	echo "&nbsp;$leftdot&nbsp;";
	echo "$number";
}
echo "</span>";
if ($end == $current) {
	$rightdot = "<img src=\"$imageDir/nav_dot_right.gif\" width=\"8\" height=\"11\">";
} else {
	$rightdot = "<img src=\"$imageDir/nav_dot.gif\" width=\"8\" height=\"11\">";
}
echo "&nbsp;$rightdot&nbsp;";
?>
    </td>
<?
#-- 'next' button cell ---
$nextPage = $navigator["page"] + 1;
if ($nextPage <= $navigator["maxPages"]) {
?>
    <td bgcolor="<?=$borderIn?>" width="1" height="1"><?=$pixelImage?></td>
    <td align="center" width="72" height="18"><span class="nav">
      <a href=<?=$navigator[url]?>?<?=$navigator[pageVar]?>=<?=$nextPage?>><img src="<?=$imageDir?>/nav_next.gif" border="0" width="72" height="11" alt="Next Page"></a>
      </span></td>
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
?>
    <td bgcolor="<?=$borderIn?>" width="1" height="1"><img src="<?=$imageDir?>/pixel_trans.gif" width="1" height="1"></td>
    <td align="center" width="27" height="18"><span class="nav">
      <a href=<?=$navigator[url]?>?<?=$navigator[pageVar]?>=<?=$navigator[maxPages]?>><img src="<?=$imageDir?>/nav_last.gif" border="0" width="27" height="11" alt="Last Page"></a>
      </span></td>
<?
} 
?>
    <td align="right" bgcolor="<?=$borderIn?>" width="1" height="1"><?=$pixelImage?></td>
  </tr>
  <tr> 
    <td colspan="11" bgcolor="<?=$borderIn?>"><?=$pixelImage?></td>
  </tr>
</table>
    
