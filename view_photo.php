<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2004 Bharat Mediratta
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
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 * $Id$
 */
?>
<?php

require_once(dirname(__FILE__) . '/init.php');

list($full, $id, $index, $votes) = getRequestVar(array('full', 'id', 'index', 'votes'));
list($save, $commenter_name, $comment_text) = getRequestVar(array('save', 'commenter_name', 'comment_text'));

// Hack check and prevent errors
if (empty($gallery->session->albumName) || !$gallery->user->canReadAlbum($gallery->album) || !$gallery->album->isLoaded()) {
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
	$id = $gallery->album->getPhotoId($index);
}

// Determine if user has the rights to view full-sized images
if (!empty($full) && !$gallery->user->canViewFullImages($gallery->album)) {
	header("Location: " . makeAlbumHeaderUrl($gallery->session->albumName, $id));
	return;
} elseif (!$gallery->album->isResized($index) && !$gallery->user->canViewFullImages($gallery->album)) {
	header("Location: " . makeAlbumHeaderUrl($gallery->session->albumName));
	return;
}


if (!isset($full)) {
	$full=NULL;
}


if (!empty($votes)) {
    if (!isset($votes[$id]) && 
	  $gallery->album->getPollScale() == 1 && 
	  $gallery->album->getPollType() == "critique") {
               $votes[$id]=null;
    }
       
    saveResults($votes);
    if ($gallery->album->getPollShowResults()) {
	list($buf, $rank)=showResultsGraph(0);
	print $buf;
    }
}

// is photo hidden?  should user see it anyway?
if (($gallery->album->isHidden($index))
    && (!$gallery->user->canWriteToAlbum($gallery->album))){
    header("Location: " . makeAlbumHeaderUrl($gallery->session->albumName));
    return;
}

$albumName = $gallery->session->albumName;
if (!isset($gallery->session->viewedItem[$gallery->session->albumName][$id]) 
	&& !$gallery->session->offline) {
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
               !strcmp($gallery->album->fields["use_fullOnly"],"yes");
if ($do_fullOnly) {
	$full = $gallery->user->canViewFullImages($gallery->album);
}
    
$fitToWindow = !strcmp($gallery->album->fields["fit_to_window"], "yes") 
		&& !$gallery->album->isResized($index) 
		&& !$full 
		&& (!$GALLERY_EMBEDDED_INSIDE || $GALLERY_EMBEDDED_INSIDE =='phpBB2');

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

if ($index > $gallery->album->numPhotos(1)) {
	$index = $numPhotos;
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
$borderwidth = $gallery->album->fields["border"];
if ($borderwidth == 0) {
	$borderwidth = 1;
}

$mainWidth = "100%"; 

$navigator["id"] = $id;
$navigator["allIds"] = $gallery->album->getIds($gallery->user->canWriteToAlbum($gallery->album));
$navigator["fullWidth"] = "100";
$navigator["widthUnits"] = "%";
$navigator["url"] = ".";
$navigator["bordercolor"] = $bordercolor;

#-- breadcrumb text ---
$upArrowURL = '<img src="' . getImagePath('nav_home.gif') . '" width="13" height="11" '.
		'alt="' . _("navigate UP") .'" title="' . _("navigate UP") .'" border="0">';

if ($gallery->album->fields['returnto'] != 'no') {
	$breadcrumb["text"][]= _("Gallery") .": <a class=\"bread\" href=\"" . makeGalleryUrl("albums.php") . "\">" .
		$gallery->app->galleryTitle . "&nbsp;" . $upArrowURL . "</a>";
	foreach ($gallery->album->getParentAlbums(true) as $name => $title) {
		$breadcrumb["text"][] = _("Album") .": <a class=\"bread\" href=\"" . makeAlbumUrl($name) . "\">" .
			$title. "&nbsp;" . $upArrowURL . "</a>";
        }
}

$extra_fields=$gallery->album->getExtraFields(false);
$title=NULL;
if (in_array("Title", $extra_fields)) {
	$title=$gallery->album->getExtraField($index, "Title");
}
if (!$title) {
	$title=$photo->image->name;
}

if (isset($gallery->app->comments_length)) {
	$maxlength=$gallery->app->comments_length;
} else {
	$maxlength=0;
}

if (!empty($save)) {
	if ( empty($commenter_name) || empty($comment_text)) {
		$error_text = _("Name and comment are both required to save a new comment!");
	} elseif ($maxlength >0 && strlen($comment_text) > $maxlength) {
		$error_text = sprintf(_("Your comment is too long, the admin set maximum length to %d chars"), $maxlength);
	} elseif (isBlacklistedComment($tmp = array('commenter_name' => $commenter_name, 'comment_text' => $comment_text), false)) {
		$error_text = _("Your Comment contains forbidden words. It will not be added.");
	} else {
		$comment_text = removeTags($comment_text);
		$commenter_name = removeTags($commenter_name);
		$IPNumber = $_SERVER['REMOTE_ADDR'];
		$gallery->album->addComment($id, stripslashes($comment_text), $IPNumber, $commenter_name);
		$gallery->album->save();
		emailComments($id, $comment_text, $commenter_name);
	}
}

if (!$GALLERY_EMBEDDED_INSIDE) {
	doctype(); ?>
<html> 
<head>
  <title><?php echo $gallery->app->galleryTitle . ' :: '. $gallery->album->fields["title"] . ' :: '. $title ; ?></title>
  <?php 	
	common_header();
	
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
      		if ($navigator['allIds'][0] != 'unknown') { ?>
   <link rel="first" href="<?php echo makeAlbumUrl($gallery->session->albumName, $navigator['allIds'][0]) ?>" >
<?php		}
      		if ($navigator['allIds'][$navpage-1] != 'unknown') { ?>
   <link rel="prev" href="<?php echo makeAlbumUrl($gallery->session->albumName, $navigator['allIds'][$navpage-1]) ?>" >
<?php 		}
  	}
  	if ($navigator['allIds'][$navcount - 1] != $id) {
      		if ($navigator['allIds'][$navpage+1] != 'unknown') { ?>
  <link rel="next" href="<?php echo makeAlbumUrl($gallery->session->albumName, $navigator['allIds'][$navpage+1]) ?>" >
 <?php 		}
      		if ($navigator['allIds'][$navcount-1] != 'unknown') { ?>
  <link rel="last" href="<?php echo makeAlbumUrl($gallery->session->albumName, $navigator['allIds'][$navcount - 1]) ?>" >
<?php 		}
  	} ?>
  <link rel="up" href="<?php echo makeAlbumUrl($gallery->session->albumName) ?>">
<?php 	if ($gallery->album->isRoot() && 
		(!$gallery->session->offline || isset($gallery->session->offlineAlbums["albums.php"]))) { ?>
  <link rel="top" href="<?php echo makeGalleryUrl('albums.php', array('set_albumListPage' => 1)) ?>">	 
<?php 	}
	$keyWords=$gallery->album->getKeywords($index);
	if (!empty($keyWords)) {
		$metakeywords = ereg_replace("[[:space:]]+",' ',$keyWords); 
		echo "  <meta name=\"Keywords\" content=\"$metakeywords\">\n";
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
  <body dir="<?php echo $gallery->direction ?>"<?php echo ($fitToWindow) ? ' onResize="calculateNewSize()"' : '' ?>>
<?php
} // End if ! embedded

includeHtmlWrap("photo.header");

if ($fitToWindow) {
	/* Include Javascript */
	include(dirname(__FILE__) .'/js/fitToWindow.js.php');
}
?>
<!-- Top Nav Bar -->
<form name="admin_form" action="view_photos.php">
<table border="0" width="<?php echo $mainWidth ?>" cellpadding="0" cellspacing="0">

<tr>
<td>
<?php

$adminText = '';
$page_url = makeAlbumUrl($gallery->session->albumName, $id, array("full" => 0));

if (!$gallery->album->isMovie($id)) {
    print "<a id=\"photo_url\" href=\"$photoURL\" ></a>\n";
    print '<a id="page_url" href="'. $page_url .'"></a>'."\n";
    if ($gallery->user->canWriteToAlbum($gallery->album)) {
	$iconText = getIconText('window_fullscreen.gif', _("resize photo"));
	$iconElements[] = popup_link($iconText, "resize_photo.php?index=$index", false, true, 500, 500);
    }

    if ($gallery->user->canDeleteFromAlbum($gallery->album) || 
	($gallery->album->getItemOwnerDelete() && $gallery->album->isItemOwner($gallery->user->getUid(), $index))) {
	// determine index of next item (after deletion)
	// we move to previous image if we're at the end
	// and move forward if we're not
	if ($index >= $numPhotos && $index > 1) {
	    $nextIndex = $index - 1;
	}
	elseif ($index + 1 <= $numPhotos) {
	    $nextIndex = $index + 1;
	}
	else {
	    $nextIndex = $index;
	}
	// make sure that the "next" item isn't an album
	if ($gallery->album->isAlbum($nextIndex)) {
	    $nextId='';
	} else {
	    $nextId = $gallery->album->getPhotoId($nextIndex);
	}

	$iconText = getIconText('delete.gif', _("delete photo"));
	$iconElements[] = popup_link($iconText, "delete_photo.php?id=$id&nextId=$nextId", false, true, 500, 500);
    }

    if (!strcmp($gallery->album->fields["use_fullOnly"], "yes") &&
	!$gallery->session->offline  &&
	 $gallery->user->canViewFullImages($gallery->album)) {

	$lparams['set_fullOnly'] = (!isset($gallery->session->fullOnly) || strcmp($gallery->session->fullOnly,"on")) ? "on" : "off";
	$link = makeAlbumURL($gallery->session->albumName, $id, $lparams);

	$adminText = '&nbsp;' . _('View Images') .':&nbsp;[&nbsp;';
	if (!isset($gallery->session->fullOnly) || strcmp($gallery->session->fullOnly,"on")) {
	    $adminText .= _('normal') . "&nbsp;|&nbsp;<a class=\"admin\" href=\"$link\">" . _('full') .'</a>&nbsp;]';
	} else {
	    $adminText .= "<a class=\"admin\" href=\"$link\">" . _("normal") .'</a>&nbsp;|&nbsp;'. _('full') .'&nbsp;]';
	}
    } 
	
    if ($gallery->album->fields["use_exif"] == "yes" &&
	(eregi("jpe?g\$", $photo->image->type)) &&
	(isset($gallery->app->use_exif) || isset($gallery->app->exiftags))) {
	
	$albumName = $gallery->session->albumName;
	$iconText = getIconText('frame_query.gif', _("photo properties"));
	$iconElements[] =  popup_link($iconText, "view_photo_properties.php?set_albumName=$albumName&index=$index", 0, false, 500, 500);
    }

    if (isset($gallery->album->fields["print_photos"]) &&
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
		
    function enablePrintForm($name) {
	global $printEZPrintsForm, $printPhotoAccessForm, $printShutterflyForm, $printFotoserveForm;
	
	switch ($name) {
	    case 'ezprints':
		$printEZPrintsForm = true;
	    break;
                                
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
	    'ezprints'    => 'EZ Prints',
	    'fotokasten'  => 'Fotokasten',
	    'fotoserve'   => 'Fotoserve',
	    'shutterfly'  => 'Shutterfly',
	    'photoaccess' => 'PhotoWorks',
	),
	'Mobile Service' => array('mpush' => 'mPUSH (mobile service)')
    );

    /* display a <select> menu if more than one option */
    if ($numServices > 1) {
	// Build an array with groups, but only for enabled services
	foreach ($fullNames as $serviceGroupName => $serviceGroup) {
	    foreach ($serviceGroup as $name => $fullName) {
	        if (!isset($printServices[$name]['checked'])) {
		    continue;
	        } else {
		    $serviceGroups[$serviceGroupName][$name] = $fullName;
		}
	    }
	}

	if (isset($serviceGroups['Mobile Service'])) {
	    $instructionLine = "\n\t". '<option>'. _("Send photo to...") .'</option>';
	} else {
	    $instructionLine = "\n\t". '<option>'. _("Print photo with...") .'</option>';	
	}

	$selectCommand = "\n". '<select name="print_services" class="adminform" onChange="doPrintService()">';
	$selectCommand .= $instructionLine;

	$firstGroup = true;
	// now build the real select options.
	foreach ($serviceGroups as $serviceGroupName => $serviceGroup) {
	    if (! $firstGroup) {
		    $selectCommand .= '<option value="">----------</option>';
		}
	    $firstGroup = false;
	    foreach ($serviceGroup as $name => $fullName) {
		enablePrintForm($name);
		$selectCommand .= "\n\t". '<option align="center" value="'. $name .'">&nbsp;&nbsp;&nbsp;'. $fullName .'</option>';
	    }
	}
	$selectCommand .= '</select>';
	$adminText .= $selectCommand;
	
	/* just print out text if only one option */
    } elseif ($numServices == 1 && isset($printServices[@key($printServices)]['checked'])) {
	$name = @key($printServices);

	enablePrintForm($name);
	foreach ($fullNames as $serviceGroupName => $serviceGroup) {
	    foreach ($serviceGroup as $name => $fullName) {
	        if (!isset($printServices[$name]['checked'])) {
		    continue;
	        } else {
		    $adminText .= "<a class=\"admin\" href=\"#\" onClick=\"doPrintService('$name');\">[" .
				    sprintf(_('process this photo with %s'), $fullName) . ']</a>';
		}
	    }
	}
		
    }
?>
<script language="javascript1.2" type="text/JavaScript">
	 function doPrintService(input) {
		if (!input) {
		    input = document.admin_form.print_services.value;
		}
		switch (input) {
		case 'ezprints':
			document.ezPrintsForm.returnpage.value=document.location;
			document.ezPrintsForm.submit();
			break;
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
}

if (!$GALLERY_EMBEDDED_INSIDE && !$gallery->session->offline) {
	if ($gallery->user->isLoggedIn()) {
		$iconText = getIconText('exit.gif', _("logout"));
		$iconElements[] = '<a href="'.
				doCommand("logout", array(), "view_album.php", array("page" => $page)) .
					'">'. $iconText .'</a>';
	} else {
		$iconText = getIconText('identity.gif', _("login"));
		$iconElements[] = popup_link($iconText, "login.php", false, true, 500, 500);
        }
}
includeLayout('navtablebegin.inc');

$adminbox["text"] = $adminText;	
$adminbox["commands"] = makeIconMenu($iconElements);;
$adminbox["bordercolor"] = $bordercolor;
includeLayout('adminbox.inc');
includeLayout('navtablemiddle.inc');

$breadcrumb["bordercolor"] = $bordercolor;
$breadcrumb["top"] = true;
$breadcrumb['bottom'] = false;
includeLayout('breadcrumb.inc');
includeLayout('navtablemiddle.inc');
includeLayout('navphoto.inc');
includeLayout('navtableend.inc');

#-- if borders are off, just make them the bgcolor ----
if ($gallery->album->fields["border"] == 0) {
	$bordercolor = $gallery->album->fields["bgcolor"];
}
if ($bordercolor) {
	$bordercolor = "bgcolor=$bordercolor";
}
?>
<br>
</td>
</tr>
</table>
</form>

<div width="<?php echo $mainWidth ?>">
<?php includeHtmlWrap("inline_photo.header"); ?>
</div>

<!-- image -->
<a name="image"></a>

<?php

$href="";
if (!$gallery->album->isMovie($id)) {
	if (!$do_fullOnly && ($full || $fitToWindow || $gallery->album->isResized($index))) {
		if ($fitToWindow) {
			$href="";
		}
		else if ($full) { 
			$href= makeAlbumUrl($gallery->session->albumName, $id);
	 	} else if ($gallery->user->canViewFullImages($gallery->album)) {
			$href= makeAlbumUrl($gallery->session->albumName, $id, array("full" => 1));
		}
	}
} else {
	$href= $gallery->album->getPhotoPath($index) ;
}

$photoTag="";
$frame= $gallery->album->fields['image_frame'];
if ($fitToWindow && (eregi('safari|opera', $_SERVER['HTTP_USER_AGENT']) || $gallery->session->offline)) {
	//Safari/Opera can't render dynamically sized image frame
	$frame = 'none';
}
$photoTag .= $gallery->album->getPhotoTag($index, $full);

list($width, $height) = $photo->getDimensions($full);
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
<p align="center" class="pcaption"><?php echo editCaption($gallery->album, $index) ?>

<!-- Custom Fields -->
<?php
	displayPhotoFields($index, $extra_fields, true, in_array('EXIF', $extra_fields), $full);
?>
</p>

<!-- voting -->
<?php

/*
** Block for Voting
*/

if ( canVote()) {
	echo "\n<!-- Voting pulldown -->\n";
	echo makeFormIntro("view_photo.php", array("name" => "vote_form",
                                       "method" => "POST"));
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
?>

<?php if ($gallery->user->isLoggedIn() &&  
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

		echo _("Email me when comments are added");
?>
	<input type="checkbox" name="comments" <?php echo ($gallery->album->getEmailMe('comments', $gallery->user, $id)) ? "checked" : "" ?> onclick="location.href='<?php echo $url; ?>'" >
	</form>
<?php
	} 
}
echo "</div>";
includeLayout('navtablebegin.inc');
includeLayout('navphoto.inc');
$breadcrumb["top"] = false;
includeLayout('navtablemiddle.inc');
includeLayout('breadcrumb.inc');
includeLayout('navtableend.inc');
includeLayout('ml_pulldown.inc');
if ($fitToWindow) {
?>
<script type="text/javascript">
<!--
	calculateNewSize();
//-->
</script>
<?php 
}
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
if (isset($printEZPrintsForm)) { ?>
<form method="post" name="ezPrintsForm" action="http://gallery.mye-pix.com/partner.asp">
  <?php
     /* Print the caption on back of photo. If no caption,
      * then print the URL to this page. */
     $imbkprnt = $gallery->album->getCaption($index);
     if (empty($imbkprnt)) {
        $imbkprnt = makeAlbumUrl($gallery->session->albumName, $id);
     }
  ?>
  <input type="hidden" name="count" value="1">
  <input type="hidden" name="title0" value="<?php echo htmlentities(strip_tags($imbkprnt)) ?>">
  <input type="hidden" name="lo_res_url0" value="<?php echo $thumbImage ?>">
  <input type="hidden" name="hi_res_url0" value="<?php echo $rawImage ?>">
  <input type="hidden" name="returnpage" value="this-gets-set-by-javascript-in-onClick">
  <input type="hidden" name="width0" value="<?php echo $imageWidth ?>">
  <input type="hidden" name="height0" value="<?php echo $imageHeight ?>">
  <input type="hidden" name="startwith" value="cart">
</form>
<?php }
	includeHtmlWrap("photo.footer");
	if (!$GALLERY_EMBEDDED_INSIDE) { ?>
</body>
</html>
<?php } ?>
