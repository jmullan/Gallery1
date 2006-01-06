<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2006 Bharat Mediratta
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

if (!$GALLERY_EMBEDDED_INSIDE) {
    doctype();
?>
<html>
<head>
<title><?php echo $gallery->app->galleryTitle ?>::<?php echo _("Gallery statistics - Wizard") ?></title>
<?php 
	common_header() ;
?>
</head>
<body dir="<?php echo $gallery->direction ?>">
<?php  
}
    includeHtmlWrap("gallery.header");

    $adminbox['text'] ='<span class="head">'. _("Gallery statistics - Wizard") .'</span>';
    $adminCommands = '[<a href="'. makeGalleryUrl("admin-page.php") .'">'. _("return to admin page") .'</a>] ';
    $adminCommands .= '[<a href="'. makeAlbumUrl() .'">'. _("return to gallery") .'</a>] ';

    $adminbox["commands"] = $adminCommands;
    $adminbox["bordercolor"] = $gallery->app->default["bordercolor"];
    $breadcrumb['text'][] = languageSelector();

    includeLayout('navtablebegin.inc');
    includeLayout('adminbox.inc');
    includeLayout('navtablemiddle.inc');
    includeLayout('breadcrumb.inc');
    includeLayout('navtableend.inc');

?>
<div class="popup" align="center">
<?php
/* note: the script is below as the header of the environment needs to loaded before. */
?>
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
			document.stats_form.showGrid.checked == false) {
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
	echo makeFormIntro("stats.php", array("name" => "stats_form", "onChange" => 'updateUrl()'));
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

	echo "\n". '<div align="left">';
	echo _("Maybe your want to use your OWN statistics somewhere .. Just copy and paste the url from this textbox.");
	echo "\n<br>". '<form name="url_form" action="#">';
	echo "\n". '<input type="text" name="stats_url" size="150" value="" readonly';
	echo "\"</div>";
	echo "\n</form>";

?>
<script type="text/javascript">
  // Run the script at the when page is showed.
  // We could do this onLoad, but this doesnt work embedded.
  updateUrl();
</script>
</div>
<?php
includeHtmlWrap("stats.footer");

if (!$GALLERY_EMBEDDED_INSIDE) { ?>
</body>
</html>
<?php } ?>
