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

function getGroupIdList() {
	global $gallery;

	$groupIdList = array();
	$userDir = $gallery->app->userDir;


	// Öffnen eines bekannten Verzeichnisses und danach seinen Inhalt einlesen
	if (is_dir($userDir)) {
		if ($dirHandle = opendir($userDir)) {
			while (($file = readdir($dirHandle)) !== false) {
				if(! hasValidGroupIdFormat($file)) {
					continue;
				}
				$groupIdList [] = $file;
			}
			closedir($dirHandle);
		}
	}

	return $groupIdList;
}

function deleteGroup($gid) {
	global $gallery;

	$userDir = $gallery->app->userDir;
	$status = true;

	if(fs_file_exists("$userDir/$gid")) {
		$status = fs_unlink("$userDir/$gid");
	}

	if (fs_file_exists("$userDir/$gid.bak")) {
		$status = $status && fs_unlink("$userDir/$gid.bak");
	}

	if (fs_file_exists("$userDir/$gid.lock")) {
		$status = $status && fs_unlink("$userDir/$gid.lock");
	}

	return $status;
}

function validNewGroupName($groupname) {
	$saveToDisplayGroupName = '<i>'. htmlentities($groupname) .'</i>';

	echo debugMessage(sprintf(gTranslate('core',
		"Checking groupname '%s' for validity"), $saveToDisplayGroupName),
		__FILE__, __LINE__, 4);

	if (strlen($groupname) == 0) {
		return gTranslate('core', "Please enter a groupname.");
	}

	if (strlen($groupname) < 2) {
		return sprintf(gTranslate('core', "Groupname '%s' is to short. Must be at least 2 characters."),
		$saveToDisplayGroupName);
	}

	if (strlen($groupname) > 25) {
		return sprintf(gTranslate('core', "Groupname '%s' too long. Must be at most 25 characters."),
		$saveToDisplayGroupName);
	}

	if (ereg("[^[:alnum:]]", $groupname)) {

		return sprintf(gTranslate('core', "Illegal groupname '%s'. Only letters and digits allowed."),
		$saveToDisplayGroupName);
	}


	$group = Gallery_Group::loadByName($groupname);

	if ($group) {
		return sprintf(gTranslate('core', "A group with the groupname of '%s' already exists"),
		$saveToDisplayGroupName);
	}

	return null;
}

/**
 * Returns an array with all groups. Structure 'gid' => 'name'
 *
 * @return unknown
 */
function buildGroupsList() {
	$groupIdList	= getGroupIdList();
	$groupList		= array();

	if(! empty($groupIdList)) {
		foreach ($groupIdList as $groupID) {
			$tmpGroup = new Gallery_Group();
			$tmpGroup->load($groupID);
			$groups[] = array(
				'value' => $groupID,
				'text' => $tmpGroup->getName()
			);
		}
	}

	array_sort_by_fields($groups, 'text', 'asc', true, true);
	
	return $groupList;
}
