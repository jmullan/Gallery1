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
 */
?>
<?php
class NobodyUser extends Abstract_User {
	function NobodyUser() {
		$this->username = "NOBODY";
		$this->fullname = "Nobody";
		$this->setIsAdmin(false);
		$this->setCanCreateAlbums(false);
		$this->uid = "nobody";
	}

	function isLoggedIn() {
		return false;
	}

	function isPseudo() {
		return true;
	}
}
?>
