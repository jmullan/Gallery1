<?php if ($gallery->app->ML_mode == 3) { ?>
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
		$language_names=$nuke_langname ;
	} else {
		$language_names=$langname;
	}
	$nls = getNLS();
	foreach ($gallery->app->available_lang as $value) {
		if ($gallery->language == $value) $selected="selected"; else $selected="";
		$args=$_GET;
		$args["newlang"]=$value;
		echo "\n\t\t<option value=\"".makeGalleryUrl(
				end(explode("/", $_SERVER["PHP_SELF"])),
				$args)."\" $selected>" . $nls['languages'][$value] ."</option>";
	}
?>
	</select>
	</td></tr></table>
	</form>
<?php } ?>
