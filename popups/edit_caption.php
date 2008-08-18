<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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

require_once(dirname(dirname(__FILE__)) . '/init.php');

$index = getRequestVar('index');

// Hack checks
if (! isset($gallery->album) || ! isset($gallery->session->albumName) ||
    ! ($item = $gallery->album->getPhoto($index)))
{
	printPopupStart(gTranslate('core', "Edit texts"));
	showInvalidReqMesg();
	exit;
}

// Hack check
if (!$gallery->user->canChangeTextOfAlbum($gallery->album) &&
    !($gallery->album->isItemOwner($gallery->user->getUid(), $index) &&
    $gallery->album->getItemOwnerModify()))
{
	printPopupStart(gTranslate('core', "Edit texts"));
	echo showInvalidReqMesg(gTranslate('core', "You are not allowed to perform this action!"));
	exit;
}

list($save, $saveclose) = getRequestVar(array('save', 'saveclose'));

list($caption, $description, $keywords, $extra_fields, $captureDate) =
	getRequestVar(array('caption', 'description', 'keywords', 'extra_fields', 'captureDate'));

$infoMessages = array();

if (isset($save) || isset($saveclose)) {
	// Only allow dates which mktime() will operate on.
	// 1970-2037 (Windows and some UNIXes) -- 1970-2069 (Some UNIXes)
	// Two digit values between 0-69 mapping to 2000-2069 and 70-99 to 1970-1999
	if (((int)$capture_year < 2070 && (int)$capture_year > 1969) || (int)$capture_year < 100) {
		$gallery->album->setCaption($index, $caption);
		$gallery->album->setDescription($index, $description);
		$gallery->album->setKeywords($index, $keywords);

		$dateArray['year']		= $capture_year;
		$dateArray['mon']		= $capture_mon;
		$dateArray['mday']		= $capture_mday;
		$dateArray['hours']		= $capture_hours;
		$dateArray['minutes']	= $capture_minutes;
		$dateArray['seconds']	= $capture_seconds;

		$timestamp = mktime($capture_hours, $capture_minutes, $capture_seconds, $capture_mon, $capture_mday, $capture_year);
		$gallery->album->setItemCaptureDate($index, $timestamp);

		if (isset($extra_fields)) {
			foreach ($extra_fields as $field => $value){
				$gallery->album->setExtraField($index, $field, trim($value));
			}
		}

		$status = $gallery->album->save(
			array(
				i18n("Captions and/or custom fields modified for %s"),
				makeAlbumURL($gallery->album->fields["name"], $gallery->album->getPhotoId($index)))
		);

		if($status) {
			if(isset($saveclose)) {
				dismissAndReload();
				exit;
			}

			$infoMessages[] = array(
				'type' => 'success',
				'text' => gTranslate('core', "Successfully saved.")
			);
		}
		else {
			$infoMessages[] = array(
				'type' => 'error',
				'text' => gTranslate('core', "Gallery was not able to save the data.")
			);
		}
	}
	else {
		$infoMessages[] = array(
			'type' => 'error',
			'text' => gTranslate('core', "Year must be between 1969 and 2070.")
		);
	}
}

printPopupStart(
	gTranslate('core', "Edit texts"),
	sprintf(gTranslate('core', "Edit texts for '<i>%s</i>'"), $gallery->album->getCaption($index)),'', 'left');

if(isset($save)) {
	reload();
}

echo '<p class="center">';
echo $gallery->album->getThumbnailTag($index);
echo '</p>';

echo infoBox($infoMessages);

echo makeFormIntro('edit_caption.php', array(), array('type' => 'popup'));
?>

<input type="hidden" name="index" value="<?php echo $index ?>">
<table>
<tr>
	<td colspan="2" class="g-double-bottom-border-spacer">&nbsp;<?php echo gTranslate('core', "Fixed fields"); ?></td>
</tr>

<tr>
	<td style="vertical-align: top" class="g-emphasis"><?php echo gTranslate('core', "Caption") ?></td>
	<td><textarea name="caption" rows="2" cols="38"><?php echo $gallery->album->getCaption($index) ?></textarea></td>
</tr>
<tr>
	<td style="vertical-align: top" class="g-emphasis"><?php echo gTranslate('core', "Description") ?></td>
	<td><textarea name="description" rows="5" cols="38"><?php echo $gallery->album->getdescription($index) ?></textarea></td>
</tr>
<tr>
	<td valign=top><b><?php echo gTranslate('core', "Keywords") ?></b></td>
	<td><textarea name="keywords" rows="1" cols="38"><?php echo $gallery->album->getKeywords($index) ?></textarea></td>
</tr>

<?php

$translateableFields = translateableFields();

$extra_field_List = $gallery->album->getExtraFields();
if(!empty($extra_field_List)) {
	foreach ($extra_field_List as $field) {
?>

	<!-- Custom Fields -->
<tr>
	<td colspan="2" class="g-double-bottom-border-spacer">&nbsp;<?php echo gTranslate('core', "Custom fields"); ?></td>
</tr>
<?php
		if (in_array($field, array_keys(automaticFieldsList()))) {
			continue;
		}

		$value = $gallery->album->getExtraField($index, $field);

		if (in_array($field, array_keys($translateableFields))) {
			$fieldLabel = $translateableFields[$field];
			$rows = 1;
		}
		else {
			$fieldLabel = $field;
			$rows = 3;
		}

		echo "\n<tr>";
		echo "\n\t". '<td style="vertical-align: top;" class="g-emphasis">'. $fieldLabel .':</td>';
		echo "\n\t". '<td><textarea name="extra_fields['. $field .']" rows="'. $rows .'" cols="38">'. $value .'</textarea></td>';
		echo "\n</tr>";
	}
}
?>

</table>

<?php
// get the itemCaptureDate
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
<table align="center">
  <caption><?php echo gTranslate('core', "Photo Capture Date") ?></caption>
  <tr>
	<td><?php echo gTranslate('core', "Month") ?></td>
	<td><?php echo gTranslate('core', "Day") ?></td>
	<td><?php echo gTranslate('core', "Year") ?></td>
	<td><?php echo gTranslate('core', "Hours") ?></td>
	<td><?php echo gTranslate('core', "Minutes") ?></td>
	<td><?php echo gTranslate('core', "Seconds") ?></td>
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

<br>
  <?php echo gSubmit('save', gTranslate('core', "_Save")); ?>
  <?php echo gSubmit('saveclose', gTranslate('core', "Sav_e and Close")); ?>
  <?php echo gButton('cancel', gTranslate('core', "_Cancel"), 'parent.close()'); ?>
</form>

<script language="javascript1.2" type="text/JavaScript">
<!--
// position cursor in top form field
document.g1_form.caption.focus();
//-->
</script>
</div>

</body>
</html>
