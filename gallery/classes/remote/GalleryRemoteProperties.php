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
 * $Id$
 */
?>
<?php

/*
 * workaround for php < 4.1.0
 */
if (!function_exists ('array_key_exists')) {
	function array_key_exists($key, $arr)
	{
		if (!is_array($arr)) {
			return false;
		}
		foreach (array_keys($arr) as $k) {
			if ($k == $key) {
				return true;
			}
		}
		return false;
	}
}

/**
 *  This class partially mirrors the functionality of the java class
 *	java.util.Properties.
 *
 *@author     tmiller
 *@created    September 29, 2002
 */
class Properties {
	var $map;
	
	function Properties( $defaults = null ) {
		$this->map = $defaults;
	}

	function getProperty( $key, $defaultValue = null ) {
		if ( array_key_exists( $key, $this->map ) ) {
			return $this->map[ $key ];
		} else {
			return $defaultValue;
		}
	}
	
	function listprops() {	// list is a php reserved word
		$ret = "#__GR2PROTO__\n";
		foreach (array_keys($this->map) as $k) {
			$ret .= "$k=" . $this->escape($this->map[$k]) . "\n";
		}
		return $ret;
	}
	
	function setProperty( $key, $value ) {
		if ( $key != null ) {
			$this->map[$key] = $value;	
		}
	}

	function escape( $value ) {
		// TODO: real Java properties escaping...
		$result = str_replace("\r\n", "\\n", $value);
		$result = str_replace("\n", "\\n", $result);
		$result = str_replace("\r", "\\n", $result);
		
		return $result;
	}
}
?>
