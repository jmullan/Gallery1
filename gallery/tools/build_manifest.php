<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2005 Bharat Mediratta
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
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 * $Id$
 */

/*
 * This script must be run from command line, in directory gallery/
 * eg php tools/build_manifest.php
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
    /*
     * There are some exceptions.  We don't want to require these directories
     *   po
     *
     * because they're not critical to the proper functioning of gallery
     */
    if (preg_match('{^(po)/}', $file)) {
	continue;
    }
    
    fwrite($fd, "\$versions['$file']='$version';\n");
}	

fwrite($fd, "?>\n");
fclose($fd);
print "Done\n";

function getManifestFiles($dir) {
    $results = array();
    
    if (fs_file_exists("$dir/CVS/Entries")) {
	$fd = fs_fopen("$dir/CVS/Entries", "rb");
	while ($line = fgets($fd, 4096)) {
	    if ($line[0] == 'D') {
		preg_match('|^D/(.*?)/|', $line, $regs);
		if (!empty($regs)) {
		    $results = array_merge($results, getManifestFiles("$dir/$regs[1]"));
		}
	    } else {
		preg_match('|^/(.*?)/|', $line, $regs);
		$file = substr("$dir/$regs[1]", 2);
		$version = getCVSVersion($file);
		if (!empty($version)) {
		    $results[$file] = $version;
		}
	    }
	}
	fclose($fd);
    }

    return $results;
}

?>
