
<?
function row_text($key, $arr) {
	$buf = "<tr><td valign=top align=right>$arr[desc]</td><td valign=top>";
	if ($arr["type"]) {
		$type = "type=$arr[type]";
	}
	$buf .= "<input $type name=$key value='$arr[value]' size=40>";
	$buf .= "</td></tr>";
	return $buf;
}

function row_choice($key, $arr) {
	$buf = "<tr><td valign=top align=right>$arr[desc]</td><td valign=top>";
	$buf .= "<select name=$key>";
	foreach ($arr["choices"] as $choice) {
		$selected = "";
		if (!strcmp($choice, $arr["value"])) {
			$selected = "SELECTED";
		}
		$buf .= "<option $selected> $choice";
	}
	$buf .= "</select>";
	$buf .= "</td></tr>";
	return $buf;
}

function locateDir($filename) {
	foreach (explode(":", getenv("PATH")) as $path) {
		if (file_exists("$path/$filename")) {
			return $path;
		}
	}
}

function locateFile($filename) {
	foreach (explode(":", getenv("PATH")) as $path) {
		if (file_exists("$path/$filename")) {
			return "$path/$filename";
		}
	}
}

function constant($key, $value) {
	return "\$app->$key = \"$value\";\n";
}

function defaults($key, $value) {
	return "\$app->default[\"$key\"] = \"$value\";\n";
}

function use_feature($feature) {
	return "\$app->feature[\"$feature\"] = 1;\n";
}
function no_feature($feature, $cause) {
	return "\$app->feature[\"$feature\"] = 0; // ($cause)\n";
}
function error_missing($desc) {
	return error("Missing value: <b>$desc</b>!");
}
function error($msg) {
	return "<tr><td><font color=red>$msg</font></td></tr>";
}
function row_wrap($buf) {
	return "<tr><td><code>$buf</td></tr>";
}
?>

