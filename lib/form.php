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
 * @package Forms
 *
 * Function for HTML forms
 */

function insertFormJS($formName) {

?>
<script type="text/javascript" language="javascript">
// <!--
function setCheck(val,elementName) {
	ufne = document.<?php echo $formName; ?>;
	for(i = 0 ; i < ufne.elements.length; i++) {
		if (ufne.elements[i].name == elementName) {
			if (ufne.elements[i].type == 'select-multiple') {
				for (j = 0; j < ufne.elements[i].length; j++) {
					ufne.elements[i].options[j].selected = val;
				}
			}
			else {
				ufne.elements[i].checked = val;
			}
		}
	}
}

function invertCheck(elementName) {
	ufne = document.<?php echo $formName; ?>;
	len = ufne.elements.length;
	for(i = 0 ; i < ufne.elements.length; i++) {
		if (ufne.elements[i].name==elementName) {
			if (ufne.elements[i].type == 'select-multiple') {
				for (j = 0; j < ufne.elements[i].length; j++) {
					ufne.elements[i].options[j].selected = !(ufne.elements[i].options[j].selected);
				}
			}
			else {
				ufne.elements[i].checked = !(ufne.elements[i].checked);
			}
		}
	}
}
// -->
</script>
<?php
}

function insertFormJSLinks($elementName) {
	$buf='
	<a href="javascript:setCheck(1,\'' . $elementName . '\')">'. gTranslate('common', "Check All") . '</a>
	-
	<a href="javascript:setCheck(0,\'' . $elementName . '\')">'. gTranslate('common', "Clear All") . '</a>
	-
	<a href="javascript:invertCheck(\'' . $elementName . '\')">'. gTranslate('common', "Invert Selection") .'</a>';

	return $buf;
}

/**
 * $opts is now a name/value array, where $key is the value returned, and $name
 * is the value displayed (and translated).
*/
function selectOptions($album, $field, $opts) {
	foreach ($opts as $key => $value) {
		$sel = '';
		if (isset($album->fields[$field]) && !strcmp($key, $album->fields[$field])) {
			$sel = 'selected';
		}
		echo "\n\t<option value=\"$key\" $sel>$value</option>";
	}
	echo "\n";
}

/**
 * Returns the partitial HTML code for HTML tags attributes
 *
 * @param	array	$attrList	Format: 'key' => 'value'
 * @return	string	$attrList
 * @author	Jens Tkotz
 */
function generateAttrs($attrList) {
	$attrs = '';

	if(!empty($attrList) && is_array($attrList)) {
		foreach ($attrList as $key => $value) {
			if ($value === false) {
				continue;
			}
			elseif ($value === NULL) {
				$attrs .= " $key";
			}
			else {
				$attrs .= " $key=\"$value\"";
			}
		}
	}

	return $attrs;
}

/**
 * Returns the HTML code for a selectbox
 *
 * @param string  $name		 Name attribute of the selectbox
 * @param array   $options	 Array of options. Format 'value' => 'text'
 * @param mixed   $selected	 String or integer, if a value or key is equal this, the entry is selected.
 * @param integer $size		 Size of the box, default 1
 * @param array   $attrList	 Optional Attributs for the selectbox
 * @return string $html
 */
function drawSelect($name, $options, $selected = '', $size = 1, $attrList = array()) {
	$crlf = "\n\t";
	$attrs = generateAttrs($attrList);
	$html = "<select name=\"$name\" size=\"$size\"$attrs>" . $crlf;

	if(!empty($options)) {
		foreach ($options as $value => $text) {
			$sel = '';
			if (is_array($selected)) {
				if (in_array($value, $selected)) {
					$sel = ' selected';
				}
			}
			else if (!strcmp($value, $selected) || !strcmp($text, $selected) || $selected === '__ALL__') {
				$sel = ' selected';
			}
			$html .= "<option value=\"$value\"$sel>$text</option>" . $crlf;
		}
	}
	$html .= '</select>'. $crlf;

	return $html;
}

/**
 * Returns the HTML code for a selectbox
 *
 * @param string  $name		 Name attribute of the selectbox
 * @param array   $options	 Array of options. Format 'trash' => array('text' => .., 'value' => ''
 * @param array   $attrList	 Optional Attributs for the selectbox
 * @return string $html
 * @author Jens Tkotz
 */
function drawSelect2($name, $options, $attrList = array()) {
	$crlf = "\n\t";

	// This attributes are no real HTML attribs and thus should be deleted.
	$optionIgnoreAttrs = array('text', 'icon', 'separate', 'html', 'type', 'requirements');

	if (!isset($attrList['size'])) {
		$attrList['size'] = 1;
	}

	$attrs = generateAttrs($attrList);

	$html = "$crlf<select name=\"$name\"$attrs>$crlf";

	if(!empty($options)) {
		foreach ($options as $option) {
			$option['text'] = removeAccessKey($option['text']);

			if(!isset($option['class'])) {
				$option['class'] = '';
			}

			if(isset($option['selected']) && $option['selected'] != false) {
				$option['selected'] = null;
				$option['class'] .= ' g-selected';

			}

			if(!isset($option['value'])) {
				$option['class'] .= ' center g-disabled';
			}

			if(empty($option['class'])) {
				unset($option['class']);
			}

			$text = $option['text'];

			foreach ($optionIgnoreAttrs as $delete) {
				unset($option[$delete]);
			}

			$optAttrs = generateAttrs($option);
			$html .= '<option'. $optAttrs .'>'. $text .'</option>' . $crlf;
		}
	}

	$html .= "</select>". $crlf;

	return $html;
}

/**
 * makeFormIntro() is a wrapper around makeGalleryUrl() that will generate
 * a <form> tag suitable for usage in either standalone or embedded mode.
 * You can specify the additional attributes you want in the optional second
 * argument.  Eg:
 *
 * makeFormIntro("add_photos.php",
 *			  array("name" => "count_form",
 *					  "enctype" => "multipart/form-data",
 *					  "method" => "post"));
 *
 * If no method is given in attrList, then "post" is used.
 * @param string	$target
 * @param array()   $attrList
 * @param array()   $urlargs
 * @return string   $form
 */
function makeFormIntro($target, $attrList = array(), $urlargs = array()) {
	static $usedIDs = array();
	static $idCounter = 0;

	// We don't want the result HTML escaped since we split on "&", below
	// use the header version of makeGalleryUrl()
	if(urlIsRelative($target)) {
		$url = makeGalleryHeaderUrl($target, $urlargs);
	}
	else {
		$url = unhtmlentities($target);
	}

	$result = split("\?", $url);
	$target = $result[0];
	$tmp = (sizeof($result) > 1) ? $result[1] :'';

	$defaults = array(
		'method' => 'post',
		'name'	 => 'g1_form'
	);

	foreach($defaults as $attr => $value) {
		if(!isset($attrList[$attr])) {
			$attrList[$attr] = $value;
		}
	}

	$attrs = generateAttrs($attrList);

	$form = "\n<form action=\"$target\"$attrs>\n";

	$args = split("&", $tmp);
	foreach ($args as $arg) {
		if (strlen($arg) == 0) {
			continue;
		}
		list($key, $val) = split("=", $arg);
		if(in_array($key, $usedIDs)) {
			$id = "${key}_${idCounter}";
			$idCounter++;
		}
		else {
			$id = $key;
			$usedIDs[] = $id;
		}

		$form .= gInput('hidden', $key, null, false, $val, array('id' => $id));
	}

	return $form;
}

function formVar($name) {
	$_REQUEST = array_merge($_GET, $_POST);

	if (!strncmp($_REQUEST[$name], 'false', 5)) {
		return false;
	}
	else {
		return getRequestVar($name);
	}
}

function emptyFormVar($name) {
	$_REQUEST = array_merge($_GET, $_POST);

	return !isset($_REQUEST[$name]);
}

/**
 * Returns the HTML for a so called colorpicker.
 * Its a table with 3 cells.
 * First one is the Hexcode or colorname.
 * Second is a box with the color as background.
 * Third is an icon that open a fance colorpicker thing.
 *
 * @param array		$attrs
 * @return string	$html
 * @author Jens Tkotz
 */
function showColorpicker($attrs = array(), $addCallBack = false, $updatePreview = false, $label = '') {
	static $initDone = false;

	$id = $name = $attrs['name'];
	$imgColorpicker = gImage("colorpicker.png", gTranslate('core', "colorpicker"));

	$html = '';

	$callBack = ($addCallBack) ? ' callBack(color.hex);' : '';
	$updateOnComplete = ($updatePreview) ? " \$('mooDestination_$id').style.backgroundColor=color.hex;" : '';

	if(! $initDone) {
		$html .= _getStyleSheetLink('mooRainbow');
		$html .= "\n";
		$html .= jsHTML('rgbcolor.js');
		$html .= jsHTML('moorainbow/mootools.v1.11.js');
		$html .= jsHTML('moorainbow/mooRainbow.js.php');

		$initDone = true;
	}

	$html .= '<script type="text/javascript">';
	$html .= "window.addEvent('load', function() {";
	$html .= "var g_colorpicker_$id = new MooRainbow('colorpicker_$id', {";
	$html .= "id: 'gal_colorpicker_$id',";
	$html .= "destination : '$id',";
	$html .= "imgPath: '". getGalleryBaseUrl() . "/images/moorainbow/',";
	$html .= "onComplete: function(color) {";
	$html .= "\$('$id').value = color.hex;";
	$html .= $callBack;
	$html .= $updateOnComplete;
	$html .= "}";
	$html .= "});";
	$html .= "});";
	$html .= "</script>";

	$html .= "\n<table cellspacing=\"0\">";
	$html .= "\n<tr><td>$label </td>";
	$html .= gInput('text', $name,'','cell', $attrs['value'], array('size' => 8, 'maxlength' => 7));
	$html .= "\n\t<td id=\"mooDestination_$id\" width=\"20\" style=\"background-color: {$attrs['value'] }\"></td>";
	$html .= "\n\t<td id=\"colorpicker_$id\">${imgColorpicker}</td>";
	$html .= "\n</tr></table>\n";

	return $html;
}

function showByteCalculator($id, $initValue = 0, $positionBelow = false, $changeable = true) {
	if($changeable) {
		$type = 'text';
		$value = '';
	}
	else {
		$type = 'fixedhidden';
		$value = formatted_filesize($initValue);

	}

	$html = gInput($type, "${id}_niceBytes", null, false, $value, array('readonly' => 'readonly', 'id' => "${id}_niceBytes"));

	if($changeable) {
		$html .= "\n<a onClick=\"showByteCalculator('$id');\">";
		$html .= gImage('icons/calc.png', '',  array('id' => "${id}_byteCalcIcon"));
		$html .= "</a>\n";

		$units = array(
			1		=> gTranslate('common', "Byte"),
			1024		=> gTranslate('common', "KB"),
			1048576		=> gTranslate('common', "MB"),
			1073741824	=> gTranslate('common', "GB"),
		);

		$html .= ($positionBelow) ? '<br>&nbsp;' : '';
		$html .= "<fieldset id=\"${id}_byteCalcBox\" style=\"position: absolute; display: none; border: 1px solid black; width:215px; background: #fff;\">";
		$html .= "\n<legend>". gTranslate('common', "Byte calculator") . '</legend>';
		$html .= "\n<div>";
		$html .= "\n<input id=\"${id}_mixedSize\" onkeyup=\"update('$id')\" value=\"$initValue\"> ";
		$html .= drawSelect("${id}_unit", $units, '', 1, array('onchange' => "update('$id')", 'id' => "${id}_unit"));
		$html .= "\n</div>";
		$html .= "<div style=\"width:100%; text-align: right; margin-top: 2px;\">";
		$html .= galleryLink('#', gTranslate('core', "_Close"), array('onclick' => "closeByteCalculator('$id')"));
		$html .= "</div>";
		$html .= "\n</fieldset>\n";

		$html .= "\n<input type=\"hidden\" id=\"$id\" name=\"$id\" value=\"$initValue\">";
		$html .= "<script type=\"text/javascript\">update('$id')</script>";
	}

	return $html;
}

function showChoice($label, $target, $args, $class = '') {
	global $gallery;

	if (empty($args['set_albumName'])) {
		$args['set_albumName'] = $gallery->session->albumName;
	}

	$args['type'] = 'popup';

	echo "\t<option class=\"$class\" value='" . makeGalleryUrl($target, $args) . "'>$label</option>\n";
}

function showChoice2($target, $args, $popup = true) {
	global $gallery;

	if (empty($args['set_albumName'])) {
		$args['set_albumName'] = $gallery->session->albumName;
	}

	if($popup) {
		$args['type'] = 'popup';
	}

	return makeGalleryUrl($target, $args);
}

/**
 * Returns the HTML Code for a submit button (<input type="submit">).
 *
 * @param string    $name              Name of the button.
 * @param string    $value            Value shown on the button.
 * @param array     $additionalAttrs  Additional HTML attributes
 * @return string   $html             The HTML code.
 * @author Jens Tkotz
 */
function gSubmit($name, $value, $additionalAttrs = array()) {
	static $ids = array();

	if(!in_array($name, $ids)) {
		$attrList['name'] = $attrList['id'] = $name;
		$ids[] = $name;
	}
	else {
		$attrList['name'] = $name;
	}

	$attrList['type'] = 'submit';
	$attrList['accesskey'] = getAndRemoveAccessKey($value);
	$attrList['value'] = $value;
	$attrList['class'] = 'g-button';
	$attrList['title'] = isset($additionalAttrs['title']) ? $additionalAttrs['title'] : $value;

	if($attrList['accesskey'] != '') {
	   $attrList['title'] .= ' '. sprintf(gtranslate('common', "(Accesskey '%s')"), $attrList['accesskey']);
	}

	$attrList = array_merge($attrList, $additionalAttrs);
	$attrs = generateAttrs($attrList);

	$html = "  <input$attrs>\n";

	return $html;
}

/**
 * Returns the HTML Code for an input element
 *
 * @param string $type				E.g. 'text', 'textarea', 'checkbox' et.c
 * @param string $name
 * @param string $label
 * @param boolean $tableElement		Wether the form field should be a table line
 * @param mixed $value
 * @param array $attrList			List of attributes for the form field
 * @param boolean $multiInput		If true, then multiple fields are dynamically added/removed
 * @param booelan $autocomplete
 * @return string $html
 * @author Jens Tkotz
 */
function gInput($type, $name, $label = null, $tableElement = false, $value = null, $attrList = array(), $multiInput = false, $autocomplete = false) {
	global $browser;
	static $pwCheckAdded	= false;
	static $idcnt		= 0;

	$attrList['name']	= $name;
	$attrList['accesskey']	= getAndSetAccessKey($label);

	$idcnt++;
	if(!isset($attrList['id'])) {
		if ($type != 'radio') {
			$attrList['id'] = $attrList['name'];
		}
		else {
			$attrList['id'] = $attrList['name'] . '_' . $idcnt;
		}
	}

	$id = $attrList['id'];

	if ($value !== null && $type != 'textarea') {
		$attrList['value'] = $value;
	}

	// pre-processing
	switch($type) {
		case 'fixedhidden':
			$attrList['type'] = 'hidden';
		break;

		case 'textarea':
		break;

		case 'password':
			$attrList['type'] = $type;
			if(isset($attrList['addCheck'])) {
				$attrList['onkeyup'] = "checkPw('{$attrList['id']}')";
				$addCheck = true;
				unset($attrList['addCheck']);
			}
		break;

		default:
			$attrList['type'] = $type;
		break;
	}

	if(!isset($attrList['class'])) {
		switch ($type) {
			case 'text':
			case 'password':
				$attrList['class'] = 'g-form-text';
			break;
		}
	}

	$attrs = generateAttrs($attrList);

	if($autocomplete && isset($browser)) {
		$input = initAutocompleteJS(
			$label,
			$name,
			$id,
			$browser->hasFeature('xmlhttpreq')
		);
		$label = null;
	}
	elseif ($type == 'textarea') {
			$input = "<textarea$attrs>$value</textarea>";
	}
	else {
		$input = "<input$attrs>";
	}

	// post-processing
	switch ($type) {
		case 'fixedhidden':
			$input .= $value;
		break;
	}

	if ($type == 'password' && isset($addCheck)) {
		$input .= "\n" .
			gInput(
				'text',
				"result_{$attrList['id']}", null, false, '',
				array('size' => 13, 'class' => 'g-pwcheck', 'disabled' => null));

		if(!$pwCheckAdded) {
			$input .= jsHTML('number_conversions.js');
			$input .= jsHTML('passwordStrengthMeter.js');
			$pwCheckAdded = true;
		}
	}

	// End post-processing
	if($tableElement){
		$html = ($tableElement === 'cell') ? '' : "  <tr>\n";
		if($label) {
			if($type == 'checkbox' || $type == 'radio') {
				$html .= "\t<td width=\"10\">$input</td>\n";
				$html .= "\t<td><label for=\"$id\">$label</label></td>\n";
			}
			else {
				$html .= "\t<td><label for=\"$id\">$label</label></td>\n";
				$html .= "\t<td>$input</td>\n";
			}
		}
		else {
			$html .= "\t<td>$input</td>\n";
		}
		$html .= ($tableElement === 'cell') ? '' : "  </tr>\n";
	}
	else {
		if($label) {
			if($type == 'checkbox' || $type == 'radio') {
				$html = "  $input<label for=\"$id\">$label</label>\n";
			}
			else {
				$html = "  <label for=\"$id\">$label</label> $input\n";
			}
		}
		else {
			$html = "  $input\n";
		}
	}

	if($multiInput) {
		$html .= gButton('addField', gTranslate('common', "Add field"), "${id}obj.newField()");
		$html .= "\n<div id=\"${id}_multiInputContainer\"></div>\n\n";

		$html .= '<script language="JavaScript" type="text/javascript">';
		$html .= "\n\tvar ${id}obj = new MultiInput('$id', '${id}_multiInputContainer')";
		$html .= "\n</script>\n";
	}

	return $html;
}

function gButton($name, $value, $onClick, $additionalAttrs = array()) {
	static $ids = array();

	$attrList['type']	= 'button';
	$attrList['accesskey']	= getAndRemoveAccessKey($value);
	$attrList['value']	= $value;
	$attrList['class']	= 'g-button';
	$attrList['onClick']	= $onClick;
	$attrList['title']	= isset($additionalAttrs['title']) ? $additionalAttrs['title'] : $value;

	if(!in_array($name, $ids)) {
		$attrList['name'] = $attrList['id'] = $name;
		$ids[] = $name;
	}
	else {
		$attrList['name'] = $name;
	}

	$attrList = array_merge($attrList, $additionalAttrs);

	if($attrList['accesskey'] != '') {
		$attrList['title'] .= ' '. sprintf(gtranslate('common', "(Accesskey '%s')"), $attrList['accesskey']);
	}

	$attrs = generateAttrs($attrList);

	$html = "  <input$attrs>\n";

	return $html;
}

function gReset($name, $value, $additionalAttrs = array()) {
	$attrList['name']	= $name;
	$attrList['type']	= 'reset';
	$attrList['accesskey']	= getAndRemoveAccessKey($value);
	$attrList['value']	= $value;
	$attrList['class']	= 'g-button';
	$attrList['title']	= isset($additionalAttrs['title']) ? $additionalAttrs['title'] : $value;

	if($attrList['accesskey'] != '') {
		$attrList['title'] .= ' '. sprintf(gtranslate('common', "(Accesskey '%s')"), $attrList['accesskey']);
	}

	$attrs = generateAttrs($attrList);

	$html = "  <input$attrs>\n";

	return $html;
}

function gDate($name, $label = null, $value = null) {
	if(empty($value)) {
		$value = time();
	}

	$html = '';
	$html .= gInput('hidden', $name, $label, false, strftime("%Y-%m-%d %H:%M:%S", $value));
	$html .= '<span id="date_'. $name .'" class="g-form-date">' .
				strftime("%d. %b %Y %H:%M:%S", $value) .
			 '</span>';
	$html .= "\n <button id=\"button_". $name ."\">...</button>\n<br>";
	$html .= "\n<br>  <script type=\"text/javascript\">Calendar.setup(
				{inputField: \"". $name ."\",
				 displayArea: \"date_". $name ."\",
				 button: \"button_". $name . "\"});
			</script>";

	return $html;
}
?>