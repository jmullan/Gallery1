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

/* This class is written for phpBB2 and provides full integration of the phpbb users database
** Instead of using or duplicating memberships manually in Gallery.
**
** Gallery <-> phpBB2 integration ver. (www.snailsource.com)
** Written by Martin Smallridge       <info@snailsource.com>
**
** This file was modified for official integration into Gallery 1.4.3 by
** Jens Tkotz
*/

class phpbb_User extends Abstract_User {
	var $db;

	function phpbb_User() {
		global $gallery, $userdata;
		$this->db = $gallery->database{"phpbb"};
	}

	function loadByUid($uid) {
		global $userdata, $table_prefix;
		$sql = "SELECT username, user_email FROM ".$table_prefix."users WHERE user_id='$uid'";
		$results = $this->db->query($sql);
		$row = $this->db->fetch_row($results);
		$this->username = $row[0];
		$this->fullname = $row[0];
		$this->email = $row[1];
		$this->uid = $uid;

		if ($userdata['user_level'] == '1') {
			$this->isAdmin = 1;
			$this->canCreateAlbums = 1;
		}
/* Commented out by Jens Tkotz
		else {
			// Not an Admin so Check if User ID is in the Gallery User Group (ie: can create albums)
			$gallery_perm = $this->galleryperm($userdata['user_id']);

			// Defaults
			$this->isAdmin = 0;
			$this->canCreateAlbums = 0;

			if ($gallery_perm == 2) {
				$this->isAdmin = 1;
			}
			if ($gallery_perm >= 1) {
				$this->canCreateAlbums = 1;
			}
		}
*/
	}

	function loadByUserName($uname) {
		global $userdata, $table_prefix;
		$results = $this->db->query("SELECT user_id, user_email FROM ".$table_prefix."users WHERE username='$uname'");
		$row = $this->db->fetch_row($results);
		$this->uid = $row[0];
		$uid = $row[0];
		$this->fullname = $uname;
		$this->email = $row[1];
		$this->username = $uname;

		if ($userdata['user_level'] == '1') {
			$this->isAdmin = 1;
			$this->canCreateAlbums = 1;
		}
/* Commented out by Jens Tkotz
		else {
			// Not an Admin so Check if User ID is in the Gallery User Group (ie: can create albums)
			$gallery_perm = $this->galleryperm($userdata['user_id']);

			// Defaults
			$this->isAdmin = 0;
			$this->canCreateAlbums = 0;

			if ($gallery_perm == 2) {
				$this->isAdmin = 1;
			}
			if ($gallery_perm >= 1) {
				$this->canCreateAlbums = 1;
			}
		}
*/
	}

/* Commented out by Jens Tkotz
	function galleryperm($user_id) {
		global $table_prefix, $db;

		// NB: We use the phpBB2 database object as it allows the use of named fields (easier to debug!)

		$gallery_perm = 0; // Set default
		// Get the user permissions first.
		$sql = "SELECT user_gallery_perm FROM " . $table_prefix. "users WHERE user_id = '$user_id'";
		if ( !($result = $db->sql_query($sql)) ) {
			message_die(GENERAL_ERROR, 'Could not select Gallery permission from user table', '', __LINE__, __FILE__, $sql);
		}
		$row = $db->sql_fetchrow($result);

		// Get the group permissions second.
		$sql2 = "SELECT group_gallery_perm FROM " . $table_prefix. "user_group ug, " . $table_prefix. "groups g 
			WHERE ug.user_id = '$user_id' AND g.group_id = ug.group_id";
		if ( !($result2 = $db->sql_query($sql2)) ) {
			message_die(GENERAL_ERROR, 'Could not select Gallery permission from user, usergroup table', '', __LINE__, __FILE__, $sql2);
		}
		$topgroup = 0;
		while($rowg = $db->sql_fetchrow($result2)) {
			if($topgroup < $rowg['group_gallery_perm']) {
				$topgroup = $rowg['group_gallery_perm']; 
			}
		}

		// Use whichever value is highest.
		if ($topgroup > $row['user_gallery_perm']) {
			$gallery_perm = $topgroup;
		}
		else {
			$gallery_perm = $row['user_gallery_perm'];
		}
	        return $gallery_perm;
	}
*/

	function isLoggedIn() {
		if ($this->uid != -1) {
			return true;
		} else {
			return false;
		}
	}
}

?>