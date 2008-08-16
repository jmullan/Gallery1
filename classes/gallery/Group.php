<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2007 Bharat Mediratta
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
?>
<?php
class Gallery_Group extends Abstract_Group {
	var $version;

	function Gallery_Group() {
		global $gallery;

		$this->name		= '';
		$this->description	= '';
		$this->memberList	= array();
		$this->gid		= 'g_' . time() . '_' . mt_rand();
	}

	/**
	 * Loads serialized data into a group object.
	 *
	 * @param integer	$gid
	 * @return boolean	true on a successfull load, otherwise false.
	 */
	function load($gid) {
		global $gallery;

		$dir = $gallery->app->userDir;

		$tmp = getFile("$dir/$gid");
		
		if($tmp) {
			foreach (unserialize($tmp) as $k => $v) {
				$this->$k = $v;
			}
		}
	}


	function loadByName($groupname) {
		global $gallery;

		$getGroupIdList = getGroupIdList();

		foreach($getGroupIdList as $id) {
			$tmpGrp = new Gallery_Group();
			$tmpGrp->load($id);
			$tmpName = $tmpGrp->getName();
			if($tmpName == $groupname) {
				return $tmpGrp;
			}
		}
	}

	function save() {
		global $gallery;
		$success = 0;

		$dir = $gallery->app->userDir;
		return safe_serialize($this, "$dir/$this->gid");
	}

	function getMemberlist() {
		return $this->memberList;
	}

	function setMemberlist($gidList) {
		$this->memberList = $gidList;
		$this->memberList = $gidList;
	}

	function getName() {
		return $this->name;
	}

	function setName($groupName) {
		$this->name = $groupName;
	}

	function getDescription() {
		return $this->description;
	}

	function setDescription($description) {
		$this->description = $description;
	}

	function userIsMember($userID) {
		foreach($this->getMemberlist() as $memberUserId) {
			if ($memberUserId == $userID) {
				return true;
			}
		}
		return false;
	}
}

?>
