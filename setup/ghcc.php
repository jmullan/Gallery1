<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2003 Bharat Mediratta
 *
 * This file by Andrew Lindeman
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
?>
<?php
$tests = array ();

// First element is the test, second is if it's fatal or not

$tests['safe mode disabled'] = array ( (ini_get ('safe_mode') == ''), true);
$tests['exec() function enabled'] = array (! (in_array ('exec', split (',\s*', ini_get ('disable_functions')))), true);
$tests['file_uploads enabled'] = array ( (ini_get ('file_uploads') != ''), true);
$tests['session.save_path writable'] = array (( is_dir (ini_get ('session.save_path')) && is_writable (ini_get ('session.save_path')) ), true);
$tests['session.use_cookies on'] = array ( (ini_get ('session.use_cookies') != ''), true);
$tests['allow_url_fopen on'] = array ( (ini_get ('allow_url_fopen') != ''), 'Gallery will not be able to fetch pictures from remote hosts');

if (ini_get ('upload_tmp_dir')) { // it defaults to a default system location, only test if this is set
       	$tests['upload_tmp_dir writable'] = array (( is_dir (ini_get ('upload_tmp_dir')) && is_writable (ini_get ('upload_tmp_dir')) ), true);
}

?>
<h1 align="center">Gallery Host Compatability Checker</h1>
This scripts tests many of the basic requirements for Gallery to
run on your system.  It is not a catch all, but does check most
settings that Gallery requires.  <i>It does not check for
ImageMagick and NetPBM (or other programs Gallery can use)</i>
<br><br>
If any of these tests fail with a <b>fatal warning</b>, Gallery
will not run on your host
<hr width="50%">
<table>
<?php
foreach ($tests as $desc => $pass) {
       	print '<tr><th>';
       	print $desc;
       	print '</th>';

	print '<td>';

	if ($pass[0] && $pass[1]) {
	       	print '<font color="#00bb00">Pass</font>';
       	} else if (! $pass[0] && $pass[1] !== true) {
	       	print '<font color="#ff7050">Warning</font> -- '.$pass[1];
       	} else {
	       	print '<font color="#bb0000">Fail</font>';
       	}

	print '</td></tr>';
}
?>
</table>
