<?
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000 Bharat Mediratta
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */
?>
<? require($GALLERY_BASEDIR . "init.php"); ?>
<?
// Hack check
if (!$gallery->user->canChangeTextOfAlbum($gallery->album)) {
	exit;
}
$err = "";	
if ($save) {
	if (($capture_year < 3000) && ($capture_year > 1000)) { // only allow photo capture dates from 1000 to 3000.
		$gallery->album->setCaption($index, stripslashes($data));
		$gallery->album->setKeywords($index, stripslashes($keywords));
		$dateArray["year"] = $capture_year;	
		$dateArray["mon"] = $capture_mon;
		$dateArray["mday"] = $capture_mday;
		$dateArray["hours"] = $capture_hours;
		$dateArray["minutes"] = $capture_minutes;
		$dateArray["seconds"] = $capture_seconds;
		$gallery->album->setItemCaptureDate($index, $dateArray );
		$gallery->album->save();
		dismissAndReload();
		return;
	} else {
		$err = "Year must be between 1000 and 3000";
	}
}
?>
<html>
<head>
  <title>Edit Caption</title>
  <?= getStyleSheetLink() ?>
</head>
<body>

<center>
Enter a caption for this picture in the text
box below.
<br><br>
<?= $gallery->album->getThumbnailTag($index) ?>

<form name="theform" action=edit_caption.php method=POST>
<input type=hidden name="save" value=1>
<input type=hidden name="index" value="<?= $index ?>">
<textarea name="data" rows=5 cols=40>
<?= $gallery->album->getCaption($index) ?>
</textarea>
<br><br>
Enter "keywords" for this photo in the text box below.
<br><br>
<textarea name="keywords" rows=1 cols=40>
<?= $gallery->album->getKeywords($index) ?>
</textarea>

<br><br>

<?
// get the itemCaptureDate
echo "<span class=error>$err</span><br><br>";
$itemCaptureDate = $gallery->album->getItemCaptureDate($index);

$hours = $itemCaptureDate["hours"];
$minutes = $itemCaptureDate["minutes"];
$seconds = $itemCaptureDate["seconds"];
$mon = $itemCaptureDate["mon"];
$mday = $itemCaptureDate["mday"];
$year = $itemCaptureDate["year"];
// start capture date table
?>
<table>
  <tr>
    <td colspan="5" align="center">Photo Capture Date</td>
  </tr>
  <tr>
    <td>Month</td>
    <td>Day</td>
    <td>Year</td>
    <td>Hours</td>
    <td>Minutes</td>
    <td>Seconds</td>
  </tr>
  <tr>
<?
// start making drop downs
echo "<td><select name=\"capture_mon\">";
for ($i = 1; $i <= 12; $i++) {
	if ($i == $mon) {
		$sel = "selected";
	} else {
		$sel = "";
	}
	echo "<option value=\"$i\" $sel>$i</option>"; 
}
echo "</select></td>\n";
echo "<td><select name=\"capture_mday\">";
for ($i = 1; $i <= 31; $i++) {
	if ($i == $mday) {
		$sel = "selected";
	} else {
		$sel = "";
	}
	echo "<option value = \"$i\" $sel>$i</option>";
}
echo "</select></td>\n";
echo "<td><INPUT TYPE=TEXT NAME=\"capture_year\" VALUE=$year SIZE=4></td>";
echo "<td><select name=\"capture_hours\">";
for ($i = 1; $i <= 23; $i++) {
	if ($i == $hours) {
		$sel = "selected";
	} else {
		$sel = "";
	}
	echo "<option value = \"$i\" $sel>$i</option>";
}
echo "</select></td>\n";
echo "<td><select name=\"capture_minutes\">";
for ($i = 0; $i <= 59; $i++) {
        if ($i == $minutes) {
                $sel = "selected";
        } else {
                $sel = "";
        }
        echo "<option value = \"$i\" $sel>$i</option>";
}
echo "</select></td>\n";
echo "<td><select name=\"capture_seconds\">";
for ($i = 0; $i <=59; $i++) {
	if ($i == $seconds) {
		$sel = "selected";
	} else {
		$sel = "";
	}
	echo "<option value = \"$i\" $sel>$i</option>";
}
echo "</select></td>\n";
?>
  </tr>
</table>
<br><br>
<input type=submit name="submit" value="Save">
<input type=submit name="submit" value="Cancel" onclick='parent.close()'>


</form>

<script language="javascript1.2">
<!--   
// position cursor in top form field
document.theform.data.focus();
//-->
</script>

</body>
</html>
