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
 * $Id$
 */

require_once(dirname(dirname(__FILE__)) . '/init.php');

list($index, $cmd, $return, $parentName, $rebuild_type, $albumName) =
	getRequestVar(array('index', 'cmd', 'return', 'parentName', 'rebuild_type', 'albumName'));

$backUrl = '';  

/*
 * Test for relative URL, which we know to be local.  If URL contains ://
 * assume that it's remote and test it against our local full URLs
 * to ensure security.  Don't check for http:// or https:// because
 * for all we know, someone put their album URL on a gopher server...
 */
$gUrl = makeGalleryUrl();
$gUrlStripped = substr($gUrl, 0, strrpos($gUrl, '/'));

if (!empty($return) &&
	strncmp($return, $gUrlStripped, strlen($gUrlStripped)) != 0 &&
	strncmp($return, $gallery->app->photoAlbumURL, strlen($gallery->app->photoAlbumURL)) != 0 &&
	strncmp($return, $gallery->app->albumDirURL, strlen($gallery->app->albumDirURL)) != 0 &&
	! isValidUrl($return))
{
	die(gTranslate('core', 'Attempted security breach.'));

}

if(isset($index) && !isValidGalleryInteger($index)) {
	die(gTranslate('core', 'Attempted security breach.'));	
}

/* This is used for deleting comments from stats.php */
if (!empty($albumName)) {
	$gallery->album = new Album();
	$gallery->album->load($albumName);
}

if (empty($rebuild_type)) {
	$title = gTranslate('core', "Rebuilding thumbnails");
}
else {
	$title = gTranslate('core', "Performing operation...");
}

switch ($cmd) {
	case 'rebuild_highlights':
		$albumDB = new AlbumDB(true);
		$albumList = $albumDB->albumList;
		$i = 0;

		foreach($albumList as $nr => $album) {
			if($album->isRoot()) {
				$i++;
				echo "\n<br><b>". sprintf (gTranslate('core', "Rebuilding highlight %s"), $i) . '</b>';
				$album->setHighlight($album->getHighlight());
				$album->save();
			}
		}
		dismissAndReload();

		break;

	case 'remake-thumbnail':
		echo "\n<h3>" . gTranslate('core', "Rebuilding 1 thumbnail...") .'</h3>';
		my_flush();
		set_time_limit($gallery->app->timeLimit);
		$gallery->album->makeThumbnail($index);

		$gallery->album->save();
		//-- this is expected to be loaded in a popup, so dismiss ---
		dismissAndReload();

		break;

	case 'hide':
		if ($gallery->user->canWriteToAlbum($gallery->album)) {
			$gallery->album->hidePhoto($index);
			$gallery->album->save();
		}
		else {
			if ($gallery->album->isAlbum($index)) {
				$myAlbumName = $gallery->album->getAlbumName($index);
				$myAlbum = new Album;
				$myAlbum->load($myAlbumName);
			}

			if ((isset($myAlbum) && $gallery->user->isOwnerOfAlbum($myAlbum)) ||
	   			$gallery->album->isItemOwner($gallery->user->getUid(), $index))
	   		{
				$gallery->album->hidePhoto($index);
				$gallery->album->save();
			}
		}
		//-- this is expected to be loaded in a popup, so dismiss ---
		dismissAndReload();
	break;

	case 'show':
		if ($gallery->user->canWriteToAlbum($gallery->album)) {
			$gallery->album->unhidePhoto($index);
			$gallery->album->save();
		}
		else {
			if ($gallery->album->isAlbum($index)) {
				$myAlbumName = $gallery->album->getAlbumName($index);
				$myAlbum = new Album;
				$myAlbum->load($myAlbumName);
			}

			if ((isset($myAlbum) && $gallery->user->isOwnerOfAlbum($myAlbum)) ||
					$gallery->album->isItemOwner($gallery->user->getUid(), $index)) {
					$gallery->album->unhidePhoto($index);
					$gallery->album->save();
			}
		}
		
		//-- this is expected to be loaded in a popup, so dismiss ---
		dismissAndReload();
	break;

	case 'highlight':
		if ($gallery->user->canWriteToAlbum($gallery->album)) {
			$gallery->album->setHighlight($index);
			$gallery->album->save(array(i18n("Changed Highlight")));
		}

		//-- this is expected to be loaded in a popup, so dismiss ---
		dismissAndReload();

	break;

	case 'new-album':
		if ($gallery->user->canCreateAlbums() ||
			$gallery->user->canCreateSubAlbum($gallery->album))
		{
			if (!isset($parentName)) {
				$parentName = null;
			}

			createNewAlbum($parentName);

			if(!headers_sent()) {
				header("Location: " . makeAlbumHeaderUrl($gallery->session->albumName));
			}
			else {
				$backUrl = makeAlbumUrl($gallery->session->albumName);
			}
		}
		else {
			header("Location: " . makeAlbumHeaderUrl());
		}

	break;

	case 'reset-album-clicks':
		if ($gallery->user->canWriteToAlbum($gallery->album)) {
			$gallery->album->resetAllClicks();
			// this is a popup do dismiss and reload!
			dismissAndReload();
		}
		else {
			header("Location: " . makeAlbumHeaderUrl());
		}

	break;

	case 'delete-comment':
		$comment_index = getRequestVar('comment_index');

		if ($gallery->user->canWriteToAlbum($gallery->album) ||
		    !isValidGalleryInteger($comment_index) && isset($index))
		{
			$comment = $gallery->album->getComment($index, $comment_index);
			$gallery->album->deleteComment($index, $comment_index);
			$gallery->album->save(array(i18n("Comment \"%s\" by %s deleted from %s"),
					$comment->getCommentText(),
				       	$comment->getName(),
				       	makeAlbumURL($gallery->album->fields["name"],
						$gallery->album->getPhotoId($index))));
			if (!empty($return)) {
				dismissAndLoad($return);
			}
			else {
				dismissAndReload();
			}
		}
		else {
			header("Location: " . makeAlbumHeaderUrl());
		}

	break;

	default:
		if (!empty($return)) {
			// No command; Can be used to set a session variable
			header("Location: $return");
		}
	break;

}

?>

	<div align="center">
	<form>
<?php if (!empty($backUrl)) :?>
		<input type="button" value="<?php echo gTranslate('core', "Close window") ?>" onclick="document.location='<?php echo $backUrl; ?>'" class="g-button">
<?php else : ?>
		<input type="button" value="<?php echo gTranslate('core', "Cancel") ?>" onclick="parent.close()" class="g-button">
<?php endif ?>
	</form>
	</div>
</div>
</body>
</html>
