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
<?php 

	/* 
	** Its important to have this as first position.
	** Otherwise constants are not defined.
	*/
	require (dirname(__FILE__) . '/init.php');
	
	require (dirname(__FILE__) . '/functions.inc');
	require (dirname(__FILE__) . '/config_data.inc');
	require (dirname(dirname(__FILE__)) . '/js/sectionTabs.js.php');
?>
<?php doctype(); ?>
<html>
<head>
	<title><?php echo _("Gallery Configuration") ?></title>
	<?php common_header(); ?>

	<script language="JavaScript" type="text/javascript">
        <!--

	function localGetElementsByTagName(tagName) {
		var eleArray;
		if (window.opera) eleArray = document.body.getElementsByTagName(tagName);
		else if (document.getElementsByTagName) eleArray = document.getElementsByTagName(tagName);
		else if (document.all) eleArray = document.all.tags(tagName);
		else if (document.layers) {
			eleArray = new Array();
			nnGetAllLayers(window, eleArray, 0);
		}
		return eleArray;
	}

	function nnGetAllLayers(parent, layerArray, nextIndex) {
		var i, layer;
		for (i = 0; i < parent.document.layers.length; i++) {
			layer = parent.document.layers[i];
			layerArray[nextIndex++] = layer;
			if (layer.document.layers.length) nextIndex = nnGetAllLayers(layer, layerArray, nextIndex);
		}
		return nextIndex;
	}

	function enableButtons() {
		var buttons = localGetElementsByTagName("input");

		var i = 0;
		while (buttons[i]) {
			if (buttons[i].type == "submit" || buttons[i].type == "button") {
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


if (isset ($go_defaults)) {
	$setup_page = $this_page;
} else if (isset ($go_next)) {
	$setup_page = $next_page;
} else if (isset ($go_back)) {
	$setup_page = $back_page;
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
if (!isset($setup_page)) {
	$setup_page = "check";
}

$legit = array("check", "constants", "defaults", "confirm", "write");
if (in_array($setup_page, $legit)) {
	include(dirname(__FILE__) ."/$setup_page.inc");
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
				$name = stripWQuotesON("$key" . "[$real_key]");
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
