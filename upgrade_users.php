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

// should only be called from init.php
if (!$gallery->version) { 
	exit; 
}

$noticeMessages = array();
printPopupStart(gTranslate('core', "Upgrading Users"), '', 'left');

echo gTranslate('core', "The user database in your gallery was created with an older version of the software and is out of date.");
echo "\n<br>";
echo gTranslate('core', "This is not a problem!");
echo gTranslate('core', "We will upgrade it.  This may take some time.");
echo "\n<br>";
echo gTranslate('core', "Your data will not be harmed in any way by this process.");
echo "\n<br>";
echo gTranslate('core', "Rest assured, that if this process takes a long time now, it's going to make your gallery run more efficiently in the future.");
echo "\n<p>";
echo gTranslate('core', "If you get an error, and only some users are upgraded, try refreshing the page to upgrade remaining users.");
echo "\n<br><br>";
echo gTranslate('core', "Please Wait...") . "\n<br>";

if (!$gallery->userDB->integrityCheck() ) {
	$noticeMessages[] = array(
	  'type' => 'error',
	  'text' => gTranslate('core', "There was a problem upgrading users.  Please check messages above, and try again.")
	);
	$button = gButton('retry', gTranslate('core', "_Retry"), 'location.reload()');
}
else {
	$noticeMessages[] = array(
	  'type' => 'success',
	  'text' => gTranslate('core', "Users upgraded successfully.")
	);
	$button = gButton('done', gTranslate('core', "_Done"), 'location.reload()');
}
echo infobox($noticeMessages);
?>

  <div align="center"><?php echo $button; ?></div>
</div>
</body>
</html>
