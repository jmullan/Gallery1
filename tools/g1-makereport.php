<?php
	$GALLERY_BASEDIR="../";

	include ($GALLERY_BASEDIR . "Version.php");

        if(substr(PHP_OS, 0, 3) == 'WIN') {
		include($GALLERY_BASEDIR . "platform/fs_win32.php");
        } else {
		include($GALLERY_BASEDIR . "platform/fs_unix.php");
	}
	include ($GALLERY_BASEDIR . "util.php");
	load_languages();

$total=array();
$eastergg=0;
foreach ($gallery->nls['language'] as $locale => $langname) {
	$dirname=$locale;
	if ($locale == "en_GB" || $locale == "en_US") continue; 
	$total['lang']++;
	$dir=opendir($GALLERY_BASEDIR . "locale/$dirname");
	$tpd=0;
	$cc=0;
	while ($file = readdir($dir)) {
		if (ereg("^([a-z]{2}_[A-Z]{2})(\.[a-zA-Z0-9]+)?(\-gallery.+\.po)$", $file, $matches)) {
			$pos=strpos($file,"gallery_");
			$component=substr($file,$pos+8,-3);

			$lines=file($GALLERY_BASEDIR . "locale/$dirname/$file");
			$fuzzy=0;
			$untranslated=-2;
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
			$all=$translated+$untranslated;
			$percent_done=round(($translated-$fuzzy)/$all*100,2);
			$rpd=round($percent_done,0);

			if ($component =="config" ) {
				$border=40;
			} else {
				$border=50;
			}
			if($rpd < $border) {
				$color=dechex(255-$rpd*2). "0000";
			} else {
				$color="00" . dechex(55+$rpd*2). "00";
			}
			if (strlen($color) <6) $color="0". $color;
			$tpd+=$percent_done;
			if ($percent_done == 100) $easteregg++;
			$report[$locale][$component]=array ($color,$percent_done,$all,$translated,$fuzzy,$untranslated,$obsolete);
			$total['percent_done'] = $total['percent_done'] + $percent_done;
			$cc++;

			$rtpd=round($tpd/$cc,0);
			if($rtpd <50) {
				$color=dechex(255-$rtpd*2). "0000";
			} else {
				$color="00" . dechex(55+$rtpd*2). "00";
			}
			if (strlen($color) <6) $color="0". $color;
			$report[$locale]['tpd']=$tpd/$cc;
			$report[$locale]['color']=$color;
		}
	}
	closedir($dir);
}

$total['percent_done'] = round($total['percent_done'] / ($total['lang']*2),2);

function my_usort_function ($a, $b) {
	if ($a['tpd'] > $b['tpd']) { return -1; }
	if ($a['tpd'] < $b['tpd']) { return 1; }
	return 0;
}

uasort ($report, 'my_usort_function');

//print_r($report);

//if ($easteregg ==1 && $report['de_DE'][1] == 100) $report['de_DE'][1]=substr($gallery->version,-3);

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
	krsort($value);

	$i++;
	if ($i%2==0) {
		$scheme="light";
	} else {
		$scheme="dark";
	}
	
	if ($value['tpd'] != $last_value['tpd']) { 
		$lfd_nr++;
		$line=$lfd_nr;	
	} else {
		$line="";
	}

	fwrite($handle,"\n\t<locale id=\"$key\" scheme=\"$scheme\">");
	fwrite($handle,"\n\t\t<nr scheme=\"$scheme\">$line</nr>");
	fwrite($handle,"\n\t\t<language scheme=\"$scheme\">". $gallery->nls['language'][$key] ."</language>");
	fwrite($handle,"\n\t\t<tpd style=\"background-color:#". $value['color'] ."\">". $value['tpd'] ."</tpd>");
	
	foreach ($value as $subkey => $subvalue) {
		if ($subkey =="tpd" or $subkey=="cc" or $subkey=="color") continue;
		fwrite($handle,"\n\t\t<component scheme=\"$scheme\">");
		fwrite($handle,"\n\t\t\t<name scheme=\"$scheme\">". $subkey ."</name>");
		fwrite($handle,"\n\t\t\t<percent_done style=\"background-color:#". $subvalue[0] ."\">$subvalue[1] %</percent_done>");
		fwrite($handle,"\n\t\t\t<lines scheme=\"$scheme\">". $subvalue[2] ."</lines>");
		fwrite($handle,"\n\t\t\t<translated scheme=\"translated_$scheme\">$subvalue[3]</translated>");
		fwrite($handle,"\n\t\t\t<fuzzy scheme=\"fuzzy_$scheme\">$subvalue[4]</fuzzy>");
		fwrite($handle,"\n\t\t\t<untranslated scheme=\"untranslated_$scheme\">$subvalue[5]</untranslated>");
		fwrite($handle,"\n\t\t\t<obsolete scheme=\"obsolete_$scheme\">$subvalue[6]</obsolete>");
		fwrite($handle,"\n\t\t</component>");
	}

	fwrite($handle,"\n\t</locale>");

	$last_value=$value;	
}
fwrite($handle,"\n\t<total>");
fwrite($handle,"\n\t\t<languages>". $total['lang'] ."</languages>");
fwrite($handle,"\n\t\t<t_percent_done align=\"right\">". $total['percent_done'] ."</t_percent_done>");
fwrite($handle,"\n\t</total>");
fwrite($handle,"\n</report>");
fclose($handle);

exec("/usr/bin/xsltproc g1-report.xslt g1-report.xml > g1-report.html");
?>