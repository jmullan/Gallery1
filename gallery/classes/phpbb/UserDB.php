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

/* This class is written for phpBB2 and provides full integration of the phpbb users database
 * Instead of using or duplicating memberships manually in Gallery.
 *
 * Gallery <-> phpBB2 integration ver. (www.snailsource.com)
 * Written by Martin Smallridge	   <info@snailsource.com>
 *
 * This file was modified for official integration into Gallery 1.4.3 by
 * Jens Tkotz
*/

if (! class_exists('Abstract_UserDB')) {
	exit;
}

class phpbb_UserDB extends Abstract_UserDB {
	var $db;

	function phpbb_UserDB() {
		global $gallery;
		$this->db = $gallery->database{"phpbb"};
		$this->nobody = new NobodyUser();
		$this->everybody = new EverybodyUser();
		$this->loggedIn = new LoggedInUser();
	}

	function getUidList() {
		global $table_prefix;
		$uidList = array();
		$db = $this->db;

		$result = $db->query("select user_id from ".$table_prefix."users");

		while ($row = $db->fetch_row($result)) {
			array_push($uidList, $row[0]);
		}

		array_push($uidList, $this->nobody->getUid());
		array_push($uidList, $this->everybody->getUid());
		array_push($uidList, $this->loggedIn->getUid());

		sort($uidList);
		return $uidList;
	}

	function getUserByUsername($username, $level=0) {
		if (!strcmp($username, $this->nobody->getUsername())) {
			return $this->nobody;
		} else if (!strcmp($username, $this->everybody->getUsername())) {
			return $this->everybody;
		} else if (!strcmp($username, $this->loggedIn->getUsername())) {
			return $this->loggedIn;
		} 

		$user = new phpbb_User();
		$user->loadByUsername($username);
		return $user;
	}

	function getUserByUid($uid) {
		global $gallery;
		$userDir = $gallery->app->userDir;

		if (!$uid || !strcmp($uid, $this->nobody->getUid())) {
			return $this->nobody;
		} else if (!strcmp($uid, $this->everybody->getUid())) {
			return $this->everybody;
		} else if (!strcmp($uid, $this->loggedIn->getUid())) {
			return $this->loggedIn;
		} 

		$user = new phpbb_User();
		$user->loadByUid($uid);
		return $user;
	}
}

?>
