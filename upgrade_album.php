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
<?
/*
 * This page is designed to work in standalone mode AND to be included
 * from init.php, so be certain not to require init.php twice.  We
 * can't use require_once() here because some older versions of PHP4
 * barf on require_once() with a variable parameter.
 *
 * Since init.php will also include this file under some circumstances, 
 * we want to keep track to see  if we're in an include loop.  If so, return.
 */
$UPGRADE_LOOP++;
if ($UPGRADE_LOOP == 2) {
	return;
}

if (!$gallery->version) { require($GALLERY_BASEDIR . "init.php"); }
?>
<?
/*
 * If we're not the admin, we can only upgrade the album that we're
 * looking at.
 */
if ($gallery->session->albumName) {
	$upgrade_albumname = $gallery->session->albumName;
}

// Hack check
if (!$gallery->user->isAdmin() && !$upgrade_albumname) {
	exit;
}

$albumDB = new AlbumDB();

function close_button() {
	print "<center>";
	print "<form>";
	print "<input type=submit value=\"Done\" onclick='opener.location.reload(); parent.close()'>";
	print "</form>";
	print "</center>";
}

function reload_button() {
	print "<center>";
	print "<form>";
	print "<input type=submit value=\"Done\" onclick='location.reload()'>";
	print "</form>";
	print "</center>";
}

function end_file() {
	print "</body>";
	print "</html>";
}

function process($arr) {
	print "<br>";
	print "<b>Progress:</b>";
	print "<ul>";
	foreach ($arr as $album) {
		print "<b>Album: " . $album->fields["title"] . "</b><br>";
		if ($album->integrityCheck()) {
			$album->save(0);
		}
		print "<br>";
	}
	print "</ul>";
}

function find_albums(&$results, $album="") {
	global $gallery;
	global $albumDB;

	if ($album) {
		if ($album->versionOutOfDate()) {
			$results[] = $album;
		}

		$count = $album->numPhotos(1);
		for ($j = 1; $j <= $count; $j++) {
			$name = $album->isAlbumName($j);
			if ($name) {
				find_albums($results, $albumDB->getAlbumByName($name));
			}
		}
	} else {
		$numAlbums = $albumDB->numAlbums($gallery->user);
		for ($i = 1; $i <= $numAlbums; $i++) {
			$album = $albumDB->getAlbum($gallery->user, $i);
			find_albums($results, $album);
		}
	} 
}
?>

<html>
<head>
  <title>Upgrade Albums</title>
  <?= getStyleSheetLink() ?>
</head>
<body>
<center>
<span class="title">
Upgrade Albums
</span>
</center>
<p>
The following albums in your Gallery were created with an older
version of the software and are out of date.  This is not a problem!
We can upgrade them.  This may take some time for large albums but
we'll try to keep you informed as we proceed.  None of your photos
will be harmed in any way by this process.  Rest assured, that if this
process takes a long time now, it's going to make your Gallery run
more efficiently in the future.

<p>

<?
if ($upgrade_albumname) {
	$album = new Album();
	$album->load($upgrade_albumname);
}

if ($album && $album->versionOutOfDate()) {
	process(array($album));
	reload_button();
	end_file();
	exit;
}

$ood = array();
find_albums($ood);

if ($upgradeall && sizeof($ood)) {
	process($ood);
	reload_button();
	end_file();
	exit;
}
	
if (!$ood) {
	print "<center>";
	print "<b>All albums are up to date.</b>";
	print "</center>";
	close_button();
} else {
?>
The following albums need to be upgraded.  You can process them
individually by clicking the upgrade link next to the album that
you desire, or you can just
<a href="<?=makeGalleryUrl("upgrade_album.php", array("upgradeall" => 1))?>">upgrade them all at once</a>.
<ul>
<?
	foreach ($ood as $album) {
		print "<a href=\"";
		print makeGalleryUrl("upgrade_album.php", 
			array("upgrade_albumname" => $album->fields["name"]));
		print "\">[upgrade]</a> ";
		print "<b>" . $album->fields["title"] . "</b>";
		print " (" . $album->numPhotos(1) . " items)";
		print "<br>";
	}
	close_button();
}
?>
</ul>
<?
end_file();
?>
