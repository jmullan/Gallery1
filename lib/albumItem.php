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

/**
 * @package	Item
 * @author	Jens Tkotz
 */

/**
 * This function shows all possible actions for an album item.
 *
 * @param	integer   $i           index number of the item
 * @param   boolean   $withIcons   Wether icons should be used, or not.
 * @return  array                  Array of all possible album item for the current user.
 * @author  Jens Tkotz <jens@peino.de>
 */
function getItemActions($i, $withIcons = false, $popupsOnly = false) {
	global $gallery;
	global $nextId;

	static $javascriptSet;

	$id = $gallery->album->getPhotoId($i);
	$override = ($withIcons) ? '' : 'no';
	$options = array();
	$javascript = '';

    if (!$gallery->session->offline && empty($javascriptSet)) {
        $javascript ="
  <script language=\"javascript1.2\" type=\"text/JavaScript\">
      function imageEditChoice(selected_select) {
      	var sel_index = selected_select.selectedIndex;
      	var sel_value = selected_select.options[sel_index].value;
      	var sel_class = selected_select.options[sel_index].className;
      	selected_select.options[0].selected = true;
      	selected_select.blur();
      	if (sel_class == 'url') {
      		document.location = sel_value;
      	} else {
      		// the only other option should be popup
    		". popup('sel_value', 1) ."
      	}
      }
  </script>";

        $javascriptSet = true;
    }

    $isAlbum = false;
    $isMovie = false;
    $isPhoto = false;

    if ($gallery->album->isAlbum($i)) {
    	$label = gTranslate('core', "Album");
    	if(!isset($myAlbum)) {
    		$myAlbum = $gallery->album->getNestedAlbum($i, true);
    	}
    	$isAlbum = true;
    }
    elseif ($gallery->album->isMovieByIndex($i)) {
    	$label = gTranslate('core', "Movie");
    	$isMovie = true;
    }
    else {
    	$label = gTranslate('core', "Photo");
		$isPhoto = true;
    }

    if ($gallery->user->isAdmin()) {
    	$isAdmin = true;
    }

    if (isset($isAdmin) ||
      (isset($myAlbum) && $gallery->user->isOwnerOfAlbum($myAlbum)) ||
      $gallery->album->isItemOwner($gallery->user->getUid(), $i)) {
      	$isOwner = true;
	}

    if ($gallery->user->canWriteToAlbum($gallery->album) ||
       ($gallery->album->getItemOwnerModify() && isset($isOwner))) {
		$canModify = true;
	}

    /* ----- User can write to album, or is owner of the item and item-owner can modify items ----- */
    if (isset($canModify)) {
    	if ($isPhoto) {
    		$options[] = array(
	    		'text' => gTranslate('core', "Edit _Text"),
	    		'value' => showChoice2("edit_caption.php", array("index" => $i)),
	    		'icon' => ($withIcons) ? 'kcmfontinst.gif' : ''
    		);
    		$options[] = array(
        		'text' => gTranslate('core', "Edit Th_umbnail"),
        		'value' => showChoice2('edit_thumb.php', array('index' => $i)),
        		'icon' => ($withIcons) ? 'thumbnail.gif' : ''
    		);
    		$options[] = array(
        		'text' => sprintf(gTranslate('core', "Rotate/_Flip"), $label),
        		'value' => showChoice2('rotate_photo.php', array('index' => $i)),
        		'icon' => ($withIcons) ? 'reload.gif' : ''
        		);
    		$options[] = array(
	    		'text' => gTranslate('core', "Re_size"),
	    		'value' => showChoice2('resize_photo.php', array('index' => $i)),
	    		'icon' => ($withIcons) ? 'window_fullscreen.gif' : ''
    		);
    		if (!empty($gallery->app->watermarkDir)) {
    			$options[] = array(
	    			'text' => gTranslate('core', "_Watermark"),
	    			'value' =>  showChoice2('edit_watermark.php', array('index' => $i)),
	    			'icon' => ($withIcons) ? 'camera.gif' : ''
    			);
    		}
    		if(!$popupsOnly) {
    			$options[] = array(
	    			'text' => gTranslate('core', "Image_Map"),
	    			'value' => showChoice2('imagemap.php', array('index' => $i), false),
	    			'icon' => ($withIcons) ? 'behavior-capplet.gif' : ''
    			);
    		}
    	}

    	$options[] = array(
	    	'text' => gTranslate('core', "Mo_ve"),
	    	'value' => showChoice2("move_photo.php", array("index" => $i, 'reorder' => 0)),
	    	'icon' => ($withIcons) ? 'tab_duplicate.gif' : ''
    	);

    	/* ----- Item is subalbum ----- */
    	if ($isAlbum) {
    		$options[] = array(
	    		'text' => gTranslate('core', 'Edit Title'),
	    		'value' =>  showChoice2("edit_field.php", array("set_albumName" => $myAlbum->fields["name"], "field" => "title")),
    		);
    		$options[] = array(
	    		'text' => gTranslate('core', 'Edit Description'),
	    		'text' => getIconText('', gTranslate('core', 'Edit Description'), $override, $withIcons),
	    		'value' => showChoice2("edit_field.php", array("set_albumName" => $myAlbum->fields["name"], "field" => "description")),
	    		'icon' => ''
    		);

    		$options[] = array(
	    		'text' => gTranslate('core', "Rename Album"),
	    		'value' => showChoice2("rename_album.php", array("set_albumName" => $myAlbum->fields["name"], "index" => $i)),
    		);

    		$options[] = array(
	    		'text' => gTranslate('core', "Reset Counter"),
	    		'value' => showChoice2("do_command.php",
		    		array(
			    		'cmd' => 'reset-album-clicks',
			    		'set_albumName' => $gallery->album->getAlbumName($i),
			    		'return' => urlencode(makeGalleryUrl("view_album.php"))
		    		)
	    		),
    		);

    		$options[] = array(
	    		'text' => gTranslate('core', "Permissions"),
	    		'value' => showChoice2("album_permissions.php", array("set_albumName" => $myAlbum->fields["name"])),
    		);

    		// Watermarking support is enabled and user is allowed to watermark images/albums /
    		if (!empty($gallery->app->watermarkDir) && $myAlbum->numPhotos(1)) {
    			$options[] = array(
	    			'text' => gTranslate('core', "Watermark Album"),
	    			'value' => showChoice2("watermark_album.php", array("set_albumName" => $myAlbum->fields["name"])),
    			);
    		}
    		if ($gallery->user->canViewComments($myAlbum) && ($myAlbum->lastCommentDate("no") != -1)) {
    			$options[] = array(
	    			'text' => gTranslate('core', "View Comments"),
	    			'value' => showChoice2("view_comments.php", array("set_albumName" => $myAlbum->fields["name"]),"url"),
    			);
    		}
    	}
    	if (! $isAlbum) {
    	    $options[] = array(
        	    'text' => gTranslate('core', "Cop_y"),
        	    'value' => showChoice2("copy_photo.php", array("index" => $i)),
        	    'icon' => ($withIcons) ? 'editcopy.gif' : ''
    	    );
    	}
    }

    if ($gallery->user->canWriteToAlbum($gallery->album)) {
    	$options[] = array(
	    	'text' => gTranslate('core', "_Reorder"),
	    	'value' => showChoice2("move_photo.php", array("index" => $i, 'reorder' => 1)),
	    	'icon' => ($withIcons) ? 'tab_duplicate.gif' : ''
    	);

    	/* ----- Item is photo, or subalbum with highlight ----- */
    	if ($isPhoto || (isset($myAlbum) && $myAlbum->hasHighlight())) {
    		$options[] = array(
	    		'text' => gTranslate('core', "Set as high_light"),
	    		'value' => showChoice2('do_command.php', array('cmd' => 'highlight', 'index' => $i)),
	    		'icon' => ($withIcons) ? 'viewmag1.gif' : ''
    		);
    	}
    }

    if (isset($isAdmin)) {
        $options[] = array(
            'text' => gTranslate('core', "Change Ow_ner"),
            'value' => showChoice2("photo_owner.php", array("id" => $id)),
            'icon' => ($withIcons) ? 'yast_kuser.gif' : ''
        );
        $options[] = array(
            'text' => sprintf(gTranslate('core', "Feature %s"), $label),
            'value' => showChoice2('featured-photo.php',
                            array(
                                'set' => 1,
                                'set_albumName' => $gallery->album->fields['name'],
                                'index' => $i)),
            'icon' => ($withIcons) ? 'signal-1.gif' : ''
        );
    }

    if (isset($isOwner)) {
    	if ($gallery->album->isHidden($i)) {
    		$options[] = array(
	    		'text' => gTranslate('core', "_Show"),
	    		'value' => showChoice2("do_command.php", array("cmd" => "show", "index" => $i)),
	    		'icon' => ($withIcons) ? 'idea.gif' : ''
    		);
    	}
    	else {
    		$options[] = array(
	    		'text' => gTranslate('core', "_Hide"),
	    		'value' => showChoice2("do_command.php", array("cmd" => "hide", "index" => $i)),
	    		'icon' => ($withIcons) ? 'no_idea.gif' : ''
    		);
    	}
    }

    if ($gallery->user->canDeleteFromAlbum($gallery->album) ||
      ($gallery->album->getItemOwnerDelete() && isset($isOwner))) {
    	if($isAlbum) {
    		if($gallery->user->canDeleteAlbum($myAlbum)) {
    			$options[] = array(
	    			'text' => gTranslate('core', "_Delete"),
	    			'value' => showChoice2("delete_photo.php",
	    			                       array(
	    			                        "id" => $myAlbum->fields["name"],
	    			                        "albumDelete" => 1)),
    			);
    		}
    	} else {
    		$options[] = array(
	    		'text' => gTranslate('core', "_Delete"),
	    		'value' => showChoice2('delete_photo.php', array('id' => $id, 'nextId' => $nextId)),
	    		'icon' => ($withIcons) ? 'delete.gif' : ''
    		);
    	}
    }

    if($isPhoto) {
        $photo = $gallery->album->getPhoto($i);
        if ($gallery->album->fields["use_exif"] == 'yes' &&
        (eregi("jpe?g\$", $photo->image->type)) &&
        (isset($gallery->app->use_exif) || isset($gallery->app->exiftags))) {
            $options['showExif'] = array(
                'text' => gTranslate('core', "Photo _properties"),
                'value' => showChoice2("view_photo_properties.php", array("index" => $i)),
                'icon' => ($withIcons) ? 'frame_query.gif' : '',
                'separate' => true
            );
        }


        if(isset($gallery->album->fields["ecards"]) && $gallery->album->fields["ecards"] == 'yes' &&
        $gallery->app->emailOn == 'yes') {
            $options['eCard'] = array(
                'text' => gTranslate('core', "Send photo as e_Card"),
                'value' => showChoice2('ecard_form.php', array('photoIndex' => $i)),
                'icon' => ($withIcons) ? 'ecard.gif' : '',
                'separate' => true
            );
        }

	}

    if(!empty($options)) {
    	if(sizeof($options) > 1) {
    		array_sort_by_fields($options, 'text', 'asc', false, true);
    	}
    	$options = array_merge(array(
    	  array(
	    	'text' => '&laquo; '. sprintf(gTranslate('core', "%s actions"), $label) . ' &raquo;',
	    	'value' => '',
	    	'selected' => true)
	    	), $options
    	);
    }
    return array($options, $javascript);
}

/**
 * Returns a HTML with all comments of an album item.
 *
 * @param integer   $index      itemindex
 * @param string    $albumName  Name of the album containing the item
 * @param boolean   $reverse    Wether to show in reverse order or not
 * @return string               A rendered HTML Table that contains the comments.
 * @author Jens Tkotz <jens@peino.de>
 */
function showComments ($index, $albumName, $reverse = false) {
    global $gallery;

    $numComments = $gallery->album->numComments($index);
    $delCommentText = getIconText('delete.gif', gTranslate('core', "delete comment"), 'yes');

    $commentdraw["index"] = $index;

    $commentTable = new galleryTable();
    $commentTable->setAttrs(array(
        'cellspacing' => 0,
        'cellpadding' => 0,
        'class' => 'g-comment-box')
    );

    $columns = ($gallery->user->canWriteToAlbum($gallery->album)) ? 4 : 3;
    $commentTable->setColumnCount($columns);

    for ($nr =1; $nr <= $numComments; $nr++) {
        $comment = $gallery->album->getComment($index, $nr);

        $commenterName = '<b>'. wordwrap($comment->getName(), 50, " ", 1) .'</b>';
        if ($gallery->user->isAdmin()) {
            $commenterName .= '@ &nbsp;'. $comment->getIPNumber();
        }

        $commentTable->addElement(array(
            'content' => gTranslate('core', "From:"),
            'cellArgs' => array('class' => 'left', 'width' => 50, 'height' => '25')));

        $commentTable->addElement(array(
            'content' => $commenterName,
            'cellArgs' => array('class' => 'left', 'width' => '55%')));

        $commentTable->addElement(array(
            'content' => '('. $comment->getDatePosted() .')',
            'cellArgs' => array('class' => 'left')));

        if ($gallery->user->canWriteToAlbum($gallery->album)) {
            $url = doCommand('delete-comment',
            array('index'=> $index,
                'comment_index' => $nr,
                'albumName' => $albumName)
            );

            $commentTable->addElement(array(
                'content' => '<a href="#" onclick="javascript:' . popup($url,1) . '">'. $delCommentText .' </a>')
            );
        }

        $commentTable->addElement(array(
            'content' => wordwrap($comment->getCommentText(), 100, " ", 1),
            'cellArgs' => array('colspan' => $columns, 'class' => 'left g-desc-cell g-comment-text-cell'))
        );
    }
    if ($reverse) {
        $commentTable['elements'] = array_reverse($commentTable['elements']);
    }

    return $commentTable->render();
}

/**
 * Determine id of next photo or movie.
 * Ater deletion we move to previous image if we're at the end.
 * and move forward if we're not.
 *
 * @param integer $currentId
 * @return integer              NextId ;)
 */
function getNextId($currentId) {
    global $gallery;

    $allIds = $gallery->album->getIds($gallery->user->canWriteToAlbum($gallery->album));
    $current = array_search($currentId, $allIds);

    if ($current < sizeof($allIds)-1) {
        $nextId = $allIds[$current+1];
    }
    elseif ($current > 0) {
        $nextId = $allIds[$current-1];
    }
    else {
        $nextId = $currentId;
    }

    return $nextId;
}

/**
 * What should the caption be, if no caption was given by user ?
 * See captionOptions.inc.php for options
 *
 * @param intger    $captionType
 * @param string    $originalFilename
 * @param string    $filename
 * @return string   $caption
 * @author Jens Tkotz <jens@peino.de>
 */
function generateCaption($captionType = 1, $originalFilename, $filename) {
    global $gallery;

    if (isset($gallery->app->dateTimeString)) {
        $dateTimeFormat = $gallery->app->dateTimeString;
    }
    else {
        $dateTimeFormat = "%D %T";
    }

    switch ($captionType) {
        case 0:
            $caption = '';
            $captionTypeString = 'no caption';
            break;

        case 1:
        default:
            /* Use filename */
            $caption = strtr($originalFilename, '_', ' ');
            $captionTypeString = 'filename';
            break;

        case 2:
            /* Use file creation date */
            $caption = strftime($dateTimeFormat, filectime($filename));
            $captionTypeString = 'file creation date';
            break;

        case 3:
            /* Use capture date */
            $caption = strftime($dateTimeFormat, getItemCaptureDate($filename));
            $captionTypeString = 'file capture date';
            break;
    }

    echo debugMessage(sprintf(gTranslate('core', "Generating caption. Type: %s"), $captionTypeString), __FILE__, __LINE__, 3);

    return $caption;
}

/**
 * Returns the label (photo, movie, or album) of an album item.
 *
 * @param integer   $index
 * @return string   $label
 * @author Jens Tkotz <jens@peino.de>
 */
function getLabelByIndex($index) {
    global $gallery;

    if ($gallery->album->isAlbum($index)) {
        $label = gTranslate('core', "Album");
    }
    elseif ($gallery->album->isMovieByIndex($index)) {
        $label = gTranslate('core', "Movie");
    }
    else {
        $label = gTranslate('core', "Photo");
    }

    return $label;
}
?>
