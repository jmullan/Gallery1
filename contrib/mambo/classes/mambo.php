<?php
// $Id$
/**
* Foundation classes
* @package Mambo Open Source
* @Copyright (C) 2000 - 2003 Miro International Pty Ltd
* @ All rights reserved
* @ Mambo Open Source is Free Software
* @ Released under GNU/GPL License : http://www.gnu.org/copyleft/gpl.html
* @version $Revision$
**/

// ensure this file is being included by a parent file
defined( '_VALID_MOS' ) or die( 'Direct Access to this location is not allowed.' );
define( '_MOS_MAMBO_INCLUDED', 1 );

if (@$mosConfig_error_reporting === 0) {
	error_reporting( 0 );
} else if (@$mosConfig_error_reporting > 0) {
	error_reporting( $mosConfig_error_reporting );
}

// Verify that the $mosConfig_absolute_path isn't overwritten with a remote exploit
if (!realpath($mosConfig_absolute_path)) {
	print _("Security violation") ."\n";
	exit;
} else {
	if (! defined('MOSCONFIG_ABSOLUTE_PATH')) {
		define("MOSCONFIG_ABSOLUTE_PATH", $mosConfig_absolute_path);
	}
}

$local_backup_path = MOSCONFIG_ABSOLUTE_PATH. '/administrator/backups';
$media_path = MOSCONFIG_ABSOLUTE_PATH. '/media/';
$image_path = MOSCONFIG_ABSOLUTE_PATH. '/images/stories';
$image_size = 100;

include_once(MOSCONFIG_ABSOLUTE_PATH . '/version.php');

require_once(MOSCONFIG_ABSOLUTE_PATH . '/classes/database.php');
require_once(MOSCONFIG_ABSOLUTE_PATH . '/classes/gacl.class.php');
require_once(MOSCONFIG_ABSOLUTE_PATH . '/classes/gacl_api.class.php');

/**
* MOS Mainframe class
*
* Provide many supporting API functions
*/
class mosMainFrame {
	/** @var database Internal database class pointer */
	var $_db=null;
	/** @var object An object of configuration variables */
	var $_config=null;
	/** @var object An object of path variables */
	var $_path=null;
	/** @var mosSession The current session */
	var $_session=null;
	/** @var string The current template */
	var $_template=null;
	/** @var array An array to hold global user state within a session */
	var $_userstate=null;
	/**
	* Class constructor
	* @param database A database connection object
	* @param string The url option
	* @param string The path of the mos directory
	*/
	function mosMainFrame( &$db, $option, $basePath ) {
		$this->_db = $db;

		// load the configuration values
		//return( $this->loadConfig() );
		$this->_setConfig( $basePath );
		$this->_setTemplate();
		$this->_setAdminPaths( $option, $this->getCfg( 'absolute_path' ) );
		if (isset( $_SESSION['session_userstate'] )) {
			$this->_userstate =& $_SESSION['session_userstate'];
		} else {
			$this->_userstate = null;
		}
	}
	/**
	* Gets the value of a user state variable
	* @param string The name of the variable
	*/
	function getUserState( $var_name ) {
		if (is_array( $this->_userstate )) {
			return mosGetParam( $this->_userstate, $var_name, null );
		} else {
			return null;
		}
	}
	/**
	* Gets the value of a user state variable
	* @param string The name of the user state variable
	* @param string The name of the variable passed in a request
	* @param string The default value for the variable if not found
	*/
	function getUserStateFromRequest( $var_name, $req_name, $var_default=null ) {
		if (is_array( $this->_userstate )) {
			if (isset( $_REQUEST[$req_name] )) {
				$this->setUserState( $var_name, $_REQUEST[$req_name] );
			} else if (!isset( $this->_userstate[$var_name] )) {
				$this->setUserState( $var_name, $var_default );
			}
			return $this->_userstate[$var_name];
		} else {
			return null;
		}
	}
	/**
	* Sets the value of a user state variable
	* @param string The name of the variable
	* @param string The value of the variable
	*/
	function setUserState( $var_name, $var_value ) {
		if (is_array( $this->_userstate )) {
			$this->_userstate[$var_name] = $var_value;
		}
	}
	/**
	* Initialises the user session
	*
	* Old sessions are flushed based on the configuration value for the cookie
	* lifetime. If an existing session, then the last access time is updated.
	* If a new session, a session id is generated and a record is created in
	* the mos_sessions table.
	*/
	function initSession() {
		$past = time() - intval( $this->getCfg( 'lifetime' ) );
		$query = "DELETE FROM #__session WHERE (time < $past) AND (usertype <> 'administrator' AND usertype <> 'superadministrator')";
		$this->_db->setQuery($query);
		if (!$this->_db->query()) {
			die($this->_db->stderr(true));
		}

		$lifetime = time() + intval( $this->getCfg( 'lifetime' ) );
		$session =& $this->_session;

		$session = new mosSession( $this->_db );
		$session->purge(intval( $this->getCfg( 'lifetime' ) ));

		$sessioncookie = mosGetParam( $_COOKIE, 'sessioncookie', null );

		if ($session->load( md5( $sessioncookie ) )) {
			if ($session->username) {
				setcookie( "usercookie", $session->getCookie(), $lifetime, "/" );
				//$_COOKIE["usercookie"] = $session->getCookie();
			}
			$session->time = time();
			$session->update();

		} else {
			$session->generateId();
			$session->guest = 1;
			$session->username = '';
			$session->time = time();
			$session->gid = 0;

			if (!$session->insert()) {
				die( $session->getError() );
			}

			setcookie( "sessioncookie", $session->getCookie(), $lifetime, "/" );
			//$_COOKIE["usercookie"] = $session->getCookie();
		}
	}
	/**
	* Login validation function
	*
	* Username and encoded password is compare to db entries in the mos_users
	* table. A successful validation updates the current session record with
	* the users details.
	*/
	function login() {
		global $acl;

		$usercookie = mosGetParam( $_COOKIE, 'usercookie', '' );
		$sessioncookie = mosGetParam( $_COOKIE, 'sessioncookie', '' );
		$username = trim( mosGetParam( $_POST, 'username', '' ) );
		$passwd = trim( mosGetParam( $_POST, 'passwd', '' ) );

		if (!$username || !$passwd) {
			echo "<script> alert(\""._LOGIN_INCOMPLETE."\"); window.history.go(-1); </script>\n";
			exit();
		} else {
			$passwd = md5( $passwd );

			$this->_db->setQuery( "SELECT id, gid, block, usertype"
			. "\nFROM #__users"
			. "\nWHERE username='$username' AND password='$passwd' AND block='0'"
			);
			$row = null;
			if ($this->_db->loadObject( $row )) {
				if ($row->block == 1) {
					echo "<script>alert(\""._LOGIN_BLOCKED."\"); window.history.go(-1); </script>\n";
					exit();
				}
				// fudge the group stuff
				$grp = $acl->getAroGroup( $row->id );
				$row->gid = 1;

				if ($acl->is_group_child_of( $grp->name, 'Registered', 'ARO' )) {
					// fudge Authors, Editors and Publishers into the Special Group
					$row->gid = 2;
				}
				$row->usertype = $grp->name;

				$session =& $this->_session;
				$session->guest = 0;
				$session->username = $username;
				$session->userid = intval( $row->id );
				$session->usertype = $row->usertype;
				$session->gid = intval( $row->gid );

				$session->update();

				$lifetime = time() + intval( $this->getCfg( 'lifetime' ) );
				setcookie( "usercookie", $session->getCookie(), $lifetime, "/" );
			} else {
				echo "<script>alert(\""._LOGIN_INCORRECT."\"); window.history.go(-1); </script>\n";
				exit();
			}
		}
	}
	/**
	* User logout
	*
	* Reverts the current session record back to 'anonymous' parameters
	*/
	function logout() {
		$session =& $this->_session;

		$query = "SELECT registerDate FROM #__users WHERE id='$session->userid'";
		$this->_db->setQuery($query);
		if (!$this->_db->query()) {
			die($this->_db->stderr(true));
		}
		$registerDate = $this->_db->loadResult();

		$currentDate = date("Y-m-d\TH:i:s");
		$query = "UPDATE #__users SET registerDate='$registerDate', lastvisitDate='$currentDate' where id='$session->userid'";
		$this->_db->setQuery($query);
		if (!$this->_db->query()) {
			die($this->_db->stderr(true));
		}

		$session->guest = 1;
		$session->username = '';
		$session->userid = '';
		$session->usertype = '';
		$session->gid = 0;

		$session->update();

		// this is daggy??
		@session_destroy();
	}
	/**
	* @return mosUser A user object with the information from the current session
	*/
	function getUser() {
		$user = new mosUser( $this->_db );

		$user->id = intval( $this->_session->userid );
		$user->username = $this->_session->username;
		$user->usertype = $this->_session->usertype;
		$user->gid = intval( $this->_session->gid );

		return $user;
	}
	/**
	* Loads the configuration.php file and assigns values to the internal variable
	* @param string The base path from which to load the configuration file
	*/
	function _setConfig( $basePath='.' ) {
		$this->_config = new stdClass();

		require( "$basePath/configuration.php" );

		$this->_config->offline = $mosConfig_offline;
		$this->_config->host = $mosConfig_host;
		$this->_config->user = $mosConfig_user;
		$this->_config->password = $mosConfig_password;
		$this->_config->db = $mosConfig_db;
		$this->_config->dbprefix = $mosConfig_dbprefix;
		$this->_config->lang = $mosConfig_lang;
		$this->_config->absolute_path = MOSCONFIG_ABSOLUTE_PATH;
		$this->_config->live_site = $mosConfig_live_site;
		$this->_config->sitename = $mosConfig_sitename;
		$this->_config->shownoauth = $mosConfig_shownoauth;
		$this->_config->offline_message = $mosConfig_offline_message;
		$this->_config->error_message = $mosConfig_error_message;
		$this->_config->UseBanner = $mosConfig_UseBanner;
		$this->_config->lifetime = $mosConfig_lifetime;
		$this->_config->MetaDesc = $mosConfig_MetaDesc;
		$this->_config->MetaKeys = $mosConfig_MetaKeys;
		$this->_config->debug = $mosConfig_debug;
		$this->_config->vote = $mosConfig_vote;
		$this->_config->hideAuthor = $mosConfig_hideAuthor;
		$this->_config->hideCreateDate = $mosConfig_hideCreateDate;
		$this->_config->hideModifyDate = $mosConfig_hideModifyDate;
		$this->_config->hidePdf = $mosConfig_hidePdf;
		$this->_config->hidePrint = $mosConfig_hidePrint;
		$this->_config->hideEmail = $mosConfig_hideEmail;
		$this->_config->enable_log_items = $mosConfig_enable_log_items;
		$this->_config->enable_log_searches = $mosConfig_enable_log_searches;
		$this->_config->sef = $mosConfig_sef;
		$this->_config->vote = $mosConfig_vote;
		$this->_config->hideModifyDate = $mosConfig_hideModifyDate;
		$this->_config->multipage_toc = $mosConfig_multipage_toc;
		$this->_config->allowUserRegistration = $mosConfig_allowUserRegistration;
		$this->_config->error_reporting = $mosConfig_error_reporting;
		$this->_config->link_titles = $mosConfig_link_titles;
	}
	/**
	* @param string The name of the variable (from configuration.php)
	* @return mixed The value of the configuration variable or null if not found
	*/
	function getCfg( $varname ) {
		if (isset( $this->_config->$varname )) {
			return $this->_config->$varname;
		} else {
			return null;
		}
	}

	// TODO
	function loadConfig() {
		unset( $this->_config );

		$this->_db->setQuery( "SELECT name,value FROM #__config2" );
		if (!$this->_config = $this->_db->loadObjectList( 'name' )) {
			echo $this->_db->stderr();
			return false;
		}
		return true;
	}

	function _setTemplate() {
		$mosConfig_absolute_path = $this->getCfg( 'absolute_path' );

		$t = new mosTemplate( $this->_db );
		$t->load( 0 );
		$cur_template = $t->cur_template;
		$col_main = $t->col_main;

		// TemplateChooser Start
		$mos_user_template = mosGetParam( $_COOKIE, 'mos_user_template', '' );
		$mos_change_template = mosGetParam( $_REQUEST, 'mos_change_template', $mos_user_template );
		if ($mos_change_template) {
			// check that template exists in case it was deleted
			if (file_exists( "$mosConfig_absolute_path/templates/$mos_change_template/index.php" )) {
				$lifetime = 60*10;
				$cur_template = $mos_change_template;
				setcookie( "mos_user_template", "$mos_change_template", time()+$lifetime);
			} else {
				setcookie( "mos_user_template", "", time()-3600 );
			}
		}
		// TemplateChooser End

		if (isset($GLOBALS['gallery_popup'])) {
		    $cur_template = 'gallery_popup';
		}

		$this->_template = $cur_template;
		$this->_template_cols = $col_main;
	}

	function getTemplate() {
		return $this->_template;
	}

	/**
	* Determines the paths for including engine and menu files
	* @param string The current option used in the url
	* @param string The base path from which to load the configuration file
	*/
	function _setAdminPaths( $option, $basePath='.' ) {
		$option = strtolower( $option );
		$this->_path = new stdClass();

		$prefix = substr( $option, 0, 4 );
		$name = substr( $option, 4 );
		switch ($prefix) {
			case 'com_':
			// components
			if (file_exists( "$basePath/templates/$this->_template/components/$name.html.php" )) {
				$this->_path->front = "$basePath/components/$option/$name.php";
				$this->_path->front_html = "$basePath/templates/$this->_template/components/$name.html.php";
			} else if (file_exists( "$basePath/components/$option/$name.php" )) {
				$this->_path->front = "$basePath/components/$option/$name.php";
				$this->_path->front_html = "$basePath/components/$option/$name.html.php";
			}
			if (file_exists( "$basePath/administrator/components/$option/admin.$name.php" )) {
				$this->_path->admin = "$basePath/administrator/components/$option/admin.$name.php";
				$this->_path->admin_html = "$basePath/administrator/components/$option/admin.$name.html.php";
			}
			if (file_exists( "$basePath/administrator/components/$option/toolbar.$name.php" )) {
				$this->_path->toolbar = "$basePath/administrator/components/$option/toolbar.$name.php";
				$this->_path->toolbar_html = "$basePath/administrator/components/$option/toolbar.$name.html.php";
				$this->_path->toolbar_default = "$basePath/administrator/menubar/html/menudefault.php";
			}
			if (file_exists( "$basePath/components/$option/$name.class.php" )) {
				$this->_path->class = "$basePath/components/$option/$name.class.php";
			} else if (file_exists( "$basePath/administrator/components/$option/$name.class.php" )) {
				$this->_path->class = "$basePath/administrator/components/$option/$name.class.php";
			} else if (file_exists( "$basePath/classes/$name.php" )) {
				$this->_path->class = "$basePath/classes/$name.php";
			}
			break;

			default:
			// core
			if (file_exists( "$basePath/administrator/$option.php" )) {
				$this->_path->admin = "$basePath/administrator/$option.php";
			}
			if (file_exists( "$basePath/administrator/menubar/$option.php" )) {
				$this->_path->toolbar = "$basePath/administrator/menubar/$option.php";
			}
			if (file_exists( "$basePath/components/com_$option/$option.php" )) {
				$this->_path->front = "$basePath/components/com_$option/$option.php";
				$this->_path->front_html = "$basePath/components/com_$option/$option.php";
			}
			break;
		}
	}
	/**
	* Returns a stored path variable
	*
	*/
	function getPath( $varname, $option='' ) {
		if ($option) {
			$temp = $this->_path;
			$this->_setAdminPaths( $option, $this->getCfg( 'absolute_path' ) );
		}
		$result = null;
		if (isset( $this->_path->$varname )) {
			$result = $this->_path->$varname;
		}
		if ($option) {
			$this->_path = $temp;
		}
		return $result;
	}
	/**
	* Detects a 'visit'
	*
	* This function updates the agent and domain table hits for a particular
	* visitor.  The user agent is recorded/incremented if this is the first visit.
	* A cookie is set to mark the first visit.
	*/
	function detect() {
		if (mosGetParam( $_COOKIE, 'mosvisitor', 0 )) {
			return;
		}
		setcookie( "mosvisitor", "1" );

		if (phpversion() <= "4.2.1") {
			$agent = getenv( "HTTP_USER_AGENT" );
			$domain = gethostbyaddr( getenv( "REMOTE_ADDR" ) );
		} else {
			$agent = $_SERVER['HTTP_USER_AGENT'];
			$domain = gethostbyaddr( $_SERVER['REMOTE_ADDR'] );
		}

		$browser = mosGetBrowser( $agent );

		$this->_db->setQuery( "SELECT count(*) FROM #__stats_agents WHERE agent='$browser' AND type='0'" );
		if ($this->_db->loadResult()) {
			$this->_db->setQuery( "UPDATE #__stats_agents SET hits=(hits+1) WHERE agent='$browser' AND type='0'" );
		} else {
			$this->_db->setQuery( "INSERT INTO #__stats_agents (agent,type) VALUES ('$browser','0')" );
		}
		$this->_db->query();

		$os = mosGetOS( $agent );

		$this->_db->setQuery( "SELECT count(*) FROM #__stats_agents WHERE agent='$os' AND type='1'" );
		if ($this->_db->loadResult()) {
			$this->_db->setQuery( "UPDATE #__stats_agents SET hits=(hits+1) WHERE agent='$os' AND type='1'" );
		} else {
			$this->_db->setQuery( "INSERT INTO #__stats_agents (agent,type) VALUES ('$os','1')" );
		}
		$this->_db->query();

		// tease out the last element of the domain
		$tldomain = split( "\.", $domain );
		$tldomain = $tldomain[count( $tldomain )-1];

		if (is_numeric( $tldomain )) {
			$tldomain = "Unknown";
		}

		$this->_db->setQuery( "SELECT count(*) FROM #__stats_agents WHERE agent='$tldomain' AND type='2'" );
		if ($this->_db->loadResult()) {
			$this->_db->setQuery( "UPDATE #__stats_agents SET hits=(hits+1) WHERE agent='$tldomain' AND type='2'" );
		} else {
			$this->_db->setQuery( "INSERT INTO #__stats_agents (agent,type) VALUES ('$tldomain','2')" );
		}
		$this->_db->query();
	}
}

/**
* Utility class for all HTML drawing classes
*/
class mosHTML {
	function makeOption( $value, $text='' ) {
		$obj = new stdClass;
		$obj->value = $value;
		$obj->text = $text ? $text : $value;
		return $obj;
	}
	/**
	* Generates an HTML select list
	* @param array An array of objects
	* @param string The value of the HTML name attribute
	* @param string Additional HTML attributes for the <select> tag
	* @param string The name of the object varible for the option value
	* @param string The name of the object varible for the option text
	* @param mixed The key that is selected
	* @returns string HTML for the select list
	*/
	function selectList( &$arr, $tag_name, $tag_attribs, $key, $text, $selected ) {
		reset( $arr );
		$html = "\n<select name=\"$tag_name\" $tag_attribs>";
		for ($i=0, $n=count( $arr ); $i < $n; $i++ ) {
			$k = $arr[$i]->$key;
			$t = $arr[$i]->$text;

			$sel = '';
			if (is_array( $selected )) {
				foreach ($selected as $obj) {
					$k2 = $obj->$key;
					if ($k == $k2) {
						$sel = " selected=\"selected\"";
						break;
					}
				}
			} else {
				$sel = ($k == $selected ? " selected=\"selected\"" : '');
			}
			$html .= "\n\t<option value=\"".$k."\"$sel>" . $t . "</option>";
		}
		$html .= "\n</select>\n";
		return $html;
	}
	/**
	* Writes a select list of integers
	* @param int The start integer
	* @param int The end integer
	* @param int The increment
	* @param string The value of the HTML name attribute
	* @param string Additional HTML attributes for the <select> tag
	* @param mixed The key that is selected
	* @param string The printf format to be applied to the number
	* @returns string HTML for the select list
	*/
	function integerSelectList( $start, $end, $inc, $tag_name, $tag_attribs, $selected, $format="" ) {
		$start = intval( $start );
		$end = intval( $end );
		$inc = intval( $inc );
		$arr = array();
		for ($i=$start; $i <= $end; $i+=$inc) {
			$fi = $format ? sprintf( "$format", $i ) : "$i";
			$arr[] = mosHTML::makeOption( $fi, $fi );
		}

		return mosHTML::selectList( $arr, $tag_name, $tag_attribs, 'value', 'text', $selected );
	}
	/**
	* Writes a select list of month names based on Language settings
	* @param string The value of the HTML name attribute
	* @param string Additional HTML attributes for the <select> tag
	* @param mixed The key that is selected
	* @returns string HTML for the select list values
	*/
	function monthSelectList( $tag_name, $tag_attribs, $selected ) {
		$arr = array(
		mosHTML::makeOption( '01', _JAN ),
		mosHTML::makeOption( '02', _FEB ),
		mosHTML::makeOption( '03', _MAR ),
		mosHTML::makeOption( '04', _APR ),
		mosHTML::makeOption( '05', _MAY ),
		mosHTML::makeOption( '06', _JUN ),
		mosHTML::makeOption( '07', _JUL ),
		mosHTML::makeOption( '08', _AUG ),
		mosHTML::makeOption( '09', _SEP ),
		mosHTML::makeOption( '10', _OCT ),
		mosHTML::makeOption( '11', _NOV ),
		mosHTML::makeOption( '12', _DEC )
		);

		return mosHTML::selectList( $arr, $tag_name, $tag_attribs, 'value', 'text', $selected );
	}
	/**
	* Generates an HTML select list from a tree based query list
	* @param array Source array with id and parent fields
	* @param array The id of the current list item
	* @param array Target array.  May be an empty array.
	* @param array An array of objects
	* @param string The value of the HTML name attribute
	* @param string Additional HTML attributes for the <select> tag
	* @param string The name of the object varible for the option value
	* @param string The name of the object varible for the option text
	* @param mixed The key that is selected
	* @returns string HTML for the select list
	*/
	function treeSelectList( &$src_list, $src_id, $tgt_list, $tag_name, $tag_attribs, $key, $text, $selected ) {

		// establish the hierarchy of the menu
		$children = array();
		// first pass - collect children
		foreach ($src_list as $v ) {
			$pt = $v->parent;
			$list = @$children[$pt] ? $children[$pt] : array();
			array_push( $list, $v );
			$children[$pt] = $list;
		}
		// second pass - get an indent list of the items
		$ilist = mosTreeRecurse( 0, '', array(), $children );

		// assemble menu items to the array
		$this_treename = '';
		foreach ($ilist as $item) {
			if ($this_treename) {
				if ($item->id != $src_id && strpos( $item->treename, $this_treename ) === false) {
					$tgt_list[] = mosHTML::makeOption( $item->id, $item->treename );
				}
			} else {
				if ($item->id != $src_id) {
					$tgt_list[] = mosHTML::makeOption( $item->id, $item->treename );
				} else {
					$this_treename = "$item->treename/";
				}
			}
		}
		// build the html select list
		return mosHTML::selectList( $tgt_list, $tag_name, $tag_attribs, $key, $text, $selected );
	}
	/**
	* Writes a yes/no select list
	* @param string The value of the HTML name attribute
	* @param string Additional HTML attributes for the <select> tag
	* @param mixed The key that is selected
	* @returns string HTML for the select list values
	*/
	function yesnoSelectList( $tag_name, $tag_attribs, $selected ) {
		$arr = array(
		mosHTML::makeOption( '0', _CMN_NO ),
		mosHTML::makeOption( '1', _CMN_YES ),
		);

		return mosHTML::selectList( $arr, $tag_name, $tag_attribs, 'value', 'text', $selected );
	}

	function keySelectList( $key_context, $key_name, $tag_name, $tag_attribs, $selected ) {
		global $database;

		$database->setQuery( "SELECT a.id FROM #__core_lookup_keys AS a"
		. "\nWHERE a.context='$key_context' AND a.name='$key_name'"
		);
		$key_id = intval( $database->loadResult() );
		echo $database->getErrorMsg();

		$database->setQuery( "SELECT a.id AS value, a.name AS text FROM #__core_lookup_labels AS a"
		. "\nWHERE a.key_id='$key_id'"
		. "\nORDER BY a.ordering, a.name"
		);
		$arr = $database->loadObjectList();
		echo $database->getErrorMsg();

		return mosHTML::selectList( $arr, $tag_name, $tag_attribs, 'value', 'text', $selected );
	}

	function sortIcon( $base_href, $field, $state='none' ) {
		global $mosConfig_live_site;

		$alts = array(
		'none' => _CMN_SORT_NONE,
		'asc' => _CMN_SORT_ASC,
		'desc' => _CMN_SORT_DESC,
		);
		$next_state = 'asc';
		if ($state == 'asc') {
			$next_state = 'desc';
		} else if ($state == 'desc') {
			$next_state = 'none';
		}

		$html = "<a href=\"$base_href&field=$field&order=$next_state\">"
		. "<img src=\"$mosConfig_live_site/images/M_images/sort_$state.png\" width=\"12\" height=\"12\" border=\"0\" alt=\"{$alts[$next_state]}\" />"
		. "</a>";
		return $html;
	}
}

/**
* Category database table class
*/
class mosCategory extends mosDBTable {
	/** @var int Primary key */
	var $id=null;
	/** @var string The menu title for the Category (a short name)*/
	var $title=null;
	/** @var string The full name for the Category*/
	var $name=null;
	/** @var string */
	var $image=null;
	/** @var string */
	var $section=null;
	/** @var int */
	var $image_position=null;
	/** @var string */
	var $description=null;
	/** @var boolean */
	var $published=null;
	/** @var boolean */
	var $checked_out=null;
	/** @var time */
	var $checked_out_time=null;
	/** @var int */
	var $ordering=null;
	/** @var int */
	var $access=null;

	/**
	* @param database A database connector object
	*/
	function mosCategory( &$db ) {
		$this->mosDBTable( '#__categories', 'id', $db );
	}
	// overloaded check function
	function check() {
		// check for valid name
		if (trim( $this->title ) == '') {
			$this->_error = "Your Category must contain a title.";
			return false;
		}
		if (trim( $this->name ) == '') {
			$this->_error = "Your Category must have a name.";
			return false;
		}
		// check for existing name
		$this->_db->setQuery( "SELECT id FROM #__categories "
		. "\nWHERE name='".$this->name."' AND section='".$this->section."'"
		);

		$xid = intval( $this->_db->loadResult() );
		if ($xid && $xid != intval( $this->id )) {
			$this->_error = "There is a category already with that name, please try again.";
			return false;
		}
		return true;
	}
}

/**
* Section database table class
*/
class mosSection extends mosDBTable {
	/** @var int Primary key */
	var $id=null;
	/** @var string The menu title for the Section (a short name)*/
	var $title=null;
	/** @var string The full name for the Section*/
	var $name=null;
	/** @var string */
	var $image=null;
	/** @var string */
	var $scope=null;
	/** @var int */
	var $image_position=null;
	/** @var string */
	var $description=null;
	/** @var boolean */
	var $published=null;
	/** @var boolean */
	var $checked_out=null;
	/** @var time */
	var $checked_out_time=null;
	/** @var int */
	var $ordering=null;
	/** @var int */
	var $access=null;

	/**
	* @param database A database connector object
	*/
	function mosSection( &$db ) {
		$this->mosDBTable( '#__sections', 'id', $db );
	}
	// overloaded check function
	function check() {
		// check for valid name
		if (trim( $this->title ) == '') {
			$this->_error = "Your Section must contain a title.";
			return false;
		}
		if (trim( $this->name ) == '') {
			$this->_error = "Your Section must have a name.";
			return false;
		}
		// check for existing name
		$this->_db->setQuery( "SELECT id FROM #__sections "
		. "\nWHERE name='$this->name' AND scope='$this->scope'"
		);

		$xid = intval( $this->_db->loadResult() );
		if ($xid && $xid != intval( $this->id )) {
			$this->_error = "There is a section already with that name, please try again.";
			return false;
		}
		return true;
	}
}

/**
* Module database table class
*/
class mosContent extends mosDBTable {
	/** @var int Primary key */
	var $id=null;
	/** @var string */
	var $title=null;
	/** @var string */
	var $title_alias=null;
	/** @var string */
	var $introtext=null;
	/** @var string */
	var $fulltext=null;
	/** @var int */
	var $state=null;
	/** @var int The id of the category section*/
	var $sectionid=null;
	/** @var int */
	var $mask=null;
	/** @var int */
	var $catid=null;
	/** @var datetime */
	var $created=null;
	/** @var int User id*/
	var $created_by=null;
	/** @var string An alias for the author*/
	var $created_by_alias=null;
	/** @var datetime */
	var $modified=null;
	/** @var int User id*/
	var $modified_by=null;
	/** @var boolean */
	var $checked_out=null;
	/** @var time */
	var $checked_out_time=null;
	/** @var datetime */
	var $frontpage_up=null;
	/** @var datetime */
	var $frontpage_down=null;
	/** @var datetime */
	var $publish_up=null;
	/** @var datetime */
	var $publish_down=null;
	/** @var string */
	var $images=null;
	/** @var string */
	var $attribs=null;
	/** @var int */
	var $version=null;
	/** @var int */
	var $parentid=null;
	/** @var int */
	var $ordering=null;
	/** @var string */
	var $metakey=null;
	/** @var string */
	var $metadesc=null;
	/** @var int */
	var $access=null;
	/** @var int */
	var $hits=null;

	/**
	* @param database A database connector object
	*/
	function mosContent( &$db ) {
		$this->mosDBTable( '#__content', 'id', $db );
	}

	function check() {
		$this->introtext = trim( $this->introtext );
		$this->fulltext = trim( $this->fulltext );

		if (trim( str_replace( '&nbsp;', '', $this->fulltext ) ) == '') {
			$this->fulltext = '';
		}

		return true;
	}

	/**
	* Search method
	*
	* The sql must return the following fields that are used in a common display
	* routine: href, title, section, created, text, browsernav
	* @param string Target search string
	* @param integer The state to search for -1=archived, 0=unpublished, 1=published [default]
	* @param string A prefix for the section label, eg, 'Archived '
	*/
	function search( $text, $state='1', $sectionPrefix='' ) {
		global $my;
		global $mosConfig_abolute_path, $mosConfig_lang;

		$text = strtolower( trim( $text ) );
		$state = intval( $state );
		if ($text == '') {
			return array();
		}

		$where = array();
		$where[] = "LOWER(a.title) LIKE '%$text%'";
		$where[] = "LOWER(a.introtext) LIKE '%$text%'";
		$where[] = "LOWER(a.fulltext) LIKE '%$text%'";
		$where[] = "LOWER(a.metakey) LIKE '%$text%'";
		$where[] = "LOWER(a.metadesc) LIKE '%$text%'";

		$this->_db->setQuery( "SELECT a.title AS title,"
		. "\n	DATE_FORMAT(a.created,'%d %b') AS created,"
		. "\n	a.introtext AS text,"
		. "\n	CONCAT_WS('','$sectionPrefix',u.title,'/',b.title) AS section,"
		. "\n	CONCAT('index.php?option=content&task=view&id=',a.id) AS href,"
		. "\n	'2' AS browsernav"
		. "\nFROM #__content AS a"
		. "\nINNER JOIN #__categories AS b ON b.id=a.catid AND b.access <='$my->gid'"
		. "\nLEFT JOIN #__sections AS u ON u.id = a.sectionid"
		. "\nWHERE (".(implode( ' OR ', $where ) ).")"
		. "\n	AND a.state='$state' AND a.access<='$my->gid'"
		. "\nORDER BY created DESC"
		);

		$list = $this->_db->loadObjectList();

		// search typed content
		$this->_db->setQuery( "SELECT a.title, DATE_FORMAT(a.created,'%d %b') AS created,"
		. "\n	a.introtext AS text,"
		. "\n	CONCAT('index.php?option=content&task=view&id=',a.id,'&Itemid=',m.id) AS href,"
		. "\n	'2' as browsernav, '{$sectionPrefix}Typed' AS section"
		. "\nFROM #__content AS a"
		. "\nLEFT JOIN #__menu AS m ON m.componentid = a.id"
		. "\nWHERE (".(implode( ' OR ', $where ) ).")"
		. "\nAND a.state='$state' AND a.access<='$my->gid' AND m.type='content_typed'"
		. "\nORDER BY created DESC"
		);
		//print_r ($this->_db->loadObjectList());
		$list2 = $this->_db->loadObjectList();

		return array_merge( $list, $list2 );
	}


	/**
	* @param string Target search typed content string
	* @param integer The state to search for -1=archived, 0=unpublished, 1=published [default]
	*/
	function search_typed( $text, $state='1' ) {
		global $my;
		$text = trim( $text );
		$state = intval( $state );
		if ($text == '') {
			return array();
		}

		$this->_db->setQuery( "SELECT a.id, a.title, DATE_FORMAT(a.created,'%d %b') AS created, a.modified, a.catid, a.introtext AS text,"
		. " CONCAT('index.php?option=content&task=view&id=',a.id,'&Itemid=',m.id) AS href"
		. "\nFROM #__content AS a"
		. "\nLEFT JOIN #__menu AS m ON m.componentid = a.id"
		. "\nWHERE (a.title LIKE '%$text%' OR a.introtext LIKE '%$text%' OR a.fulltext LIKE '%$text%')"
		. "\nAND a.state='$state' AND a.access<='$my->gid' AND m.type='content_typed'"
		. "\nORDER BY created DESC"
		);
		//print_r ($this->_db->loadObjectList());
		return $this->_db->loadObjectList();
	}
}

/**
* Module database table class
*/
class mosMenu extends mosDBTable {
	/** @var int Primary key */
	var $id=null;
	/** @var string */
	var $menutype=null;
	/** @var string */
	var $name=null;
	/** @var string */
	var $link=null;
	/** @var int */
	var $type=null;
	/** @var int */
	var $published=null;
	/** @var int */
	var $componentid=null;
	/** @var int */
	var $parent=null;
	/** @var int */
	var $sublevel=null;
	/** @var int */
	var $ordering=null;
	/** @var boolean */
	var $checked_out=null;
	/** @var datetime */
	var $checked_out_time=null;
	/** @var boolean */
	var $pollid=null;

	/** @var string */
	var $browserNav=null;
	/** @var int */
	var $access=null;
	/** @var int */
	var $utaccess=null;
	/** @var string */
	var $params=null;

	/**
	* @param database A database connector object
	*/
	function mosMenu( &$db ) {
		$this->mosDBTable( '#__menu', 'id', $db );
	}
}

/**
* Users Table Class
*
* Provides access to the mos_templates table
*/
class mosUser extends mosDBTable {
	/** @var int Unique id*/
	var $id=null;
	/** @var string The users real name (or nickname)*/
	var $name=null;
	/** @var string The login name*/
	var $username=null;
	/** @var string email*/
	var $email=null;
	/** @var string MD5 encrypted password*/
	var $password=null;
	/** @var string */
	var $usertype=null;
	/** @var int */
	var $block=null;
	/** @var int */
	var $sendEmail=null;
	/** @var int The group id number */
	var $gid=null;
	/** @var datetime */
	var $registerDate=null;
	/** @var datetime */
	var $lastvisitDate=null;

	/**
	* @param database A database connector object
	*/
	function mosUser( &$database ) {
		$this->mosDBTable( '#__users', 'id', $database );
	}

	function check()
	{
		// Validate user information
		if (trim( $this->name ) == '') {
			$this->_error = _REGWARN_NAME;
			return false;
		}

		if (trim( $this->username ) == '') {
			$this->_error = _REGWARN_UNAME;
			return false;
		}

		if (eregi( "[^0-9A-Za-z]", $this->username)) {
			$this->_error = sprintf( _VALID_AZ09, "login name." );
			return false;
		}

		if ((trim($this->email == "")) || (preg_match("/[\w\.\-]+@\w+[\w\.\-]*?\.\w{1,4}/", $this->email )==false)) {
			$this->_error = _REGWARN_MAIL;
			return false;
		}

		// check for existing name
		$this->_db->setQuery( "SELECT id FROM #__users "
		. "\nWHERE username='$this->username' AND id!='$this->id'"
		);

		$xid = intval( $this->_db->loadResult() );
		if ($xid && $xid != intval( $this->id )) {
			$this->_error = _REGWARN_INUSE;
			return false;
		}

		return true;
	}

	function store( $updateNulls=false ) {
		global $acl, $migrate;
		$section_value = 'users';

		$k = $this->_tbl_key;
		$key =  $this->$k;
		if( $key && !$migrate) {
			// existing record
			$ret = $this->_db->updateObject( $this->_tbl, $this, $this->_tbl_key, $updateNulls );
			// syncronise ACL
			// single group handled at the moment
			// trivial to expand to multiple groups
			$groups = $acl->get_object_groups( $section_value, $this->$k, 'ARO' );
			$acl->del_group_object( $groups[0], $section_value, $this->$k, 'ARO' );
			$acl->add_group_object( $this->gid, $section_value, $this->$k, 'ARO' );

			$object_id = $acl->get_object_id( $section_value, $this->$k, 'ARO' );
			$acl->edit_object( $object_id, $section_value, $this->name, $this->$k, null, null, 'ARO' );
		} else {
			// new record
			$ret = $this->_db->insertObject( $this->_tbl, $this, $this->_tbl_key );
			// syncronise ACL
			$acl->add_object( $section_value, $this->name, $this->$k, null, null, 'ARO' );
			$acl->add_group_object( $this->gid, $section_value, $this->$k, 'ARO' );
		}
		if( !$ret ) {
			$this->_error = get_class( $this )."::store failed <br>" . $this->_db->getErrorMsg();
			return false;
		} else {
			return true;
		}
	}

	function delete( $oid=null ) {
		global $acl;
		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval( $oid );
		}
		$aro_id = $acl->get_object_id( 'users', $this->$k, 'ARO' );
		$acl->del_object( $aro_id, 'ARO', true );

		$this->_db->setQuery( "DELETE FROM $this->_tbl WHERE $this->_tbl_key = '".$this->$k."'" );

		if ($this->_db->query()) {
			// cleanup related data

			// :: private messaging
			$this->_db->setQuery( "DELETE FROM #__messages_cfg WHERE user_id='".$this->$k."'" );
			if (!$this->_db->query()) {
				$this->_error = $this->_db->getErrorMsg();
				return false;
			}
			$this->_db->setQuery( "DELETE FROM #__messages WHERE user_id_to='".$this->$k."'" );
			if (!$this->_db->query()) {
				$this->_error = $this->_db->getErrorMsg();
				return false;
			}

			return true;
		} else {
			$this->_error = $this->_db->getErrorMsg();
			return false;
		}
	}
}

/**
* Template Table Class
*
* Provides access to the mos_templates table
*/
class mosTemplate extends mosDBTable {
	/** @var int */
	var $id=null;
	/** @var string */
	var $cur_template=null;
	/** @var int */
	var $col_main=null;

	/**
	* @param database A database connector object
	*/
	function mosTemplate( &$database ) {
		$this->mosDBTable( '#__templates', 'id', $database );
	}
}

/**
* Utility function to return a value from a named array or a specified default
*/
function mosGetParam( &$arr, $name, $def=null ) {
	return isset( $arr[$name] ) ? $arr[$name] : $def;
}

/**
* Copy the named array content into the object as properties
* only existing properties of object are filled. when undefined in hash, properties wont be deleted
* @param array the input array
* @param obj byref the object to fill of any class
* @param string
* @param boolean
*/
function mosBindArrayToObject( $array, &$obj, $prefix=NULL, $checkSlashes=true ) {
	if (!is_array( $array ) || !is_object( $obj )) {
		return (false);
	}

	if ($prefix) {
		foreach (get_object_vars($obj) as $k => $v) {
			if (isset($array[$prefix . $k ])) {
				$obj->$k = ($checkSlashes && get_magic_quotes_gpc()) ? stripslashes( $array[$k] ) : $array[$k];
			}
		}
	} else {
		foreach (get_object_vars($obj) as $k => $v) {
			if (isset($array[$k])) {
				$obj->$k = ($checkSlashes && get_magic_quotes_gpc()) ? stripslashes( $array[$k] ) : $array[$k];
			}
		}
	}

	return true;
}

/**
* Utility function to read the files in a directory
* @param string The file system path
* @param string A filter for the names
*/
function mosReadDirectory( $path, $filter='.' ) {
	$arr = array();
	if (!@is_dir( $path )) {
		return $arr;
	}
	$handle = opendir( $path );

	while ($file = readdir($handle)) {
		if (($file <> ".") && ($file <> "..") && preg_match( "/$filter/", $file )) {
			//check for xml files with two periods . in the title : for example template.xml.bak which we want to avoid
			if ($filter == ".xml"){
				$file_count = explode(".",$file);
				if (count($file_count) == "2"){
					$arr[] = trim( $file );
				}
			} else {
				$arr[] = trim( $file );
			}

		}
	}
	closedir($handle);
	asort($arr);
	return $arr;
}

/**
* Utility function redirect the browser location to another url
*
* Can optionally provide a message.
* @param string The file system path
* @param string A filter for the names
*/
function mosRedirect( $url, $msg='' ) {
	if (trim( $msg )) {
		if (strpos( $url, '?' )) {
			$url .= "&mosmsg=$msg";
		} else {
			$url .= "?mosmsg=$msg";
		}
	}

	if (headers_sent()) {
		echo "<script>document.location.href='$url';</script>\n";
	} else {
		header( "Location: $url" );
		//header ("Refresh: 0 url=$url");
	}
	exit();
}

function mosTreeRecurse($id, $indent, $list, &$children, $maxlevel=9999, $level=0) {
	if (@$children[$id] && $level <= $maxlevel) {
		foreach ($children[$id] as $v) {
			$id = $v->id;
			$txt = $v->name;
			$pt = $v->parent;
			$list[$id] = $v;
			$list[$id]->treename = "$indent$txt";
			$list[$id]->children = count( @$children[$id] );
			$list = mosTreeRecurse( $id, "$indent$txt/", $list, $children, $maxlevel, $level+1 );
		}
	}
	return $list;
}

/**
** Function to strip additional / or \ in a path name
*/

function mosPathName($p_path,$p_addtrailingslash = true)
{
	$retval = "";

	if((substr(PHP_OS, 0, 3) == 'WIN' && stristr ( $_SERVER["SERVER_SOFTWARE"], "microsoft")))
	{
		$retval = str_replace('/','\\',$p_path);
		if($p_addtrailingslash)
		{
			if(substr($retval,-1) != '\\')
			$retval .= '\\';
		}

		// Remove double \\
		$retval = str_replace('\\\\','\\',$retval);
	}
	else
	{
		$retval = str_replace('\\','/',$p_path);
		if($p_addtrailingslash)
		{
			if(substr($retval,-1) != '/')
			$retval .= '/';
		}

		// Remove double //
		$retval = str_replace('//','/',$retval);
	}

	return $retval;

}

/**
* Module database table class
*/
class mosModule extends mosDBTable {
	/** @var int Primary key */
	var $id=null;
	/** @var string */
	var $title=null;
	/** @var string */
	var $showtitle=null;
	/** @var int */
	var $content=null;
	/** @var int */
	var $ordering=null;
	/** @var string */
	var $position=null;
	/** @var boolean */
	var $checked_out=null;
	/** @var time */
	var $checked_out_time=null;
	/** @var boolean */
	var $published=null;
	/** @var string */
	var $module=null;
	/** @var int */
	var $numnews=null;
	/** @var int */
	var $access=null;
	/** @var string */
	var $params=null;

	/**
	* @param database A database connector object
	*/
	function mosModule( &$db ) {
		$this->mosDBTable( '#__modules', 'id', $db );
	}
	// overloaded check function
	function check() {
		// check for valid name
		if (trim( $this->title ) == '') {
			$this->_error = "Your Module must contain a title.";
			return false;
		}
		// check for existing title
		$this->_db->setQuery( "SELECT id FROM #__modules"
		. "\nWHERE title='$this->title'"
		);

		$xid = intval( $this->_db->loadResult() );
		if ($xid && $xid != intval( $this->id )) {
			$this->_error = "There is a module already with that name, please try again.";
			return false;
		}
		return true;
	}
}

/**
* Session database table class
*/
class mosSession extends mosDBTable {
	/** @var int Primary key */
	var $session_id=null;
	/** @var string */
	var $time=null;
	/** @var string */
	var $userid=null;
	/** @var string */
	var $usertype=null;
	/** @var string */
	var $username=null;
	/** @var time */
	var $gid=null;
	/** @var int */
	var $guest=null;

	var $_session_cookie=null;

	/**
	* @param database A database connector object
	*/
	function mosSession( &$db ) {
		$this->mosDBTable( '#__session', 'session_id', $db );
	}

	function insert() {
		$ret = $this->_db->insertObject( $this->_tbl, $this );

		if( !$ret ) {
			$this->_error = get_class( $this )."::store failed <br>" . $this->_db->stderr();
			return false;
		} else {
			return true;
		}
	}

	function update( $updateNulls=false ) {
		$ret = $this->_db->updateObject( $this->_tbl, $this, 'session_id', $updateNulls );

		if( !$ret ) {
			$this->_error = get_class( $this )."::store failed <br>" . $this->_db->stderr();
			return false;
		} else {
			return true;
		}
	}

	function generateId() {
		$failsafe = 20;
		$randnum = 0;
		while ($failsafe--) {
			$randnum = md5( uniqid( microtime(), 1 ) );
			if ($randnum != "") {
				$cryptrandnum = md5( $randnum );
				$this->_db->setQuery( "SELECT $this->_tbl_key FROM $this->_tbl WHERE $this->_tbl_key=MD5('$randnum')" );
				if(!$result = $this->_db->query()) {
					die( $this->_db->stderr( true ));
					// todo: handle gracefully
				}
				if ($this->_db->getNumRows($result) == 0) {
					break;
				}
			}
		}
		$this->_session_cookie = $randnum;
		$this->session_id = md5( $randnum );
	}

	function getCookie() {
		return $this->_session_cookie;
	}

	function purge( $inc=1800 ) {
		$past = time() - $inc;
		$query = "DELETE FROM $this->_tbl"
		. "\nWHERE (time < $past) AND (usertype <> 'administrator' AND usertype <> 'superadministrator')";
		$this->_db->setQuery($query);

		return $this->_db->query();
	}
}


function mosObjectToArray($p_obj)
{
	$retarray = null;
	if(is_object($p_obj))
	{
		$retarray = array();
		foreach (get_object_vars($p_obj) as $k => $v)
		{
			if(is_object($v))
			$retarray[$k] = mosObjectToArray($v);
			else
			$retarray[$k] = $v;
		}
	}
	return $retarray;
}
/**
* Checks the user agent string against known browsers
*/
function mosGetBrowser( $agent ) {
	require( "includes/agent_browser.php" );

	if (preg_match( "/msie[\/\sa-z]*([\d\.]*)/i", $agent, $m )
	&& !preg_match( "/webtv/i", $agent )
	&& !preg_match( "/omniweb/i", $agent )
	&& !preg_match( "/opera/i", $agent )) {
		// IE
		return "MS Internet Explorer $m[1]";
	} else if (preg_match( "/netscape.?\/([\d\.]*)/i", $agent, $m )) {
		// Netscape 6.x, 7.x ...
		return "Netscape $m[1]";
	} else if ( preg_match( "/mozilla[\/\sa-z]*([\d\.]*)/i", $agent, $m )
	&& !preg_match( "/gecko/i", $agent )
	&& !preg_match( "/compatible/i", $agent )
	&& !preg_match( "/opera/i", $agent )
	&& !preg_match( "/galeon/i", $agent )
	&& !preg_match( "/safari/i", $agent )) {
		// Netscape 3.x, 4.x ...
		return "Netscape $m[2]";
	} else {
		// Other
		$found = false;
		foreach ($browserSearchOrder as $key) {	# Search ID in order of BrowsersSearchIDOrder
		if (preg_match( "/$key.?\/([\d\.]*)/i", $agent, $m )) {
			$name = "$browsersAlias[$key] $m[1]";
			return $name;
			break;
		}
		}
	}

	return 'Unknown';
}
/**
* Checks the user agent string against known operating systems
*/
function mosGetOS( $agent ) {
	require( "includes/agent_os.php" );

	foreach ($osSearchOrder as $key) {	# Search ID in order of osSearchIDOrder
	if (preg_match( "/$key/i", $agent )) {
		return $osAlias[$key];
		break;
	}
	}

	return 'Unknown';
}

/**
* @param string SQL with ordering As value and 'name field' AS text
*/
function mosGetOrderingList( $sql ) {
	global $database;

	$order = array();
	$database->setQuery( $sql );
	if (!($orders = $database->loadObjectList())) {
		if ($database->getErrorNum()) {
			echo $database->stderr();
			return false;
		} else {
			$order[] = mosHTML::makeOption( 1, 'first' );
			return $order;
		}
	}
	for ($i=0, $n=count( $orders ); $i < $n; $i++) {
		$order[] = mosHTML::makeOption( $orders[$i]->value, $orders[$i]->value.' (Currently: '.$orders[$i]->text.')' );
	}
	$order[] = mosHTML::makeOption( $orders[$i-1]->value+1, ($orders[$i-1]->value+1).' last' );

	return $order;
}

/**
* Makes a variable safe to display in forms
*
* Object parameters that are non-string, array, object or start with underscore
* will be converted
* @param object An object to be parsed
* @param int The optional quote style for the htmlspecialchars function
* @param string|array An optional single field name or array of field names not
*                     to be parsed (eg, for a textarea)
*/
function mosMakeHtmlSafe( &$mixed, $quote_style=null, $exclude_keys='' ) {
	if (is_object( $mixed )) {
		foreach (get_object_vars( $mixed ) as $k => $v) {
			if (is_array( $v ) || is_object( $v ) || $v == NULL || substr( $k, 1, 1 ) == '_' ) {
				continue;
			}
			if (is_string( $exclude_keys ) && $k == $exclude_keys) {
				continue;
			} else if (is_array( $exclude_keys ) && in_array( $k, $exclude_keys )) {
				continue;
			}
			$mixed->$k = htmlspecialchars( $v, $quote_style );
		}
	}
}

/**
* Checks whether a menu option is within the users access level
* @param int Item id number
* @param string The menu option
* @param int The users group ID number
* @param database A database connector object
* @return boolean True if the visitor's group at least equal to the menu access
*/
function mosMenuCheck( $Itemid, $menu_option, $gid, &$db ) {
	$dblink="index.php?option=$menu_option";
	$db->setQuery( "SELECT access FROM #__menu WHERE id='$Itemid' OR link='$dblink'" );
	$access = intval( $db->loadResult() );
	return ($access <= $gid);
}

function mosFormatDate( $date, $format=_DATE_FORMAT_LC ){
	global $mosConfig_offset;
	if ( $date && ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})[ ]([0-9]{2}):([0-9]{2}):([0-9]{2})", $date, $regs ) ) {
		$date = mktime( $regs[4], $regs[5], $regs[6], $regs[2], $regs[3], $regs[1] );
		$date = $date > -1 ? strftime( $format, $date + ($mosConfig_offset*3600) ) : '-';
	}
	return $date;
}

function mosCreateGUID(){
	srand((double)microtime()*1000000);
	$r = rand ;
	$u = uniqid(getmypid() . $r . (double)microtime()*1000000,1);
	$m = md5 ($u);
	return($m);
}

function mosCompressID( $ID ){
	return(Base64_encode(pack("H*",$ID)));
}

function mosExpandID( $ID ) {
	return ( implode(unpack("H*",Base64_decode($ID)), '') );
}

?>