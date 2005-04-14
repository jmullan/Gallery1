<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2005 Bharat Mediratta
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

function insertFormJS($formName) {

?>
<script type="text/javascript" language="javascript">
// <!-- 
function setCheck(val,elementName) {
	ufne=document.<?php echo $formName; ?>;
	len = ufne.elements.length;
	for(i = 0 ; i < len ; i++) {
		if (ufne.elements[i].name==elementName) {
			ufne.elements[i].checked=val;
		}
	}
}

function invertCheck(elementName) {
	ufne=document.<?php echo $formName; ?>;
	len = ufne.elements.length;
	for(i = 0 ; i < len ; i++) {
		if (ufne.elements[i].name==elementName) {
			ufne.elements[i].checked = !(ufne.elements[i].checked);
		}
	}
}
// -->
</script>
<?php
}

function insertFormJSLinks($elementName) {
$buf='
	<a href="javascript:setCheck(1,\'' . $elementName . '\')">'. _("Check All") . '</a>
	-
	<a href="javascript:setCheck(0,\'' . $elementName . '\')">'. _("Clear All") . '</a>
	-
	<a href="javascript:invertCheck(\'' . $elementName . '\')">'. _("Invert Selection") .'</a>';

return $buf;
}

/*
** $opts is now a name/value array, where $key is the value returned, and $name
** is the value displayed (and translated).
*/

function selectOptions($album, $field, $opts) {
	foreach ($opts as $key => $value) {
		$sel = "";
		if (isset($album->fields[$field]) && !strcmp($key, $album->fields[$field])) {
			$sel = "selected";
		}
		echo "\n\t<option value=\"$key\" $sel>$value</option>";
	}
	echo "\n";
}


function drawSelect($name, $array, $selected, $size, $attrList=array()) {
	$attrs = "";
	if (!empty($attrList)) {
		$attrs = " ";
		foreach ($attrList as $key => $value) {
			if ($value == NULL) {
				$attrs .= " $key";
			}
			else {
				$attrs .= " $key=\"$value\"";
			}
		}
	}

	$buf = "";
	$buf .= "<select name=\"$name\" size=\"$size\"$attrs>";
	foreach ($array as $uid => $username) {
		$sel = "";
		if (is_array($selected)) {
			if (in_array($uid, $selected)) {
				$sel = " selected";
			}
		}
		else if (!strcmp($uid, $selected)) {
			$sel = " selected";
                }
		$buf .= "<option value=\"$uid\"$sel>". $username ."</option>";
	}
	$buf .= "</select>";

	return $buf;
}

/*
 * makeFormIntro() is a wrapper around makeGalleryUrl() that will generate
 * a <form> tag suitable for usage in either standalone or embedded mode.
 * You can specify the additional attributes you want in the optional second
 * argument.  Eg:
 *
 * makeFormIntro("add_photos.php",
 *                      array("name" => "count_form",
 *                              "enctype" => "multipart/form-data",
 *                              "method" => "POST"));
 */
function makeFormIntro($target, $attrList=array(), $urlargs=array()) {

	// We don't want the result HTML escaped since we split on "&", below
	// use the header version of makeGalleryUrl()
	$url = makeGalleryHeaderUrl($target, $urlargs);

	$result = split("\?", $url);
	$target = $result[0];
	if (sizeof($result) > 1) {
		$tmp = $result[1];
	} else {
		$tmp = "";
	}

	$attrs = '';
	foreach ($attrList as $key => $value) {
		$attrs .= " $key=\"$value\"";
	}

	$form = "<form action=\"$target\" $attrs>\n";

	$args = split("&", $tmp);
	foreach ($args as $arg) {
		if (strlen($arg) == 0) {
			continue;
		}
		list($key, $val) = split("=", $arg);
		$form .= "<input type=\"hidden\" name=\"$key\" value=\"$val\">\n";
	}
	return $form;
}

function formVar($name) {
	if (!strncmp($_REQUEST[$name], 'false', 5)) {
		return false;
	} else {
		return getRequestVar($name); 
	}
}


function emptyFormVar($name) {
	return !isset($_REQUEST[$name]);
}

?>
