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
 * Functions for the usage page.
 *
 * @author Dave Moore
 * @author Joan McGalliard
 * @author Jens Tkotz
 * @package Usage
 */

/**
 * Outputs (not returns) a HTML table with the diskusage per album
 *
 * @param string $sortby	One of: uname, link, name, uid, owner, or bytes
 * @param string $order		desc, or asc
 */
function showUsagePerAlbum($sortby = 'bytes', $order = 'desc') {
	global $gallery;
	$albumDB = new AlbumDB(false);

	$usageTable = array();

	foreach ($albumDB->albumList as $album) {
		if (!$album->isRoot()) {
			continue;
		}

		$usageTableEntry = array();

		$title	= $album->fields['title'];
		$url	= makeAlbumUrl($album->fields['name']);
		$dir	= $gallery->app->albumDir. "/" .$album->fields['name'];
		$bytes	= get_size($dir);
		$uid	= $album->fields['owner'];
		$tmpUser = $gallery->userDB->getUserByUid($uid);

		$usageTableEntry = array();

		$usageTableEntry['uname']	= $tmpUser->getUsername();
		$usageTableEntry['link']	= galleryLink($url, $title);
		$usageTableEntry['name']	= $album->fields['name'];
		$usageTableEntry['uid']		= $uid;
		$usageTableEntry['owner']	= showOwner($album->getOwner());
		$usageTableEntry['bytes']	= $bytes;
		$usageTableEntry['usage']	= formatted_filesize($bytes);

		$usageTable[] = $usageTableEntry;
	}

	array_sort_by_fields($usageTable, $sortby, $order);
?>
<style type="text/css"> .bottom {
	border-top: 1px solid silver;
}
</style>

<fieldset>
<legend><?php printf(gTranslate('core', "Space used per album :: Sorted by %s"), $sortby); ?></legend>
<table class="g-usageTable" cellspacing="0">
	<tr>
		<th align="center"><?php echo gTranslate('core', "Album"); ?></th>
		<th align="center"><?php echo gTranslate('core', "User"); ?></th>
		<th align="center"><?php echo gTranslate('core', "Disk usage main album"); ?></th>
		<th align="center"><?php echo gTranslate('core', "Subalbums"); ?></th>
	</tr>
<?php

foreach ($usageTable as $usageTableEntry) {
?>
	<tr>
		<td class="bottom"><?php echo $usageTableEntry['link'] ?></td>
		<td class="bottom"><?php echo $usageTableEntry['owner'] ?></td>
		<td class="right bottom"><?php echo $usageTableEntry['usage']; ?></td>
		<td class="bottom">&nbsp;</td>
	</tr>
	<?php print printAlbumListSub($usageTableEntry['name']);
}

echo "\n</table>";
}

/**
 * Outputs (not returns) a HTML table with the diskusage per user
 *
 * @param string $sortby	One of: uid, uname, owner, usage, or bytes
 * @param string $order		desc or asc
 */
function showUsageByUser($sortby = 'bytes', $order = 'desc') {
	global $gallery;
	$usageTable = array();

	foreach ($gallery->userDB->getUidList() as $uid) {
		$tmpUser = $gallery->userDB->getUserByUid($uid);

		$usageTableEntry = array();

		$usageTableEntry['uid']		= $uid;
		$usageTableEntry['uname']	= $tmpUser->getUsername();
		$usageTableEntry['owner']	= showOwner($gallery->userDB->getUserByUid($uid));
		list($usageTableEntry['usage'], $usageTableEntry['bytes'])	= usrDiskUsage($uid);

		$usageTable[] = $usageTableEntry;
	}

	array_sort_by_fields($usageTable, $sortby, $order);
?>
<fieldset>
<legend><?php printf(gTranslate('core', "Space used by users :: Sorted by %s"), $sortby); ?></legend>
<table class="g-usageTable">
	<tr>
		<th align="center"><?php echo gTranslate('core', "User"); ?></th>
		<th align="center"><?php echo gTranslate('core', "Disk usage"); ?></th>
	</tr>
<?php

foreach ($usageTable as $usageTableEntry) {
?>
	<tr>
		<td><?php echo $usageTableEntry['owner'] ?></td>
		<td class="right"><?php echo $usageTableEntry['usage']; ?></td>
	</tr>
<?php
}
?>
</table>
</fieldset>
<?php
}

function printAlbumListSub($albumName, $depth = 0) {
	set_time_limit(60);
	global $gallery;

	$myAlbum = new Album();
	$myAlbum->load($albumName);
	$numPhotos = $myAlbum->numPhotos(1);
	for ($i=1; $i <= $numPhotos; $i++) {
		$myName = $myAlbum->getAlbumName($i);
		if ($myName && !$myAlbum->isHidden($i)) {
			$nestedAlbum = new Album();
			$nestedAlbum->load($myName);
			if ($gallery->user->canReadAlbum($nestedAlbum)) {
				$dashes = str_repeat('-- ', $depth + 1);
				$name = makeAlbumUrl($myName);
				$title = $nestedAlbum->fields['title'];
				$val2 = $dashes . "<a href=\"$name\">$title</a>\n";
				$val3 = showOwner($myAlbum->getOwner());

				print "<tr>";
				print "<td>$val2</td>\n";
				print "<td> $val3 </td>";
				print "<td>&nbsp;</td>";
				$dir = $gallery->app->albumDir. "/" .$nestedAlbum->fields['name'];
				print "<td class=\"right\">" . formatted_filesize(get_size($dir)) . "</td>\n";
				print "</tr>\n";
				print printAlbumListSub($myName,$depth+1);
				print "";
			}
		}
	}
}

