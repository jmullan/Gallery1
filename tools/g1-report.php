<?php
	include ("../Version.php");
	include ("../nls.php");

$nls=getNLS();


$handle=opendir('../po');
while ($file = readdir($handle)) {
        if (ereg("^([a-z]{2}_[A-Z]{2})(\.[a-zA-Z0-9]+)?(\-gallery.po)$", $file, $matches)) {
		$locale=$matches[1] . $matches[2];
		if ($locale == "en_GB") continue; 

		$lines=file("../po/$file");
		$fuzzy=0;
		$untranslated=-1;
		$translated=0;
		foreach ($lines as $line) {
			if(strpos($line,'#, fuzzy') === 0) $fuzzy++;
			if(strpos($line,'msgstr') === 0) {
				if(stristr($line,'msgstr ""')) {
					$untranslated++;
				} else {
					$translated++;
				}
			}
		}
		$all=$translated+$fuzzy+$untranslated;
		$percent_done=round($translated/$all*100,2);
		$rpd=round($percent_done,0);
		$report[$locale]=array ($percent_done,$translated,$fuzzy,$untranslated,$bgcolor);
		if($rpd <50) {
			$color=dechex(255-$rpd*2). "0000";
		} else {
			$color="00" . dechex(55+$rpd*2). "00";
		}
		if (strlen($color) <6) $color="0". $color;
		$report[$locale]=array ($color, $percent_done,$translated,$fuzzy,$untranslated);
        }
}
closedir($handle);

function my_usort_function ($a, $b) {
	if ($a[1] > $b[1]) { return -1; }
	if ($a[1] < $b[1]) { return 1; }
	return 0;
}

uasort ($report, 'my_usort_function');

?>

<html>
<body>
<head>
	   <link rel="stylesheet" type="text/css" href="g1-report.css">
</head>
<h2>Localization Status Report for Gallery 1</h2>
<h2>Build : <?php echo $gallery->version ?></h2>
<table align="center" border="0" cellspacing="0" cellpadding="0">
<tr>
	<th>Language</th>
	<th>Locale</th>
	<th>Status</th>
	<th valign="bottom" style="width: 30px;">T<br/>r<br/>a<br/>n<br/>s<br/>l<br/>a<br/>t<br/>e<br/>d</th>
	<th valign="bottom" style="width: 30px;">F<br/>u<br/>z<br/>z<br/>y</th>
	<th valign="bottom" style="width: 30px;">U<br/>n<br/>t<br/>r<br/>a<br/>n<br/>s<br/>l<br/>a<br/>t<br/>e<br/>d</th>
</tr>

<?php
foreach ($report as $key => $value) {
		echo "\n<tr>";
		echo "\n\t<td>". $nls['languages'][$key] . "</td>";
		echo "\n\t<td>". $key ."</td>";
		echo "\n\t<td style=\"background-color:#". $value[0] . "\">". $value[1] ."% done</td>";
		echo "\n\t<td class=\"translated\">". $value[2] ."</td>";
		echo "\n\t<td class=\"fuzzy\">". $value [3] . "</td>";
		echo "\n\t<td class=\"untranslated\">". $value[4] ."</td>";
		echo "\t</tr>";
}
?>
</table>
</body>
</html>