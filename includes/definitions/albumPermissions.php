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
 * $Id: albumProperties.php 17321 2007-12-29 07:17:50Z JensT $
 */

/**
 * Defintion for album properties.
 *
 * @package Definitions
 * @author Jens Tkotz
 */

if (!isset($gallery) || !function_exists('gTranslate')) {
	exit;
}

/*
 * This array contains a list of all possible album permissions a user can have.
 */
$perms = array(
	'canRead',
	'canAddTo',
	'canDeleteFrom',
	'canWrite',
	'canCreateSubAlbum',
	'zipDownload',
	'canViewComments',
	'canAddComments',
	'canViewFullImages',
	'canChangeText'
);

// Set values for selectboxes
foreach($perms as $perm)  {
	$ids[$perm] = $gallery->album->getPermIds($perm);
	asort($ids[$perm]);
	correctPseudoUsers($ids[$perm], $ownerUid);
}

function userBox($perm) {
	global $ids;

	$html = '<div style="float:left;">';
	$html .= "\n\t". gSubmit("submit[$perm]", '-->') .'<br><br>';
	$html .= "\n\t" .gSubmit("submit[$perm]", '<--');
	$html .= "\n</div>";
	$html .= drawSelect("actionUids", $ids[$perm], '', 7);

	return $html;
}

/*
 * This array contains details to the permissions defined above
 */
$permsDetailed = array(
	'canRead'	=> array(
		'type'		=> 'group',
		'initial'	=> 'true',
		'title'		=> gTranslate('core', "_View album"),
		'desc'		=> gTranslate('core', "Users / Groups that can see the album."),
		'content'   => userBox('canRead')
	),
	'canAddTo'	=> array(
		'type'		=> 'group',
		'title'		=> gTranslate('core', "_Add items"),
		'desc'		=> gTranslate('core', "Users / Groups that can add items."),
		'content'	=> userBox('canAddTo')
	),
	'canDeleteFrom'	=> array(
		'type'		=> 'group',
		'title'		=> gTranslate('core', "_Delete items"),
		'desc'		=> gTranslate('core', "Users / Groups that can delete items."),
		'content'	=> userBox('canDeleteFrom')
	),
	'canWrite' 	=> array(
		'type'		=> 'group',
		'title'		=> gTranslate('core', "_Modify items"),
		'desc'		=> gTranslate('core', "Users / Groups that can modify items."),
		'content'	=> userBox('canWrite')
	),
	'canCreateSubAlbum'	=> array(
		'type'		=> 'group',
		'title'		=> gTranslate('core', "Create _Subalbums"),
		'desc'		=> gTranslate('core', "Users / Groups that can create sub albums."),
		'content'	=> userBox('canCreateSubAlbum')
	),
	'zipDownload'	=> array(
		'type'		=> 'group',
		'title'		=> gTranslate('core', "Zip_download"),
		'desc'		=> gTranslate('core', "Users / Groups that can to download album (with subalbums) as archive."),
		'content'	=> userBox('zipDownload')
	),
	'canViewComments'	=> array(
		'type'		=> 'group',
		'name'		=> 'canViewComments',
		'title'		=> gTranslate('core', "View _comments"),
		'desc'		=> gTranslate('core', "Users / Groups that can view comments."),
		'content'	=> userBox('canViewComments')
	),
	'canAddComments'	=> array(
		'type'		=> 'group',
		'title'		=> gTranslate('core', "Add c_omments"),
		'desc'		=> gTranslate('core', "Users / Groups that can add comments."),
		'content'	=> userBox('canAddComments')
	),
	'canViewFullImages'	=> array(
		'type'		=> 'group',
		'title'		=> gTranslate('core', "View _full images"),
		'desc'		=> gTranslate('core', "Users / Groups that can view _full (original) images."),
		'content'	=> userBox('canViewFullImages')
	),
	'canChangeText'	=> array(
		'type'		=> 'group',
		'title'		=> gTranslate('core', "_Edit texts"),
		'desc'		=> gTranslate('core', "Users / Groups that can change album text."),
		'content'	=> userBox('canChangeText')
	)
);

?>