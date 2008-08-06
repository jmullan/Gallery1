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

class Nuke5_AdminUser extends Abstract_User {
	var $db;
	var $prefix;

	function Nuke5_AdminUser($admin) {
		global $gallery;
		$this->db = $gallery->database{"nuke"};
		$this->prefix = $gallery->database{"prefix"};

		if(!is_array($admin)) {
			$admin = base64_decode($admin);
			$admin = explode(":", $admin);
			$aid = "$admin[0]";
		} else {
			$aid = "$admin[0]";
		}

		$results = $this->db->query("select name, email from " .
				$this->prefix . "authors " .
				"where aid='$aid'");
		$row = $this->db->fetch_row($results);
		$this->username = $aid;
		$this->fullname = $row[0];
		$this->email = $row[1];
		$this->isAdmin = 1;
		$this->canCreateAlbums = 1;
		$this->uid = "admin_$aid";
	}
}

?>
