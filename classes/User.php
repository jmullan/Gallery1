<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2002 Bharat Mediratta
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
 */
?>
<?php
class Abstract_User {
	var $username;
	var $fullname;
	var $password;
	var $email;
	var $isAdmin;
	var $canCreateAlbums;
	var $uid;

	function Abstract_User() {
		$this->setIsAdmin(false);
		$this->setCanCreateAlbums(false);
		$this->uid = time() . ":" . mt_rand();
	}

	function setPassword($password) {
		$this->password = md5($password);
	}

	function isCorrectPassword($password) {
		return (!strcmp($this->password, md5($password)));
	}

	function getUid() {
		return $this->uid;
	}

	function setUsername($username) {
		$this->username = $username;
	}

	function getUsername() {
		return $this->username;
	}

	function setEmail($email) {
		$this->email = $email;
	}

	function getEmail() {
		return $this->email;
	}

	function setFullName($fullname) {
		$this->fullname = $fullname;
	}

	function getFullName() {
		return $this->fullname;
	}

	function isAdmin() {
		return $this->isAdmin;
	}

	function isPseudo() {
		return false;
	}

	function setIsAdmin($bool) {
		$this->isAdmin = $bool;
	}

	function canReadAlbum($album) {
		if ($this->isAdmin()) {
			return true;
		}

		if ($album->canRead($this->uid)) {
			return true;
		}

		return false;
	}

	function canWriteToAlbum($album) {
		if ($this->isAdmin()) {
			return true;
		}

		if ($album->canWrite($this->uid)) {
			return true;
		}

		return false;
	}

	function canAddToAlbum($album) {
		if ($this->isAdmin()) {
			return true;
		}

		// If they can write, they can add
		if ($this->canWriteToAlbum($album)) {
			return true;
		}

		if ($album->canAddTo($this->uid)) {
			return true;
		}

		return false;
	}		

	function canDeleteFromAlbum($album) {
		if ($this->isAdmin()) {
			return true;
		}

		if ($album->canDeleteFrom($this->uid)) {
			return true;
		}

		return false;
	}		

	function canDeleteAlbum($album) {
		if ($this->isAdmin()) {
			return true;
		}

		if ($album->canDelete($this->uid)) {
			return true;
		}

		return false;
	}

	function canCreateSubAlbum($album) {
		if ($this->isAdmin()) {
			return true;
		}

		if ($album->canCreateSubAlbum($this->uid)) {
			return true;
		}

		return false;
	}
	
	function canCreateAlbums() {
		if ($this->isAdmin()) {
			return true;
		}

		if ($this->canCreateAlbums) {
			return true;
		}

		return false;
	}

	function setCanCreateAlbums($bool) {
		$this->canCreateAlbums = $bool;
	}

	function canChangeTextOfAlbum($album) {
		if ($this->isAdmin()) {
			return true;
		}

		if ($album->canChangeText($this->uid)) {
			return true;
		}

		return false;
	}

	function isOwnerOfAlbum($album) {
		if ($album->isOwner($this->uid)) {
			return true;
		}

		return false;
	}

	function isLoggedIn() {
		return true;
	}
}

?>
