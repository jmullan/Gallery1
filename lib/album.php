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
 * @package	Album
 * @author	Jens Tkotz
 */

/**
 * Returns the default value for an album property. Either for a specific album, or global
 * @param   string $property
 * @param   object $album
 * @param   boolean $global
 * @return  mixed $retPoperty
 * @author  Jens Tkotz
 */
function getPropertyDefault($property, $album = false, $global = false) {
	global $gallery;

	$retProperty = false;

	if ($album) {
		if ($global) {
			if(isset($gallery->app->default[$property])) {
				$retProperty = $gallery->app->default[$property];
			}
		}
		else {
			if(isset($album->fields[$property])) {
				$retProperty = $album->fields[$property];
			}
		}
	}
	elseif ($global) {
		$retProperty = $gallery->app->$property;
	}

	return $retProperty;
}

/**
 * Creates a new album
 *
 * @param string $parentName
 * @param string $newAlbumName
 * @param string $newAlbumTitle
 * @param string $newAlbumDesc
 * @return mixed
 */
function createNewAlbum($parentName, $newAlbumName = '', $newAlbumTitle = '', $newAlbumDesc = '') {
	global $gallery;

	// get parent album name
	$albumDB = new AlbumDB(false);

	// set new album name from param or default
	$gallery->session->albumName = $albumDB->newAlbumName($newAlbumName);

	$gallery->album = new Album();
	$gallery->album->fields["name"] = $gallery->session->albumName;

	// guid is not created during new Album() as a performance optimization
	// it only needs to be created when an album is created or modified by adding or deleting photos
	$gallery->album->fields['guid'] = genGUID();

	// set title and description
	if (!empty($newAlbumTitle)) {
		$gallery->album->fields["title"] = $newAlbumTitle;
	}
	if (!empty($newAlbumDesc)) {
		$gallery->album->fields["description"] = $newAlbumDesc;
	}

	$gallery->album->setOwner($gallery->user->getUid());
	$gallery->album->fields['creation_date']  = time();

	/* if this is a nested album, set nested parameters */
	if (!empty($parentName)) {
		$gallery->album->fields['parentAlbumName'] = $parentName;
		$parentAlbum = $albumDB->getAlbumByName($parentName);
		$parentAlbum->addNestedAlbum($gallery->session->albumName);
		$parentAlbum->save(array(i18n("Album \"{$gallery->album->fields['name']}\" created as a sub-album of \"$parentName\".")));
		// Set default values in nested album to match settings of parent.
		$gallery->album->fields["perms"]		= $parentAlbum->fields["perms"];
		$gallery->album->fields['extra_fields']		= $parentAlbum->fields['extra_fields'];
		$gallery->album->fields["bgcolor"]		= $parentAlbum->fields["bgcolor"];
		$gallery->album->fields["textcolor"]		= $parentAlbum->fields["textcolor"];
		$gallery->album->fields["linkcolor"]		= $parentAlbum->fields["linkcolor"];
		$gallery->album->fields['background']		= $parentAlbum->fields['background'];
		$gallery->album->fields["font"]			= $parentAlbum->fields["font"];
		$gallery->album->fields["border"]		= $parentAlbum->fields["border"];
		$gallery->album->fields["bordercolor"]		= $parentAlbum->fields["bordercolor"];
		$gallery->album->fields["thumb_size"]		= $parentAlbum->fields["thumb_size"];
		$gallery->album->fields["resize_size"]		= $parentAlbum->fields["resize_size"];
		$gallery->album->fields["resize_file_size"]	= $parentAlbum->fields["resize_file_size"];
		$gallery->album->fields['max_size']		= $parentAlbum->fields['max_size'];
		$gallery->album->fields['max_file_size']	= $parentAlbum->fields['max_file_size'];
		$gallery->album->fields['returnto']		= $parentAlbum->fields['returnto'];
		$gallery->album->fields["rows"]			= $parentAlbum->fields["rows"];
		$gallery->album->fields["cols"]			= $parentAlbum->fields["cols"];
		$gallery->album->fields["fit_to_window"]	= $parentAlbum->fields["fit_to_window"];
		$gallery->album->fields["use_fullOnly"]		= $parentAlbum->fields["use_fullOnly"];
		$gallery->album->fields["print_photos"]		= $parentAlbum->fields["print_photos"];
		$gallery->album->fields['slideshow_type']	= $parentAlbum->fields['slideshow_type'];
		$gallery->album->fields['slideshow_recursive']	= $parentAlbum->fields['slideshow_recursive'];
		$gallery->album->fields['slideshow_length']	= $parentAlbum->fields['slideshow_length'];
		$gallery->album->fields['slideshow_loop']	= $parentAlbum->fields['slideshow_loop'];
		$gallery->album->fields['album_frame']		= $parentAlbum->fields['album_frame'];
		$gallery->album->fields['thumb_frame']		= $parentAlbum->fields['thumb_frame'];
		$gallery->album->fields['image_frame']		= $parentAlbum->fields['image_frame'];
		$gallery->album->fields["use_exif"]		= $parentAlbum->fields["use_exif"];
		$gallery->album->fields["display_clicks"]	= $parentAlbum->fields["display_clicks"];
		$gallery->album->fields["item_owner_display"]	= $parentAlbum->fields["item_owner_display"];
		$gallery->album->fields["item_owner_modify"]	= $parentAlbum->fields["item_owner_modify"];
		$gallery->album->fields["item_owner_delete"]	= $parentAlbum->fields["item_owner_delete"];
		$gallery->album->fields["add_to_beginning"]	= $parentAlbum->fields["add_to_beginning"];
		$gallery->album->fields['showDimensions']	= $parentAlbum->fields['showDimensions'];

		$returnVal = $gallery->album->save(array(i18n("Album \"{$gallery->album->fields['name']}\" created as a sub-album of \"$parentName\".")));
	}
	else {
		$gallery->album->save(array(i18n("Root album \"{$gallery->album->fields['name']}\" created.")));
		/*
		* Get a new albumDB because our old copy is not up to
		* date after we created a new album
		*/
		$albumDB = new AlbumDB(false);

		/* move the album to the top if not a nested album*/
		$numAlbums = $albumDB->numAlbums($gallery->user);
		$albumDB->moveAlbum($gallery->user, $numAlbums, 1);
		$returnVal = $albumDB->save();
	}

	if (!empty($returnVal)) {
		return $gallery->session->albumName;
	}
	else {
		return 0;
	}
}

/**
 * Returns an array of the parent album names for a given child
 * album.
 * Array is reverted, so the first Element is the topalbum.
 * If you set $addChild true, then the child album itself is added as last Element.
 * Based on code by: Dariush Molavi
 */
function getParentAlbums($childAlbum, $addChild = false) {
	$pAlbum = $childAlbum;
	$parentNameArray = array();

	if ($addChild == true) {
		$parentNameArray[$pAlbum->fields['name']] = $pAlbum->fields['title'];
	}

	while ($pAlbum = $pAlbum->getParentAlbum(FALSE)) {
		$parentNameArray[$pAlbum->fields['name']] = $pAlbum->fields['title'];
	}

	$parentNameArray = array_reverse($parentNameArray);

	return $parentNameArray;
}

/**
 * This function just displays the prefetching navigation for an the mainpages
 *
 */
function prefetchRootAlbumNav() {
	global $gallery, $navigator, $maxPages;

	if ($navigator['page'] > 1) {
?>
  <link rel="top" href="<?php echo makeGalleryUrl('albums.php', array('set_albumListPage' => 1)) ?>">
  <link rel="first" href="<?php echo makeGalleryUrl('albums.php', array('set_albumListPage' => 1)) ?>">
  <link rel="prev" href="<?php echo makeGalleryUrl('albums.php', array('set_albumListPage' => $navigator['page']-1)) ?>">
<?php
	}

	if ($navigator['page'] < $maxPages) {
?>
  <link rel="next" href="<?php echo makeGalleryUrl('albums.php', array('set_albumListPage' => $navigator['page']+1)) ?>">
  <link rel="last" href="<?php echo makeGalleryUrl('albums.php', array('set_albumListPage' => $maxPages)) ?>">
<?php
	}
}

/**
 * This function just displays the RSS Link on the mainpages
 */
function rootRSSLink() {
	global $gallery, $galleryTitle;

	if ($gallery->app->rssEnabled == "yes" && !$gallery->session->offline) {
		$rssTitle = sprintf(gTranslate('common', "%s RSS"), $galleryTitle);
		$rssHref = $gallery->app->photoAlbumURL . "/rss.php";

		echo "  <link rel=\"alternate\" title=\"$rssTitle\" href=\"$rssHref\" type=\"application/rss+xml\">\n";
	}
}

/**
 * This function just displays the prefetching navigation for the albumpages
 */
function prefetchAlbumNav() {
	global $gallery;
	global $first, $previousPage, $last, $nextPage, $maxPages;

	/* prefetching/navigation */
	if (!isset($first)) { ?>
  <link rel="first" href="<?php echo makeAlbumUrl($gallery->session->albumName, '', array('page' => 1)) ?>" >
  <link rel="prev" href="<?php echo makeAlbumUrl($gallery->session->albumName, '', array('page' => $previousPage)) ?>" >
<?php
	}
	if (!isset($last)) { ?>
  <link rel="next" href="<?php echo makeAlbumUrl($gallery->session->albumName, '', array('page' => $nextPage)) ?>" >
  <link rel="last" href="<?php echo makeAlbumUrl($gallery->session->albumName, '', array('page' => $maxPages)) ?>" >
<?php }

	if ($gallery->album->isRoot() &&
		(!$gallery->session->offline ||
		isset($gallery->session->offlineAlbums["albums.php"]))) { ?>
  <link rel="up" href="<?php echo makeAlbumUrl(); ?>" >
<?php
	}
	else if (!$gallery->session->offline ||
			 isset($gallery->session->offlineAlbums[$pAlbum->fields['parentAlbumName']])) { ?>
  <link rel="up" href="<?php echo makeAlbumUrl($gallery->album->fields['parentAlbumName']); ?>" >
<?php
	}
	if (!$gallery->session->offline ||
		isset($gallery->session->offlineAlbums["albums.php"])) { ?>
  <link rel="top" href="<?php echo makeGalleryUrl('albums.php', array('set_albumListPage' => 1)) ?>" >
<?php
	}
}

/**
 * This function just displays the RSS Link on the albumpages
 *
 */
function albumRSSLink() {
	global $gallery, $albumTitle, $albumRSSURL;

	if ($gallery->app->rssEnabled == 'yes' && !$gallery->session->offline) {
		$rssTitle = htmlentities(sprintf(gTranslate('common', "%s RSS"), $albumTitle));

		echo "  <link rel=\"alternate\" title=\"$rssTitle\" href=\"$albumRSSURL\" type=\"application/rss+xml\">\n";
	}
}

/**
 * This function returns the CSS for the settings a user did in the album appearance
 *
 */
function customCSS() {
	global $gallery;

	$customCSS = '';

	// the link colors have to be done here to override the style sheet
	if ($gallery->album->fields["linkcolor"]) {
		$customCSS .= "  a:link, a:visited, a:active { color: ".$gallery->album->fields['linkcolor'] ."; }\n";
		$customCSS .= "  a:hover { color: #ff6600; }\n";
	}

	if ($gallery->album->fields["bgcolor"]) {
		$customCSS .= "  body { background-color:".$gallery->album->fields['bgcolor']."; }\n";
	}

	if (isset($gallery->album->fields['background']) && $gallery->album->fields['background']) {
		$customCSS .= "  body { background-image:url(".$gallery->album->fields['background']."); }\n";
	}

	if ($gallery->album->fields["textcolor"]) {
		$customCSS .= "  body, tf { color:".$gallery->album->fields['textcolor']."; } \n";
	}

	return $customCSS;
}

/**
 * returns the a HTML string containg links to the upper albums
 *
 * @param object  $album
 * @param boolean $withCurrentAlbum
 * @return string $pathArray
 */
function returnToPathArray($album = NULL, $withCurrentAlbum = true, $photoview = false) {
	global $gallery;

	$pathArray = array();

	$upArrowAltText = gTranslate('common', "navigate _UP");
	$upArrow = gImage('icons/navigation/nav_home.gif', $upArrowAltText);

	$accesskey = getAccessKey($upArrowAltText);
	$lastUpArrowAltText = $upArrowAltText . ' '.
	   sprintf(gTranslate('common', "(accesskey '%s')"), $accesskey);

	$lastUpArrow = gImage('icons/navigation/nav_home.gif', $lastUpArrowAltText);

	if (!empty($album)) {
		if ($album->fields['returnto'] != 'no') {
			$parents = $album->getParentAlbums($withCurrentAlbum);
			$numParents = sizeof($parents);
			$i = 0;
			foreach ($parents as $navAlbum) {
				$i++;
				$link = $navAlbum['prefixText'] .': ';
				if($i == $numParents) {
					$link .= galleryLink($navAlbum['url'], $navAlbum['title'] ."&nbsp;$lastUpArrow",
						array('accesskey' => $accesskey), '', false, false);
				}
				else {
					$link .= galleryLink($navAlbum['url'], $navAlbum['title'] ."&nbsp;$upArrow",
						array(), '', false, false);
				}
				$pathArray[] = $link;
			}
		}
		elseif ($photoview) {
			$pathArray[] = galleryLink(
						makeAlbumUrl($gallery->album->fields['name']),
						$gallery->album->fields['title'] ."&nbsp;$lastUpArrow",
						array('accesskey' => $accesskey), '', false, false
			);
		}
	}
	else {
		$pathArray[] = sprintf(
			gTranslate('common', "Gallery: %s"),
			galleryLink(
				makeGalleryUrl("albums.php"),
				clearGalleryTitle() ."&nbsp;$lastUpArrow",
				array('accesskey' => $accesskey), '', false, false)
		);
	}

	return $pathArray;
}

/**
 * Generates an array that represents an album tree.
 * Each element has this structure:
 * $tree[] = array(
		'albumUrl' => $albumUrl,
		'albumName' => $myName,
		'title' => $title,
		'clicksText' => $clicksText,
		'microthumb' => $microthumb,
		'subTree' => $subtree
   );
 *
 * This function is recursive, so the subtree is again a tree.
 * @param string    $albumName
 * @param integer   $depth	Maximum depth of the tree
 * @return array    $tree	Structure like described above
 * @author Jens Tkotz
 */
function createTreeArray($albumName,$depth = 0) {
	global $gallery;

	$myAlbum = new Album();
	$myAlbum->load($albumName);
	$numPhotos = $myAlbum->numPhotos(1);

	$tree = array();

	if ($depth >= $gallery->app->albumTreeDepth) {
		return $tree;
	}

	for ($i = 1; $i <= $numPhotos; $i++) {
		set_time_limit($gallery->app->timeLimit);
		if ($myAlbum->isAlbum($i) && !$myAlbum->isHidden($i)) {
			$myName = $myAlbum->getAlbumName($i, false);
			$nestedAlbum = new Album();
			$nestedAlbum->load($myName);
			if ($gallery->user->canReadAlbum($nestedAlbum)) {
				$title = $nestedAlbum->fields['title'];
				if (!strcmp($nestedAlbum->fields['display_clicks'], 'yes')
					&& !$gallery->session->offline)
				{
					$clicksText = "(" . gTranslate('common', "1 view", "%d views", $nestedAlbum->getClicks(), '', true) . ")";
				}
				else {
					$clicksText = '';
				}

				$albumUrl = makeAlbumUrl($myName);
				$subtree = createTreeArray($myName, $depth+1);

				$highlightTag = $nestedAlbum->getHighlightTag(
					$gallery->app->default["nav_thumbs_size"],
					array('class' => 'nav_micro_img', 'alt' => "$title $clicksText")
				);

				$microthumb = "<a href=\"$albumUrl\">$highlightTag</a> ";
				$tree[] = array(
					'albumUrl' => $albumUrl,
					'albumName' => $myName,
					'title' => $title,
					'clicksText' => $clicksText,
					'microthumb' => $microthumb,
					'subTree' => $subtree
				);
			}
		}
	}

	return $tree;
}


/**
 * Test Suite for albums
 *
 * @param string $test
 * @return boolean
 * @author Beckett Madden-Woods
 */
function testRequirement($test) {
	global $gallery;

	if(substr($test, 0,1 ) == "!") {
		$test = substr($test, 1);
		$negativeTest = true;
	}
	else {
		$negativeTest = false;
	}

	switch ($test) {
		case 'albumIsRoot':
			$result = $gallery->album->isRoot();
		break;

		case 'isAdminOrAlbumOwner':
			$result = $gallery->user->isAdmin() || $gallery->user->isOwnerOfAlbum($gallery->album);
		break;

		case 'comments_enabled':
			$result = $gallery->app->comments_enabled == 'yes';
		break;

		case 'allowComments':
			$result = $gallery->album->fields["perms"]['canAddComments'];
		break;

		case 'hasComments':
			$result = ($gallery->album->lastCommentDate("no") != -1);
		break;

		case 'canAddToAlbum':
			$result = $gallery->user->canAddToAlbum($gallery->album);
		break;

		case 'canDeleteAlbum':
			$result = $gallery->user->canDeleteAlbum($gallery->album);
		break;

		case 'extraFieldsExist':
			$extraFields = $gallery->album->getExtraFields();
			$result = !empty($extraFields);
		break;

		case 'isAlbumOwner':
			$result = $gallery->user->isOwnerOfAlbum($gallery->album);
		break;

		case 'canCreateSubAlbum':
			$result = $gallery->user->canCreateSubAlbum($gallery->album);
		break;

		case 'notOffline':
			$result = !$gallery->session->offline;
		break;

		case 'canChangeText':
			$result = $gallery->user->canChangeTextOfAlbum($gallery->album);
		break;

		case 'canWriteToAlbum':
			$result = $gallery->user->canWriteToAlbum($gallery->album);
		break;

		case 'photosExist':
			$result = $gallery->album->numPhotos(true);
		break;

		case 'watermarkingEnabled':
			$result = isset($gallery->app->watermarkDir);
		break;

		case 'exif':
			$result = (getExifDisplayTool() !== false);
		break;

		case 'votingOn':
			$result = ($gallery->album->getVoterClass()) != 'Nobody';
		break;

		default:
			$result = false;
		break;
	}

	if ($negativeTest) {
		$result = ! $result;
	}

	return $result;
}

/**
 * Checks whether a requirement is set.
 *
 * @return unknown
 */
function checkRequirements() {
	$requirementList = func_get_args();

	$enabled = true;

	while ($enabled && $test = array_shift($requirementList)) {
		$success = testRequirement($test);
		$enabled = ($success) ? true : false;
	}

	return $enabled;
}

/**
 * Returns an array with all album options for an user
 *
 * @param object $album
 * @param boolean $caption
 * @param boolean $mainpage
 * @return array $albumCommands
 */
function getAlbumCommands($album, $caption = false, $mainpage = true) {
	global $i;
	global $page, $perPage;

	$id = $album->fields['name'];
	$albumCommands = array();

/*
	global $gallery;
	if (!$gallery->session->offline) {
		return $albumCommands;
	}
*/

	$albumName = $album->fields["name"];

	/* Commands shown for all albums */
	if(checkRequirements('canAddToAlbum')) {
		$albumCommands[] = array(
			'text'	=> gTranslate('common', "Add photos"),
			'html'	=> popup_link(gTranslate('common', "Add photos"),
						"add_photos_frame.php?set_albumName=$albumName",
				   		false, true, 550, 600, '', '', 'new.png'
				   ),
			'value'	=> build_popup_url("add_photos_frame.php?set_albumName=$albumName")
		);
	}

	if (checkRequirements('canCreateSubAlbum')) {
		$albumCommands[] = array(
			'class'	=> 'url',
			'text'	=> gTranslate('common', "New nested album"),
			'html'	=> galleryLink(
						doCommand('new-album',
							array('parentName' => $albumName),
							'view_album.php'),
						gTranslate('common', "New nested album"),
						array(), 'folder_new.png', true),
			'value'	=> doCommand('new-album',
						array('parentName' => $albumName),
						'view_album.php')
		);
	}

	/* User ist allowed to change album captions */
	/* Should this be into the above group ? */
	if (checkRequirements('canChangeText')) {
		$albumCommands[] = array(
			'class'	=> 'url',
			'text'	=> gTranslate('common',"Edit captions"),
			'html'	=> galleryLink(
					makeGalleryUrl(
						'captionator.php',
						array(
							'set_albumName'=> $albumName,
							'page' => $page,
							'perPage' => $perPage)),
						gTranslate('common',"Edit captions"),
						array(), 'kcmfontinst.gif', true),
			'value'	=> makeGalleryUrl("captionator.php", array("set_albumName" => $albumName))
		);
	}


	/* User is Admin or Owner */
	if (checkRequirements('isAdminOrAlbumOwner')) {
		$albumCommands[] = array(
			'text'	=> gTranslate('common', "Change foldername"),
			'html'	=> popup_link(gTranslate('common',"Change foldername"),
						"rename_album.php?set_albumName={$albumName}&useLoad=true",
						false, true, 550, 600, '', '', 'folder_edit.png'
				   ),
			'value'	=> build_popup_url("rename_album.php?set_albumName={$albumName}&useLoad=true")
		);

		/* User is allowed to change album permissions */
		$albumCommands[] = array(
			'text'	=> gTranslate('common', "Permissions"),
			'html'	=> popup_link(gTranslate('common',"Permissions"),
						"album_permissions.php?set_albumName={$albumName}",
						false, true, 550, 700, '', '', 'key.png'
				   ),
			'value' => build_popup_url("album_permissions.php?set_albumName=$albumName")
		);

		/* And to change album properties */
		$albumCommands[] = array(
			'text'	=> gTranslate('common',"Properties"),
			'html'	=> popup_link(gTranslate('common',"Properties"),
						"edit_appearance.php?set_albumName={$albumName}",
						false, true, 700, 800, '', '', 'options.png'
				   ),
			'value'	=> build_popup_url("edit_appearance.php?set_albumName=$albumName")
		);


		/* Watermarking support is enabled and user is allowed to watermark images/albums */
		if (checkRequirements('photosExist','watermarkingEnabled')) {
			$albumCommands[] = array(
				'text'	=> gTranslate('common',"Watermark&nbsp;album"),
				'html'	=> popup_link(gTranslate('common',"Watermark&nbsp;album"),
							"watermark_album.php?set_albumName=$albumName",
							false, true, 550, 600, '', '', 'camera.gif'
					   ),
				'value'	=> build_popup_url("watermark_album.php?set_albumName=$albumName")
			);
		}
	}


	/* Options only shown for root albums */
	if (checkRequirements('albumIsRoot')) {
		/* User is allowed to delete the album */
		if (checkRequirements('canDeleteAlbum')) {
			$albumCommands[] = array(
				'text'	=> gTranslate('common',"Delete album"),
				'html'	=> popup_link(gTranslate('common',"Delete album"),
							"delete_album.php?set_albumName=$albumName",
							false, true, 550, 600, '', '', 'delete.gif'
					   ),
				'value'	=> build_popup_url("delete_album.php?set_albumName=$albumName")
			);
		}

		/* User is allowed to change the album */
		if (checkRequirements('canWriteToAlbum')) {
			$albumCommands[] = array(
				'text'	=> gTranslate('common',"Move album"),
				'html'	=> popup_link(gTranslate('common',"Move album"),
							"move_album.php?set_albumName={$albumName}&index=$i&reorder=0",
							false, true, 550, 600, '', '', 'move.png'
					    ),
				'value'	=> build_popup_url("move_album.php?set_albumName={$albumName}&index=$i&reorder=0")
			);

			$albumCommands[] = array(
				'text'	=> gTranslate('common',"Reorder album"),
				'html'	=> popup_link(gTranslate('common',"Reorder album"),
							"move_album.php?set_albumName={$albumName}&index=$i&reorder=1",
							false, true, 550, 600, '', '', 'move.png'
					   ),
				'value'	=> build_popup_url("move_album.php?set_albumName={$albumName}&index=$i&reorder=1")
			);
		}
	}
	/* Options that are only shown for subalbums */
	else {
		/* User is allowed to delete the subalbum */
		if (checkRequirements('canDeleteAlbum')) {
			$albumCommands[] = array(
				'text'	=> gTranslate('common',"Delete this (sub)album"),
				'html'	=> popup_link2(
							gTranslate('common', "Delete this (sub)album"),
							makeGalleryUrl('delete_photo.php',
								array(
									'set_albumName'	=> $album->fields['parentAlbumName'],
									'index' => $i,
									'id' => $id,
									'gallery_popup' => true)),
							array('accesskey' => true, 'icon' => 'delete.gif')),
				'value'	=> build_popup_url(makeGalleryUrl('delete_photo.php',
								array(
									'set_albumName' => $album->fields['parentAlbumName'],
									'index' => $i,
									'id' => $id,
									'gallery_popup' => true)),
								true)
			);
		}
	}

	/* Options shown only in thumbsview */
	if(!$mainpage) {
		if (checkRequirements('canWriteToAlbum', 'photosExist')) {
			$albumCommands[] = array(
				'text'	=> gTranslate('common', "Sort items"),
				'html'	=> popup_link(gTranslate('common',"Sort items"),
								 "sort_album.php?set_albumName=$albumName",
								 false, true, 550, 600, '', '', 'puzzle.png'
					   ),
				'value'	=> build_popup_url("sort_album.php?set_albumName=$albumName")
			);

			$albumCommands[] = array(
				'text'	=> gTranslate('common', "Resize all"),
				'html'	=> popup_link(gTranslate('common',"Resize all"),
						      "resize_photo.php?set_albumName={$albumName}&index=0",
						      false, true, 550, 600, '', '', 'window_fullscreen.gif'
					   ),
				'value'	=> build_popup_url("resize_photo.php?set_albumName={$albumName}&index=0")
			);

			$albumCommands[] = array(
				'text'	=> gTranslate('common', "Recreate captions"),
				'html'	=> popup_link(gTranslate('common',"Recreate captions"),
						      "recreate_captions.php?set_albumName={$albumName}",
						      false, true, 550, 600, '', '', 'refresh.png'
					   ),
				'value'	=> build_popup_url("recreate_captions.php?set_albumName={$albumName}")
			);

			$albumCommands[] = array(
				'text'	=> gTranslate('common', "Rebuild thumbs"),
				'html'	=> popup_link(gTranslate('common',"Rebuild thumbs"),
						      "rebuild_thumbs.php?set_albumName={$albumName}",
						      false, true, 550, 600, '', '', 'paint.png'
					   ),
				'value'	=> build_popup_url("rebuild_thumbs.php?set_albumName={$albumName}")
			);

			$albumCommands[] = array(
				'text'	=> gTranslate('common', "Rearrange items"),
				'html'	=> popup_link(gTranslate('common',"Rearrange items"),
						      "rearrange.php?set_albumName={$albumName}",
						      false, true, 550, 600, '', '', 'arrow_switch.png'
					   ),
				'value'	=> build_popup_url("rearrange.php?set_albumName={$albumName}")
			);

			if(checkRequirements('exif')) {
				$albumCommands[] = array(
					'text'	=> gTranslate('common', "Rebuild capture dates"),
					'html'	=> popup_link(gTranslate('common',"Rebuild capture dates"),
							      "rebuild_capture_dates.php?set_albumName={$albumName}",
							      false, true, 550, 600, '', '', 'folder_clock.png'
						   ),
					'value'	=> build_popup_url("rebuild_capture_dates.php?set_albumName={$albumName}")
				);

			}
		}

		if(checkRequirements('isAdminOrAlbumOwner', 'votingOn')) {
			$albumCommands[] = array(
				'text'	=> gTranslate('common', "Poll results"),
				'html' => galleryLink(makeGalleryUrl(
										'poll_results.php',
										array(
											'set_albumName' => $albumName,
											'gallery_popup' => true)),
									  gTranslate('common',"Poll results"),
									  array(),'', true),
				'value' => makeGalleryUrl("poll_results.php",
										  array(
										  	'set_albumName' => $albumName,
										  	'gallery_popup' => true))
			);

			$albumCommands[] = array(
				'class'	=> 'url',
				'text'	=> gTranslate('common', "Poll reset"),
				'html'	=> popup_link(gTranslate('common',"Poll reset"),
						      "reset_votes.php?set_albumName={$albumName}",
						      false, true, 550, 600, '', '', 'undo.png'
					   ),
				'value'	=> build_popup_url("reset_votes.php?set_albumName={$albumName}")
			);
		}
	}
	/* Options shown only on the mainpage */
	else {
		/* User is allowed to view ALL comments */
		if (checkRequirements('isAdminOrAlbumOwner', 'allowComments', 'comments_enabled', 'hasComments')) {
			$albumCommands[] = array(
				'class'	=> 'url',
				'text' => gTranslate('common',"View&nbsp;comments"),
				'html' => galleryLink(
							makeGalleryUrl("view_comments.php", array("set_albumName" => $albumName)),
							gTranslate('common',"View&nbsp;comments"),
							array(),'', true),
				'value' => makeGalleryUrl("view_comments.php", array("set_albumName" => $albumName))
			);
		}
	}

	array_sort_by_fields($albumCommands, 'text');

	if(!empty($albumCommands) && $caption) {
		array_unshift($albumCommands, array(
			'text'		=> gTranslate('common',"<< Album actions >>"),
			'selected'	=> true
		));
	}

	return $albumCommands;
}
?>