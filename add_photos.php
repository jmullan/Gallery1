<?
require('style.php');
if (!$boxes) {
	$boxes = 5;
}
	
?>

<center>

<b>Add Photos</b>
<br>
Click the <b>Browse</b> button to locate a photo to upload.
<br>
Tip:  Upload a ZIP file full of photos and movies!
<br>
<font size=+0>(Supported file types: JPG, GIF, PNG, AVI, MPG)</font>

<br>
<form enctype="multipart/form-data" action="save_photos.php" method=post>
<input type="hidden" name="max_file_size" value="10000000">
<? for ($i = 0; $i < $boxes;  $i++) { ?>
<br> <input name="userfile[]" type="file">
<? } ?>
<br> URL <input name="url">

<p>
<input type="submit" value="Send Files">
<input type=submit name="submit" value="Cancel" onclick='parent.close()'>
</form>

<? if ($boxes < 10) { ?>
<table bordercolor=black cellpadding=0 cellspacing=0 border=1><tr><td>
<table width=100% bgcolor=#9999CC>
<tr><td align=center>
<font face=arial size=+1>
<a href=add_photos.php?boxes=<?=$boxes+5?>>More boxes, please!</a>
<br>
<font size=2>
Warning! you'll lose what you've already entered
</font>
</td></tr>
</table>
<? } ?>

