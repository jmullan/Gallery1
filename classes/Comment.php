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
class Comment {
	var $commentText;	// text of comment 
	var $datePosted; 	// time() the comment was entered
	var $IPNumber; 		// IP number from which the comment was posted
	var $name;		// name or email of person who entered comment
	var $UID;		// UID of person who entered comment

	function Comment($commentText, $IPNumber, $name, $UID="") {

		$this->commentText = substr(wordwrap($commentText, 100, " ", 1), 0, 1000);
		$this->datePosted = time();
		$this->IPNumber = $IPNumber;
		$this->name = substr($name, 0, 100);
		$this->UID = $UID;
	}

	function getCommentText() {
		return nl2br($this->commentText);
	}

	function getDatePosted() {
		global $gallery;
		$time = $this->datePosted;
		return strftime($gallery->app->dateTimeString, $time);
	}

	function getIPNumber() {
		return $this->IPNumber;
	}

	function getName() {
		global$gallery;
		
		$name = '';
		$user = $gallery->userDB->getUserByUid($this->UID);
		
		if  ($gallery->app->comments_anonymous == 'no') {
		    $name = $user->printableName($gallery->app->comments_display_name);
		}
		
		if (!$name) {
			$name = $this->name;
		}
		return $name;
	}
	
	function getUID() {
		return $this->UID;
	}
}
?>
