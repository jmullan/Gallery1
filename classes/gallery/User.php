<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2003 Bharat Mediratta
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
class Gallery_User extends Abstract_User {

	var $defaultLanguage;
	var $version;
	var $recoverpasshash;

	function Gallery_User() {
		global $gallery;
		Abstract_User::Abstract_User();
		$this->setDefaultLanguage("");
		$this->version = $gallery->user_version;
	}

	function load($uid) {
		global $gallery;

		$dir = $gallery->app->userDir;
		
		$tmp = getFile("$dir/$uid");

		/*
		 * We renamed User.php to Gallery_User.php in v1.2, so port forward
		 * any saved user objects.
		 */
		if (!strcmp(substr($tmp, 0, 10), 'O:4:"user"')) {
			$tmp = ereg_replace('O:4:"user"', 'O:12:"gallery_user"', $tmp);
			$this = unserialize($tmp);
			$this->save();
		} else {
			$this = unserialize($tmp);
		}
	}

	function save() {
		global $gallery;
		$success = 0;

		$dir = $gallery->app->userDir;
		return safe_serialize($this, "$dir/$this->uid");
	}

	/*
	 * Whenever you change this code, you should bump the $gallery->user_version
	 * appropriately.
	 */	
	function integrityCheck() {
		global $gallery;

		if (!isset($this->version)) {
			$this->version == "0";
		}
		if (!strcmp($this->version, $gallery->user_version)) {
			return true;
		}

		if ($this->version < 1) 
		{
			$this->setDefaultLanguage("");
		}
		if ($this->version < 2) 
		{
			$this->setRecoverPasswordHash("");
		}
		$this->version = $gallery->user_version;
		if ($this->save()) {
			$success=true;
		} else {
			$success = false;
		}

		return $success;
	}
	function setDefaultLanguage($defaultLanguage) {
		$this->defaultLanguage = $defaultLanguage;
	}

	function getDefaultLanguage() {
		return $this->defaultLanguage;
	}

	function setRecoverPasswordHash($hash) {
		$this->recoverpasshash = $hash;
	}

	function getRecoverPasswordHash() {
		return $this->recoverpasshash;
	}

}

?>
