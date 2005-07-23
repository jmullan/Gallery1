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
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */
?>
<?php

class Geeklog_UserDB extends Abstract_UserDB {
 var $db;

 function Geeklog_UserDB() {
   global $gallery;

   $this->nobody = new NobodyUser();
   $this->everybody = new EverybodyUser();

/* New Property with Gallery 1.3 - created by object classes/gallery/UserDB  */
/* May 08/2002: Blaine Lang  */

   $this->loggedIn = new LoggedInUser();

 }

 function getUidList() {
   global $_TABLES;

   $uidList = array();

   $result = DB_query("SELECT uid FROM {$_TABLES['users']} WHERE uid > 1 AND passwd <> '" . md5('') . "' ORDER BY username");
   $nrows = DB_numRows($result);

   for ($i = 0; $i < $nrows; $i++) {
     $A = DB_fetchArray($result);
     array_push($uidList, $A['uid']);
   }

   $result = DB_query("SELECT grp_id FROM {$_TABLES['groups']} WHERE grp_id > 2 AND grp_id <> 13 ORDER BY grp_name");
   $nrows = DB_numRows($result);

   for ($i = 0; $i < $nrows; $i++) {
     $A = DB_fetchArray($result);
     array_push($uidList, 0 - $A['grp_id']);
   }
   
   array_push($uidList, $this->nobody->getUid());
   array_push($uidList, $this->everybody->getUid());
   array_push($uidList, $this->loggedIn->getUid());

   return $uidList;
 }

 function getUserByUsername($username, $level=0) {
	global $uid;
	if (!strcmp($username, $this->nobody->getUsername())) {
		return $this->nobody;
	} else if (!strcmp($username, $this->everybody->getUsername())) {
		return $this->everybody;
	} else if (!strcmp($uid, $this->loggedIn->getUid())) {
		return $this->loggedIn;
	}

	$user = new Geeklog_User();
	$user->loadByUsername($username);

return $user;
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

   $user = new Geeklog_User();
   $user->loadByUid($uid);
   return $user;
 }
}

?>
