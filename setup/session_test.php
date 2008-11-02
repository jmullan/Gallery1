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

if (isset($_GET['destroy'])) {
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
  <div class="g-sitedesc left">
		<?php echo gTranslate('config', "If sessions are configured properly in your PHP installation, then you should see a session id below."); ?>
	<br>
		<?php echo gTranslate('config', "The &quot;page views&quot; number should increase every time you reload the page."); ?>
	<br>
		<?php echo gTranslate('config', "Clicking &quot;Start over&quot; should reset the page view number back to 1 (But you need to login again)."); ?>
	<br><br>
		<?php echo infoBox(array(array(
			  'type' => 'warning',
			  'text' =>
				  gTranslate('config', "If this <b>does not</b> work, then you most likely have a configuration issue with your PHP installation.") .
				  '<p>' .
				  gTranslate('config', "Gallery will not work properly until PHP's session management is configured properly.") .
				  '</p>'
			  )), '', true);
		?>
	</div>

	<br>
	<table width="50%" align="center">
		<tr>
			<td class="g-shortdesc"><?php echo gTranslate('config', "Your session id is") ?></td>
			<td class="g-desc"><?php echo session_id() ?></td>
		</tr>
		<tr>
			<td class="g-shortdesc"><?php echo gTranslate('config', "Page views in this session") ?></td>
			<td class="g-desc"><?php echo $_SESSION['count'] ?></td>
		</tr>
		<tr>
			<td class="g-shortdesc"><?php echo gTranslate('config', "Server IP address") ?></td>
			<td class="g-desc"><?php echo $_SERVER["SERVER_ADDR"] ?></td>
		</tr>
	</table>

</div>

<div class="center">
	<?php echo gButton('reload', gTranslate('config', "_Reload"), 'location.href=\'session_test.php\''); ?>
	<?php echo gButton('restart', gTranslate('config', "_Start over"), 'location.href=\'session_test.php?destroy=1\''); ?>

	<br><br>
	<?php echo returnToDiag(); ?><?php echo returnToConfig(); ?>
</div>

</body>
</html>
