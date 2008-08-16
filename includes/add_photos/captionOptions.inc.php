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
 * Central caption options formular for adding items.
 * @package Gallery
 */

if(!isset($setCaption) || (int)$setCaption > 3) {
	$setCaption = 1;
}

echo gInput('radio', 'setCaption', gTranslate('core', "Leave blank."), false, 0,
		array('id' => 'setCaption0', 'checked' => ($setCaption == 0) ? NULL : false));
echo "\n<br>";

echo gInput('radio', 'setCaption', gTranslate('core', "Use filename as caption."), false, 1,
		array('id' => 'setCaption1', 'checked' => ($setCaption == 1) ? NULL : false));
echo "\n<br>";

echo gInput('radio', 'setCaption', gTranslate('core', "Use file creation date/time stamp."), false, 2,
		array('id' => 'setCaption2', 'checked' => ($setCaption == 2) ? NULL : false));
echo "\n<br>";

if (isset($gallery->app->use_exif)) {
	echo gInput('radio', 'setCaption', gTranslate('core', "Set photo captions with file capture times."), false, 3,
			array('id' => 'setCaption3', 'checked' => ($setCaption == 3) ? NULL : false));
}

echo "\n<br><br>";
echo gTranslate('core', "For the date/time related options Gallery will use the format you specified in the config for date/time strings.");
?>
