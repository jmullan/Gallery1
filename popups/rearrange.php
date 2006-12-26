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

if (!isset($gallery->album) || !$gallery->user->canWriteToAlbum($gallery->album)) {
		echo gTranslate('core', "You are not allowed to perform this action!");
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

doctype();

?>

<html>
<head>
  <title><?php printf(gTranslate('core', "Rearrange items in album: %s"),$gallery->album->fields["title"]); ?></title>
  <?php common_header(); ?>
  <script type="text/javascript">

  var sel = -1, list = new Array();

  function copy(from, to) {
	  to.src = from.src;
	  to.width = from.width;
	  to.height = from.height;
	  to.style.border = from.style.border;
  }

  function doclick(idx) {
	  if (sel < 0) {
		  sel = idx;
		  savedFromBorder = document.getElementById('im_'+sel).style.border;
		  document.getElementById('im_'+sel).style.borderStyle='dashed';
	  }
	  else {
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
  
  function saveOrder() {
	  var s = '';
	  for (i = 1; i <list.length; i++) {
		  if (i > 1) s+=',';
		  s+=list[i];
	  }
	  document.forms['rearr_form'].rearrList.value = s;
	  document.forms['rearr_form'].submit();
  }
  </script>
</head>
<body>
<div class="g-header-popup">
  <div class="g-pagetitle-popup"><?php echo sprintf (gTranslate('core', "Rearrange Album: %s"),$gallery->album->fields["title"]) ?></div>
</div>
<div class="g-sitedesc">
<?php 
echo gTranslate('core', "Here you can rearrange your pictures easily. Just click on the item you want to reorder. Then click on the item at which position you want it to be.");
?>

<table width="300" cellspacing="3" cellpadding="2" align="center">
<caption class="g-emphasis"><?php echo gTranslate('core', "Meaning of the borderstyle"); ?></caption>
<tr>
	<td style="text-align: center; border: 2px solid black"><?php echo gTranslate('core', "Picture"); ?></td>
	<td style="text-align: center; border: 2px dotted black"><?php echo gTranslate('core', "Movie"); ?></td>
	<td style="text-align: center; border: 3px double black"><?php echo gTranslate('core', "Subalbum"); ?></td>
	<td style="text-align: center; border: 2px dashed black"><?php echo gTranslate('core', "Selected"); ?></td>
</tr>

<tr>
	<td style="text-align: center; color: green" colspan="2"><?php echo gTranslate('core', "Visible"); ?></td>
	<td style="text-align: center; color: red" colspan="2"><?php echo gTranslate('core', "Hidden"); ?></td>
</tr>
</table>

</div>

<div class="g-content-popup" align="center">
<?php
  echo makeFormIntro('rearrange.php', array('name' => 'rearr_form'), array('type' => 'popup'));
?>
<input type="hidden" name="rearrList" value="">

<?php
$pictureTable = new galleryTable();
$pictureTable->setAttrs(array('width' => '100%', 'cellspacing' => 0, 'cellpadding' => 2));

$pictureTable->setColumnCount($cols);

$pictureTable->addElement(array(
	'content' => gButton('saveButtonTop', gTranslate('core', "_Save"), 'saveOrder();') .
				 gButton('cancelButtonTop', gTranslate('core', "_Cancel"), 'window.close();'),
	'cellArgs' => array('colspan' => $cols, 'class' => 'right')));

$list = array();
$j = 1;
$page = 1;

for ($i = getNextPhoto(0), $i = 1; $i <= $numPhotos; $i = getNextPhoto($i)) {
	if ($j++==($cols*$rows) || $page == 1) {
		$pictureTable->addElement(array(
			'content' => sprintf(gTranslate('core', "******* Page %s *******"), $page),
			'cellArgs' => array('colspan' => $cols, 'align' => 'center')));

		$j = 1;
		$page++;
	}

	$attrs = array(
		'id' => "im_$i",
		'onClick' => "doclick($i)",
		'style' => 'margin: 1px; padding: 2px; border: '. ($gallery->album->isHidden($i) ? ' red' : ' green')
	);

	if ($gallery->album->isAlbum($i)) {
		$myAlbumName = $gallery->album->getAlbumName($i);
		$myAlbum = new Album();
		$myAlbum->load($myAlbumName);
		$attrs['style'] .= ' 3px double';
		$tag = $myAlbum->getHighlightTag(0, $attrs);
	}
	elseif ($gallery->album->isMovieByIndex($i)) {
		$attrs['style'] .= ' 2px dotted';
		$tag = $gallery->album->getThumbnailTag($i, 0, $attrs);
	}
	else {
		$attrs['style'] .= ' 2px solid';
		$tag = $gallery->album->getThumbnailTag($i, 0, $attrs);
	}
	
	$pictureTable->addElement(array('content' => $tag, 'cellArgs' => array('align' => 'center')));

	$list[] = $i;
}

$pictureTable->addElement(array(
	'content' => gButton('saveButtonBottom', gTranslate('core', "_Save"), 'saveOrder();') .
				 gButton('cancelButtonBottom', gTranslate('core', "_Cancel"), 'window.close();'),
	'cellArgs' => array('colspan' => $cols, 'class' => 'right')));

echo $pictureTable->render();
?>
</form>
</div>

<script language="javascript" type="text/javascript">
<?php 
foreach ($list as $key => $value) { 
	echo "list[".($key+1)."]=$value;\n"; 
}
?>
</script>
</body>
</html>