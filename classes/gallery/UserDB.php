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
class Gallery_UserDB extends Abstract_UserDB {
	var $userMap;
	var $nobody;
	var $everybody;
	var $loggedIn;
	var $version;
	var $initialized;

	function Gallery_UserDB() {
		global $gallery;
		
		$this->initialized = false;
		
		if(empty($gallery->app->userDir)) {
            echo infoBox(array(array(
                'type' => 'error',
                'text' => sprintf("No Userdir defined ! Please rerun the %sconfiguration wizard%s.",
                    '<a href="setup">', '</a>')
            )));
            exit;
		}
		
		$userDir = $gallery->app->userDir;

		// assuming revision 4 ensures that if the user_version is
		// not properly read from file due to the file format changes
		// that we perform the necessary upgrade.
		$this->version = 4;

		$this->userMap = array();

		if (!fs_file_exists($userDir)) {
		    if(isDebugging()) {
    		    echo infoBox(array(array(
                    'type' => 'warning',
                    'text' => sprintf("The Diretory for storing the user information (%s) is defined but does not exits. Trying to create it ...",
                        $userDir)
                )));
		    }

            if (!@mkdir($userDir, 0777)) {
			    echo infoBox(array(array(
                    'type' => 'error',
                    'text' => sprintf("Gallery is unable to use/create the userdir. Please check the path to the albums folder and userdir in your config.php. You can't use the config wizard, as Gallery can't verify your useraccount.",
                        '<a href="'. makeGalleryUrl('setup/') .'">', '</a>')
                )));
                
				return false;
			}
		}
		else {
			if (!fs_is_dir($userDir)) {
				echo gallery_error(sprintf(gTranslate('core', "%s exists, but is not a directory!"),
							$userDir));
				return;
			}
		}

		if (!fs_file_exists("$userDir/.htaccess")) {
			if (is_writeable($userDir)) {
				$fd = fs_fopen("$userDir/.htaccess", "w");
				fwrite($fd, "Order deny,allow\nDeny from all\n");
				fclose($fd);
			}
			else {
				echo gallery_error(sprintf(gTranslate('core', "The folder folder which contains your user information (%s) is not writable for the webserver."),
								$userDir));
				exit;
			}
		}


		if (fs_file_exists("$userDir/userdb.dat") && is_writeable("$userDir/userdb.dat")) {
			$tmp = getFile("$userDir/userdb.dat");

			/*
			 * We moved from class UserDB.php to class Gallery_UserDB.php
			 * in v1.2.  If we're upgrading from an old version, just ignore
			 * the old cache file (it'll get rebuilt automatically).
			 */
			if (strcmp(substr($tmp, 0, 12), 'O:6:"userdb"')) {
				foreach (unserialize($tmp) as $k => $v) {
					$this->$k = $v;
				}
			}
		} elseif (fs_file_exists("$userDir/userdb.dat") && !is_writeable("$userDir/userdb.dat")) {
			echo gallery_error(gTranslate('core', "Your Userfile is not writeable"));
			exit;
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
		
		$this->initialized = true;
	}

	
	/**
	 * Returns wether the UserDB was succesfully initialized or not
	 *
	 * @return boolean     true if succesfully initialized.
	 * @author Jens Tkotz <jens@peino.de>
	 */
	function isInitialized() {
        return $this->initialized === true;
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

		$saveToDisplayUserName = '<i>'. htmlentities($username) .'</i>';
		echo debugMessage(sprintf("Geting user by username '%s'", $saveToDisplayUserName),
			__FILE__, __LINE__, 4);
			
		if ($level > 1) {
			// We've recursed too many times.  Abort;
			return;
		}

		if (!strcmp($username, $this->nobody->getUsername())) {
			return $this->nobody;
		}
		else if (!strcmp($username, $this->everybody->getUsername())) {
			return $this->everybody;
		}
		else if (!strcmp($username, $this->loggedIn->getUsername())) {
			return $this->loggedIn;
		}

		if (!isset($this->userMap[$username])) {
			$this->rebuildUserMap();
			if (!isset($this->userMap[$username])) {
				return;
			}
			else {
				$uid = $this->userMap[$username];
			}
		}
		else {
			$uid = $this->userMap[$username];

		}
		
		$uid = $this->convertUidToNewFormat($uid);
		$user = $this->getUserByUid($uid);
		if (!$user || strcmp($user->getUsername(), $username)) {
			// We either got no uid for this name, or we got a uid
			// but that uid has a different username.  Either way
			// this means our map is out of date.
			$this->rebuildUserMap();
			return $this->getUserByUsername($username, ++$level);
		}
		else {
			return $user;
		}

	}

	function getUserByUid($uid, $tryOldFormat=false) {
		global $gallery;
		$userDir = $gallery->app->userDir;

		if (!$uid || !strcmp($uid, $this->nobody->getUid())) {
			return $this->nobody;
		}
		else if (!strcmp($uid, $this->everybody->getUid())) {
			return $this->everybody;
		}
		else if (!strcmp($uid, $this->loggedIn->getUid())) {
			return $this->loggedIn;
		}

		$user = new Gallery_User();
		$uidNew = $this->convertUidToNewFormat($uid);

		if (fs_file_exists("$userDir/$uidNew")) {
			$user->load($uidNew);
		}
		else if ($tryOldFormat && fs_file_exists("$userDir/$uid")) {
			$user->load($uid);
		}
		else {
			$user = $this->nobody;
		}

		return $user;
	}

	function getOrCreateUser($username) {
		global $gallery;

		$user = $this->getUserByUsername($username);
		if (!$user) {
			$user = new Gallery_User();
			$user->setUsername($username);
			$user->version = $gallery->user_version;
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
			if (fs_file_exists("$userDir/$uid.bak")) {
				return fs_unlink("$userDir/$uid.bak");
			}
			if (fs_file_exists("$userDir/$uid.lock")) {
				return fs_unlink("$userDir/$uid.lock");
			}
		}
		$this->rebuildUserMap();

		return 0;
	}

	function rebuildUserMap() {
		global $gallery;
		$userDir = $gallery->app->userDir;

		$this->userMap = array();
		foreach ($this->getUidList() as $uid) {
			$tmpUser = $this->getUserByUid($uid);
			$username = $tmpUser->getUsername();
			$this->userMap[$username] = $uid;
			$this->userMap[$uid] = $username;
		}

		return safe_serialize($this, "$userDir/userdb.dat");
	}
	
	function save() {
		global $gallery;
		$userDir = $gallery->app->userDir;

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
				if (!strcasecmp(get_class($user), "gallery_user")) {
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

	function validNewUserName($username) {
		$saveToDisplayUserName = '<i>'. htmlentities($username) .'</i>';
		
		echo debugMessage(sprintf(gTranslate('core',
			"Checking username '%s' for validity"), $saveToDisplayUserName),
			__FILE__, __LINE__, 4);
			
		if (strlen($username) == 0) {
			return gTranslate('core', "Please enter a username.");
		}

		if (strlen($username) < 2) {
			return sprintf(gTranslate('core', "Username '%s' is to short. Must be at least 2 characters."),
					$saveToDisplayUserName);
		}

		if (strlen($username) > 15) {
			return sprintf(gTranslate('core', "Username '%s' too long. Must be at most 15 characters."),
					$saveToDisplayUserName);
		}

		if (ereg("[^[:alnum:]]", $username)) {

			return sprintf(gTranslate('core', "Illegal username '%s'. Only letters and digits allowed."),
					$saveToDisplayUserName);
		}

		if (!strcmp($username, $this->nobody->getUsername()) ||
		    !strcmp($username, $this->everybody->getUsername()) ||
		    !strcmp($username, $this->loggedIn->getUsername())) {
			return sprintf(gTranslate('core', "'%s' is reserved and cannot be used."),
					$saveToDisplayUserName);
		}

		$user = $this->getUserByUsername($username);
		if ($user) {
			return sprintf(gTranslate('core', "A user with the username of '%s' already exists"),
				$saveToDisplayUserName);
		}

		return null;
	}

	function validPassword($password) {
		if (strlen($password) < 3) {
			return gTranslate('core', "Password must be at least 3 characters");
		}

		return null;
	}

	/**
	 * Checks wether the UserDB is out of Date.
	 *
	 * @return boolean     true if out of Date.
	 */
	function versionOutOfDate() {
		global $gallery;
		
		if (strcmp($this->version, $gallery->user_version)) {
			return true;
		}
		return false;
	}
	
	function integrityCheck() {
		global $gallery;

		if (!isset($this->version)) {
			$this->version = "0";
		}
		if (!strcmp($this->version, $gallery->user_version)) {
			return true;
		}

		$success = true;
		$nobody = $this->nobody->getUsername();
		$everybody = $this->everybody->getUsername();
		$loggedin = $this->loggedIn->getUsername();
		
		$count = 1;
		$total = sizeof($this->getUidList());
		foreach ($this->getUidList() as $uid) {
			printf("\n<br>". gTranslate('core', "Checking user %d of %d . . . .") .' ', $count++, $total);
			$user = $this->getUserByUid($uid, true);
			if ($user->username == $nobody ||
			    $user->username == $everybody ||
			    $user->username == $loggedin) {
				printf(gTranslate('core', "Skipped %s (Reserved username from Gallery)"), $user->username);
				continue;
			}
			if (!$user->integrityCheck()) {
				$success = false;
			}
		}
		$this->version=$gallery->user_version;
		if ($success) {
			$this->rebuildUserMap();
			if (!$this->save()) {
				$success = false;
			}
		}

		return $success;
	}

	function CreateUser($uname, $email, $new_password, $fullname, $canCreate, $language, $log) {
		global $gErrors;
	       	$errorCount = 0;
	       	$gErrors = array();
	       	$gErrors["uname"] = $this->validNewUserName($uname);
	       	if ($gErrors["uname"]) {
		       	$errorCount++;
	       	} else {
		       	$gErrors["new_password"] = $this->validPassword($new_password);
		       	if ($gErrors["new_password"]) {
			       	$errorCount++;
		       	}
	       	}

		if (!$errorCount) {
		       	$tmpUser = new Gallery_User();
		       	$tmpUser->setUsername($uname);
		       	$tmpUser->setPassword($new_password);
		       	$tmpUser->setFullname($fullname);
		       	$tmpUser->setCanCreateAlbums($canCreate);
		       	$tmpUser->setEmail($email);
		       	$tmpUser->setDefaultLanguage($language);
			$tmpUser->origEmail=$email;
		       	$tmpUser->log($log);
		       	$tmpUser->save();
		       	return $tmpUser;
	       	} else {
			processingMsg( "<b>" . sprintf(gTranslate('core', "Problem adding %s:"), $uname)."</b>");
		       	foreach ($gErrors as $key_var => $value_var) {
			       	echo "\n<br>". gallery_error($gErrors[$key_var]);
		       	}
		       	return false;
	       	}
       	}

	/*
	 * Since user_version == 4, we've replaced ':' and ';' with '_'
	 * in the user IDs, so convert any old values
	 */
	function convertUidToNewFormat($uid) {
		return strtr($uid, ':;', '__');
	}
}

?>
