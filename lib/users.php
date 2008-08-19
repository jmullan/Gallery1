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
 * $Id: albumItem.php 17321 2007-12-29 07:17:50Z JensT $
 */

/**
 * @package	Users
 * @author	Jens Tkotz
 */

/**
 * Builds a set of users. Structure 'uid' => 'name'
 * Return is an array of three sets of users.
 * 'specialUsers', all Pseudousers like everybody, or nobody
 * 'users' all normal users
 * 'allUsers', ALL users ;-)
 *
 * @param boolean $longformat   If true, then the full name and username is showed,
 *                              otherwise only the username.
 * @return array
 */
function buildUsersList($longformat = false) {
	global $gallery;

	$specialUsers	= array();
	$users			= array();
	$allUsers		= array();

	if(!isset($gallery->userDB)) {
		return array($specialUsers, $users, $allUsers);
	}

	foreach ($gallery->userDB->getUidList() as $uid) {
		$tmpUser		 = $gallery->userDB->getUserByUid($uid);
		$tmpUserFullName = $tmpUser->getFullName();

		if($longformat && ! empty($tmpUserFullName)) {
			$userDisplay = $tmpUser->getFullName() . ' (' . $tmpUser->getUsername() .')';
		}
		else {
			$userDisplay = $tmpUser->getUsername();
		}

		if ($tmpUser->isPseudo()) {
			$specialUsers[] = array(
				'value' => $uid,
				'text' => "*$userDisplay*",
			);

			$allUsers[$uid] = "*$userDisplay*";
		}
		else {
			$users[] = array(
				'value' => $uid,
				'text' => $userDisplay,
			);

			$allUsers[$uid] = $userDisplay;
		}
	}

	asort($allUsers);
	array_sort_by_fields($users, 'text', 'asc', true, true);

	return array($specialUsers, $users, $allUsers);
}
