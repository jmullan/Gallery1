<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2004 Bharat Mediratta
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
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 * $Id$
 */
?>
<?php

require(dirname(__FILE__) . '/init.php');

/* 
 * Test for relative URL, which we know to be local.  If URL contains ://
 * assume that it's remote and test it against our local full URLs
 * to ensure security.  Don't check for http:// or https:// because
 * for all we know, someone put their album URL on a gopher server...
 */
if (isset($return) && $return[0] != '/' && strstr($return, '://') !== false) {
    if (strncmp($return, $gallery->app->photoAlbumURL, strlen($gallery->app->photoAlbumURL)) != 0 &&
            strncmp($return, $gallery->app->albumDirURL, strlen($gallery->app->albumDirURL)) != 0) {
        die(_('Attempted security breach.'));
    }
}

if (!strcmp($cmd, "remake-thumbnail")) {
	if ($gallery->user->canWriteToAlbum($gallery->album)) {
?>
<html>
<head>
  <title><?php echo _("Rebuilding Thumbnails") ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>">
<span class="popup">
<?php
		if ($gallery->session->albumName && isset($index)) {
			if (!strcmp($index, "all")) {
				$np = $gallery->album->numPhotos(1);
				echo ("<br> " . sprintf(_("Rebuilding %d thumbnails..."), $np));
				my_flush();
				for ($i = 1; $i <= $np; $i++) {
					echo("<br> ". sprintf(_("Processing image %d..."), $i));
					my_flush();
					set_time_limit($gallery->app->timeLimit);
					$gallery->album->makeThumbnail($i);
				}
			} else {
				echo ("<br> " . _("Rebuilding 1 thumbnail..."));
				my_flush();
				set_time_limit($gallery->app->timeLimit);
				$gallery->album->makeThumbnail($index);
			}
			$gallery->album->save();
			//-- this is expected to be loaded in a popup, so dismiss ---
			dismissAndReload();
		}
	}
} else if (!strcmp($cmd, "logout")) {
	gallery_syslog("Logout by ". $gallery->session->username ." from ". $HTTP_SERVER_VARS['REMOTE_ADDR']);
	$gallery->session->username = "";
	$gallery->session->language = "";
	if (!ereg("^http|^{$gallery->app->photoAlbumURL}", $return)) {
		$return = makeGalleryHeaderUrl($return);
	}
	header("Location: $return");
} else if (!strcmp($cmd, "hide")) {
	if ($gallery->user->canWriteToAlbum($gallery->album)) {
		$gallery->album->hidePhoto($index);
		$gallery->album->save();
	} else {
		if ($gallery->album->isAlbum($index)) {
			$myAlbumName = $gallery->album->getAlbumName($index);
			$myAlbum = new Album;
			$myAlbum->load($myAlbumName);
		}

		if ((isset($myAlbum) && $gallery->user->isOwnerOfAlbum($myAlbum)) || 
		    $gallery->album->isItemOwner($gallery->user->getUid(), $index)) {
			$gallery->album->hidePhoto($index);
			$gallery->album->save();
		}
	}
	//-- this is expected to be loaded in a popup, so dismiss ---
	dismissAndReload();
} else if (!strcmp($cmd, "show")) {
	if ($gallery->user->canWriteToAlbum($gallery->album)) {
		$gallery->album->unhidePhoto($index);
		$gallery->album->save();
	} else {
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
} else if (!strcmp($cmd, "highlight")) {
	if ($gallery->user->canWriteToAlbum($gallery->album)) {
		$gallery->album->setHighlight($index);
		$gallery->album->save(array(i18n("Changed Highlight")));
	}
	//-- this is expected to be loaded in a popup, so dismiss ---
	dismissAndReload();
} else if (!strcmp($cmd, "new-album")) {
	if ($gallery->user->canCreateAlbums() ||
	    $gallery->user->canCreateSubAlbum($gallery->album)) {
		if (!isset($parentName)) {
			$parentName=null;
		}
		createNewAlbum($parentName);

		header("Location: " . makeAlbumHeaderUrl($gallery->session->albumName));
	} else {
	        header("Location: " . makeAlbumHeaderUrl());
	}
} else if (!strcmp($cmd, "reset-album-clicks")) {
	if ($gallery->user->canWriteToAlbum($gallery->album)) {
		$gallery->album->resetAllClicks();
		// this is a popup do dismiss and reload!
		dismissAndReload();
	} else {
	        header("Location: " . makeAlbumHeaderUrl());
	}

} else if (!strcmp($cmd, "delete-comment")) {
	if ($gallery->user->canWriteToAlbum($gallery->album)) {
		$comment=$gallery->album->getComment($index, $comment_index); 
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
	} else {
	        header("Location: " . makeAlbumHeaderUrl());
	}

} else if (!empty($return)) {
	// No command; Can be used to set a session variable
	header("Location: $return");
}
?>

<center>
<form>
<input type="button" value="<?php echo _("Dismiss") ?>" onclick='parent.close()'>
</form>

</span>
</body>
</html>
