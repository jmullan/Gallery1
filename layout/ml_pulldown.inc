<?php if ($gallery->app->ML_mode == 3 && !$gallery->session->offline) {

if($gallery->app->show_flags !='yes') {?>
<script language="JavaScript" type="text/javascript">
function ML_reload() {
	var newlang=document.MLForm.newlang[document.MLForm.newlang.selectedIndex].value ;
	window.location.href=newlang;
}
</script>
<?php } ?>

	<form name="MLForm" action="#">
	<table width="100%">
	<tr><td valign="top" align="right">

<?php

if($gallery->app->show_flags !='yes') {
	echo '<select style="font-size:8pt;" name="newlang" onChange="ML_reload()" size="1">';
} else {
	echo '<table border="0"><tr>';
}

	global $GALLERY_EMBEDDED_INSIDE_TYPE;

	// we have 2+1 ways: phpnuke, postnuke or standalone
	if ($GALLERY_EMBEDDED_INSIDE) {
		$urlTemp = ($HTTP_GET_VARS['include'] ? $HTTP_GET_VARS['include'] : '');        
	} else {
        	$urlTemp = end(explode("/", $HTTP_SERVER_VARS["PHP_SELF"]));
	}
	$nls = getNLS();
	foreach ($gallery->app->available_lang as $value) {
		// Only use language from config.php if its defined in nls.php
		if (! isset($nls['language'][$value])) continue;

		if (!language_exists($value)) {
			continue;
		}

		$args = $HTTP_GET_VARS;
		if (isset($GALLERY_EMBEDDED_INSIDE)) {
			if ($GALLERY_EMBEDDED_INSIDE_TYPE == 'postnuke') {
				/* postnuke */
				if (! isset($nls['postnuke'][$value])) continue;
				$args["newlang"]=$nls['postnuke'][$value];
			}
			else {
				/* phpnuke */ 
				if (! isset($nls['phpnuke'][$value])) continue;
				$args["newlang"]=$nls['phpnuke'][$value];
			} 
		} else {
			$args["newlang"]=$value;
		}

		if($gallery->app->show_flags !='yes') {
			if ($gallery->language == $value) $selected="selected"; else $selected="";
			echo "\n\t\t<option value=\"".makeGalleryUrl(
					$urlTemp,
					$args)."\" $selected>" . $nls['language'][$value] ."</option>";
		} else {
			echo "\n\t\t<td><a href=\"". makeGalleryUrl( $urlTemp, $args) . "\"><img src=\"$GALLERY_BASEDIR/images/flags/$value.gif\" border=\"0\" alt=\"" .$nls['language'][$value] . "\" title=\"" .$nls['language'][$value] . "\"></a></td>";
		}
	}

if($gallery->app->show_flags !='yes') {
	echo "</select>";
} else {
	echo "</tr></table>";
}
?>
	</td></tr></table>
	</form>
<?php } ?>