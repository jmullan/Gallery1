<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2007 Bharat Mediratta
 * This file by Joan McGalliard.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * $Id$
*/

/*
* This script must be run from command line, in directory gallery/
* eg php tools/build_manifest_1_3.php
*/
?>
<?php

if (php_sapi_name() != "cli") {
	print _("This page is for development use only.");
	print "<br>";
	exit;
}

include (dirname(dirname(__FILE__)) . '/util.php');
include (dirname(dirname(__FILE__)) . '/platform/fs_unix.php');

if (!fs_is_readable("setup")) {
	print "Cannot build manifest unless in config mode";
	print "\n";
	exit (2);
}

$files = getManifestFiles(".");

$outfile="manifest.inc";
copy("setup/gpl.txt", $outfile);
$fd=fopen($outfile, "a");

fwrite($fd, "<?php\n\n");
fwrite($fd, "/*\n * DO NOT EDIT!!!  This file is created by build_manifest.php.\n * Edit that file and re-run via command line to modify this.\n */\n\n");
fwrite($fd, "\$versions=array();\n");

ksort($files);
foreach ($files as $file => $version) {
	fwrite($fd, "\$versions['$file']='$version';\n");
}

fwrite($fd, "?>\n");
fclose($fd);
print "\nDone\n";

function getManifestFiles($folder) {
	$results = array();
	$filename = "$folder/.svn/entries";
	$skipfolder = array('docs', 'po', 'tools', 'locale');
	$skipfiles = array('ChangeLog.archive.gz', '.htaccess');

	printf("\nFolder: %s", $folder);

	if(in_array(substr($folder,2), $skipfolder)) {
		echo " ----- Skipping -----";
		return array();
	}

	if (fs_file_exists($filename)) {
		$data = fs_file_get_contents($filename);
		$parser = xml_parser_create();
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parse_into_struct($parser, $data, $values, $tags);
		xml_parser_free($parser);

		$i = 0;
		foreach($values as $nr => $content) {
			if($content['tag'] == 'entry') {
				echo ".";
				if($content['attributes']['kind'] == 'file') {
					$i++;
					$filename = $content['attributes']['name'];
					$fullFilename = $folder . '/'. $filename;

					if(in_array($filename, $skipfiles)) {
						continue;
					}

					$localRevision = getSVNRevision($fullFilename);
					/*
					printf("\nName: %s, Repository Revision: %s, LocalRevision: %s",
					$content['attributes']['name'],
					$content['attributes']['committed-rev'],
					$localRevision
					);
					*/
					$results[substr($fullFilename,2)] = $localRevision;
				}
				elseif($content['attributes']['kind'] == 'dir' && !empty($content['attributes']['name'])) {
					//printf("\nDir: %s<ul>", $content['attributes']['name']);
					$results = array_merge($results, getManifestFiles($folder . '/'. $content['attributes']['name']));
					//echo "</ul>";
				}
			}
		}
	}

	return $results;
}

?>
