<?php if ($gallery->app->ML_mode == 3) { ?>
<script language="JavaScript">
function ML_reload() {
	var newlang=document.TopForm.newlang[document.TopForm.newlang.selectedIndex].value ;
	<?php
		$len=strpos($HTTP_SERVER_VARS['REQUEST_URI'],"&newlang");
		if ($len == FALSE) $len=strlen($HTTP_SERVER_VARS['REQUEST_URI']);
		$path=substr($HTTP_SERVER_VARS['REQUEST_URI'],0, $len);
		if (strpos($path,"?") == FALSE) $path=$path ."?";
	?>
	var new_path='<?php echo $path ?>&newlang='+ newlang;
//	alert('<?php echo $HTTP_SERVER_VARS['REQUEST_URI'] ?>' + ' <-----> ' + new_path);
	window.location.href=new_path;
}
</script>

	<form name="TopForm">
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
		echo "\n\t\t<option value=\"$value\" $selected>" . $nls['languages'][$value] ."</option>";
	}
?>
	</select>
	</td>
	</form>
<?php } ?>
