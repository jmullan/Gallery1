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

$filename="./g1-report.xml";

if (!$handle = fopen($filename, "w+")) {
	echo _("Unable to open ") . $filename ;
	exit;
}

setlocale(LC_ALL,"de_DE");
fwrite($handle,"<report date=\"".strftime("%x",time()). "\" time=\"".strftime("%X",time()). "\" build=\"$gallery->version\">");

$i=0;
$j=0;
foreach ($report as $key => $value) {

	$i++;
	if ($i%2==0) {
		$scheme="light";
	} else {
		$scheme="dark";
	}

	if ($report[$key][1] != $report[$last_key][1]) { 
		$lfd_nr++;
		$line=$lfd_nr;	
	} else {
		$line="";
	}

	fwrite($handle,"\n\t<locale id=\"$key\" scheme=\"$scheme\">");
	fwrite($handle,"\n\t\t<nr scheme=\"$scheme\">$line</nr>");
	fwrite($handle,"\n\t\t<language scheme=\"$scheme\">". $nls['language'][$key] ."</language>");
	fwrite($handle,"\n\t\t<percent_done style=\"background-color:#". $value[0] ."\">$value[1] %</percent_done>");
	fwrite($handle,"\n\t\t<translated scheme=\"translated_$scheme\">$value[2]</translated>");
	fwrite($handle,"\n\t\t<fuzzy scheme=\"fuzzy_$scheme\">$value[3]</fuzzy>");
	fwrite($handle,"\n\t\t<untranslated scheme=\"untranslated_$scheme\">$value[4]</untranslated>");
	fwrite($handle,"\n\t\t<obsolete scheme=\"obsolete_$scheme\">$value[5]</obsolete>");
	fwrite($handle,"\n\t</locale>");


	$last_key=$key;	
}
fwrite($handle,"\n</report>");
fclose($handle);

exec("/usr/bin/xsltproc g1-report.xslt g1-report.xml > g1-report.html");
?>