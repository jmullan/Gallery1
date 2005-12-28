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
?>
<?php

require_once(dirname(__FILE__) . '/init.php');

if (!isset($gallery->album) || !$gallery->user->canWriteToAlbum($gallery->album)) {
        echo _("You are not allowed to perform this action!");
        exit;
}

$rearrList = getRequestVar('rearrList');

if (!empty($rearrList)) {
	$gallery->album->rearrangePhotos(explode(',',$rearrList));
	$gallery->album->save(array(i18n("Images rearranged")));
	dismissAndReload();
	return;
}

$rows = $gallery->album->fields["rows"];
$cols = $gallery->album->fields["cols"];
$numPhotos = $gallery->album->numPhotos(1);

doctype() ?>
<html>
<head>
  <title><?php echo $gallery->app->galleryTitle ?> :: <?php echo sprintf (_("Rearrange Album: %s"),$gallery->album->fields["title"]) ?></title>
  <?php common_header(); ?>

<script language="javascript" type="text/javascript">
var sel = -1, list = new Array();

function save() {
  var s = '';
  for (i=1; i<list.length; i++) {
    if (i>1) s+=',';
    s+=list[i];
  }
  document.forms['rearr_form'].rearrList.value = s;
  document.forms['rearr_form'].submit();
}
function copy(from, to) {
  to.src = from.src;
  to.width = from.width;
  to.height = from.height;
  to.style.border = from.style.border;
}

function doclick(idx) {
  if (sel < 0) {
    sel=idx;
    savedFromBorder = document.getElementById('im_'+sel).style.border;
    document.getElementById('im_'+sel).style.borderStyle='dashed';
  } else {
    if (idx != sel) {
      var sv = new Object()
      var si; 
      var dir = (sel<idx)?1:-1;

      sv.style = new Object();

      copy(document.getElementById('im_'+sel), sv);
      si = list[sel];
      for (i=sel; i!=idx; i+=dir) {
        copy(document.getElementById('im_'+(i+dir)),
             document.getElementById('im_'+i));
        list[i] = list[i+dir];
      }
      copy(sv, document.getElementById('im_'+idx));
      list[idx] = si;
    }
    document.getElementById('im_'+idx).style.border = savedFromBorder;	
    sel = -1;
  }
}
</script>
</head>

<body dir="<?php echo $gallery->direction ?>" class="popupbody">
<div class="popuphead"><?php echo sprintf (_("Rearrange Album: %s"),$gallery->album->fields["title"]) ?></div>
<div class="admin" align="center">
<?php 
echo _("Here you can rearrange your pictures easily. Just click on the item you want to reorder. Then click on the item at which position you want it to be.");

$explainTable = new galleryTable;
$explainTable->setCaption(_("Meaning of the borderstyle"), 'attention');
$explainTable->setAttrs(array('width' => 300, 'cellspacing' => 3, 'cellpadding' => 2));
$explainTable->setColumnCount(4);

$explainTable->addElement(array('content' => _("Picture"), 'cellArgs' => array('style' => 'text-align: center; border: 2px solid black')));
$explainTable->addElement(array('content' => _("Movie"), 'cellArgs' => array('style' => 'text-align: center; border: 2px dotted black')));
$explainTable->addElement(array('content' => _("Subalbum") , 'cellArgs' => array('style' => 'text-align: center; border: 3px double black')));
$explainTable->addElement(array('content' => _("Selected") , 'cellArgs' => array('style' => 'text-align: center; border: 2px dashed black')));
$explainTable->addElement(array('content' => _("Visible") , 'cellArgs' => array('colspan' => 2, 'style' => 'text-align: center; color: green')));
$explainTable->addElement(array('content' => _("Hidden") , 'cellArgs' => array('colspan' => 2, 'style' => 'text-align: center; color: red')));

?>
<br><br><center><?php echo $explainTable->render(); ?></center>
</div>

<div class="popup" align="center">
<?php
  echo makeFormIntro('rearrange.php',array('name' => 'rearr_form'));
?>
<input type="hidden" name="rearrList" value="">

<?php
$pictureTable = new galleryTable();
$pictureTable->setAttrs(array('width' => '100%', 'cellspacing' => 0, 'cellpadding' => 2));

$pictureTable->setColumnCount($cols);

$pictureTable->addElement(array(
    'content' => '<input type="button" onclick="save();return false" value="' . _("save") .'">'. 
    '<input type="button" onclick="window.close();return false" value="'. _("cancel") .'">',
    'cellArgs' => array('colspan' => $cols, 'align' => 'right')));

$list = array();
$j = 1;
$page = 1;

for ($i = getNextPhoto(0), $i = 1; $i <= $numPhotos; $i = getNextPhoto($i)) {
    if ($j++==($cols*$rows) || $page == 1) {
        $pictureTable->addElement(array(
            'content' => sprintf(_("******* Page %s *******"), $page),
            'cellArgs' => array('colspan' => $cols, 'align' => 'center')));

        $j = 1;
        $page++;
    }

    $attrs = 'id="im_'.$i.'" onclick="doclick('.$i.')" style="padding: 2px; border: '
    . ($gallery->album->isHidden($i) ? ' red' : ' green');

    if ($gallery->album->isAlbum($i)) {
        $myAlbumName = $gallery->album->getAlbumName($i);
        $myAlbum = new Album();
        $myAlbum->load($myAlbumName);
        $tag = $myAlbum->getHighlightTag(0,$attrs. ' 3px double"');
    } elseif ($gallery->album->isMovieByIndex($i)) {
        $tag = $gallery->album->getThumbnailTag($i,0,$attrs.' 2px dotted"');
    } else {
        $tag = $gallery->album->getThumbnailTag($i,0,$attrs.' 2px solid"');
    }

    $pictureTable->addElement(array('content' => $tag, 'cellArgs' => array('align' => 'center')));

    $list[] = $i;
}

$pictureTable->addElement(array(
    'content' => '<input type="button" onclick="save();return false" value="' . _("save") .'">'. 
	  '<input type="button" onclick="window.close();return false" value="'. _("cancel") .'">',
    'cellArgs' => array('colspan' => $cols, 'align' => 'right')));

echo $pictureTable->render();
?>
</form>
</div>
<?php print gallery_validation_link("rearrange.php", true); ?>

<script language="javascript" type="text/javascript">
<?php 
foreach ($list as $key=>$value) { 
	echo "list[".($key+1)."]=$value;\n"; 
}
?>
</script>
</body>
</html>