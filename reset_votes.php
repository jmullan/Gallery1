<?php
/*
   $Id$

 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2004 Bharat Mediratta
 *
 * This file Copyright (C) 2003-2004 Joan McGalliard
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
// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
	print _("Security violation") . "\n";
	exit;
}

if (!isset($GALLERY_BASEDIR)) {
    $GALLERY_BASEDIR = './';
}

require(dirname(__FILE__) . '/init.php');

if (isset($id)) {
        $index = $gallery->album->getPhotoIndex($id);
}

// Hack check
if (!$gallery->user->canDeleteFromAlbum($gallery->album) && !$gallery->album->isItemOwner($gallery->user, $index)) {
	exit;
}

if (isset($confirm) && $confirm) {
	$gallery->album->fields["votes"]=array();
	$gallery->album->save(array(i18n("All votes removed")));
	dismissAndReload();
	return;
}
?>

<html>
<head>
  <title><?php echo _("Reset Voting") ?></title>
  <?php echo getStyleSheetLink() ?>
</head>
<body>

<center>
<?php echo sprintf(_("Do you really want to remove all votes in %s?"), "<b>{$gallery->album->fields['title']}</b>") ?>
<br>
<br>
<?php echo makeFormIntro("reset_votes.php"); ?>
<input type=submit name=confirm value="<?php echo _("Remove Votes") ?>">
<input type=submit value="<?php echo _("Cancel") ?>" onclick='parent.close()'>
</form>
<br>

</body>
</html>
