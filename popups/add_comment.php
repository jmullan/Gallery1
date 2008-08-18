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

require_once(dirname(dirname(__FILE__)) . '/init.php');

list($save, $id, $commenter_name, $comment_text) =
	getRequestVar(array('save', 'id', 'commenter_name', 'comment_text'));


if(empty($gallery->album) ||
   $gallery->album->getPhotoIndex($id) == -1)
{
   	printPopupStart(gTranslate('core', "Add comment"));
   	showInvalidReqMesg();
   	exit;
}

if (!$gallery->user->canAddComments($gallery->album)) {
	printPopupStart(gTranslate('core', "Add comment"));
	showInvalidReqMesg(gTranslate('core', "You are not allowed to perform this action!"));
   	exit;
}

$notice_messages = array();

require(dirname(dirname(__FILE__)) . '/includes/comments/commentHandling.inc.php');

if (isset($reload)) {
	// Note: In stats.php this causes the browser to show a message about POST data ...
	dismissAndReload();
}

printPopupStart(gTranslate('core', "Add comment"));

echo "\n<p>". gTranslate('core', "Enter your comment in the text box below.") . '</p>';

echo $gallery->album->getThumbnailTagById($id);

echo infoBox($comment_messages);

echo makeFormIntro("add_comment.php", array(), array('type' => 'popup'));

drawCommentAddForm($commenter_name, 35, $comment_text);
?>
<input type="hidden" name="id" value="<?php echo $id ?>">

<br><?php echo gButton('cancelButton', gTranslate('core', "_Cancel"), 'parent.close()'); ?>

</form>
</div>
<script language="javascript1.2" type="text/JavaScript">
<!--
  // position cursor in top form field
  document.g1_form.commenter_name.focus();
//-->
</script>

</body>
</html>