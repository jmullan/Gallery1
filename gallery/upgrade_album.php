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
/*
 * This page is designed to work in standalone mode AND to be included
 * from init.php, so be certain not to require init.php twice.  We
 * can't use require_once() here because some older versions of PHP4
 * barf on require_once() with a variable parameter.
 *
 * Since init.php will also include this file under some circumstances, 
 * we want to keep track to see  if we're in an include loop.  If so, return.
 */
if (!isset($UPGRADE_LOOP)) {
	$UPGRADE_LOOP=0;
}
$UPGRADE_LOOP++;
if ($UPGRADE_LOOP == 2) {
	return;
}

if (!isset($gallery->version)) { 
	require_once(dirname(__FILE__) . '/init.php'); 
}

list($upgrade_albumname, $upgradeall) = getRequestVar(array('upgrade_albumname', 'upgradeall'));

/*
 * If we're not the admin, we can only upgrade the album that we're
 * looking at.
 */
if ($gallery->session->albumName) {
	$upgrade_albumname = $gallery->session->albumName;
}

// Hack check
if (!$gallery->user->isAdmin() && empty($upgrade_albumname)) {
	echo _("You are not allowed to perform this action!");
	exit;
}

$albumDB = new AlbumDB(FALSE);

function reload_button() {
	print "<center>";
	print "<form>";
	print "<input type=\"button\" name=\"done\" value=\"" . _("Done") ."\" onclick='location.reload()'>";
	print "</form>";
	print "</center>";
}

function end_file() {
	print "\n</div>";
	print "\n</body>";
	print "\n</html>";
}

function process($album=null) {
	global $albumDB;

	print "<br>";
	print "<b>" . _("Progress") .":</b>";
	print "<ul>";
	if ($album) {
		print "<b>". _("Album") . ": " . $album->fields["title"] . "</b><br>";
		// Upgrade the album
		if ($album->integrityCheck()) {
			$album->save(array(), 0);
		}
		print "<br>";
	} else {
		foreach ($albumDB->outOfDateAlbums as $albumName) {

			// Retrieve the album
			$album = $albumDB->getAlbumByName($albumName);

			print "<b>". _("Album") . ": " . $album->fields["title"] . "</b><br>";

			// Upgrade the album
			if ($album->integrityCheck()) {
				$album->save(array(), 0);
			}

			print "<br>";
		}
	}
	print "</ul>";
}

doctype();
?>
<html>
<head>
  <title><?php echo _("Upgrade Albums") ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>" class="popupbody">
<div class="popuphead"><?php echo _("Upgrade Albums") ?></div>
<div class="popup">
<p>
<?php 
	echo _("The following albums in your gallery were created with an older version of the software and are out of date.");
	echo '<br>';
	echo _("This is not a problem!");
	echo '<br><br>';
	echo _("We can upgrade them.  This may take some time for large albums but we'll try to keep you informed as we proceed.");
	echo '<br>';
	echo _("None of your photos will be harmed in any way by this process.");
	echo '<br>';
	echo _("Rest assured, that if this process takes a long time now, it's going to make your gallery run more efficiently in the future.");
?>  
</p>

<?php
if (isset($upgrade_albumname)) {
	$album = new Album();
	$album->load($upgrade_albumname);
}

if (isset($album) && $album->versionOutOfDate()) {
	process($album);
	reload_button();
	end_file();
	exit;
}

if (isset($upgradeall) && sizeof($albumDB->outOfDateAlbums)) {
	process();
	reload_button();
	end_file();
	exit;
}
	

if (!sizeof($albumDB->outOfDateAlbums)) {
	print "<b>". _("All albums are up to date.") ."</b>";
} else {

	echo "\n<p>";
	echo sprintf(_("The following albums need to be upgraded.  You can process them individually by clicking the upgrade link next to the album that you desire, or you can just %s."),
		'<a class="error" href="' . makeGalleryUrl("upgrade_album.php", array("upgradeall" => 1, 'type' => 'popup')) . '"><b>' . _("upgrade them all at once") . '</b></a>');

	echo '</p>';
	echo "\n<table align=\"center\">";
	foreach ($albumDB->outOfDateAlbums as $albumName) {
		$album = $albumDB->getAlbumByName($albumName);
		echo "\n<tr>";
		echo '<td><a href="';
		echo makeGalleryUrl("upgrade_album.php", 
			array("upgrade_albumname" => $album->fields["name"], 'type' => 'popup'));
		echo '">['. _("upgrade") .']</a></td>';
		echo '<td><b>'. $album->fields["title"] . '</b></td>';
		echo '<td>('. $album->numPhotos(1) .' '. _("items"). ')</td>';
		echo '</tr>';
	}
	echo "\n</table>\n";
}

	print '<form name="close" action="#">';
	print '<input type="button" name="close" value="'. _("Done") .'" onclick="opener.location.reload(); parent.close()">';
	print '</form>';

	echo "\n</div>";
	print gallery_validation_link("upgrade_album.php");
?>
</body>
</html>
