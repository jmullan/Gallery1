<? require_once('init.php'); ?>

<html>
<head>
  <title>Gallery Configuration</title>
  <style type="text/css">
   body { background: #CCCCCC; }
   .error { color: #FF0000; }
  </style>
</head>
<body>

<?
require("../util.php");
require("functions.inc");

if (fs_file_exists("../config.php")) {
	include("../config.php");
}

if (function_exists("posix_getpwuid")) {
	$rec = posix_getpwuid(posix_getuid());
	$webserver_user = $rec["name"];
} else {
	$whoami = locateFile("whoami");
	if ($whoami) {
		exec($whoami, $results);
		$webserver_user = $results[0];
	} else {
		$webserver_user = "unknown";
	}
}

require("config_data.inc");

if ($go_defaults) {
	$setup_page = $this_page;
} else if ($go_next) {
	$setup_page = $next_page;
} else if ($go_back) {
	$setup_page = $back_page;
}

if (!$setup_page) {
	$setup_page = "check";
}

/* Array-ize the preserve list */
$tmp = split(" ", urldecode($preserve));
$preserve = array();
foreach ($tmp as $key) {
	$preserve[$key] = 1;
}

foreach (array_keys($preserve) as $key) {
	if ($$key) {
		$$key = urldecode($$key);
	}
}

?>

<form method=POST>

<? include("$setup_page.inc"); ?>

<?
function embed_hidden($key) {
	global $$key;

	$buf .= "<input type=hidden name=$key value=\"";
	$buf .= urlencode($$key);
	$buf .= "\">\n";
	return $buf;
}

foreach ($preserve as $key => $val) {
	if ($key && !$onThisPage[$key]) {
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
