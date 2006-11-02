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

require_once(dirname(dirname(__FILE__)) . '/init.php');

list($index, $rotate) = getRequestVar(array('index', 'rotate'));

// Hack check
if (!$gallery->user->canWriteToAlbum($gallery->album) &&
    !($gallery->album->isItemOwner($gallery->user->getUid(), $index) &&
    $gallery->album->getItemOwnerModify())) {
    echo gTranslate('core', "You are not allowed to perform this action!");
    exit;
}

doctype();
?>

<html>
<head>
  <title><?php echo gTranslate('core', "Rotate/Flip Photo") ?></title>
  <?php common_header(); ?>
  <META HTTP-EQUIV="Pragma" CONTENT="no-cache">
  <META HTTP-EQUIV="expires" CONTENT="0">
  </head>
<body dir="<?php echo $gallery->direction ?>" class="g-popup">
<div class="g-header-popup">
  <div class="g-pagetitle-popup"><?php echo gTranslate('core', "Rotate/Flip Photo"); ?></div>
</div>
<div class="g-content-popup" align="center">
<?php
if ($gallery->session->albumName && isset($index)) {
    if (isset($rotate) && !empty($rotate)) {
        echo gTranslate('core', "Rotating/Flipping photo.");
        echo "\n<br>";
        echo gTranslate('core', "(this may take a while)");

        my_flush();
        set_time_limit($gallery->app->timeLimit);
        $gallery->album->rotatePhoto($index, $rotate);
        $gallery->album->save(array(i18n("Image %s rotated"),
        makeAlbumURL($gallery->album->fields["name"],
        $gallery->album->getPhotoId($index))));
        reload();
        print "<p>" . gTranslate('core', "Manipulate again?");
    }
    else {
        echo gTranslate('core', "How do you want to manipulate this photo?");
    }

    echo "\n<br><br>";

    $args = array("albumName" => $gallery->album->fields["name"], "index" => $index, 'type' => 'popup');

    $args["rotate"] = "90";
    $rotateElements[] = galleryIconLink(
        makeGalleryUrl("rotate_photo.php", $args),
        'imageedit/rotate-90.gif', gTranslate('core', "Clockwise 90&deg;"), '', array('accesskey' => 1)
    );

    $args["rotate"] = "180";
    $rotateElements[] = galleryIconLink(
        makeGalleryUrl("rotate_photo.php", $args),
        'imageedit/rotate-180.gif', gTranslate('core', "180&deg;"), '', array('accesskey' => 2)
    );

    $args["rotate"] = "-90";
    $rotateElements[] = galleryIconLink(
        makeGalleryUrl("rotate_photo.php", $args),
        'imageedit/rotate-270.gif', gTranslate('core', "Counter-Clockwise 90&deg;"), '', array('accesskey' => 3)
    );

    $args["rotate"] = "fh";
    $rotateElements[] = galleryIconLink(
        makeGalleryUrl("rotate_photo.php", $args),
        'imageedit/mirror.gif', gTranslate('core', "Horizontal"), '', array('accesskey' => 4)
    );

    $args["rotate"] = "fl";
    $rotateElements[] = galleryIconLink(
        makeGalleryUrl("rotate_photo.php", $args),
        'imageedit/flip.gif', gTranslate('core', "Vertical"), '', array('accesskey' => 5)
    );

    $actionTable = new galleryTable();
    $actionTable->setColumnCount(5);
    $actionTable->setAttrs(array('class' => 'g-iconmenu'));

    $actionTable->addElement(array(
        'content' => "<b>". gTranslate('core', "Rotate") ."</b>",
        'cellArgs' => array('colspan' => 3,'align' => 'center'))
    );

    $actionTable->addElement(array(
        'content' => "<b>". gTranslate('core', "Flip") ."</b>" ,
        'cellArgs' => array('colspan' => 2,'align' => 'center'))
    );

    foreach ($rotateElements as $element) {
        $actionTable->addElement(array('content' => $element));
    }

    echo $actionTable->render();
?>
<br>
<?php echo gButton('close', gTranslate('core', "_Close"), 'parent.close()'); ?>

<p>
<?php
echo $gallery->album->getThumbnailTag($index);
}
else {
    echo gallery_error(gTranslate('core', "no album / index specified"));
}
?>
</div>

</body>
</html>
