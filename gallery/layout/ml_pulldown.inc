<?php
/*
  $Id$

  Gallery - a web based photo album viewer and editor
  Copyright (C) 2000-2004 Bharat Mediratta

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or (at
  your option) any later version.

  This program is distributed in the hope that it will be useful, but
  WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
  General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

*/
?>

<?php 
global $GALLERY_EMBEDDED_INSIDE, $GALLERY_EMBEDDED_INSIDE_TYPE, $HTTP_SERVER_VARS;

if ($gallery->app->ML_mode == 3 && !$gallery->session->offline && sizeof($gallery->app->available_lang) > 1) {
    
if($gallery->app->show_flags !='yes') {?>
<script language="JavaScript" type="text/javascript">
function ML_reload() {
	var newlang=document.MLForm.newlang[document.MLForm.newlang.selectedIndex].value ;
	window.location.href=newlang;
}
</script>
<?php } ?>
	<form name="MLForm" action="#">
	<table width="100%" cellpadding="0" cellspacing="0">
	<tr><td valign="top" align="right">
<?php

if($gallery->app->show_flags !='yes') {
	echo '<select style="font-size:8pt;" name="newlang" onChange="ML_reload()" size="1">';
}
	$nls = getNLS();

	$count=0;
	$half=sizeof($gallery->app->available_lang)/2;
	foreach ($gallery->app->available_lang as $value) {

		/* 
		** We only allow show languages which are available in gallery.
		** These could differ to the languages defined in config.php.
		*/
		if (! isset($nls['language'][$value])) continue;

		$count++;
		$args = isset($HTTP_GET_VARS) ? $HTTP_GET_VARS : array();
		if (isset($GALLERY_EMBEDDED_INSIDE) && $GALLERY_EMBEDDED_INSIDE=='nuke') {
			if ($GALLERY_EMBEDDED_INSIDE_TYPE == 'postnuke') {
				/* postnuke */
				if (! isset($nls['postnuke'][$value])) continue;
				$new_lang=$nls['postnuke'][$value];
			}
			else {
				/* phpnuke */ 
				if (! isset($nls['phpnuke'][$value])) continue;
				$new_lang=$nls['phpnuke'][$value];
			} 
		} else {
			$new_lang=$value;
		}

		/* now we buil the URL according to the new language */
		$request_url=$HTTP_SERVER_VARS['REQUEST_URI'];
		$pos=strpos($request_url, "newlang");
		if ($pos >0) {
			$request_url=substr($request_url,0,$pos-1);
		}
	        if (stristr($request_url,"?")) {
			$url=$request_url ."&newlang=". $new_lang;
		}
		else {
			$url=$request_url ."?newlang=". $new_lang;
		}
		$url = htmlspecialchars($url);
	
		/* Show pulldown or flags */
		if($gallery->app->show_flags !='yes') {
			if ($gallery->language == $value) $selected="selected"; else $selected="";
			echo "\n\t\t<option value=\"$url\" $selected>" . $nls['language'][$value] ."</option>";
		} else {
			$flagname=$value;
			echo "\n\t\t";
			if ($gallery->language != $value) {
				$style="";
				echo "<a href=\"$url\">";
			}
			else {
				$style="style=\"margin:7px\"";
			}
			echo "<img $style src=\"". $gallery->app->photoAlbumURL . "/locale/$flagname/flagimage/$flagname.gif\" border=\"1\" alt=\"" .$nls['language'][$value] . "\" title=\"" .$nls['language'][$value] . "\">";
			if ($gallery->language != $value) {
				echo "</a>";
			}
			if ($count > $half && $half >10) {
				echo "<br>";
				$count=0;
			}
		}
	}

if($gallery->app->show_flags !='yes') {
	echo "</select>";
}
?>
	</td></tr></table>
	</form>
<?php } ?>
