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
	$UPGRADE_LOOP = 0;
}

$UPGRADE_LOOP++;
if ($UPGRADE_LOOP == 2) {
	return;
}

if (!isset($gallery->version)) {
	require_once(dirname(dirname(__FILE__)) . '/init.php');
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
	printPopupStart(gTranslate('core', "Upgrade Albums"), '', 'left');
	showInvalidReqMesg(gTranslate('core', "You are not allowed to perform this action!"));
	exit;
}

$albumDB = new AlbumDB(FALSE);

function process($album = null) {
	global $albumDB;

	echo addProgressbar('mainProgessbar', gTranslate('core', "Total Progress:"));
	echo "\n<br>";
	echo addProgressbar('albumProgessbar', gTranslate('core', "Album Progress:"));

	if ($album) {
		echo '<br><span class="g-emphasis">' .
		  sprintf(gTranslate('core', "Album: %s"), $album->fields["title"]) .
		  "</span>\n";

		// Upgrade the album
		if ($album->integrityCheck()) {
			$album->save(array(), 0);
		}

		echo "\n<script type=\"text/javascript\">updateProgressBar('mainProgessbar', '',100)</script>";
		echo "\n<script type=\"text/javascript\">updateProgressBar('albumProgessbar', '',100)</script>";
	}
	else {
		$count = sizeof($albumDB->outOfDateAlbums);
		$onePercent = 100/$count;
		$i = 0;

		echo '<br><div class="g-emphasis"><i>' . gTranslate('core', "Statusbox") . '</i></div>';
		echo gTranslate('core', "Please ensure there are no errors.");
		echo '<div class="albumUpgradeStatus">';
		foreach ($albumDB->outOfDateAlbums as $albumName) {
			$i++;
			// Retrieve the album
			$album = $albumDB->getAlbumByName($albumName);

			echo "\n<div class=\"g-emphasis\">".
				sprintf(gTranslate('core', "Album: %s"), $album->fields["title"]) .
				"</div>";

			// Upgrade the album
			if ($album->integrityCheck()) {
				$album->save(array(), 0);
			}

			echo "\n<script type=\"text/javascript\">updateProgressBar('mainProgessbar', '',". ceil($i * $onePercent) .")</script>";
			print "<br><br>";
		}
		echo "</div>";
	}
}

/* Start HTML output */
printPopupStart(gTranslate('core', "Upgrade Albums"), '', 'left');

if (isset($upgrade_albumname)) {
	$album = new Album();
	$album->load($upgrade_albumname);

	if ($album->versionOutOfDate()) {
		process($album);
		$actionDone = true;
	}
}
elseif (isset($upgradeall) && sizeof($albumDB->outOfDateAlbums)) {
	process();
	$actionDone = true;
}

if (sizeof($albumDB->outOfDateAlbums) == 0) {
	print "<b>". gTranslate('core', "All albums are up to date.") ."</b>";
}
elseif (!isset($actionDone)) {
	echo gTranslate('core', "Some albums in your gallery were created with an older version of the software and are out of date.");
	echo '<br>';
	echo gTranslate('core', "This is not a problem!");
	echo '<p>';
	echo gTranslate('core', "We can upgrade them.  This may take some time for large albums but we'll try to keep you informed as we proceed.");
	echo '<br>';
	echo gTranslate('core', "None of your photos will be harmed in any way by this process.");
	echo '<br>';
	echo gTranslate('core', "Rest assured, that if this process takes a long time now, it's going to make your gallery run more efficiently in the future.");

	echo "\n<p>";
	echo sprintf(gTranslate('core', "The following albums need to be upgraded.  You can process them individually by clicking the upgrade link next to the album that you desire, or you can just %s."),
		'<a class="g-error" href="' . makeGalleryUrl("upgrade_album.php", array("upgradeall" => 1, 'type' => 'popup')) . '"><b>' . gTranslate('core', "upgrade them all at once") . '</b></a>');

	echo '</p>';

	$updateList = new galleryTable();

	$updateList->setAttrs(array('align' => 'center'));

	foreach ($albumDB->outOfDateAlbums as $albumName) {
		$album = $albumDB->getAlbumByName($albumName);

		$updateList->addElement(array(
			'content' => $album->fields["title"],
			'cellArgs' => array('class' => 'g-emphasis'))
		);

		$updateList->addElement(array(
			'content' => gTranslate('core', "One item", "%d items", $album->numPhotos(1), gTranslate('core', "Empty"),true),
			'cellArgs' => array('class' => 'right'))
		);

		$updateList->addElement(array('content' => galleryLink(
			makeGalleryUrl('upgrade_album.php', array(
				'upgrade_albumname' => $album->fields['name'],
				'type' => 'popup')),
			gTranslate('core', "upgrade"), '', '', true))
		);
	}

	echo $updateList->render();
}
?>
	<form action="#" class="center">
	<?php echo gButton("reloadButton", gTranslate('core', "_Reload"), 'location.reload()'); ?>
	<?php echo gButton("closeButton", gTranslate('core', "_Close Window"), 'parent.close() ; location.reload();'); ?>
	</form>

</div>

</body>
</html>
