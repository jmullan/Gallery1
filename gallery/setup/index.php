<? require_once('init.php'); ?>

<html>
<head>
  <title>Gallery Configuration</title>
  <link rel="stylesheet" type="text/css" href="../css/standalone_style.css.default">
</head>
<body>

<?
require("../util.php");
require("functions.inc");

if (file_exists("../config.php")) {
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

?>

<form method=POST>

<? include("$setup_page.inc"); ?>

</form>

</body>
</html>
