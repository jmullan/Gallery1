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
class Abstract_User {
    var $username;
    var $fullname;
    var $password;
    var $email;
    var $isAdmin;
    var $canCreateAlbums;
    var $uid;
    var $canChangeOwnPw;

    function Abstract_User() {
        $this->setIsAdmin(false);
        $this->setCanCreateAlbums(false);
        $this->uid = time() . "_" . mt_rand();
    }

    function integrityCheck() {
        return 0;
    }
    function versionOutOfDate() {
        return false;
    }

    function salt($len = 4)
    {
        $salt = '';
        for($i = 0; $i < $len; $i++)
        {
            $char = mt_rand(48, 109);
            if($char > 57) $char += 7;
            if($char > 90) $char += 6;
            $salt .= chr($char);
        }
        return $salt;
    }

    function setPassword($password) {
        $salt = $this->salt();
        $this->password = $salt.md5($salt.$password);
    }

    function isCorrectPassword($password) {
        $hash = '';
        if(strlen($this->password) == 32) { // old password schema
        $hash =  md5($password);
        }
        else {
            $salt = substr($this->password,0, 4);
            $hash = $salt.md5($salt.$password);
        }
        return (!strcmp($this->password, $hash));
    }

    function getUid() {
        return $this->uid;
    }

    function setUsername($username) {
        $this->username = $username;
    }

    function getUsername() {
        return $this->username;
    }

    function printableName($format = '!!FULLNAME!! (!!USERNAME!!)') {
        if (empty($this) || $this->isPseudo()) {
            return '';
        }
        else {
            $name = $format;
            $name = str_replace('!!FULLNAME!!', $this->getFullName(), $name);
            $name = str_replace('!!USERNAME!!', $this->getUsername(), $name);
            $name = str_replace('!!EMAIL!!',$this->getEmail(), $name);
            $name = str_replace('!!MAILTO_FULLNAME!!', '<a href="mailto:' . $this->getEmail().'">'.
              $this->getFullName() ? $this->getFullName() : $this->getUserName() . '</a>', $name);
            $name = str_replace('!!MAILTO_USERNAME!!',
              '<a href="mailto:'. $this->getEmail().'">'.$this->getUserName() . '</a>', $name);
        }

        if(empty($name)) {
            $name = $this->username;
        }
        return $name;
    }

    function setEmail($email) {
        $this->email = $email;
    }

    function getEmail() {
        return $this->email;
    }

    function setFullName($fullname) {
        $this->fullname = $fullname;
    }

    function getFullName() {
        if (get_magic_quotes_gpc()) {
            return stripslashes($this->fullname);
        } else {
            return $this->fullname;
        }
    }

    function isAdmin() {
        return $this->isAdmin;
    }

    function isPseudo() {
        return false;
    }

    function setIsAdmin($bool) {
        $this->isAdmin = $bool;
    }

    function setCanChangeOwnPw($bool) {
        $this->canChangeOwnPw = $bool;
    }

    function canChangeOwnPw() {
        return $this->canChangeOwnPw;
    }

    function canReadAlbum($album) {
        if ($this->isAdmin()) {
            return true;
        }

        if ($album->canRead($this->uid)) {
            return true;
        }

        return false;
    }

    function canWriteToAlbum($album) {
        if ($this->isAdmin()) {
            return true;
        }

        if ($album->canWrite($this->uid)) {
            return true;
        }

        return false;
    }

    function canAddToAlbum($album) {
        if ($this->isAdmin()) {
            return true;
        }

        if (!$album)
        {
            return false;
        }

        // If they can write, they can add
        if ($this->canWriteToAlbum($album)) {
            return true;
        }

        if ($album->canAddTo($this->uid)) {
            return true;
        }

        return false;
    }

    function canDeleteFromAlbum($album) {
        if ($this->isAdmin()) {
            return true;
        }

        if ($album->canDeleteFrom($this->uid)) {
            return true;
        }

        return false;
    }

    function canDeleteAlbum($album) {
        if ($this->isAdmin()) {
            return true;
        }

        if ($album->canDelete($this->uid)) {
            return true;
        }

        return false;
    }

    function canCreateSubAlbum($album) {
        if ($this->isAdmin()) {
            return true;
        }

        if ($album->canCreateSubAlbum($this->uid)) {
            return true;
        }

        return false;
    }

    function canCreateAlbums() {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->canCreateAlbums) {
            return true;
        }

        return false;
    }

    function setCanCreateAlbums($bool) {
        $this->canCreateAlbums = $bool;
    }

    function canChangeTextOfAlbum($album) {
        if ($this->isAdmin()) {
            return true;
        }

        if ($album->canChangeText($this->uid)) {
            return true;
        }

        return false;
    }

    function canViewFullImages($album) {
        if ($this->isAdmin()) {
            return true;
        }

        if ($album->canViewFullImages($this->uid)) {
            return true;
        }

        return false;
    }

    function canAddComments($album) {
        if ($this->isAdmin()) {
            return true;
        }

        if ($album->canAddComments($this->uid)) {
            return true;
        }

        return false;
    }

    function canViewComments($album) {
        if ($this->isAdmin()) {
            return true;
        }

        /** Note Jens Tkotz, 23.09.2004
         * Allow album owner to see comments, as they are a kind of local admin
         */
        if ($this->isOwnerOfAlbum($album)) {
            return true;
        }

        if ($album->canViewComments($this->uid)) {
            return true;
        }

        return false;
    }

    function isOwnerOfAlbum($album) {
        if ($album->isOwner($this->uid)) {
            return true;
        }

        return false;
    }

    function isLoggedIn() {
        return true;
    }

    function getDefaultLanguage() {
        return '';
    }

    function setDefaultLanguage($var) {
    }

    function displayName() {
        $FullName = $this->getFullName();
        if (! empty($FullName)) {
            return $this->getFullname();
        } else {
            return $this->getUsername();
        }
    }

   function canDownloadAlbum($album) {
	if ($this->hasAlbumPermission('zipDownload', $album) &&
	  canCreateArchive('zip')) {
	    return true;
	}
	else {
	    return false;
	}
    }

   function hasAlbumPermission($perm, $album) {
	/* Note: owners do not have explict every Permission via this method. Just admin. */
	if ($this->isAdmin()) {
            return true;
        }

	if ($album->getPerm($perm, $this->uid)) {
	    return true;
	}
	else {
	    return false;
	}
    }
}

?>
