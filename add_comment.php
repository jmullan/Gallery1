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
// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
	print "Security violation\n";
	exit;
}
?>
<? require($GALLERY_BASEDIR . "init.php"); ?>
<?
// Hack check

if (strcmp($gallery->album->fields["public_comments"], "yes")) {
	exit;
}

$error_text = "";

if ($save) {
	if ($commenter_name && $comment_text) {
	        $comment_text = removeTags($comment_text);
	        $commenter_name = removeTags($commenter_name);
		$gallery->album->addComment($index, stripslashes($comment_text), $IPNumber, $commenter_name);
		$gallery->album->save();
		dismissAndReload();
		return;
	} else {
		$error_text = "Name and Comment are both required to save a new comment!";
	}
}
?>
<html>
<head>
  <title>Add Comment</title>
  <?= getStyleSheetLink() ?>
</head>
<body>

<center>
Enter your comment for this picture in the text
box below.
<br><br>
<?= $gallery->album->getThumbnailTag($index) ?>
<?
if ($error_text) {
?>
<br><br>
<span class=error><?=$error_text?></span>
<br><br>
<?
}
?>

<?= makeFormIntro("add_comment.php", array("name" => "theform", "method" => "POST")); ?>
<input type=hidden name="save" value=1>
<input type=hidden name="index" value="<?= $index ?>">
<input type=hidden name="IPNumber" value="<?=$REMOTE_ADDR ?>">
<table border=0 cellpadding=5>
<tr>
  <td>Name or email:</td>
  <td><input name="commenter_name" value="<?=$commenter_name?>" size=30></td>
</tr>
<tr>
  <td colspan=2><textarea name="comment_text" rows=5 cols=40><?=$comment_text?></textarea></td>
</tr>
</table>
<br>
<input type=submit name="submit" value="Save">
<input type=submit name="submit" value="Cancel" onclick='parent.close()'>

</form>

<script language="javascript1.2">
<!--   
// position cursor in top form field
document.theform.commenter_name.focus();
//-->
</script>

</body>
</html>
