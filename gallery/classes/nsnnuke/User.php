<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2006 Bharat Mediratta
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
class CPGNuke_User extends Abstract_User {
	var $db;
	var $prefix;
	var $fields;
	
	function CPGNuke_User() {
		global $gallery;
		$this->db = $gallery->database{"cpgnuke"};
		$this->prefix = $gallery->database{"user_prefix"};
		$this->fields = $gallery->database{'fields'};
	}

	function loadByUid($uid) {

		$sql = 'select '.
			$this->fields{'uname'} . ', '. 
			$this->fields{'name'} . ', '. 
			$this->fields{'email'} .
			' from ' . $this->prefix . 'users'. 
			' where ' . $this->fields{'uid'} . "='$uid'";

		$results = $this->db->query($sql);
		$row = $this->db->fetch_row($results);
		$this->username = $row[0];
		$this->fullname = $row[1];
		$this->email = $row[2];
		$this->isAdmin = 0;
		$this->canCreateAlbums = 0;
		$this->uid = $uid;
	}

	function loadByUserName($uname) {

		$sql = 'select '. 
			$this->fields{'uid'} . ', '. 
			$this->fields{'name'} . ', '. 
			$this->fields{'email'} .
			' from ' . $this->prefix . 'users'. 
			' where ' . $this->fields{'uname'} . "='$uname'";

		$results = $this->db->query($sql);
		$row = $this->db->fetch_row($results);
		$this->uid = $row[0];
		$this->fullname = $row[1];
		$this->email = $row[2];
		$this->isAdmin = 0;
		$this->canCreateAlbums = 0;
		$this->username = $uname;
	}
}

?>
