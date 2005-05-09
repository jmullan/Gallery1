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
class galleryTable {

    var $class;
    var $columns;
    var $headers;
    var $elements;

    function galleryTable($columns = 3) {
	$this->columns = $columns;
	$this->elements = array();
    }

    function addElement($element = null) {

	if (!empty($element)) {
	    if (!is_array($element)) {
		$this->elements[] = $element;
	    } else {
		$this->elements = array_merge($this->elements,$element);
	    }
	    return true;
	} else {
	    return false;
        }
    }

    function render() {

	$buf = "\n<table>";
	if (!empty($this->headers)) {
	    $buf .= "\n<tr>";
	    $i = 0;
	    foreach ($this->headers as $header) {
		$i++;
		$buf .="\n<th>$header</th>";
	    }

	    for (; $i < $this->columns; $i++) {
		$buf .="\n<th>&nbsp;</th>";
	    }

	}
	if (!empty($this->elements)) {
	    $i = 0;
	    $buf .= "\n<tr>";
	    foreach ($this->elements as $element) {
		$i++;
		if ($i % $this->columns) {
		    $buf .= "\n</tr>\n<tr>";
		}
		$buf .= "\n    <td>$element</td>";
	    }
	    $buf .= "\n</tr>";
	}
	$buf .= "\n</table>";

	return $buf;
    }
}   

?>
