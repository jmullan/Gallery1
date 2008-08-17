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
 * $Id: featured-item.php 15631 2007-01-02 05:52:18Z jenst $
 *
 * Featured item block for Gallery
 * Beckett Madden-Woods (beckett@beckettmw.com)
 * Edited by Jens Tkotz
 *
*/

require_once(dirname(dirname(__FILE__)) . '/init.php');

define('FEATURE_CACHE', $gallery->app->albumDir . '/featured-item.cache');

list($set, $index) = getRequestVar(array('set', 'index'));

$notice_messages = array();

if (!empty($set)) {
	if (!$gallery->user->isAdmin()) {
		printPopupStart(sprintf(gTranslate('core', "Featured item"), $label));
		showInvalidReqMesg(gTranslate('core', "You are not allowed to perform this action!"));
		exit;
	}

	if ($gallery->session->albumName && 
		isValidGalleryInteger($index) && 
		$photo = $gallery->album->getPhoto($index)) 
	{
		$label = getLabelByIndex($index);

		printPopupStart(sprintf(gTranslate('core', "Featured %s"), $label));

		echo "<p>". $gallery->album->getThumbnailTag($index) ."</p>";

		if ($fd = @fs_fopen(FEATURE_CACHE, 'w')) {
			fwrite($fd, $gallery->session->albumName . "/$index");
			fclose($fd);

			$notice_messages[] = array(
				'type' => 'success',
				'text' => sprintf(gTranslate('core', "New featured %s saved."), $label)
			);
		}
		else {
			$notice_messages[] = array(
				'type' => 'error',
				'text' => gTranslate('core', "Could not write the cache file!") . '<br>' .
					sprintf(
						gTranslate('core', "Make sure that the file %s in your albums folder is writeable for the webserver."),
						'<i>featured-item.cache</i>')
			);
		}
	}
	else {
		printPopupStart(gTranslate('core', "Featured item"));

		$notice_messages[] = array(
				'type' => 'error',
				'text' => gTranslate('core', "Invalid Parameters.")
		);
	}

	echo infoBox($notice_messages);

	echo "<br>\n";

	echo gButton('closeButton', gTranslate('core', "_Close Window"), 'parent.close()');

	echo "</div>\n";
	echo "</body>\n";
	echo "</html>";
}

?>
