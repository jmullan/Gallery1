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
*/

/**
 * @package	Layout
 * @author	Jens Tkotz
 */

function editField($album, $field, $link = null) {
    global $gallery;

    $buf = '';
    if ($link) {
        $buf .= "<a href=\"$link\">";
    }
    $buf .= $album->fields[$field];
    if ($link) {
        $buf .= '</a>';
    }
    if ($gallery->user->canChangeTextOfAlbum($album)) {
        if (!strcmp($buf, "")) {
            $buf = "<i>&lt;". gTranslate('common', "Empty") . "&gt;</i>";
        }
        $url = "edit_field.php?set_albumName={$album->fields['name']}&field=$field"; // should replace with &amp; for validatation
        $buf .= ' <span class="editlink">';
        $buf .= popup_link( "[". sprintf(gTranslate('common', "edit %s"), gTranslate('common', $field)) . "]", $url) ;
        $buf .= '</span>';
    }
    return $buf;
}

function editCaption($album, $index) {
    global $gallery;

    $abuf ='';
    $buf  = nl2br($album->getCaption($index));

    if (($gallery->user->canChangeTextOfAlbum($album) ||
      ($gallery->album->getItemOwnerModify() &&
      $gallery->album->isItemOwner($gallery->user->getUid(), $index))) &&
      !$gallery->session->offline) {

        if (empty($buf)) {
            $buf = '<i>&lt;'. gTranslate('common', "No Caption") .'&gt;</i>';
        }
        $url = "edit_caption.php?set_albumName={$album->fields['name']}&index=$index";
        $abuf = '<span class="editlink">';
        $abuf .= popup_link("[". gTranslate('common',"edit") ."]", $url);
        $abuf .= '</span>';
    }
    $buf .= $album->getCaptionName($index);
    $buf .= $abuf;

    return $buf;
}

function viewComments($index, $addComments, $page_url, $newestFirst = false, $addType = '', $album = false) {
    global $gallery;
    global $commenter_name;

    echo showComments($index, $album, $newestFirst);

    if ($addComments) {
        /* Default is the popup link.
		 * addType given through function call overrides default.
		 */
        if (empty($addType)) {
            $addType = (isset($gallery->app->comments_addType) ? $gallery->app->comments_addType : "popup");
        }
        if ($addType == 'inside') {
            echo '<br><form action="'. $page_url .'" name="theform" method="post">';
            drawCommentAddForm($commenter_name);
            echo '</form>';
        }
        else {
            $id = $gallery->album->getPhotoId($index);
            $url = "add_comment.php?set_albumName={$gallery->album->fields['name']}&id=$id";
            echo "\n" .'<div align="center" class="editlink">' .
            popup_link('[' . gTranslate('common', "add comment") . ']', $url, 0) .
            '</div><br>';
        }
    }
}

function drawCommentAddForm($commenter_name = '', $cols = 50) {
    global $gallery;
    if ($gallery->user->isLoggedIn() &&
      (empty($commenter_name) || $gallery->app->comments_anonymous == 'no')) {
        $commenter_name = $gallery->user->printableName($gallery->app->comments_display_name);
    }
?>

<table class="commentbox" cellpadding="0" cellspacing="0">
<tr>
	<td colspan="2" class="commentboxhead"><?php echo gTranslate('common', "Add your comment") ?></td>
</tr>
<tr>
	<td class="commentboxhead"><?php echo gTranslate('common', "Commenter:"); ?></td>
	<td class="commentboxhead">
<?php

if (!$gallery->user->isLoggedIn() ) {
    echo '<input name="commenter_name" value="'. $commenter_name .'" size="30">';
} else {
    if ($gallery->app->comments_anonymous == 'yes') {
        echo '<input name="commenter_name" value="'. $commenter_name. '" size="30">';
    } else {
        echo $commenter_name;
        echo '<input type="hidden" name="commenter_name" value="'. $commenter_name .'" size="30">';
    }
}
?>
</td>
</tr>
<tr>
	<td class="commentlabel" valign="top"><?php echo gTranslate('common', "Message:") ?></td>
	<td><textarea name="comment_text" cols="<?php echo $cols ?>" rows="5"></textarea></td>
</tr>
<tr>
	<td colspan="2" class="commentboxfooter" align="right"><input name="save" type="submit" value="<?php echo gTranslate('common', "Post comment") ?>"></td>
</tr>
</table>
<?php
}

function drawApplet($width, $height, $code, $archive, $album, $defaults, $overrides, $configFile, $errorMsg) {
    global $gallery, $GALLERY_EMBEDDED_INSIDE, $GALLERY_EMBEDDED_INSIDE_TYPE;
    global $_CONF; // for geeklog
    global $board_config; // for phpBB2

    if (file_exists($configFile)) {
        include($configFile);

        if (isset($configDefaults)) {
            $defaults = array_merge($defaults, $configDefaults);
        }
        if (isset($configOverrides)) {
            $overrides = array_merge($overrides, $configOverrides);
        }
    }

    $cookieInfo = session_get_cookie_params();

    $cookie_name = session_name();
    $cookie_value = session_id();
    $cookie_domain = $cookieInfo['domain'];
    $cookie_path = $cookieInfo['path'];

    // handle CMS-specific overrides
    if (isset($GALLERY_EMBEDDED_INSIDE)) {
        switch ($GALLERY_EMBEDDED_INSIDE_TYPE) {
            case 'phpnuke':
                $cookie_name = 'user';
                $cookie_value = $_COOKIE[$cookie_name];
            break;
            case 'GeekLog':
                $cookie_name = $_CONF['cookie_session'];
                $cookie_value = $_COOKIE[$cookie_name];
            break;
            case 'phpBB2':
                $cookie_name = $board_config['cookie_name'] . '_sid';
                $cookie_value = $_COOKIE[$cookie_name];
            break;
            case 'mambo':
	    case 'joomla':
		if (!empty($_COOKIE['sessioncookie'])) {
                    // really mambo
                    $cookie1_name = 'sessioncookie';
                    $cookie1_value = $_COOKIE[$cookie1_name];
                } else {
                    // try to find Joomla cookie (this is shaky)
                    foreach ($_COOKIE as $cookie1_name => $cookie1_value) {
                        if (strlen($cookie1_name) == 32 && strlen($cookie1_value) == 32) {
                           // this is probably the right cookie...
                           break;
                        }
                    }
                }
            break;
        }
    }

    $defaults['uiLocale'] = $gallery->language;
?>
	<object
		classid="clsid:8AD9C840-044E-11D1-B3E9-00805F499D93"
		codebase="http://java.sun.com/products/plugin/autodl/jinstall-1_4-windows-i586.cab#Version=1,4,0,0"
		width="<?php echo $width ?>" height="<?php echo $height ?>">
	<param name="code" value="<?php echo $code ?>">
	<param name="archive" value="<?php echo $archive ?>">
	<param name="type" value="application/x-java-applet;version=1.4">
	<param name="scriptable" value="false">
	<param name="progressbar" value="true">
	<param name="boxmessage" value="Downloading the Gallery Remote Applet">
	<param name="gr_url" value="<?php echo $gallery->app->photoAlbumURL ?>">
<?php if (isset($GALLERY_EMBEDDED_INSIDE)) { ?>
	<param name="gr_url_full" value="<?php echo makeGalleryUrl('gallery_remote2.php') ?>">
<?php } ?>
	<param name="gr_cookie_name" value="<?php echo $cookie_name ?>">
	<param name="gr_cookie_value" value="<?php echo $cookie_value ?>">
	<param name="gr_cookie_domain" value="<?php echo $cookie_domain ?>">
	<param name="gr_cookie_path" value="<?php echo $cookie_path ?>">
<?php if (isset($cookie1_name)) { ?>
	<param name="gr_cookie1_name" value="<?php echo $cookie1_name ?>">
	<param name="gr_cookie1_value" value="<?php echo $cookie1_value ?>">
<?php } ?>
	<param name="gr_album" value="<?php echo $album ?>">
<?php
foreach ($defaults as $key => $value) {
    echo "\t<param name=\"GRDefault_". $key ."\" value=\"". $value ."\">\n";
}

foreach ($overrides as $key => $value) {
    echo "\t<param name=\"GROverride_". $key ."\" value=\"". $value ."\">\n";
}
?>

	<comment>
		<embed
				type="application/x-java-applet;version=1.4"
				code="<?php echo $code ?>"
				archive="<?php echo $archive ?>"
				width="<?php echo $width ?>"
				height="<?php echo $height ?>"
				scriptable="false"
				progressbar="true"
				boxmessage="Downloading the Gallery Remote Applet"
				pluginspage="http://java.sun.com/j2se/1.4.2/download.html"
				gr_url="<?php echo $gallery->app->photoAlbumURL ?>"
<?php if (isset($GALLERY_EMBEDDED_INSIDE)) { ?>
				gr_url_full="<?php echo makeGalleryUrl('gallery_remote2.php') ?>"
<?php } ?>
				gr_cookie_name="<?php echo $cookie_name ?>"
				gr_cookie_value="<?php echo $cookie_value ?>"
				gr_cookie_domain="<?php echo $cookie_domain ?>"
				gr_cookie_path="<?php echo $cookie_path ?>"
<?php if (isset($cookie1_name)) { ?>
				gr_cookie1_name="<?php echo $cookie1_name ?>"
				gr_cookie1_value="<?php echo $cookie1_value ?>"
<?php } ?>
				gr_album="<?php echo $album ?>"
<?php
foreach ($defaults as $key => $value) {
    echo "\t\t\t\tGRDefault_". $key ."=\"". $value ."\"\n";
}

foreach ($overrides as $key => $value) {
    echo "\t\t\t\tGROverride_". $key ."=\"". $value ."\"\n";
}
?>
			<noembed alt="<?php echo $errorMsg ?>"><?php echo $errorMsg ?></noembed>
		</embed>
	</comment>
</object>
<?php
}

function createTreeArray($albumName,$depth = 0) {
    global $gallery;
    $printedHeader = 0;
    $myAlbum = new Album();
    $myAlbum->load($albumName);
    $numPhotos = $myAlbum->numPhotos(1);

    $tree = array();
    if ($depth >= $gallery->app->albumTreeDepth) {
        return $tree;
    }

    for ($i = 1; $i <= $numPhotos; $i++) {
        set_time_limit($gallery->app->timeLimit);
        if ($myAlbum->isAlbum($i) && !$myAlbum->isHidden($i)) {
            $myName = $myAlbum->getAlbumName($i, false);
            $nestedAlbum = new Album();
            $nestedAlbum->load($myName);
            if ($gallery->user->canReadAlbum($nestedAlbum)) {
                $title = $nestedAlbum->fields['title'];
                if (!strcmp($nestedAlbum->fields['display_clicks'], 'yes')
                  && !$gallery->session->offline) {
                    $clicksText = "(" . gTranslate('common', "1 view", "%d views", $nestedAlbum->getClicks()) . ")";
                } else {
                    $clicksText = '';
                }

		$albumUrl = makeAlbumUrl($myName);
		$subtree = createTreeArray($myName, $depth+1);
		$highlightTag = $nestedAlbum->getHighlightTag($gallery->app->default["nav_thumbs_size"],
                  'class="nav_micro_img"', "$title $clicksText");
                $microthumb = "<a href=\"$albumUrl\">$highlightTag</a> ";
		$tree[] = array(
		    'albumUrl' => $albumUrl,
		    'albumName' => $myName,
		    'titel' => $title,
		    'clicksText' => $clicksText,
		    'microthumb' => $microthumb,
		    'subTree' => $subtree);
            }
        }
    }
    return $tree;
}

function printChildren($tree, $depth = 0) {
	if ($depth == 0 && !empty($tree)) {
		echo '<div style="font-weight: bold; margin-bottom: 3px">'. gTranslate('common', "Sub-albums:") ."</div>\n";
	}

	foreach($tree as $nr => $content) {
		echo "\n<table cellpadding=\"0\" cellspacing=\"0\" class=\"subalbumTreeLine\" style=\"margin-". langLeft() .":". 20 * $depth ."px\">";
		echo "<tr><td>";
		if(empty($content['subTree']) && $nr < sizeof($tree)-1) {
			echo gImage('icons/tree/join-'. langRight(). '.gif', '');
		}
		else {
			echo gImage('icons/tree/joinbottom-'. langRight() .'.gif', '');
		}
		echo "</td><td class=\"subalbumTreeElement\">";
		echo '<a href="'. $content['albumUrl'] .'">';
		echo $content['titel'] .' ';
		echo $content['clicksText'] .'</a>';
		echo "</td></tr></table>";
		if(!empty($content['subTree'])) {
			printChildren($content['subTree'], $depth+1);
		}
	}
}

function printMicroChildren2($tree, $depth = 0) {
    if ($depth == 0 && !empty($tree)) {
        echo '<div style="font-weight: bold; margin-bottom: 3px">'. gTranslate('common', "Sub-albums:") ."</div>\n";
    }

    foreach($tree as $nr => $content) {
	echo $content['microthumb'];
	if(!empty($content['subTree'])) {
            printMicroChildren2($content['subTree'], $depth+1);
        }
    }
}

function printMetaData($image_info) {
    // Print meta data
    echo "<table border=\"1\">\n";
    $row = 0;
    foreach ($image_info as $info) {
        echo '<tr>';
        if ($row == 0) {
            $keys = array_keys($info);
            foreach ($keys as $key) {
                echo "<th>$key</th>";
            }
            echo "</tr>\n<tr>";
        }

        foreach ($keys as $key) {
            echo '<td>'. $info[$key]. '</td>';
        }
        $row++;
        echo "</tr>\n";
    }
    echo "</table>\n";
}

/**
 * Returns a link to the docs, if present, or NULL
 * @author    Andrew Lindeman
 */
function galleryDocs() {
    global $gallery;

    $base = dirname(dirname(__FILE__));

    if (fs_file_exists("$base/docs/index.html")) {
        if (isset($gallery->app->photoAlbumURL)) {
            $url = $gallery->app->photoAlbumURL . '/docs/index.html';
        }
        else {  // When first time config without $gallery set.
            $url = '../docs/index.html';
        }
        return $url;
    } else {
        return NULL;
    }
}

/**
 * This function displays tables with the Fields of an Photo
 * @param	integer	$index				Fields of this photo are displayed.
 * @param	array	$extra_fields		You need to give the extrafields ; hint: use getExtraFields()
 * @param	boolean	$withExtraFields	if true, then the extra fields are displayed
 * @param 	boolean	$withExif			if true, then the EXIF Data are displayed
 * @param	mixed	$full				Needed for getting dimensions of the photo
 * @param	boolean	$forceRefresh		Needed for getting EXIF Data
 */
function displayPhotoFields($index, $extra_fields, $withExtraFields = true, $withExif = true, $full = NULL, $forceRefresh = 0) {
    global $gallery;

    $photo = $gallery->album->getPhoto($index);

    // if we have extra fiels and we want to show them, then get the values
    if (isset($extra_fields) && $withExtraFields) {
        $CF = getExtraFieldsValues($index, $extra_fields, $full);
        if (!empty($CF)) {
            $tables = array('' => $CF);
        }
    }

    if ($withExif && (isset($gallery->app->use_exif) || isset($gallery->app->exiftags)) &&
      (eregi("jpe?g\$", $photo->image->type))) {
        $myExif = $gallery->album->getExif($index, isset($forceRefresh));
        if (!empty($myExif) && !isset($myExif['Error'])) {

            $tables[gTranslate('common', "EXIF Data")]  = $myExif;
        } elseif (isset($myExif['status']) && $myExif['status'] == 1) {
            echo '<p class="warning">'. gTranslate('common', "Display of EXIF data enabled, but no data found.") .'</p>';
        }
    }

    if (!isset($tables)) {
        return;
    }

    foreach ($tables as $caption => $fields) {
        $customFieldsTable = new galleryTable();
        $customFieldsTable->setAttrs(array('class' => 'customFieldsTable'));
        $customFieldsTable->setCaption($caption, 'customFieldsTableCaption');

        foreach ($fields as $key => $value) {
            $customFieldsTable->addElement(array('content' => $key));
            $customFieldsTable->addElement(array('content' => ':'));
            $customFieldsTable->addElement(array('content' => $value));
        }
        echo $customFieldsTable->render();
    }
}

function includeTemplate($tplName, $skinname='') {
    global $gallery;

    $base = dirname(dirname(__FILE__));

    if (!$skinname) {
        $skinname = $gallery->app->skinname;
    }

    $filename = "$base/skins/$skinname/tpl/$tplName";
    if (fs_is_readable($filename)) {
        include($filename);
        return true;
    } else {
        return false;
    }
}

/**
 * Displays the ownename, if an email is available, then as mailto: link
 * @param  object  $owner
 * @return string
 * @author Jens Tkotz <jens@peino.de
 */
function showOwner($owner) {
    global $GALLERY_EMBEDDED_INSIDE_TYPE;
    global $_CONF;				/* Needed for GeekLog */

    switch ($GALLERY_EMBEDDED_INSIDE_TYPE) {
        case 'GeekLog':
        return '<a href="'. $_CONF['site_url'] .'/users.php?mode=profile&uid='. $owner->uid .'">'. $owner->displayName() .'</a>';
        break;

        default:
        $name = $owner->displayName();

        if (!$owner->getEmail()) {
            return $name;
        } else {
            return '<a href="mailto:' . $owner->getEmail() . '">' . $name . '</a>';
        }
        break;
    }
}

function getIconText($iconName = '', $altText = '', $overrideMode = '', $useBrackets = true) {
    global $gallery;

    $text = $altText;
    $base = dirname(dirname(__FILE__));

    if (!empty($overrideMode)) {
        $iconMode = $overrideMode;
    } elseif (isset($gallery->app->useIcons)) {
        $iconMode = $gallery->app->useIcons;
    } else {
        $iconMode = 'no';
    }

    if ($iconMode != "no" && $iconName != '') {
        if ($iconMode == 'both') {
            $altText = '';
        }

        if (file_exists("$base/images/icons/$iconName")) {
            $imgSrc = $gallery->app->photoAlbumURL .'/images/icons/'. $iconName;
            $linkText = "<img src=\"$imgSrc\" title=\"$altText\" alt=\"$altText\" style=\"border: none;\">";

            if ($iconMode == "both") {
                $linkText .= "<br>$text";
            }
        }
    }

    if (empty($linkText)) {
        if($useBrackets) {
            $linkText = '['. $text . ']';
        } else {
            $linkText = $text;
        }
    }

    return $linkText;
}

function makeIconMenu($iconElements, $align = 'left', $closeTable = true, $linebreak = false) {
    global $gallery;

    if (empty($iconElements)) {
        return '';
    }

    // For rtl/ltr stuff
    if ($gallery->direction == 'rtl') {
        $align = ($align == 'left') ? 'right' : 'left';
    }

    $html = "\n". '<table id="menu" align="'. $align .'"><tr>';
    $i = 0;
    foreach ($iconElements as $element) {
        $i++;
        if (stristr($element,'</a>')) {
            $html .= "\n\t". '<td>'. $element .'</td>';
        } else {
            $html .= "\n\t". '<td class="noLink">'. $element .'</td>';
        }
        if($i > sizeof($iconElements)/2 && $linebreak) {
            $html .= "\n</tr>\n</tr>";
            $i=0;
        }
    }

    if ($closeTable == true) {
        $html .= "</tr>\n</table>";
    }

    return $html;
}

/**
 * @param	string	$formerSearchString	Optional former searchh string
 * @param	string	$align			Optional alignment
 * @return	string	$html			HTML code that contains a form for entering the searchstring
 * @author	Jens Tkotz <jens@peino.de>
 */
function addSearchForm($formerSearchString = '', $align = '') {
    $html = '';

    $html .= makeFormIntro('search.php', array(
        'name'     => 'search_form',
        'style'    => "text-align: $align",
        'class'   => 'search')
    );

    $html .= "\t". gTranslate('common', "Search:");
    $html .= '<input class="searchform" type="text" name="searchstring" value="'. $formerSearchString .'" size="25">';
    $html .= "\n</form>\n";

    return $html;
}

/**
 * This is either a wrapper around fs_file_size(),
 * or just a formatter for a filesize given in bytes.
 * Return a human readable filesize.
 *
 * @param   string  $filesize   if omitted, function gets filesize of given filename
 * @param	string	$filename
 * @return	string  the formated filesize
 * @author	Jens Tkotz <jens@peino.de>
 */
function formatted_filesize($filesize = 0, $filename = '') {

    $filesize = (int)$filesize;
    if($filesize == 0 || $filename != '') {
        $filesize = fs_filesize($filename);
    }

    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    $unit_count = (count($units) - 1);

    $pass = 0; // set zero, for Bytes
    while( $filesize >= 1024 && $pass < $unit_count ) {
        $filesize /= 1024;
        $pass++;
    }

    return round($filesize, 2) .'&nbsp;'. $units[$pass];
}

function dismissAndReload() {
    if (isDebugging()) {
        echo "\n<body onLoad='opener.location.reload();'>\n";
        echo '<p align="center" class="error">';
        echo gTranslate('common', "Not closing this window because debug mode is on") ;
        echo "\n<hr>\n</p>";
        echo "\n</body>";
    } else {
        echo "<body onLoad='opener.location.reload(); parent.close()'></body>";
    }
    echo "\n</html>";
}

function reload() {
    echo '<script language="javascript1.2" type="text/JavaScript">';
    echo 'opener.location.reload()';
    echo '</script>';
}

function dismissAndLoad($url) {
    if (isDebugging()) {
        echo("<BODY onLoad='opener.location = \"$url\"; '>");
        echo("Loading URL: $url");
        echo("<center><b>" . gTranslate('common', "Not closing this window because debug mode is on") ."</b></center>");
        echo("<hr>");
    } else {
        echo("<BODY onLoad='opener.location = \"$url\"; parent.close()'>");
    }
}

function dismiss() {
    echo("<BODY onLoad='parent.close()'>");
}

function includeLayout($name, $skinname='') {
    global $gallery;

    $base = dirname(dirname(__FILE__));

    if (!$skinname) {
        $skinname = $gallery->app->skinname;
    }

    $defaultname = "$base/layout/$name";
    $fullname = "$base/skins/$skinname/layout/$name";

    if (fs_file_exists($fullname) && !broken_link($fullname)) {
        include ($fullname);
    } elseif (fs_file_exists($defaultname) && !broken_link($defaultname)) {
        include ($defaultname);
    } else {
        echo gallery_error(sprintf(gTranslate('common', "Problem including file %s"), $name));
    }
}

function includeHtmlWrap($name, $skinname = '') {

    // define these globals to make them available to custom text
    global $gallery;

    $base = dirname(dirname(__FILE__));
    $domainname = $base . '/html_wrap/' . $_SERVER['HTTP_HOST'] . "/$name";

    if (!$skinname) {
        $skinname = $gallery->app->skinname;
    }

    if (fs_file_exists($domainname) && !broken_link($domainname)) {
        include ($domainname);
    }
    else {
        $defaultname = "$base/html_wrap/$name";
        $fullname = "$base/skins/$skinname/html_wrap/$name";

        if (fs_file_exists($fullname) && !broken_link($fullname)) {
            include ($fullname);
        }
        elseif (fs_file_exists($defaultname) && !broken_link($defaultname)) {
            include ($defaultname);
        } else {
            include ("$defaultname.default");
        }
    }

    return 1;
}

/**
 * Wrapper around _getStyleSheetLink, its defines which stylesheet link is generated.
 * @return	string	$styleSheetLinks	The generated HTML <LINK> to load the stylesheets. Empty when already loaded.
 */
function getStyleSheetLink() {
    global $GALLERY_EMBEDDED_INSIDE;
    global $GALLERY_OK;

    static $styleSheetSet;

    $styleSheetLinks = '';

    if(! $styleSheetSet) {
        if (isset($GALLERY_OK) && $GALLERY_OK == false) {
            $styleSheetLinks = _getStyleSheetLink("config");
        } else {
            $styleSheetLinks = _getStyleSheetLink("base");

            if ($GALLERY_EMBEDDED_INSIDE) {
                $styleSheetLinks .= _getStyleSheetLink("embedded_style");
            } else {
                $styleSheetLinks .= _getStyleSheetLink("screen");
            }
        }

        $styleSheetSet = true;
    }

    return $styleSheetLinks;
}

/**
 * Generates a HTML <link> to a css file.
 *
 * @param	string	$filename	Name of css file.
 * @param	string	$skinname	Optional skinname, if omitted and not embedded, default skin is used.
 * @return	string
 */
function _getStyleSheetLink($filename, $skinname='') {
    global $gallery;
    global $GALLERY_EMBEDDED_INSIDE;

    $base = dirname(dirname(__FILE__));

    if (!$skinname && isset($gallery->app) && isset($gallery->app->skinname) && !$GALLERY_EMBEDDED_INSIDE) {
        $skinname = $gallery->app->skinname;
    }

    $sheetname = "skins/$skinname/css/$filename.css";
    $sheetpath = "$base/$sheetname";

    $sheetdefaultdomainname = 'css/'. $_SERVER['HTTP_HOST'] ."/$filename.css";
    $sheetdefaultname = "css/$filename.css";
    $sheetdefaultpath = "$base/$sheetdefaultname";

    if (fs_file_exists($sheetpath) && !broken_link($sheetpath)) {
        $file = $sheetname;
    } elseif (fs_file_exists($sheetdefaultpath) && !broken_link($sheetdefaultpath)) {
        $file = $sheetdefaultname;
    } elseif (fs_file_exists($sheetdefaultdomainname) && !broken_link($sheetdefaultdomainname)) {
        $file = $sheetdefaultdomainname;
    } else {
        $file = $sheetdefaultname. '.default';
    }

    $url = getGalleryBaseUrl() ."/$file";

    return "\n". '  <link rel="stylesheet" type="text/css" href="' .$url . '">';
}

// The following 2 functions, printAlbumOptionList and printNestedVals provide
// a html options list for moving photos and albums around within gallery.  There
// were some defects in the original implimentation (I take full credit for the
// defects), and thus on 5/22/03, I rewrote the 2 functions to conform to the
// following requirements:
//
// For moving albums, there are 2 cases:
// 1. moving root albums:  the user should be able to move a
//    root album to any album to which they have write permissions
//    AND not to an album nested beneath it in the same tree
//    AND not to itself.
// 2. moving nested albums:  the user should be able to move a
//    nested album to any album to which they have write permissions
//    AND not to an album nested beneath it in the same tree
//    AND not to itself
//    AND not to its parent album.
//    The user should also be able to move it to the ROOT level
//    with appropriate permissions.
//
// For moving pictures, there is 1 case:
// 1. moving pictures:  the user should be able to move a picture
//    to any album to which they have write permissions
//    AND not to the album to which it already belongs.
//
// -jpk

function printAlbumOptionList($rootDisplay = true, $moveRootAlbum = false, $movePhoto = false, $readOnly = false) {
    global $gallery, $albumDB, $index;

    $uptodate = true;
    $mynumalbums = $albumDB->numAlbums($gallery->user);

    if (!$readOnly) {
        echo "\n\t<option value=\"0\" selected> << ". gTranslate('common', "Select Album") ." >> </option>\n\t";
    }

    // create a ROOT option for the user to move the
    // album to the main display
    if ($gallery->user->canCreateAlbums() && $rootDisplay && !$readOnly) {
        echo "\n\t<option value=\".root\">". gTranslate('common', "Move to top level") ."</option>\n\t";
    }

    // display all albums that the user can move album to
    for ($i = 1; $i <= $mynumalbums; $i++) {
        $myAlbum = $albumDB->getAlbum($gallery->user, $i);
        $myAlbumName = $myAlbum->fields['name'];
        $myAlbumTitle = $myAlbum->fields['title'];

        if ($gallery->user->canWriteToAlbum($myAlbum) ||
          ($readOnly && $gallery->user->canReadAlbum($myAlbum))) {
            if ($myAlbum->versionOutOfDate()) {
                $uptodate = false;
                continue;
            }
            if (!$readOnly && ($myAlbum == $gallery->album)) {
                // Don't allow the user to move to the current location with
                // value=0, but notify them that this is the current location
                echo "<option value=\"$myAlbumName\">-- $myAlbumTitle (". gTranslate('common', "current location"). ")</option>\n\t";
            } else {
                if (sizeof($gallery->album->fields["votes"]) && $gallery->album->pollsCompatible($myAlbum)) {
                    $myAlbumTitle .= ' *';
                }
                echo "<option value=\"$myAlbumName\">-- $myAlbumTitle</option>\n\t";
            }
        }

        if ( !$readOnly && $moveRootAlbum && ($myAlbum == $gallery->album) && !$movePhoto )  {
            // do nothing -- we are moving a root album, and we don't
            // want to move it into its own album tree

        } elseif (!$readOnly && !$gallery->album->isRoot() &&
            ($myAlbum == $gallery->album->getNestedAlbum($index)) && !$movePhoto )  {
            // do nothing -- we are moving an album, and we don't
            // want to move it into its own album tree
        } else {
            printNestedVals(1, $myAlbumName, $movePhoto, $readOnly);
        }
    }

    return $uptodate;
}

function printNestedVals($level, $albumName, $movePhoto, $readOnly) {
    global $gallery, $index;

    $myAlbum = new Album();
    $myAlbum->load($albumName);

    $numPhotos = $myAlbum->numPhotos(1);

    for ($i = 1; $i <= $numPhotos; $i++) {
        if ($myAlbum->isAlbum($i)) {
            $myName = $myAlbum->getAlbumName($i);
            $nestedAlbum = new Album();
            $nestedAlbum->load($myName);
            if ($gallery->user->canWriteToAlbum($nestedAlbum) ||
              ($readOnly && $gallery->user->canReadAlbum($myAlbum))) {
                $val2 = str_repeat("-- ", $level+1);
                $val2 .= $nestedAlbum->fields['title'];

                if (!$readOnly && ($nestedAlbum == $gallery->album)) {
                    // don't allow user to move to here (value=0), but
                    // notify them that this is their current location
                    echo "<option value=\"0\"> $val2 (". gTranslate('common', "Current location") .")</option>\n\t";
                } elseif (!$readOnly && !$gallery->album->isRoot() &&
                  ($nestedAlbum == $gallery->album->getNestedAlbum($index))) {
                    echo "<option value=\"0\"> $val2 (". gTranslate('common', "This album itself"). ")</option>\n\t";
                } else {
                    echo "<option value=\"$myName\"> $val2</option>\n\t";
                }
            }

            if (!$readOnly && !$gallery->album->isRoot() &&
             ($nestedAlbum == $gallery->album->getNestedAlbum($index)) && !$movePhoto ) {
                // do nothing -- don't allow album move into its own tree
            } else {
                printNestedVals($level + 1, $myName, $movePhoto, $readOnly);
            }
        }
    }
}

/* Formats a nice string to print below an item with comments */
function lastCommentString($lastCommentDate, &$displayCommentLegend) {
    global $gallery;
    if ($lastCommentDate  <= 0) {
        return  '';
    }
    if ($gallery->app->comments_indication_verbose == 'yes') {
        $ret = "<br>".
          sprintf(gTranslate('common', "Last comment %s."), strftime($gallery->app->dateString, $lastCommentDate));
    } else {
        $ret= '<span class="commentIndication">*</span>';
        $displayCommentLegend = 1;
    }
    return $ret;
}

function available_skins($description_only = false) {
    global $gallery;
    $version = '';
    $last_update = '';
    $possibleSkins = array();
    $base = dirname(dirname(__FILE__));

    if (isset($gallery->app->photoAlbumURL)) {
        $base_url = $gallery->app->photoAlbumURL;
    }
    else {
        $base_url = '..';
    }

    $dir = "$base/skins";
    $opts['none'] = gTranslate('common', "No Skin");
    $descriptions = '<dl>';
    $name = "<a href=\"#\" onClick=\"document.config.skinname.options[0].selected=true; return false;\">".
	gTranslate('common', "No Skin") . "</a>";
    $descriptions .= sprintf("<dt>%s</dt>", $name);
    $descriptions .= '<dd>'. gTranslate('common', "The original look and feel.") .'</dd>';
    $skincount = 0;

    if (fs_is_dir($dir) && is_readable($dir) && $fd = fs_opendir($dir)) {
        while ($file = readdir($fd)) {
            $subdir = "$dir/$file/css";
            $skincss = "$subdir/screen.css";
            if (fs_is_dir($subdir) && fs_file_exists($skincss)) {
                $possibleSkins[] = $file;
            }
        }

        sort($possibleSkins);
        foreach($possibleSkins as $file) {
            $subdir = "$dir/$file/css";
            $skininc = "$dir/$file/style.def";
            $name = '';
            $description = '';
            $skincss = "$subdir/screen.css";
            $skincount++;

            if (fs_file_exists($skininc)) {
                require($skininc);
            }

            if (empty($name)) {
                $name = $file;
            }

            $opts[$file]=$name;
            if (fs_file_exists("$dir/$file/images/screenshot.jpg")) {
                $screenshot = $base_url . "/skins/$file/images/screenshot.jpg";
            }
            elseif (fs_file_exists("$dir/$file/images/screenshot.gif")) {
                $screenshot = $base_url . "/skins/$file/images/screenshot.gif";
            }
            else {
                $screenshot = '';
            }

            if ($screenshot) {
                $name = popup_link($name, $screenshot, 1, false,
                500, 800, '', 'document.config.skinname.options['. $skincount. '].selected=true; ');
            }

            $descriptions.="\n<dt style=\"margin-top:5px;\">$name";
            if (!isset ($version)) {
                $version = gTranslate('common', "unknown");
            }

            if (!isset($last_update)) {
                $last_update = gTranslate('common', "unknown");
            }

            $descriptions .= '<span style="margin-left:10px; font-size:x-small">';
            $descriptions .= sprintf(gTranslate('common', "Version: %s"),$version);
            $descriptions .= '&nbsp;&nbsp;&nbsp;';
            $descriptions .= sprintf(gTranslate('common', "Last Update: %s"), $last_update) ."</span></dt>";
            $descriptions .= "<dd style=\"font-weight:bold; background-color:white;\">$description<br></dd>";
        }

        $descriptions .="\n</dl>";

        if ($description_only) {
            return $descriptions;
        } else {
            return $opts;
        }
    }
}

function available_frames($description_only = false) {
    $GALLERY_BASE = dirname(dirname(__FILE__));

    $opts = array(
        'none' => gTranslate('common', "None"),
        'dots' => gTranslate('common', "Dots"),
        'solid' => gTranslate('common', "Solid"),
        );

    $descriptions="<dl>" .
        "<dt>" . popup_link(gTranslate('common', "None"), "frame_test.php?frame=none", 1)  . "</dt><dd>". gTranslate('common', "No frames")."</dd>" .
        "<dt>" . popup_link(gTranslate('common', "Dots"), "frame_test.php?frame=dots", 1)  . "</dt><dd>". gTranslate('common', "Just a simple dashed border around the thumb.")."</dd>" .
        "<dt>" . popup_link(gTranslate('common', "Solid"), "frame_test.php?frame=solid", 1) . "</dt><dd>". gTranslate('common', "Just a simple solid border around the thumb.")."</dd>" ;

    $dir = $GALLERY_BASE . '/html_wrap/frames';

    if (fs_is_dir($dir) && is_readable($dir) && $fd = fs_opendir($dir)) {
        while ($file = readdir($fd)) {
            $subdir = "$dir/$file";
            $frameinc = "$subdir/frame.def";
            if (fs_is_dir($subdir) && fs_file_exists($frameinc)) {
                $name = NULL;
                $description = NULL;
                require($frameinc);
                if (empty($name )) {
                    $name = $file;
                }
                if (empty($description )) {
                    $description = $file;
                }
                $opts[$file] = $name;
                $descriptions.="\n<dt>" . popup_link($name, "frame_test.php?frame=$file", 1) . "</dt><dd>$description</dd>";
            } else {
                if (false && isDebugging()) {
                    echo gallery_error(sprintf(gTranslate('common', "Skipping %s."),
                    $subdir));
                }
            }
        }
    } else {
        echo gallery_error(sprintf(gTranslate('common', "Can't open %s"), $dir));
    }

    $descriptions .= "\n</dl>";
    if ($description_only) {
        return $descriptions;
    } else {
        return $opts;
    }
}

function doctype() {
    echo '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">' . "\n";
}

function common_header($adds = array()) {
    $metaTagAdds = array();
    if(isset($adds['metaTags'])) {
        $metaTagAdds = $adds['metaTags'];
    }

    // Do some meta tags
    metatags($metaTagAdds);

    // Import CSS Style_sheet
    echo getStyleSheetLink();

    // Set the Gallery Icon
    echo "\n  <link rel=\"shortcut icon\" href=\"". makeGalleryUrl('images/favicon.ico') . "\">\n";
}

function metatags($adds = array()) {
    global $gallery;

    echo '<meta http-equiv="content-style-type" content="text/css">';
    echo "\n  ". '<meta http-equiv="content-type" content="Mime-Type; charset='. $gallery->charset .'">';
    echo "\n  ". '<meta name="content-language" content="' . str_replace ("_","-",$gallery->language) . '">';

    if(!empty($adds)) {
        foreach ($adds as $name => $content) {
            echo "\n  ". '<meta name="'. $name .'" content="'. $content .'">';
        }
    }
    echo "\n";
}

/**
 * Generates a link to w3c validator
 *
 * @param	string	$file	file to validate, relative to gallery dir
 * @param	boolean	$valid	true/false wether we know the result ;)
 * @param	array	$arg	optional array with urlargs
 * @return	string	$link	HTML hyperlink
 */
function gallery_validation_link($file, $valid=true, $args = array()) {
    global $gallery;

    if (isset($gallery->app->devMode) && $gallery->app->devMode == "no") {
        return '';
    }

    $args['PHPSESSID'] = session_id();
    $url = makeGalleryURL($file, $args);

    if (!empty($file) && isset($gallery->app->photoAlbumURL)) {
        $uri = urlencode(eregi_replace("&amp;", "&", $url));
    }
    else {
        $uri = 'referer&amp;PHPSESSID='. $args['PHPSESSID'];
    }

    $link = '<a href="http://validator.w3.org/check?uri='. $uri .'">'.
      '<img border="0" src="http://www.w3.org/Icons/valid-html401" alt="Valid HTML 4.01!" height="31" width="88"></a>';

    if (!$valid) {
        $link .= gTranslate('common', "Not valid yet");
    }

    return $link;
}

// uses makeAlbumURL
function album_validation_link($album, $photo='', $valid=true) {
    global $gallery;
    if ($gallery->app->devMode == "no") {
        return '';
    }
    $args=array();
    $args['PHPSESSID']=session_id();
    $link='<a href="http://validator.w3.org/check?uri='.
      urlencode(eregi_replace("&amp;", "&",
      makeAlbumURL($album, $photo, $args))).
      '"> <img border="0" src="http://www.w3.org/Icons/valid-html401" alt="Valid HTML 4.01!" height="31" width="88"></a>';

    if (!$valid) {
        $link .= gTranslate('common', "Not valid yet");
    }
    return $link;
}

/**
 * This function outputs the HTML Start elements of an Popup.
 * It was made to beautify php code ;)
 */
function printPopupStart($title = '', $header = '', $align = 'center') {
	global $gallery;
	if (!empty($title) && empty($header)) {
		$header = $title;
	}
?>
<html>
<head>
  <title><?php echo $title; ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>" class="popupbody">
<div class="popuphead"><?php echo $header; ?></div>
<div class="popup" align="<?php echo $align; ?>">

<?php
}


function showImageMap($index) {
    global $gallery;

    $allImageAreas = $gallery->album->getAllImageAreas($index);
    $html = '';

    if (!empty($allImageAreas)) {
        $html .= "\n". '<map name="myMap">';
        foreach($allImageAreas as $nr => $area) {
            $html .= "\n\t<area alt=\"my nice Map $nr\" title=\"my nice Map $nr\" shape=\"poly\" ".
                "coords=\"". $area['coords'] ."\" ".
                "onmouseover=\"return escape('". $area['hover_text'] ."')\"";

            if(!empty($area['url'])) {
                $html .=' href="'. $area['url'] .'"';
            }
            $html .='>';
        }
        $html .= "\n</map>\n";
    }

    return $html;
}

/**
 * Generates a complete <img ...> html
 * @param $relativPath  string  path to the images relativ to gallery root
 * @param $altText      string  alt Text
 * @param $attrs        array   optional additional attributs (id, name..)
 * @param $skin		string	optional input of skin, because the image could be in skindir.
 * @author Jens Tkotz <jens@peino.de>
 */
function gImage($relativePath, $altText, $attrs = array(), $skin = '') {
    global $gallery;

    $html = '';
    $imgUrl = getImagePath($relativePath, $skin);

    $html .= "<img src=\"$imgUrl\" alt=\"$altText\" title=\"$altText\"";

    if(!empty($attrs)) {
        foreach ($attrs as $key => $value) {
            $html .= " $key=\"$value\"";
        }
    }
    $html .= '>';

    return $html;
}

?>