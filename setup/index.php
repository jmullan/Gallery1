<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2004 Bharat Mediratta
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
<?php /* $Id$ */ ?>
<?php 

	/* 
	** Its important to have this as first position.
	** Otherwise constants are defined.
	*/
	require (dirname(__FILE__) . '/init.php');
	
	include (GALLERY_BASE . '/config.php');
	require (GALLERY_BASE . '/Version.php');

	require (dirname(__FILE__) . '/functions.inc');
	require (dirname(__FILE__) . '/config_data.inc');
?>
<?php echo doctype(); ?>
<html>
<head>
	<title><?php echo _("Gallery Configuration") ?></title>
	<?php echo getStyleSheetLink() ?>
</head>

<body dir="<?php echo $gallery->direction ?>">
<?php
if (function_exists("posix_getpwuid")) {
	$rec = @posix_getpwuid(posix_getuid());
	$webserver_user = $rec["name"];
} else {
	$whoami = locateFile("whoami");
	if ($whoami) {
		fs_exec($whoami, $results, $status);
		$webserver_user = $results[0];
	} else {
		$webserver_user = _("unknown");
	}
}

if (isset ($go_defaults)) {
	$setup_page = $this_page;
} else if (isset ($go_next)) {
	$setup_page = $next_page;
} else if (isset ($go_back)) {
	$setup_page = $back_page;
}

if (!isset($setup_page)) {
	$setup_page = "check";
}

/* Cache passwords in order to prevent them from being erased.
 * Otherwise, we'll lose the passwords if someone revisits Step 2
 * and forgets to re-enter them. */

if (isset($editPassword) && (!empty($editPassword[0]) || !empty($editPassword[1]))) {
	$editPassword[2] = $editPassword[0];
	$editPassword[3] = $editPassword[1];
}

/* Array-ize the preserve list */
if (isset($preserve)) {
	$tmp = explode(" ", urldecode($preserve));
	$preserve = array();
	foreach ($tmp as $key) {
		$preserve[$key] = 1;
	}
} else {
	$preserve=array();
}
foreach (array_keys($preserve) as $key) {
	if (!isset($$key)) {
		continue;
	}
	$$key = array_urldecode($$key);
}

?>

<form method="post" action="index.php" name="config">

<?php
$legit = array("check", "constants", "defaults", "confirm", "write");
if (in_array($setup_page, $legit)) {
  include("$setup_page.inc");
} else {
  print _("Security violation") .".\n";
  exit;
}
?>

<?php

function embed_hidden($key) {
	global $$key;

	$buf = "";
	$real = $$key;

	if (is_array($real)) {
		foreach ($real as $real_key => $value) {
			if (is_array($value)) {
				foreach($value as $sub_key => $sub_value) {
					$name = stripWQuotesON($key . "[$real_key][$sub_key]");
					$buf .= '<input type="hidden" name="'. $name .'" value="';
					$buf .= urlencode($sub_value);
					$buf .= "\">\n";
				}
			} else {
				$name = stripWQuotesON(${key} ."[$real_key]");
				$buf .= '<input type="hidden" name="'. $name .'" value="';
				$buf .= urlencode($value);
				$buf .= "\">\n";
			}
		}
	} else {
		$buf .= '<input type="hidden" name="'. stripWQuotesON($key) . '" value="';
		$buf .= urlencode($real);
		$buf .= "\">\n";
	}
	return $buf;
}

foreach ($preserve as $key => $val) {
	if ($key && !isset($onThisPage[$key])) {
		print embed_hidden($key);
	}
}

// String-ize preserve list
$preserve = join(" ", array_keys($preserve));
print embed_hidden("preserve");

?>

</form>

</body>
</html>
