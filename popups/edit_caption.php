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
	if (isValidTimestamp(strtotime($captureDate))) {
		$gallery->album->setItemCaptureDate($index, strtotime($captureDate));
	}
	else {
		$infoMessages[] = array(
			'type' => 'error',
			'text' => gTranslate('core', "Given capture date is not valid.")
		);
	}

	if (isValidText($caption)) {
		$gallery->album->setCaption($index, $caption);
	}
	else {
		$infoMessages[] = array(
			'type' => 'error',
			'text' => gTranslate('core', "Given caption not valid.")
		);
	}

	if (isValidText($description)) {
		$gallery->album->setDescription($index, $description);
	}
	else {
		$infoMessages[] = array(
			'type' => 'error',
			'text' => gTranslate('core', "Given description is not valid.")
		);
	}

	if (isValidText($keywords)) {
		$gallery->album->setKeywords($index, $keywords);
	}
	else {
		$infoMessages[] = array(
			'type' => 'error',
			'text' => gTranslate('core', "Given keyword(s) is/are not valid.")
		);
	}

	if (isset($extra_fields)) {
		foreach ($extra_fields as $field => $value){
			if (isValidText($value)) {
				$gallery->album->setExtraField($index, $field, trim($value));
			}
		}
	}

	if(empty($infoMessages)) {
		$status = $gallery->album->save(array(
			i18n("Captions and/or custom fields modified for %s"),
			makeAlbumURL($gallery->album->fields["name"], $gallery->album->getPhotoId($index))
		));


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
}

$caption = $gallery->album->getCaption($index);
$itemCaptureDate = $gallery->album->getItemCaptureDate($index);

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

<br>
<?php

echo _getStyleSheetLink('jscalendar/aqua/theme');
echo jsHTML('jscalendar/calendar.js');
echo jsHTML('jscalendar/calendar-translation.js.php');
echo jsHTML('jscalendar/calendar-setup.js');

echo "\n<br>";
echo gDate('captureDate', '<span class="g-emphasis">'. gTranslate('core', "Capture date:") .'</span>', $itemCaptureDate);

echo "\n<p class=\"center\">";
echo gSubmit('save', gTranslate('core', "_Save"));
echo gSubmit('saveclose', gTranslate('core', "Sav_e and Close"));
echo gButton('close', gTranslate('core', "_Close"), 'parent.close()');
echo gReset('reset', gTranslate('core', "_Reset"));
echo "\n</p>";

echo "\n</form>";
includeTemplate('overall.footer');

?>
</body>
</html>
