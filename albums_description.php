<?
if ($name) {
	$album->load($name);
}
?>

<? if ($name) { ?> 
<BODY onLoad='show()' bgcolor=#EEDDFF>
<? } else { ?>
<BODY bgcolor=#EEDDFF>
<? } ?>

<SCRIPT LANGUAGE=JavaScript>
var hiding = null

function show() { 
	clearTimeout(hiding)
        newSize = "*,"+document.body.scrollHeight
	parent.document.body.rows = newSize
	hiding = setTimeout("hide()",5000)
}

function hide() {
	parent.document.body.rows = "*,0"
}
</SCRIPT>

<center>
<font face=arial size=+1>
<?= editField($album, "description", $edit); ?>
