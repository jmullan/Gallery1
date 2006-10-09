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
 * $Id: album.php 12505 2006-01-08 20:15:14Z jenst $
*/

/**
 * @package	yui
 * @author	Yahoo!
 * @author	Jens Tkotz <jens@peino.de>
 */
?>
<?php

header('Content-type: text/plain');

include(dirname(dirname(dirname(__FILE__))) .'/util.php');
setGalleryPaths();

if (getOS() == OS_WINDOWS) {
    require(GALLERY_BASE . '/platform/fs_win32.php');
} else {
    require(GALLERY_BASE . '/platform/fs_unix.php');
}


$query = $_GET['query'];
$results = search($query);
sendResults($results);

function search($query) {
    $results = array();

    if (strlen($query) == 0) {
        return array();
    }

    if(is_dir($query)) {
        $dirname = $query;
    }
    else {
        $dirname = dirname($query);
        $basename = basename($query);
    }

    if(!realpath($dirname)) {
        return array();
    }

    $forbidden = array('.', '..');

    if ($handle = opendir($dirname)) {
        $i= 0;
        while (false !== ($file = readdir($handle))) {
            $i++;
            $ext = getExtension($file);

            if(empty($basename)) {
                $path = $dirname . $file;
            }
            elseif (strpos($file, $basename) === 0) {
                $path = "$dirname/$file";
            }
            else {
                continue;
            }

            if (in_array($file, $forbidden) ||
                fs_fileIsHidden($file) ||
                (!acceptableFormat($ext) && !acceptableArchive($ext) && !fs_is_dir($path))) {
                continue;
            }
            else {
                $results[] = $path;
            }
        }
        closedir($handle);
    }

    return $results;
}

function sendResults($results) {
    for ($i = 0; $i < count($results); $i++) {
        print "$results[$i]\n";
    }
}

?>
