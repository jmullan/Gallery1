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
	<table width="100%" cellpadding="0" cellspacing="0">
	<tr><td valign="top" align="right"><br />

<?php

if($gallery->app->show_flags !='yes') {
	echo '<select style="font-size:8pt;" name="newlang" onChange="ML_reload()" size="1">';
}

	global $GALLERY_EMBEDDED_INSIDE_TYPE;

	// we have 2+1 ways: phpnuke, postnuke or standalone
	if ($GALLERY_EMBEDDED_INSIDE) {
		$urlTemp = ($HTTP_GET_VARS['include'] ? $HTTP_GET_VARS['include'] : '');        
	} else {
        	$urlTemp = end(explode("/", $HTTP_SERVER_VARS["PHP_SELF"]));
	}
	$nls = getNLS();

	$count=0;
	foreach ($gallery->app->available_lang as $value) {

		// Only use language from config.php if its defined in nls.php
		if (! isset($nls['language'][$value])) continue;

		if (!language_exists($value)) {
			continue;
		}

		$count++;
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
			if (isset($nls['flag'][$value])) {
				$flagname=$nls['flag'][$value];
			} else {
				$flagname=$value;
			}
			echo "\n\t\t<a href=\"". makeGalleryUrl( $urlTemp, $args) . "\"><img src=\"". $gallery->app->photoAlbumURL ."/images/flags/$flagname.gif\" border=\"0\" alt=\"" .$nls['language'][$value] . "\" title=\"" .$nls['language'][$value] . "\"></a>";
		}
	}

if($gallery->app->show_flags !='yes') {
	echo "</select>";
}
?>
	</td></tr></table>
	</form>
<?php } ?>