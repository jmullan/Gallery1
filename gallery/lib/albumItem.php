<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2007 Bharat Mediratta
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
 * @param	integer $i		index number of the item
 * @param	boolean	$withIcons	Wether icons should be used, or not.
 * @return 	array			Array of all possible album item for the current user.
 */
function getItemActions($i, $withIcons = false) {
	global $gallery;
	global $nextId;

	static $javascriptSet;

	$id = $gallery->album->getPhotoId($i);
	$override = ($withIcons) ? '' : 'no';
	$options = array();

    if (!$gallery->session->offline && empty($javascriptSet) && !$withIcons) { ?>
  <script language="javascript1.2" type="text/JavaScript">
  <!-- //

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
  		<?php echo popup('sel_value', 1) ?>
  	}
  }
  //-->
  </script>
<?php
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
	    		'pure_text' => gTranslate('core', "Edit Text"),
	    		'text' => getIconText('kcmfontinst.gif', gTranslate('core', "Edit Text"), $override, $withIcons),
	    		'value' => showChoice2("edit_caption.php", array("index" => $i))
    		);
    		$options[] = array(
	    		'pure_text' => gTranslate('core', "Edit Thumbnail"),
	    		'text' => getIconText('thumbnail.gif', gTranslate('core', "Edit Thumbnail"), $override, $withIcons),
	    		'value' => showChoice2('edit_thumb.php', array('index' => $i))
    		);
    		$options[] = array(
	    		'pure_text' => sprintf(gTranslate('core', "Rotate/Flip"), $label),
	    		'text' => getIconText('reload.gif', sprintf(gTranslate('core', "Rotate/Flip"), $label), $override, $withIcons),
	    		'value' => showChoice2('rotate_photo.php', array('index' => $i))
    		);
    		$options[] = array(
	    		'pure_text' => gTranslate('core', "Resize"),
	    		'text' => getIconText('window_fullscreen.gif', gTranslate('core', "Resize"), $override, $withIcons),
	    		'value' => showChoice2('resize_photo.php', array('index' => $i))
    		);
    		if (!empty($gallery->app->watermarkDir)) {
    			$options[] = array(
	    			'pure_text' => gTranslate('core', "Watermark"),
	    			'text' => getIconText('camera.gif', gTranslate('core', "Watermark"), $override, $withIcons),
	    			'value' =>  showChoice2('edit_watermark.php', array('index' => $i))
    			);
    		}
    		$options[] = array(
	    			'pure_text' => gTranslate('core', "ImageMap"),
	    			'text' => getIconText('behavior-capplet.gif', gTranslate('core', "ImageMap"), $override, $withIcons),
	    			'value' => showChoice2('imagemap.php', array('index' => $i), false),
				'attrs' => array('class' => 'url')

    			);
    	}

    	$options[] = array(
		'pure_text' => gTranslate('core', "Move"),
		'text' => getIconText('tab_duplicate.gif', gTranslate('core', "Move"), $override, $withIcons),
		'value' => showChoice2("move_photo.php", array("index" => $i, 'reorder' => 0))
    	);

    	/* ----- Item is subalbum ----- */
    	if ($isAlbum) {
    		$options[] = array(
	    		'pure_text' => gTranslate('core', 'Edit Title'),
	    		'text' => getIconText('', gTranslate('core', 'Edit Title'), $override, $withIcons),
	    		'value' =>  showChoice2("edit_field.php", array("set_albumName" => $myAlbum->fields["name"], "field" => "title"))
    		);
    		$options[] = array(
	    		'pure_text' => gTranslate('core', 'Edit Description'),
	    		'text' => getIconText('', gTranslate('core', 'Edit Description'), $override, $withIcons),
	    		'value' =>  showChoice2("edit_field.php", array("set_albumName" => $myAlbum->fields["name"], "field" => "description"))
    		);

    		$options[] = array(
	    		'pure_text' => gTranslate('core', "Rename Album"),
	    		'text' => getIconText('', gTranslate('core', "Rename Album"), $override, $withIcons),
	    		'value' => showChoice2("rename_album.php", array("set_albumName" => $myAlbum->fields["name"], "index" => $i))
    		);

    		$options[] = array(
	    		'pure_text' => gTranslate('core', "Reset Counter"),
	    		'text' => getIconText('', gTranslate('core', "Reset Counter"), $override, $withIcons),
	    		'value' => showChoice2("do_command.php",
		    		array(
			    		'cmd' => 'reset-album-clicks',
			    		'set_albumName' => $gallery->album->getAlbumName($i),
			    		'return' => urlencode(makeGalleryUrl("view_album.php"))
		    		)
	    		)
    		);

    		$options[] = array(
	    		'pure_text' => gTranslate('core', "Permissions"),
	    		'text' => getIconText('decrypted.gif', gTranslate('core', "Permissions"), $override, $withIcons),
	    		'value' => showChoice2("album_permissions.php", array("set_albumName" => $myAlbum->fields["name"]))
    		);

    		// Watermarking support is enabled and user is allowed to watermark images/albums /
    		if (!empty($gallery->app->watermarkDir) && $myAlbum->numPhotos(1)) {
    			$options[] = array(
	    			'pure_text' => gTranslate('core', "Watermark Album"),
	    			'text' => getIconText('', gTranslate('core', "Watermark Album"), $override, $withIcons),
	    			'value' => showChoice2("watermark_album.php", array("set_albumName" => $myAlbum->fields["name"]))
    			);
    		}
    		if ($gallery->user->canViewComments($myAlbum) && ($myAlbum->lastCommentDate("no") != -1)) {
    			$options[] = array(
	    			'pure_text' => gTranslate('core', "View Comments"),
	    			'text' => getIconText('', gTranslate('core', "View Comments"), $override, $withIcons),
	    			'value' => showChoice2("view_comments.php", array("set_albumName" => $myAlbum->fields["name"]),"url")
    			);
    		}
    	}
    	if (! $isAlbum) {
    	    $options[] = array(
	    		'pure_text' => gTranslate('core', "Copy"),
	    		'text' => getIconText('editcopy.gif', gTranslate('core', "Copy"), $override, $withIcons),
	    		'value' => showChoice2("copy_photo.php", array("index" => $i))
    	    );
    	}

    	if ($gallery->album->isHidden($i)) {
    		$options[] = array(
	    		'pure_text' => gTranslate('core', "Show"),
	    		'text' => getIconText('idea.gif', gTranslate('core', "Show"), $override, $withIcons),
	    		'value' => showChoice2("do_command.php", array("cmd" => "show", "index" => $i))
    		);
    	} else {
    		$options[] = array(
	    		'pure_text' => gTranslate('core', "Hide"),
	    		'text' => getIconText('no_idea.gif', gTranslate('core', "Hide"), $override, $withIcons),
	    		'value' => showChoice2("do_command.php", array("cmd" => "hide", "index" => $i))
    		);
    	}
    }

    if ($gallery->user->canWriteToAlbum($gallery->album)) {
    	$options[] = array(
	    	'pure_text' => gTranslate('core', "Reorder"),
	    	'text' => getIconText('tab_duplicate.gif',gTranslate('core', "Reorder"), $override, $withIcons),
	    	'value' => showChoice2("move_photo.php", array("index" => $i, 'reorder' => 1))
    	);

    	/* ----- Item is photo, or subalbum with highlight ----- */
    	if ($isPhoto || (isset($myAlbum) && $myAlbum->hasHighlight())) {
    		$options[] = array(
	    		'pure_text' => gTranslate('core', "Set as highlight"),
	    		'text' => getIconText('viewmag1.gif', gTranslate('core', "Set as highlight"), $override, $withIcons),
	    		'value' => showChoice2('do_command.php', array('cmd' => 'highlight', 'index' => $i))
    		);
    	}
    }

    if (isset($isAdmin) && ! $isAlbum) {
    	$options[] = array(
	    	'pure_text' => gTranslate('core', "Change Owner"),
	    	'text' => getIconText('yast_kuser.gif', gTranslate('core', "Change Owner"), $override, $withIcons),
	    	'value' => showChoice2("photo_owner.php", array("id" => $id))
    	);
    }

    if ($gallery->user->canDeleteFromAlbum($gallery->album) ||
	   ($gallery->album->getItemOwnerDelete() && isset($isOwner)))
	{
    	if($isAlbum) {
    		if($gallery->user->canDeleteAlbum($myAlbum)) {
    			$options[] = array(
	    			'pure_text' => gTranslate('core', "Delete"),
	    			'text' =>getIconText('delete.gif', gTranslate('core', "Delete"), $override, $withIcons),
	    			'value' => showChoice2("delete_photo.php", array("id" => $myAlbum->fields["name"], "albumDelete" => 1))
    			);
    		}
    	}
    	else {
    		$options[] = array(
	    		'pure_text' => gTranslate('core', "Delete"),
	    		'text' => getIconText('delete.gif',gTranslate('core', "Delete"), $override, $withIcons),
	    		'value' => showChoice2('delete_photo.php', array('id' => $id, 'nextId' => $nextId))
    		);
    	}
    }

    if($isPhoto) {
    	$photo = $gallery->album->getPhoto($i);

    	if ($gallery->album->fields["use_exif"] == 'yes' &&
    	  (eregi("jpe?g\$", $photo->image->type)) &&
    	  (isset($gallery->app->use_exif) || isset($gallery->app->exiftags)))
		{
    		$options['showExif'] = array(
	    		'pure_text' => gTranslate('core', "Photo properties"),
	    		'text' => getIconText('frame_query.gif', gTranslate('core', "Photo properties"), $override, $withIcons),
	    		'value' => showChoice2("view_photo_properties.php", array("index" => $i))
    		);
    	}
    }

    if(!empty($options)) {
    	if(sizeof($options) > 1) {
    		array_sort_by_fields($options, 'pure_text', 'asc', false, true);
    	}
    	$options = array_merge(array(
    	  array(
	    	'pure_text' => sprintf(gTranslate('core', "%s actions"), $label),
	    	'text' => '&laquo; '. sprintf(gTranslate('core', "%s actions"), $label) . ' &raquo;',
	    	'value' => '',
	    	'selected' => true)
	    	), $options
    	);
    }

    return $options;
}

/**
 * Returns a HTML with all comments of an album item.
 *
 * @param integer   $index		itemindex
 * @param string	$albumName	Name of the album containing the item
 * @param boolean   $reverse	Wether to show in reverse order or not
 * @return string				A rendered HTML Table that contains the comments.
 * @author Jens Tkotz
 */
function showComments ($index, $albumName, $reverse = false) {
    global $gallery;

    $numComments = $gallery->album->numComments($index);
    $delCommentText = getIconText('delete.gif', gTranslate('core', "Delete comment"), 'yes');

    $commentdraw["index"] = $index;

    $commentTable = new galleryTable();
    $commentTable->setAttrs(array(
        'width' => '75%',
        'style' => 'padding-left:30px;',
        'border' => 0,
        'cellspacing' => 0,
        'cellpadding' => 0,
        'class' => 'commentbox')
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
            'cellArgs' => array('class' => 'admin', 'width' => 50, 'height' => '25')));

        $commentTable->addElement(array(
            'content' => $commenterName,
            'cellArgs' => array('class' => 'admin', 'width' => '55%')));

        $commentTable->addElement(array(
            'content' => '('. $comment->getDatePosted() .')',
            'cellArgs' => array('class' => 'admin')));

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
            'cellArgs' => array('colspan' => $columns, 'style' => 'padding-left:10px; border-top:1px solid black', 'class' => 'albumdesc'))
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
 * @param integer	$currentId
 * @return integer	NextId ;)
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
?>
