<?php

// $Id$
// Note that this box is assumed to be attached to a navigator and uses the navigator's
// width parameters .

if ($gallery->app->ML_mode == 3 and $adminbox["top"]) {
	?>
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
<?php }

// If the border color is not passed in, we do a black/white combo
if ($adminbox["bordercolor"]) {
	$borderIn = $adminbox["bordercolor"];
} else {
	$borderIn = "#FFFFFF";
}
$imageDir = $gallery->app->photoAlbumURL."/images";
$pixelImage = "<img src=\"$imageDir/pixel_trans.gif\" width=\"1\" height=\"1\">";

?>
<table width="<?php echo $navigator["fullWidth"] . $navigator["widthUnits"] ?>" border="0" cellspacing="0" cellpadding="0">
<?php
if ($adminbox["top"]) {
?>
  <tr> 
    <td colspan="4" bgcolor="<?php echo $borderIn ?>"><?php echo $pixelImage ?></td>
  </tr>
<?php
}
?>
  <tr> 
    <td bgcolor="<?php echo $borderIn ?>" width="1" height="18"><?php echo $pixelImage ?></td>
    <td align="left" valign="middle" width="3000" height="18">&nbsp;<?php echo $adminbox["text"] ?></td>
    <td align="right" valign="middle" width="3000" height="18">&nbsp;<?php echo $adminbox["commands"] ?>&nbsp;</td>
<?php 
	if ($gallery->app->ML_mode == 3 and $adminbox["top"]) {

		if (stristr($HTTP_SERVER_VARS['REQUEST_URI'],"slideshow") == FALSE) {
			echo "    <form name=\"TopForm\">";
		}
?>
    <td valign="bottom">
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
<?php
	if (stristr($HTTP_SERVER_VARS['REQUEST_URI'],"slideshow") == FALSE) echo "\n   </form>\n";
	}
?>
<td bgcolor="<?php echo $borderIn ?>" width="1" height="18"><?php echo $pixelImage ?></td>
  </tr>
<?php
if (!$adminbox["top"]) {
?>
  <tr> 
    <td colspan="4" height="1" bgcolor="<?php echo $borderIn ?>"><?php echo $pixelImage ?></td>
  </tr>
<?php
}
?>
</table>    
