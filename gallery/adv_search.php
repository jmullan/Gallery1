<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2004 Bharat Mediratta
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

require(dirname(__FILE__) . '/init.php');

/*
!!
!!
!!
!!  2004-05-23:   adv_search.php has been broken
!!  for an unknown amount of time.  Since it has
!!  no references from outside files, it will 
!!  automatically redirect to albums.php until
!!  it can be fixed
!!
!!
!!
*/
header("Location: " . makeAlbumHeaderUrl());

function getOwnerString($owners) {
	global $gallery;
	$ownersString = "";
	$first = true;
        foreach ($owners as $uid) {
               	$tmpUser = $gallery->userDB->getUserByUid($uid);
		if (!$first) {
			$ownersString .= " or ";
		}
		$first = false;
               	$ownersString .= $tmpUser->getFullName()." (".
                       	$tmpUser->getUsername().")";
       	}
	return $ownersString;
}
function getAlbumString($albums) {
	global $gallery;
	$albumsString = "";
	$first = true;
        foreach ($albums as $albumName) {
		if (!$first) {
			$albumsString .= " or ";
		}
		$first = false;
               	$albumsString .= $albumName;
       	}
	return $albumsString;
}
?>
<?php
$borderColor = $gallery->app->default["bordercolor"];
$thumbSize = $gallery->app->default["thumb_size"];
?>
<?php if (!$GALLERY_EMBEDDED_INSIDE) {
	doctype();
?>
<html>
<head>
  <title><?php echo $gallery->app->galleryTitle ?> :: Advanced Search</title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction; ?>">
<?php } ?>

<!-- search.header begin -->
<?php 
includeHtmlWrap("search.header");
?>
<?php
if (!isset($searchstring)) { 
	$searchstring = "";
}
$searchstring = removeTags($searchstring);
?>
<!-- search.header ends -->
<!-- Top Nav -->
<?php
if (isset($go)) {
	$search_again='<a href="'.makeGalleryUrl('adv_search.php').'">' .
		_("Search Again") . '</a><p>';
	print $search_again;
}
$breadtext[0] = _("Gallery") . ": <a href=\"". makeGalleryUrl("albums.php") . "\">".$gallery->app->galleryTitle."</a>";
$breadcrumb["text"] = $breadtext;
$breadcrumb["bordercolor"] = $borderColor;
$breadcrumb["top"] = true;
$breadcrumb["bottom"] = true;
includeLayout('breadcrumb.inc');
?>
<!-- end Top Nav -->
<?php

$navigator["fullWidth"] = 100;
$navigator["widthUnits"] = "%";

$albumDB = new AlbumDB();
if (isset($albums)) {
	foreach ($albums as $albumName) {
		$album = new Album();
		$album->load($albumName);
		$list[]=$album;
	}
} else {
	$list = $albumDB->albumList;
}
$numAlbums = count($list);
$photoMatch = 0;
$albumMatch = 0;
if (!isset ($album_owners)) {
	$album_owners = NULL;
}
if (!isset ($item_owners)) {
	$item_owners = NULL;
}
if (!isset ($commenters)) {
	$commenters = NULL;
}
if (isset($go)) {
  if ($searchstring || $album_owners) {
	$adminbox["text"] = "<span class=\"admin\">Albums";
	if ($album_owners) {
		$adminbox["text"] .= " owned by ".getOwnerString($album_owners);
	}
	if (isset($albums)) {
		$adminbox["text"] .= " in ".getAlbumString($albums);
	}
	if (isset($searchstring)) {
		$adminbox["text"] .= " containing \"$searchstring\"";
	}
	$adminbox["text"] .= "</span>";
        $adminbox["bordercolor"] = $borderColor;
        $adminbox["top"] = false;
	includeLayout('adminbox.inc');
        echo "<br>";
        echo "<table width=\"".$navigator["fullWidth"] . $navigator["widthUnits"]."\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";

    	if (!$searchstring ) { // just match all album owners.
		for ($i = 0; $i<$numAlbums; $i++) {
			$searchAlbum = $list[$i];
			$uid = $gallery->user->getUid();
			if (!$searchAlbum->canRead($uid) && !$gallery->user->isAdmin()) {
				continue;
			}
			if (in_array($searchAlbum->fields["owner"], $album_owners)) {
           			$albumMatch += 1;
				$searchTitle = $searchAlbum->fields['title'];
				$searchDescription = $searchAlbum->fields['description'];
				$photoURL = makeAlbumUrl($searchAlbum->fields['name']);
				$searchdraw["bordercolor"] = $borderColor;
				$searchdraw["top"] = true;
				$searchdraw["photolink"] = $searchAlbum->getHighlightTag($thumbSize);
				$searchdraw["photoURL"] = $photoURL;
				$searchdraw["Text1"] = "<span class=title><a href=\"$photoURL\">$searchTitle</a></span>";
				$searchdraw["Text2"] = "<span class=desc>$searchDescription</span>";
				includeLayout('searchdraw.inc');	
			}
		}
	
	    }
	    else {
		print _("search albums here") . "<p>";
	    }
	    echo "<tr><td valign=top><span class=desc>".
		    pluralize_n2(ngettext("One album matches", "%d albums match", $albumMatch), $albumMatch, _("No album matches")) .".</span></td></tr>";
	    echo "</table><br>";
    }
    if ($searchstring || $item_owners) {
	$breadtext[0] = "";
        $breadcrumb["text"] = $breadtext;
	includeLayout('breadcrumb.inc');
	$adminbox["text"] = "<span class=\"admin\">Photos";
	if ($item_owners) {
		$adminbox["text"] .= " owned by ".getOwnerString($item_owners);
	}
	if (isset($albums)) {
		$adminbox["text"] .= " in ".getAlbumString($albums);
	}
	if (isset($searchstring)) {
		$adminbox["text"] .= " containing \"$searchstring\"";
	}
	$adminbox["text"] .= ".</span>";
        $adminbox["bordercolor"] = $borderColor;
        $adminbox["top"] = false;
	includeLayout('adminbox.inc');
        echo "<br>";
        echo "<table width=\"".$navigator["fullWidth"] . $navigator["widthUnits"]."\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";

    	if (!$searchstring ) { // just match all item owners.
		for ($i = 0; $i<$numAlbums; $i++) { 
			$searchAlbum = $list[$i];
			$uid = $gallery->user->getUid();
			if ($searchAlbum->canRead($uid) || $gallery->user->isAdmin()) {
				$numPhotos = $searchAlbum->numPhotos(1);
				for ($j = 1; $j <= $numPhotos; $j++) {
					if (in_array($searchAlbum->getItemOwner($j),
						     $item_owners) && 
					    (!$searchAlbum->isHidden($j) || 
					     $searchAlbum->isOwner($uid) ||
					     $gallery->user->isAdmin())) {

						$photoMatch += 1;
						$id = $searchAlbum->getPhotoId($j);
						$searchdraw["bordercolor"] = $borderColor;
						$searchdraw["top"] = true;
						$searchdraw["photolink"] = $searchAlbum->getThumbnailTag($j, $thumbSize);
						$searchdraw["photoURL"] = makeAlbumUrl($searchAlbum->fields['name'], $id);
						$searchCaption = $searchAlbum->getCaption($j);
						$searchCaption .= $searchAlbum->getCaptionName($j, true);
						$searchdraw["Text2"] = "<span class=desc>$searchCaption</span>";
						$searchdraw["Text1"] = "<span class=fineprint>From Album:&nbsp;&nbsp;<a href=\"" .  makeAlbumUrl($searchAlbum->fields['name']) . "\">" .  $searchAlbum->fields['title'] . "</a></span>";
						$searchdraw["Text3"] = "";
						$searchdraw["Text4"] = "";
						$searchdraw["Text5"] = "";
						includeLayout('searchdraw.inc');
					}     

					
				}
			}

	
	    	}
	}
	else {
		print _("search photos here") . "<p>";
	}
	    echo "<tr><td valign=top><span class=desc>".
			pluralize_n2(ngettext("One photo matches", "%d photos match", $photoMatch), $photoMatch, _("No photo matches")) .
		    "</span></td></tr>";
	    echo "</table><br>";
	}
    if ($searchstring || $commenters) {
	$breadtext[0] = "";
        $breadcrumb["text"] = $breadtext;
	includeLayout('breadcrumb.inc');
	$adminbox["text"] = "<span class=\"admin\">Comments";
	if ($commenters) {
		$adminbox["text"] .= " made by ".getOwnerString($commenters);
	}
	if (isset($albums)) {
		$adminbox["text"] .= " in ".getAlbumString($albums);
	}
	if (isset($searchstring)) {
		$adminbox["text"] .= " containing \"$searchstring\"";
	}
	$adminbox["text"] .= ".</span>";
        $adminbox["bordercolor"] = $borderColor;
        $adminbox["top"] = false;
	includeLayout('adminbox.inc');
        echo "<br>";
        echo "<table width=\"".$navigator["fullWidth"] . $navigator["widthUnits"]."\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";

    	if (!$searchstring ) { // just match all item owners.
		for ($i = 0; $i<$numAlbums; $i++) { 
			$searchAlbum = $list[$i];
			$uid = $gallery->user->getUid();
			if ($searchAlbum->canRead($uid) || $gallery->user->isAdmin()) {
				$numPhotos = $searchAlbum->numPhotos(1);
				for ($j = 1; $j <= $numPhotos; $j++) {
					    if ($searchAlbum->isHidden($j) && 
					     !$searchAlbum->isOwner($uid) &&
					     !$gallery->user->isAdmin()) {
					     	continue;
					     }	
					$commentText = "";
					$commentMatch = 0;
					for ($k = 1; $k <= $searchAlbum->numComments($j); $k++) {
						$comment=$searchAlbum->getComment($j, $k);
						if (in_array($comment->getUID($j),
						     $commenters)) { 

						$commentMatch += 1;
						$commentText .= $comment->getCommentText()."<br><br>\n";
						}
					}
					if (strlen($commentText) == 0) {
						continue;
					}
					$id = $searchAlbum->getPhotoId($j);
					$searchdraw["bordercolor"] = $borderColor;
					$searchdraw["top"] = true;
					$searchdraw["photolink"] = $searchAlbum->getThumbnailTag($j, $thumbSize);
					$searchdraw["photoURL"] = makeAlbumUrl($searchAlbum->fields['name'], $id);
					$searchCaption = $searchAlbum->getCaption($j);
					$searchCaption .= $searchAlbum->getCaptionName($j, true);
					$searchdraw["Text2"] = "<span class=desc>$searchCaption</span>";
					$searchdraw["Text1"] = "<span class=fineprint>From Album:&nbsp;&nbsp;<a href=\"" .  makeAlbumUrl($searchAlbum->fields['name']) . "\">" .  $searchAlbum->fields['title'] . "</a></span>";
					$searchdraw["Text3"] = "";
					$searchdraw["Text4"] = "";
					$searchdraw["Text5"] = $commentText;
					includeLayout('searchdraw.inc');
			}     

					
			}

	
	    	}
	}
	else {
		print _("search comments here") . "<p>";
	}
	    echo "<tr><td valign=top><span class=desc>".
			pluralize_n2(ngettext("One comment Matches", "%d comment match", $commentMatch), 
				$commentMatch, _("No comment matches")) .
		    "</span></td></tr>";
	    echo "</table><br>";
	}
  print $search_again;
} else {
	$uAll = array();
        foreach ($gallery->userDB->getUidList() as $uid) {
                $tmpUser = $gallery->userDB->getUserByUid($uid);
		$fullname = trim($tmpUser->getFullName());
		if (strlen($fullname) == 0) {
			$fullname = $tmpUser->getUsername();
		}
                $uAll[$uid] = $fullname." (".
                        $tmpUser->getUsername().")";
        }
       uasort($uAll, create_function('$a,$b','return strcasecmp($a,$b);'));

?>
<br><?php echo _("Search the Gallery's Album and Photo titles, descriptions and comment.") ?><p>
	<table width=\"100%\" border=0 cellspacing=0>
	<tr><?php echo makeFormIntro("adv_search.php"); ?></tr>
	<!--
	<tr><td><?php echo _("Text for which to search:") ?></td>
	<td valign="middle" align="left">
	<input type="text" name="searchstring" value="<?php echo $searchstring ?>" size="25"> 
	</td></tr> 
	<tr><td><?php echo _("Look only in the following albums [unselect all to search all albums]") ?></td>
	<td><select name="albums[]" multiple size=5>
	<?php
	// printAlbumOptionList(0, 1, 0, true);  
	?> 
	</select>
	</td></tr>
	-->
	<tr> <td valign="top"><?php echo _("Search for albums owned by:") ?></td> <td>
<?php echo drawSelect("album_owners[]", $uAll, $album_owners, 5, array("MULTIPLE" =>NULL)); ?>
	</td> </tr>
	<tr> <td valign="top"><?php echo _("Search for items owned by:") ?></td> <td>
<?php echo drawSelect("item_owners[]", $uAll, $item_owners, 5, array("MULTIPLE" =>NULL)); ?>
	</td> </tr>
	<tr> <td valign="top"><?php echo _("Search for comments made by:") ?></td> <td>
<?php echo drawSelect("commenters[]", $uAll, $commenters, 5, array("MULTIPLE" =>NULL)); ?>
	</td> </tr>
	<tr><td><input type="submit" name="go" value="<?php echo _("Go!") ?>"></td></tr>
	</form>  
	</tr>
	</table>
<?php
}
echo "<br>";
$breadtext[0] = _("Gallery") . ":<a href=\"". makeGalleryUrl("albums.php") . "\">".$gallery->app->galleryTitle."</a>";
$breadcrumb["text"] = $breadtext;
$breadcrumb["bordercolor"] = $borderColor;
$breadcrumb["top"] = true;
$breadcrumb["bottom"] = true;
includeLayout('breadcrumb.inc');
?>
<?php 
includeLayout('ml_pulldown.inc');
includeHtmlWrap("search.footer");
?>
<?php if (!$GALLERY_EMBEDDED_INSIDE) { ?>
</body>
</html>
<?php } ?>

