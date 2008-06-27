<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2007 Bharat Mediratta
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

    echo doctype();
?>
<html>
<head>
  <title> <?php echo gTranslate('config', "Check Versions"); ?> </title>
  <?php common_header(); ?>
  
  <style type="text/css">
	.shortdesc { width:30% }
  </style>
  
  <script type="text/javascript" src="../js/toggle.js"></script>
  </head>

<body dir="<?php echo $gallery->direction ?>">
 <div class="header"><?php echo gTranslate('config', "Check Versions") ?></div>
<?php    configLogin(basename(__FILE__)); ?>

<div class="sitedesc"><?php
	echo sprintf(gTranslate('config', "This page gives you information about the version of each necessary %s file. "),Gallery());
	echo "\n<br>";
	echo gTranslate('config', "If you see any error(s), we highly suggest to get the actual version of those files.");
?></div>
<br>
<?php

list($oks, $errors, $warnings) = checkVersions(false);

$tests = array(
	'errors' => array(
		'text' => gTranslate('config', "One file is missing, corrupt or older than expected.", "%d files are missing, corrupt or older than expected.",  count($errors), gTranslate('config', "All files okay."), true),
		'class' => 'errorpct',
		'hinttext' => sprintf(gTranslate('config', "There are problems with the following files.  Please correct them before configuring %s."), Gallery())
		),
	'warnings' => array(
		'text' => gTranslate('config', "One file is more recent than expected.", "%d files are more recent than expected.", count($warnings), gTranslate('config', "All files okay."), true),
		'class' => 'warningpct',
		'hinttext' => sprintf(gTranslate('config', "The following files are more up-to-date than expected for this version of %s.  If you are using pre-release code, this is OK."), Gallery())
		),
	'oks' => array(
		'text' => gTranslate('config', "One file is up-to-date.", "%d files are up-to-date.", count($oks),  gTranslate('config', "All files are up-to-date."), true),
		'class' => 'successpct',
		'hinttext' => gTranslate('config', "The following files are up-to-date.")
		)
);

foreach($tests as $testname => $args) {
    if  ($$testname) { ?>
<div class="inner">
  <div style="white-space:nowrap;">
    <a href="#" onClick="gallery_toggle('<?php echo $testname; ?>'); return false;"><?php echo gImage('expand.gif', gTranslate('config', "Show/hide more information"), array('id' => "toogleBut_$testname")); ?></a>
    <span class="<?php echo $args['class']; ?>"><?php echo $args['text']; ?></span>
  </div>
  <div style="width:100%; display:none;" id="toggleFrame_<?php echo $testname; ?>">
    <table>
	  <tr>
        <td class="desc" colspan="2"><?php echo $args['hinttext']; ?></td>
	  </tr>
	  <?php
	  foreach ($$testname as $file => $result) {
	    echo "\n<tr>";
	    echo "\n\t<td class=\"shortdesc\">$file:</td>";
	    echo "\n\t<td class=\"desc\">$result</td>";
	    echo "\n</tr>";
	  }
      ?>
      </table>
  </div>
</div>
<?php
    }
}
?>

<p align="center"><?php echo returnToConfig(); ?></p>

</body>
</html>
