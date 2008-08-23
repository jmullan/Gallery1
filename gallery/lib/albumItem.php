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

/**
 * @package	Item
 * @author	Jens Tkotz
 */

/**
 * This function shows all possible actions for an album item.
 *
 * @param   integer     $i            index number of the item
 * @param   boolean     $withIcons    Whether icons should be used, or not.
 * @param   boolean     $popupsOnly   Whether only options that result in a popup should be shown, or all
 * @param   boolean     $caption      Add a 'caption' option or not. (mostly just in dropdowns)
 * @return  array       $options      Array of all possible album item for the current user.
 * @author  Jens Tkotz
 */
function getItemActions($i, $withIcons = false, $popupsOnly = false, $caption = false) {
	global $gallery;
	global $nextId;

	static $javascriptSet;

	$id		= $gallery->album->getPhotoId($i);
	$override	= ($withIcons) ? '' : 'no';
	$options	= array();

	$isAlbum	= false;
	$isMovie	= false;
	$isPhoto	= false;

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
		$gallery->album->isItemOwner($gallery->user->getUid(), $i))
	{
		$isOwner = true;
	}

	if ($gallery->user->canWriteToAlbum($gallery->album) ||
		($gallery->album->getItemOwnerModify() && isset($isOwner)))
	{
		$canModify = true;
	}

	/* ----- User can write to album, or is owner of the item and item-owner can modify items ----- */
	if (isset($canModify)) {
		if ($isPhoto) {
			$options[] = array(
				'text'	=> gTranslate('core', "Edit Text"),
				'value'	=> showChoice2("edit_caption.php", array("index" => $i)),
				'icon'	=> ($withIcons) ? 'kcmfontinst.gif' : ''
			);

			$options[] = array(
				'text'	=> gTranslate('core', "Edit Thumbnail"),
				'value'	=> showChoice2('edit_thumb.php', array('index' => $i)),
				'icon'	=> ($withIcons) ? 'thumbnail.gif' : ''
			);

			$options[] = array(
				'text'	=> sprintf(gTranslate('core', "Rotate/Flip"), $label),
				'value'	=> showChoice2('rotate_photo.php', array('index' => $i)),
				'icon'	=> ($withIcons) ? 'reload.gif' : ''
			);

			$options[] = array(
				'text'	=> gTranslate('core', "Resize"),
				'value'	=> showChoice2('resize_photo.php', array('index' => $i)),
				'icon'	=> ($withIcons) ? 'window_fullscreen.gif' : ''
			);

			if (!empty($gallery->app->watermarkDir)) {
				$options[] = array(
					'text'	=> gTranslate('core', "Watermark"),
					'value'	=>  showChoice2('edit_watermark.php', array('index' => $i)),
					'icon'	=> ($withIcons) ? 'camera.gif' : ''
				);
			}

			if(!$popupsOnly) {
				$options[] = array(
					'text'	=> gTranslate('core', "ImageMap"),
					'value'	=> showChoice2('imagemap.php', array('index' => $i), false),
					'icon'	=> ($withIcons) ? 'behavior-capplet.gif' : ''
				);
			}
		}

		/* ----- Item is subalbum ----- */
		if ($isAlbum) {
			$options[] = array(
				'text'	=> gTranslate('core', "Edit Title"),
				'value'	=>  showChoice2('edit_field.php',
							array('set_albumName' => $myAlbum->fields['name'], 'field' => 'title')),
			);

			$options[] = array(
				'text' => gTranslate('core', "Edit Description"),
				'value'	=> showChoice2('edit_field.php',
							array('set_albumName' => $myAlbum->fields['name'], 'field' => 'description')),
				'icon'	=> ''
			);

			$options[] = array(
				'text'	=> gTranslate('core', "Rename Album"),
				'value'	=> showChoice2('rename_album.php',
							array('set_albumName' => $myAlbum->fields['name'], 'index' => $i)),
			);

			$options[] = array(
				'text'	=> gTranslate('core', "Reset Counter"),
				'value'	=> showChoice2('do_command.php',
					array(
						'cmd' 		=> 'reset-album-clicks',
						'set_albumName' => $gallery->album->getAlbumName($i),
						'return'	=> urlencode(makeGalleryUrl('view_album.php'))
					)
				)
			);

			$options[] = array(
				'text'	=> gTranslate('core', "Permissions"),
				'value'	=> showChoice2('album_permissions.php',
							array('set_albumName' => $myAlbum->fields['name'])),
			);

			// Watermarking support is enabled and user is allowed to watermark images/albums /
			if (!empty($gallery->app->watermarkDir) && $myAlbum->numPhotos(1)) {
				$options[] = array(
					'text'	=> gTranslate('core', "Watermark Album"),
					'value'	=> showChoice2('watermark_album.php',
								array('set_albumName' => $myAlbum->fields['name'])),
				);
			}

			if(! $popupsOnly) {
				if ($gallery->user->canViewComments($myAlbum) && ($myAlbum->lastCommentDate("no") != -1)) {
					$options[] = array(
						'text'	=> gTranslate('core', "View Comments"),
						'value'	=> showChoice2("view_comments.php", array("set_albumName" => $myAlbum->fields["name"]),"url"),
					);
				}
			}
		}

		if (! $isAlbum) {
			$options[] = array(
				'text' => gTranslate('core', "Copy"),
				'value'	=> showChoice2("copy_photo.php", array("index" => $i)),
				'icon'	=> ($withIcons) ? 'editcopy.gif' : ''
			);
		}
	}

	if ($gallery->user->canWriteToAlbum($gallery->album)) {
		$options[] = array(
			'text'	=> gTranslate('core', "Reorder"),
			'value'	=> showChoice2("move_photo.php", array("index" => $i, 'reorder' => 1)),
			'icon'	=> ($withIcons) ? 'tab_duplicate.gif' : ''
		);

		$options[] = array(
			'text'	=> gTranslate('core', "Move"),
			'value'	=> showChoice2("move_photo.php", array("index" => $i, 'reorder' => 0)),
			'icon'	=> ($withIcons) ? 'tab_duplicate.gif' : ''
		);

		/* ----- Item is photo, or subalbum with highlight ----- */
		if ($isPhoto || (isset($myAlbum) && $myAlbum->hasHighlight())) {
			$options[] = array(
				'text'	=> gTranslate('core', "Set as highlight"),
				'value'	=> showChoice2('do_command.php', array('cmd' => 'highlight', 'index' => $i)),
				'icon'	=> ($withIcons) ? 'viewmag1.gif' : ''
			);
		}
	}

	if (isset($isAdmin)) {
		$options[] = array(
			'text'	=> gTranslate('core', "Change Owner"),
			'value'	=> showChoice2("photo_owner.php", array("id" => $id)),
			'icon'	=> ($withIcons) ? 'yast_kuser.gif' : ''
		);
	}

	if (isset($isOwner)) {
		if ($gallery->album->isHidden($i)) {
			$options[] = array(
				'text'	=> gTranslate('core', "Unhide"),
				'value'	=> showChoice2("do_command.php", array("cmd" => "show", "index" => $i)),
				'icon'	=> ($withIcons) ? 'idea.gif' : ''
			);
		}
		else {
			$options[] = array(
				'text'	=> gTranslate('core', "Hide"),
				'value'	=> showChoice2("do_command.php", array("cmd" => "hide", "index" => $i)),
				'icon'	=> ($withIcons) ? 'no_idea.gif' : ''
			);
		}
	}

	if ($gallery->user->canDeleteFromAlbum($gallery->album) ||
	    ($gallery->album->getItemOwnerDelete() && isset($isOwner)))
	{
		if($isAlbum) {
			if($gallery->user->canDeleteAlbum($myAlbum)) {
				$options[] = array(
					'text'	=> gTranslate('core', "Delete"),
					'value'	=> showChoice2("delete_photo.php", array('index' => $i)),
					'icon'	=> ($withIcons) ? 'delete.gif' : ''
				);
			}
		}
		else {
			$options[] = array(
				'text'	=> gTranslate('core', "Delete"),
				'value'	=> showChoice2('delete_photo.php', array('index' => $i, 'nextId' => $nextId)),
				'icon'	=> ($withIcons) ? 'delete.gif' : ''
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
				'text'	=> gTranslate('core', "Photo properties"),
				'value'	=> showChoice2("view_photo_properties.php", array("index" => $i)),
				'icon'	=> ($withIcons) ? 'frame_query.gif' : '',
				'separate' => true
			);
		}


		if(isset($gallery->album->fields["ecards"]) &&
		   $gallery->album->fields["ecards"] == 'yes' &&
		   $gallery->app->emailOn == 'yes')
		{
			$options['eCard'] = array(
				'text'	=> gTranslate('core', "Send photo as eCard"),
				'value'	=> showChoice2('ecard_form.php', array('photoIndex' => $i)),
				'icon'	=> ($withIcons) ? 'ecard.gif' : '',
				'separate' => true
			);
		}
	}

	array_sort_by_fields($options, 'text');

	if(!empty($options) && $caption) {
		array_unshift($options, array(
					'text'		=> '&laquo; '. sprintf(gTranslate('core', "%s actions"), $label) . ' &raquo;',
					'selected'	=> true
		));
	}

	return $options;
}

/**
 * Returns a HTML with all comments of an album item.
 *
 * @param integer    $index        itemindex
 * @param string     $albumName    Name of the album containing the item
 * @param boolean    $reverse      Wether to show in reverse order or not
 * @return string                  A rendered HTML Table that contains the comments.
 * @author Jens Tkotz
 */
function showComments ($index, $albumName, $reverse = false) {
	global $gallery;

	$numComments	= $gallery->album->numComments($index);
	$delCommentText	= getIconText('delete.gif', gTranslate('core', "Delete comment"), 'yes');

	$commentdraw['index'] = $index;

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
			'content'	=> gTranslate('core', "From:"),
			'cellArgs'	=> array('class' => 'admin', 'width' => 50, 'height' => '25')));

		$commentTable->addElement(array(
			'content'	=> $commenterName,
			'cellArgs'	=> array('class' => 'admin', 'width' => '55%')));

		$commentTable->addElement(array(
			'content'	=> '('. $comment->getDatePosted() .')',
			'cellArgs'	=> array('class' => 'admin')));

		if ($gallery->user->canWriteToAlbum($gallery->album)) {
			$url = doCommand('delete-comment', array(
								'index'		=> $index,
								'comment_index'	=> $nr,
								'albumName'	=> $albumName)
			);

			$commentTable->addElement(array(
				'content' => '<a href="#" onclick="javascript:' . popup($url,1) . '">'. $delCommentText .' </a>')
			);
		}

		$commentTable->addElement(array(
			'content'	=> wordwrap($comment->getCommentText(), 100, " ", 1),
			'cellArgs'	=> array(
						'colspan'	=> $columns,
						'style'		=> 'padding-left:10px; border-top:1px solid black',
						'class'		=> 'albumdesc'))
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
 * @param integer     $currentId
 * @return integer    $nextId			;-)
 */
function getNextId($currentId) {
	global $gallery;

	$allIds	 = $gallery->album->getIds($gallery->user->canWriteToAlbum($gallery->album));
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

/**
 * What should the caption be, if no caption was given by user ?
 * See captionOptions.inc.php for options
 *
 * @param intger    $captionType
 * @param string    $originalFilename
 * @param string    $filename
 * @return string   $caption
 * @author Jens Tkotz
 */
function generateCaption($captionType = 1, $originalFilename, $filename) {
	global $gallery;

	if (isset($gallery->app->dateTimeString)) {
		$dateTimeFormat = $gallery->app->dateTimeString;
	}
	else {
		$dateTimeFormat = "%D %T";
	}

	switch ($captionType) {
		case 0:
			$caption		= '';
			$captionTypeString	= 'no caption';
			break;

		case 1:
		default:
			/* Use filename */
			$caption		= strtr($originalFilename, '_', ' ');
			$captionTypeString	= 'filename';
			break;

		case 2:
			/* Use file creation date */
			$caption		= strftime($dateTimeFormat, filectime($filename));
			$captionTypeString	= 'file creation date';
			break;

		case 3:
			/* Use capture date */
			$caption		= strftime($dateTimeFormat, getItemCaptureDate($filename));
			$captionTypeString	= 'file capture date';
			break;
	}

	echo debugMessage(sprintf(gTranslate('core', "Generated caption. Type: %s"), $captionTypeString), __FILE__, __LINE__, 1);

	return $caption;
}

/**
 * Processes an image and adds it to $gallery->album
 *
 * @param string    $file
 * @param string    $ext
 * @param string    $name
 * @param string    $caption
 * @param integer   $setCaption
 * @param array     $extra_fields
 * @param string    $wmName
 * @param integer   $wmAlign
 * @param integer   $wmAlignX
 * @param integer   $wmAlignY
 * @param integer   $wmSelect
 * @return string                       Empty on success, otherwise an errormessage.
 */
function processNewImage($file, $ext, $name, $caption, $setCaption = '', $extra_fields = array(), $wmName = '', $wmAlign = 0, $wmAlignX = 0, $wmAlignY = 0, $wmSelect = 0) {
	global $gallery;
	global $temp_files;

	$error = '';

	echo debugMessage(sprintf(gTranslate('core', "Entering function: '%s'"), __FUNCTION__), __FILE__, __LINE__, 3);

	echo debugMessage(sprintf(gTranslate('core', "Processing file: %s"), $file), __FILE__, __LINE__,3);

	/* Begin of code for the case the uploaded file is an archive */
	if (isAcceptableArchive($ext)) {
		processingMsg(sprintf(gTranslate('core', "Processing file '%s' as archive"), $name));
		$tool = canDecompressArchive($ext);
		if (!$tool) {
			$error = sprintf(gTranslate('core', "Skipping '%s' (%s support not enabled)"), $name, $ext);
			return $error;
		}

		$temp_filename	= tempnam($gallery->app->tmpDir, 'g1_tmp_');
		$temp_dirname	= $temp_filename . '.dir';

		if (fs_is_dir($temp_dirname)) {
			$error = gTranslate('core', "Error occurred before extracting the archive. Temporary destination exists.");
			return $error;
		}

		if (! fs_mkdir($temp_dirname)) {
			$error = gTranslate('core', "Error occurred before extracting the archive. Temporary destination could not be created.");
			return $error;
		}

		processingMsg(gTranslate('core', "Extracting archive"));
		if(! extractArchive($file, $ext, $temp_dirname)) {
			$error = gTranslate('core', "Extracting archive failed.");
			return $error;
		}

		echo debugMessage(gTranslate('core', "Processing archive content."), __FILE__, __LINE__);
		$files_to_process	= array();
		$dir_handle		= fs_opendir($temp_dirname);
		while (false !== ($content_filename = readdir($dir_handle))) {
			if(! isXSSclean($content_filename) ||
			   $content_filename == "." || $content_filename == '..')
			{
				continue;
			}

			$content_file_ext	= getExtension($content_filename);
			$fullpath_content_file	= $temp_dirname .'/' . $content_filename;

			if ($content_file_ext == 'csv') {
				$image_info = array_merge($image_info, parse_csv($fullpath_content_file, ';'));
			}
			elseif (isAcceptableFormat($content_file_ext) ||
				isAcceptableArchive($content_file_ext))
			{
				$files_to_process[] = array(
					'filename'	=> $fullpath_content_file,
					'ext'		=> $content_file_ext
				);
			}
		}

		closedir($dir_handle);

		if(!empty($image_info)) {
			debugMessage(printMetaData($image_info), __FILE__, __LINE__);
		}
		else {
			echo debugMessage(gTranslate('core', "No Metadata"), __FILE__, __LINE__);
		}

		/* Now process all valid files we found */
		echo debugMessage(gTranslate('core', "Processing valid files from archive"), __FILE__, __LINE__);
		$loop = 0;
		foreach ($files_to_process as $current_file) {
			$current_file_name = basename($current_file['filename']);
			$current_file_ext  = basename($current_file['ext']);

			/* Now process the metadata. */
			$extra_fields = array();
			if(! empty($image_info)) {
				/* Find in meta data array */
				$firstRow	= 1;
				$fileNameKey	= 'File Name';

				/* $captionMetaFields will store the names (in order of priority to set caption to) */
				$captionMetaFields = array("Caption", "Title", "Description", "Persons");
				foreach ($image_info as $info ) {
					if ($firstRow) {
						/* Find the name of the file name field */
						foreach (array_keys($info) as $currKey) {
							if (eregi("^\"?file\ ?name\"?$", $currKey)) {
								$fileNameKey = $currKey;
							}
						}
						$firstRow = 0;
					}

					if ($info[$fileNameKey] == $current_file_name) {
						/* Loop through fields */
						foreach ($captionMetaFields as $field) {
							/* If caption isn't populated and current field is */
							if (!strlen($caption) && strlen($info[$field])) {
								$caption = $info[$field];
							}
						}

						$extra_fields = $info;
					}
				}
			}

			processNewImage($current_file['filename'],
							$current_file_ext,
							$current_file_name,
							$caption,
							$setCaption,
							$extra_fields,
							$wmName, $wmAlign, $wmAlignX, $wmAlignY, $wmSelect
			);
		}
		/* End of archive processing */
		rmdirRecursive($temp_dirname);
		fs_unlink($temp_filename);
	}
	else {
		echo debugMessage(gTranslate('core', "Start processing single file (image/movie not archive)."), __FILE__, __LINE__);

		if (isAcceptableFormat($ext)) {
			echo debugMessage(gTranslate('core', "Extension is accepted."), __FILE__, __LINE__, 3);

			echo debugMessage(gTranslate('core', "Filename processing."), __FILE__, __LINE__,3);

			/* Remove %20 and the like from name */
			$name = urldecode($name);

			/* parse out original filename without extension */
			$originalFilename = eregi_replace(".$ext$", "", $name);

			/* replace multiple non-word characters with a single "_" */
			$mangledFilename = ereg_replace("[^[:alnum:]]", "_", $originalFilename);

			/* Get rid of extra underscores */
			$mangledFilename = ereg_replace("_+", "_", $mangledFilename);
			$mangledFilename = ereg_replace("(^_|_$)", "", $mangledFilename);

			if (empty($mangledFilename)) {
				$mangledFilename = $gallery->album->newPhotoName();
			}

			/*
			* need to prevent users from using original filenames that are purely numeric.
			* Purely numeric filenames mess up the rewriterules that we use for mod_rewrite
			* specifically:
			* RewriteRule ^([^\.\?/]+)/([0-9]+)$	/~jpk/gallery/view_photo.php?set_albumName=$1&index=$2	[QSA]
			*/

			if (ereg("^([0-9]+)$", $mangledFilename)) {
				$mangledFilename .= "_G";
			}

			/*
			 * Move the uploaded image to our temporary directory
			 * using move_uploaded_file so that we work around
			 * issues with the open_basedir restriction.
			 */
			if (function_exists('move_uploaded_file')) {
				$newFile = tempnam($gallery->app->tmpDir, "gallery");
				if (move_uploaded_file($file, $newFile)) {
					$file = $newFile;
				}

				/* Make sure we remove this file when we're done */
				$temp_files[$newFile] = 1;
			}

			/* What should the caption be, if no caption was given by user ?
			 * See captionOptions.inc.php for options
			 */
			if (empty($caption)) {
				echo debugMessage(gTranslate('core', "No caption given, generating it."), __FILE__, __LINE__, 1);
				$caption = generateCaption($setCaption, $originalFilename, $file);
			}

			echo infobox(array(array(
				'type' => 'information',
				'text' => '<b>'. sprintf(gTranslate('core', "Adding %s"), $name) .'</b>'
			)));

			/* After all the preprocessing, NOW ADD THE element */
			set_time_limit($gallery->app->timeLimit);

			/*
			 * function addPhoto($file, $tag, $originalFilename, $caption, $pathToThumb="", $extraFields=array(), $owner="", $votes=NULL,
			 *				     $wmName="", $wmAlign=0, $wmAlignX=0, $wmAlignY=0, $wmSelect=0)
		 	*/
			list($status, $statusMsg) = $gallery->album->addPhoto(
									$file,
									$ext,
									$mangledFilename,
									$caption,
									'',
									$extra_fields,
									$gallery->user->uid,
									NULL,
									$wmName, $wmAlign, $wmAlignX, $wmAlignY, $wmSelect
			);

			echo $statusMsg;

			if (! $status) {
				$error = gTranslate('core',"Adding item failed. Please enable debug mode and redo the procedure to get more information.");
				echo gallery_error($error);
				processingMsg('<b>' .
					sprintf(gTranslate('core', "Need more help?  Look in the  %s%s FAQ%s."),
					'<a href="http://gallery.sourceforge.net/faq.php" target=_new>',
					Gallery(),
					'</a>') .
					'</b>'
				);
			}
		}
		else {
			$error = sprintf(gTranslate('core', "Skipping '%s' (can't handle '%s' format)"), $name, $ext);
			echo gallery_error($error);
		}
	}

	return $error;
}

?>
