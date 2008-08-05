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

/**
 * @package setup
 */

/**
 * Its important to have this as first position.
 * Otherwise constants are not defined.
 */

require (dirname(__FILE__) . '/init.php');

require (dirname(__FILE__) . '/config_data.inc');
require (GALLERY_BASE . '/js/sectionTabs.js.php');

list($preserve, $go_next, $go_back, $next_page, $back_page, $this_page, $go_defaults, $refresh) =
	getRequestVar(array('preserve', 'go_next', 'go_back', 'next_page', 'back_page', 'this_page', 'go_defaults', 'refresh'));

doctype();
?>
<html>
<head>
	<title><?php echo gTranslate('config', "Gallery Configuration") ?></title>
	<?php common_header(); ?>

	<script language="JavaScript" type="text/javascript">
	<!--
	function enableButtons() {
		var buttons = document.getElementsByTagName('input');

		var i = 0;
		while (buttons[i]) {
			if (buttons[i].type == 'submit' || buttons[i].type == 'button') {
				buttons[i].disabled = false;
			}
			i++;
		}
	}

	-->
	</script>

</head>

<body dir="<?php echo $gallery->direction ?>" onload="enableButtons()">
<?php
// Require a user to be logged in before allowing them to configure the server.
// If Gallery has not been configured before, allow to continue without logging in
configLogin(basename(__FILE__));


if (isset($go_defaults) || isset($refresh)) {
	$setup_page = $this_page;
}
else if (isset($go_next)) {
	$setup_page = $next_page;
}
else if (isset($go_back)) {
	$setup_page = $back_page;
}

/* Array-ize the preserve list */
if (!empty($preserve)) {
	$tmp = explode(" ", urldecode($preserve));
	$preserve = array();
	foreach ($tmp as $key) {
		$preserve[$key] = 1;
		if (($gallery->session->configForm->$key = getRequestVar($key)) === NULL) {
			$gallery->session->configForm->$key = "";
			continue;
		}
	}
	$preserve = array();
}
else {
	$preserve = array();
}

/* Cache passwords in order to prevent them from being erased.
* Otherwise, we'll lose the passwords if someone revisits Step 2
* and forgets to re-enter them. */
if (isset($gallery->session->configForm->editPassword) &&
    (!empty($gallery->session->configForm->editPassword[0]) || !empty($gallery->session->configForm->editPassword[1])))
{
	$gallery->session->configForm->editPassword[2] = $gallery->session->configForm->editPassword[0];
	$gallery->session->configForm->editPassword[3] = $gallery->session->configForm->editPassword[1];
	$_REQUEST['editPassword'] = $gallery->session->configForm->editPassword;
}
if (isset($gallery->session->configForm->smtpPassword) &&
    (!empty($gallery->session->configForm->smtpPassword[0]) || !empty($gallery->session->configForm->smtpPassword[1])))
{
	$gallery->session->configForm->smtpPassword[2] = $gallery->session->configForm->smtpPassword[0];
	$gallery->session->configForm->smtpPassword[3] = $gallery->session->configForm->smtpPassword[1];
	$_REQUEST['smtpPassword'] = $gallery->session->configForm->smtpPassword;
}

?>

<form method="post" action="index.php" name="config" enctype="application/x-www-form-urlencoded">
<?php
if (!isset($setup_page)) {
	$setup_page = "check";
}

$legit = array("check", "constants", "defaults", "confirm", "write");
if (in_array($setup_page, $legit)) {
	include(dirname(__FILE__) ."/$setup_page.inc");
}
else {
	print gTranslate('config', "Security violation") .".\n";
	exit;
}

foreach ($preserve as $key => $val) {
	if ($key && !isset($onThisPage[$key])) {
		$gallery->session->configForm->$key = $$key;
	}
}

// String-ize preserve list
$preserve = join(" ", array_keys($preserve));
print embed_hidden("preserve");

?>

</form>
</body>
</html>
