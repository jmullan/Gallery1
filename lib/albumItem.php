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

/**
 * @package	Item
 * @author	Jens Tkotz
 */

/**
 * This function shows all possible actions for an album item.
 * @param	integer $i	index number of the item
 * @return 	array		Array of all possible album item for the current user.
 */
function getItemActions($i, $withIcons = false) {
    global $gallery;
    global $nextId;

    static $javascriptSet;

    $id = $gallery->album->getPhotoId($i);
    $override = ($withIcons) ? '' : 'no';
    $options = array();

    if (!$gallery->session->offline && empty($javascriptSet)) { ?>
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

    if ($gallery->album->isMovieByIndex($i)) {
        $label = _("Movie");
    } elseif ($gallery->album->isAlbum($i)) {
        $label = _("Album");
    } else {
        $label = _("Photo");
    }

    if($gallery->album->isAlbum($i)) {
        if(!isset($myAlbum)) {
            $albumName = $gallery->album->getAlbumName($i);
            $myAlbum = new Album();
            $myAlbum->load($albumName);
        }
    }

    $options[] = array(
        'text' => '&laquo; '. sprintf(_("%s actions"), $label) . ' &raquo;',
        'value' => ''
    );

    if ($gallery->album->getItemOwnerDelete() &&
      $gallery->album->isItemOwner($gallery->user->getUid(), $i) &&
      !$gallery->album->isAlbum($i) &&
      !$gallery->user->canDeleteFromAlbum($gallery->album)) {
        $options[] = array(
          'text' => getIconText('delete.gif',_("Delete"), $override, $withIcons),
            'value' => showChoice2('delete_photo.php', array('id' => $id, 'nextId' => $nextId))
        );
    }

    if ($gallery->user->canChangeTextOfAlbum($gallery->album)) {
        if (isset($myAlbum)) {
            if ($gallery->user->canChangeTextOfAlbum($myAlbum)) {
                $options[] = array(
                    'text' => getIconText('',_('Edit Title'), $override, $withIcons),
                    'value' =>  showChoice2("edit_field.php", array("set_albumName" => $myAlbum->fields["name"], "field" => "title"))
                );
                $options[] = array(
                    'text' => getIconText('',_('Edit Description'), $override, $withIcons),
                    'value' =>  showChoice2("edit_field.php", array("set_albumName" => $myAlbum->fields["name"], "field" => "description"))
                );
            }
            if ($gallery->user->isAdmin() || $gallery->user->isOwnerOfAlbum($myAlbum)) {
                $options[] = array(
                    'text' => getIconText('',_("Rename Album"), $override, $withIcons),
                    'value' => showChoice2("rename_album.php", array("set_albumName" => $myAlbum->fields["name"], "index" => $i))
                );
            }
        } else {
            $options[] = array(
                'text' => getIconText('kcmfontinst.gif',_("Edit Text"), $override, $withIcons),
                'value' => showChoice2("edit_caption.php", array("index" => $i))
            );
        }
    }

    if ($gallery->user->canWriteToAlbum($gallery->album)) {
        if (!$gallery->album->isMovieByIndex($i) && !$gallery->album->isAlbum($i)) {
            $options[] = array(
                'text' => getIconText('thumbnail.gif',_("Edit Thumbnail"), $override, $withIcons),
                'value' => showChoice2('edit_thumb.php', array('index' => $i))
            );
            $options[] = array(
                'text' => getIconText('reload.gif',sprintf(_("Rotate/Flip"), $label), $override, $withIcons),
                'value' => showChoice2('rotate_photo.php', array('index' => $i))
            );
            $options[] = array(
                'text' => getIconText('window_fullscreen.gif',_("Resize"), $override, $withIcons),
                'value' => showChoice2('resize_photo.php', array('index' => $i))
            );
            if (!empty($gallery->app->watermarkDir)) {
                $options[] = array(
                    'text' => getIconText('camera.gif',_("Watermark"), $override, $withIcons),
                    'value' =>  showChoice2('edit_watermark.php', array('index' => $i))
                );
            }
        }
        if (!$gallery->album->isMovieByIndex($i)) {
            $nestedAlbum=$gallery->album->getNestedAlbum($i);
            if (!$gallery->album->isAlbum($i) || $nestedAlbum->hasHighlight()) {
                $options[] = array(
                    'text' => getIconText('viewmag1.gif',sprintf(_("Set as highlight"),$label), $override, $withIcons),
                    'value' => showChoice2('do_command.php', array('cmd' => 'highlight', 'index' => $i))
                );
            }
        }
        if ($gallery->album->isAlbum($i)) {
            $options[] = array(
                'text' => getIconText('',_("Reset Counter"), $override, $withIcons),
                'value' => showChoice2("do_command.php", array("cmd" => "reset-album-clicks", "set_albumName" => $gallery->album->getAlbumName($i),"return" => urlencode(makeGalleryUrl("view_album.php"))))
            );
        }
        $options[] = array(
            'text' => getIconText('tab_duplicate.gif',_("Move"), $override, $withIcons),
            'value' => showChoice2("move_photo.php", array("index" => $i, 'reorder' => 0))
        );
        $options[] = array(
            'text' => getIconText('tab_duplicate.gif',_("Reorder"), $override, $withIcons),
            'value' => showChoice2("move_photo.php", array("index" => $i, 'reorder' => 1))
        );
        if (!$gallery->album->isAlbum($i)) {
            $options[] = array(
                'text' => getIconText('editcopy.gif',_("Copy"), $override, $withIcons),
                'value' => showChoice2("copy_photo.php", array("index" => $i))
            );
        }
    }

    if ($gallery->user->isAdmin() || ((isset($myAlbum) && $gallery->user->isOwnerOfAlbum($myAlbum)) ||
      $gallery->album->isItemOwner($gallery->user->getUid(), $i))) {
        if ($gallery->album->isHidden($i)) {
            $options[] = array(
                'text' => getIconText('idea.gif',_("Show"), $override, $withIcons),
                'value' => showChoice2("do_command.php", array("cmd" => "show", "index" => $i))
            );
        } else {
            $options[] = array(
                'text' => getIconText('no_idea.gif',_("Hide"), $override, $withIcons),
                'value' => showChoice2("do_command.php", array("cmd" => "hide", "index" => $i))
            );
        }
    }

    if ($gallery->user->canDeleteFromAlbum($gallery->album)) {
        if($gallery->album->isAlbum($i)) {
            if($gallery->user->canDeleteAlbum($myAlbum)) {
                $options[] = array(
                    'text' =>getIconText('delete.gif', _("Delete"), $override, $withIcons),
                    'value' => showChoice2("delete_photo.php", array("id" => $myAlbum->fields["name"], "albumDelete" => 1))
                );
            }
        } else {
            $options[] = array(
                'text' => getIconText('delete.gif',_("Delete"), $override, $withIcons),
                'value' => showChoice2('delete_photo.php', array('id' => $id, 'nextId' => $nextId))
            );
        }
    }

    if($gallery->album->isAlbum($i)) {
        if ($gallery->user->isAdmin() || $gallery->user->isOwnerOfAlbum($myAlbum)) {
            $options[] = array(
                'text' => getIconText('decrypted.gif',_("Permissions"), $override, $withIcons),
                'value' => showChoice2("album_permissions.php", array("set_albumName" => $myAlbum->fields["name"]))
            );
            // Watermarking support is enabled and user is allowed to watermark images/albums /
            if (!empty($gallery->app->watermarkDir) && $myAlbum->numPhotos(1)) {
                $options[] = array(
                    'text' => getIconText('',_("Watermark Album"), $override, $withIcons),
                    'value' => showChoice2("watermark_album.php", array("set_albumName" => $myAlbum->fields["name"]))
                );
            }
            if ($gallery->user->canViewComments($myAlbum) && ($myAlbum->lastCommentDate("no") != -1)) {
                $options[] = array(
                    'text' => getIconText('',_("View Comments"), $override, $withIcons),
                    'value' => showChoice2("view_comments.php", array("set_albumName" => $myAlbum->fields["name"]),"url")
                );
            }
        }
    } else {
        $photo = $gallery->album->getPhoto($i);
        if ($gallery->album->fields["use_exif"] == "yes" &&
          (eregi("jpe?g\$", $photo->image->type)) &&
          (isset($gallery->app->use_exif) || isset($gallery->app->exiftags))) {
            $options[] = array(
                'text' => getIconText('frame_query.gif',_("Photo properties"), $override, $withIcons),
                'value' => showChoice2("view_photo_properties.php", array("index" => $i))
            );
        }
    }

    if ($gallery->user->isAdmin() && !$gallery->album->isAlbum($i)) {
        $options[] = array(
            'text' => getIconText('yast_kuser.gif',_("Change Owner"), $override, $withIcons),
            'value' => showChoice2("photo_owner.php", array("id" => $id))
        );
    }

    array_sort_by_fields($options, 'text');
    return $options;
}


function showComments ($index, $albumName, $reverse = false) {
    global $gallery;

    $numComments = $gallery->album->numComments($index);
    $delCommentText = getIconText('delete.gif', _("delete comment"), 'yes');

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
            'content' => _("From:"),
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
 */
function getNextId($currentId) {
    global $gallery;
    
    $allIds = $gallery->album->getIds($gallery->user->canWriteToAlbum($gallery->album));
    $current = array_search($currentId, $allIds);
    
    if ($current < sizeof($allIds)-1) {
        $nextId = $allIds[$current+1];
    } elseif ($current > 0) {
        $nextId = $allIds[$current-1];
    } else {
        $nextId = $currentId;
    }
    
    return $nextId;
}
?>
