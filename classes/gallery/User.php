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
	var $recoverPassHash;
	var $lastAction;
	var $lastActionDate;
	var $origEmail; 
		// the email from original account creation.  Just incase user goes feral

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
			$this->genRecoverPasswordHash(true);
		}
		if ($this->version < 3) 
		{
			$this->lastAction=NULL;
			$this->lastActionDate=time(0);
			$this->origEmail=$this->email;
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

	function genRecoverPasswordHash($reset=false) {
		if ($reset) {
		       	$this->recoverPassHash = NULL;
			return "";
		}
	       	$rec_pass_hash=substr(md5($this->password.
					$this->uid.microtime()), 0, 5);
		$this->recoverPassHash = md5($rec_pass_hash);
	       	return makeGalleryUrl('new_password.php', array('hash' => $rec_pass_hash, 'uname' => $this->getUsername()));
	}

	function checkRecoverPasswordHash($hash) {
		if (md5($hash) == $this->recoverPassHash) {
			return true;
		}
		return false;
	}

	function log($action) {
		$valid_actions = array("register", "self_register", 
				"bulk_register", "login", 
				"new_password_request", "new_password_set");
		if (!in_array($action, $valid_actions)) {
			gallery_error(sprintf(_("Not a valid action: %s"), 
						$action));
			return;
	       	}
		$this->lastAction=$action;
		$this->lastActionDate=time();
	}
}

?>
