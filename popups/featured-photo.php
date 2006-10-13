<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2006 Bharat Mediratta
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
 *
 *
 * Featured photo block for Gallery
 * Beckett Madden-Woods (beckett@beckettmw.com)
 * Edited by Jens Tkotz <jens@peino.de>
 *
*/

require_once(dirname(dirname(__FILE__)) . '/init.php');

define('FEATURE_CACHE', $gallery->app->albumDir . '/featured-photo.cache');

list($set, $index) = getRequestVar(array('set', 'index'));
$notice_messages = array();

if (!empty($set)) {
    if (!$gallery->user->isAdmin()) {
    	echo gTranslate('core', "You are not allowed to perform this action!");
    	exit;
    }

    printPopupStart(gTranslate('core', "Featured Photo"));

    if ($gallery->session->albumName && $index) {
	   echo "<p>". $gallery->album->getThumbnailTag($index) ."</p>";

        if ($fd = @fs_fopen(FEATURE_CACHE, 'w')) {
            fwrite($fd, $gallery->session->albumName . "/$index");
            fclose($fd);

            $notice_messages[] = array(
                'type' => 'success',
                'text' => gTranslate('core', "New featured photo saved.")
            );
        }
        else {
            $notice_messages[] = array(
                'type' => 'error',
                'text' => gTranslate('core', "Could not write the cache file!") . '<br>' .
                    sprintf(
                        gTranslate('core', "Make sure that the file %s in your albums folder is writeable for the webserver."),
                        '<i>featured-photo.cache</i>')
            );
        }
    }
    else {
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
