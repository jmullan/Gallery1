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
 *
 * $Id$
 */
?>
<?php
// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
	print _("Security violation") . "\n";
	exit;
}
?>
<?php if (!isset($GALLERY_BASEDIR)) {
    $GALLERY_BASEDIR = './';
}
require($GALLERY_BASEDIR . 'init.php'); ?>
<?php
// Hack check

if (strcmp($gallery->album->fields["public_comments"], "yes")) {
	exit;
}

$error_text = "";

if (isset($save)) {
	if ($commenter_name && $comment_text) {
	        $comment_text = removeTags($comment_text);
	        $commenter_name = removeTags($commenter_name);
		$IPNumber = $HTTP_SERVER_VARS['REMOTE_ADDR'];
		$gallery->album->addComment($index, stripslashes($comment_text), $IPNumber, $commenter_name);
		$gallery->album->save();
		dismissAndReload();
		return;
	} else {
		$error_text = _("Name and comment are both required to save a new comment!");
	}
}
?>
<html>
<head>
  <title><?php echo _("Add Comment") ?></title>
  <?php echo getStyleSheetLink() ?>
</head>
<body dir=<?php echo $gallery->direction ?>>

<center>
<?php echo _("Enter your comment for this picture in the text box below.") ?>
<br><br>
<?php echo $gallery->album->getThumbnailTag($index) ?>
<?php
if ($error_text) {
?>
<br><br>
<span class=error><?php echo $error_text ?></span>
<br><br>
<?php
}
?>

<?php echo makeFormIntro("add_comment.php", array("name" => "theform", "method" => "POST")); ?>
<input type=hidden name="index" value="<?php echo $index ?>">
<table border=0 cellpadding=5>
<tr>
  <td><?php echo _("Name or email:") ?></td>
  <td><input name="commenter_name" value="<?php echo $commenter_name ?>" size=30></td>
</tr>
<tr>
  <td colspan=2><textarea name="comment_text" rows=5 cols=40><?php echo $comment_text ?></textarea></td>
</tr>
</table>
<br>
<input type="submit" name="save" value="<?php echo _("Save") ?>">
<input type=button value="<?php echo _("Cancel") ?>" onclick='parent.close()'>

</form>

<script language="javascript1.2">
<!--   
// position cursor in top form field
document.theform.commenter_name.focus();
//-->
</script>

</body>
</html>
