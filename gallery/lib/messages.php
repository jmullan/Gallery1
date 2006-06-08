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
 */
?>
<?php

function gallery_error($message) {
    return '<span class="g-error">'. _("Error:") . " $message</span>\n";
}

function infoLine($messages, $type = '') {
    $message = (is_array($messages)) ? implode("<br>\n", $messages) : $messages;

    if(!empty($message)) {
        return "<div class=\"$type\">Infoline: $message</div>\n";
    } else {
	return '';
    }
}

function infoBox($messages = array(), $caption = '', $withOuterBorder = true) {
    $html = '';

    $types = array('success', 'warning', 'error', 'informaion');
    if(!empty($messages)) {
        if($withOuterBorder) {
            $html = "\n<div class=\"g-notice\">";
        }

        if(!empty($caption)) {
            $html .= "<span class=\"g-notice-caption\">$caption</span>";
        }

	ksort($messages);
        foreach ($messages as $message) {
	    if(! in_array($message['type'], $types)) {
		$message['type'] = 'information';
	    }

            $html .= "\n  ". '<div class="g-'. $message['type'] .' left">';
            $html .= gImage('icons/notice/'. $message['type'] .'.png');
            $html .= ' '. $message['text'];
            $html .= "\n  </div>";
        }
        if($withOuterBorder) {
            $html .= "\n</div>";
        }
    }

    return $html;
}

function errorRow($key) {
    global $gErrors;

    if (!empty($gErrors[$key])) {
        echo "\n<tr>";
        echo "\n  <td colspan=\"2\">";
	echo "\n  ". gallery_error($gErrors[$key]);
	echo "  </td>";
        echo "\n</tr>";
    }
}

function processingMsg($buf) {
    echo "\n- $buf<br>";
    my_flush();
}

/* returns the offical name of the gallery */
function Gallery() {
    return "Gallery";
}

function poweredBy () {
    global $gallery;
    $link = '<a href="'.$gallery->url.'" target="_blank">'.Gallery().'</a>';
    if (isDebugging() || $gallery->app->devMode == "yes" || $gallery->user->isAdmin()) {
        $version = $gallery->version;
    } else {
        $version = "1<!-- {$gallery->version} -->";
    }

    if ($gallery->session->offline) {
        return sprintf(_("Generated by %s v%s"), $link, $version);
    } else {
        return sprintf(_("Powered by %s v%s"), $link, $version);
    }
}

function debugMessage($msg, $file, $line, $level = NULL) {
    global $gallery;

    if (isDebugging($level)) {
        echo "\n<div class=\"debugmessage\">". basename($file) ."::$line -> $msg</div>\n";
    }
}

?>
