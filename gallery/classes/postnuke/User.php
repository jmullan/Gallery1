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

if (! class_exists('Abstract_User')) {
	exit;
}

class PostNuke_User extends Abstract_User {
	var $db;
	var $prefix;

	function PostNuke_User() {
		global $gallery;
		$this->db = $gallery->database{"db"};
		$this->prefix = $gallery->database{"prefix"};
	}

	function loadByUid($uid) {
			$result = $this->db->Execute("select uname, name, email from " .
						 $this->prefix . "users" . " " .
						 "where uid='$uid'");
		list($this->username,
			 $this->fullname,
			 $this->email) = $result->fields;

		$result->Close();
		
		$this->isAdmin = (authorised(0, '::', '::', ACCESS_ADMIN));
		$this->canCreateAlbums = 0;
		$this->uid = $uid;
	}

	function loadByUserName($uname) {
			$result = $this->db->Execute("select uid, name, email from " .
						 $this->prefix . "users" . " " .
						 "where uname='$uname'");
		list($this->uid,
			 $this->fullname,
			 $this->email) = $result->fields;

		$result->Close();
		
		$this->isAdmin = (authorised(0, '::', '::', ACCESS_ADMIN));
		$this->canCreateAlbums = 0;
		$this->username = $uname;
	}
}

?>
