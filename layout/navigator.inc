<table border=0 width=<?=$navigator["fullWidth"]?>>
<tr width=<?=$navigator["fullWidth"]/4?>>
<td>
<font size=+0 face=<?=$album->fields["font"]?>>
<?
if ($navigator["page"] == 1) {
	echo "&nbsp;";
} else {
	echo "<a href=view_album.php?set_page=1><< first page</a>";
}
?>

</td>
<td width=<?=$navigator["fullWidth"]/2?> align=center>

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

if ($end > $begin) {
	for ($i = $begin; $i <= $end; $i++) {
		if ($i == $navigator["page"]) {
			$size = "+2";
		} else {
			$size = "+0";
		}
	
		?>
			<font size=<?=$size?> face=<?=$album->fields["font"]?>>
			<a href=view_album.php?set_page=<?=$i?>><?=$i?></a>
		<?
	}
}
?>

</td>
<td width=<?=$navigator["fullWidth"]/4?> align=right>
<font size=+0 face=<?=$album->fields["font"]?>>
<?
if ($navigator["page"] == $navigator["maxPages"]) {
        echo "&nbsp;";
} else {
	echo "<a href=view_album.php?set_page=$navigator[maxPages]>last page >></a>";
}
?>
</td>

</tr>
</table>
