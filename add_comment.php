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

require_once(dirname(__FILE__) . '/init.php');

/* Hack check*/
if (!$gallery->user->canAddComments($gallery->album)) {
	echo _("You are not allowed to perform this action!");
	exit;
}

list($save, $id, $commenter_name, $comment_text) = getRequestVar(array('save', 'id', 'commenter_name', 'comment_text'));

$error_text = '';
if ($gallery->user->isLoggedIn() ) {
	if (empty($commenter_name) || $gallery->app->comments_anonymous == 'no') {
		$commenter_name = $gallery->user->printableName($gallery->app->comments_display_name);
	}
} elseif (!isset($commenter_name)) {
	$commenter_name = '';
}

if (empty($comment_text)) {
	$comment_text = '';
}

if (isset($gallery->app->comments_length)) {
	$maxlength = $gallery->app->comments_length;
} else {
	$maxlength = 0;
}

if (isset($save)) {
	if ( empty($commenter_name) || empty($comment_text)) {
		$error_text = _("Name and comment are both required to save a new comment!");
	} elseif ($maxlength >0 && strlen($comment_text) > $maxlength) {
		$error_text = sprintf(_("Your comment is too long, the admin set maximum length to %d chars"), $maxlength);
	} elseif (isBlacklistedComment($tmp = array('commenter_name' => $commenter_name, 'comment_text' => $comment_text), false)) {
		$error_text = _("Your Comment contains forbidden words. It will not be added.");
	} else {
		$comment_text = removeTags($comment_text);
		$commenter_name = removeTags($commenter_name);
		$IPNumber = $_SERVER['REMOTE_ADDR'];
		$gallery->album->addComment($id, $comment_text, $IPNumber, $commenter_name);
		
		$gallery->album->save();
		emailComments($id, $comment_text, $commenter_name);
		
		// Note: In stats.php this causes the browser to show a message about POST data ...
		dismissAndReload();
		return;
	}
}
doctype();
?>
<html>
<head>
  <title><?php echo _("Add Comment") ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>" class="popupbody">
<div class="popuphead"><?php echo _("Add Comment") ?></div>
<div class="popup" align="center">
<p><?php echo _("Enter your comment for this picture in the text box below.") ?></p>

<?php 
echo $gallery->album->getThumbnailTagById($id);
if (!empty($error_text)) {
	echo "\n<br>". gallery_error($error_text);
}
echo "<br><br>";
echo makeFormIntro("add_comment.php",
	array("name" => "theform", "method" => "POST"),
	array('type' => 'popup'));

drawCommentAddForm($commenter_name, 35);
?>
<input type="hidden" name="id" value="<?php echo $id ?>">
<br><input type="button" value="<?php echo _("Cancel") ?>" onclick='parent.close()'>

</form>
</div>
<script language="javascript1.2" type="text/JavaScript">
<!--
// position cursor in top form field
document.theform.commenter_name.focus();
//-->
</script>
</div>
<?php print gallery_validation_link("add_comment.php", true, array('id' => $id)); ?>
</body>
</html>