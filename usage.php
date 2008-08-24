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

set_time_limit(120);
require(dirname(__FILE__) . '/init.php');
require(dirname(__FILE__) . '/lib/usage.php');

list($action, $sortby, $order) = getRequestVar(array('action', 'sortby', 'order'));

$iconElements = array();
$messages = array();

$adminbox['text'] = '<span class="g-title">'. gTranslate('core', "Filesystem usage") .'</span>';

if (!($gallery->user->isAdmin())) {
	if ($gallery->user->isLoggedIn()) {
		$messages[] = array(
			'type' => 'information',
			'text' => sprintf(gTranslate('core', "You are currently logged in as %s."),
					  '<i>'. $gallery->user->username .'</i>')
		);
	}

	$messages[] = array(
		'type' => 'information',
		'text' => gTranslate('core', "You must be logged in as an administrator to see the usage.")
	);
}
else {
	$iconElements[] = galleryIconLink(
				makeGalleryUrl("admin-page.php"),
				'navigation/return_to.gif',
				gTranslate('core', "Return to _admin page"));
}

$iconElements[] = galleryIconLink(
				makeAlbumUrl(),
				'navigation/return_to.gif',
				gTranslate('core', "Return to _gallery"));

$iconElements[] = LoginLogoutButton(makeGalleryUrl("usage.php"));

$adminbox['commands'] = makeIconMenu($iconElements, 'right');

$actionChoice = array(
	'byUser'	=> gTranslate('core',"Show usage per each user"),
	'perAlbum'	=> gTranslate('core',"Show usage per album")
);

$sortbyChoices = array(
	'bytes'	=> gTranslate('core',"used space"),
	'uname'	=> gTranslate('core',"username")
);

$orderChoices = array(
	'desc'	=> gTranslate('core',"descending"),
	'asc'	=> gTranslate('core',"ascending")
);

/* --- Lets Start the real output --- */

if (!$GALLERY_EMBEDDED_INSIDE) {
	doctype();
?>
<html>
<head>
<title><?php echo clearGalleryTitle(gTranslate('core', "User / album Usage")) ?></title>
<?php
	common_header() ;
?>
</head>
<body>
<?php
}

includeTemplate("gallery.header", '', 'classic');


includeLayout('adminbox.inc');
includeLayout('breadcrumb.inc');

printInfoBox($messages);

if ($gallery->user->isAdmin()) {
	echo "<br>";
	echo gTranslate('core', "Getting the filesystem usage may take a long time! Choose which usage you want to see.");
	echo makeFormIntro('usage.php');
	echo drawSelect('action', $actionChoice, $action);
	echo gTranslate('core', "sorted by:");
	echo drawSelect('sortby', $sortbyChoices, $sortby);
	echo gTranslate('core', "ordered:");
	echo drawSelect('order', $orderChoices, $order);
	echo gSubmit('submit', gTranslate('core', "Do it !"));
	echo "</form>\n<br>";

	if (!empty($action)) {

		switch($action) {
			case 'byUser':
				showUsageByUser($sortby, $order);

				break;

			case 'perAlbum':
				showUsagePerAlbum($sortby, $order);

				break;
		}

		/*
		$dir = $gallery->app->albumDir;
		$size = formatted_filesize(get_size($dir, true));

		echo "<p>Your Albums dir ($dir') is <b>$size</b><br>";
		*/
	}
}

includeTemplate("overall.footer");

if (!$GALLERY_EMBEDDED_INSIDE) {
?>
   </body>
   </html>
<?php
}
?>
