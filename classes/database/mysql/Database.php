<?
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2001 Bharat Mediratta
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
 */
?>
<?
class MySQL_Database extends Abstract_Database {
	var $link;

	function MySQL_Database($host, $uname, $pass, $dbname) {
		$this->link = mysql_connect($host, $uname, $pass);
		mysql_select_db($dbname, $this->link);
	}

	function query($sql) {
		return mysql_query($sql, $this->link);
	}

	function fetch_row($results) {
		$row = mysql_fetch_row($results);
		return $row;
	}
}
?>