<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2004 Bharat Mediratta
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
	print _("Security violation") ."\n";
	exit;
}

if (!isset($GALLERY_BASEDIR)) {
    $GALLERY_BASEDIR = './';
}

require(dirname(__FILE__) . '/init.php');

// Hack check
if (!$gallery->user->canWriteToAlbum($gallery->album)) {
	exit;
}
	
if (isset($save)) {
/*
** The Radiobutton for Shutterflys Donation is set always.
** So its saved always as Service.
** This is a workaround. But if we have a similar Service in Future (another Radiobutton)
** we should change it.
**
*/
	if (! isset($print_photos['shutterfly']['checked'])) {
        	unset($print_photos['shutterfly']);
	}

	if (get_magic_quotes_gpc()) {
		$gallery->album->fields["summary"] = stripslashes($summary);
		$gallery->album->fields["title"] = stripslashes($title);
	} else {
		$gallery->album->fields["summary"] = $summary;
		$gallery->album->fields["title"] = $title;
	}
	$gallery->album->fields["bgcolor"] = $bgcolor;
	$gallery->album->fields["textcolor"] = $textcolor;
	$gallery->album->fields["linkcolor"] = $linkcolor;
	$gallery->album->fields["font"] = $font;
	$gallery->album->fields["bordercolor"] = $bordercolor;
	$gallery->album->fields["border"] = $border;
	$gallery->album->fields["background"] = $background;
	$gallery->album->fields["thumb_size"] = $thumb_size;
	$gallery->album->fields["resize_size"] = $resize_size;
	$gallery->album->fields["resize_file_size"] = $resize_file_size;
	$gallery->album->fields['max_size'] = $max_size;
	$gallery->album->fields['max_file_size'] = $max_file_size;
	$gallery->album->fields["returnto"] = $returnto;
	$gallery->album->fields["rows"] = $rows;
	$gallery->album->fields["cols"] = $cols;
	$gallery->album->fields["fit_to_window"] = $fit_to_window;
	$gallery->album->fields["use_fullOnly"] = $use_fullOnly;
	$gallery->album->fields["print_photos"] = $print_photos;
	$gallery->album->fields["use_exif"] = $use_exif;
	$gallery->album->fields["display_clicks"] = $display_clicks;
	$gallery->album->fields["item_owner_modify"] = $item_owner_modify;
	$gallery->album->fields["item_owner_delete"] = $item_owner_delete;
	$gallery->album->fields["item_owner_display"] = $item_owner_display;
	$gallery->album->fields["add_to_beginning"] = $add_to_beginning;
	$gallery->album->fields["slideshow_type"] = $slideshow_type;
	$gallery->album->fields["slideshow_recursive"] = $slideshow_recursive;
	$gallery->album->fields["slideshow_loop"] = $slideshow_loop;
	$gallery->album->fields["slideshow_length"] = $slideshow_length;
	$gallery->album->fields["album_frame"] = $album_frame;
	$gallery->album->fields["thumb_frame"] = $thumb_frame;
	$gallery->album->fields["image_frame"] = $image_frame;
	$gallery->album->fields["showDimensions"] = $showDimensions;
	$gallery->album->save(array(i18n("Properties changed")));

	if (isset($setNested)) {
	
		$gallery->album->setNestedProperties();

	}

	reload();
}

?>
<html>
<head>
  <title><?php echo _("Album Properties") ?></title>
  <?php echo getStyleSheetLink() ?>
</head>
<body dir="<?php echo $gallery->direction ?>">

<center>
<span class="popuphead">
<?php echo _("Album Properties") ?>
</span>
<span class="popup">
<?php echo makeFormIntro("edit_appearance.php", 
			array("name" => "theform", 
				"method" => "POST")); ?>
<input type="hidden" name="save" value="1">
<table>
<tr>
<td colspan="2"><?php echo _("Album Summary") ?></td>
</tr>
<tr>
<td colspan="2" align="left">
<textarea cols="60" rows="8" name="summary"><?php echo $gallery->album->fields["summary"] ?></textarea>
</td>
</tr>
<tr>
<td class="popup"><?php echo _("Album Title") ?></td>
<?php 
	/* 
	** Check if we are using PHP >= 4.1.0
	** If yes, we can use 3rd Parameter so e.g. titles in chinese BIG5 or UTF8 are displayed correct.
	** Otherwise they are messed.
	** Not all Gallery Charsets are supported by PHP, so only those listed are recognized.
	*/

	$supportedCharsets=array(
			'UTF-8',
			'ISO-8859-1',
			'ISO-8859-15',
			'cp1252',
			'BIG5',
			'GB2312',
			'BIG5-HKSCS',
			'Shift_JIS',
			'EUC-JP'
	);

	$supportedCharsetsNewerPHP=array(
			'cp866',
			'cp1251',
			'KOI8-R'
	);

	if (function_exists('version_compare')) {
		if ( in_array($gallery->charset, $supportedCharsets) || // PHP >=4.1.0, but < 4.3.2
		   ( version_compare(phpversion(), "4.3.2") >=0 && in_array($gallery->charset, $supportedCharsetsNewerPHP) ) ) {
			$album_title=htmlentities($gallery->album->fields["title"],ENT_COMPAT, $gallery->charset);
		} else {
			$album_title=htmlentities($gallery->album->fields["title"]);
		}
	}
	else {
		$album_title=htmlentities($gallery->album->fields["title"]);
	}
?>
<td><input type="text" name="title" value="<?php echo $album_title; ?>"></td>
</tr>
<tr>
<td class="popup"><?php echo _("Background Color") ?></td>
<td><input type="text" name="bgcolor" value="<?php echo $gallery->album->fields["bgcolor"] ?>"></td>
</tr>
<tr>
<td class="popup"><?php echo _("Text Color") ?></td>
<td><input type="text" name="textcolor" value="<?php echo $gallery->album->fields["textcolor"] ?>"></td>
</tr>
<tr>
<td class="popup"><?php echo _("Link Color") ?></td>
<td><input type="text" name="linkcolor" value="<?php echo $gallery->album->fields["linkcolor"] ?>"></td>
</tr>
<tr>
<td class="popup"><?php echo _("Background Image") ?> (URL)</td>
<td><input type="text" name="background" value="<?php echo $gallery->album->fields["background"] ?>"></td>
</tr>
<tr>
<td class="popup"><?php echo _("Font") ?></td>
<td><input type="text" name="font" value="<?php echo $gallery->album->fields["font"] ?>"></td>
</tr>
<tr>
<td class="popup"><?php echo _("Borders") ?></td>
<?php _("off") ?>
<td><select name="border"><?php echo selectOptions($gallery->album, "border", array("off" => _("off"), 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 10 => 10, 15 => 15, 20 => 20)) ?></select></td>
</tr>
<tr>
<td class="popup"><?php echo _("Border color") ?></td>
<td><input type="text" name="bordercolor" value="<?php echo $gallery->album->fields["bordercolor"] ?>"></td>
</tr>
<tr>
<td class="popup"><?php echo _("Thumbnail size") ?></td>
<td><input type="text" name="thumb_size" value="<?php echo $gallery->album->fields["thumb_size"] ?>"></td>
</tr>
<tr>
<td class="popup"><?php echo _("Maximum dimensions of intermediate sized images") ?></td>
<td><select name="resize_size"><?php echo selectOptions($gallery->album, "resize_size", array("off" => _("off"), 400 => 400, 500 => 500, 600 => 600, 640 => 640, 700 => 700, 800 => 800, 1024 => 1024, 1280 => 1280)) ?></select></td>
</tr>
<tr>
<td class="popup"><?php echo _("Maximum file size of intermediate sized JPEG/PNG images in kilobytes (0 or blank for no size restriction)") ?></td>
<td><input type="text" name="resize_file_size" value="<?php echo $gallery->album->fields["resize_file_size"] ?>"></td>
</tr>
<tr>
<td class="popup"><?php echo _("Maximum dimensions of full sized images") ?></td>
<td><select name="max_size"><?php echo selectOptions($gallery->album, 'max_size', array('off' => _('off'), 400 => 400, 500 => 500, 600 => 600, 640 => 640, 700 => 700, 800 => 800, 1024 => 1024, 1280 => sprintf(_('%d (%d MPix)'), 1280, 1), 1600 => sprintf(_('%d (%d MPix)'), 1600, 2), 2048 => sprintf(_('%d (%d MPix)'), 2048, 3))) ?></select></td>
</tr>
<tr>
<td class="popup"><?php echo _("Maximum file size of full sized JPEG/PNG images in kilobytes (0 or blank for no size restriction)") ?></td>
<td><input type="text" name="max_file_size" value="<?php echo $gallery->album->fields['max_file_size'] ?>"></td>
</tr>
<tr>
<td class="popup"><?php echo _("Show <i>Return to</i> link") ?></td>
<td><select name="returnto"><?php echo selectOptions($gallery->album, "returnto", array("yes" => _("yes"), "no" => _("no"))) ?></select></td>
</tr>
<tr>
<td class="popup"><?php echo _("Rows") ?></td>
<td>
 <select name="rows">
  <?php echo selectOptions($gallery->album, "rows", array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10)) ?>
 </select>
</td>
</tr>
<tr>
<td class="popup"><?php echo _("Columns") ?></td>
<td>
 <select name="cols">
  <?php echo selectOptions($gallery->album, "cols", array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10)) ?>
 </select>
</td>
</tr>
<tr>
<td class="popup"><?php echo _("Auto fit-to-window for<br>images without a resized copy") ?></td>
<td><select name="fit_to_window"><?php echo selectOptions($gallery->album, "fit_to_window", array("yes" => _("yes"), "no" => _("no"))) ?></select></td>
</tr>
<tr>
<td class="popup"><?php echo _("Offer visitors ability to specify<br>preference for full-size or resized images") ?></td>
<td><select name="use_fullOnly"><?php echo selectOptions($gallery->album, "use_fullOnly", array("yes" => _("yes"), "no" => _("no"))) ?></select></td>
</tr>
<tr>
<td  class="popup" valign="top"><?php echo _("Which photo printing services<br>do you want to let visitors use?") ?></td>
<td valign="top">
<?php
$services = array(
	'photoaccess' => array(
		'name'    => 'PhotoAccess',
		'url'     => 'http://www.photoaccess.com/'),
	'shutterfly'  => array(
		'name'    => 'Shutterfly',
		'url'     => 'http://www.shutterfly.com/',
		'radio'   => array(
			'yes' => _('with donation'),
			'no'  => _('without donation')
		)
	),
	'fotokasten'  => array(
		'name'    => 'Fotokasten',
		'url'     => 'http://www.fotokasten.de/'),
	'ezprints'    => array(
		'name'    => 'EZ Prints',
		'url'     => 'http://www.ezprints.com/')                  
);
foreach ($services as $item => $data) {
	if (isset($gallery->album->fields['print_photos'][$item])) {
		$value = $gallery->album->fields['print_photos'][$item];
	} else {
		$value = array('checked' => false);
	}
	$checked = !empty($value['checked']) ? ' checked' : '';
	print "<input name=\"print_photos[$item][checked]\" value=\"checked\" type=\"checkbox\"$checked><a target=\"_blank\" href=\"${data['url']}\">${data['name']}</a><br />\n";
	if (isset($data['radio'])) {
		if (!isset($value['donation'])) {
			$value['donation'] = 'yes';
		}
		foreach ($data['radio'] as $radio => $values) {
			$checked = $value['donation'] === $radio
				? ' checked' : '';
			print "&nbsp;&nbsp;&nbsp;<input name=\"print_photos[$item][donation]\" value=\"$radio\" type=\"radio\"$checked>" . $values . "<br />\n";
		}
	}
}
?>
</td>
</tr>
<tr>
<td class="popup"><?php echo _("Slideshow Type") ?></td>
<td><select name="slideshow_type"><?php echo selectOptions($gallery->album, "slideshow_type", array( "off" => _("Off"), "ordered" => _("Ordered"), "random" => _("Random"))) ?></select></td>
</tr>
<tr>
<td class="popup"><?php echo _("Include sub-albums in slideshow") ?></td>
<td><select name="slideshow_recursive"><?php echo selectOptions($gallery->album, "slideshow_recursive", array("yes" => _("yes"), "no" => _("no"))) ?></select></td>
</tr>
<tr>
<td class="popup"><?php echo _("Allow slideshow to loop") ?></td>
<td><select name="slideshow_loop"><?php echo selectOptions($gallery->album, "slideshow_loop", array("yes" => _("yes"), "no" => _("no"))) ?></select></td>
</tr>
<tr>
<tr>
<td class="popup"><?php echo _("Slideshow Length") ?></td>
<td><input type="text" name="slideshow_length" value="<?php echo $gallery->album->fields["slideshow_length"] ?>"></td>
</tr>
<tr>
<td class="popup"><?php echo _("Album Frame") ?></td>
<td><select name="album_frame"><?php echo selectOptions($gallery->album, "album_frame", available_frames()) ?></select></td>
</tr>
<tr>
<td class="popup"><?php echo _("Thumb Frame") ?></td>
<td><select name="thumb_frame"><?php echo selectOptions($gallery->album, "thumb_frame", available_frames()) ?></select></td>
</tr>
<tr>
<td class="popup"><?php echo _("Image Frame") ?></td>
<td><select name="image_frame"><?php echo selectOptions($gallery->album, "image_frame", available_frames()) ?></select></td>
</tr>
<?php
if ($gallery->app->use_exif) {
?>
<tr>
<td class="popup"><?php echo _("Display EXIF data?") ?></td>
<td><select name="use_exif"><?php echo selectOptions($gallery->album, "use_exif", array("no" => _("no"), "yes" => _("yes"))) ?></select></td>
</tr>
<?php
} // end if
?>
<tr>
<td class="popup"><?php echo _("Display click counter for this album?") ?></td>
<td><select name="display_clicks"><?php echo selectOptions($gallery->album, "display_clicks", array("yes" => _("yes"), "no" => _("no"))) ?></select></td>
</tr>
<tr>
<td class="popup"><?php echo _("Display owners name with caption") ?></td>
<td><select name="item_owner_display"><?php echo selectOptions($gallery->album, "item_owner_display", array("yes" => _("yes"), "no" => _("no"))) ?></select></td>
</tr>
<tr>
<td class="popup"><?php echo _("Allow item owners to modify their images") ?></td>
<td><select name="item_owner_modify"><?php echo selectOptions($gallery->album, "item_owner_modify", array("yes" => _("yes"), "no" => _("no"))) ?></select></td>
</tr>
<tr>
<td class="popup"><?php echo _("Allow item owners to delete their images") ?></td>
<td><select name="item_owner_delete"><?php echo selectOptions($gallery->album, "item_owner_delete", array("yes" => _("yes"), "no" => _("no"))) ?></select></td>
</tr>
<tr>
<td class="popup"><?php echo _("Add new items at beginning of album") ?></td>
<td><select name="add_to_beginning"><?php echo selectOptions($gallery->album, "add_to_beginning", array("yes" => _("yes"), "no" => _("no"))) ?></select></td>
</tr>
<tr>
<td class="popup"><?php echo _("Display clickable image dimensions") ?></td>
<td><select name="showDimensions"><?php echo selectOptions($gallery->album, "showDimensions", array("yes" => _("yes"), "no" => _("no"))) ?></select></td>
</tr>
</table>

<br>
<input type="checkbox" name="setNested" value="1"><span class="popup"><?php echo _("Apply values to nested Albums (except Album Title and Summary).") ?></span>
<br>
<br>
<input type="submit" name="apply" value="<?php echo _("Apply") ?>">
<input type="reset" value="<?php echo _("Undo") ?>">
<input type="button" name="close" value="<?php echo _("Close") ?>" onclick='parent.close()'>

</form>

<script language="javascript1.2" type="text/JavaScript">
<!--   
// position cursor in top form field
document.theform.title.focus();
//-->
</script>

</span>
</body>
</html>