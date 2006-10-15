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

/**
 * @package	Layout
 * @author	Jens Tkotz
 */

/** Shows the content of a field and if permitted also a link to the edit popup
 * @param   object  $album
 * @param   string  $field
 * @param   string  $url
 * @return  string  $html
*/
function editField($album, $field, $url = null) {
    global $gallery;

    if($url) {
        $html = galleryLink($url, $album->fields[$field]);
    }
    else {
        $html = $album->fields[$field];
    }

    if ($gallery->user->canChangeTextOfAlbum($album)) {
        if (empty($album->fields[$field])) {
            $html = "<i>&lt;". gTranslate('common', "Empty") . "&gt;</i>";
        }
        // should replace with &amp; for validatation
        $url = "edit_field.php?set_albumName={$album->fields['name']}&field=$field";

        $html .= ' '. popup_link(sprintf(gTranslate('common', "edit %s"), _($field)), $url, 0,true, 500, 500, 'g-small');
    }

    return $html;
}

/** Shows the caption of an albumitem and if permitted also a link to the edit popup
 * @param   object  $album
 * @param   integer $index	albumitem index
 * @return  string  $html
*/
function editCaption($album, $index) {
    global $gallery;

    $html  = nl2br($album->getCaption($index));

    if (($gallery->user->canChangeTextOfAlbum($album) ||
      ($gallery->album->getItemOwnerModify() &&
      $gallery->album->isItemOwner($gallery->user->getUid(), $index))) &&
      !$gallery->session->offline) {

        if (empty($html)) {
            $html = '<i>&lt;'. gTranslate('common', "No Caption") .'&gt;</i>';
        }
        $url = "edit_caption.php?set_albumName={$album->fields['name']}&index=$index";
        $html .= popup_link(gTranslate('common',"edit"), $url);
    }

    $html .= $album->getCaptionName($index);

    return $html;
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
            echo '<br>'. makeFormIntro($page_url);
            drawCommentAddForm($commenter_name);
            echo '</form>';
        }
        else {
            $id = $gallery->album->getPhotoId($index);
            $url = "add_comment.php?set_albumName={$gallery->album->fields['name']}&id=$id";
            echo popup_link(gTranslate('common', "_Add comment"), $url);
            echo "<br><br>";
        }
    }
}

function drawCommentAddForm($commenter_name = '', $cols = 50) {
    global $gallery;
    if ($gallery->user->isLoggedIn() &&
      (empty($commenter_name) || $gallery->app->comments_anonymous == 'no')) {
        $commenter_name = $gallery->user->printableName($gallery->app->name_display);
    }
?>

<table class="g-commentadd-box" cellpadding="0" cellspacing="0">
<tr>
	<th colspan="2"><?php echo gTranslate('common', "Add your comment") ?></th>
</tr>
<tr>
	<td class="g-commentadd-box-head left"><?php echo gTranslate('common', "Commenter:"); ?></td>
	<td class="g-commentadd-box-head">
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
	<td class="g-commentadd-box-middle right"><?php echo gTranslate('common', "Message:") ?></td>
	<td><textarea name="comment_text" cols="<?php echo $cols ?>" rows="5"></textarea></td>
</tr>
<tr>
	<td colspan="2" class="g-commentadd-box-footer right">
	  <input name="save" type="submit" value="<?php echo gTranslate('common', "Post comment") ?>" class="g-button">
        </td>
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
			if (strlen($cookie1_name) == 32 &&
				strlen($cookie1_value) == 32) {
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

function createTreeArray($albumName,$depth = 0, $fromSetup = false) {
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
            if ($gallery->user->canReadAlbum($nestedAlbum) || $fromSetup) {
                $title = $nestedAlbum->fields['title'];
                if (!strcmp($nestedAlbum->fields['display_clicks'], 'yes')
                  && !$gallery->session->offline) {
                    $clicksText = "(" . gTranslate('common', "1 view", "%d views", $nestedAlbum->getClicks(), '', true) . ")";
                } else {
                    $clicksText = '';
                }

		$albumUrl = makeAlbumUrl($myName);
		$subtree = createTreeArray($myName, $depth+1);
		$highlightTag = $nestedAlbum->getHighlightTag(
				$gallery->app->default["nav_thumbs_size"],
                array('alt' => "$title $clicksText")
        );
		$microthumb = "<a href=\"$albumUrl\">$highlightTag</a> ";
		$tree[] = array(
		    'albumUrl' => $albumUrl,
		    'albumName' => $myName,
		    'title' => $title,
		    'clicksText' => $clicksText,
		    'microthumb' => $microthumb,
		    'subTree' => $subtree);
            }
        }
    }

    return $tree;
}

function printChildren($tree, $depth = 0, $parentNode = 'main') {
    $html = '';

	if ($depth == 0 && !empty($tree)) {
        $treeName = $tree[0]['albumName'];

        $html = '<div style="font-weight: bold; margin-bottom: 3px">'. gTranslate('common', "Sub-albums:") ."</div>\n";

        $html = "<div id=\"tree_$treeName\"></div>
        <script type=\"text/javascript\">
            var tree;

            tree_$treeName = new YAHOO.widget.TreeView(\"tree_$treeName\");
            tree_${treeName}.setExpandAnim(YAHOO.widget.TVAnim.FADE_IN);
            tree_${treeName}.setCollapseAnim(YAHOO.widget.TVAnim.FADE_OUT);
            var root = tree_${treeName}.getRoot();

            var main = new YAHOO.widget.TextNode(\"". gTranslate('common', "Sub-albums:") ."\", root, false);
        ";
	}

	foreach($tree as $nr => $content) {
        $nodename = $content['albumName'];

        $label = $content['title'] . ' '. $content['clicksText'];
        $html .= "\n\t var ${nodename}_obj = { label: \"$label\", href:\"${content['albumUrl']}\" }";
        $html .= "\n\t var $nodename = new YAHOO.widget.TextNode(${nodename}_obj, $parentNode, false);";

        if(!empty($content['subTree'])) {
			$html .= printChildren($content['subTree'], $depth+1, $nodename);
		}
    }

    if ($depth == 0 && !empty($tree)) {
        $html .= "\n\n\t tree_{$treeName}.draw();";
        $html .= "\n\n\t </script>\n";
    }

	return $html;
}

function printMicroChildren2($tree, $depth = 0) {
    $html = '';
    if ($depth == 0 && !empty($tree)) {
        $html = '<div style="font-weight: bold; margin-bottom: 3px">'. gTranslate('common', "Sub-albums:") ."</div>\n";
    }

    foreach($tree as $nr => $content) {
        $html .= $content['microthumb'];
        if(!empty($content['subTree'])) {
            $html .= printMicroChildren2($content['subTree'], $depth+1);
        }
    }
    return $html;
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
function displayPhotoFields($index, $extra_fields, $withExtraFields = true, $withExif = true, $full = NULL, $forceRefresh = false) {
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
        $myExif = $gallery->album->getExif($index, $forceRefresh);

        if (!empty($myExif) && !isset($myExif['Error'])) {
            $tables[gTranslate('common', "EXIF Data")]  = $myExif;
        }
        elseif (isset($myExif['status']) && $myExif['status'] == 1) {
            echo infoBox(array(array(
		'text' => gTranslate('common', "Display of EXIF data enabled, but no data found.")
	    )), '', false);
        }
    }

    if (!isset($tables)) {
        return;
    }

    foreach ($tables as $caption => $fields) {
        $customFieldsTable = new galleryTable();
        $customFieldsTable->setAttrs(array('class' => 'g-customFieldsTable'));
        $customFieldsTable->setCaption($caption, 'g-columnheader');

        foreach ($fields as $key => $value) {
            $customFieldsTable->addElement(array('content' => $key));
            $customFieldsTable->addElement(array('content' => ':'));
            $customFieldsTable->addElement(array('content' => $value));
        }
        echo $customFieldsTable->render();
    }
}

/**
 * Displays the ownename, if an email is available, then as mailto: link
 * @param  object  $owner
 * @return string
 * @author Jens Tkotz <jens@peino.de
 */
function showOwner($owner) {
    global $gallery;
    global $GALLERY_EMBEDDED_INSIDE_TYPE;
    global $_CONF;				/* Needed for GeekLog */

    switch ($GALLERY_EMBEDDED_INSIDE_TYPE) {
        case 'GeekLog':
            $name = '<a href="'. $_CONF['site_url'] .'/users.php?mode=profile&uid='. $owner->uid .'">'. $owner->displayName() .'</a>';
        break;

        default:
            $name = $owner->printableName($gallery->app->name_display);
        break;
    }
    return $name;
}

function getIconText($iconName = '', $text = '', $overrideMode = '', $addBrackets = true, $altText = '', $stickyAlt = false) {
    global $gallery;

    if(empty($altText)) {
    	$altText = $text;
    }

    $text = makeAccessKeyString($text);
    getAndRemoveAccessKey($altText);

    if (!empty($overrideMode)) {
    	$iconMode = $overrideMode;
    } elseif (isset($gallery->app->useIcons)) {
    	$iconMode = $gallery->app->useIcons;
    } else {
    	$iconMode = 'no';
    }

    if ($iconMode != "no" && $iconName != '') {
    	if ($iconMode == 'both' && !$stickyAlt) {
    		$altText = '';
    	}

    	$linkText = gImage("icons/$iconName", $altText);

    	if ($iconMode == "both") {
    		$linkText .= "<br>$text";
    	}
    }

    if (empty($linkText)) {
    	if(empty($text)) {
    		$text = $altText;
    	}
    	if($addBrackets) {
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
    if ($gallery->direction == 'rtl' && isset($align) && $align != 'center') {
        $align = ($align == 'left') ? 'right' : 'left';
    }

    $html = "\n<table class=\"g-iconmenu\" align=\"$align\" cellspacing=\"0\" cellpadding=\"0\">\n<tr>";
    $i = 0;
    foreach ($iconElements as $element) {
        $i++;
        if (stristr($element,'</a>')) {
            $html .= "\n\t<td>$element</td>";
        } else {
            $html .= "\n\t<td style=\"padding: 2px;\" class=\"g-icon-nolink\">$element</td>";
        }
        if($i > sizeof($iconElements)/2 && $linebreak) {
            $html .= "\n</tr>\n<tr>";
            $i = 0;
        }
    }

    if ($closeTable == true) {
        $html .= "\n</tr>\n</table>";
    }

    return $html;
}

/**
 * @param	string	$formerSearchString    Optional former search string
 * @param	string	$align                 Optional alignment
 * @return	string	$html                  HTML code that contains a form for entering the searchstring
 * @author	Jens Tkotz <jens@peino.de>
 */
function addSearchForm($formerSearchString = '') {
    $html = '';

    $html .= makeFormIntro('search.php', array(
        'name'    => 'search_form',
        'class'   => 'g-search-form')
    );

    $html .= gInput('text', 'searchstring', gTranslate('common', "_Search:"), false, $formerSearchString,
            array('class' => 'g-search-form', 'size' => 25));
    $html .= "</form>\n";

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
        common_header();
        echo infoBox(array(
            array('type' => 'information',
            'text' => gTranslate('common', "Not closing this window because debug mode is on.")
        )));
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
        echo "<body onLoad='opener.location = \"$url\"; '>";
        common_header();
        echo infoBox(array(
            array(
                'type' => 'information',
                'text' => sprintf(gTranslate('common', "Loading URL: %s"), $url)
            ),
            array(
                'type' => 'information',
                'text' => gTranslate('common', "Not closing this window because debug mode is on.")
            )
        ));
    } else {
        echo("<body onLoad='opener.location = \"$url\"; parent.close()'>");
    }
}

function dismiss() {
    echo("<body onLoad='parent.close()'>");
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
    }
    elseif (fs_file_exists($defaultname) && !broken_link($defaultname)) {
        include ($defaultname);
    }
    else {
        echo gallery_error(sprintf(gTranslate('common', "Problem including file %s"), $name));
    }
}

function includeTemplate($name, $skinname = '', $theme = '') {
    global $gallery;

    $base = dirname(dirname(__FILE__));
    $domainname = $base . '/templates/' . $_SERVER['HTTP_HOST'] . "/$name";

    $name = "$name.tpl";

    if(!$theme) {
        $theme = $gallery->app->theme;
    }

    if (!$skinname) {
        $skinname = $gallery->app->skinname;
    }

    if (fs_file_exists($domainname) && !broken_link($domainname)) {
        require($domainname);
    }
    else {
        $defaultname = "$base/templates/$name";
        $defaultThemeName = "$base/templates/$theme/$name";
        $fullName = "$base/skins/$skinname/templates/$name";

        if (fs_file_exists($fullName) && !broken_link($fullName)) {
            require ($fullName);
        }
        elseif (fs_file_exists($defaultname) && !broken_link($defaultname)) {
            require($defaultname);
        }
        elseif (fs_file_exists("$defaultname.default") && !broken_link("$defaultname.default")) {
            require("$defaultname.default");
        }
        elseif (fs_file_exists("$defaultThemeName") && !broken_link("$defaultThemeName")) {
            require("$defaultThemeName");
        }
        elseif (fs_file_exists("$defaultThemeName.default") && !broken_link("$defaultThemeName.default")) {
            require("$defaultThemeName.default");
        }
        else {
            return false;
        }
    }

    return true;
}

/**
 * Wrapper around _getStyleSheetLink, its defines which stylesheet link is generated.
 * @return	string	$styleSheetLinks	The generated HTML <LINK> to load the stylesheets. Empty when already loaded.
 */
function getStyleSheetLink() {
	global $gallery, $GALLERY_EMBEDDED_INSIDE, $GALLERY_OK;
	static $styleSheetSet;

	$styleSheetLinks = '';

	if(! $styleSheetSet) {
		$styleSheetLinks = _getStyleSheetLink("base");
		if(isset($gallery->direction) && $gallery->direction == 'rtl') {
			$styleSheetLinks .= _getStyleSheetLink("rtl");
		}
		else {
			$styleSheetLinks .= _getStyleSheetLink("ltr");
		}


		if ($GALLERY_EMBEDDED_INSIDE) {
			$styleSheetLinks .= _getStyleSheetLink("embedded_style");
		} else {
			$styleSheetLinks .= _getStyleSheetLink("screen");
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
function _getStyleSheetLink($filename, $skinname = '') {
    global $gallery;
    global $GALLERY_EMBEDDED_INSIDE;

    $base = dirname(dirname(__FILE__));

    if (!$skinname &&
      isset($gallery->app) &&
      isset($gallery->app->skinname) &&
      !$GALLERY_EMBEDDED_INSIDE) {
        $skinname = $gallery->app->skinname;
    }

    $sheetname = "skins/$skinname/css/$filename.css";
    $sheetdefaultname = "css/$filename.css";

    if (fs_file_exists("$base/$sheetname")) {
        $file = $sheetname;
    }
    elseif (fs_file_exists("$base/${sheetname}.default")) {
        $file = "${sheetname}.default";
    }
    elseif (fs_file_exists("$base/$sheetdefaultname")) {
        $file = $sheetdefaultname;
    }
    else {
        $file = "${sheetdefaultname}.default";
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

/**
 * Formats a nice string to print below an item with comments
 * @param  int		$lastCommentDate		Timestamp of last comment
 * @param  boolean	$displayCommentLegend	indicator wether a Legend showed be showed later.
 * @return string	$html
 */
function lastCommentString($lastCommentDate, &$displayCommentLegend) {
    global $gallery;
    if ($lastCommentDate  <= 0) {
        return  '';
    }
    if ($gallery->app->comments_indication_verbose == 'yes') {
        $html = "<br>".
          sprintf(gTranslate('common', "Last comment %s."), strftime($gallery->app->dateString, $lastCommentDate));
    } else {
        $html= '<span class="g-commentIndication">*</span>';
        $displayCommentLegend = true;
    }
    return $html;
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
            if($file === '.' || $file === '..') continue;
            $subdir = "$dir/$file/css";
            $skincss = "$subdir/screen.css";
            if (fs_is_dir($subdir) &&
               /* When all 1.6 skins are converted use this line ! */
               //(fs_file_exists($skincss. '.default') || fs_file_exists($skincss))) {
               fs_file_exists($skincss. '.default')) {
                $possibleSkins[] = $file;
            }
        }

        sort($possibleSkins);
        foreach($possibleSkins as $file) {
            $subdir = "$dir/$file/css";
            $skininc = "$dir/$file/style.def";
            $name = '';
            $description = '';
            $skincount++;

            if (fs_file_exists($skininc)) {
                require($skininc);
            }

            if (empty($name)) {
                $name = $file;
            }

            $opts[$file] = $name;
            if (fs_file_exists("$dir/$file/images/screenshot.jpg")) {
                $screenshot = $base_url . "/skins/$file/images/screenshot.jpg";
            } elseif (fs_file_exists("$dir/$file/images/screenshot.gif")) {
                $screenshot = $base_url . "/skins/$file/images/screenshot.gif";
            } else {
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

function availableRandomBlockFrames() {
    $html = gTranslate('config', sprintf("In Addition to the %s, you also use the following opportunities:",
        popup_link(gTranslate('config', "usual thumbs"), makeGalleryURL('setup/frame_test.php'), true)));

    $html .=
	"\n<dl>".
        "\n<dt><u>". gTranslate('common',"Album image frames") ."</u></dt>" .
		"<dd>". gTranslate('common',"Frame defined for images in the corresponding album") ."</dd>".
        "\n<dt><u>". gTranslate('common',"Album thumb frames") ."</u></dt>" .
		"<dd>". gTranslate('common',"Frame defined for thumbs in the corresponding album") ."</dd>".
        "\n<dt><u>". gTranslate('common',"Mainpage thumb frames") ."</u></dt>" .
		"<dd>". gTranslate('common',"Frame defined for thumbs on mainpage") . "</dd>" .
	"\n</dl>";

   return $html;
}

function available_frames($description_only = false, $forRandomBlock = false) {
    $GALLERY_BASE = dirname(dirname(__FILE__));
    $opts = array();

    if ($forRandomBlock) {
	   $opts = array(
            'albumImageFrame' => '* '. gTranslate('common',"Album image frames") .' *',
            'albumThumbFrame' => '* '. gTranslate('common',"Album thumb frames") .' *',
            'mainThumbFrame' => '* '. gTranslate('common',"Mainpage thumb frames") .' *'
        );
    }

    $opts = array_merge($opts, array(
        'none' => gTranslate('common', "None"),
        'dots' => gTranslate('common', "Dots"),
        'solid' => gTranslate('common', "Solid"),
        'siriux' => 'Siriux',
        )
    );

    $descriptions= "<dl>" .
        "<dt>". popup_link(gTranslate('common', "None"), "frame_test.php?frame=none", true)  ."</dt><dd>".
            gTranslate('common', "No frames")."</dd>".
        "<dt>". popup_link(gTranslate('common', "Dots"), "frame_test.php?frame=dots", true)  ."</dt><dd>".
            gTranslate('common', "Just a simple dashed border around the thumb.")."</dd>" .
        "<dt>". popup_link(gTranslate('common', "Solid"), "frame_test.php?frame=solid", true) ."</dt><dd>".
            gTranslate('common', "Just a simple solid border around the thumb.")."</dd>" .
        "<dt>". popup_link('Siriux', "frame_test.php?frame=siriux", true) ."</dt><dd>" .
            gTranslate('common', "The frame from Nico Kaisers Siriux theme.")."</dd>" ;

    $dir = $GALLERY_BASE . '/layout/frames';

    if (fs_is_dir($dir) && is_readable($dir) && $fd = fs_opendir($dir)) {
        while ($file = readdir($fd)) {
            $subdir = "$dir/$file";
            $frameinc = "$subdir/frame.def";
            if (fs_is_dir($subdir) && fs_file_exists($frameinc)) {
                $name = NULL;
                $description = NULL;
                require($frameinc);
                if (empty($name)) {
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

function showImageMap($index) {
    global $gallery;

    $allImageAreas = $gallery->album->getAllImageAreas($index);
    $html = '';

    if (!empty($allImageAreas)) {
        $html .= "\n". '<map name="myMap">';
        foreach($allImageAreas as $nr => $area) {
            $html .= "\n\t<area alt=\"my nice Map $nr\" title=\"my nice Map $nr\" shape=\"poly\" ".
                "coords=\"". $area['coords'] ."\" ".
                "onmouseover=\"return escape('". $area['hover_text'] ."')\" href=\"#\"";

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
function gImage($relativePath, $altText = '', $attrList = array(), $skin = '') {
    global $gallery;

    $html = '';

    getAndRemoveAccessKey($altText);
    $attrList['src'] = getImagePath($relativePath, $skin);
    $attrList['alt'] = $altText;
    $attrList['title'] = $altText;

    if(!empty($attrList['src'])) {
        $attrs = generateAttrs($attrList);
        $html .= "<img$attrs>";
    }

    return $html;
}

/**
 * Returns a html string that represents the login/logout button, or just the text.
 * @return string	$html
 * @author Jens Tkotz<jens@peino.de>
*/
function LoginLogoutButton($returnUrl) {
	global $gallery, $GALLERY_EMBEDDED_INSIDE;
	$html = '';

	if (!$GALLERY_EMBEDDED_INSIDE && !$gallery->session->offline) {
		if ($gallery->user->isLoggedIn()) {
			$html = galleryIconLink($returnUrl, 'logout.gif', gTranslate('common', "log_out"));
		}
		else {
			$html = popup_link(
				gTranslate('common', "log_in"),
				'login.php', false, true, 500, 500, '','','login.gif');
		}
	}
	return $html;
}

/**
 * Returns the accesskey of a string
 * @param   string  $text
 * @return  string  $accesskey
 * @author  Jens Tkotz
 */
function getAccessKey($text) {
    $pos = strpos($text, '_');
    $accesskey = false;

    if ($pos !== false) {
        $accesskey = substr($text,$pos+1,1);
    }
    return $accesskey;
}

function makeAccessKeyString($text) {
    $accesskey = false;
    $pos = strpos($text, '_');

    if ($pos !== false) {
        $accesskey = substr($text,$pos+1,1);
        $text = substr_replace($text, '<span class="g-accesskey">'. $accesskey .'</span>', $pos,2);
    }
    return $text;
}

/**
 * Modifies a string so that the accesskey is surrounded by span tag.
 * returns the access key.
 * @param   string  $text
 * @return  mixed   $accesskey  the accesskey, or null if no accesskey found
 * @author  Jens Tkotz
 */
function getAndSetAccessKey(& $text) {
    $accesskey = false;
    $pos = strpos($text, '_');

    if ($pos !== false) {
        $accesskey = substr($text,$pos+1,1);
        $altText = substr_replace($text, '', $pos,1);
        $text = substr_replace($text, '<span class="g-accesskey">'. $accesskey .'</span>', $pos,2);
    }
    return $accesskey;
}

/**
 * Modifies the input string, remove the accesskey and returns it.
 * @param   string  & $text
 * @return  string  $accesskey
 * @author  Jens Tkotz
 */
function getAndRemoveAccessKey(& $text) {
    $pos = strpos($text, '_');
    $accesskey = false;

    if ($pos !== false) {
        $accesskey = substr($text,$pos+1,1);
        $text = substr_replace($text, '', $pos,1);
    }
    return $accesskey;
}

/**
 * Removes an accesskey from a string
 * @param   string  $text
 * @return  string  $text
 * @author  Jens Tkotz
 */
function removeAccessKey($text) {
    $pos = strpos($text, '_');
    $accesskey = false;

    if ($pos !== false) {
        $accesskey = substr($text,$pos+1,1);
        $text = substr_replace($text, '', $pos,1);
    }
    return $text;
}

/**
 * Returns the HTML code for loading YUI autocomplete Javascript
 *
 * @return  string  $html
 * @author  Jens Tkotz <jens@peino.de
*/
function autoCompleteJS() {
    global $gallery;

    $baseUrl = getGalleryBaseUrl();

    $html = '
    <!-- Dependencies -->
    <script type="text/javascript" src="' . $baseUrl . '/js/yui/yahoo-min.js"></script>
    <script type="text/javascript" src="' . $baseUrl . '/js/yui/dom-min.js"></script>
    <script type="text/javascript" src="' . $baseUrl . '/js/yui/event-min.js"></script>

    <!-- OPTIONAL: Connection (required only if using XHR DataSource) -->
    <script type="text/javascript" src="' . $baseUrl . '/js/yui/connection-min.js"></script>

    <!-- OPTIONAL: Animation (required only if enabling animation) -->
    <script type="text/javascript" src="' . $baseUrl . '/js/yui/animation-min.js"></script>

    <!-- Source file -->
    <script type="text/javascript" src="' . $baseUrl . '/js/yui/autocomplete-min.js"></script>
';

    return $html;
}


/**
 * Returns the HTML/Javascript code that initializes an autocomplete field
 * if 4th param is false, then just an input field is returned.
 *
 * @param   string  $label      descriptive Text
 * @param   string  $inputName  name of the input field
 * @param   string  $id         id of the input field
 * @return  string  $html       Output
 * @author  Jens Tkotz <jens@peino.de>
 */
function initAutocompleteJS ($label, $inputName, $id, $enableAutocomplete = false, $disabled = false) {
    global $gallery;

    $disable = ($disabled) ? ' disabled' : '';

    $html = "
    <div class=\"YUIsearchdiv right5 floatleft\">$label
        <input name=\"$inputName\" id=\"$id\" class=\"YUIsearchinput\" type=\"text\" size=\"75\"$disable>
        <div id=\"${id}_container\" class=\"YUIsearchcontainer\"></div>
    </div>
    ";

    if($enableAutocomplete) {
        $html .= '
<script type="text/javascript">
    oACDS = new YAHOO.widget.DS_XHR("' . $gallery->app->photoAlbumURL .'/lib/autocomplete/YUIsearch_files.php", ["\n", "\t"]);
    oACDS.responseType = YAHOO.widget.DS_XHR.prototype.TYPE_FLAT;
    oACDS.maxCacheEntries = 50;
    oACDS.queryMatchSubset = true;

    // Instantiate auto complete
    oAutoComp = new YAHOO.widget.AutoComplete(\''. $id .'\',\''. $id .'_container\', oACDS);
    oAutoComp.queryDelay = 0;
    oAutoComp.typeAhead = true;
    oAutoComp.useShadow = true;
    oAutoComp.allowBrowserAutocomplete = false;
    oAutoComp.autoHighlight = false;
    oAutoComp.useIFrame = true;
</script>';
    }

    return $html;
}
?>