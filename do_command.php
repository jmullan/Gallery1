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
require_once(dirname(__FILE__) . '/init.php');

list($index, $cmd, $return, $parentName, $rebuild_type, $albumName) = 
  getRequestVar(array('index', 'cmd', 'return', 'parentName', 'rebuild_type', 'albumName'));

/* 
 * Test for relative URL, which we know to be local.  If URL contains ://
 * assume that it's remote and test it against our local full URLs
 * to ensure security.  Don't check for http:// or https:// because
 * for all we know, someone put their album URL on a gopher server...
 */
if ($return[0] != '/' && strstr($return, '://') !== false) {
    if (strncmp($return, $gallery->app->photoAlbumURL, strlen($gallery->app->photoAlbumURL)) || 
	    strncmp($return, $gallery->app->albumDirURL, strlen($gallery->app->albumDirURL))) {
	die _('Attempted security breach.');
    }
}	

/* This is used for deleting comments from stats.php */
if (!empty($albumName)) {
	$gallery->album = new Album();
	$gallery->album->load($albumName);
}

if (empty($rebuild_type)) {
	$title = _("Rebuilding Thumbnails");
} else {
	$title = _("Performing Operation..");
}

switch ($cmd) {
	case 'remake-thumbnail':
		if ($gallery->user->canWriteToAlbum($gallery->album)) {
			printPopupStart($title);

			if (empty($rebuild_type)) {
				echo _("Do you also want to rebuild the thumbnails in subalbums?");
				echo makeFormIntro('do_command.php', 
					array('method' => 'post'),
					array('type' => 'popup', 'index' => $index, 'cmd' => $cmd, 
						'return' => $return, 'parentName' => $parentName));
?>
		<br>
		<input type="radio" name="rebuild_type" value="recursive"><?php echo _("yes"); ?>
		<input type="radio" name="rebuild_type" value="single" checked><?php echo _("no"); ?>
		<br><br>
		<input type="submit" value="<?php echo _("Start") ?>"><br><br>
	</form>
<?php
			}
			else {
				if (!strcmp($rebuild_type, "single")) {
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
				} else if (!strcmp($rebuild_type, "recursive")) {
					if ($gallery->session->albumName && isset($index)) {
						$gallery->album->makeThumbnailRecursive($index);
						$gallery->album->save();
						dismissAndReload();
					}
				}
			}
		}
	break;
	
	case 'logout':
		gallery_syslog("Logout by ". $gallery->session->username ." from ". $HTTP_SERVER_VARS['REMOTE_ADDR']);
		$gallery->session->username = "";
		$gallery->session->language = "";
		if (!ereg("^http|^{$gallery->app->photoAlbumURL}", $return)) {
			$return = makeGalleryHeaderUrl($return);
		}
		header("Location: $return");
	break;
	
	case 'hide':
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
	break;
		
	case 'show':
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
	    	$gallery->user->canCreateSubAlbum($gallery->album)) {
			if (!isset($parentName)) {
				$parentName = null;
			}
			createNewAlbum($parentName);
			header("Location: " . makeAlbumHeaderUrl($gallery->session->albumName));
		} else {
		        header("Location: " . makeAlbumHeaderUrl());
		}
	break;
		
	case 'reset-album-clicks':
		if ($gallery->user->canWriteToAlbum($gallery->album)) {
			$gallery->album->resetAllClicks();
			// this is a popup do dismiss and reload!
			dismissAndReload();
		} else {
       			header("Location: " . makeAlbumHeaderUrl());
		}
	break;
		
	case 'delete-comment':
		if ($gallery->user->canWriteToAlbum($gallery->album)) {
			$comment_index = getRequestVar('comment_index');
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
		<input type="button" value="<?php echo _("Dismiss") ?>" onclick='parent.close()'>
	</form>
	</div>
</div>
</body>
</html>
