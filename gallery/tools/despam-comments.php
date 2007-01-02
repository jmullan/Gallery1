<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2007 Bharat Mediratta
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

if (!$GALLERY_EMBEDDED_INSIDE) {
    doctype();
?>
<html>
<head>
<title><?php echo $gallery->app->galleryTitle ?></title>
<?php 
common_header() ;
?>

</head>
<body dir="<?php echo $gallery->direction ?>">
<?php  
}
includeHtmlWrap("gallery.header");
$adminbox['text'] ='<span class="head">'. _("Find and remove comment spam") .'</span>';
$adminCommands = '[<a href="'. makeGalleryUrl("admin-page.php") .'">'. _("return to admin page") .'</a>] ';
$adminCommands .= '[<a href="'. makeAlbumUrl() .'">'. _("return to gallery") .'</a>] ';

$adminbox["commands"] = $adminCommands;
$adminbox["bordercolor"] = $gallery->app->default["bordercolor"];
$breadcrumb['text'][] = languageSelector();

includeLayout('navtablebegin.inc');
includeLayout('adminbox.inc');
includeLayout('navtablemiddle.inc');
includeLayout('breadcrumb.inc');
includeLayout('navtableend.inc');
?>
<div class="popup">
<table width="100%">
<tr>
<?php
echo '<td style="vertical-align:top; white-space:nowrap; width:280px;">';
offerOptions();
echo "</td>";

$g1_mode=getRequestVar('g1_mode');

echo '<td class="borderleft-popup">';
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
includeHtmlWrap("gallery.footer");
if (!$GALLERY_EMBEDDED_INSIDE) {
?>
</body>
</html>
<?php }

/* Everything below is a utility function */
function deleteComments() {
    printf("<h2>%s</h2>", _("Delete Comments"));
    if (!getRequestVar('delete')) {
        printf("<h3>%s</h3>", _("No action taken!"));
    } else {
        $removedTotal = 0;
        foreach (getRequestVar('delete') as $key) {
            list ($albumName, $imageId, $key) = explode('|', $key);
            $albumQueue[$albumName][$imageId][$key] = 1;
        }

        $albumDB = new AlbumDB();
        foreach ($albumQueue as $albumName => $imageQueue) {
            $album = $albumDB->getAlbumByName($albumName);
            $removedInAlbum = 0;
            foreach ($imageQueue as $imageId => $keys) {
                $photoIndex = $album->getPhotoIndex($imageId);
                for ($j = $album->numComments($photoIndex); $j > 0; $j--) {
                    $comment = $album->getComment($photoIndex, $j);
                    $key = getCommentKey($comment);
                    if (isset($keys[$key])) {
                        $removedInAlbum++;
                        $removedTotal++;
                        $album->deleteComment($photoIndex, $j);
                    }
                }
            }
            $album->save(array(_("Deleted %d spam comments"), $removedInAlbum));
        }

        printf("<h3> %s </h3>",
            gTranslate('core', "Deleted %d spam comment.", "Deleted %d spam comments.",
            $removedTotal, _("No comment deleted.")));
    }
}

function findBlacklistedComments() {
    global $gallery;
    $list = array();
    $start = explode(' ', microtime());

    $albumDB = new AlbumDB();
    $totals = array('albums' => 0,
        'photos'   => 0,
        'comments' => 0);
    $totalComments = 0;

    foreach ($albumDB->albumList as $album) {
        set_time_limit(30);
        $totals['albums']++;
        $numPhotos = $album->numPhotos(1);
        for ($i = 1; $i <= $numPhotos; $i++) {
            set_time_limit(30);
            $photo = $album->getPhoto($i);
            $numComments = $album->numComments($i);
            if ($numComments > 0) {
                for ($j = 1; $j <= $numComments; $j++) {
                    set_time_limit(30);
                    $comment = $album->getComment($i, $j);
                    $totalComments++;
                    if (isBlacklistedComment($comment)) {
                        $list[] = array('albumName' => $album->fields['name'],
                            'imageId' => $photo->image->getId(),
                            'comment' => $comment,
                            'key' => getCommentKey($comment));
                    }
                }
                $totals['comments'] += $numComments;
            }
        }
        $totals['photos'] += $numPhotos;
    }

    $stop = explode(' ', microtime());
    $elapsed = ($stop[0] - $start[0]) + ($stop[1] - $start[1]);

    sprintf('<h3>'. _("Scanned %d albums, %d photos, %d comments in %2.2f seconds") .'</h3>',
        $totals['albums'],
        $totals['photos'],
        $totals['comments'],
        $elapsed);
    if (empty($list)) {
        printf("<h3>%s</h3>", _("No spam comments."));
    } else {
        print makeFormIntro('tools/despam-comments.php', array('name' => 'deleteComments'));
        print "\n<table>";
        printf("\n\t<tr> <th> %s </th> <th>%s</th> </tr>", _("Entry"), _("Delete"));
        foreach ($list as $entry) {
            print "\n\t<tr>";
            print "<td>";
            printf("%s: <a href=\"%s\">%s/%s</a> <br/>\n", _("Location"),
            makeAlbumUrl($entry['albumName'], $entry['imageId']),
                $entry['albumName'],
                $entry['imageId']);
            printf("%s: %s (on %s from %s) <br/>\n", _("Commenter"),
                $entry['comment']->getName(),
                $entry['comment']->getDatePosted(),
                $entry['comment']->getIPNumber());
            printf("%s: %s <br/>\n", _("Comment"), $entry['comment']->getCommentText());
            print "</td>";
            print "<td>";
            printf("<input type=\"checkbox\" name=\"delete[]\" value=\"%s\" checked=\"checked\"/>",
            sprintf("%s|%s|%s",
                $entry['albumName'],
                $entry['imageId'],
                $entry['key']));
            print "</td>";
            print "\n\t</tr>";
            print "\n\t" . '<tr><td colspan="2"><hr></td></tr>';
        }
        print("<input type=\"hidden\" name=\"g1_mode\" value=\"deleteComments\"/>");
        print "</table>";
        printf("<input type=\"submit\" value=\"%s\"/>", _("Delete Checked Comments"));
        print "</form>";
    }
}

function getCommentKey(&$comment) {
    return md5($comment->getCommentText() .
        $comment->getDatePosted() .
        $comment->getIPNumber() .
        $comment->getName() .
        $comment->getUID());
}

function editBlacklist() {
    $blacklist = loadBlacklist();
    printf("<h2>%s</h2>", _("Delete from blacklist"));
    if (!getRequestVar('delete')) {
        printf("<h3>%s</h3>", _("No action taken!"));
    } else {
        $removed = array();
        foreach (getRequestVar('delete') as $key) {
            if (isset($blacklist['entries'][$key])) {
                $removed[$blacklist['entries'][$key]] = 1;
                unset($blacklist['entries'][$key]);
            }
        }

        if (empty($removed)) {
            printf("<h3>%s</h3>", _("No action taken!"));
        } else {
            $success = saveBlacklist($blacklist);
            if (!$success) {
                printf("<h3>%s</h3>", _("Error saving blacklist!"));
            } else {
                printf("<h3>%s</h3>",
                gTranslate('core', "Deleted %d entry from blacklist.", "Deleted %d entries from blacklist",
                sizeof($removed)));
                print "<ul>";
                foreach (array_keys($removed) as $entry) {
                    printf("<li> %s </li>", $entry);
                }
                print "</ul>";
            }
        }
    }
}

function saveBlacklist($blacklist) {
    return safe_serialize($blacklist, getBlacklistFilename());
}

function updateBlacklist() {
    $blacklist = loadBlacklist();
    $dupes = array();
    $added = array();
    $newlines = split("[\n\r]+", getRequestVar('newBlacklistEntries'));
    foreach ($newlines as $line) {
        $line = ereg_replace("#.*", "", $line);
        $line = trim($line);
        if (empty($line)) {
            continue;
        }

        // Check for duplicates
        $key = md5($line);
        if (isset($blacklist['entries'][$key])) {
            $dupes[$line] = 1;
        } else {
            $blacklist['entries'][$key] = $line;
            $added[$line] = 1;
        }
    }

    $success = saveBlacklist($blacklist);

    if (!$success) {
        printf("<h3>%s</h3>", _("Error saving blacklist!"));
    } else {
        if (!empty($added)) {
            printf("<h3>%s</h3>", _("Added to blacklist:"));
            print "<ul>";
            foreach (array_keys($added) as $entry) {
                printf("<li> %s </li>", $entry);
            }
            print "</ul>";
        }

        if (!empty($dupes)) {
            printf("<h3>%s</h3>", _("Following duplicates were not added:"));
            print "<ul>";
            foreach (array_keys($dupes) as $entry) {
                printf("<li> %s </li>", $entry);
            }
            print "</ul>";
        }

        if (empty($added) && empty($dupes) && empty($removed)) {
            printf("<h3>%s</h3>", _("No action taken!"));
        }
    }
}

function viewBlacklist() {
    $blacklist = loadBlacklist();
    printf("<h3>%s (%d) </h3>", _("Current blacklist"), sizeof($blacklist['entries']));
    if (empty($blacklist['entries'])) {
        print _("Your blacklist is empty.  You must add new entries to your blacklist for it to be useful.");
    } else {
        echo insertFormJS('updateBlacklistForm');

        print makeFormIntro("tools/despam-comments.php", array('name' => 'updateBlacklistForm'));
        $blacklistTable = new galleryTable();
        $blacklistTable->setAttrs(array('align' => 'center', 'width' => '60%'));
        $blacklistTable->setHeaders(array(_("Entry"), _("Delete")));
        $blacklistTable->setColumnCount(2);

        foreach ($blacklist['entries'] as $key => $regex) {
            $blacklistTable->addElement(array('content' => wordwrap($regex, 80, "<br>", true)));

            $blacklistTable->addElement(array(
                'content' => '<input type="checkbox" name="delete[]" value="'. $key .'">',
                'cellArgs' => array('align' => 'center')));
        }
        $blacklistTable->addElement(array(
            'content' => insertFormJSLinks('delete[]'),
            'cellArgs' => array('colspan' => 2, 'align' => 'center')));

        echo $blacklistTable->render();

        print "\n<input type=\"hidden\" name=\"g1_mode\" value=\"editBlacklist\">";
        printf("\n<input type=\"submit\" value=\"%s\">", _("Update Blacklist"));
        print "\n</form>";
    }
}

function showAddBox() {
    print makeFormIntro('tools/despam-comments.php');
    printf("<h2>%s</h2>", _("Enter new blacklist entries"));
    print _("Useful blacklists: "). "<ul>";
    foreach (array("http://www.jayallen.org/comment_spam/blacklist.txt") as $url) {
        printf("<li> <a href=\"%s\">%s</a> ", $url, $url);
    }
    print "</ul>";
    print _("You can just cut and paste these blacklists into the text box, or add new entries of your own.");
    print "<br><textarea rows=\"10\" cols=\"80\" name=\"newBlacklistEntries\"></textarea>";
    print "<br>";
    print "<input type=\"hidden\" name=\"g1_mode\" value=\"updateBlacklist\">";
    printf("<input type=\"submit\" value=\"%s\"/>", _("Update Blacklist"));
    print "</form>";
}

function offerOptions() {
    $options = array(
        "findBlacklistedComments" => _("Find blacklisted comments"),
        "viewBlacklist" => _("View/Edit blacklist"),
        "addBlacklistEntries" => _("Add blacklist entries")
    );

    echo _("Options");
    echo "\n<ol>";
    foreach ($options as $key => $text) {
        printf("\n\t<li><a href=\"%s\">%s</a></li>",
        makeGalleryUrl('tools/despam-comments.php', array('g1_mode' => $key)), $text);
    }
    echo "\n</ol>";
}
?>
