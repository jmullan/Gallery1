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
 *
 */
?>
<?php

if (!isset($gallery->version)) {
        require_once(dirname(__FILE__) . '/init.php');
}

// Security check
if (!$gallery->user->isAdmin()) {
	header("Location: " . makeAlbumHeaderUrl());
	exit;
}

/* Layout function */
function stats_showBlock($block, $caption=null) {
	echo "\n\t<table>";
	if (isset($caption)) {
		echo "<caption>$caption</caption>"; 
	}
	foreach ($block as $option => $attr) {
		echo "\n<tr>";
		switch ($attr['type']) {
			case 'checkbox':
			case 'radio':
					echo "\n\t". '<td><input type="'. $attr['type'] .'" name="'. $option .'" value="1" '. $attr['checked'] .'></td>';
			break;
			case 'select':
					echo "\n\t". '<td><select name="'. $option .'">';
					foreach ($attr['options'] as $opt) {
							echo "\n\t\t<option>$opt</option>";
					}
					echo "\n\t</select></td>";
			break;
			default:
				echo "\n\t". '<td><input type="'. $attr['type'] .'" name="'. $option .'" value="'. $attr['default'] .'" size="5"></td>';
			break;
		}
		echo "\n\t<td>". $attr['text'] ."</td>";
		echo "\n</tr>";
	}
	echo "\n\t</td>";
	echo "\n</tr>";
	echo "\n</table>";
}

doctype();
?>

<html>
<head>
<title><?php echo $gallery->app->galleryTitle ?></title>
<?php 
	common_header() ;
?>
  <style>
	.blockcell { vertical-align: top; border-bottom: 1px solid #000000 }
	caption	{ font-weight:bold; margin-bottom: 5px}
  </style>
</head>
<body dir="<?php echo $gallery->direction ?>">
<?php  
        includeHtmlWrap("gallery.header");
?>
<p align="center" class="popuphead"><?php echo _("Statistic Wizard") ?></p>
<div style="text-align:right"><a href="<?php echo makeAlbumUrl(); ?>"><?php echo _("Return to Gallery"); ?></a></div>

<?php
	$types = array (
		'views'		=> array ('type' => 'radio', 'checked' => 'checked',	'text' => _("Sort by most viewed image first")),
		'date'		=> array ('type' => 'radio', 'checked' => '', 		'text' => _("Sort by the latest added image first")),
		'cdate'		=> array ('type' => 'radio', 'checked' => '', 		'text' => _("Sort by image capture date")),
		'comments'	=> array ('type' => 'radio', 'checked' => '', 		'text' => _("Show images with comments - latest are shown first")),
		'ratings'	=> array ('type' => 'radio', 'checked' => '', 		'text' => _("Show images with the highest ratings first")),
		'random'	=> array ('type' => 'radio', 'checked' => '', 		'text' => _("Show random images"))
	);


	$options = array (
		'sca'	=> array('type' => 'checkbox', 'checked' => 'checked', 	'text' => _("Show caption")),
		'sal'	=> array('type' => 'checkbox', 'checked' => 'checked', 	'text' => _("Show album link")),
		'sde'	=> array('type' => 'checkbox', 'checked' => 'checked', 	'text' => _("Show description")),
		'sco'	=> array('type' => 'checkbox', 'checked' => '', 	'text' => _("Show comments")),
		'scd'	=> array('type' => 'checkbox', 'checked' => '', 	'text' => _("Show capture date")),
		'sud'	=> array('type' => 'checkbox', 'checked' => '', 	'text' => _("Show upload date")),
		'svi'   => array('type' => 'checkbox', 'checked' => '', 	'text' => _("Show number of views")),
		'sac'	=> array('type' => 'checkbox', 'checked' => 'checked', 	'text' => _("Show the add comment link")),
//		'svo'   => array('type' => 'checkbox', 'checked' => '', 	'text' => _("Show the number of 'simplified' votes an image has")),
		'sav'	=> array('type' => 'checkbox', 'checked' => '', 	'text' => _("Show the add vote link")),
		'sao'	=> array('type' => 'checkbox', 'checked' => '', 	'text' => _("Show the album owners")),
		'stm'	=> array('type' => 'checkbox', 'checked' => '', 	'text' => _("Show timing basic information"))
	);
	
	$layout = array(
		'rev'	=> array('type' => 'checkbox', 'checked' => '', 	'text' => _("Reverses sort order - see above")),
		'tsz'	=> array('type' => 'text', 'default' => (isset($gallery->app->default["thumb_size"])) ? $gallery->app->default["thumb_size"]:100,	'text' => _("Thumb size in pixels")),
		'ppp'	=> array('type' => 'text', 'default' => '5', 		'text' => _("Controls the number of photos displayed on one page")),
		'total'	=> array('type' => 'text', 'default' => '-1', 		'text' => _("Controls the maximum number of photos listed, -1 for all")),
		'sgr'	=> array('type' => 'checkbox', 'checked' => '', 	'text' => _("Use Grid Layout")),
		'rows'	=> array('type' => 'text', 'default' => (isset($gallery->app->default["rows"])) ? $gallery->app->default["rows"] : 3, 		'text' => _("Controls the number of rows to display in grid mode")),
		'cols'	=> array('type' => 'text', 'default' => (isset($gallery->app->default["cols"])) ? $gallery->app->default["cols"] : 3, 		'text' => _("Controls the number of columns to display in grid mode")),
		'addLinksPos' => array ('type' => 'select', 'options' => array ('abovecomments', 'oncaptionline', 'abovestats', 'belowcomments'),	'text' => _("Position of the add vote and add comment links"))
	);

	$filters = array(
		'ty'	=> array('type' => 'text', 'default' => '', 'text' => _("Filter by year")),
		'tm'	=> array('type' => 'text', 'default' => '', 'text' => _("Filter by month")),
		'td'	=> array('type' => 'text', 'default' => '', 'text' => _("Filter by day")),
	);

	echo makeFormIntro("stats.php", array("name" => "stats_form", "method" => "POST"));
	echo "\n<table width=\"100%\" border=\"0\">";
	echo "\n<tr>";
	echo "\n<td class=\"blockcell\">";
		stats_showBlock($types, _("Type"));
	echo "\n</td>";

	echo "\n<td class=\"blockcell\">";
		stats_showBlock($options, _("Options"));
	echo "\n\t</td>";
	echo "\n</tr>";
	echo "\n<tr>";
	echo "\n<td class=\"blockcell\">";
		stats_showBlock($layout, _("Layout"));
	echo "\n\t</td>";
	
	echo "\n<td class=\"blockcell\">";
		stats_showBlock($filters, _("Filter by Capture Date"));
	echo "\n\t</td>";
	
	echo "\n</tr>";
	echo "\n</table>";
	echo "\n". '<input type="submit" value="'. _("Show me the statistic") . '">';
	echo "\n</form>";
?>
</body>
</html>
