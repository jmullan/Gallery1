<? require("../style.php") ?>
<?
if (file_exists("../config.php")) {
	include("../config.php");
}
$webserver_user = posix_getpwuid(posix_getuid());
require("functions.inc");
require("config_data.inc");

if ($go_defaults) {
	$setup_page = $this_page;
} else if ($go_next) {
	$setup_page = $next_page;
} else if ($go_back) {
	$setup_page = $back_page;
}

if (!$setup_page) {
	$setup_page = "form";
}

?>

<form method=POST>

<? include("$setup_page.inc"); ?>

</form>

