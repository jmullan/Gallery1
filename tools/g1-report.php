<?php
	include ("../Version.php");
	include ("../nls.php");

$nls=getNLS();

$handle=opendir('../po');
while ($file = readdir($handle)) {
        if (ereg("^([a-z]{2}_[A-Z]{2})(\.[a-zA-Z0-9]+)?(\-gallery.po)$", $file, $matches)) {
		$locale=$matches[1] . $matches[2];
		if ($locale == "en_GB") continue; 
		$i++;

		$lines=file("../po/$file");
		$fuzzy=0;
		$untranslated=-1;
		$translated=0;
		$obsolete=0;
		foreach ($lines as $line) {
			if(strpos($line,'msgstr') === 0) {
				if(stristr($line,'msgstr ""')) {
					$untranslated++;
				} else {
					$translated++;
				}
			} elseif (strpos($line,'#, fuzzy') === 0) {
				$fuzzy++;
			} elseif (strpos($line,'#~ ') === 0) {
				$obsolete++;
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
		$report[$locale]=array ($color,$percent_done,$translated,$fuzzy,$untranslated,$obsolete);
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
	<th>&nbsp;</th>
	<th>Language</th>
	<th>Locale</th>
	<th>Status</th>
	<th valign="bottom" style="width: 30px;">T<br/>r<br/>a<br/>n<br/>s<br/>l<br/>a<br/>t<br/>e<br/>d</th>
	<th valign="bottom" style="width: 30px;">F<br/>u<br/>z<br/>z<br/>y</th>
	<th valign="bottom" style="width: 30px;">U<br/>n<br/>t<br/>r<br/>a<br/>n<br/>s<br/>l<br/>a<br/>t<br/>e<br/>d</th>
	<th valign="bottom" style="width: 30px;">O<br/>b<br/>s<br/>o<br/>l<br/>e<br/>t<br/>e</th>
</tr>

<?php
$i=0;
$j=0;
foreach ($report as $key => $value) {
	$i++;
	if ($i%2==0) {
		$color="#ffffff";
		$nr=1;
	} else {
		$color="#CECECE";
		$nr=2;
	}

	echo "\n<tr>";
	if ($report[$key][1] != $report[$last_key][1]) { 
		$lfd_nr++;	
		echo "\n\t<td style=\"background-color:$color\">$lfd_nr.)</td>";
	} else {
		echo "\n\t<td style=\"background-color:$color\">&nbsp;</td>";
	}
	echo "\n\t<td style=\"background-color:$color\">". $nls['language'][$key] . "</td>";
	echo "\n\t<td style=\"background-color:$color\">". $key ."</td>";
	echo "\n\t<td style=\"background-color:#". $value[0] . "\">". $value[1] ."% done</td>";
	echo "\n\t<td class=\"translated$nr\">". $value[2] ."</td>";
	echo "\n\t<td class=\"fuzzy$nr\">". $value [3] . "</td>";
	echo "\n\t<td class=\"untranslated$nr\">". $value[4] ."</td>";
	echo "\n\t<td class=\"obsolete$nr\">". $value[5] ."</td>";
	echo "\t</tr>";
	$last_key=$key;
}
?>
</table>
</body>
</html>