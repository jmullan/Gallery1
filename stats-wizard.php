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

require_once(dirname(__FILE__) . '/includes/stats/stats.inc.php');

doctype();
?>

<html>
<head>
<title><?php echo $gallery->app->galleryTitle ?></title>
<?php 
	common_header() ;
?>
  <style type="text/css">
	.blockcell { vertical-align: top; border-bottom: 1px solid #000000 }
	caption	{ font-weight:bold; margin-bottom: 5px}
  </style>
</head>
<body dir="<?php echo $gallery->direction ?>" onLoad="updateUrl()">
<script type="text/javascript">
  function updateUrl() {
	var value;
	var url;
	url='<?php echo makeGalleryUrl('stats.php') .'?'; ?>';

	/* This javascript goes through all elements of the form 'stats_form'
	** depending if set or not it generates an string that represents the parameters for stats.php
	*/
	for(var i=0;i<document.stats_form.length; i++) {
		value = false;
		/* special case */
		if ((document.stats_form.elements[i].name == 'cols' || document.stats_form.elements[i].name == 'rows') &&
			document.stats_form.sgr.checked == false) {
			continue;
		}
		switch(document.stats_form.elements[i].type) {
			case 'submit':
				continue;
			break;
			
			case 'checkbox':
				if(document.stats_form.elements[i].checked) {
					value = 1;
				}
			break;

			case 'radio':
				if (document.stats_form.elements[i].checked) {
					value = document.stats_form.elements[i].value;
				}
			break;
			
			default:
				value = document.stats_form.elements[i].value;
			break;
		}
		if (value) {
			url = url + '&'+ document.stats_form.elements[i].name +'=' + value;
		}
	}
	document.url_form.stats_url.value = url;
}
</script>
<?php  
	$stats_title = " - " . _("Wizard");
        includeHtmlWrap("stats.header");
?>
<div style="text-align:right">[<a href="<?php echo makeAlbumUrl(); ?>"><?php echo _("return to gallery"); ?></a>]</div>

<?php
	echo makeFormIntro("stats.php", array("name" => "stats_form", 
						"method" => "POST", 
						"onChange" => 'updateUrl()'));
	echo "\n<table width=\"100%\" border=\"0\">";
	echo "\n<tr>";
	echo "\n<td class=\"blockcell\">";
		stats_showBlock($stats['types'], _("Type"));
	echo "\n</td>";

	echo "\n<td class=\"blockcell\">";
		stats_showBlock($stats['options'], _("Options"));
	echo "\n\t</td>";
	echo "\n</tr>";
	echo "\n<tr>";
	echo "\n<td class=\"blockcell\">";
		stats_showBlock($stats['layout'], _("Layout"));
	echo "\n\t</td>";
	
	echo "\n<td class=\"blockcell\">";
		stats_showBlock($stats['filter'], _("Filter by Capture Date"));
	echo "\n\t</td>";
	
	echo "\n</tr>";
	echo "\n</table>";
	echo "\n". '<input type="submit" name="submitbutton" value="'. _("Show statistics") . '">';
	echo "\n</form>";

	echo _("Maybe your want to use your OWN statistics somewhere .. Just copy and paste the url from this textbox.");
	echo "\n<br>". '<form name="url_form" action="#">';
	echo "\n". '<input type=text" name="stats_url" size="150" value="" readonly';
	echo "\n</form>";


includeHtmlWrap("stats.footer");

if (!$GALLERY_EMBEDDED_INSIDE) { ?>
</body>
</html>
<?php } ?>
