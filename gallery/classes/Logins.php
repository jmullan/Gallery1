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
 * $Id: Album.php 17321 2007-12-29 07:17:50Z JensT $
*/

/**
 * Class to log, manipulate, check etc. login attempts.
 *
 * @author Jens Tkotz
 */
class Logins {

	var $attempts;		// array('number', 'lasttry')
	var $maxtries;		// Number of login tries after which an account is locked.
	var $expireTime;   	// Time in seconds when a lock is removed
	var $filename;		// File where the logins are protocolled

	/**
	 * Constructor with defaults.
	 *
	 * @return Logins
	 * @author Jens Tkotz
	 */
	function Logins() {
		global $gallery;

		$dir = $gallery->app->albumDir;

		$this->attempts		= array();
		$this->maxtries		= 10;
		$this->expireTime	= 1 * 60 * 60; // Default 1 hour.
		$this->filename		= "$dir/logins.dat";
	}

	/**
	 * Add a login try for a username.
	 *
	 * @param string $username
	 * @author Jens Tkotz
	 */
	function addLoginTry($username) {
		if(!isset($this->attempts[$username])) {
			$this->attempts[$username] = array(
				'tries' => 1,
				'lasttry' => time()
			);
		}
		else {
			$this->attempts[$username]['tries']++;
			$this->attempts[$username]['lasttry'] = time();
		}
	}

	/**
	 * Remove all login attemps for a username (or array of usernames)
	 *
	 * @param mixed $username
	 * @author Jens Tkotz
	 */
	function reset($username) {
		if(is_array($username)) {
			foreach ($username as $uname) {
				$this->reset($uname);
			}
			return;
		}

		if(empty($this->attempts[$username])) {
			return;
		}
		else {
			unset($this->attempts[$username]);
		}
	}

	/**
	 * A username is locked when the number of login attempts is greater
	 * than number of maxtries set in the constructore
	 *
	 * @param string $username
	 * @return boolean
	 * @author Jens Tkotz
	 */
	function userIslocked($username) {
		if(!isset($this->attempts[$username])) {
			return false;
		}
		elseif ($this->attempts[$username]['tries'] < $this->maxtries) {
			return false;
		}
		else {
			return true;
		}
	}

	/**
	 * The lock for a username expires when the expireTime from constructor has went by.
	 * than number of maxtries set in the constructore
	 *
	 * @param string $username
	 * @return boolean
	 * @author Jens Tkotz
	 */
	function lockIsExpired($username) {
		if (time() - $this->attempts[$username]['lasttry'] > $this->expireTime) {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Checks for every username if the username is locked and expired.
	 * If so, the login attempts are resetted.
	 *
	 * After this procedure, the list is saved.
	 *
	 * @author Jens Tkotz
	 */
	function cleanup() {
		if(empty($this->attempts)) {
			return;
		}

		foreach($this->attempts as $username => $values) {
			if($this->userIslocked($username) && $this->lockIsExpired($username)) {
				$this->reset($username);
			}
		}

		$this->save();
	}

	/**
	 * Load the attempts from the disk
	 *
	 * @author Jens Tkotz
	 */
	function load() {
		$tmp = fs_file_get_contents($this->filename);

		if (!empty($tmp)) {
			$this->attempts = unserialize($tmp);

			if (empty($this->attempts)) {
				$this->attempts = array();
			}
		}
	}

	/**
	 * Save the attempts to the disk
	 *
	 * @return boolean    True on success, false otherwise.
	 * @author Jens Tkotz
	 */
	function save() {
		$ret = unsafe_serialize($this->attempts, $this->filename);

		return $ret;
	}
}