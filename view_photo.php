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
 * $Id$
 */

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

// Hack check and prevent errors
if (empty($gallery->session->albumName) ||
	!$gallery->user->canReadAlbum($gallery->album) ||
	!$gallery->album->isLoaded())
{
	$gallery->session->gRedirDone = false;
	header("Location: " . makeAlbumHeaderUrl('', '', array('gRedir' => 1)));
	return;
}

list($full, $id, $index, $votes) =
	getRequestVar(array('full', 'id', 'index', 'votes'));

list($save, $commenter_name, $comment_text) =
	getRequestVar(array('save', 'commenter_name', 'comment_text'));

// Set $index from $id
if (isset($id)) {
	$index = $gallery->album->getPhotoIndex($id);
	if ($index == -1) {
		// That photo no longer exists.
		header("Location: " . makeAlbumHeaderUrl($gallery->session->albumName));
		return;
	}
}
else {
    if ($index > $gallery->album->numPhotos(1)) {
        $index = $gallery->album->numPhotos(1);
    }
    $id = $gallery->album->getPhotoId($index);
}

$nextId = getNextId($id);

$currentUrlResized = makeAlbumHeaderUrl($gallery->session->albumName, $id);

/* Determine if user has the rights to view full-sized images */
if (!empty($full) && !$gallery->user->canViewFullImages($gallery->album)) {
	header("Location: $currentUrlResized");
	return;
}
elseif (!$gallery->album->isResized($index) &&
	!$gallery->user->canViewFullImages($gallery->album))
{
	header("Location: " . makeAlbumHeaderUrl($gallery->session->albumName));
	return;
}

if (!isset($full) || (isset($full) && !$gallery->album->isResized($index))) {
	$full = NULL;
}

if (!empty($votes) && canVote()) {
	if (!isset($votes[$id]) &&
		$gallery->album->getPollScale() == 1 &&
		$gallery->album->getPollType() == "critique")
	{
		$votes[$id] = null;
	}

	saveResults($votes);
	if ($gallery->album->getPollShowResults()) {
		list($buf, $rank)=showResultsGraph(0);
		print $buf;
	}
}

$albumName = $gallery->session->albumName;
$noCount = getRequestVar('noCount');

if ($noCount != 1 && !isset($gallery->session->viewedItem[$gallery->session->albumName][$id]) &&
    !$gallery->session->offline) {
	$gallery->session->viewedItem[$albumName][$id] = 1;
	$gallery->album->incrementItemClicks($index);
}

$photo = $gallery->album->getPhoto($index);

if ($photo->isMovie()) {
	$image = $photo->thumbnail;
}
else {
	$image = $photo->image;
}

$photoURL = $gallery->album->getAlbumDirURL("full") . "/" . $image->name . "." . $image->type;
list($imageWidth, $imageHeight) = $image->getRawDimensions();

$do_fullOnly = isset($gallery->session->fullOnly) &&
		     !strcmp($gallery->session->fullOnly,"on") &&
		     !strcmp($gallery->album->fields["use_fullOnly"], "yes");

if ($do_fullOnly) {
	$full = $gallery->user->canViewFullImages($gallery->album);
}

$fitToWindow = $gallery->album->fields["fit_to_window"] == "yes" &&
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

/**
 * We might be prev/next navigating using this page so recalculate the 'page' variable
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

$bordercolor = $gallery->album->fields['bordercolor'];

$navigator['id']	  = $id;
$navigator['allIds']	  = $gallery->album->getIds($gallery->user->canWriteToAlbum($gallery->album));
$navigator['url']	  = '.';
$navigator['bordercolor'] = $bordercolor;

/* -- breadcrumb text --- */
$breadcrumb['text'] = returnToPathArray($gallery->album, true, true);

$extra_fields = $gallery->album->getExtraFields(false);
$g_pageTitle = NULL;
if (in_array("Title", $extra_fields)) {
	$g_pageTitle = $gallery->album->getExtraField($index, "Title");
}
if (!$g_pageTitle) {
	$g_pageTitle = $photo->image->name;
}

$maxlength = isset($gallery->app->comments_length) ? $gallery->app->comments_length : 0;

require(dirname(__FILE__) . '/includes/comments/commentHandling.inc.php');

$metaTags = array();
$keyWords = $gallery->album->getKeywords($index);
if (!empty($keyWords)) {
	$metaTags['Keywords'] = ereg_replace("[[:space:]]+",' ',$keyWords);
}

/* prefetch/navigation */
$navcount = sizeof($navigator['allIds']);
$navpage = $navcount - 1;
while ($navpage > 0) {
	if (!strcmp($navigator['allIds'][$navpage], $id)) {
		break;
	}
	$navpage--;
}

$useIcons = (!$iconsForItemOptions || $gallery->app->useIcons == 'no') ? false : true;
$albumItemOptions = getItemActions($index, $useIcons);

$page_url = makeAlbumUrl($gallery->session->albumName, $id, array("full" => 0));
$iconElements = array();
$adminTextIconElemens = array();

if (!$gallery->album->isMovie($id)) {
	if (!empty($gallery->album->fields["print_photos"]) && !$gallery->session->offline){
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
		}
		else if ($photo->image->resizedName) {
			$thumbImage .= $photo->image->name . "." . "sized" . "." . $photo->image->type;
		}
		else {
			$thumbImage .= $photo->image->name . "." . $photo->image->type;
		}

		list($imageWidth, $imageHeight) = $photo->image->getRawDimensions();

		/* Now build the admin Texts / left colun */
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
			'Mobile Service' => array('mpush' => 'mPUSH (mobile service)')
		);

		/* display a <select> menu if more than one option */
		if ($numServices > 1) {
			// Build an array with groups, but only for enabled services
			foreach ($fullNames as $serviceGroupName => $serviceGroup) {
				foreach ($serviceGroup as $name => $fullName) {
					if (!in_array($name, $printServices)) {
						continue;
					}
					else {
						$serviceGroups[$serviceGroupName][$name] = $fullName;
					}
				}
			}

			$options = array();

			if (isset($serviceGroups['Mobile Service'])) {
				$options[] = gTranslate('core', "Send photo to...") ;
			}
			else {
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
				array('onChange' =>'doPrintService()', 'class' => 'g-admin')
			);
			$printServicesText .= '</form>';
			$adminTextIconElemens[] = $printServicesText;

			/* just print out text if only one option */
		}
		elseif ($numServices == 1) {
			foreach ($fullNames as $serviceGroupName => $serviceGroup) {
				foreach ($serviceGroup as $name => $fullName) {
					if (!in_array($name, $printServices)) {
						continue;
					}
					else {
						enablePrintForm($name);
						$iconText = getIconText('', sprintf(gTranslate('core', "process this photo with %s"), $fullName));
						$adminTextIconElemens[] = "<a class=\"g-admin\" href=\"#\" onClick=\"doPrintService('$name');\">$iconText</a>";
					}
				}
			}
		}

	}

	if (!strcmp($gallery->album->fields["use_fullOnly"], "yes") &&
	  	!$gallery->session->offline  &&
	  	$gallery->user->canViewFullImages($gallery->album))
	{
		$lparams['set_fullOnly'] = (!isset($gallery->session->fullOnly) || strcmp($gallery->session->fullOnly,"on")) ? "on" : "off";
		$link = makeAlbumURL($gallery->session->albumName, $id, $lparams);
		$adminTextIconElemens[] = gTranslate('core', 'View Images:');
		$iconTextNormal = gTranslate('core', "normal");
		$iconTextFull = gTranslate('core', "full");

		if (!isset($gallery->session->fullOnly) || strcmp($gallery->session->fullOnly,"on")) {
			$adminTextIconElemens[] = $iconTextNormal;
			$adminTextIconElemens[] = '|';
			$adminTextIconElemens[] = "<a class=\"g-admin\" href=\"$link\">[". $iconTextFull .']</a>';
		}
		else {
			$adminTextIconElemens[] = "<a class=\"g-admin\" href=\"$link\">[" .$iconTextNormal .']</a>';
			$adminTextIconElemens[] = '|';
			$adminTextIconElemens[] = $iconTextFull;
		}
	}

	/* Show all separate item options. Such as eCard or photo properties link. */
	foreach ($albumItemOptions as $key => $option) {
		if (!isset($option['separate'])) continue;

		if(!empty($option['value'])) {
			if (stristr($option['value'], 'popup')) {
				$content = popup_link(
					$option['text'], $option['value'],
					true, false, 550, 600, '', '', $option['icon']);
			}
			else {
				$content = galleryIconLink($option['value'], $option['icon'], $option['text']);
			}

			$adminTextIconElemens[] = $content;
		}
		/* remove it from the list, as later on we do only need the rest of the options. */
		unset($albumItemOptions[$key]);
	}
}
// Endif !movie

/* Show a dropdown if no icons are wanted and there a items
 * Note: First options is just a descriptive text
*/
if(sizeof($albumItemOptions) > 1 && !$useIcons) {
	$iconElements[] =
	'<form name="admin_options_form" action="view_album.php" class="right">' .
	drawSelect2(
		'itemOptions',
		$albumItemOptions,
		array(
			'onChange' => "imageEditChoice(document.admin_options_form.itemOptions)",
			'class' => 'g-admin'
		)) .
	"</form>\n"
	;
}

$iconElements[] = LoginLogoutButton($currentUrlResized);

$adminbox['text']		= makeIconMenu($adminTextIconElemens, 'left');
$adminbox['commands']		= makeIconMenu($iconElements, 'right');
$adminbox['bordercolor']	= $bordercolor;

$breadcrumb['bordercolor']	= $bordercolor;

/* Icon menu above the photo */
if($useIcons && sizeof($albumItemOptions) > 1) {
	foreach ($albumItemOptions as $trash => $option) {
		if(!empty($option['value'])) {
			if (stristr($option['value'], 'popup')) {
				$content = popup_link(
					$option['text'], $option['value'],
					true, false, 500, 500, '', '', $option['icon']);
			}
			else {
				$content = galleryIconLink($option['value'], $option['icon'], $option['text']);
			}

			$itemActions[] = $content;
		}
	}
	$itemActionsMenu = makeIconMenu($itemActions, 'center', true, true);
}

#-- if borders are off, just make them the bgcolor ----
if ($gallery->album->fields['border'] == 0) {
	$bordercolor = $gallery->album->fields['bgcolor'];
}
if ($bordercolor) {
	$bordercolor = "bgcolor=$bordercolor";
}

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
}
else {
	$href = $gallery->album->getPhotoPath($index) ;
}

$frame = $gallery->album->fields['image_frame'];
if ($fitToWindow && (eregi('safari|opera', $_SERVER['HTTP_USER_AGENT']) || $gallery->session->offline)) {
	//Safari/Opera can't render dynamically sized image frame
	$frame = 'none';
}

$allImageAreas = $gallery->album->getAllImageAreas($index);

if(empty($full) && !empty($allImageAreas)) {
	$imageMapHTML = showImageMap($index, $href);
	$photoTag = $gallery->album->getPhotoTag($index, $full, array('id' => 'galleryImage', 'usemap' => '#myMap'));
}
else {
	$photoTag = $gallery->album->getPhotoTag($index, $full, array('id' => 'galleryImage'));
}

list($width, $height) = $photo->getDimensions($full);

$gallery->html_wrap['borderColor']		= $gallery->album->fields["bordercolor"];
$gallery->html_wrap['borderWidth']		= $gallery->album->fields["border"];
$gallery->html_wrap['frame']			= $frame;
$gallery->html_wrap['imageWidth']		= $width;
$gallery->html_wrap['imageHeight']		= $height;
$gallery->html_wrap['imageHref']		= $href;
$gallery->html_wrap['imageTag']			= $photoTag;

if ($fitToWindow && $gallery->user->canViewFullImages($gallery->album)) {
	$gallery->html_wrap['attrlist']['onclick'] = 'sizeChange.toggle()';
}

$gallery->html_wrap['pixelImage'] = getImagePath('pixel_trans.gif');


if ($gallery->app->comments_enabled == 'yes' &&
	$gallery->user->isLoggedIn() &&
	$gallery->user->getEmail() &&
	!$gallery->session->offline &&
	$gallery->app->emailOn == "yes")
{
	$emailMeComments = getRequestVar('emailMeComments');
	if (!empty($emailMeComments)) {
		if ($emailMeComments == 'true') {
			$gallery->album->setEmailMe('comments', $gallery->user, $id);
		}
		else {
			$gallery->album->unsetEmailMe('comments', $gallery->user, $id);
		}
	}

	if (! $gallery->album->getEmailMe('comments', $gallery->user)) {
		$emailMeForm = "\n<form name=\"emailMe\" class=\"left g-emailMe-box\" action=\"#\">";

		$url= makeAlbumUrl($gallery->session->albumName, $id, array(
			'emailMeComments' => ($gallery->album->getEmailMe('comments', $gallery->user, $id)) ? 'false' : 'true')
		);

		$checked = ($gallery->album->getEmailMe('comments', $gallery->user, $id)) ? " checked" : "";
		$emailMeForm .= "<input type=\"checkbox\" name=\"comments\" $checked onclick=\"location.href='$url'\"> ";

		$emailMeForm .= gTranslate('core', "Email me when comments are added");

		$emailMeForm .= "\n</form>";
	}
}

define('READY_TO_INCLUDE', 'DISCO');
$templateFile = getDefaultFilename(GALLERY_BASE .'/templates/photo.tpl');

require($templateFile);
?>