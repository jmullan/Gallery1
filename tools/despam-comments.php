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
 * The idea for this was blatantly lifted from Jay Allen's most excellent
 * MT-Blacklist, a plugin for MovableType that helps kill spam dead.  No code
 * was taken, though.
 */
?>
<?php

if (!isset($gallery->version)) {
    require_once(dirname(dirname(__FILE__)) . '/init.php');
}

// Security check
if (!$gallery->user->isAdmin()) {
    header("Location: " . makeAlbumHeaderUrl());
    exit;
}

require_once(dirname(__FILE__) .'/lib/lib-despam_comments.php');

if (!$GALLERY_EMBEDDED_INSIDE) {
    doctype();
?>
<html>
<head>
<title><?php echo clearGalleryTitle(gTranslate('core', "Find and remove comment spam")) ?></title>
<?php
common_header() ;
?>

</head>
<body>
<?php
}
includeTemplate("gallery.header", '', 'classic');

$adminbox['text'] = gTranslate('core', "Find and remove comment spam");
$adminbox["commands"] = galleryLink(
                            makeGalleryUrl("admin-page.php"),
                            gTranslate('core', "return to _admin page"),
                            array(), '', true);

$adminbox["commands"] .= galleryLink(
                            makeAlbumUrl(),
                            gTranslate('core', "return to _gallery"),
                            array(), '', true);

$adminbox["bordercolor"] = $gallery->app->default["bordercolor"];
$breadcrumb['text'][] = languageSelector();

includeLayout('adminbox.inc');
includeLayout('breadcrumb.inc');
?>
<div class="g-content-popup">
<table width="100%">
<tr>
<?php
echo '<td style="vertical-align:top;">';
offerOptions();
echo "</td>";

$g1_mode = getRequestVar('g1_mode');

echo '<td class="g-border-left" style="padding-left: 10px;">';

switch($g1_mode) {
    case 'deleteComments':
        deleteComments();
    break;

    case 'findBlacklistedComments':
        findBlacklistedComments();
    break;

    case 'updateBlacklist':
        updateBlacklist();
    break;

    case 'viewBlacklist':
        viewBlacklist();
    break;

    case 'editBlacklist':
        editBlacklist();
    break;

    case 'addBlacklistEntries':
        showAddBox();
    break;

    default:
    break;
}
echo "</td></tr>";
?>
</table>
</div>
<?php
includeTemplate("overall.footer");

if (!$GALLERY_EMBEDDED_INSIDE) {
?>
</body>
</html>
<?php
}
?>
