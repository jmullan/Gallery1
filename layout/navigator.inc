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
?>

<table border=0 width=<?=$navigator["fullWidth"] . $navigator["widthUnits"]?>>
 <tr>
  <td valign=bottom width=<?=.124 * $navigator["fullWidth"] . $navigator["widthUnits"]?>>
   <font size=+0 face=<?=$album->fields["font"]?>>
<?
if ($navigator["page"] == 1) {
	echo "&nbsp;";
} else {
	echo "<a href=$navigator[url]?set_$navigator[pageVar]=1>[<< first]</a>";
}

if ($end > $begin) {
?>
  </td>
  <td valign=bottom width=<?=.125 * $navigator["fullWidth"] . $navigator["widthUnits"]?> align=center>

<?
	$prevPage = $navigator["page"] - 1;
	if ($prevPage >= 1) {
		echo "<a href=$navigator[url]?set_$navigator[pageVar]=$prevPage>[< back]</a>";
	} else {
		echo "&nbsp;";
	}
?>
  </td>
  <td valign=bottom width=<?=.5 * $navigator["fullWidth"] . $navigator["widthUnits"]?> align=center>
<?
	for ($i = $begin; $i <= $end; $i++) {
		if ($i == $navigator["page"]) {
			$size = "+2";
		} else {
			$size = "+0";
		}
	
		?>
			<font size=<?=$size?> face=<?=$album->fields["font"]?>>
			<a href=<?=$navigator[url]?>?set_<?=$navigator[pageVar]?>=<?=$i?>><?=$i?></a>
			</font>
		<?
	}
?>
  </td>
  <td valign=bottom width=<?=.125 * $navigator["fullWidth"] . $navigator["widthUnits"]?> align=center>
<?  
	$nextPage = $navigator["page"] + 1;
	if ($nextPage <= $navigator["maxPages"]) {
		echo "<a href=$navigator[url]?set_$navigator[pageVar]=$nextPage>[next >]</a>";
	} else {
		echo "&nbsp;";
	}
}
?>

  </td>
  <td valign=bottom width=<?=.125 * $navigator["fullWidth"] . $navigator["widthUnits"]?> align=right>
   <font size=+0 face=<?=$album->fields["font"]?>>
<?
if ($navigator["page"] == $navigator["maxPages"]) {
        echo "&nbsp;";
} else {
	echo "<a href=$navigator[url]?set_$navigator[pageVar]=$navigator[maxPages]>[last >>]</a>";
}
?>
  </td>
 </tr>
 <tr>
  <td align=center colspan=5>
   <font size=-2>(Click here to switch to a new page)</font>
  </td>
 </tr>
</table>
