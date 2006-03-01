<?php
// $Id$
/**
* Content code
* @package Mambo Open Source
* @Copyright (C) 2000 - 2003 Miro International Pty Ltd
* @ All rights reserved
* @ Mambo Open Source is Free Software
* @ Released under GNU/GPL License : http://www.gnu.org/copyleft/gpl.html
* @version $Revision$
**/

defined( '_VALID_MOS' ) or die( 'Direct Access to this location is not allowed.' );

/**
* Utility functions and classes
*/

function mosLoadComponent( $name ) {
	// set up some global variables for use by the frontend component
	global $mainframe, $database;
	
	include( $mainframe->getCfg( 'absolute_path' )."/components/com_$name/$name.php" );
}

function mosCountModules(  $position='left' ) {
	global $database, $my, $Itemid, $MOS_GALLERY_PARAMS;

	if (isset($MOS_GALLERY_PARAMS['hideRightSide'])
	    && $MOS_GALLERY_PARAMS['hideRightSide'])
	{
		if ($position == 'right' || $position == 'user2') {
			return 0;
		}
	}

	$query = "SELECT COUNT(*)"
	."\nFROM #__modules AS m"
	. "\nINNER JOIN #__modules_menu AS mm ON mm.moduleid=m.id"
	. "\nWHERE m.published='1' AND m.access <= '$my->gid' AND m.position='$position'"
	. "\nAND (mm.menuid = '$Itemid' OR mm.menuid = '0')";
	
	$database->setQuery($query);
	//echo $database->getQuery();
	return $database->loadResult();
}

function mosLoadModules( $position='left', $horiz=false ) {
	global $database, $my, $Itemid;
	
	require_once( "includes/frontend.html.php" );

	$query = "SELECT id, title, module, position, content, showtitle, params"
	."\nFROM #__modules AS m, #__modules_menu AS mm"
	. "\nWHERE m.published='1' AND m.access <= '$my->gid' AND m.position='$position'"
	. "\nAND mm.moduleid=m.id"
	. "\nAND (mm.menuid = '$Itemid' OR mm.menuid = '0')"
	. "\nORDER BY ordering";
	
	$database->setQuery( $query );
	$modules = $database->loadObjectList();
	if($database->getErrorNum()) {
		echo "MA ".$database->stderr(true);
		return;
	}
	
	if (count( $modules ) < 1) {
		$horiz = false;
	}
	if ($horiz) {
		echo "<table cellspacing=\"1\" cellpadding=\"0\" border=\"0\" width=\"100%\">";
		echo "\n<tr>";
	}
	foreach ($modules as $module) {
		$params = mosParseParams( $module->params );

		if ($horiz) {
			echo "<td valign=\"top\">";
		}
			
		if ((substr("$module->module",0,4))=="mod_") {
			modules_html::module2( $module, $params, $Itemid );
		} else {
			modules_html::module( $module, $params, $Itemid );
		}

		if ($horiz) {
			echo "</td>";
		}
	}
	if ($horiz) {
		echo "\n</tr>\n</table>";
	}
}

/**
*
*/
function mosParseParams( $txt ) {
	$sep1 = "\n";	// line separator
	$sep2 = "=";	// key value separator
	
	$temp = explode( $sep1, $txt );
	$obj = new stdClass();
	// We use trim() to make sure a numeric that has spaces
	// is properly treated as a numeric
	foreach ($temp as $item) {
		if($item) {
			$temp2 = explode( $sep2, $item, 2 );
			$k = trim( $temp2[0] );
			if (isset( $temp2[1] )) {
				$obj->$k = trim( $temp2[1] );
			} else {
				$obj->$k = $k;
			}
		}
	}
	return $obj;
}

function mosSendAdminMail( $adminName, $adminEmail, $email, $type, $title, $author ) {
	global $mosConfig_live_site;

	$headers = "";
	$subject = _MAIL_SUB." '$type'";
	$message = _MAIL_MSG;
	eval ("\$message = \"$message\";");
	$headers .= "From: ".$adminName." <".$adminEmail.">\r\n";
	$headers .= "Reply-To: <".$adminEmail.">\r\n";
	$headers .= "X-Priority: 3\r\n";
	$headers .= "X-MSMail-Priority: Low\r\n";
	$headers .= "X-Mailer: Mambo Open Source 4.5\r\n";
	@mail($adminEmail, $subject, $message, $headers);
}

/**
* Displays a not authorised message
*
* If the user is not logged in then an addition message is displayed.
*/
function mosNotAuth() {
	global $my;

	echo _NOT_AUTH;
	if ($my->id < 1) {
		echo "<br>" . _DO_LOGIN;
	}
}
?>