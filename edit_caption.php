<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2003 Bharat Mediratta
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
 *
 * $Id$
 */
?>
<?php
// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
	print "Security violation\n";
	exit;
}
?>
<?php if (!isset($GALLERY_BASEDIR)) {
    $GALLERY_BASEDIR = '';
}
require($GALLERY_BASEDIR . 'init.php'); ?>
<?php
// Hack check
if (!$gallery->user->canChangeTextOfAlbum($gallery->album)) {
	exit;
}
$err = "";	
if (!strcmp($submit, "Save")) {
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
		foreach ($extra_fields as $field => $value)
		{
			$gallery->album->setExtraField($index, $field, trim(strip_tags($value)));
		}
		$gallery->album->save();
		dismissAndReload();
		if (!isDebugging()) {
			return;
		}
	} else {
		$err = "Year must be between 1000 and 3000";
	}
}
?>
<html>
<head>
  <title>Edit Text</title>
  <?php echo getStyleSheetLink() ?>
</head>
<body>

<center>
<?php echo $gallery->album->getThumbnailTag($index) ?>
</center>

<table>
<tr><td valign=top><b>Caption:</b></td>
<?php echo makeFormIntro("edit_caption.php", 
			array("name" => "theform", 
				"method" => "POST")); ?>
<input type=hidden name="index" value="<?php echo $index ?>">
<td><textarea name="data" rows=4 cols=40>
<?php echo $gallery->album->getCaption($index) ?>
</textarea></td></tr>
<?php
foreach ($gallery->album->getExtraFields() as $field)
{
	if ($field == "Capture Date" || $field == "Upload Date")
	{
		continue;
	}
        $value=$gallery->album->getExtraField($index, $field);
	if ($field == "Title")
	{
		print "<tr><td valign=top><b>Title:<b></td><td>";
		print "<input type=text name=\"extra_fields[$field]\" value=\"$value\" size=\"40\">";
	}
	else
	{
		print "<tr><td valign=top><b>$field:<b></td><td>";
		print "<textarea name=\"extra_fields[$field]\" rows=4 cols=40>";
		print "$value</textarea>";
	}
	print "</td></tr>";
}
?>
<tr><td valign=top><b>Keywords:</b></td>
<td><textarea name="keywords" rows=1 cols=40>
<?php echo $gallery->album->getKeywords($index) ?>
</textarea></td></tr>

</table>
<?php
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
<table border=0>
  <tr>
    <td colspan="6" align="center">Photo Capture Date</td>
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
<?php
// start making drop downs
echo "<td>";
echo drawSelect("capture_mon", padded_range_array(1, 12), $mon, 1);
echo "</td>";

echo "<td>";
echo drawSelect("capture_mday", padded_range_array(1, 31), $mday, 1);
echo "</td>";

echo "<td>";
echo "<input type=text name=\"capture_year\" value=\"$year\" size=\"4\">";
echo "</td>";

echo "<td>";
echo drawSelect("capture_hours", padded_range_array(0, 23), $hours, 1);
echo "</td>";

echo "<td>";
echo drawSelect("capture_minutes", padded_range_array(0, 59), $minutes, 1);
echo "</td>";

echo "<td>";
echo drawSelect("capture_seconds", padded_range_array(0, 59), $seconds, 1);
echo "</td>";
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
