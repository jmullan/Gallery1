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
 */
?>
<?php

class Geeklog_User extends Abstract_User {

var $isGroup;
 
function Geeklog_User() {
	global $gallery;
}

function loadByUid($uid) {
   global $_TABLES;

   if ($uid > 0) {
      $result = DB_query("SELECT username,fullname,email " .
          "FROM {$_TABLES['users']} WHERE uid = '$uid'");
      $userInfo = DB_fetchArray($result);

      $this->uid = $uid;
      $this->username = $userInfo['username'];
      $this->fullname = $userInfo['fullname'];
      $this->email = $userInfo['email'];
      $this->isAdmin = SEC_inGroup('Root', $uid);
      $this->canCreateAlbums = $this->canCreateAlbums();
      $this->isGroup = 0;
   } else {
      $result = DB_query("SELECT grp_name " .
          "FROM {$_TABLES['groups']} WHERE grp_id = '" . abs($uid) . "'");
      $userInfo = DB_fetchArray($result);

      $this->uid = $uid;
      $this->username = $userInfo['grp_name'];
      $this->fullname = $userInfo['grp_name'] . " Group";
      $this->email = '';
      $this->isAdmin = false;
      $this->canCreateAlbums = 0;
      $this->isGroup = 1;
   }
}

function loadByUserName($uname) {
	global $_TABLES;

	$result = DB_query("SELECT uid,username,fullname,email FROM ". $_TABLES['users'] . 
			   " WHERE username = '$uname'");
   
	$userInfo = DB_fetchArray($result);

	$this->uid = $userInfo['uid'];
	$this->username = $userInfo['username'];
	$this->fullname = $userInfo['fullname'];
	$this->email = $userInfo['email'];
	$this->isAdmin = SEC_inGroup('Root', $this->uid);
	$this->canCreateAlbums = $this->canCreateAlbums();
}

function isLoggedIn() {
   if ($this->uid > 1) {
      return true;
   }
   return false;
}

function isCorrectPassword($password) {
	# Get the user's password hash from Geeklog
	$gl_passwd = COM_getpassword($this->username);
	return (!strcmp($gl_passwd, md5($password)));
} 

}

?>
