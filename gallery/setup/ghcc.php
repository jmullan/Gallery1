<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2005 Bharat Mediratta
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
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * $Id$
 */
?>
<?php
require(dirname(__FILE__) . "/init.php");
require(GALLERY_SETUPDIR . "/functions.inc");
configLogin(basename(__FILE__));

/*
desc - A description of the test
test - Boolean value of the result, true = good, false = bad
failmsg - Message to display on fail
sev - Severity of a fail, 0 = warning, 1 = fatal
*/

$tests = array (0 => array ("desc" => "safe mode",
			    "test" => (ini_get ('safe_mode') == ''),
			    "failmsg" => "<b>safe_mode</b> must be disabled in php.ini",
			    "sev" => 1),
		1 => array ("desc" => "exec() enabled",
			    "test" => (! (in_array ('exec', split (',\s*', ini_get ('disable_functions'))))),
			    "failmsg" => "The <b>exec()</b> function must not be disabled by the <b>disabled_functions</b> parameter in php.ini",
			    "sev" => 1),
		2 => array ("desc" => "file_uploads enabled",
			    "test" => (ini_get ('file_uploads') != ''),
			    "failmsg" => "<b>file_uploads</b> must be enabled in php.ini",
			    "sev" => 1),
		3 => array ("desc" => "session.save_path writable",
			    "test" => ( is_dir (session_save_path()) && is_writable (session_save_path()) ),
			    "failmsg" => "<b>session.save_path</b> must be set to a valid directory and be writable by the web server user",
			    "sev" => 1),
		4 => array ("desc" => "session.use_cookies enabled",
			    "test" => (ini_get ('session.use_cookies') != ''),
			    "failmsg" => "<b>session.use_cookies must be enabled in php.ini",
			    "sev" => 1),
		5 => array ("desc" => "allow_url_fopen on",
			    "test" => (ini_get ('allow_url_fopen') != ''),
			    "failmsg" => "Gallery will not be able to fetch pictures from remote hosts",
			    "sev" => 0),
		);

if (ini_get ('upload_tmp_dir')) { // it defaults to a default system location, only test if this is set
	$tests[] = array ("desc" => "upload_tmp_dir writable",
			  "test" => ( is_dir (ini_get ('upload_tmp_dir')) && is_writable (ini_get ('upload_tmp_dir')) ),
			  "failmsg" => "<b>upload_tmp_dir</b> must be set to a valid directory and be writable by the web server user",
			  "sev" => 1);
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
<table cellpadding="5">
<?php
$warnings = false;
$fatals = false;
foreach ($tests as $arr) {
	print '<tr><th>';
	print $arr['desc'];
	print '</th>';

	print '<td>';
	print str_repeat ('&nbsp;', 10);
	print '<td>';

	if ($arr['test']) {
		print '<font color="#00aa00">Pass</font>';
	} else if (! $arr['sev']) {
		print '<font color="#e0850f">Warning -- '.$arr['failmsg'] . '</font>';
		$warnings = true;
       	} else {
	       	print '<font color="#bb0000">Fatal Warning -- ' . $arr['failmsg'] . '</font>';
		$fatals = true;
       	}

	print '</td></tr>';
}
?>
</table>
<br>
<?php
print '<b>Final Status Report: </b>';
if ($fatals) {
	print '<font color="#bb0000">Your PHP configuration flagged a <b>fatal warning</b>.  <b>Gallery will not run on this host without modifications to your PHP configuration</b>';
} else if ($warnings) {
	print '<font color="#e0850f">Your PHP configuration flagged some warnings (Gallery may lose some functionality)</font>';
} else {
	print '<font color="#00aa00">Your PHP configuration check passed with flying colors!</font>';
}	
?>
