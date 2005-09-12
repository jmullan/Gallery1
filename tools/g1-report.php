<?php
/*
 * $RCSfile$
 *
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2005 Bharat Mediratta
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
 */

/*
 * Authors: Jens Tkotz, Bharat Mediratta
 */

include (dirname(dirname(__FILE__)) . '/Version.php');
if(substr(PHP_OS, 0, 3) == 'WIN') {
	include (dirname(dirname(__FILE__)) . '/platform/fs_win32.php');
} else {
	include(dirname(dirname(__FILE__)) . '/platform/fs_unix.php');
}

include (dirname(dirname(__FILE__)) . '/util.php');

/*
 * Turn down the error reporting to just critical errors for now.
 */
error_reporting(E_ALL & ~E_NOTICE);

$poFiles = findPoFiles(dirname(dirname(__FILE__)) . '/locale');
list ($reportData, $total_percentDone) = parsePoFiles($poFiles);
require(dirname(__FILE__) . '/include/main.inc');
exit;

function findPoFiles($dir) {
	$handle=fs_opendir($dir);
	while ($file = readdir($handle)) {
        	if (preg_match("/^([a-z]{2}_[A-Z]{2})/", $file)) {
                	if ($file == "en_GB" || $file == "en_US") continue;
			$subdir=opendir("$dir/$file");
        	        while ($subfile = readdir($subdir)) {
				if (preg_match("/\.po$/", $subfile)) {
					$results[] = "$dir/$file/$subfile";	
				}
			}
		}
	}
return $results;
}

function parsePoFiles($poFiles) {

    /*
     * Parse each .po file for relevant statistics and gather it together into a
     * single data structure.
     */

    $nls=getNLS();
    $poData = array();
    $seenPlugins = array();
    $maxMessageCount = array();
    foreach ($poFiles as $poFile) {
	preg_match("/^(.*?)-gallery_(.*?).po$/", basename($poFile), $matches);
	list ($plugin, $locale) = array($matches[2], $matches[1]);
	$seenPlugins[$plugin] = 1;

	$fuzzy = 0;
	$translated = 0;
	$untranslated = 0;
	$obsolete = 0;
	/*
	 * Untranslated:
	 *   msgid "foo"
	 *   msgstr ""
	 *
	 * Translated:
	 *   msgid "foo"
	 *   msgstr "bar"
	 *
	 * Translated:
	 *   msgid "foo"
	 *   msgstr ""
	 *   "blah blah blah"
	 *
	 * Untranslated: 
	 *   msgid "foo"
	 *   msgstr[0] ""
	 *   msgstr[1] ""
	 *   msgstr[2] ""
	 *
	 * Translated:
	 *   msgid "foo"
	 *   msgstr[0] "bar1"
	 *   msgstr[1] "bar2"
	 *   msgstr[2] "bar3"
	 *
	 * Translated, Fuzzy:
	 *   # fuzzy
	 *   msgid "foo"
	 *   msgstr "bar"
	 *  
	 * Deleted, Fuzzy:
	 *   # fuzzy
	 *   #~ msgid "foo"
	 *   #~ msgstr "bar"
	 *  
	 */
	$msgId = null;
	$nextIsFuzzy = 0;
	$lastLineWasEmptyMsgStr = 0;
	$lastLineWasEmptyMsgId = 0;
	foreach (file($poFile) as $line) {
	    /*
	     * Scan for:
	     *   msgid "foo bar"
	     *
	     * and:
	     *   msgid ""
	     *   "foo bar"
	     */
	    if (preg_match('/^msgid "(.*)"/', $line, $matches)) {
		if (empty($matches[1])) {
		    $lastLineWasEmptyMsgId = 1;
		} else {
		    $msgId = $line;
		}
		continue;
	    }

	    /*
	     * Scan for:
	     *   msgid ""
	     *   "foo bar"
	     */
	    if ($lastLineWasEmptyMsgId) {
		if (preg_match('/^\s*"(.*)"/', $line, $matches)) {
		    $msgId = $line;
		}
		$lastLineWasEmptyMsgId = 0;
		continue;
	    }

	    if (strpos($line, '#, fuzzy') === 0) {
		$nextIsFuzzy = 1;
		continue;
	    }	    

	    if (preg_match('/^#~ msgid "(.*)"/', $line, $matches)) {
		$obsolete++;
		$nextIsFuzzy = 0;
	    }

	    /*
	     * Scan for:
	     *   msgstr ""
	     *   "foo bar"
	     */
	    if ($lastLineWasEmptyMsgStr) {
		if (preg_match('/^\s*".+"/', $line)) {
		    if ($nextIsFuzzy) {
			$fuzzy++;
		    }
		    $translated++;
		} else {
		    if ($nextIsFuzzy) {
			print "ERROR: DISCARD FUZZY for [$locale, $plugin, $msgId]<br>";
		    }
		    $untranslated++;
		}
		$msgId = null;
		$nextIsFuzzy = 0;
		$lastLineWasEmptyMsgStr = 0;
	    }
		
	    /*
	     * Scan for:
	     *   msgstr "foo bar"
	     *
	     * or:
	     *   msgstr ""
	     *   "foo bar"
	     */
	    if (!empty($msgId)) {
		if (preg_match('/^msgstr/', $line)) {
		    if (preg_match('/^msgstr(.*)""/', $line)) {
			$lastLineWasEmptyMsgStr = 1;
		    } else {
			if ($nextIsFuzzy) {
			    $fuzzy++;
			}
			$translated++;
			$msgId = null;
			$nextIsFuzzy = 0;
		    }
		}
	    }
	}

	$total = $translated + $untranslated;
	$percentDone = round(($translated - $fuzzy) / $total * 100,2);
	$poData[$locale]['plugins'][$plugin] = array('translated' => $translated,
						     'untranslated' => $untranslated,
						     'total' => $total,
						     'fuzzy' => $fuzzy,
						     'obsolete' => $obsolete,
						     'percentDone' => $percentDone);

	/* Keep track of the largest message count we've seen per plugin */
	if (empty($maxMessageCount[$plugin]) || $total > $maxMessageCount[$plugin]) {
	    $maxMessageCount[$plugin] = $total;
	}
    }

    /* Overall total message count */
    $overallTotal = array_sum(array_values($maxMessageCount));

    $total_percentDone=array();
    foreach (array_keys($poData) as $locale) {
		$pluginTotal = 0;
	
		/* Fill in any missing locales */
		foreach (array_keys($seenPlugins) as $plugin) {
			if (!isset($poData[$locale]['plugins'][$plugin])) {
				$poData[$locale]['plugins'][$plugin]['missing'] = 1;
				$poData[$locale]['plugins'][$plugin]['percentDone'] = 0;
			} else {
				/*
				 * debug
				printf("[$locale, $plugin] [%d, %d]<br>",
				$poData[$locale]['plugins'][$plugin]['translated'],
				$poData[$locale]['plugins'][$plugin]['fuzzy']);
				*/
				$pluginTotal += $poData[$locale]['plugins'][$plugin]['translated'] - $poData[$locale]['plugins'][$plugin]['fuzzy'];
				$total_percentDone[$plugin] += $poData[$locale]['plugins'][$plugin]['percentDone'];
			}
		}
		uasort($poData[$locale]['plugins'], 'sortByPercentDone');

		/* Figure out total percentage */
		$poData[$locale]['percentDone'] = round($pluginTotal / $overallTotal * 100,2);
		
		$total_percentDone['all'] += $poData[$locale]['percentDone'];

		/* Set Language Name */
		$poData[$locale]['langname'] = $nls['language'][$locale];
    }

    	/* Sort locales by overall total */
	uasort($poData, 'sortByPercentDone');

	/* Sort totals by total :) */
	uasort($total_percentDone, 'sortByPercentDone');
    
    return array($poData,$total_percentDone);
}

function sortByPercentDone($a, $b) {
    if ($a['percentDone'] == $b['percentDone']) {
	return 0;
    }
    return ($a['percentDone'] < $b['percentDone']) ? 1 : -1;
}


function percentColor($percent) {
    $border=50;
    if ($percent < $border) {
	$color = dechex(255 - $percent * 2) . "0000";
    } else {
	$color= "00" . dechex( 55 + $percent * 2 ). "00";
    }
    if (strlen($color) <6) {
	$color= "0" . $color;
    }

    return $color;
}

?>
