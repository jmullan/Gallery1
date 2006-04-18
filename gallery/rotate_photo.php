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

require_once(dirname(__FILE__) . '/init.php');

list($index, $rotate) = getRequestVar(array('index', 'rotate'));

// Hack check
if (!$gallery->user->canWriteToAlbum($gallery->album) &&
!($gallery->album->isItemOwner($gallery->user->getUid(), $index) &&
$gallery->album->getItemOwnerModify())) {
    echo _("You are not allowed to perform this action!");
    exit;
}

doctype();
?>

<html>
<head>
  <title><?php echo _("Rotate/Flip Photo") ?></title>
  <?php common_header(); ?>
  <META HTTP-EQUIV="Pragma" CONTENT="no-cache"> 
  <META HTTP-EQUIV="expires" CONTENT="0"> 
  </head>
<body dir="<?php echo $gallery->direction ?>" class="g-popup">
<div class="g-header-popup">
  <div class="g-pagetitle-popup"><?php echo _("Rotate/Flip Photo"); ?></div>
</div>
<div class="g-content-popup" align="center">
<?php
if ($gallery->session->albumName && isset($index)) {
    if (isset($rotate) && !empty($rotate)) {
        echo _("Rotating/Flipping photo.");
        echo "\n<br>";
        echo _("(this may take a while)");

        my_flush();
        set_time_limit($gallery->app->timeLimit);
        $gallery->album->rotatePhoto($index, $rotate);
        $gallery->album->save(array(i18n("Image %s rotated"),
          makeAlbumURL($gallery->album->fields["name"],
            $gallery->album->getPhotoId($index))));
        reload();
        print "<p>" . _("Manipulate again?");
    } else {
        echo _("How do you want to manipulate this photo?");
    }

    echo "\n<br><br>";

    $args = array("albumName" => $gallery->album->fields["name"], "index" => $index, 'type' => 'popup');

    $args["rotate"] = "90";
    $rotateElements[] = galleryLink(
      makeGalleryUrl("rotate_photo.php", $args),
      getIconText('imageedit/rotate-90.gif', _("Clockwise 90&deg;"))
    );

    $args["rotate"] = "180";
    $rotateElements[] = galleryLink(
      makeGalleryUrl("rotate_photo.php", $args),
      getIconText('imageedit/rotate-180.gif', _("180&deg;"))
    );

    $args["rotate"] = "-90";
    $rotateElements[] = galleryLink(
      makeGalleryUrl("rotate_photo.php", $args),
      getIconText('imageedit/rotate-270.gif', _("Counter-Clockwise 90&deg;"))
    );


    $args["rotate"] = "fh";
    $rotateElements[] = galleryLink(
      makeGalleryUrl("rotate_photo.php", $args),
      getIconText('imageedit/mirror.gif', _("Horizontal"))
    );

    $args["rotate"] = "fl";
    $rotateElements[] = galleryLink(
      makeGalleryUrl("rotate_photo.php", $args),
      getIconText('imageedit/flip.gif', _("Vertical"))
    );

    $actionTable = new galleryTable();
    $actionTable->setColumnCount(5);
    $actionTable->setAttrs(array('class' => 'g-iconmenu'));

    $actionTable->addElement(array(
        'content' => "<b>". _("Rotate") ."</b>", 
        'cellArgs' => array('colspan' => 3,'align' => 'center'))
    );
    $actionTable->addElement(array(
        'content' => "<b>". _("Flip") ."</b>" ,
        'cellArgs' => array('colspan' => 2,'align' => 'center'))
    );
    foreach ($rotateElements as $element) {
        $actionTable->addElement(array('content' => $element));
    }

    echo $actionTable->render();
?>
<br>
<input type="button" onClick="javascript:void(parent.close())" value="<?php echo _("Close") ?>" class="g-button">

<p>
<?php 
    echo $gallery->album->getThumbnailTag($index);
} else {
    echo gallery_error(_("no album / index specified"));
}
?>
</div>
<?php print gallery_validation_link("rotate_photo.php"); ?>
</body>
</html>
