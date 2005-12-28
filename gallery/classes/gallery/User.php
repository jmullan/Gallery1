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
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
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


		// assuming revision 4 ensures that if the user_version is 
		// not properly read from file due to the file format changes
		// that we perform the necessary upgrade.
		$this->version = 4;
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
			foreach (unserialize($tmp) as $k => $v) {
				$this->$k = $v;
			}
			$this->save();
		} else {
			foreach (unserialize($tmp) as $k => $v) {
				$this->$k = $v;
			}
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
			$this->version = "0";
		}
		if (!strcmp($this->version, $gallery->user_version)) {
			print _("up to date");
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
		if ($this->version < 5) 
		{
			$dir = $gallery->app->userDir;
			$olduid = $this->uid;
			$uid = strtr($olduid, ':;', '__');
			$this->uid = $uid;
			$file1 = sprintf('%s/%s', $dir, $olduid);
			$file2 = sprintf('%s/%s', $dir, $uid);

			if (fs_is_file($file1)) {
				fs_rename($file1, $file2);
			}
			if (fs_is_file("$file1.bak")) {
				fs_rename("$file1.bak", "$file2.bak");
			}
			if (fs_is_file("$file1.lock")) {
				fs_rename("$file1.lock", "$file2.lock");
			}
		}

		/* New attribut introduced in Gallery 1.5.1-cvs-b10
		** Set to 1 (yes) as this was the behaviour before.
		*/
		if ($this->version < 6)  {
		    $this->canChangeOwnPw = 1;
		}

		$this->version = $gallery->user_version;
		if ($this->save()) {
			$success=true;
			print _("upgraded");
		} else {
			$success = false;
			print _("failed");
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
	       	return str_replace("&amp;", "&", makeGalleryUrl('new_password.php', array('hash' => $rec_pass_hash, 'uname' => $this->getUsername())));
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
			echo gallery_error(sprintf(_("Not a valid action: %s"), 
						$action));
			return;
	       	}
		$this->lastAction=$action;
		$this->lastActionDate=time();
	}
}

?>
