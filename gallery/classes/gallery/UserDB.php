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
class Gallery_UserDB extends Abstract_UserDB {
	var $userMap;
	var $nobody;
	var $everybody;
	var $loggedIn;
	
	function Gallery_UserDB() {
		global $gallery;
		$userDir = $gallery->app->userDir;

		$this->userMap = array();

		if (!fs_file_exists($userDir)) {
			if (!mkdir($userDir, 0777)) {
				gallery_error(_("Unable to create dir") .": $userDir");
				return;
			}
		} else {
			if (!fs_is_dir($userDir)) {
				gallery_error("$userDir". _("exists, but is not a directory !"));
				return;
			}
		}

		if (!fs_file_exists("$userDir/.htaccess")) {
			$fd = fs_fopen("$userDir/.htaccess", "w");
			fwrite($fd, "Order deny,allow\nDeny from all\n");
			fclose($fd);
		}


		if (fs_file_exists("$userDir/userdb.dat")) {
			$tmp = getFile("$userDir/userdb.dat");

			/* 
			 * We moved from class UserDB.php to class Gallery_UserDB.php
			 * in v1.2.  If we're upgrading from an old version, just ignore
			 * the old cache file (it'll get rebuilt automatically).
			 */
			if (strcmp(substr($tmp, 0, 12), 'O:6:"userdb"')) {
				$this = unserialize($tmp);
			}
		}

		if (!$this->nobody) {
			$this->nobody = new NobodyUser();
		}

		if (!$this->everybody) {
			$this->everybody = new EverybodyUser();
		}

		if (!$this->loggedIn) {
			$this->loggedIn = new LoggedInUser();
		}
	}

	function canCreateUser() {
		return true;
	}

	function canModifyUser() {
		return true;
	}

	function canDeleteUser() {
		return true;
	}

	function getUserByUsername($username, $level=0) {
		global $gallery;

		if ($level > 1) {
			// We've recursed too many times.  Abort;
			return;
		}

		if (!strcmp($username, $this->nobody->getUsername())) {
			return $this->nobody;
		} else if (!strcmp($username, $this->everybody->getUsername())) {
			return $this->everybody;
		} else if (!strcmp($username, $this->loggedIn->getUsername())) {
			return $this->loggedIn;
		}

		$uid = $this->userMap[$username];
		if (!$uid) {
			$this->rebuildUserMap();
			$uid = $this->userMap[$username];
			if (!$uid) {
				return;
			}
		}

		$user = $this->getUserByUid($uid);
		if (!$user || strcmp($user->getUsername(), $username)) {
			// We either got no uid for this name, or we got a uid
			// but that uid has a different username.  Either way
			// this means our map is out of date.
			$this->rebuildUserMap();
			return $this->getUserByUsername($username, ++$level);
		} else {
			return $user;
		}
		
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

		if (fs_file_exists("$userDir/$uid")) {
			$user = new Gallery_User();
			$user->load($uid);
			return $user;
		}

		return $this->nobody;
	}

	function getOrCreateUser($username) {
		$user = $this->getUserByUsername($username);
		if (!$user) {
			$user = new Gallery_User();
			$user->setUsername($username);
		}
		return $user;
	}

	function getUsername($uid) {
		return $this->userMap[$uid];
	}

	function getUid($username) {
		return $this->userMap[$username];
	}

	function deleteUserByUsername($username) {
		global $gallery;
		$userDir = $gallery->app->userDir;

		$user = $this->getUserByUsername($username);
		if ($user) {
			$uid = $user->getUid();
			if (fs_file_exists("$userDir/$uid")) {
				return fs_unlink("$userDir/$uid");
			}
		}
		$this->rebuildUserMap();

		return 0;
	}

	function rebuildUserMap() {
		global $gallery;
		$userDir = $gallery->app->userDir;

		foreach ($this->getUidList() as $uid) {
			$tmpUser = $this->getUserByUid($uid);
			$username = $tmpUser->getUsername();
			$this->userMap[$username] = $uid;
			$this->userMap[$uid] = $username;
		}

		return safe_serialize($this, "$userDir/userdb.dat");
	}

	function getUidList() {
		global $gallery;
		
		$uidList = array();
		if ($fd = fs_opendir($gallery->app->userDir)) {
			while ($file = readdir($fd)) {
				if (!ereg("^[0-9].*[0-9]$", $file)) {
					continue;
				}

				if (fs_is_dir($gallery->app->userDir . "/" . $file)) {
					continue;
				}


				$tmp = getFile($gallery->app->userDir . "/" . $file);

				/* In v1.2 we renamed User to Gallery_User */
				if (!strcmp(substr($tmp, 0, 10), 'O:4:"user"')) {
				    $tmp = ereg_replace('O:4:"user"', 'O:12:"gallery_user"', $tmp);
				}
				
				$user = unserialize($tmp);
				if (!strcmp(get_class($user), "gallery_user")) {
					array_push($uidList, $user->uid);
				}
			}
		}

		array_push($uidList, $this->nobody->getUid());
		array_push($uidList, $this->everybody->getUid());
		array_push($uidList, $this->loggedIn->getUid());

		sort($uidList);
		return $uidList;
	}

	function validNewUsername($username) {

		if (strlen($username) < 2) {
			return _("Username must be at least 2 characters") ;
		}

		if (strlen($username) > 15) {
			return _("Username must be at most 15 characters") ;
		}

		if (ereg("[^[:alnum:]]", $username)) {
			return _("Username must contain only letters or digits") ;
		}

		if (!strcmp($username, $this->nobody->getUsername()) ||
		    !strcmp($username, $this->everybody->getUsername()) ||
		    !strcmp($username, $this->loggedIn->getUsername())) {
			return "<i>$username</i> ". _("is reserved and cannot be used.") ;
		}

		$user = $this->getUserByUsername($username);
		if ($user) {
			return _("A user with the username of") ." <i>$username</i> " . _("already exists");
		}

		return null;
	}

	function validPassword($password) {
		if (strlen($password) < 3) {
			return _("Password must be at least 3 characters");
		}

		return null;
	}
}

?>
