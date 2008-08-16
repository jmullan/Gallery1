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

include_once(dirname(dirname(__FILE__)) . '/js/sectionTabs.js.php');

/**
 * This function creates a tabsset  for navigating through Sections (Groups).
 *
 * It analyses a given Array which is in config_data Style:
 *
 * "<group_key>" => array (
 *			'type'	 =>
 *			'default'  =>
 *			'title'	   =>
 *			'desc'	   =>
 *			'contains_required'
 * )
 *
 * 'type'		: Indicates that a group starts or ends. Possible values: 'group_start' , 'group_end'.
 * 'default'		: Indicates whether the group is visible or not. Possible values: 'inline', 'none'.
 * 'title'		: When the group is visible, this is displayed as tab title aswell as the title over the content.
 * 'desc'		: This optional Description is displayed under the title.
 * 'contains_required'	: Indicates that this Group contains field that are required
 *
 * Note: - The first group which default is 'inline' will the group that is selected when opening the Page.
 *	 - You always need a group_end for a group. Otherwise everything below will belong to the group.
 *
 * @author Jens Tkotz
 */
function makeSectionTabs($array, $initialtab = '', $sortByTitle = false, $visibilityKeyword = '', $visibilityValue = '') {
	$tabs = array();

	foreach ($array as $key => $var) {
		if((isset($var['type']) && $var['type'] == 'group_start') ||
		   (isset($var['type']) && $var['type'] == 'group')) {
			if(!empty($visibilityKeyword)) {
				if($var[$visibilityKeyword] != $visibilityValue) {
					continue;
				}
			}
			$tabs[$key] = $var;
		}
	}

	if ($sortByTitle) {
		array_sort_by_fields($tabs, 'title', 'asc', true, true);
	}

	echo "\n<div class=\"g-tabset floatleft\">\n";

	//print_r($tabs);
	foreach ($tabs as $name => $cell) {
		$attrList = array();

		if ((isset($cell['initial']) && !$initialtab) ||
			(isset($cell['default']) && $cell['default'] == 'inline' && !$initialtab) ||
			$initialtab == $name)
		{
			$attrList['class'] = 'g-activeTab';
			if (empty($initialtab)) {
				$initialtab = $name;
			}
		}

		$text = $cell['title'];

		if (!empty($cell['contains_required'])) {
			$text .= '<span class="g-littlered">*</span>';
		}

		$attrList['id']		= "tab_$name";
		$attrList['onClick']	= "section_tabs.toggle('$name')";
		$attrList['title']	= $cell['title'];

		echo galleryLink('', $text, $attrList);
	}

	echo "</div>\n";

	$i = 0;
	echo '<script language="JavaScript" type="text/javascript">';
	echo "\n\t". 'var Sections = new Array()';
	foreach ($tabs as $name => $var) {
		if(isset($var['type']) && ($var['type'] == 'group_start' || $var['type'] == 'group')) {
			echo "\n\tSections[$i] ='$name';";
			$i++;
		}
	}

	echo "\n\tsection_tabs = new configSection('$initialtab')";
	insertSectionToggle();

	echo "\n</script>\n";

	return $initialtab;
}

function makeSimpleSectionContent($array, $initialtab = '') {
	$i = 0;
	foreach ($array as $key => $val) {
		if( isset($val["enabled"]) && $val["enabled"] == "no") continue;

		 if ($val["type"] === 'group_start') {
			echo "\n<div id=\"$key\">";
			echo make_separator($key, $val);
			continue;
		}

		if ($val["type"] === 'subgroup') {
			echo "\n<div id=\"$key\">";
			echo "\n\t<div class=\"g-subgroup center\">{$val['title']}</div>";
			if (isset($val['desc'])) {
				echo "\n\t<div>{$val['desc']}</div>";
			}
			continue;
		}

		if ($val["type"] === 'subgroup_end') {
			echo "\n</div>";
			continue;
		}

		if ($val["type"] === 'group_end') {
			echo "\n</div>";
			continue;
		}

		//echo "\n<div id=\"{$val["name"]}\" style=\"width: 100px; border: 1px solid green; 1display: ${val['default']}\">";
		if ($val["type"] === 'group') {
			if(!empty($initialtab) && $initialtab == $key) {
				$display = 'inline';
			}
			else {
				$display = 'none';
			}

			echo "\n<div id=\"$key\" style=\"display: $display\">";
			echo make_separator($key, $val);
			echo "\n<div>${val['content']}</div>";
			echo "\n</div>";
			continue;
		}

		/* if the variable is hidden, lock it in as we don't want to use previous values*/
		if ($val["type"] === 'hidden') {
			echo $val['content'];
		}
		else {
			echo $val['content'];
		}
	}
}

function make_separator($key, $arr)  {
	getAndRemoveAccessKey($arr["title"]);

	$html = "\n\t<h1>{$arr["title"]}</h1>";

	if(!empty($arr["desc"])) {
		$html .= "\n<div class=\"g-desc-cell\">{$arr["desc"]}</div>";
	}

	return $html;
}
?>