<?php if ($gallery->app->ML_mode == 3 && !$gallery->session->offline) { ?>
<script language="JavaScript">
function ML_reload() {
	var newlang=document.MLForm.newlang[document.MLForm.newlang.selectedIndex].value ;
	window.location.href=newlang;
}
</script>

	<form name="MLForm">
	<table width="100%">
	<tr>
<td valign="top" align="right">
    <select style="font-size:8pt;" name="newlang" onChange="ML_reload()" size="1">
<?php
	if ($GALLERY_EMBEDDED_INSIDE) {
		$urlTemp = ($HTTP_GET_VARS['include'] ? $HTTP_GET_VARS['include'] : '');        
	} else {
        	$urlTemp = end(explode("/", $HTTP_SERVER_VARS["PHP_SELF"]));
	}
	$nls = getNLS();
	foreach ($gallery->app->available_lang as $value) {
		// Only use language from config.php if its defined in nls.php
		if (! isset($nls['languages'][$value])) continue;
		if (isset($GALLERY_EMBEDDED_INSIDE) && ! $nls['nuke'][$value]) continue;
		
		$args = $HTTP_GET_VARS;
		if ($GALLERY_EMBEDDED_INSIDE) {
			$args["newlang"]=$nls['nuke'][$value];
		} else {
			$args["newlang"]=$value;
		}

		if ($gallery->language == $value) $selected="selected"; else $selected="";
		echo "\n\t\t<option value=\"".makeGalleryUrl(
				$urlTemp,
				$args)."\" $selected>" . $nls['languages'][$value] ."</option>";
	}
?>
	</select>
	</td></tr></table>
	</form>
<?php } ?>
