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
/**
 * @package Item
 */

/**
 * You have icons enabled, but dont like the item options to be icons.
 * You prefer a combobox ?
 * Set setting below to false
 */
$iconsForItemOptions = true;

require_once(dirname(__FILE__) . '/init.php');

list($full, $id, $index, $votes) = getRequestVar(array('full', 'id', 'index', 'votes'));
list($save, $commenter_name, $comment_text) = getRequestVar(array('save', 'commenter_name', 'comment_text'));

// Hack check and prevent errors
if (empty($gallery->session->albumName) || !$gallery->user->canReadAlbum($gallery->album) || !$gallery->album->isLoaded()) {
    $gallery->session->gRedirDone = false;
    header("Location: " . makeAlbumHeaderUrl('', '', array('gRedir' => 1)));
    return;
}

// Set $index from $id
if (isset($id)) {
    $index = $gallery->album->getPhotoIndex($id);
    if ($index == -1) {
        // That photo no longer exists.
        header("Location: " . makeAlbumHeaderUrl($gallery->session->albumName));
        return;
    }
} else {
    if ($index > $gallery->album->numPhotos(1)) {
        $index = $numPhotos;
    }
    $id = $gallery->album->getPhotoId($index);
}

$nextId = getNextId($id);

// Determine if user has the rights to view full-sized images
if (!empty($full) && !$gallery->user->canViewFullImages($gallery->album)) {
    header("Location: " . makeAlbumHeaderUrl($gallery->session->albumName, $id));
    return;
} elseif (!$gallery->album->isResized($index) && !$gallery->user->canViewFullImages($gallery->album)) {
    header("Location: " . makeAlbumHeaderUrl($gallery->session->albumName));
    return;
}

if (!isset($full) || (isset($full) && !$gallery->album->isResized($index))) {
    $full = NULL;
}

if (!empty($votes)) {
    if (!isset($votes[$id]) &&
        $gallery->album->getPollScale() == 1 &&
        $gallery->album->getPollType() == "critique") {
        $votes[$id] = null;
    }

    saveResults($votes);
    if ($gallery->album->getPollShowResults()) {
        list($buf, $rank)=showResultsGraph(0);
        print $buf;
    }
}

$albumName = $gallery->session->albumName;
if (!isset($gallery->session->viewedItem[$gallery->session->albumName][$id]) &&
  !$gallery->session->offline) {
    $gallery->session->viewedItem[$albumName][$id] = 1;
    $gallery->album->incrementItemClicks($index);
}

$photo = $gallery->album->getPhoto($index);

if ($photo->isMovie()) {
    $image = $photo->thumbnail;
} else {
    $image = $photo->image;
}

$photoURL = $gallery->album->getAlbumDirURL("full") . "/" . $image->name . "." . $image->type;
list($imageWidth, $imageHeight) = $image->getRawDimensions();

$do_fullOnly = isset($gallery->session->fullOnly) &&
    !strcmp($gallery->session->fullOnly,"on") &&
    strcmp($gallery->album->fields["use_fullOnly"],"yes");
    
if ($do_fullOnly) {
    $full = $gallery->user->canViewFullImages($gallery->album);
}

$fitToWindow = !strcmp($gallery->album->fields["fit_to_window"], "yes") &&
    !$gallery->album->isMovieByIndex($index) &&
    !$gallery->album->isResized($index) &&
    !$full &&
    (!$GALLERY_EMBEDDED_INSIDE || $GALLERY_EMBEDDED_INSIDE =='phpBB2');

$numPhotos = $gallery->album->numPhotos($gallery->user->canWriteToAlbum($gallery->album));

$next = $index+1;
if ($next > $numPhotos) {
    //$next = 1;
    $last = 1;
}

$prev = $index-1;
if ($prev <= 0) {
    //$prev = $numPhotos;
    $first = 1;
}

/*
* We might be prev/next navigating using this page
*  so recalculate the 'page' variable
*/
$rows = $gallery->album->fields["rows"];
$cols = $gallery->album->fields["cols"];
$perPage = $rows * $cols;
$page = (int)(ceil($index / ($rows * $cols)));

$gallery->session->albumPage[$gallery->album->fields['name']] = $page;

/*
* Relative URLs are tricky if we don't know if we're rewriting
* URLs or not.  If we're rewriting, then the browser will think
* we're down 1 dir farther than we really are.  Use absolute
* urls wherever possible.
*/
$top = $gallery->app->photoAlbumURL;

$bordercolor = $gallery->album->fields["bordercolor"];

$mainWidth = "100%";

$navigator["id"] = $id;
$navigator["allIds"] = $gallery->album->getIds($gallery->user->canWriteToAlbum($gallery->album));
$navigator["fullWidth"] = "100";
$navigator["widthUnits"] = "%";
$navigator["url"] = ".";
$navigator["bordercolor"] = $bordercolor;

#-- breadcrumb text ---
$upArrowURL = '<img src="' . getImagePath('nav_home.gif') . '" width="13" height="11" '.
  'alt="' . gTranslate('core', "navigate UP") .'" title="' . gTranslate('core', "navigate UP") .'" border="0">';

foreach ($gallery->album->getParentAlbums(true) as $navAlbum) {
    $breadcrumb["text"][] = $navAlbum['prefixText'] .': <a class="bread" href="'. $navAlbum['url'] . '">'.
      $navAlbum['title'] . "&nbsp;" . $upArrowURL . "</a>";
}

$extra_fields = $gallery->album->getExtraFields(false);
$title = NULL;
if (in_array("Title", $extra_fields)) {
    $title = $gallery->album->getExtraField($index, "Title");
}
if (!$title) {
    $title = $photo->image->name;
}

if (isset($gallery->app->comments_length)) {
    $maxlength = $gallery->app->comments_length;
} else {
    $maxlength = 0;
}

if (!empty($save)) {
    if ( empty($commenter_name) || empty($comment_text)) {
        $error_text = gTranslate('core', "Name and comment are both required to save a new comment!");
    } elseif ($maxlength >0 && strlen($comment_text) > $maxlength) {
        $error_text = sprintf(gTranslate('core', "Your comment is too long, the admin set maximum length to %d chars"), $maxlength);
    } elseif (isBlacklistedComment($tmp = array('commenter_name' => $commenter_name, 'comment_text' => $comment_text), false)) {
        $error_text = gTranslate('core', "Your Comment contains forbidden words. It will not be added.");
    } else {
        $comment_text = $comment_text;
        $commenter_name = $commenter_name;
        $IPNumber = $_SERVER['REMOTE_ADDR'];
        $gallery->album->addComment($id, $comment_text, $IPNumber, $commenter_name);
        $gallery->album->save();
        emailComments($id, $comment_text, $commenter_name);
    }
}

$metaTags = array();
$keyWords = $gallery->album->getKeywords($index);
if (!empty($keyWords)) {
    $metaTags['Keywords'] = ereg_replace("[[:space:]]+",' ',$keyWords);
}

if (!$GALLERY_EMBEDDED_INSIDE) {
	doctype(); ?>
<html> 
<head>
  <title><?php echo $gallery->app->galleryTitle . ' :: '. $gallery->album->fields["title"] . ' :: '. $title ; ?></title>
  <?php 	
  common_header(array('metaTags' => $metaTags));

  /* prefetch/navigation */
  $navcount = sizeof($navigator['allIds']);
  $navpage = $navcount - 1;
  while ($navpage > 0) {
      if (!strcmp($navigator['allIds'][$navpage], $id)) {
          break;
      }
      $navpage--;
  }
  if ($navigator['allIds'][0] != $id) {
      if ($navigator['allIds'][0] != 'unknown') {
          echo "\n  ". '<link rel="first" href="'. makeAlbumUrl($gallery->session->albumName, $navigator['allIds'][0]) .'">';
      }

      if ($navigator['allIds'][$navpage-1] != 'unknown') {
          echo "\n  ". '<link rel="prev" href="'. makeAlbumUrl($gallery->session->albumName, $navigator['allIds'][$navpage-1]) .'">';
      }
  }
  if ($navigator['allIds'][$navcount - 1] != $id) {
      if ($navigator['allIds'][$navpage+1] != 'unknown') {
          echo "\n  ". '<link rel="next" href="'. makeAlbumUrl($gallery->session->albumName, $navigator['allIds'][$navpage+1]) .'">';
      }
      if ($navigator['allIds'][$navcount-1] != 'unknown') {
          echo "\n  ". '<link rel="last" href="'. makeAlbumUrl($gallery->session->albumName, $navigator['allIds'][$navcount - 1]) .'">';
      }
  }

  echo "\n  ". '<link rel="up" href="' . makeAlbumUrl($gallery->session->albumName) .'">';
  if ($gallery->album->isRoot() &&
  (!$gallery->session->offline || isset($gallery->session->offlineAlbums["albums.php"]))) {
      echo "\n  ". '<link rel="top" href="'. makeGalleryUrl('albums.php', array('set_albumListPage' => 1)) .'">';
  }
?>

  <style type="text/css">
<?php
// the link colors have to be done here to override the style sheet
if (!empty($gallery->album->fields["linkcolor"])) {
    ?>
    A:link, A:visited, A:active
      { color: <?php echo $gallery->album->fields['linkcolor'] ?>; }
    A:hover
      { color: #ff6600; }
<?php 
}
if (!empty($gallery->album->fields["bgcolor"])) {
    echo "BODY { background-color:" . $gallery->album->fields['bgcolor'] . "; }";
}
if (!empty($gallery->album->fields["background"])) {
    echo "BODY { background-image:url(" . $gallery->album->fields['background'] . "); } ";
}
if (!empty($gallery->album->fields["textcolor"])) {
    echo "BODY, TD, P, DIV, SPAN { color:" . $gallery->album->fields['textcolor'] . "; }\n";
    echo ".head { color:" . $gallery->album->fields['textcolor'] . "; }\n";
    if (!empty($gallery->album->fields["bgcolor"])) {
        echo ".headbox { background-color:" . $gallery->album->fields['bgcolor'] . "; }\n";
    }
}
?>
  </style> 

  </head>
  <body dir="<?php echo $gallery->direction ?>">
<?php
} // End if ! embedded

includeHtmlWrap("photo.header");

$useIcons = (!$iconsForItemOptions || $gallery->app->useIcons == 'no') ? false : true;
$albumItemOptions = getItemActions($index, $useIcons);

if ($fitToWindow) {
    /* Include Javascript */
    include(dirname(__FILE__) .'/js/fitToWindow.js.php');
}
?>
<!-- Top Nav Bar -->
<div class="topNavbar" style="width:<?php echo $mainWidth ?>">
<?php

$page_url = makeAlbumUrl($gallery->session->albumName, $id, array("full" => 0));
$iconElements = array();
$adminTextIconElemens = array();

if (!$gallery->album->isMovie($id)) {
    print "<a id=\"photo_url\" href=\"$photoURL\" ></a>\n";
    print '<a id="page_url" href="'. $page_url .'"></a>'."\n";

    if ($gallery->album->fields["use_exif"] == "yes" &&
      (eregi("jpe?g\$", $photo->image->type)) &&
      (isset($gallery->app->use_exif) || isset($gallery->app->exiftags)) &&
      sizeof($albumItemOptions) == 2) {
        $albumName = $gallery->session->albumName;
        $iconText = getIconText('frame_query.gif', gTranslate('core', "photo properties"));
        $iconElements[] =  popup_link($iconText, "view_photo_properties.php?set_albumName=$albumName&index=$index", 0, false, 500, 500);
    }

    if (!empty($gallery->album->fields["print_photos"]) &&
      !$gallery->session->offline &&
      !$gallery->album->isMovie($id)){

        $photo = $gallery->album->getPhoto($GLOBALS["index"]);
        $photoPath = $gallery->album->getAlbumDirURL("full");
        $prependURL = '';
        if (!ereg('^https?://', $photoPath)) {
            $prependURL = 'http';
            if (isset($_SERVER['HTTPS']) && stristr($_SERVER['HTTPS'], "on")) {
                $prependURL .= 's';
            }

            $prependURL .= '://'. $_SERVER['HTTP_HOST'];
        }

        $rawImage = $prependURL . $photoPath . "/" . $photo->image->name . "." . $photo->image->type;

        $thumbImage= $prependURL . $photoPath . "/";
        if ($photo->thumbnail) {
            $thumbImage .= $photo->image->name . "." . "thumb" . "." . $photo->image->type;
        } else if ($photo->image->resizedName) {
            $thumbImage .= $photo->image->name . "." . "sized" . "." . $photo->image->type;
        } else {
            $thumbImage .= $photo->image->name . "." . $photo->image->type;
        }

        list($imageWidth, $imageHeight) = $photo->image->getRawDimensions();

        /**
         * Now build the admin Texts / left colun
         */
        function enablePrintForm($name) {
            global $printPhotoAccessForm, $printShutterflyForm, $printFotoserveForm;

            switch ($name) {
                case 'shutterfly':
                    $printShutterflyForm = true;
                    break;
                case 'fotoserve':
                    $printFotoserveForm = true;
                    break;
                case 'photoaccess':
                    $printPhotoAccessForm = true;
                    break;
                default:
                break;
            }
        }

        /* display photo printing services */
        $printServices = $gallery->album->fields['print_photos'];
        $numServices = count($printServices);

        $fullNames = array(
            'Print Services' => array(
                'fotokasten'  => 'Fotokasten',
                'fotoserve'   => 'Fotoserve',
                'shutterfly'  => 'Shutterfly',
                'photoaccess' => 'PhotoWorks',
            ),
        '   Mobile Service' => array('mpush' => 'mPUSH (mobile service)')
        );

        /* display a <select> menu if more than one option */
        if ($numServices > 1) {
            // Build an array with groups, but only for enabled services
            foreach ($fullNames as $serviceGroupName => $serviceGroup) {
                foreach ($serviceGroup as $name => $fullName) {
                    if (!in_array($name, $printServices)) {
                        continue;
                    } else {
                        $serviceGroups[$serviceGroupName][$name] = $fullName;
                    }
                }
            }

            $options = array();

            if (isset($serviceGroups['Mobile Service'])) {
                $options[] = gTranslate('core', "Send photo to...") ;
            } else {
                $options[] = gTranslate('core', "Print photo with...");
            }

            $firstGroup = true;
            // now build the real select options.
            foreach ($serviceGroups as $serviceGroupName => $serviceGroup) {
                if (! $firstGroup) {
                    $options[]= '----------';
                }
                $firstGroup = false;
                foreach ($serviceGroup as $name => $fullName) {
                    enablePrintForm($name);
                    $options[$name] = '&nbsp;&nbsp;&nbsp;'. $fullName;
                }
            }

            $printServicesText = makeFormIntro('view_photo.php', array("name" => "print_form"));
            $printServicesText .= drawSelect('print_services',
                $options,
                '',
                1,
                array('onChange' =>'doPrintService()', 'class' => 'adminform'),
                true
            );
            $printServicesText .= '</form>';
            $adminTextIconElemens[] = $printServicesText;

            /* just print out text if only one option */
        } elseif ($numServices == 1) {

            foreach ($fullNames as $serviceGroupName => $serviceGroup) {
                foreach ($serviceGroup as $name => $fullName) {
                    if (!in_array($name, $printServices)) {
                        continue;
                    } else {
			enablePrintForm($name);
                        $iconText = getIconText('', sprintf(gTranslate('core', "process this photo with %s"), $fullName));
                        $adminTextIconElemens[] = "<a class=\"admin\" href=\"#\" onClick=\"doPrintService('$name');\">$iconText</a>";
                    }
                }
            }
        }

?>
<script language="javascript1.2" type="text/JavaScript">

function doPrintService(input) {
    if (!input) {
        input = document.print_form.print_services.value;
    }
    switch (input) {
        case 'fotokasten':
            window.open('<?php echo "http://1071.partner.fotokasten.de/affiliateapi/standard.php?add=" . $rawImage . '&thumbnail=' . $thumbImage . '&height=' . $imageHeight . '&width=' . $imageWidth; ?>','Print_with_Fotokasten','<?php echo "height=500,width=500,location=no,scrollbars=yes,menubars=no,toolbars=no,resizable=yes"; ?>');
            break;
        case 'photoaccess':
            document.photoAccess.returnUrl.value=document.location;
            document.photoAccess.submit();
            break;
        case 'shutterfly':
            document.sflyc4p.returl.value=document.location;
            document.sflyc4p.submit();
            break;
        case 'mpush':
            window.open('http://mpush.msolutions.cc/req.php?account=<?php echo $gallery->app->default['mPUSHAccount'] ?>&image=<?php echo $rawImage ?>&caption=<?php echo urlencode($gallery->album->getCaption($index)) ?>','_MPUSH','width=640,height=420,titlebar=1,resizable=1,scrollbars=1');
            break;
        case 'fotoserve':
            document.fotoserve.redirect.value=document.location;
            document.fotoserve.submit();
        break;
    }
}
</script>
<?php
    }
    if (!strcmp($gallery->album->fields["use_fullOnly"], "yes") &&
      !$gallery->session->offline  &&
      $gallery->user->canViewFullImages($gallery->album)) {
        $lparams['set_fullOnly'] = (!isset($gallery->session->fullOnly) || strcmp($gallery->session->fullOnly,"on")) ? "on" : "off";
        $link = makeAlbumURL($gallery->session->albumName, $id, $lparams);
        $adminTextIconElemens[] = _('View Images:');
        $iconTextNormal = gTranslate('core', "normal");
        $iconTextFull = gTranslate('core', "full");

        if (!isset($gallery->session->fullOnly) || strcmp($gallery->session->fullOnly,"on")) {
            $adminTextIconElemens[] = $iconTextNormal;
            $adminTextIconElemens[] = '|';
            $adminTextIconElemens[] = "<a class=\"admin\" href=\"$link\">[". $iconTextFull .']</a>';
        } else {
            $adminTextIconElemens[] = "<a class=\"admin\" href=\"$link\">[" .$iconTextNormal .']</a>';
            $adminTextIconElemens[] = '|';
            $adminTextIconElemens[] = $iconTextFull;
        }
    }

    /* If eCards are enabled, show the link.
    ** The eCard opens in a popup and sends the actual displayed photo.
    */
    if(isset($gallery->album->fields["ecards"]) && $gallery->album->fields["ecards"] == 'yes' &&
      $gallery->app->emailOn == 'yes') {
        $iconText = getIconText('ecard.gif', gTranslate('core', "Send Photo as eCard"));
        $adminTextIconElemens[] = popup_link($iconText,
            makeGalleryUrl('ecard_form.php', array('photoIndex' => $index,'gallery_popup' => 'true' )), 1, true, 550, 600);
    }
}

if(sizeof($albumItemOptions) > 2 && !$useIcons) {
    $iconElements[] =  drawSelect2(
    	'itemOptions',
    	$albumItemOptions,
    	array(
        	'onChange' => "imageEditChoice(document.admin_options_form.itemOptions)",
        	'class' => 'adminform'
        )
    );
}

if (!$GALLERY_EMBEDDED_INSIDE && !$gallery->session->offline) {
    if ($gallery->user->isLoggedIn()) {
        $iconText = getIconText('exit.gif', gTranslate('core', "logout"));
        $iconElements[] = '<a href="'.
          doCommand("logout", array(), "view_album.php", array("page" => $page)) .
          '">'. $iconText .'</a>';
    } else {
        $iconText = getIconText('identity.gif', gTranslate('core', "login"));
        $iconElements[] = popup_link($iconText, "login.php", false);
    }
}
includeLayout('navtablebegin.inc');

$adminbox["text"] = makeIconMenu($adminTextIconElemens, 'left');
$adminbox["commands"] = makeIconMenu($iconElements, 'right');
$adminbox["bordercolor"] = $bordercolor;
includeLayout('adminbox.inc');
includeLayout('navtablemiddle.inc');

$breadcrumb["bordercolor"] = $bordercolor;
includeLayout('breadcrumb.inc');

/* Show itemOptions only if we have more then one (photo properties) */
if(sizeof($albumItemOptions) > 2 && $useIcons) {
    includeLayout('navtablemiddle.inc');
    $albumItemOptionElements = array();
    $itemActionTable = new galleryTable();
    $itemActionTable->setColumnCount(10);
    $itemActionTable->setAttrs(array('align' => langLeft()));
    foreach ($albumItemOptions as $trash => $option) {
        if(!empty($option['value'])) {
            if (stristr($option['value'], 'popup')) {
                $content = popup_link($option['text'], $option['value'], true, false, 500, 500, 'iconLink');
            } else {
                $content = '<a class="iconLink" href="'. $option['value'] .'">'. $option['text'] . '</a>';
            }
            $itemActionTable->addElement(array('content' => $content));
        }
    }
    echo $itemActionTable->render();
}

includeLayout('navtablemiddle.inc');

if ($gallery->album->fields['nav_thumbs'] != 'no' &&
  $gallery->album->fields['nav_thumbs_location'] != 'bottom') {
    includeLayout('navmicro.inc');
    includeLayout('navtablemiddle.inc');
}

if ( $gallery->album->fields['nav_thumbs'] != 'yes') {
    includeLayout('navphoto.inc');
}

includeLayout('navtableend.inc');

#-- if borders are off, just make them the bgcolor ----
if ($gallery->album->fields["border"] == 0) {
    $bordercolor = $gallery->album->fields["bgcolor"];
}
if ($bordercolor) {
    $bordercolor = "bgcolor=$bordercolor";
}
?>
<!-- End Top Nav Bar -->
</div>


<div style="width:<?php echo $mainWidth ?>"> 
<?php includeHtmlWrap("inline_photo.header"); ?>
</div>

<!-- image -->
<a name="image"></a>

<?php

$href = '';
if (!$gallery->album->isMovie($id)) {
    if(!$do_fullOnly  && ($full || $fitToWindow || $gallery->album->isResized($index))) {
        switch(true) {
            case $fitToWindow:
                $href = '';
                break;
            case $full:
                $href = makeAlbumUrl($gallery->session->albumName, $id);
                break;
            case $gallery->user->canViewFullImages($gallery->album):
                $href = makeAlbumUrl($gallery->session->albumName, $id, array("full" => 1));
                break;
        }
    }
} else {
    $href = $gallery->album->getPhotoPath($index) ;
}

$frame = $gallery->album->fields['image_frame'];
if ($fitToWindow && (eregi('safari|opera', $_SERVER['HTTP_USER_AGENT']) || $gallery->session->offline)) {
    //Safari/Opera can't render dynamically sized image frame
    $frame = 'none';
}

if(empty($full) && $allImageAreas = $gallery->album->getAllImageAreas($index)) {
    echo showImageMap($index);
    $photoTag = $gallery->album->getPhotoTag($index, $full,"id=\"galleryImage\" usemap=\"#myMap\"");
}
else {
    $photoTag = $gallery->album->getPhotoTag($index, $full,"id=\"galleryImage\"");
}

if($gallery->album->isMovie($id)) {
    list($width, $height) = $photo->getThumbDimensions();
}
else {
    list($width, $height) = $photo->getDimensions($full);
}
$gallery->html_wrap['borderColor'] = $gallery->album->fields["bordercolor"];
$gallery->html_wrap['borderWidth'] = $gallery->album->fields["border"];
$gallery->html_wrap['frame'] = $frame;
$gallery->html_wrap['imageWidth'] = $width;
$gallery->html_wrap['imageHeight'] = $height;
$gallery->html_wrap['imageHref'] = $href;
$gallery->html_wrap['imageTag'] = $photoTag;
if ($fitToWindow && $gallery->user->canViewFullImages($gallery->album)) {
    $gallery->html_wrap['attr'] = 'onclick="sizeChange.toggle()"';
}
$gallery->html_wrap['pixelImage'] = getImagePath('pixel_trans.gif');

includeHtmlWrap("inline_photo.frame");
?>
<div class="pview" align="center">
<!-- caption -->
<p align="center" class="pcaption"><?php echo editCaption($gallery->album, $index) ?></p>

<!-- Custom Fields -->
<?php
displayPhotoFields($index, $extra_fields, true, in_array('EXIF', $extra_fields), $full);
?>

<!-- voting -->
<?php

/*
** Block for Voting
*/

if ( canVote()) {
    echo "\n<!-- Voting pulldown -->\n";
    echo makeFormIntro('view_photo.php', array('name' => 'vote_form'));
?>
	<script language="javascript1.2" type="text/JavaScript">
	function chooseOnlyOne(i, form_pos, scale) {
	    for(var j=0;j<scale;j++) {
	        if(j != i) {
	            eval("document.vote_form['votes["+j+"]'].checked=false");
	        }
	    }
	    document.vote_form.submit("Vote");
	}
	</script>
	<?php
	echo '<input type="hidden" name="id" value="'. $id .'">';
	echo addPolling("item.$id");
	?>
	</form>
<?php
}

if ($gallery->album->getPollShowResults()) {
    echo "\n<!-- Voting Results -->";
    echo "\n". '<p align="center">';
    echo showResults("item.$id");
    echo "\n</p>";
}

echo "\n<!-- Comments -->";
if (isset($error_text)) {
    echo gallery_error($error_text) ."<br><br>";
}

if ($gallery->user->canViewComments($gallery->album) && $gallery->app->comments_enabled == 'yes') {
    echo viewComments($index, $gallery->user->canAddComments($gallery->album), $page_url);
}

echo "<br>";

includeHtmlWrap("inline_photo.footer");

if ($gallery->user->isLoggedIn() &&  
  $gallery->user->getEmail() &&
  !$gallery->session->offline &&
  $gallery->app->emailOn == "yes") {
    $emailMeComments = getRequestVar('emailMeComments');
    if (!empty($emailMeComments)) {
        if ($emailMeComments == 'true') {
            $gallery->album->setEmailMe('comments', $gallery->user, $id);
        } else {
            $gallery->album->unsetEmailMe('comments', $gallery->user, $id);
        }
    }

    if (! $gallery->album->getEmailMe('comments', $gallery->user)) {
        echo "\n<form name=\"emailMe\" action=\"#\">";

        $url= makeAlbumUrl($gallery->session->albumName, $id, array(
        'emailMeComments' => ($gallery->album->getEmailMe('comments', $gallery->user, $id)) ? 'false' : 'true')
        );

        echo gTranslate('core', "Email me when comments are added");
?>
	<input type="checkbox" name="comments" <?php echo ($gallery->album->getEmailMe('comments', $gallery->user, $id)) ? "checked" : "" ?> onclick="location.href='<?php echo $url; ?>'" >
	</form>
<?php
    }
}
echo "</div>";
includeLayout('navtablebegin.inc');

if ($gallery->album->fields['nav_thumbs'] != 'no' &&
  $gallery->album->fields['nav_thumbs_location'] != 'top') {
    includeLayout('navmicro.inc');
    includeLayout('navtablemiddle.inc');
}

if ( $gallery->album->fields['nav_thumbs'] != 'yes') {
    includeLayout('navphoto.inc');
    includeLayout('navtablemiddle.inc');
}

includeLayout('breadcrumb.inc');
includeLayout('navtableend.inc');
echo languageSelector();

if (isset($printShutterflyForm)) { ?>
<form name="sflyc4p" action="http://www.shutterfly.com/c4p/UpdateCart.jsp" method="post">
  <input type=hidden name=addim value="1">
  <input type=hidden name=protocol value="SFP,100">
  <input type=hidden name=pid value="C4PP">
  <input type=hidden name=psid value="GALL">
  <input type=hidden name=referid value="gallery">
  <input type=hidden name=returl value="this-gets-set-by-javascript-in-onClick">
  <input type=hidden name=imraw-1 value="<?php echo $rawImage ?>">
  <input type=hidden name=imrawheight-1 value="<?php echo $imageHeight ?>">
  <input type=hidden name=imrawwidth-1 value="<?php echo $imageWidth ?>">
  <input type=hidden name=imthumb-1 value="<?php echo $thumbImage ?>">
  <?php
  /* Print the caption on back of photo. If no caption,
  * then print the URL to this page. Shutterfly cuts
  * the message off at 80 characters. */
  $imbkprnt = $gallery->album->getCaption($index);
  if (empty($imbkprnt)) {
      $imbkprnt = makeAlbumUrl($gallery->session->albumName, $id);
  }
  ?>
  <input type=hidden name=imbkprnta-1 value="<?php echo htmlentities(strip_tags($imbkprnt)) ?>">
</form>
<?php }
if (isset($printFotoserveForm)) { ?>
<form name="fotoserve" 
action="http://www.fotoserve.com/menalto/build.html" method="post">
  <input type="hidden" name="image" value="<?php echo $rawImage ?>">
  <input type="hidden" name="thumb" value="<?php echo $thumbImage ?>">
  <input type="hidden" name="redirect" value="this-gets-set-by-javascript-in-onClick">
  <input type="hidden" name="name" value="<?php echo $photo->image->name . '.' . $photo->image->type; ?>">
</form>
<?php }
if (isset($printPhotoAccessForm)) { ?>
  <form method="post" name="photoAccess" action="http://www.tkqlhce.com/click-1660787-10381744">
  <input type="hidden" name="cb" value="CB_GP">
  <input type="hidden" name="redir" value="true">
  <input type="hidden" name="returnUrl" value="this-gets-set-by-javascript-in-onClick">
  <input type="hidden" name="imageId" value="<?php echo $photo->image->name . '.' . $photo->image->type; ?>">
  <input type="hidden" name="imageUrl" value="<?php echo $rawImage ?>">
  <input type="hidden" name="thumbUrl" value="<?php echo $thumbImage ?>">
  <input type="hidden" name="imgWidth" value="<?php echo $imageWidth ?>">
  <input type="hidden" name="imgHeight" value="<?php echo $imageHeight ?>">
</form>
<?php }

    includeHtmlWrap("photo.footer");
    if (!empty($allImageAreas)) {
        echo '<script language="JavaScript" type="text/javascript" src="'. $gallery->app->photoAlbumURL .'/js/wz_tooltip.js"></script>';
    }
    if ($fitToWindow) {
?>
<script type="text/javascript">
<!--
calculateNewSize();
//-->
</script>
<?php
    }
    if (!$GALLERY_EMBEDDED_INSIDE) { ?>
</body>
</html>
<?php
}
?>