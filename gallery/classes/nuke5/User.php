<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2005 Bharat Mediratta
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
class Nuke5_User extends Abstract_User {
	var $db;
	var $prefix;
	var $fields;
	
	function Nuke5_User() {
		global $gallery;
		$this->db = $gallery->database{"nuke"};
		$this->prefix = $gallery->database{"user_prefix"};
		$this->fields = $gallery->database{'fields'};
	}

	function loadByUid($uid) {
		$results = $this->db->query('select ' . $this->fields{'uname'} .
		   ', ' . $this->fields{'name'} . ', ' . $this->fields{'email'} .
		   ' from ' . $this->prefix . 'users '. 'where ' .
		   $this->fields{'uid'} . "='$uid'");
		$row = $this->db->fetch_row($results);
		$this->username = $row[0];
		$this->fullname = $row[1];
		$this->email = $row[2];
		$this->isAdmin = 0;
		$this->canCreateAlbums = 0;
		$this->uid = $uid;
	}

	function loadByUserName($uname) {
		$results = $this->db->query('select ' . $this->fields{'uid'} .
		   ', ' . $this->fields{'name'} . ', ' . $this->fields{'email'} .
		   ' from ' . $this->prefix . 'users ' . 'where ' .
		   $this->fields{'uname'} . "='$uname'");
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
