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

if (isset($_REQUEST['destroy'])) {
	session_destroy();
	header("Location: session_test.php");
	exit;
}

if(!isset($_SESSION['count'])) {
	$_SESSION['count'] = 0;
}

$_SESSION['count']++;

printPopupStart(gTranslate('config', "Check Session"));

configLogin(basename(__FILE__));
?>
	<div class="sitedesc left">
		<?php echo gTranslate('config', "If sessions are configured properly in your PHP installation, then you should see a session id below.") ?>
	<br>
		<?php echo gTranslate('config', "The &quot;page views&quot; number should increase every time you reload the page.") ?>
	<br>
		<?php printf(gTranslate('config', "Clicking %s should reset the page view number back to 1. (But it will also log you out.)"), '"Start over"') ?>
	<p>
		<?php echo gTranslate('config', "If this <b>does not</b> work, then you most likely have a configuration issue with your PHP installation.") ?>   
		<?php echo gTranslate('config', "Gallery will not work properly until PHP's session management is configured properly.") ?>  
	</p>
	</div>
	<table width="100%">
	<tr>
		<td>
		<table width="100%" class="inner">
		<tr>
			<td class="shortdesc"><?php echo gTranslate('config', "Your session id is") ?></td>
			<td class="desc"><?php echo session_id() ?></td>
		</tr>
		<tr>
			<td class="shortdesc"><?php echo gTranslate('config', "Page views in this session") ?></td>
			<td class="desc"><?php echo $_SESSION['count'] ?></td>
		</tr>
		<tr>
			<td class="shortdesc"><?php echo gTranslate('config', "Server IP address") ?></td>
			<td class="desc"><?php echo $_SERVER["SERVER_ADDR"] ?></td>
		</tr>
		</table>
		</td>
	</tr>
	</table>
	
	<table width="100%" class="inner">
	<tr>
		<td class="desc" align="center">
			<a href="#" onClick="location.reload()"><?php echo gTranslate('config', "Clicking on this link should reload the page and <b>increase the counter</b>"); ?></a>
			<br><br>
			<a href="session_test.php?destroy=1"><?php echo gTranslate('config', "Start over") ?></a>
		</td>
     </tr>
     </table>
     
     <p><?php echo returnToConfig(); ?></p>

</div>
</body>
</html>
