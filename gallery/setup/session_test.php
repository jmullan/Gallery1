<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2006 Bharat Mediratta
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
    require_once(dirname(__FILE__) . '/init.php');

    session_start();

// Pull the $count variable in also
foreach($_SESSION as $key => $value) {
	$$key =& $_SESSION[$key];
}
session_register("count");


if (isset($_REQUEST['destroy'])) {
    session_destroy();
    header("Location: session_test.php");
    exit;
}
$count++;
?>

<html>
<head>
	<title><?php echo _("Gallery Session Test") ?></title>
	<?php echo getStyleSheetLink() ?>
</head>

<body dir="<?php echo $gallery->direction ?>">
	<h1 class="header"><?php echo _("Session Test") ?></h1>

	<div class="sitedesc">
		<?php echo _("If sessions are configured properly in your PHP installation, then you should see a session id below.") ?>
	<br>
		<?php echo _("The &quot;page views&quot; number should increase every time you reload the page.") ?>
	<br>
		<?php echo sprintf(_("Clicking %s should reset the page view number back to 1."), '"Start over"') ?>
	<p>
		<?php echo _("If this <b>does not</b> work, then you most likely have a configuration issue with your PHP installation.") ?>   
		<?php echo _("Gallery will not work properly until PHP's session management is configured properly.") ?>  
	</p>
	</div>
	<table width="100%">
	<tr>
		<td>
		<table width="100%" class="inner">
		<tr>
			<td class="shortdesc"><?php echo _("Your session id is") ?></td>
			<td class="desc"><?php echo session_id() ?></td>
		</tr>
		<tr>
			<td class="shortdesc"><?php echo _("Page views in this session") ?></td>
			<td class="desc"><?php echo $count ?></td>
		</tr>
		<tr>
			<td class="shortdesc"><?php echo _("Server IP address") ?></td>
			<td class="desc"><?php echo $_SERVER["SERVER_ADDR"] ?></td>
		</tr>
		</table>
		</td>
	</tr>
	</table>
	
	<table width="100%" class="inner">
	<tr>
		<td class="desc" align="center"><a href="session_test.php?destroy=1"><?php echo _("Start over") ?></a>
      		<p><?php echo returnToConfig(); ?></p>
</body>
</html>
