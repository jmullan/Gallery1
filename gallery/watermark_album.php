<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2006 Bharat Mediratta
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
/**
 * This popup provides the possibility to put a watermark on every picture in an album.
 * Subalbums can be watermarked revcursively.
 *
 * @package Item
 */

/**
 *
 */
require_once(dirname(__FILE__) . '/init.php');

list($index, $save, $preview, $wmAlign, $wmName, $wmSelect) =
    getRequestVar(array('index', 'save', 'preview', 'wmAlign', 'wmName', 'wmSelect'));
list($wmAlignX, $wmAlignY, $recursive, $previewFull) =
    getRequestVar(array('wmAlignX', 'wmAlignY', 'recursive', 'previewFull'));

// Hack check
if (!$gallery->user->canChangeTextOfAlbum($gallery->album)) {
    echo _("You are not allowed to perform this action!");
    exit;
}

if (empty($index)) {
    $index = '';
}
$highlightIndex = $gallery->album->getHighlight();
$err = '';

if (isset($save) || isset($preview)) {
    if (isset($wmAlign) && ($wmAlign > 0) && ($wmAlign < 12)) {
        if (isset($wmName) && !empty($wmName)) {
            if (isset($save)) {
                printPopupStart(gTranslate('core', "Watermarking album..."));
		
		echo gTranslate('core', "(this may take a while)");
                my_flush();
                set_time_limit($gallery->app->timeLimit);
                $gallery->album->watermarkAlbum(
                    $wmName,
                    "",
                    $wmAlign,
                    $wmAlignX,
                    $wmAlignY,
                    $recursive,
                    $wmSelect
                );
                $gallery->album->save();
		echo '<br><br>';
    		echo gButton('close', gTranslate('core', "_Close"), 'parent.close()');
?>
</div>
</body>
</html>
<?php
            } else {
                // create a preview of the highlight image
                $gallery->album->watermarkPhoto(
                    $highlightIndex,
                    $wmName,
                    "",
                    $wmAlign,
                    isset($wmAlignX) ? $wmAlignX : 0,
                    isset($wmAlignY) ? $wmAlignY : 0,
                    1, // set as preview
                    isset($previewFull) ? $previewFull : 0);
            }
        } else {
            $err = _("Please select a watermark.");
        }
    } else {
        $err = _("Please select an alignment.");
    }
} else {
    if (!isset($recursive)) {
        $recursive = 1;
    }
}

printPopupStart(gTranslate('core', "Watermark Album"));

if (!$gallery->album->numPhotos(1)) {
    echo "\n<p>". gallery_error(_("No items to watermark.")) . "</p>";
} else {
    $highlightIndex = $gallery->album->getHighlight();
    if (isset($highlightIndex)) {
        if (isset($preview)) {
            echo $gallery->album->getPreviewTag($highlightIndex);
        } else {
            echo $gallery->album->getThumbnailTag($highlightIndex);
        }
    }

    if (!empty($err)) {
        echo "\n<p>". gallery_error($err) . "</p>";
    }

    echo infoBox(array(array(
	'type' => 'information',
	'text' => gTranslate('core', "Keep in mind that watermarking on animated gifs is currently not supported and will 'deface & unanimate' your pictures.")
    )));
    echo makeFormIntro('watermark_album.php',  array('name' => 'theform'));
    global $watermarkForm;
    $watermarkForm["askRecursive"] = 1;
    $watermarkForm["askPreview"] = 1;
    $watermarkForm["allowNone"] = 0;
    includeLayout ('watermarkform.inc');
?>

<p>
	<input type="hidden" name="index" value="<?php echo $index ?>">
<?php

echo gSubmit('save', gTranslate('core', "_Save"));
// only allow preview if there is a highlight
if (isset($highlightIndex)) {
    echo gSubmit('preview', gTranslate('core', "_Preview"));
}
    echo gButton('cancel', gTranslate('core', "_Cancel"), 'parent.close()');
?>
</p>
</form>

<script language="javascript1.2" type="text/JavaScript">
<!--
// position cursor in top form field
document.theform.data.focus();
//-->
</script>
<?php 
} // end if numPhotos()
?>
</div>
<?php
print gallery_validation_link("watermark_album.php", false, array('set_albumName' => $gallery->album->fields["name"]));
?>
</body>
</html>
