<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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

require_once(dirname(__FILE__) . '/init.php');

printPopupStart(gTranslate('config', "Check Versions"));

configLogin(basename(__FILE__));

?>
<script type="text/javascript" src="../js/toggle.js.php"></script>

<div class="g-sitedesc left"><?php
	printf(gTranslate('config', "This page gives you information about the version of each necessary %s file. "),Gallery());
	echo "\n<br>";
	echo gTranslate('config', "If you see any errors, we highly suggest to get the actual version of those files.");
?></div>
<br>
<?php

$versionStatus = checkVersions(false);

$tests = array(
	'missing' => array(
		'text' => gTranslate('config',
				"One file is missing or corrupt.",
				"%d files are missing or corrupt.",
				count($versionStatus['missing']),
				'', true),
		'type' => 'error',
		'hinttext' => sprintf(gTranslate('config', "There are problems with the following files.  Please correct them before configuring %s."), Gallery())
	),
	'older' => array(
		'text' => gTranslate('config',
				"One file is older than expected.",
				"%d files are older than expected.",
				count($versionStatus['older']),
				'', true),
		'type' => 'error',
		'hinttext' => sprintf(gTranslate('config', "The following files are older than expected for this version of %s. Please update them as soon as possible."), Gallery())
	),
	'unkown' => array(
		'text' => gTranslate('config',
				"One file is not in the manifest file, but has a Version number.",
				"%d files are not in the manifest file, but have a Version number.",
				count($versionStatus['unkown']),
				'',
				true),
		'type' => 'warning',
		'hinttext' => sprintf(gTranslate('config', "There are problems with the following files.  Please correct them before configuring %s."), Gallery())
	),
	'newer' => array(
		'text' => gTranslate('config',
			"One file is more recent than expected.",
			"%d files are more recent than expected.",
			count($versionStatus['newer']),
			'', true),
		'type' => 'warning',
		'hinttext' => sprintf(gTranslate('config', "The following files are more up-to-date than expected for this version of %s.  If you are using pre-release code, this is OK."), Gallery())
	),
	'ok' => array(
		'text' => gTranslate('config',
				"One file is up-to-date.",
				"%d files are up-to-date.",
				count($versionStatus['ok']),
				gTranslate('config', "All files are up-to-date."),
				true),
		'type' => 'success',
		'hinttext' => gTranslate('config', "The following files are up-to-date.")
	),
);

foreach($tests as $testname => $args) {
	if  (!empty($versionStatus[$testname])) { ?>
<div class="g-notice left">
	<a href="#" style="float: left;" onClick="gallery_toggle('<?php echo $testname; ?>'); return false;"><?php echo gImage('expand.gif', gTranslate('config', "Show/hide more information"), array('id' => "toggleBut_$testname")); ?></a>
	<?php echo infobox(array(array('type' => $args['type'], 'text' => $args['text'])), '', false); ?>
  <div style="width:100%; display:none;" id="toggleFrame_<?php echo $testname; ?>">
	<table>
	  <tr>
		<td class="g-sitedesc" colspan="2"><?php echo $args['hinttext']; ?></td>
	  </tr>
	  <?php
	  foreach ($versionStatus[$testname] as $file => $result) {
		echo "\n<tr>";
		echo "\n\t<td class=\"g-shortdesc\">$file:</td>";
		echo "\n\t<td class=\"g-desc\">$result</td>";
		echo "\n</tr>";
	  }
	  ?>
	  </table>
  </div>
</div>
<?php
	}
}

if(!empty($versionStatus['fail'])) {
	foreach($versionStatus['fail'] as $error => $message) {
		echo gallery_error($message);
	}
}
?>

</div>

<div class="center">
	<?php echo returnToDiag(); ?><?php echo returnToConfig(); ?>
</div>

</body>
</html>
