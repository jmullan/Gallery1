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
 *
*/

/**
 * This defines all posibilities for the stats-wizard
 *
 * The wizard is divided into 4 sections
 * - types
 * - options
 * - layout
 * - filters
 *
 * Every item has properties:
 *   type	which html type is used in the gui.
 *		'radio', 'checkbox', 'text' or 'select'
 *   default	default value
 *		NOTE: this is also used for the publich links to stats !
 *   name	used for radiogroups. all elements with same name are grouped.
 *   text	This Text is displayed next to the element
 * 	 linktext	This Text is used for the public links
 *
 * @package Statistics
*/

if (!isset($gallery) || !function_exists('gTranslate')) {
	exit;
}

$stats['types'] = array (
	'views'		=> array('type' => 'radio',
				 'default' => 'checked',
				 'name' =>'type',
				 'text' => gTranslate('core', "Sort by most viewed image first"),
				 'linktext' => gTranslate('core', "most viewed")),
	'date'		=> array('type' => 'radio',
				 'default' => '',
				 'name' =>'type',
				 'text' => gTranslate('core', "Sort by the latest added image first"),
				 'linktext' => gTranslate('core', "latest added")),
	'cdate'		=> array('type' => 'radio',
				 'default' => '',
				 'name' =>'type',
				 'text' => gTranslate('core', "Sort by image capture date"),
				 'linktext' => gTranslate('core', "latest shots")),
	'comments'	=> array('type' => 'radio',
				 'default' => '',
				 'name' =>'type',
				 'text' => gTranslate('core', "Show images with comments - latest are shown first"),
				 'linktext' => gTranslate('core', "latest comments")),
/*
	'ratings'	=> array('type' => 'radio',
				 'default' => '',
				 'name' =>'type',
				 'text' => gTranslate('core', "Show images with the highest ratings first"),
				 'linktext' => gTranslate('core', 'highest ratings')),
*/
	'random'	=> array('type' => 'radio',
				 'default' => '',
				 'name' =>'type',
				 'text' => gTranslate('core', "Show random images"),
				 'linktext' => gTranslate('core', "random images"))
);

$stats['options'] = array (
	'sca'		=> array('type' => 'checkbox',
				 'default' => 'checked',
				 'text' => gTranslate('core', "Show caption")),
	'sal'		=> array('type' => 'checkbox',
				 'default' => 'checked',
				 'text' => gTranslate('core', "Show album link")),
	'sde'		=> array('type' => 'checkbox',
				 'default' => 'checked',
				 'text' => gTranslate('core', "Show description")),
	'sco'		=> array('type' => 'checkbox',
				 'default' => 'checked',
				 'text' => gTranslate('core', "Show comments")),
	'scd'		=> array('type' => 'checkbox',
				 'default' => '',
				 'text' => gTranslate('core', "Show capture date")),
	'sud'		=> array('type' => 'checkbox',
				 'default' => '',
				 'text' => gTranslate('core', "Show upload date")),
	'svi'		=> array('type' => 'checkbox',
				 'default' => '',
				 'text' => gTranslate('core', "Show number of views")),
	'sac'		=> array('type' => 'checkbox',
				 'default' => 'checked',
				 'text' => gTranslate('core', "Show the add comment link")),
/*	'svo'		=> array('type' => 'checkbox',
				 'default' => '',
				 'text' => gTranslate('core', "Show the number of 'simplified' votes an image has")),
	'sav'		=> array('type' => 'checkbox',
				 'default' => '',
				 'text' => gTranslate('core', "Show the add vote link")),
*/
	'sao'		=> array('type' => 'checkbox',

				 'default' => '',
				 'text' => gTranslate('core', "Show the album owners")),
	'stm'		=> array('type' => 'checkbox',
				  'default' => '',
				 'text' => gTranslate('core', "Show timing basic information"))
);

$stats['layout'] = array(
	'reverse'	=> array('type' => 'checkbox',
				 'default' => '',
				 'text' => gTranslate('core', "Reverses sort order - see above")),
	'tsz'		=> array('type' => 'text',
				 'default' => (isset($gallery->app->default["thumb_size"])) ? $gallery->app->default["thumb_size"]:100,
				 'text' => gTranslate('core', "Thumb size in pixels")),
	'ppp'		=> array('type' => 'text',
				 'default' => '5',
				 'text' => gTranslate('core', "Controls the number of photos displayed on one page")),
	'total'		=> array('type' => 'text',
				 'default' => '-1',
				 'text' => gTranslate('core', "Controls the maximum number of photos listed, -1 for all")),
	'showGrid'		=> array('type' => 'checkbox',
				 'default' => '',
				 'text' => gTranslate('core', "Use Grid Layout")),
	'rows'		=> array('type' => 'text',
				 'default' => (isset($gallery->app->default["rows"])) ? $gallery->app->default["rows"] : 3,
				 'text' => gTranslate('core', "Controls the number of rows to display in grid mode")),
	'cols'		=> array('type' => 'text',
				 'default' => (isset($gallery->app->default["cols"])) ? $gallery->app->default["cols"] : 3,
				 'text' => gTranslate('core', "Controls the number of columns to display in grid mode")),
	'addLinksPos'	=> array('type' => 'select',
				 'options' => array ('abovecomments'	=> gTranslate('core', "Above the comments"),
						     'oncaptionline'	=> gTranslate('core', "In the caption line"),
						     'abovestats'	=> gTranslate('core', "Above the stats"),
						     'belowcomments'	=> gTranslate('core', "Below the comments")),
				 'text' => gTranslate('core', "Position of the add vote and add comment links")));

$stats['filter'] = array(
	'ty'		=> array('type' => 'text',
				 'default' => '',
				 'text' => gTranslate('core', "Filter by year")),
	'tm'		=> array('type' => 'text',
				 'default' => '',
				 'text' => gTranslate('core', "Filter by month")),
	'td'		=> array('type' => 'text',
				 'default' => '',
				 'text' => gTranslate('core', "Filter by day")),
);

/**
 * Returns a string that contains the HTML code for links to certain statistics
 *
 * @return	string	$links	HTML code to statistics
 */
function generateStatsLinks() {
	global $gallery, $stats;

	$links = '';

	if (!empty($gallery->app->stats_foruser)) {
		foreach ($gallery->app->stats_foruser as $key) {
			if($key == 'comments' && $gallery->app->comments_enabled != 'yes') continue;

			if (isset($stats['types'][$key])) {
				$links .= "\n\t". '[<a href="'. defaultStatsUrl($key) .'">' . $stats['types'][$key]['linktext'] .'</a>]';
			}
		}
	}

	return $links;
}

/* Layout function */
function stats_showBlock($block, $caption=null) {
	echo "\n<table width=\"100%\">";

	if (isset($caption)) {
		echo "\n<caption>$caption</caption>";
	}

	foreach ($block as $option => $attr) {
		echo "\n<tr>";
		echo "\n\t<td>". $attr['text'] ."</td>";

		switch ($attr['type']) {
			case 'radio':
				echo "\n\t". '<td width="30%"><input type="'. $attr['type'] .'" name="'. $attr['name'] .'" value="'. $option .'" '. $attr['default'] .'></td>';
				break;
			case 'checkbox':
				echo "\n\t". '<td width="30%"><input type="'. $attr['type'] .'" name="'. $option .'" value="1" '. $attr['default'] .'></td>';
				break;
			case 'select':
				echo "\n\t". '<td width="30%"><select name="'. $option .'">';
				foreach ($attr['options'] as $optkey => $optvalue) {
					echo "\n\t\t<option value=\"$optkey\">$optvalue</option>";
				}
				echo "\n\t</select></td>";
				break;
			default:
				echo "\n\t". '<td width="30%"><input type="'. $attr['type'] .'" name="'. $option .'" value="'. $attr['default'] .'" size="5"></td>';
				break;
		}
		echo "\n</tr>";
	}
	echo "\n</table>";
}

function defaultStatsUrl($type='') {
	global $stats;

	$urlParams = array();

	$paramListGroups = array('options', 'layout', 'filter');
	if (isset($type)) {
		$urlParams['type'] = $type;
		foreach($paramListGroups as $group) {
			foreach($stats[$group] as $itemKey => $itemValue) {
				if (!empty($stats[$group][$itemKey]['default'])) {
					if($stats[$group][$itemKey]['default'] == 'checked') {
						$urlParams[$itemKey] = 1;
					}
					else {
						$urlParams[$itemKey] = $stats[$group][$itemKey]['default'];
					}
				}
			}
		}
	}

	$msStatsUrl = makeGalleryUrl('stats.php', $urlParams);

	return $msStatsUrl;
}

?>
