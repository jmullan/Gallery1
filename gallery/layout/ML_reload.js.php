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
