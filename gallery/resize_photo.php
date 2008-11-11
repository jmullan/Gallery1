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

require_once(dirname(__FILE__) . '/init.php');

list($index, $manual, $newsize, $resize_file_size, $remove_resized, $resize_recursive) =
	getRequestVar(array('index', 'manual', 'newsize', 'resize_file_size', 'remove_resized',
			    'resize_recursive'));

if (intval($index) > 0) {
	printPopupStart(gTranslate('core', "Resize Photo"), '', 'left');
}
else {
	printPopupStart(gTranslate('core', "Resize all Photos"), '', 'left');
}

// Hack checks
if (empty($gallery->album) || ! isValidGalleryInteger($index, true)) {
	showInvalidReqMesg();
	exit;
}

if (intval($index) > 0 &&
    (! $gallery->album->getPhoto($index) || !$gallery->album->isImageByIndex($index)))
{
	showInvalidReqMesg();
	exit;
}

if (! ($gallery->user->canWriteToAlbum($gallery->album) ||
	($gallery->album->getItemOwnerModify() &&
	 $gallery->album->isItemOwner($gallery->user->getUid(), $index))))
{
	showInvalidReqMesg(gTranslate('core', "You are not allowed to perform this action!"));
	exit;
}

$notice_messages = array();
$checklist = array(
	'manual'		=> 'int_ZeroEmpty',
	'newsize'		=> 'int_ZeroEmpty',
	'resize_file_size'	=> 'int_ZeroEmpty',
	'resize_recursive'	=> 'int_ZeroEmpty',
	'remove_resized'	=> 'text'
);

foreach($checklist as $fieldName => $type) {
	list($status, ${$fieldName}, $infoMessage) = sanityCheck(${$fieldName}, $type);

	if ($status > 0) {
		$notice_messages[] = array(
			'type' => 'error',
			'text' => sprintf(gTranslate('core', "Problem with input of field '%s'. %s"),
							  $fieldName, $infoMessage)
		);
		$error = true;
	}
}

if (isset($manual) && $manual > 0) {
	$newsize = $manual;
}

if (!empty($remove_resized)) {
	$newsize = 'orig';
}

if($gallery->album->getAllImageAreas($index)) {
	$notice_messages[] = array(
		'type' => 'warning',
		'text' => gTranslate('core', "This image has at least one imagearea. All imageareas will be deleted when you modify the picture.")
	);
}

if (!empty($newsize) && ! isset($error)) {
	if ($index == 0) {
		$gallery->album->resizeAllPhotos($newsize, $resize_file_size, $resize_recursive);
	}
	else {
		echo("<br> ". gTranslate('core', "Resizing 1 photo..."));
		my_flush();
		set_time_limit($gallery->app->timeLimit);

		$gallery->album->deleteAllImageAreas($index);
		$gallery->album->resizePhoto($index, $newsize, $resize_file_size);
	}

	$gallery->album->save(array(i18n("Images resized to %s pixels, %s kbytes. Imageareas deleted."),
				$newsize, $resize_file_size));

	dismissAndReload();
	exit;
}
else {
	printInfobox($notice_messages);
?>

<p><?php echo gTranslate('core', "This will resize your intermediate photos so that the longest side of the photo is equal to the target size below and the filesize will be close to the chosen size."); ?>
</p>

<?php
	if($index != 0) {
		echo "\n<p align=\"center\">";
		echo $gallery->album->getThumbnailTag($index);
		echo "\n</p>";
	}

	echo makeFormIntro('resize_photo.php',
		array('name' => 'resize_photo'),
		array('type' => 'popup')
	);
?>

<fieldset>
<legend><?php echo gTranslate('core', "Resizing"); ?></legend>
<table style="border: 1px solid; padding: 10px 20px; margin: 1px;">
<tr>
	<td><?php echo gTranslate('core', "Target filesize"); ?></td>
	<td><input type="text" size="4" name="resize_file_size" value="<?php print $gallery->album->fields["resize_file_size"] ?>" >  kbytes</td>
</tr>
<tr><td>&nbsp;</td></tr>
<tr>
	<td style="vertical-align: middle"><?php echo gTranslate('core', "Maximum side length in pixels") ?></td>
	<td>
	<table>
<?php
	$choices = array(1280, 1024, 700, 800, 640, 600, 500, 400);
	for ($i = 0; $i<count($choices); $i = $i+2) {
		echo "\n\t<tr>";
		echo "\n\t\t". '<td style="white-space:nowrap">' . gInput('radio', 'newsize', $choices[$i], false, $choices[$i], array('id' => "size_${choices[$i]}")) . '</td>';
		echo "\n\t\t". '<td style="white-space:nowrap">' . gInput('radio', 'newsize', $choices[$i+1], false, $choices[$i+1], array('id' => "size_${choices[$i+1]}")) . '</td>';
		echo "\n\t</tr>\n";
	}
?>
	<tr>
		<td colspan="2">
			<input id="none" type="radio" name="newsize" value="0">
			<input type="text" size="5" name="manual" onFocus="document.getElementById('none').checked=true;"><label for="none"> <?php echo gTranslate('core', "(manual value)"); ?></label>
		</td>
	</tr>
	</table>
	</td>
</tr>
</table>

<table style="margin-top: 2px; width: 100%">
<tr>
<?php
	if ($index === '0') {
		echo '<td>';
		echo gInput('checkbox', 'resize_recursive', gTranslate('core', "Apply to nested albums?"), false, 1);
		echo '</td>';
	}

	echo '<td align="right">';
	echo gSubmit('change_size', gTranslate('core', "Change Size"));
	echo '</td>';
?>
</tr>
</table>
</fieldset>

<br>
<input type="hidden" name="index" value="<?php echo $index ?>">
<fieldset>
<legend><?php echo gTranslate('core', "Removing"); ?></legend>
	<table style="width: 100%">
	<tr>
		<td><?php echo gTranslate('core', "Use only the original picture? Click button to remove all resized."); ?></td>
		<td align="right"><?php echo gSubmit('remove_resized', gTranslate('core', "Get rid of resized")); ?></td>
	</tr>
	</table>
</fieldset>
</form>

<?php
	echo "\n<p align=\"center\">";
	echo gButton('cancel', gTranslate('core', "Cancel"), 'parent.close()');
	echo "\n</p>";
}

?>
</div>
</body>
</html>
