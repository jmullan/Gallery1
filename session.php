<?
session_register_and_set("cols");
session_register_and_set("rows");
session_register_and_set("page");
session_register_and_set("edit");
session_register_and_set("albumName");

function session_register_and_set($name) {
	session_register($name);
	$setname = "set_$name";
	global $$name;
	global $$setname;
	if (!empty($$setname)) {
		$$name = $$setname;
	} if (!$$name) {
		global $app;
		if (strcmp($app->default["$name"], "")) {
			$$name = $app->default["$name"];
		}
	}
}
?>
