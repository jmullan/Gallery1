<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2006 Bharat Mediratta
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

require_once(dirname(dirname(__FILE__)) . '/init.php');
require_once(dirname(dirname(__FILE__)) . '/classes/hn_captcha/hn_captcha.class.x1.php');
require_once(dirname(dirname(__FILE__)) . '/includes/captcha/captcha_init.php');

/* Hack check*/
if (!$gallery->user->canAddComments($gallery->album)) {
	echo gTranslate('core', "You are not allowed to perform this action!");
	exit;
}

list($save, $id, $commenter_name, $comment_text) =
    getRequestVar(array('save', 'id', 'commenter_name', 'comment_text'));

$captcha =& new hn_captcha_X1($CAPTCHA_INIT);

$notice_messages = array();

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
    	$notice_messages[] = array(
                    'type' => 'error',
                    'text' => gTranslate('core', "Name and comment are both required to save a new comment!")
    	);
    }

    if ($maxlength >0 && strlen($comment_text) > $maxlength) {
    	$notice_messages[] = array(
                    'type' => 'error',
                    'text' => sprintf(gTranslate('core', "Your comment is too long, the admin set maximum length to %d chars"), $maxlength)
    	);
    }

    if (isBlacklistedComment($tmp = array('commenter_name' => $commenter_name, 'comment_text' => $comment_text), false)) {
    	$notice_messages[] = array(
                    'type' => 'error',
                    'text' => gTranslate('core', "Your Comment contains forbidden words. It will not be added.")
    	);
    }


    switch($captcha->validate_submit()) {
        case 1:
            // PUT IN ALL YOUR STUFF HERE //
            echo "<p><br>Congratulation. You will get the resource now.";
            echo "<br><br><a href=\"".$_SERVER['PHP_SELF']."?download=yes&id=1234\">New DEMO</a></p>";
            break;

        case 2:
            $notice_messages[] = array(
                'type' => 'error',
                'text' => gTranslate('core', "You didn't enter the correct chars/numbers.")
            );
            break;
    }

    // Everything went fine, add the comment
    if(empty($notice_messages)) {
    	$comment_text = strip_tags($comment_text);
    	$commenter_name = strip_tags($commenter_name);
    	$IPNumber = $_SERVER['REMOTE_ADDR'];
    	$gallery->album->addComment($id, $comment_text, $IPNumber, $commenter_name);

    	$gallery->album->save();
    	emailComments($id, $comment_text, $commenter_name);

    	// Note: In stats.php this causes the browser to show a message about POST data ...
    	dismissAndReload();
    	return;
    }
}
printPopupStart(gTranslate('core', "Add Comment"));

echo "\n<p>". gTranslate('core', "Enter your comment for this picture in the text box below.") . '</p>';

echo $gallery->album->getThumbnailTagById($id);

echo infoBox($notice_messages);

echo makeFormIntro("add_comment.php", array(), array('type' => 'popup'));

drawCommentAddForm($commenter_name, 35);
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