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
 * $Id$
 */

/**
 * Processes given comment information.
 * @package Comments
 */
$comment_messages = array();

if($gallery->user->canAddComments($gallery->album)) {
	if(enableCaptcha()) {
		/**
		 * Captcha classes.
		 */
		require(dirname(dirname(dirname(__FILE__))) . '/classes/hn_captcha/hn_captcha.class.x1.php');
		require(dirname(dirname(__FILE__)) . '/captcha/captcha_init.php');
		$captcha =& new hn_captcha_X1($CAPTCHA_INIT);
	}

	if ($gallery->user->isLoggedIn() ) {
		if (empty($commenter_name) || $gallery->app->comments_anonymous == 'no') {
		   $commenter_name = $gallery->user->printableName($gallery->app->name_display);
		}
	}
	elseif (!isset($commenter_name)) {
		$commenter_name = '';
	}

	if (empty($comment_text)) {
		$comment_text = '';
	}

	$maxlength = isset($gallery->app->comments_length) ? $gallery->app->comments_length : 0;

	if (isset($save)) {
		if (empty($commenter_name) || empty($comment_text)) {
			$comment_messages[] = array(
				'type' => 'error',
				'text' => gTranslate('core', "Name and comment are both required to save a new comment!")
			);
		}

		if ($maxlength >0 && strlen($comment_text) > $maxlength) {
			$comment_messages[] = array(
				'type' => 'error',
				'text' => sprintf(gTranslate('core', "Your comment is too long, the admin set maximum length to %d chars"), $maxlength)
			);
		}

		if (!empty($comment_text) &&
			isBlacklistedComment($tmp = array('commenter_name' => $commenter_name, 'comment_text' => $comment_text), false))
		{
			$comment_messages[] = array(
				'type' => 'error',
				'text' => gTranslate('core', "Your comment contains forbidden words. It will not be added.")
			);
		}

		if(enableCaptcha() && $captcha->validate_submit() == 2) {
			$comment_messages[] = array(
				'type' => 'error',
				'text' => gTranslate('core', "You didn't enter the correct chars/numbers.")
			);
		}


		// Everything went fine, add the comment
		if(empty($comment_messages)) {
			$comment_text = strip_tags($comment_text);
			$commenter_name = strip_tags($commenter_name);
			$IPNumber = $_SERVER['REMOTE_ADDR'];
			$gallery->album->addComment($id, $comment_text, $IPNumber, $commenter_name);

			$gallery->album->save();
			emailComments($id, $comment_text, $commenter_name);
			$reload = true;
		}
	}
}

?>