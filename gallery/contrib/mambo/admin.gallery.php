<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2005 Bharat Mediratta
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
 * Gallery Component for Mambo Open Source CMS v4.5 or newer
 * Original author: Beckett Madden-Woods <beckett@beckettmw.com>
 *
 * $Id$
 */

defined( '_VALID_MOS' ) or die( 'Direct Access to this location is not allowed.' );

// ensure user has access to this function
if (!($acl->acl_check('administration', 'edit', 'users', $my->usertype, 'components', 'all') | $acl->acl_check('administration', 'edit', 'users', $my->usertype, 'components', 'com_gallery'))) {
	mosRedirect('index2.php', _NOT_AUTH);
}

require_once($mainframe->getPath('admin_html'));

$act = mosGetParam($_REQUEST, 'act', null);
$task = mosGetParam($_REQUEST, 'task', array(0));
$cid = mosGetParam($_POST, 'cid', array(0));
if (!is_array( $cid )) {
	$cid = array(0);
}

switch ($task) {
	case "save":
		saveSettings($option, $act);
	break;
	default:
		viewSettings($option, $act);
	break;
}

/* Displays Gallery component settings */
function viewSettings( $option, $act ) {
	global $database, $my, $acl;

	$row = new mosUser($database);
	// load the row from the db table
	$cid = mosGetParam($_REQUEST, 'cid', array(0));
	$uid = intval($cid[0]);
	$row->load($uid);
	
	$database->setQuery("SELECT * FROM #__gallery");
	$param = $database->loadRowList();

	/* extract params from the DB query */
	$params = array();
	foreach ($param as $curr) {
		$params[$curr[0]] = $curr[1];
	}

	/* Code to generate list of groups to select minimum Gallery Admin
	 * authorization level (copied from MOS com_users/admin.users.php) */

	$my_group = strtolower($acl->get_group_name($row->gid, 'ARO'));

	// ensure user can't add group higher than themselves
	$my_groups = $acl->get_object_groups('users', $my->id, 'ARO');
	if (is_array($my_groups) && count($my_groups) > 0) {
		$ex_groups = $acl->get_group_children($my_groups[0], 'ARO', 'RECURSE');
	} else {
		$ex_groups = array();
	}

	$gtree = $acl->get_group_children_tree(null, 'USERS', false);

	// remove users 'above' me
	$i = 0;
	while ($i < count($gtree)) {
		if (in_array($gtree[$i]->value, $ex_groups)) {
			array_splice($gtree, $i, 1);
		} else {
			$i++;
		}
	}

	$params['minAuthType'] = mosHTML::selectList($gtree, 'minAuthType', 'size="6"', 'value', 'text', isset($params['minAuthType']) ? $params['minAuthType'] : 20);
	$params['hideRightSide'] = mosHTML::yesnoSelectList('hideRightSide', 'class="inputbox" size="1"', isset($params['hideRightSide']) ? $params['hideRightSide'] : 1);
	
	HTML_content::showSettings($option, $params, $act);
}

/* Saves Gallery component settings */
function saveSettings( $option, $act ) {
	global $database;

	$path = mosGetParam($_POST, 'path', '');
	if (!is_dir($path)) {
		echo "<script> alert('Path must be a full server path to your Gallery!'); window.history.go(-1); </script>\n";
		die;
	}
	if (!ereg('[/\\]$', $path)) {
		$path .= addslashes(DIRECTORY_SEPARATOR);
	}
	$params['path'] = $path;
	
	$params['minAuthType'] = mosGetParam($_POST, 'minAuthType', 20);
	$params['hideRightSide'] = mosGetParam($_POST, 'hideRightSide', true);

	foreach ($params as $field => $value) {
		$database->setQuery("UPDATE #__gallery SET value='$value' WHERE field='$field'");
		if (!$database->query()) {
			echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
			die;
		}
	}
	mosRedirect( "index2.php?mosmsg=The%20Gallery%20component%20settings%20have%20been%20saved%20successfully." );
}
?>
