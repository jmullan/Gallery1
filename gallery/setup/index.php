<?php /* $Id$ */ ?>
<?php 

	$GALLERY_BASEDIR = '../';

	@include($GALLERY_BASEDIR . "config.php");
	require ($GALLERY_BASEDIR . 'setup/init.php');
	require ($GALLERY_BASEDIR . 'setup/functions.inc');
	require ($GALLERY_BASEDIR . 'Version.php');
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

require($GALLERY_BASEDIR . 'setup/config_data.inc');

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

<form method="post" action="index.php">

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
					$buf .= "<input type=hidden name=${key}[$real_key][$sub_key] value=\"";
					$buf .= urlencode($sub_value);
					$buf .= "\">\n";
				}
			} else {
				$buf .= "<input type=hidden name=${key}[$real_key] value=\"";
				$buf .= urlencode($value);
				$buf .= "\">\n";
			}
		}
	} else {
		$buf .= "<input type=hidden name=$key value=\"";
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
