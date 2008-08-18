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
 * This page Created by Joseph D. Scheve ( chevy@tnatech.com ) for the
 * very pimp application that is Gallery.
 *
 * $Id$
*/

require_once(dirname(__FILE__) . '/init.php');

// Hack check
if (empty ($gallery->album) || !$gallery->album->isLoaded()) {
	printPopupStart(gTranslate('core', "View Comments"));
	showInvalidReqMesg();
	exit;
}

// Further hack check
if (!$gallery->user->canViewComments($gallery->album) &&
	(! isset($gallery->app->comments_overview_for_all) || $gallery->app->comments_overview_for_all != "yes"))
{
	printPopupStart(gTranslate('core', "View Comments"));
	showInvalidReqMesg();
	exit;
}

$albumName = $gallery->session->albumName;

if (empty($gallery->session->viewedAlbum[$albumName]) && !$gallery->session->offline) {
	$gallery->session->viewedAlbum[$albumName] = 1;
	$gallery->album->incrementClicks();
}

$bordercolor = $gallery->album->fields["bordercolor"];

$breadcrumb["bordercolor"] = $bordercolor;

if (!$GALLERY_EMBEDDED_INSIDE) {
	doctype();
?>
<html>
<head>
  <title><?php echo $gallery->app->galleryTitle ?> :: <?php echo $gallery->album->fields["title"] ?></title>
  <?php echo getStyleSheetLink() ?>
  <?php
  if( !empty($gallery->album->fields["linkcolor"]) ||
  	  !empty($gallery->album->fields["bgcolor"]) ||
  	  !empty($gallery->album->fields["textcolor"]))
  {
  	echo "\n<style type=\"text/css\">";
  	// the link colors have to be done here to override the style sheet
  	if ($gallery->album->fields["linkcolor"]) {
  		echo "\n  a:link, a:visited, a:active {";
  		echo "\n		color: ".$gallery->album->fields['linkcolor'] ."; }";
  		echo "\n  a:hover { color: #ff6600; }";

  	}
  	if ($gallery->album->fields["bgcolor"]) {
  		echo "body { background-color:".$gallery->album->fields['bgcolor']."; }";
  	}

  	if (isset($gallery->album->fields['background']) && $gallery->album->fields['background']) {
  		echo "body { background-image:url(".$gallery->album->fields['background']."); } ";
  	}

  	if ($gallery->album->fields["textcolor"]) {
  		echo "body, tf {color:".$gallery->album->fields['textcolor']."; }";
  		echo ".head {color:".$gallery->album->fields['textcolor']."; }";
  		echo ".headbox {background-color:".$gallery->album->fields['bgcolor']."; }";
  	}

  	echo "\n  </style>";
  }
  ?>
</head>

<body>
<?php }

/* User maybe wants to delete comments */
list($index, $comment_index, $submit) = getRequestVar(array('index', 'comment_index', 'submit'));

if (!empty($submit) && $gallery->user->canWriteToAlbum($gallery->album) &&
	!empty($comment_index) && isValidGalleryInteger($comment_index) &&
	!empty($index) && $comment_index[$index])
{
	$saveMsg = '';
	/* First we reverse the index array, as we want to delete backwards */
	foreach(array_reverse($comment_index, true) as $com_index => $trash) {
		$comment = $gallery->album->getComment($index, $com_index);

		/* maybe user reloaded page, this prevents an errormessage */
		if (!isset($comment)) {
			continue;
		}

		if (isDebugging()) {
			echo "\n<br>". sprintf(gTranslate('core', "Deleting comment %d from item with index: %d"), $com_index, $index);
		}

		$saveMsg = array(
			i18n("Comment \"%s\" by %s deleted from %s"),
			$comment->getCommentText(),
			$comment->getName(),
			makeAlbumURL($gallery->album->fields["name"], $gallery->album->getPhotoId($index))
		);

		$gallery->album->deleteComment($index, $com_index);
		$gallery->album->save($saveMsg);
	}
}

includeTemplate('album.header');

includeLayout('adminbox.inc');

includeLayout('breadcrumb.inc');

if (!$gallery->user->canViewComments($gallery->album)) {
	echo "<p>". gallery_error(_("Sorry.  You are not allowed to see comments of this album.")) ."</p>";
}
else {
	$numPhotos = $gallery->album->numPhotos(1);
	$commentbox["bordercolor"] = $bordercolor;
	$i = 1;
	while($i <= $numPhotos) {
		set_time_limit($gallery->app->timeLimit);
		$id = $gallery->album->getPhotoId($i);
		$index = $gallery->album->getPhotoIndex($id);
		if ($gallery->album->isAlbum($i)) {
			$myAlbumName = $gallery->album->getAlbumName($i);
			$myAlbum = new Album();
			$myAlbum->load($myAlbumName);

			if ( $myAlbum->lastCommentDate("no") != -1 &&
				((!$gallery->album->isHidden($i) && $gallery->user->canReadAlbum($myAlbum)) ||
				$gallery->user->isAdmin() ||
				$gallery->user->isOwnerOfAlbum($gallery->album) ||
				$gallery->user->isOwnerOfAlbum($myAlbum)))
			{
				$subAlbum = true;
				$myHighlightTag = $myAlbum->getHighlightTag();
				require(dirname(__FILE__) .'/templates/commentbox.tpl.default');
			}
		}
		elseif (!$gallery->album->isHidden($i) ||
				$gallery->user->isAdmin() ||
				$gallery->user->isOwnerOfAlbum($gallery->album) ||
				$gallery->album->isItemOwner($gallery->user, $i))
		{
			$comments = $gallery->album->numComments($i);
			if($comments > 0) {
				require(dirname(__FILE__) .'/templates/commentbox.tpl.default');
			}
		}

		$subAlbum = false;
		$i = getNextPhoto($i);
	}
}

includeLayout('breadcrumb.inc');

echo languageSelector();

// Its better not to touch anything below this.
includeTemplate('info_donation-block');

includeTemplate("overall.footer");

if (!$GALLERY_EMBEDDED_INSIDE) { ?>

</body>
</html>
<?php } ?>
