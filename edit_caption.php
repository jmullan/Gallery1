<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2005 Bharat Mediratta
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
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * $Id$
 */
?>
<?php

require_once(dirname(__FILE__) . '/init.php');

list($save, $data, $keywords, $index, $extra_fields) = getRequestVar(array('save', 'data', 'keywords', 'index', 'extra_fields'));
list($capture_year, $capture_mon, $capture_mday, $capture_hours, $capture_minutes, $capture_seconds) = 
	getRequestVar(array('capture_year', 'capture_mon', 'capture_mday', 'capture_hours', 'capture_minutes', 'capture_seconds'));

// Hack check
if (!$gallery->user->canChangeTextOfAlbum($gallery->album) && !($gallery->album->isItemOwner($gallery->user->getUid(), $index) && $gallery->album->getItemOwnerModify())) {
	echo _("You are not allowed to perform this action!");
	exit;
}
$err = '';

doctype();
echo "\n<html>";
if (isset($save)) {
    // Only allow dates which mktime() will operate on.
    // 1970-2037 (Windows and some UNIXes) -- 1970-2069 (Some UNIXes)
    // Two digit values between 0-69 mapping to 2000-2069 and 70-99 to 1970-1999
    if ((($capture_year < 2070) && ($capture_year > 1969)) || ($capture_year < 100)) {
        $gallery->album->setCaption($index, $data);
        $gallery->album->setKeywords($index, $keywords);
        $dateArray["year"] = $capture_year;
        $dateArray["mon"] = $capture_mon;
        $dateArray["mday"] = $capture_mday;
        $dateArray["hours"] = $capture_hours;
        $dateArray["minutes"] = $capture_minutes;
        $dateArray["seconds"] = $capture_seconds;

        $timestamp = mktime($capture_hours, $capture_minutes, $capture_seconds, $capture_mon, $capture_mday, $capture_year);
        $gallery->album->setItemCaptureDate($index, $timestamp);
        if (isset($extra_fields)) {
            foreach ($extra_fields as $field => $value){
                $gallery->album->setExtraField($index, $field, trim($value));
            }
        }
        $gallery->album->save(array(i18n("Captions and/or custom fields modified for %s"),
        makeAlbumURL($gallery->album->fields["name"], $gallery->album->getPhotoId($index))));
        dismissAndReload();
        if (!isDebugging()) {
            return;
        }
    } else {
        $err = _("Year must be between 1969 and 2070");
    }
}
?>
<head>
  <title><?php echo _("Edit Text") ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>" class="popupbody">
<div class="popuphead"><?php echo _("Edit Caption"); ?></div>
<div class="popup" align="center">
	<?php echo $gallery->album->getThumbnailTag($index) ?>

<?php echo makeFormIntro("edit_caption.php", 
		array("name" => "theform", "method" => "POST"),
		array("type" => "popup"));
?>

<input type="hidden" name="index" value="<?php echo $index ?>">
<table>
<tr>
	<td style="vertical-align: top"><b><?php echo _("Caption") ?>:</b></td>
	<td><textarea name="data" rows="4" cols="40"><?php echo $gallery->album->getCaption($index) ?></textarea></td>
</tr>
<?php

$translateableFields = translateableFields();

foreach ($gallery->album->getExtraFields() as $field) {
	if (in_array($field, array_keys(automaticFieldsList()))) {
		continue;
	}
    $value = $gallery->album->getExtraField($index, $field);

	if (in_array($field, array_keys($translateableFields))) {
		$fieldLabel = $translateableFields[$field];
		$rows = 1;
	} else {
		$fieldLabel = $field;
		$rows = 3;
	}

	echo "\n<tr>";		
	echo "\n\t". '<td style="vertical-align: top; font-weight:bold">'. $fieldLabel .':</td>';
	echo "\n\t". '<td><textarea name="extra_fields['. $field .']" rows="'. $rows .'" cols="40">'. $value .'</textarea></td>';
	echo "\n</tr>";
}
?>

<tr>
	<td valign=top><b><?php echo _("Keywords") ?>:</b></td>
	<td><textarea name="keywords" rows="1" cols="40"><?php echo $gallery->album->getKeywords($index) ?></textarea></td>
</tr>
</table>

<?php
// get the itemCaptureDate
if (!empty($err)) {
	echo "\n<p>". gallery_error($err) . "</p>";
}
$itemCaptureDate = $gallery->album->getItemCaptureDate($index);

$hours 	 = strftime('%H', $itemCaptureDate);
$minutes = strftime('%M', $itemCaptureDate);
$seconds = strftime('%S', $itemCaptureDate);
$mon 	 = strftime('%m', $itemCaptureDate);
$mday 	 = strftime('%d', $itemCaptureDate);
$year 	 = strftime('%Y', $itemCaptureDate);
// start capture date table
?>

<br>
<table border="0">
  <tr>
	<td colspan="6" align="center"><?php echo _("Photo Capture Date") ?></td>
  </tr>
  <tr>
    <td><?php echo _("Month") ?></td>
    <td><?php echo _("Day") ?></td>
    <td><?php echo _("Year") ?></td>
    <td><?php echo _("Hours") ?></td>
    <td><?php echo _("Minutes") ?></td>
    <td><?php echo _("Seconds") ?></td>
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

<p>
	<input type="submit" name="save" value="<?php echo _("Save") ?>">
	<input type="button" name="cancel" value="<?php echo _("Cancel") ?>" onclick='parent.close()'>
</p>

</form>

<script language="javascript1.2" type="text/JavaScript">
<!--   
// position cursor in top form field
document.theform.data.focus();
//-->
</script>
</div>
<?php print gallery_validation_link("edit_caption.php", true, array('index' => $index)); ?>
</body>
</html>
