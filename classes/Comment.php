<?
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2001 Bharat Mediratta
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
class Comment {
	var $commentText;	// text of comment 
	var $datePosted; 	// time() the comment was entered
	var $IPNumber; 		// IP number from which the comment was posted
	var $name;		// name or email of person who entered comment
	var $UID;		// UID of person who entered comment

	function Comment($commentText, $IPNumber, $name, $UID="") {

		$this->commentText = $commentText;
		$this->datePosted = time();
		$this->IPNumber = $IPNumber;
		$this->name = $name;
		$this->UID = $UID;
	}

	function getCommentText() {
		return $this->commentText;
	}

	function getDatePosted() {
		$time = $this->datePosted;
		return date("M d, Y H:i T", $time);
	}

	function getIPNumber() {
		return $this->IPNumber;
	}

	function getName() {
		return $this->name;
	}
	
	function getUID() {
		return $this->UID;
	}
}
?>
