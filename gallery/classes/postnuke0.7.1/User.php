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
*
* $Id$
*/
?>
<?php
class PostNuke_User extends Abstract_User {
	function loadByUid($uid) {
		/*
		* Consider the case where we're trying to load a $uid
		* that stemmed from a user created by a standalone
		* Gallery.  That $uid won't be valid for PostNuke.
		* We don't want this to cause problems, so in that
		* case we'll just pretend that it was Nobody.
		*
		* But how do we detect those users?  Well, let's take
		* the quick and dirty approach of making sure that
		* the uid is numeric.
		*/
		if (ereg("[^0-9]", $uid)) {
			$newuser = new NobodyUser();
			foreach ($newuser as $k => $v) {
				$this->$k = $v;
			}
			return;
		}

		$this->username = pnUserGetVar('uname', $uid);
		$this->fullname = pnUserGetVar('name', $uid);
		$this->email = pnUserGetVar('email', $uid);
		$this->canCreateAlbums = 0;
		$this->uid = $uid;

		/*
		* XXX: this sets the admin-ness according to the user who's
		* currently logged in -- NOT the $uid in question!  This would
		* be an issue, except that it just so happens that it doesn't
		* affect anything we're doing in the app level code.
		*/
		$modname = pnModGetName(); /* Gallery PN module name */
		$this->isAdmin = (pnSecAuthAction(0, "$modname::", '::', ACCESS_ADMIN));
	}

	function loadByUserName($uname) {
		if (substr(_PN_VERSION_NUM, 0, 7) < "0.7.5.0") {
			list($dbconn) = pnDBGetConn();
			$pntable = pnDBGetTables();
		} else {
			$dbconn =& pnDBGetConn(true);
			$pntable =& pnDBGetTables();
		}

		$userscolumn = &$pntable['users_column'];
		$userstable = $pntable['users'];

		/* Figure out the uid for this uname */
		$query = "SELECT $userscolumn[uid] " .
		"FROM $userstable " .
		"WHERE $userscolumn[uname] = '" . pnVarPrepForStore($uname) ."'";

		$result = $dbconn->Execute($query);
		list($uid) = $result->fields;
		$result->Close();

		$this->loadByUid($uid);
	}
}

?>
