<?
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000 Bharat Mediratta
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
<?
class UserDB {
	var $userMap;
	var $nobody;
	var $everybody;
	
	function UserDB() {
		global $app;

		$this->userMap = array();

		if (!file_exists($app->userDir)) {
			if (!mkdir($app->userDir, 0777)) {
				error("Unable to create dir: $app->userDir");
				return;
			}
		} else {
			if (!is_dir($app->userDir)) {
				error("$app->userDir exists, but is not a directory!");
				return;
			}
		}

		if (!file_exists("$app->userDir/.htaccess")) {
			$fd = fopen("$app->userDir/.htaccess", "w");
			fwrite($fd, "Order deny, allow\nDeny from all\n");
			fclose($fd);
		}

		if (file_exists("$app->userDir/userdb.dat")) {
			$tmp = getFile("$app->userDir/userdb.dat");
			$this = unserialize($tmp);
		}

		if (!$this->nobody) {
			$this->nobody = new NobodyUser();
		}

		if (!$this->everybody) {
			$this->everybody = new EverybodyUser();
		}
	}

	function getUserByUsername($username, $level=0) {
		global $app;

		if ($level > 1) {
			// We've recursed too many times.  Abort;
			return;
		}

		if (!strcmp($username, $this->nobody->getUsername())) {
			return $this->nobody;
		} else if (!strcmp($username, $this->everybody->getUsername())) {
			return $this->everybody;
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
		global $app;

		if (!$uid || !strcmp($uid, $this->nobody->getUid())) {
			return $this->nobody;
		} else if (!strcmp($uid, $this->everybody->getUid())) {
			return $this->everybody;
		}

		if (file_exists("$app->userDir/$uid")) {
			$user = new User();
			$user->load($uid);
			return $user;
		}

		return $this->nobody;
	}

	function getOrCreateUser($username) {
		$user = $this->getUserByUsername($username);
		if (!$user) {
			$user = new User();
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
		global $app;

		$user = $this->getUserByUsername($username);
		if ($user) {
			$uid = $user->getUid();
			if (file_exists("$app->userDir/$uid")) {
				return unlink("$app->userDir/$uid");
			}
		}
		$this->rebuildUserMap();

		return 0;
	}

	function rebuildUserMap() {
		global $app;

		foreach ($this->getUidList() as $uid) {
			$tmpUser = $this->getUserByUid($uid);
			$username = $tmpUser->getUsername();
			$this->userMap[$username] = $uid;
			$this->userMap[$uid] = $username;
		}

		$success = 0;
		$dir = $app->userDir;
		$tmpfile = tempnam($dir, "userdb.dat");
		if ($fd = fopen($tmpfile, "w")) {
			fwrite($fd, serialize($this));
			fclose($fd);
			$success = rename($tmpfile, "$dir/userdb.dat");
		}

		return $success;
	}

	function getUidList() {
		global $app;
		
		$uidList = array();
		if ($fd = opendir($app->userDir)) {
			while ($file = readdir($fd)) {
				if (!strchr($file, ":")) {
					continue;
				}

				if (is_dir($file)) {
					continue;
				}

				array_push($uidList, $file);
			}
		}

		array_push($uidList, $this->nobody->getUid());
		array_push($uidList, $this->everybody->getUid());

		sort($uidList);
		return $uidList;
	}

	function validNewUsername($username) {

		if (strlen($username) < 2) {
			return "Username must be at least 2 characters";
		}

		if (strlen($username) > 15) {
			return "Username must be at most 15 characters";
		}

		if (preg_match("/[^A-Za-z0-9]/", $username)) {
			return "Username must contain only letters or digits";
		}

		if (!strcmp($username, $this->nobody->getUsername()) ||
		    !strcmp($username, $this->everybody->getUsername())) {
			return "<i>$username</i> is reserved and cannot be used.";
		}

		$user = $this->getUserByUsername($username);
		if ($user) {
			return "A user with the username of <i>$username</i> already exists";
		}

		return null;
	}

	function validPassword($password) {
		if (strlen($password) < 3) {
			return "Password must be at least 3 characters";
		}

		return null;
	}

	function getNobody() {
		return $this->nobody;
	}

	function getEverybody() {
		return $this->everybody;
	}
}

?>
